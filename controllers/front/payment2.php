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
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to https://www.prestashop.com for more information.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CetelempaymentPayment2ModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;
    public $display_column_right = false;
    private $cetelemStates;

    public function __construct()
    {
        parent::__construct();
        require_once _PS_MODULE_DIR_ . 'cetelempayment/classes/CetelemStates.php';
        $this->cetelemStates = new CetelemStates();
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/front.css');
    }

    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        $isCetelemMoto = (Configuration::get('CETELEM_MOTO') === '1') ? true : false;

        $conexion = $isCetelemMoto
            ? (Configuration::get('CETELEM_ENV') ? CetelemPayment::CETELEM_URL_MOTO_CONNECTION : CetelemPayment::CETELEM_URL_TEST_MOTO_CONNECTION)
            : (Configuration::get('CETELEM_ENV') ? CetelemPayment::CETELEM_URL_CONNECTION : CetelemPayment::CETELEM_URL_TEST_CONNECTION);

        // Verificar clave de seguridad del usuario
        $securekey = Tools::getValue('securekey');
        if (!$securekey || $securekey != $this->context->customer->secure_key) {
            exit;
        }

        // Generación del ID de transacción
        $ano = date('Y');
        $ano = Tools::substr($ano, Tools::strlen($ano) - 1, 1);
        $ano1 = date('Y');
        $mes1 = 1;
        $dia1 = 1;
        $ano2 = date('Y');
        $mes2 = date('n');
        $dia2 = date('j');
        $timestamp1 = mktime(0, 0, 0, $mes1, $dia1, $ano1);
        $timestamp2 = mktime(4, 12, 0, $mes2, $dia2, $ano2);
        $segundos_diferencia = $timestamp1 - $timestamp2;
        $dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
        $dias_diferencia = abs($dias_diferencia);
        $day_julian = floor($dias_diferencia) + 1;

        $transact_id = $ano . str_pad($day_julian, 3, '0', STR_PAD_LEFT) . str_pad($cart->id, 8, '0', STR_PAD_LEFT);
        $this->context->cookie->__set('cetelem_transact_id', $transact_id);

        $amount = str_replace('.', '', number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
        $address = new Address($cart->id_address_invoice);
        $customer = new Customer($cart->id_customer);

        $addressText = preg_replace('/[0-9]+/', '', $address->address1 ?? '');
        $addressText = str_replace(['\\', '/', ',', '.', 'º', 'ª'], ' ', $addressText);

        $gender = ($customer->id_gender == 1) ? 'SR' : 'SRA';
        $birthday = ($customer->birthday != '0000-00-00') ? date('d/m/Y', strtotime($customer->birthday)) : '';




        $cetelem_module = Module::getInstanceByName('cetelempayment');

        $calc_type = $cetelem_module->getCalcTypeScript(Configuration::get('CETELEM_CALC_TYPE'));

        $server_url_cetelem = $cetelem_module::CETELEM_URL_SCRIPT;

        $center_code = Configuration::get('CETELEM_CLIENT_ID');

        $cetelem_cart = Context::getContext()->cart;

        $total_price = $cetelem_cart->getOrderTotal();

        $this->context->smarty->assign(
            array(
                'center_code' => $center_code,
                'total_price' => $total_price,
                'server_url_cetelem' => $server_url_cetelem,
                'color' => Configuration::get('CETELEM_TEXT_COLOR'),
                'bloquearImporte' => Configuration::get('CETELEM_AMOUNT_BLOCK'),
                'fontSize' => Configuration::get('FONT_SIZE_CETELEM'),
                'calc_type' => $calc_type
            )
        );

        if (Tools::getValue('encuotas')) {
            $conexion = (Configuration::get('CETELEM_ENV')) ? CetelemPayment::CETELEM_URL_NEWCONNECTION : CetelemPayment::CETELEM_URL_TEST_NEWCONNECTION;
        }

        // Variables específicas de moto
        $bikebrandcode = '';
        $bikematerial = '499';
        $registrationdate = '';

        if ($isCetelemMoto) {
            $products = $cart->getProducts();
            $productFeatures = [];

            foreach ($products as $product) {
                $features = Product::getFeaturesStatic((int)$product['id_product']);
                foreach ($features as $feature) {
                    $featureName = Feature::getFeature((int)$this->context->language->id, (int)$feature['id_feature']);
                    $featureValue = FeatureValue::getFeatureValueLang((int)$feature['id_feature_value']);

                    if ($featureName && isset($featureValue[0]['value'])) {
                        $productFeatures[$featureName['name']] = $featureValue[0]['value'];
                    }
                }
            }

            $bikebrandcode = $productFeatures['bikebrandcode'] ?? '';
            $bikematerial = $productFeatures['bikematerial'] ?? '499';
            $registrationdate = $productFeatures['registrationdate'] ?? '';
        }
        $mode = Configuration::get('CETELEM_MODE') ?? 'N'; // Use 'N' as fallback

        $formArray = [
            'isCetelemMoto' => $isCetelemMoto,
            'conex_url' => $conexion,
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'transact_id' => $transact_id,
            'center_code' => Configuration::get('CETELEM_CLIENT_ID') ?? '',
            'amount' => $amount,
            'url' => $this->context->link->getModuleLink('cetelempayment', 'validation'),
            'url_ok' => $this->context->link->getModuleLink('cetelempayment', 'callback'),
            'timestamp' => time(),
            'gender' => $gender,
            'firstname' => $address->firstname ?? '',
            'lastname' => $address->lastname ?? '',
            'dni' => $address->dni ?? '',
            'birthday' => $birthday,
            'address' => $addressText,
            'city' => $address->city ?? '',
            'CodigoPostalEnvio' => $address->postcode ?? '',
            'email' => $customer->email ?? '',
            'phone1' => $address->phone_mobile ?? '',
            'phone2' => $address->phone ?? '',
            'name_payment' => Configuration::get('CETELEM_LEGAL_NOM_PAGO') ?? '',
            'text_payment' => Configuration::get('CETELEM_LEGAL_CHECKOUT') ?? '',
            'orderConfirmed' => 0,
            'material' => $bikematerial,
            'bikebrandcode' => $bikebrandcode,
            'bikematerial' => $bikematerial,
            'registrationdate' => $registrationdate
        ];
        $formArray['mode'] = $mode;
        $this->context->smarty->assign($formArray);
        $this->context->smarty->assign([
            'this_path' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/'
        ]);

        $albaran = Order::getOrderByCartId((int)$cart->id);
        if (Configuration::get('CETELEM_ORDER_CREATION')) {
            if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
                Tools::redirect('index.php?controller=order&step=1');
            }

            $authorized = false;
            foreach (Module::getPaymentModules() as $module) {
                if ($module['name'] == 'cetelempayment') {
                    $authorized = true;
                    break;
                }
            }
            if (!$authorized) {
                die($this->module->l('This payment method is not available.', 'validation'));
            }

            $currency = $this->context->currency;
            $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
            $mailVars = [];

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
        } else {
            Context::getContext()->cookie->id_cart = '';
        }

        $this->context->smarty->assign(['albaran' => $albaran]);
        $this->saveTransactionToDatabase($transact_id, null, $cetelem_cart->id);
        return $this->setTemplate('module:cetelempayment/views/templates/front/payment_execution.tpl');
    }
    private function saveTransactionToDatabase($idTransaccion, $codResultado, $id_cart)
    {
        $data = [
            'id_cart' => (int)$id_cart,
            'transaction_id' => pSQL($idTransaccion),
            'result_code' => pSQL($codResultado),
            'date_add' => date('Y-m-d H:i:s')
        ];

        $result = Db::getInstance()->insert('cetelem_transactions', $data);

        if (!$result) {

            $this->writeToLog("Failed to save transaction in DB: $idTransaccion");

            PrestaShopLogger::addLog(
                'Cetelem::CallBack - DB Insert Error',
                3,
                null,
                'Transaction',
                null,
                true
            );
        }
    }
}
