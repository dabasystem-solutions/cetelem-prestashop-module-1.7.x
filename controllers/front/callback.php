<?php

/**
 *  2007-2024 PrestaShop
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  https://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to https://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2024 PrestaShop SA
 *  @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class CetelemPaymentCallbackModuleFrontController extends ModuleFrontController
{

    private const RESULT_CODE_PRE_APPROVED = '00';
    private const RESULT_CODE_APPROVED = '50';
    private const RESULT_CODE_DENIED_1 = '99';
    private const RESULT_CODE_DENIED_2 = '51';

    //DETERMINA SI SE DEBEN DE ALMACENAR LOS LOGS.
    //UTIL PARA CUANDO SE DEBE DE RASTREAR ALGUN PROBLEMA
    private const ACTIVE_LOGS = false;

    private static ?string $logFilePath = null;

    private $cetelemStates;

    public function __construct()
    {

        parent::__construct();

         if (!self::$logFilePath) {
            self::$logFilePath = _PS_MODULE_DIR_ . 'cetelempayment/tmp/registro-' . date('Y-m-d - H:i:s') . '.log';
        }

        require_once _PS_MODULE_DIR_ . 'cetelempayment/classes/CetelemStates.php';
        $this->cetelemStates = new CetelemStates();
    }

    public function initContent(){
        $this->writeToDebug('initContent');
        if (Tools::getValue('getversion')) {
            $this->getModuleVersion();
            die();
        }
        parent::initContent();
        $this->writeToDebug('initContent --> SetTemplate()');
        $this->setTemplate('module:cetelempayment/views/templates/front/callback.tpl');
        $this->writeToDebug('END initContent');
    }


    public function postProcess()
    {        
        $this->writeToLog('init postProcess');
        $this->validateCetelemIP();

        if (Tools::isSubmit('IdTransaccion') && Tools::isSubmit('codResultado')) {
            $this->writeToDebug('postProcess -> processTransaction()');
            $this->processTransaction();
        }
        $this->writeToDebug('end postProcess');
    }


    private function validateCetelemIP()
    {
        $this->writeToDebug('init validateCetelemIP');
        $cetelem_ips = Configuration::get('CETELEM_IPS')
            ? explode(",", Configuration::get('CETELEM_IPS'))
            : ['213.170.60.39', '37.29.249.178', '77.229.109.12'];

        $remoteip = $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR'];

        if (Configuration::get('CETELEM_CALLBACK_IP_RES')) {
            $matched_ip = in_array($remoteip, $cetelem_ips);
            if (!$matched_ip) {
                $this->writeToDebug('validateCetelemIP: UNAUTHORIZED');
                $this->writeToDebug("Unauthorized IP: $remoteip");
                PrestaShopLogger::addLog(
                    'Cetelem::CallBack - IP not authorized',
                    1,
                    null,
                    'Transaction',
                    null,
                    true
                );
                $this->writeToDebug('DIE validateCetelemIP');
                die('Unauthorized IP');
            }
        }
    }

    private function isTransactionDuplicate($idTransaccion, $codigo, $idCart)
    {
        $this->writeToDebug('init isTransactionDuplicate');
        if (empty($idTransaccion) || empty($codigo) || empty($idCart)) {
            $this->writeToDebug("isTransactionDuplicate: parámetros inválidos o vacíos");
            $this->writeToDebug('isTransactionDuplicate -->parámetros inválidos o vacíos ');
            return true;
        }
        $this->writeToDebug('isTransactionDuplicate COUNT cetelem_transactions');
        $query = new DbQuery();
        $query->select('COUNT(*)')
            ->from('cetelem_transactions')
            ->where('transaction_id = "' . pSQL($idTransaccion) . '"')
            ->where('result_code = "' . pSQL($codigo) . '"')
            ->where('id_cart = ' . (int)$idCart);

        try {
            $count = (int) Db::getInstance()->getValue($query);
            if ($count > 0) {
                $this->writeToDebug("isTransactionDuplicate: Transacción duplicada detectada para ID: $idTransaccion");
                $this->writeToDebug('isTransactionDuplicate COUNT cetelem_transactions > 0 ');
                return true;
            }            
            return false;
        } catch (Exception $e) {
            $this->writeToDebug('isTransactionDuplicate COUNT cetelem_transactions ERR ');
            $this->writeToDebug("isTransactionDuplicate: Error al consultar la base de datos - " . $e->getMessage());
            return true;
        }
        $this->writeToDebug('END isTransactionDuplicate');
    }

    private function getIdCart($idTransaccion)
    {
        $this->writeToDebug('INIT getIdCart()');
        $query = new DbQuery();
        $query->select('id_cart');
        $query->from('cetelem_transactions');
        $query->where('transaction_id = "' . pSQL($idTransaccion) . '"');
        $this->writeToDebug('END getIdCart()');
        return Db::getInstance()->getValue($query);
    }


    private function processTransaction()
    {
        $this->writeToDebug('INIT processTransaction');

        $idTransaccion = Tools::getValue('IdTransaccion');
        $codResultado = Tools::getValue('codResultado');

        $id_cart = $this->getIdCart($idTransaccion);
        $cart = new Cart($id_cart);
      
        $existing_order = Order::getOrderByCartId($id_cart);

        if ($existing_order) {
            $this->writeToDebug('processTransaction EXISTING ORDER == TRUE');
            $this->writeToDebug("Order already exists for Cart ID: {$cart->id}");
            $new_state = $this->getOrderStateForResultCode($codResultado);
            $this->updateOrderState(new Order($existing_order), $new_state);
            $this->sendStatus(1, $existing_order);
            $this->writeToDebug('END processTransaction');
            return;
        }

        if (!Validate::isLoadedObject($cart)) {
            $this->writeToDebug('processTransaction ERR NO CART');
            $this->writeToDebug("Cart not found for ID: $id_cart");
            $this->sendStatus(7, null);
            $this->writeToDebug('END processTransaction');
            return;
        }

        if ($this->isTransactionDuplicate($idTransaccion, $codResultado, $id_cart)) {
            $this->writeToDebug('processTransaction duplicated transaction');
            $this->writeToDebug("Duplicate transaction detected: $idTransaccion");
            PrestaShopLogger::addLog(
                'Duplicate transaction detected',
                3,
                null,
                'Transaction',
                null,
                true
            );
            $this->sendStatus(7, $existing_order);
            $this->writeToDebug('END processTransaction');
            return;
        }
        $this->writeToDebug('processTransaction updateTransactionToDatabase()');
        $this->updateTransactionToDatabase($idTransaccion, $codResultado, $id_cart);
        $this->writeToDebug('processTransaction validateOrder()');
        $this->validateOrder($cart, $codResultado);
        $this->writeToDebug('END processTransaction');
    }

    private function updateTransactionToDatabase($idTransaccion, $codResultado, $id_cart)
    {
        $this->writeToDebug('INIT updateTransactionToDatabase');
        $data = [
            'result_code' => pSQL($codResultado),
            'date_add' => date('Y-m-d H:i:s'),
        ];

        $where = 'id_cart = ' . (int)$id_cart . ' AND transaction_id = "' . pSQL($idTransaccion) . '"';

        $result = Db::getInstance()->update('cetelem_transactions', $data, $where);

        if (!$result) {
            $this->writeToDebug('updateTransactionToDatabase NO RESULT');
            $this->writeToDebug("Failed to update transaction in DB: $idTransaccion");
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - DB Update Error',
                3,
                null,
                'Transaction',
                null,
                true
            );
        }
        $this->writeToDebug('END updateTransactionToDatabase');
    }


    private function validateOrder($cart, $codResultado)
    {
        $this->writeToDebug('INIT validateOrder');
        $customer = new Customer($cart->id_customer);
       
        if (!Validate::isLoadedObject($customer)) {
            $this->writeToDebug('validateOrder Invalid customer for Cart ID');
            $this->writeToDebug("Invalid customer for Cart ID: {$cart->id}");
            PrestaShopLogger::addLog(
                "Cetelem::CallBack - Invalid Customer CUSTOMER==> {$customer->id}
                EL CART-ID eS: {$cart->id}
                ",
                1,
                null,
                'Cart',
                (int)$cart->id,
                true
            );
            $this->writeToDebug('END validateOrder');
            return;
        }
        $this->writeToDebug('validateOrder CURRENCY');
        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $moduleName = Tools::getValue('encuotas') ? 'encuotas' : $this->l('Financiación con Cetelem', 'cetelempayment');;
        $this->writeToDebug('validateOrder ORDER STATE');
        $orderState = $this->getOrderStateForResultCode($codResultado);
        $this->writeToDebug("validateOrder orderState = {$orderState}" );
       
        $arrayLog['cart->id'] = $cart->id;
        $arrayLog['orderState'] = $orderState;
        $arrayLog['currency'] = (int)$currency->id;
        $arrayLog['customer'] = $customer->secure_key;

        $this->writeToDebug("Pedido validado con código:\n ".print_r( $arrayLog, true ));
        $this->writeToDebug('validateOrder module->validateOrder()');
        $this->module->validateOrder(
            $cart->id,
            $orderState,
            $total,
            $moduleName,
            '',
            [],
            (int)$currency->id,
            false,
            $customer->secure_key
        );
        $this->writeToDebug("Pedido validado con código: $codResultado y Estado $orderState");
        $this->writeToDebug('validateOrder processOrderAfterValidation()');
        $this->processOrderAfterValidation($cart, $codResultado);
       
        $this->writeToDebug('END validateOrder');
    }


    private function getOrderStateForResultCode($codResultado)
    {
        $this->writeToDebug('INIT getOrderStateForResultCode');
        switch ($codResultado) 
        {
            case self::RESULT_CODE_PRE_APPROVED:
                $this->writeToDebug('END getOrderStateForResultCode PRE_APPROVED');
                return $this->cetelemStates->StateCetelemPreApproved();
            case self::RESULT_CODE_APPROVED:
                $this->writeToDebug('END getOrderStateForResultCode StateCetelemApproved');
                return $this->cetelemStates->StateCetelemApproved();
            case self::RESULT_CODE_DENIED_1:
            case self::RESULT_CODE_DENIED_2:
                $this->writeToDebug('END getOrderStateForResultCode DENIED');
                return $this->cetelemStates->StateCetelemDenied();
            default:
                $this->writeToDebug('END getOrderStateForResultCode DEFAULT');
                return $this->cetelemStates->StateCetelemPreApproved();       
        }
    }


    private function processOrderAfterValidation($cart, $codResultado)
    {
        $this->writeToDebug('INIT processOrderAfterValidation');
        $order = new Order(Order::getOrderByCartId($cart->id));
        if ($order->id) 
        {
            $this->writeToDebug('processOrderAfterValidation codResultado');
            switch ($codResultado) 
            {
                case self::RESULT_CODE_APPROVED:
                    $this->writeToDebug('processOrderAfterValidation codResultado APPROVED');
                    $this->updateOrderState($order, $this->cetelemStates->StateCetelemApproved());
                    $this->sendStatus(1, $order->id);
                    break;

                case self::RESULT_CODE_PRE_APPROVED:
                    $this->writeToDebug('processOrderAfterValidation codResultado PREAPPROVED');
                    if($order->getCurrentOrderState() == $this->cetelemStates->StateCetelemApproved())
                    {
                        $this->writeToDebug('processOrderAfterValidation codResultado PREAPPROVED=>Aproved');
                        $this->updateOrderState($order, $this->cetelemStates->StateCetelemApproved());
                    }
                    else
                    {
                        $this->writeToDebug('processOrderAfterValidation codResultado PREAPPROVED=>PreAproved');
                        $this->updateOrderState($order, $this->cetelemStates->StateCetelemPreApproved());
                    }
                    $this->sendStatus(1, $order->id);
                    break;

                case self::RESULT_CODE_DENIED_1:
                case self::RESULT_CODE_DENIED_2:
                    $this->writeToDebug('processOrderAfterValidation codResultado DENIED');
                    $this->updateOrderState($order, $this->cetelemStates->StateCetelemDenied());
                    $this->sendStatus(1, null);
                    break;                
            }
            $this->writeToDebug('END processOrderAfterValidation');
        }
    }
    private function updateOrderState(Order $order, $new_state)
    {
        $this->writeToDebug('INIT updateOrderState');

        try {
            $current_state = $order->getCurrentState();
            $this->writeToDebug($new_state." == ".$this->cetelemStates->StateCetelemPreApproved()." && ". $current_state ." == ".$this->cetelemStates->StateCetelemApproved()."');" );
            // Si el nuevo estado es PRE_APPROVED y el estado actual es APPROVED, no permitir el cambio
            if 
            (
                $new_state == $this->cetelemStates->StateCetelemPreApproved() &&
                $current_state == $this->cetelemStates->StateCetelemApproved()
            ) 
            {
                $this->writeToDebug('updateOrderState :PREAPROBADO un pedido ya APROBADO ');
                $this->writeToDebug("No se puede poner PREAPROBADO un pedido ya APROBADO. Order ID {$order->id} -> State actual {$current_state}");
                $this->sendStatus(8, $order->id);            
            }
            $this->writeToDebug('updateOrderState set new_state');
            $this->writeToDebug("updateOrderState newState == " . print_r($new_state, true));

            $order->setCurrentState($new_state);

            $this->writeToDebug("Order state updated: Order ID {$order->id} -> State {$new_state}");
            $this->writeToDebug('updateOrderState save()');
            $order->save();

        } catch (Exception $e) {
            $this->writeToDebug('updateOrderState ERRCatch');
            $this->writeToDebug("Failed to update order state: " . $e->getMessage());
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - Order State Update Error'.$e->getMessage(),
                3,
                $e->getCode(),
                'Order',
                $order->id,
                true
            );
        }
        $this->writeToDebug('END updateOrderState');
    }


    public function writeToLog($logText = null)
    {

        $message = "\n\n-------------------" . date("d/M/Y H:i") . "--------------------\n";
        $message .= $logText . "\n";
        $message .= "URL: " . $_SERVER['REQUEST_URI'] . "\n";
        $message .= "GET Parameters: " . json_encode($_GET) . "\n";
        $message .= "POST Parameters: " . json_encode($_POST) . "\n";
        
        if (self::ACTIVE_LOGS) {
            file_put_contents(self::$logFilePath, $message, FILE_APPEND);
        }
    }


    public function writeToDebug($logText = null)
    {

        $message = $logText."\n";
        if (self::ACTIVE_LOGS) {
            file_put_contents(self::$logFilePath, $message, FILE_APPEND);
        }
    }


    public function sendStatus($statusCode, $orderID)
    {
        $this->writeToDebug('INIT sendStatus');
        $data = [
            'statusCode' => $statusCode,
            'statusText' => $statusCode == 1 ? "OK" : "ERROR",
            'orderId' => $statusCode == 1 ? $orderID : null,
            'errorData' => $statusCode == 7 ? "Error processing transaction" : null,
        ];

        header('Content-Type: application/json');
        echo json_encode($data);
        $this->writeToDebug('END sendStatus');
        exit;
    }

    private function getModuleVersion()
    {
        $this->writeToDebug('INIT getModuleVersion');
        $module = Module::getInstanceByName('cetelempayment');
        $version = $module->version;
        $autor = $module->author;

        $this->writeToDebug('END getModuleVersion');
        echo htmlspecialchars($version);
        echo htmlspecialchars($autor);
    }
}
