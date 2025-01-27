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
class CetelemValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $id_cart = Tools::substr($this->context->cookie->cetelem_transact_id, 4);
        $cart = new Cart($id_cart);

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

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

        $transaction = Db::getInstance()->getRow('
            SELECT * 
            FROM `' . _DB_PREFIX_ . 'cetelem_transactions` 
            WHERE `id_cart` = ' . (int)$cart->id
        );

        if ($transaction) {
            $IdTransaccion = $transaction['transaction_id'];
            $CodResultado = $transaction['result_code'];

            if (empty($CodResultado) || empty($IdTransaccion)) {
                Tools::redirect('index.php?controller=order');
            }

            if ($CodResultado == '00' || $CodResultado == '50') {
                Tools::redirect(
                    'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&key=' . $customer->secure_key
                );
            } elseif ($CodResultado == '99' || $CodResultado == '51') {
                $this->display_column_left = false;
                $this->setTemplate('module:cetelem/views/templates/front/denied.tpl');
            } else {
                Tools::redirect(
                    'index.php?controller=order-confirmation&id_cart=' . $cart->id . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder . '&no_order_state=1&key=' . $customer->secure_key
                );
            }
        } else {
            Tools::redirect('index.php?controller=order');
        }
    }
}
