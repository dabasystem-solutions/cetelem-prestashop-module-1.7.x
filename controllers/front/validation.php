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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class CetelemValidationModuleFrontController extends ModuleFrontController
{


    public function postProcess()
    {
        /*if(Configuration::get('CETELEM_ORDER_CREATION')) {
            $id_cart = Order::getCartIdStatic((int)Tools::getValue('IdTransaccion'));
            /*var_dump($id_cart);
            die();*/
        /*$cart = new Cart((int)$id_cart);
    } else {*/
        $id_cart = Tools::substr($this->context->cookie->cetelem_transact_id, 4);
        $cart = new Cart($id_cart);
        //}

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'cetelem') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        //$currency = $this->context->currency;

        if (Configuration::get('CETELEM_ORDER_CREATION')) {
            $id_order = Order::getOrderByCartId($cart->id);
            $filename = _PS_MODULE_DIR_ . '/cetelem/tmp/transaction' . $id_order;
        } else {
            $filename = _PS_MODULE_DIR_ . '/cetelem/tmp/transaction' . $this->context->cookie->cetelem_transact_id;
        }

        if (file_exists($filename)) {
            if ($string = Tools::file_get_contents($filename)) {
                $arr = explode('|', $string);
                $IdTransaccion = $arr[0];
                // $NSolicitud = $arr[1];
                $CodResultado = $arr[2];

                if (empty($CodResultado) and empty($IdTransaccion)) {
                    Tools::redirect('index.php?controller=order');
                }

                // $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
                // $mailVars = array();

                $id_order = Order::getOrderByCartId($cart->id);
                // $order = new Order($id_order);

                // $templateVars = array();

                if ($CodResultado == '00') {
                    /* Change order status, add a new entry in order history and send an e-mail to the customer if needed */
//                    $order_state = new OrderState(Configuration::get('PS_OS_CETELEM_PREAPPROVED'));
//
//                    if (!Validate::isLoadedObject($order_state)) {
//                        echo Tools::displayError('The new order status is invalid.');
//                    } else {
//                        $current_order_state = $order->getCurrentOrderState();
//                        if ($current_order_state->id != $order_state->id) {
//                            // Create new OrderHistory
//                            $history = new OrderHistory();
//                            $history->id_order = $order->id;
//
//                            $use_existings_payment = false;
//                            if (!$order->hasInvoice()) {
//                                $use_existings_payment = true;
//                            }
//                            $history->changeIdOrderState((int) $order_state->id, $order, $use_existings_payment);
//
//                            // Save all changes
//                            if ($history->addWithemail(true, $templateVars)) {
//                                // synchronizes quantities if needed..
//                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
//                                    foreach ($order->getProducts() as $product) {
//                                        if (StockAvailable::dependsOnStock($product['product_id'])) {
//                                            StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
                    Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key
                    );
                } elseif ($CodResultado == '50') {
                    /* Change order status, add a new entry in order history and send an e-mail to the customer if needed */
//                    $order_state = new OrderState(Configuration::get('PS_OS_CETELEM_APPROVED'));
//
//                    if (!Validate::isLoadedObject($order_state)) {
//                        echo Tools::displayError('The new order status is invalid.');
//                    } else {
//                        $current_order_state = $order->getCurrentOrderState();
//                        if ($current_order_state->id != $order_state->id) {
//                            // Create new OrderHistory
//                            $history = new OrderHistory();
//                            $history->id_order = $order->id;
//
//                            $use_existings_payment = false;
//                            if (!$order->hasInvoice()) {
//                                $use_existings_payment = true;
//                            }
//                            $history->changeIdOrderState((int) $order_state->id, $order, $use_existings_payment);
//
//                            // Save all changes
//                            if ($history->addWithemail(true, $templateVars)) {
//                                // synchronizes quantities if needed..
//                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
//                                    foreach ($order->getProducts() as $product) {
//                                        if (StockAvailable::dependsOnStock($product['product_id'])) {
//                                            StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
                    Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key
                    );
                } elseif ($CodResultado == '99' || $CodResultado == '51') {
//                    $order_state = new OrderState(Configuration::get('PS_OS_CETELEM_DENIED'));
//
//                    if (!Validate::isLoadedObject($order_state)) {
//                        echo Tools::displayError('The new order status is invalid.');
//                    } else {
//                        $current_order_state = $order->getCurrentOrderState();
//                        if ($current_order_state->id != $order_state->id) {
//                            // Create new OrderHistory
//                            $history = new OrderHistory();
//                            $history->id_order = $order->id;
//
//                            $use_existings_payment = false;
//                            if (!$order->hasInvoice()) {
//                                $use_existings_payment = true;
//                            }
//                            $history->changeIdOrderState((int) $order_state->id, $order, $use_existings_payment);
//
//                            // Save all changes
//                            if ($history->addWithemail(true, $templateVars)) {
//                                // synchronizes quantities if needed..
//                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
//                                    foreach ($order->getProducts() as $product) {
//                                        if (StockAvailable::dependsOnStock($product['product_id'])) {
//                                            StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
                    $this->display_column_left = false;
                    $this->setTemplate('module:cetelem/views/templates/front/denied.tpl');
                } else {
                    /* Change order status, add a new entry in order history and send an e-mail to the customer if needed */
//                    $order_state = new OrderState(Configuration::get('PS_OS_CETELEM_PREAPPROVED'));
//
//                    if (!Validate::isLoadedObject($order_state)) {
//                        echo Tools::displayError('The new order status is invalid.');
//                    } else {
//                        $current_order_state = $order->getCurrentOrderState();
//                        if ($current_order_state->id != $order_state->id) {
//                            // Create new OrderHistory
//                            $history = new OrderHistory();
//                            $history->id_order = $order->id;
//
//                            $use_existings_payment = false;
//                            if (!$order->hasInvoice()) {
//                                $use_existings_payment = true;
//                            }
//                            $history->changeIdOrderState((int) $order_state->id, $order, $use_existings_payment);
//
//                            // Save all changes
//                            if ($history->addWithemail(true, $templateVars)) {
//                                // synchronizes quantities if needed..
//                                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
//                                    foreach ($order->getProducts() as $product) {
//                                        if (StockAvailable::dependsOnStock($product['product_id'])) {
//                                            StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
                    Tools::redirect(
                        'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&no_order_state=1&key=' . $customer->secure_key
                    );
                }
            }
        } else {
            Tools::redirect('index.php?controller=order');
        }
    }
}
