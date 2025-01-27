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
/**
 * @since 1.5.0
 */



class CetelemPayment2ModuleFrontController extends ModuleFrontController
{

    

    public $ssl = true;
    public $display_column_left = false;
    public $display_column_right = false;
    private $cetelemStates; 

    public function __construct()
    {
        parent::__construct();

        require_once _PS_MODULE_DIR_ . 'cetelem/classes/CetelemStates.php';
        $this->cetelemStates = new CetelemStates();
    }
    public function setMedia()
    {
        parent::setMedia();
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/front.css');
        } else {
            $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/front15.css');
        }
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        // $id_language = Tools::strtoupper($this->context->language->iso_code);

        $cart = $this->context->cart;

        $isCetelemMoto = (Configuration::get('CETELEM_MOTO') === '1') ? true : false;

        if ($isCetelemMoto) {     
            $conexion = (Configuration::get('CETELEM_ENV')) ? Cetelem::CETELEM_URL_MOTO_CONNECTION : Cetelem::CETELEM_URL_TEST_MOTO_CONNECTION;
       
            //? Miramos todos los pedidos en busca de productos moto y los metemos en un nuevo array dentro del producto
            $products = $cart->getProducts();
 
            $productsWithFeatures = [];
            // Recorrer los productos y obtener sus características
            foreach  ($products as $product) {
                // Obtener las características del producto
                $features = Product::getFeaturesStatic((int)$product['id_product']);
                
                // Inicializar un array para almacenar la información detallada de las características
                $detailedFeatures = [];
        
                // Recorrer las características y obtener información detallada
                foreach ($features as $feature) {
                    $featureName = Feature::getFeature((int)$this->context->language->id, (int)$feature['id_feature']);
                    $featureValue = FeatureValue::getFeatureValueLang((int)$feature['id_feature_value']);
                    
                    $detailedFeatures[$featureName['name']] = $featureValue[0]['value'];
                }
        
                // Añadir las características detalladas al producto
                $product['features'] = $detailedFeatures;
        
                // Añadir el producto al array de productos con características
                $productsWithFeatures[] = $product;
            }

            $product['features']['isCetelemMoto'] = "true";
        } else {
            $conexion = (Configuration::get('CETELEM_ENV')) ? Cetelem::CETELEM_URL_CONNECTION : Cetelem::CETELEM_URL_TEST_CONNECTION;
        }

        // Codigo para checkear que es el cliente quien valida el pedido
        $securekey = Tools::getValue('securekey');
        if (!isset($securekey) || $securekey != $this->context->customer->secure_key)
            exit;

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

        $transact_id = $ano . str_pad($day_julian, 3, '0', STR_PAD_LEFT) . str_pad($cart->id, 6, '0', STR_PAD_LEFT);
        $this->context->cookie->__set('cetelem_transact_id', $transact_id);
        $amount = str_replace('.', '', number_format($cart->getOrderTotal(true, 3), 2, '.', ''));


        $address = new Address($this->context->cart->id_address_invoice);
        //$loaded_address = Validate::isLoadedObject($address);

        $addressWithoutNum = preg_replace('/[0-9]+/', '', $address->address1);

        $addressText = str_replace('\\', ' ', $addressWithoutNum);
        $addressText = str_replace('/', ' ', $addressText);
        $addressText = str_replace(',', ' ', $addressText);
        $addressText = str_replace('.', ' ', $addressText);
        $addressText = str_replace('º', ' ', $addressText);
        $addressText = str_replace('ª', ' ', $addressText);

        //Customer data for Cetelem application
        $customer = new Customer($this->context->customer->id);
        //$loaded_customer = Validate::isLoadedObject($customer);
        $gender = $this->context->customer->id_gender;
        if ($gender == 1) {
            $gender = 'SR';
        } else {
            $gender = 'SRA';
        }

        if (isset($this->context->customer->birthday) && $this->context->customer->birthday !== '0000-00-00') {
            $birthday = date('d/m/Y', strtotime($this->context->customer->birthday));
        }
        
        //new calculator

        $cetelem_module = Module::getInstanceByName('cetelem');

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
            $conexion = (Configuration::get('CETELEM_ENV')) ? Cetelem::CETELEM_URL_NEWCONNECTION : Cetelem::CETELEM_URL_TEST_NEWCONNECTION;
        }

        $formArray = array(
            'conex_url' => $conexion,
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'transact_id' => $transact_id,
            'center_code' => Configuration::get('CETELEM_CLIENT_ID'),
            'amount' => $amount,
            'url' => $this->context->link->getModuleLink('cetelem', 'validation'),
            'url_ok' => $this->context->link->getModuleLink('cetelem', 'callback'),
            'timestamp' => $timestamp1,
            'gender' => $gender,
            'firstname' => $address->firstname,
            'lastname' => $address->lastname,
            'dni' => $address->dni,
            'birthday' => $birthday,
            'address' => $addressText,
            'city' => $address->city,
            'CodigoPostalEnvio' => $address->postcode,
            'email' => $this->context->customer->email,
            'phone1' => $address->phone,
            'phone2' => $address->phone_mobile,
            'name_payment' => Configuration::get('CETELEM_LEGAL_NOM_PAGO'),
            'text_payment' => Configuration::get('CETELEM_LEGAL_CHECKOUT'),
            'orderConfirmed' => 0,
            'material' => '499'
        );

        if ($isCetelemMoto) {
            //? Si es moto, cambiamos el codigo material, pero también revisamos si es boutique o moto, ya que cambian.
            /*
            499 Boutique
            451 Moto nueva
            452 Moto segunda mano 
            */
            $formArray = array_merge($formArray, $product['features']);
            $formArray['material'] = (empty($product['features']['bikematerial'])) ? '499' : $product['features']['bikematerial'];
        }

        //? Print parameters
        /* echo '<pre>'; print_r($formArray); echo '</pre>';
        die(); */

        $this->context->smarty->assign(
            $formArray
        );

        $this->context->smarty->assign(
            array(
                'this_path' => $this->module->getPathUri(),
                'this_path_bw' => $this->module->getPathUri(),
                'this_path_ssl' => Tools::getShopDomainSsl(
                    true,
                    true
                ) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/'
            )
        );

        $albaran = false;

        //$confirm_redirect = false;
        $cart = $this->context->cart;

        $albaran = Order::getOrderByCartId((int)$cart->id);

        if (Configuration::get('CETELEM_ORDER_CREATION')/* && Tools::getValue('orderConfirmed')*/) {
            //$confirm_redirect = true;
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



        $this->context->smarty->assign(
            array(
                'albaran' => $albaran
            )
        );

        //$this->setTemplate('payment_execution.tpl');
        return $this->setTemplate('module:cetelem/views/templates/front/payment_execution.tpl');
    }
}
