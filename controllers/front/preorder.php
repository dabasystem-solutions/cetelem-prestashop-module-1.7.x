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

class CetelemPreorderModuleFrontController extends ModuleFrontController
{
    private $cetelemStates; 

    public function __construct()
    {
        parent::__construct();

        require_once _PS_MODULE_DIR_ . 'cetelem/classes/CetelemStates.php';
        $this->cetelemStates = new CetelemStates();
    }

    public function postProcess()
    {
        if (Tools::getValue('ajax')) {
            $cart = $this->context->cart;
            if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 ||
                $cart->id_address_invoice == 0 || !$this->module->active) {
                Tools::redirect('index.php?controller=order&step=1');
            }
            // Check that this payment option is still available in case the customer changed
            // his address just before the end of the checkout process
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

            $currency = $this->context->currency;
            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
            $mailVars = array();
            $this->module->validateOrder(
                $cart->id,
                $this->cetelemStates->StateCetelemStanBy(),
                $total,
                $this->module->name,
                '',
                $mailVars,
                (int)$currency->id,
                false,
                $customer->secure_key
            );
            $albaran = Order::getOrderByCartId((int)$cart->id);
            if ($albaran) {
                header('Content-Type: application/json');
                echo json_encode(['albaran' => $albaran]);
                exit;
                
            } else {
                header('Content-Type: application/json');
                echo json_encode(['albaran' => false]);
                exit;
                
            }
        }
    }
}
