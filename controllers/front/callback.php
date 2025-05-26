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


    private const LOG_FILE_PATH = _PS_MODULE_DIR_ . 'cetelempayment/registro.log';


    private $cetelemStates;

    public function __construct()
    {
        parent::__construct();
        require_once _PS_MODULE_DIR_ . 'cetelempayment/classes/CetelemStates.php';
        $this->cetelemStates = new CetelemStates();
    }

    public function initContent()
    {
        if (Tools::getValue('getversion')) {
            $this->getModuleVersion();
            die();
        }
        parent::initContent();
        $this->setTemplate('module:cetelempayment/views/templates/front/callback.tpl');
    }


    public function postProcess()
    {
        $this->validateCetelemIP();

        if (Tools::isSubmit('IdTransaccion') && Tools::isSubmit('codResultado')) {
            $this->processTransaction();
        }
    }


    private function validateCetelemIP()
    {
        $cetelem_ips = Configuration::get('CETELEM_IPS')
            ? explode(",", Configuration::get('CETELEM_IPS'))
            : ['213.170.60.39', '37.29.249.178', '77.229.109.12'];

        $remoteip = $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR'];

        if (Configuration::get('CETELEM_CALLBACK_IP_RES')) {
            $matched_ip = in_array($remoteip, $cetelem_ips);
            if (!$matched_ip) {
                $this->writeToLog("Unauthorized IP: $remoteip");
                PrestaShopLogger::addLog(
                    'Cetelem::CallBack - IP not authorized',
                    1,
                    null,
                    'Transaction',
                    null,
                    true
                );
                die('Unauthorized IP');
            }
        }
    }

    private function isTransactionDuplicate($idTransaccion, $Codigo, $idCart)
    {
        $query = new DbQuery();
        $query->select('COUNT(*)');
        $query->from('cetelem_transactions');
        $query->where('transaction_id = "' . pSQL($idTransaccion) . '" AND result_code "' . pSQL($Codigo) . '" AND id_cart "' . pSQL($idCart) . '"');

        return (bool)Db::getInstance()->getValue($query);
    }
    private function getIdCart($idTransaccion)
    {
        $query = new DbQuery();
        $query->select('id_cart');
        $query->from('cetelem_transactions');
        $query->where('transaction_id = "' . pSQL($idTransaccion) . '"');
        
        return Db::getInstance()->getValue($query);
    }
    private function processTransaction()
    {
        $idTransaccion = Tools::getValue('IdTransaccion');
        $codResultado = Tools::getValue('codResultado');

        $id_cart = $this->getIdCart($idTransaccion);
        $cart = new Cart($id_cart);
      
        $existing_order = Order::getOrderByCartId($id_cart);

        if ($existing_order) {
            $this->writeToLog("Order already exists for Cart ID: {$cart->id}");
            $new_state = $this->getOrderStateForResultCode($codResultado);
            $this->updateOrderState(new Order($existing_order), $new_state);
            $this->sendStatus(1, $existing_order);
            return;
        }

        if (!Validate::isLoadedObject($cart)) {
            $this->writeToLog("Cart not found for ID: $id_cart");
            $this->sendStatus(7, null);
            return;
        }

        if ($this->isTransactionDuplicate($idTransaccion, $codResultado, $id_cart)) {
            $this->writeToLog("Duplicate transaction detected: $idTransaccion");
            $this->sendStatus(1, $existing_order);
            return;
        }

        $this->updateTransactionToDatabase($idTransaccion, $codResultado, $id_cart);
        $this->validateOrder($cart, $codResultado);
    }


    private function updateTransactionToDatabase($idTransaccion, $codResultado, $id_cart)
    {
        $data = [
            'result_code' => pSQL($codResultado),
            'date_add' => date('Y-m-d H:i:s'),
        ];

        $where = 'id_cart = ' . (int)$id_cart . ' AND transaction_id = "' . pSQL($idTransaccion) . '"';

        $result = Db::getInstance()->update('cetelem_transactions', $data, $where);

        if (!$result) {
            $this->writeToLog("Failed to update transaction in DB: $idTransaccion");
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - DB Update Error',
                3,
                null,
                'Transaction',
                null,
                true
            );
        }
    }


    private function validateOrder($cart, $codResultado)
    {
        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            $this->writeToLog("Invalid customer for Cart ID: {$cart->id}");
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - Invalid Customer',
                1,
                null,
                'Cart',
                (int)$cart->id,
                true
            );
            return;
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $moduleName = Tools::getValue('encuotas') ? 'encuotas' : $this->l('Financiación con Cetelem', 'cetelempayment');;
        $orderState = $this->getOrderStateForResultCode($codResultado);

        try {
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
            $this->writeToLog("Pedido validado con código: $codResultado y Estado $orderState");

            $this->processOrderAfterValidation($cart, $codResultado);
        } catch (Exception $e) {
            $this->writeToLog("Order validation error: " . $e->getMessage());
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - Order Validation Error',
                3,
                $e->getCode(),
                'Cart',
                (int)$cart->id,
                true
            );
        }
    }


    private function getOrderStateForResultCode($codResultado)
    {

        switch ($codResultado) {
            case self::RESULT_CODE_PRE_APPROVED:
                return $this->cetelemStates->StateCetelemPreApproved();
            case self::RESULT_CODE_APPROVED:
                return $this->cetelemStates->StateCetelemApproved();
            case self::RESULT_CODE_DENIED_1:
            case self::RESULT_CODE_DENIED_2:
                return $this->cetelemStates->StateCetelemDenied();
            default:
                return $this->cetelemStates->StateCetelemPreApproved();
        }
    }


    private function processOrderAfterValidation($cart, $codResultado)
    {
        $order = new Order(Order::getOrderByCartId($cart->id));
        if ($order->id) {
            switch ($codResultado) {
                case self::RESULT_CODE_APPROVED:
                    $this->updateOrderState($order, $this->cetelemStates->StateCetelemApproved());
                    $this->sendStatus(1, $order->id);
                    break;

                case self::RESULT_CODE_PRE_APPROVED:

                    $this->updateOrderState($order, $this->cetelemStates->StateCetelemPreApproved());
                    $this->sendStatus(2, $order->id);
                    break;

                case self::RESULT_CODE_DENIED_1:
                case self::RESULT_CODE_DENIED_2:
                    $this->updateOrderState($order, $this->cetelemStates->StateCetelemDenied());
                    $this->sendStatus(7, null);
                    break;
            }
        }
    }
    private function updateOrderState(Order $order, $new_state)
    {
        try {
            $order->setCurrentState($new_state);
            $order->save();
            $this->writeToLog("Order state updated: Order ID {$order->id} -> State {$new_state}");
        } catch (Exception $e) {
            $this->writeToLog("Failed to update order state: " . $e->getMessage());
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - Order State Update Error',
                3,
                $e->getCode(),
                'Order',
                $order->id,
                true
            );
        }
    }


    public function writeToLog($logText = null)
    {
        $message = "\n\n-------------------" . date("d/M/Y H:i") . "--------------------\n";
        $message .= $logText . "\n";
        $message .= "URL: " . $_SERVER['REQUEST_URI'] . "\n";
        $message .= "GET Parameters: " . json_encode($_GET) . "\n";
        $message .= "POST Parameters: " . json_encode($_POST) . "\n";
        if (filter_var(self::LOG_FILE_PATH, FILTER_VALIDATE_URL)) {

            file_put_contents(self::LOG_FILE_PATH, $message, FILE_APPEND);
        }
    }


    public function sendStatus($statusCode, $orderID)
    {
        $data = [
            'statusCode' => $statusCode,
            'statusText' => $statusCode == 1 ? "OK" : "ERROR",
            'orderId' => $statusCode == 1 ? $orderID : null,
            'errorData' => $statusCode == 7 ? "Error processing transaction" : null,
        ];

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function getModuleVersion()
    {
        $module = Module::getInstanceByName('cetelempayment');
        $version = $module->version;
        $autor = $module->author;


        echo htmlspecialchars($version);
        echo htmlspecialchars($autor);
    }
}
