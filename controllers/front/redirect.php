<?php
/**
 * 2007-2017 PrestaShop
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class CetelemRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        /**
         * Oops, an error occured.
         */
        if (Context::getContext()->customer->id <= 0) {
            return $this->displayError('An error occurred while trying to redirect the customer');
        } else {
            //order creation before jumping to Cetelem
            // $this->orderCreation();
            $campaign = Tools::getValue('is_campaign');
            //var_dump($campaign);
            //exit;
            if ($campaign) {
                $this->context->smarty->assign(
                    array(
                        'conex_url' => Tools::getValue('conex_url'),
                        'total' => Tools::getValue('total'),
                        'mode' => Tools::getValue('mode'),
                        'transact_id' => Tools::getValue('transact_id'),
                        'center_code' => Tools::getValue('center_code'),
                        'amount' => Tools::getValue('amount'),
                        'url' => Tools::getValue('url'),
                        'url_ok' => Tools::getValue('url_ok'),
                        'timestamp' => Tools::getValue('timestamp'),
                        'gender' => Tools::getValue('gender'),
                        'firstname' => Tools::getValue('firstname'),
                        'lastname' => Tools::getValue('lastname'),
                        'dni' => Tools::getValue('dni'),
                        'birthday' => Tools::getValue('birthday'),
                        'address' => Tools::getValue('address'),
                        'city' => Tools::getValue('city'),
                        'zip' => Tools::getValue('zip'),
                        'email' => Tools::getValue('email'),
                        'phone1' => Tools::getValue('phone1'),
                        'phone2' => Tools::getValue('phone2'),
                    )
                );
                return $this->setTemplate('module:cetelem/views/templates/front/payment_execution.tpl');
            } else {
                $this->context->smarty->assign(
                    array(
                        'conex_url' => Tools::getValue('conex_url'),
                        'total' => Tools::getValue('total'),
                        'mode' => Tools::getValue('mode'),
                        'transact_id' => Tools::getValue('transact_id'),
                        'center_code' => Tools::getValue('center_code'),
                        'amount' => Tools::getValue('amount'),
                        'url' => Tools::getValue('url'),
                        'url_ok' => Tools::getValue('url_ok'),
                        'timestamp' => Tools::getValue('timestamp'),
                        'gender' => Tools::getValue('gender'),
                        'firstname' => Tools::getValue('firstname'),
                        'lastname' => Tools::getValue('lastname'),
                        'dni' => Tools::getValue('dni'),
                        'birthday' => Tools::getValue('birthday'),
                        'address' => Tools::getValue('address'),
                        'city' => Tools::getValue('city'),
                        'zip' => Tools::getValue('zip'),
                        'email' => Tools::getValue('email'),
                        'phone1' => Tools::getValue('phone1'),
                        'phone2' => Tools::getValue('phone2'),
                    )
                );
                return $this->setTemplate('module:cetelem/views/templates/front/payment_execution.tpl');
            }
            //Tools::getValue($key);
        }
    }

    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();
        return $this->setTemplate('module:cetelem/views/templates/front/redirect.tpl');
    }

    protected function displayError($message, $description = false)
    {
        /**
         * Create the breadcrumb for your ModuleFrontController.
         */
        $this->context->smarty->assign(
            'path',
            '
			<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">' . $this->module->l(
                'Payment'
            ) . '</a>
			<span class="navigation-pipe">&gt;</span>' . $this->module->l('Error')
        );

        /**
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('module:cetelem/views/templates/front/error.tpl');
    }

    protected function orderCreation()
    {
        $cart = $this->context->cart;
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

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $mailVars = array();
        die(
            $this->module->validateOrder(
                $cart->id,
                Configuration::getGlobalValue('PS_OS_CETELEM_STANDBY'),
                $total,
                $this->module->name,
                '',
                $mailVars,
                (int)$currency->id,
                false,
                $customer->secure_key
            )
        );
    }
}
