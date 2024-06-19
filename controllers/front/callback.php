<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2021 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class CetelemCallbackModuleFrontController extends ModuleFrontController
{

  
    
    public function initContent()
    {

        parent::initContent();



        //$this->writeToLog("initContent\n");

        
        $this->setTemplate('module:cetelem/views/templates/front/callback.tpl');
    }

    // Función para escribir en el archivo de registro
    public function writeToLog($logText = null) {
        global $logFile;
        $logFile = $_SERVER['DOCUMENT_ROOT'].'/modules/cetelem/registro.log';
        $url = $_SERVER['REQUEST_URI'];

        $getParams = $_GET;
        
        $postParams = $_POST;
        
        $message = "\n\n\n-------------------".date("d/M/Y H:i")."--------------------\n\n";
        $message .= "URL: $url\n";
        $message .= $logText;
        $message .= "GET Parameters: " . json_encode($getParams) . "\n";
        $message .= "POST Parameters: " . json_encode($postParams) . "\n";

        file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND);
    }

    public function postProcess()
    {   

        //$this->writeToLog("postProcess\n");


        $file = _PS_MODULE_DIR_ . 'cetelem/tmp/transaction64e';
        file_put_contents($file, "ring");

        $cetelem_ips = explode(",", Configuration::get('CETELEM_IPS'));
        if (!is_array($cetelem_ips)) {
            $cetelem_ips = array($cetelem_ips);
        }
        if (!$cetelem_ips) {
            $cetelem_ips = array('213.170.60.39','37.29.249.178');
        }
        $matched_ip = false;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $remoteip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $remoteip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $remoteip = $_SERVER['REMOTE_ADDR'];
        }
        if (Configuration::get('CETELEM_CALLBACK_IP_RES')) {
            foreach ($cetelem_ips as $cetelem_ip) {
                if ($remoteip == $cetelem_ip) {
                    $matched_ip = true;
                }
            }
            //!temporal
            /* if (!$matched_ip) {
                die();
            } */
        }
        if (Tools::isSubmit('IdTransaccion') and Tools::isSubmit('codResultado')) {
            
            //Crea log de la trasaccion la cual luego sera verificada.
            $string = Tools::getValue('IdTransaccion') . '|' . Tools::getValue('NSolicitud') . '|' . Tools::getValue(
                'codResultado'
            );

            $file = _PS_MODULE_DIR_ . 'cetelem/tmp/transaction' . Tools::getValue('IdTransaccion');
            if(is_numeric(Tools::getValue('IdTransaccion')))
                 file_put_contents($file, $string);
                 
            if (Configuration::get('CETELEM_ORDER_CREATION')) {
                $this->valdiationWithoutURL();
            } else {
                //Check if order exist yet
                //$id_cart = Tools::substr(Order::getCartIdStatic((int)Tools::getValue('IdTransaccion')), 4);
                //$id_cart = Order::getCartIdStatic((int)Tools::substr((int)Tools::getValue('IdTransaccion'), 4));
                $id_cart = (int)Tools::substr(Tools::getValue('IdTransaccion'), 4);
                //var_dump($id_cart);
                //die();

                $cart = new Cart($id_cart);
                if (Order::getOrderByCartId($cart->id) > 0) {
                    $this->valdiationWithoutURL();
                } else {
                    $this->createWithoutURL();
                }
            }
        }
    }

    public function checkIforderExists()
    {
        //Load Cart Id from POST content
        $id_cart = Order::getCartIdStatic((int)Tools::getValue('IdTransaccion'));
        $cart = new Cart($id_cart);
        if (Order::getOrderByCartId($cart->id)) {
        }
    }

    public function valdiationWithoutURL()
    {

        //Load Cart Id from POST content
        //if the order creation is set, it means they are sending and getting back the order_id instead of cart_id (Albaran)
        if (!Configuration::get('CETELEM_ORDER_CREATION')) {
            if (Tools::getValue('IdTransaccion')) {
                $id_cart = Tools::substr(Tools::getValue('IdTransaccion'), 4);
            } else {
                // Si no viene por GET, verificar si viene por POST
                $id_cart = Tools::substr(Tools::getValue('IdTransaccion', false, false), 4);
            }
        } else {
            $id_cart = (int)Tools::substr(Tools::getValue('IdTransaccion', false, false), 4);

        }
        $cart = new Cart($id_cart);

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - Cart not completed',
                1,
                null,
                'Transaction Id Cart',
                (int)$cart->id,
                true
            );
        }
        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        /*$authorized = false;f
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'cetelem') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - This payment method is not available',
                1,
                null,
                'Transaction Id Cart',
                (int)$cart->id,
                true
            );
        }*/

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - Customer Object Not Valid',
                1,
                null,
                'Transaction Id Cart',
                (int)$cart->id,
                true
            );
        }

        if (Tools::getValue('IdTransaccion')) {
            $filename = _PS_MODULE_DIR_ . '/cetelem/tmp/transaction' . Tools::getValue('IdTransaccion');
        } else {
            // Si no viene por GET, verificar si viene por POST
            $filename = _PS_MODULE_DIR_ . '/cetelem/tmp/transaction' . Tools::substr(Tools::getValue('IdTransaccion', false, false), 4);
        }


        if (file_exists($filename)) {


            if ($string = Tools::file_get_contents($filename)) {

                $arr = explode('|', $string);
                $IdTransaccion = $arr[0];
                //$NSolicitud = $arr[1];
                $CodResultado = $arr[2];

                if (empty($CodResultado) and empty($IdTransaccion)) {
                    PrestaShopLogger::addLog(
                        'Cetelem::CallBack - Transaction file without Id Transaction or Code Result',
                        1,
                        null,
                        'Transaction Id Cart',
                        (int)$cart->id,
                        true
                    );
                }

                //$total = (float) $cart->getOrderTotal(true, Cart::BOTH);
                //$mailVars = array();
                $id_order = Order::getOrderByCartId($cart->id);
                $order = new Order($id_order);

                //we made a quicker checkl to the current status by accessing to the database, due an error caused by 2 quick tries of changing the order state, for example from approved to pre-approved, wich never could be changed to
                $current_statuses = array(); //status in order and in order_history
                /* $sql = 'SELECT current_state
                               FROM `' . _DB_PREFIX_ . 'orders`
                               WHERE `id_order` = "' . (int) $id_order . '"
                               ' . Shop::addSqlRestriction();*/
                //$current_status = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

                $sql1 = 'SELECT current_state
                      FROM `' . _DB_PREFIX_ . 'orders`
                      WHERE `id_order` = "' . (int)$id_order . '" 
                      ' . Shop::addSqlRestriction();
                $current_status_history = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql1);

                if ($current_status_history) {
                    $current_statuses[] = $current_status_history;
                }
                if (in_array(Configuration::getGlobalValue('PS_OS_CETELEM_APPROVED'), $current_statuses)) {
                    die('Already approved, can not be changed to pre aproved now');
                }

                $templateVars = array();

                if ($CodResultado == '00') {

                    /* Change order status, add a new entry in order history and send an e-mail to the customer if needed */
                    $order_state = new OrderState(Configuration::getGlobalValue('PS_OS_CETELEM_PREAPPROVED'));
                    
                    if (!Validate::isLoadedObject($order_state)) {
                        PrestaShopLogger::addLog(
                            'Cetelem::CallBack - The new order status is invalid.',
                            1,
                            null,
                            'Transaction Id Cart',
                            (int)$cart->id,
                            true
                        );
                    } else {
                        $current_order_state = $order->getCurrentOrderState();
      
                        if (Configuration::getGlobalValue('PS_OS_CETELEM_APPROVED') != (int)$current_order_state->id) {

                            if ($current_order_state->id != $order_state->id) {
                                // Create new OrderHistory
                                $history = new OrderHistory();
                                $history->id_order = $order->id;

                                $use_existings_payment = false;
                                if (!$order->hasInvoice()) {
                                    $use_existings_payment = true;
                                }
                                $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);

                                // Save all changes
                                if ($history->addWithemail(true, $templateVars)) {
                                    // synchronizes quantities if needed..
                                    if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                        foreach ($order->getProducts() as $product) {
                                            if (StockAvailable::dependsOnStock($product['product_id'])) {

                                                StockAvailable::synchronize(
                                                    $product['product_id'],
                                                    (int)$product['id_shop']
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $this->sendStatus( 1, $order->id );
                    }
                } elseif ($CodResultado == '50') {

                    /* Change order status, add a new entry in order history and send an e-mail to the customer if needed */
                    $order_state = new OrderState(Configuration::getGlobalValue('PS_OS_CETELEM_APPROVED'));
                    

                    if (!Validate::isLoadedObject($order_state)) {
                        PrestaShopLogger::addLog(
                            'Cetelem::CallBack - The new order status is invalid.',
                            1,
                            null,
                            'Transaction Id Cart',
                            (int)$cart->id,
                            true
                        );
                    } else {
                        $current_order_state = $order->getCurrentOrderState();

                    

                        if ($current_order_state->id != $order_state->id) {
                            // Create new OrderHistory
                            $history = new OrderHistory();
                            $history->id_order = $order->id;

                            $use_existings_payment = false;
                            if (!$order->hasInvoice()) {
                                $use_existings_payment = true;
                            }
                            $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);

                            // Save all changes
                            if ($history->addWithemail(true, $templateVars)) {
                                // synchronizes quantities if needed..
                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                    foreach ($order->getProducts() as $product) {
                                        if (StockAvailable::dependsOnStock($product['product_id'])) {
                                            StockAvailable::synchronize(
                                                $product['product_id'],
                                                (int)$product['id_shop']
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        $this->sendStatus( 1, $order->id );                  

                    }
                } elseif ($CodResultado == '99' || $CodResultado == '51') {
                    $order_state = new OrderState(Configuration::getGlobalValue('PS_OS_CETELEM_DENIED'));

                    if (!Validate::isLoadedObject($order_state)) {
                        PrestaShopLogger::addLog(
                            'Cetelem::CallBack - The new order status is invalid.',
                            1,
                            null,
                            'Transaction Id Cart',
                            (int)$cart->id,
                            true
                        );
                    } else if($order->module=="cetelem") {
                        $current_order_state = $order->getCurrentOrderState();
                        if ($current_order_state->id != $order_state->id) {
                            // Create new OrderHistory
                            $history = new OrderHistory();
                            $history->id_order = $order->id;

                            $use_existings_payment = false;
                            if (!$order->hasInvoice()) {
                                $use_existings_payment = true;
                            }
                            $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);
                            /* To restore the quantity of each product and / or combination */
                            // this function is already exists in hookActionOrderStatusPostUpdate

                            /* if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                   $order_details = $order->getProductsDetail();
                                   $moduleCetelem = Module::getInstanceByName('cetelem');
                                   foreach ($order_details as $order_detail) {
                                     $temp_ord_detail = new OrderDetail($order_detail['id_order_detail']);
                                     $moduleCetelem->setQuantityReinjection($temp_ord_detail, $temp_ord_detail->product_quantity);
                                   }
                                } */

                            // Save all changes
                            if ($history->addWithemail(true, $templateVars)) {
                                // synchronizes quantities if needed..
                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                    foreach ($order->getProducts() as $product) {
                                        if (StockAvailable::dependsOnStock($product['product_id'])) {
                                            StockAvailable::synchronize(
                                                $product['product_id'],
                                                (int)$product['id_shop']
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $this->sendStatus( 7, null );

                } else {
                    //! el default aprueba el pedido??? revisar, no deberia
                    /* Change order status, add a new entry in order history and send an e-mail to the customer if needed */
                    $order_state = new OrderState(Configuration::getGlobalValue('PS_OS_CETELEM_PREAPPROVED'));

                    if (!Validate::isLoadedObject($order_state)) {
                        PrestaShopLogger::addLog(
                            'Cetelem::CallBack - The new order status is invalid.',
                            1,
                            null,
                            'Transaction Id Cart',
                            (int)$cart->id,
                            true
                        );
                    } else {
                        $current_order_state = $order->getCurrentOrderState();
                        if ($current_order_state->id != $order_state->id) {
                            // Create new OrderHistory
                            $history = new OrderHistory();
                            $history->id_order = $order->id;

                            $use_existings_payment = false;
                            if (!$order->hasInvoice()) {
                                $use_existings_payment = true;
                            }
                            $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);

                            // Save all changes
                            if ($history->addWithemail(true, $templateVars)) {
                                // synchronizes quantities if needed..
                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                    foreach ($order->getProducts() as $product) {
                                        if (StockAvailable::dependsOnStock($product['product_id'])) {
                                            StockAvailable::synchronize(
                                                $product['product_id'],
                                                (int)$product['id_shop']
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - File from Cetelem Transaction not exists.',
                1,
                null,
                'Transaction Id Cart',
                (int)$cart->id,
                true
            );
        }
    }

    public function createWithoutURL()
    {

        //Load Cart Id from POST content
        //$id_cart = Order::getCartIdStatic((int)Tools::getValue('IdTransaccion'));
        $id_cart = (int)Tools::substr(Tools::getValue('IdTransaccion'), 4);
        $cart = new Cart($id_cart);

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - Cart not completed',
                1,
                null,
                'Cart',
                (int)$cart->id,
                true
            );
        }
        
        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        /*$authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'cetelem') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - This payment method is not available',
                1,
                null,
                'Transaction Id Cart',
                (int)$cart->id,
                true
            );
        }*/

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - Customer Object Not Valid',
                1,
                null,
                'Cart',
                (int)$cart->id,
                true
            );
        }


        $currency = $this->context->currency;

        $filename = _PS_MODULE_DIR_ . '/cetelem/tmp/transaction' . Tools::getValue('IdTransaccion');

        if (file_exists($filename)) {
            if ($string = Tools::file_get_contents($filename)) {
                $arr = explode('|', $string);
                $IdTransaccion = $arr[0];
                //$NSolicitud = $arr[1];
                $CodResultado = $arr[2];

                if (empty($CodResultado) and empty($IdTransaccion)) {
                    PrestaShopLogger::addLog(
                        'Cetelem::CallBack - Transaction file without Id Transaction or Code Result',
                        1,
                        null,
                        'Transaction Id Cart',
                        (int)$cart->id,
                        true
                    );
                }

                $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
                $mailVars = array();
                
                /* if ($CodResultado == '00') {
				        		sleep(2);
				        } */

                $id_order = Order::getOrderByCartId($cart->id);
							 if($id_order)
					        	return;
                $order = new Order($id_order);
                $c_order_state = $order->getCurrentOrderState();
                $moduleName = $this->module->name;
                if(Tools::getValue('encuotas')){
                    $moduleName = 'encuotas';
                }
                if ($CodResultado == '00') {
                   
                    if (Configuration::getGlobalValue('PS_OS_CETELEM_APPROVED') != (int)$c_order_state->id) {
                         PrestaShopLogger::addLog('Validamos pedido Cetelem 00', 1, null, 'Cart', (int) $cart->id, true); 
                        $this->module->validateOrder(
                            $cart->id,
                            Configuration::getGlobalValue('PS_OS_CETELEM_PREAPPROVED'),
                            $total,
                            $moduleName,
                            '',
                            $mailVars,
                            (int)$currency->id,
                            false,
                            $customer->secure_key
                        );
                        $this->valdiationWithoutURL();
                    }
                } elseif ($CodResultado == '50') {
                   /*  sleep(7); */

                    /* Change order status, add a new entry in order history and send an e-mail to the customer if needed */
                     PrestaShopLogger::addLog('Validamos pedido Cetelem 50', 1, null, 'Cart', (int) $cart->id, true);
               
                    $this->module->validateOrder(
                        $cart->id,
                        Configuration::getGlobalValue('PS_OS_CETELEM_APPROVED'),
                        $total,
                        $moduleName,
                        '',
                        $mailVars,
                        (int)$currency->id,
                        false,
                        $customer->secure_key
                    );
                } elseif ($CodResultado == '99' || $CodResultado == '51') {
                   // $order_state = new OrderState(Configuration::getGlobalValue('PS_OS_CETELEM_DENIED'));
                     PrestaShopLogger::addLog('Validamos pedido Cetelem 99 - 51', 1, null, 'Cart', (int) $cart->id, true);
                    $this->module->validateOrder(
                        $cart->id,
                        Configuration::getGlobalValue('PS_OS_CETELEM_DENIED'),
                        $total,
                        $moduleName,
                        '',
                        $mailVars,
                        (int)$currency->id,
                        false,
                        $customer->secure_key
                    );

                } else {
                    /* Change order status, add a new entry in order history and send an e-mail to the customer if needed */
                    PrestaShopLogger::addLog('Validamos pedido Cetelem ELSE', 1, null, 'Cart', (int) $cart->id, true);
                    $this->module->validateOrder(
                        $cart->id,
                        Configuration::getGlobalValue('PS_OS_CETELEM_PREAPPROVED'),
                        $total,
                        $moduleName,
                        '',
                        $mailVars,
                        (int)$currency->id,
                        false,
                        $customer->secure_key
                    );
                }
            }
        } else {
            PrestaShopLogger::addLog(
                'Cetelem::CallBack - File from Cetelem Transaction not exists.',
                1,
                null,
                'Transaction Id Cart',
                (int)$cart->id,
                true
            );
        }
    }

    public function sendStatus($statusCode, $orderID)
    {
        if ($statusCode == 1) {
            // Success
            $data = array(
                'statusCode' => $statusCode,
                "statusText" => "OK",
                "orderId" => $orderID,
                "errorData" => null,
            );
        } else if ($statusCode == 7) {
            // Error
            $data = array(
                'statusCode' => $statusCode,
                "statusText" => "ERROR",
                "orderId" => null,
                "errorData" => "Error description",
            );
        }

        $json = json_encode($data);
        header('Content-Type: application/json');
        echo $json;
        exit;
    }
}
