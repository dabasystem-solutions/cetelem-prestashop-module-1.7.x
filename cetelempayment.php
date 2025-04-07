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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CetelemPayment extends PaymentModule
{
    protected $output = '';

    const CETELEM_URL_TEST_CONNECTION = 'https://test.cetelem.es/eCommerceLite/configuracion.htm';
    const CETELEM_URL_CONNECTION = 'https://www.cetelem.es/eCommerceLite/configuracion.htm';
    /* MOTO */
    const CETELEM_URL_TEST_MOTO_CONNECTION = 'https://test.cetelem.es/eCommerceLite/moto/configuracion.htm';
    const CETELEM_URL_MOTO_CONNECTION = 'https://www.cetelem.es/eCommerceLite/moto/configuracion.htm';

    const CETELEM_URL_NEWCONNECTION = 'https://www.cetelem.es/eCommerceLite/enCuotas/configuracion.htm';
    const CETELEM_URL_TEST_NEWCONNECTION = 'https://test.cetelem.es/eCommerceLite/enCuotas/configuracion.htm';

    const CETELEM_URL_SCRIPT = 'https://www.cetelem.es';
    const CETELEM_URL_TEST_SCRIPT = 'https://test.cetelem.es';
    const CETELEM_URL_CMS = 'https://www.cetelem.es/contenidos/ecommerce/landing-text.js';
    const CETELEM_URL_CMS_PLAIN = 'https://www.cetelem.es/contenidos/ecommerce/info-plain.js?partner=';
    const URL_CSV_TIN_TAE = 'http://ecreditnow.es/CSV/partners/';
    const URL_CSV_CALC_LEGAL = 'https://www.cetelem.es/eCommerceCalculadora/cetelem/legales.htm?codCentro=';
    const URL_CSV_CAMPAIGN = 'http://ecreditnow.es/CSV/campaigns/';
    const CETELEM_CSV_NORMAL_TIN_INDEX = 1;
    const CETELEM_CSV_NORMAL_TAE_INDEX = 0;
    const CETELEM_CSV_NORMAL_FECHA_VALIDEZ = 2;
    const CETELEM_CSV_NORMAL_MESES = 3;
    const CETELEM_CSV_NORMAL_TXT = 4;
    private $showEnquotas = false;
    private $showCetelem = false;
    private $cetelemStates;

    public function __construct()
    {
        $this->name = 'cetelempayment';
        $this->tab = 'payments_gateways';
        $this->version = '17.6.2';
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '8.2.0'];
        $this->author = 'Dabasystem solutions - https://www.dabasystem.com/';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '7799a4b29a111619f24530986eef6df8';
        


        parent::__construct();
        require_once __DIR__ . '/classes/CetelemStates.php';
        $this->cetelemStates = new CetelemStates();
        $this->displayName = $this->l('Cetelem');
        $this->description = $this->l('Customers can pay with a credit.');
        $this->showEnquotas = Configuration::get('CETELEM_SHOWENCUOTAS');
        $this->showCetelem = Configuration::get('CETELEM_SHOWCETELEM');

    }

    public function install()
    {
        Configuration::updateValue('CETELEM_ENV', 0);

        Configuration::updateValue('CETELEM_ORDER_CREATION', 0);
        Configuration::updateValue('CETELEM_SPEC_MATERIAL', 0);
        Configuration::updateValue('CETELEM_CLIENT_ID', '');
        Configuration::updateValue('CETELEM_CALC_TYPE', 0);
        Configuration::updateValue('CETELEM_CALC_SHOW', 0);
        Configuration::updateValue('CETELEM_CALC_POSITION', 1);
        Configuration::updateValue('CETELEM_MIN_AMOUNT', 90);
        Configuration::updateValue('CETELEM_MIN_ENQUOTAS', 36);
        Configuration::updateValue('CETELEM_DISPLAY_CALC', 0);
        Configuration::updateValue('CETELEM_LEGAL_NOM_PAGO', '');
        Configuration::updateValue('CETELEM_LEGAL_CHECKOUT', '');
        Configuration::updateValue('CETELEM_IPS', '213.170.60.39');
        Configuration::updateValue('CETELEM_CALLBACK_IP_RES', 1);
        Configuration::updateValue('CETELEM_SHOWCETELEM', 1);
        Configuration::updateValue('CETELEM_SHOWENCUOTAS', 1);
        Configuration::updateValue('CETELEM_AMOUNT_BLOCK', 0);
        Configuration::updateValue('FONT_SIZE_CETELEM', '12');
        Configuration::updateValue('CETELEM_INFO_CALC_TEXT', '');


        return parent::install() &&
            $this->createTransactionTable() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayReassurance') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('displayPaymentReturn') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('actionOrderStatusUpdate') &&
            $this->registerHook('actionCronJob') &&
            $this->cetelemStates->createCetelemPrepprovedState() &&
            $this->cetelemStates->createCetelemApprovedState() &&
            $this->cetelemStates->createCetelemDeniedState();
    }


    public function uninstall()
    {
        Configuration::deleteByName('CETELEM_CLIENT_ID');
        Configuration::deleteByName('CETELEM_CALC_TYPE');
        Configuration::deleteByName('CETELEM_CALC_SHOW');
        Configuration::deleteByName('CETELEM_CALC_POSITION');
        Configuration::deleteByName('CETELEM_ENV');
        Configuration::deleteByName('CETELEM_ORDER_CREATION');
        Configuration::deleteByName('CETELEM_SPEC_MATERIAL');
        Configuration::deleteByName('CETELEM_MIN_AMOUNT');
        Configuration::deleteByName('CETELEM_DISPLAY_CALC');
        Configuration::deleteByName('CETELEM_TEXT_COLOR');
        Configuration::deleteByName('CETELEM_AMOUNT_BLOCK');
        Configuration::deleteByName('CETELEM_IPS');
        Configuration::deleteByName('FONT_SIZE_CETELEM');
        Configuration::deleteByName('CETELEM_INFO_CALC_TEXT');

        return parent::uninstall();
    }

    public function postProcess()
    {
        $active_connection = true;
        $active_config = false;

        if (Tools::isSubmit('submitCetelemConnectData')) {
            $error = '';

            Configuration::updateValue('CETELEM_CLIENT_ID', Tools::getValue('CETELEM_CLIENT_ID'));
            $url = self::URL_CSV_CALC_LEGAL . Configuration::get('CETELEM_CLIENT_ID');
            $arrContextOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'ciphers' => 'HIGH:!SSLv2:!SSLv3',
                    'disable_compression' => true,
                ]
            ];
            if (filter_var($url, FILTER_VALIDATE_URL)) {
            $content = Tools::file_get_contents($url, 0, stream_context_create($arrContextOptions));
            }
            if ($content) {
                $xml = new SimpleXMLElement($content);
                $nomPago = (string)$xml->nomPago;
                $legalCheckout = (string)$xml->legalCheckout;
                if ($nomPago != '') {
                    Configuration::updateValue('CETELEM_LEGAL_NOM_PAGO', $nomPago);
                }

                if ($legalCheckout != '') {
                    Configuration::updateValue('CETELEM_LEGAL_CHECKOUT', $legalCheckout);
                }
            }

            $this->output .= $this->displayConfirmation($this->l('Connection data updated successfully.'));
        } elseif (Tools::isSubmit('submitCetelemSettings')) {
            $error = '';
            $selected_carriers = Tools::getValue('CETELEM_CARRIERS');

            if (!is_array($selected_carriers)) {
                $selected_carriers = [];
            }
   
            Configuration::updateValue('CETELEM_CARRIERS', json_encode($selected_carriers));
            Configuration::updateValue('CETELEM_MOTO', Tools::getValue('CETELEM_MOTO'));
            Configuration::updateValue('CETELEM_ENV', Tools::getValue('CETELEM_ENV'));
            Configuration::updateValue('CETELEM_ORDER_CREATION', Tools::getValue('CETELEM_ORDER_CREATION'));
            Configuration::updateValue('CETELEM_SPEC_MATERIAL', Tools::getValue('CETELEM_SPEC_MATERIAL'));
            Configuration::updateValue('CETELEM_MIN_AMOUNT', Tools::getValue('CETELEM_MIN_AMOUNT'));
            Configuration::updateValue('CETELEM_DISPLAY_CALC', Tools::getValue('CETELEM_DISPLAY_CALC'));
            Configuration::updateValue('CETELEM_TEXT_COLOR', Tools::getValue('CETELEM_TEXT_COLOR'));
            Configuration::updateValue('CETELEM_AMOUNT_BLOCK', Tools::getValue('CETELEM_AMOUNT_BLOCK'));
            Configuration::updateValue('CETELEM_IPS', Tools::getValue('CETELEM_IPS'));
            Configuration::updateValue('FONT_SIZE_CETELEM', Tools::getValue('FONT_SIZE_CETELEM'));
            Configuration::updateValue('CETELEM_BACKGROUND_COLOR', Tools::getValue('CETELEM_BACKGROUND_COLOR'));
            Configuration::updateValue('CETELEM_BORDER_COLOR', Tools::getValue('CETELEM_BORDER_COLOR'));
            Configuration::updateValue('CETELEM_FEE_COLOR', Tools::getValue('CETELEM_FEE_COLOR'));
            Configuration::updateValue('CETELEM_CALC_TYPE', Tools::getValue('CETELEM_CALC_TYPE'));
            Configuration::updateValue('CETELEM_CALC_SHOW', Tools::getValue('CETELEM_CALC_SHOW'));
            Configuration::updateValue('CETELEM_CALC_POSITION', Tools::getValue('CETELEM_CALC_POSITION'));
            Configuration::updateValue('CETELEM_CALLBACK_IP_RES', Tools::getValue('CETELEM_CALLBACK_IP_RES'));
            Configuration::updateValue('CETELEM_SHOWCETELEM', Tools::getValue('CETELEM_SHOWCETELEM'));
            Configuration::updateValue('CETELEM_SHOWENCUOTAS', Tools::getValue('CETELEM_SHOWENCUOTAS'));
            Configuration::updateValue('CETELEM_PRODUCTS', Tools::getValue('CETELEM_PRODUCTS'));
            
            $cetelemInfo = array();
            foreach (Language::getLanguages() as $lang) {
                $cetelemInfo[$lang['id_lang']] = htmlspecialchars(
                    Tools::getValue('CETELEM_INFO_CALC_TEXT_' . $lang['id_lang'])
                );
            }



            Configuration::updateValue('CETELEM_INFO_CALC_TEXT', $cetelemInfo);

            if (!Validate::isInt(Tools::getValue('CETELEM_MIN_AMOUNT'))) {
                $error .= $this->l('The minimun amount must be integer.');
            } else {
                Configuration::updateValue('CETELEM_MIN_AMOUNT', Tools::getValue('CETELEM_MIN_AMOUNT'));
            }

            if ($error != '') {
                $this->output .= $this->displayError($error);
            } else {
                $this->output .= $this->displayConfirmation($this->l('Settings updated successfully.'));
            }

            $active_connection = false;
            $active_config = true;
        }

        $this->context->smarty->assign(
            array(
                'active_connection' => $active_connection,
                'active_config' => $active_config,
            )
        );
    }
    private function createTransactionTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "cetelem_transactions` (
                `id_transaction` INT(11) NOT NULL AUTO_INCREMENT,
                `id_cart` INT(11) NOT NULL,
                `transaction_id` VARCHAR(50) NOT NULL,
                `result_code` VARCHAR(10) NOT NULL,
                `date_add` DATETIME NOT NULL,
                PRIMARY KEY (`id_transaction`),
                UNIQUE (`transaction_id`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;
        ";

        return Db::getInstance()->execute($sql);
    }
    public function getContent()
    {
        $cetelemStates = new CetelemStates();
        $error = false;
        $allow_url_error = $this->l('Is required for the module to work to get the function allow_url_fopen enabled');
        if (!ini_get('allow_url_fopen')) {
            $error = $allow_url_error;
        }
        $this->postProcess();
        $cetelemStates->createCetelemPrepprovedState();
        $cetelemStates->createCetelemApprovedState();
        $cetelemStates->createCetelemDeniedState();
        $cetelemStates->createCetelemStandByState();
        $this->context->smarty->assign(
            array(
                'module_dir' => $this->_path,
                'connection_data' => $this->displayFormConnectionData(),
                'config_data' => $this->displayFormConfig(),
            )
        );
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->output .= $this->context->smarty->fetch(
                $this->local_path . 'views/templates/admin/information15.tpl'
            );
        } else {
            $this->output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/information.tpl');
        }

        if ($error) {
            return $this->displayError($error) . $this->output;
        } else {
            return $this->output;
        }
    }

    public function displayFormConnectionData()
    {
        $languages = Language::getLanguages(false);
        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = (int)$language['id_lang'] == Configuration::get('PS_LANG_DEFAULT');
        }

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'cetelem';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $languages;
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitCetelemConnectData';

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $msg = 'desc';
        } else {
            $msg = 'hint';
        }

        $this->fields_form[0]['form'] = array(
            'tinymce' => false,
            'legend' => array(
                'title' => $this->l('Connection data'),
                'icon' => 'icon-gear'
            ),
            'input' => array(
                /*array(
                    'type' => 'select',
                    'label' => $this->l('Cetelem contracted mode'),
                    'name' => 'CETELEM_MODALITY',
                    'required' => false,
                    'lang' => false,
                    'col' => 4,
                    $msg => $this->l('In most cases this option has to be kept in "Normal". If Cetelem has contracted with a payment option for their customers free select "Free" and activate the campaign. If in doubt, contact Cetelem.'),
                    'options' => array(
                        'query' => array(
                            array('value' => 'N', 'name' => $this->l('Normal')),
                            array('value' => 'G', 'name' => $this->l('Free')),
                            array('value' => 'B', 'name' => $this->l('With and without interests')),
                        ),
                        'id' => 'value',
                        'name' => 'name',
                    )
                ),*/
                array(
                    'type' => 'text',
                    'label' => $this->l('Merchant Center Code'),
                    'name' => 'CETELEM_CLIENT_ID',
                    'required' => false,
                    'lang' => false,
                    'col' => 4,
                    $msg => $this->l(
                        'Indicate in this field your client code provided by Cetelem. In case of not knowing what contact Cetelem.'
                    )
                ),
            ),
            'submit' => array(
                'name' => 'submitCetelemConnectData',
                'title' => $this->l('Save'),
                'class' => 'button pull-right'
            ),
        );

        $helper->fields_value['CETELEM_CLIENT_ID'] = Configuration::get('CETELEM_CLIENT_ID');

        return $helper->generateForm($this->fields_form);
    }

    public function displayFormConfig()
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $k => $language) {
            $languages[$k]['is_default'] = (int)$language['id_lang'] == Configuration::get('PS_LANG_DEFAULT');
        }

        $calc_types = array(
            array('calc' => 0, 'name' => $this->l('Combo Columna')),
        );

        $calc_show = array(
            array('calc' => 0, 'name' => $this->l('Cetelem')),
            array('calc' => 1, 'name' => $this->l('Encuotas')),
            array('calc' => 2, 'name' => $this->l('Cetelem && Encuotas Segun Precio')),
            array('calc' => 3, 'name' => $this->l('Cetelem && Encuotas')),
        );

        $calc_position = array(
            array('calcp' => 0, 'name' => $this->l('Posición 1')),
            array('calcp' => 1, 'name' => $this->l('Posición 2')),
        );

        $carriers = Carrier::getCarriers(
            (int)Context::getContext()->language->id,
            true,  // Solo activos
            false, // No gratuitos
            false, // No agrupados por zona
            null   // Obtener todos los transportistas
        );
    
        $carrier_options = [];
        foreach ($carriers as $carrier) {
            $carrier_options[] = [
                'id_option' => (int)$carrier['id_carrier'],
                'name' => $carrier['name'],
            ];
        }
        $selected_carriers = json_decode(Configuration::get('CETELEM_CARRIERS'), true);
        if (!is_array($selected_carriers)) {
            $selected_carriers = [];
        }
       

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'cetelem';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->languages = $languages;
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = true;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitCetelemSettings';

  
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $type = 'radio';
            $msg = 'desc';
        } else {
            $type = 'switch';
            $msg = 'hint';
        }

        $this->fields_form[0]['form'] = array(
            'tinymce' => false,
            'legend' => array(
                'title' => $this->l('Cetelem configuration'),
                'icon' => 'icon-gear'
            ),
            'input' => array(
                array(
                    'type' => $type,
                    'label' => $this->l('Enviorement Real'),
                    'name' => 'CETELEM_ENV',
                    $msg => $this->l('Change the enviorment of your module configuration'),
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                
                array(
                    'type' => $type,
                    'label' => $this->l('Activate Cetelem payment method'),
                    'name' => 'CETELEM_SHOWCETELEM',
                    'required' => false,
                    $msg => $this->l('If we deactivate this option, the Cetelem payment method will not be available.'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Activate Encuotas payment method'),
                    'name' => 'CETELEM_SHOWENCUOTAS',
                    'required' => false,
                    $msg => $this->l('If we deactivate this option, the Encuotas payment method will not be available.'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Block callback by ip'),
                    'name' => 'CETELEM_CALLBACK_IP_RES',
                    'required' => false,
                    $msg => $this->l('If we activate the option we increase the security in front of external requests that validate orders'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Cetelem ip'),
                    'name' => 'CETELEM_IPS',
                    'required' => false,
                    'lang' => false,
                    'col' => 6,
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Create order when access to Cetelem Environment'),
                    'name' => 'CETELEM_ORDER_CREATION',
                    $msg => $this->l('Create order when access to Cetelem Environment'),
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Use special material?'),
                    'name' => 'CETELEM_SPEC_MATERIAL',
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('Minimum amount'),
                    'name' => 'CETELEM_MIN_AMOUNT',
                    'help' => $this->l('Cart minimum amount to display Cetelem payment method in checkout'),
                    $msg => $this->l(
                        'IMPORTANT NOTE: if you select an amount with less than 150 €,all orders with less than this amount will be declined '
                    ),
                    'required' => false,
                    'lang' => false,
                    'suffix' => '€',
                    'col' => 4,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Font size'),
                    'name' => 'FONT_SIZE_CETELEM',
                    'required' => false,
                    'lang' => false,
                    'col' => 1,
                    'help' => $this->l('Customize the font size of the calculator'),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Display calculator in product page'),
                    'name' => 'CETELEM_DISPLAY_CALC',
                    $msg => $this->l(
                        'Keep this option enabled to increase the average size of your cart and conversion rates'
                    ),
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Type of calculator to display'),
                    'name' => 'CETELEM_CALC_TYPE',
                    'required' => false,
                    'options' => array(
                        'query' => $calc_types,
                        'id' => 'calc',
                        'name' => 'name',
                    ),
                    'class' => 'calc_depending'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Calculator to display'),
                    'name' => 'CETELEM_CALC_SHOW',
                    'required' => false,
                    'options' => array(
                        'query' => $calc_show,
                        'id' => 'calc',
                        'name' => 'name',
                    ),
                    'class' => 'calc_depending'
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Block amount (only used for slider calc type)'),
                    'name' => 'CETELEM_AMOUNT_BLOCK',
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Calculator position on product page'),
                    'name' => 'CETELEM_CALC_POSITION',
                    'required' => false,
                    'options' => array(
                        'query' => $calc_position,
                        'id' => 'calcp',
                        'name' => 'name',
                    ),
                    'class' => 'calc_depending'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Informative text calculator'),
                    'name' => 'CETELEM_INFO_CALC_TEXT',
                    'autoload_rte' => true,
                    'lang' => true,
                    'cols' => 60,
                    'rows' => 10,
                    $msg => $this->l('Invalid characters:') . ' <>;=#{}',
                    'class' => 'calc_depending'
                ),
            ),
            'input' => array(
                array(
                    'type' => $type,
                    'label' => $this->l('Enviorement Real'),
                    'name' => 'CETELEM_ENV',
                    $msg => $this->l('Change the enviorment of your module configuration'),
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Ecredit Moto'),
                    'name' => 'CETELEM_MOTO',
                    $msg => $this->l('Add ecredit moto configuration'),
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Activate Cetelem payment method'),
                    'name' => 'CETELEM_SHOWCETELEM',
                    'required' => false,
                    $msg => $this->l('If we deactivate this option, the Cetelem payment method will not be available.'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Activate Encuotas payment method'),
                    'name' => 'CETELEM_SHOWENCUOTAS',
                    'required' => false,
                    $msg => $this->l('If we deactivate this option, the Encuotas payment method will not be available.'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Block callback by ip'),
                    'name' => 'CETELEM_CALLBACK_IP_RES',
                    'required' => false,
                    $msg => $this->l('If we activate the option we increase the security in front of external requests that validate orders'),
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Cetelem ip'),
                    'name' => 'CETELEM_IPS',
                    'required' => false,
                    'lang' => false,
                    'col' => 6,
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Create order when access to Cetelem Environment'),
                    'name' => 'CETELEM_ORDER_CREATION',
                    $msg => $this->l('Create order when access to Cetelem Environment'),
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Use special material?'),
                    'name' => 'CETELEM_SPEC_MATERIAL',
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Minimum amount'),
                    'name' => 'CETELEM_MIN_AMOUNT',
                    'help' => $this->l('Cart minimum amount to display Cetelem payment method in checkout'),
                    $msg => $this->l(
                        'IMPORTANT NOTE: if you select an amount with less than 150 €,all orders with less than this amount will be declined '
                    ),
                    'required' => false,
                    'lang' => false,
                    'suffix' => '€',
                    'col' => 4,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Font size'),
                    'name' => 'FONT_SIZE_CETELEM',
                    'required' => false,
                    'lang' => false,
                    'col' => 1,
                    'help' => $this->l('Customize the font size of the calculator'),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Display calculator in product page'),
                    'name' => 'CETELEM_DISPLAY_CALC',
                    $msg => $this->l(
                        'Keep this option enabled to increase the average size of your cart and conversion rates'
                    ),
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Type of calculator to display'),
                    'name' => 'CETELEM_CALC_TYPE',
                    'required' => false,
                    'options' => array(
                        'query' => $calc_types,
                        'id' => 'calc',
                        'name' => 'name',
                    ),
                    'class' => 'calc_depending'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Calculator to display'),
                    'name' => 'CETELEM_CALC_SHOW',
                    'required' => false,
                    'options' => array(
                        'query' => $calc_show,
                        'id' => 'calc',
                        'name' => 'name',
                    ),
                    'class' => 'calc_depending'
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Block amount (only used for slider calc type)'),
                    'name' => 'CETELEM_AMOUNT_BLOCK',
                    'required' => false,
                    'is_bool' => true,
                    'class' => 't',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Calculator position on product page'),
                    'name' => 'CETELEM_CALC_POSITION',
                    'required' => false,
                    'options' => array(
                        'query' => $calc_position,
                        'id' => 'calcp',
                        'name' => 'name',
                    ),
                    'class' => 'calc_depending'
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Informative text calculator'),
                    'name' => 'CETELEM_INFO_CALC_TEXT',
                    'autoload_rte' => true,
                    'lang' => true,
                    'cols' => 60,
                    'rows' => 10,
                    $msg => $this->l('Invalid characters:') . ' <>;=#{}',
                    'class' => 'calc_depending'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Select the collection point'),
                    'name' => 'CETELEM_CARRIERS[]',
                    'multiple' => true,
                    'options' => array(
                        'query' => $carrier_options,
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                    'desc' => $this->l('Select the carriers who will be the collection points.'),
                ),
            ),
            
            'submit' => array(
                'name' => 'submitCetelemSettings',
                'title' => $this->l('Save'),
                'class' => 'button pull-right'
            ),
        );

        $helper->fields_value['CETELEM_MOTO'] = Configuration::get('CETELEM_MOTO');
        $helper->fields_value['CETELEM_ENV'] = Configuration::get('CETELEM_ENV');
        $helper->fields_value['CETELEM_IPS'] = Configuration::get('CETELEM_IPS');
        $helper->fields_value['CETELEM_ORDER_CREATION'] = Configuration::get('CETELEM_ORDER_CREATION');
        $helper->fields_value['CETELEM_SPEC_MATERIAL'] = Configuration::get('CETELEM_SPEC_MATERIAL');
        $helper->fields_value['CETELEM_MIN_AMOUNT'] = Configuration::get('CETELEM_MIN_AMOUNT');
        $helper->fields_value['CETELEM_DISPLAY_CALC'] = Configuration::get('CETELEM_DISPLAY_CALC');
        $helper->fields_value['CETELEM_TEXT_COLOR'] = Configuration::get('CETELEM_TEXT_COLOR');
        $helper->fields_value['CETELEM_AMOUNT_BLOCK'] = Configuration::get('CETELEM_AMOUNT_BLOCK');
        $helper->fields_value['FONT_SIZE_CETELEM'] = Configuration::get('FONT_SIZE_CETELEM');
        $helper->fields_value['CETELEM_BACKGROUND_COLOR'] = Configuration::get('CETELEM_BACKGROUND_COLOR');
        $helper->fields_value['CETELEM_BORDER_COLOR'] = Configuration::get('CETELEM_BORDER_COLOR');
        $helper->fields_value['CETELEM_FEE_COLOR'] = Configuration::get('CETELEM_FEE_COLOR');
        $helper->fields_value['CETELEM_CALC_TYPE'] = Configuration::get('CETELEM_CALC_TYPE');
        $helper->fields_value['CETELEM_CALC_SHOW'] = Configuration::get('CETELEM_CALC_SHOW');
        $helper->fields_value['CETELEM_CALC_POSITION'] = Configuration::get('CETELEM_CALC_POSITION');
        $helper->fields_value['CETELEM_CALLBACK_IP_RES'] = Configuration::get('CETELEM_CALLBACK_IP_RES');
        $helper->fields_value['CETELEM_SHOWCETELEM'] = Configuration::get('CETELEM_SHOWCETELEM');
        $helper->fields_value['CETELEM_SHOWENCUOTAS'] = Configuration::get('CETELEM_SHOWENCUOTAS');
        $helper->fields_value['CETELEM_PRODUCTS'] = Configuration::get('CETELEM_PRODUCTS');
        $helper->fields_value['CETELEM_CARRIERS[]'] = $selected_carriers;

        foreach (Language::getLanguages() as $lang) {
            $helper->fields_value['CETELEM_INFO_CALC_TEXT'][$lang['id_lang']] = htmlspecialchars_decode(
                Configuration::get('CETELEM_INFO_CALC_TEXT', $lang['id_lang'])
            );
        }

        return $helper->generateForm($this->fields_form);
    }

    public function hookDisplayHeader()
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addCSS($this->_path . '/views/css/front15.css');
        } else {
            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        }

        $this->context->controller->addJS($this->_path . '/views/js/ajax-cetelem.js');
        if ((!isset($this->context->controller->php_self) || ($this->context->controller->php_self != 'product' && $this->context->controller->php_self != 'cms') ||
                (!Configuration::get('CETELEM_DISPLAY_CALC') && $this->context->controller->php_self != 'cms')) &&
            get_class($this->context->controller) != 'CetelemCalculatorModuleFrontController'
        ) {
            return;
        }
        $tmp_product = false;
        $productPrice = 0;
        if (Tools::getValue('id_product')) {
            $productPrice = Product::getPriceStatic(Tools::getValue('id_product'), true);
            if ($productPrice >= (float)Configuration::get(
                'CETELEM_MIN_AMOUNT'
            )) {
                if (version_compare(_PS_VERSION_, '1.6', '<')) {
                    $this->context->controller->addJS($this->_path . '/views/js/calc15.js');
                } else {
                    $this->context->controller->addJS($this->_path . '/views/js/calc.js');
                }
                if (Tools::getValue('id_product')) {
                    $tmp_product = new Product(Tools::getValue('id_product'));
                    $tax_inc = (bool)Configuration::get('PS_TAX');
                    $cetelemPrice = $tmp_product->getPrice($tax_inc, null, 6);
                    $cetelemCombinations = $tmp_product->getWsCombinations();
                    $cetelemCombPrices = array();
                    $cetelemCombPrices[0] = $tmp_product->getPrice($tax_inc, null, 6);
                    foreach ($cetelemCombinations as $cetelemCombination) {
                        $cetelemCombPrices[$cetelemCombination['id']] = $tmp_product->getPrice(
                            $tax_inc,
                            $cetelemCombination['id'],
                            6
                        );
                    }
                    $this->smarty->assign(
                        array(
                            'productPriceC' => $cetelemPrice,
                            'cetelemCombPrices' => $cetelemCombPrices
                        )
                    );
                }
            } else {
                return;
            }
        } else {
            if (property_exists($this->context->controller, 'page_name')) {
                if ($this->context->controller->page_name == 'module-cetelem-calculator') {
                    $this->context->controller->addCSS($this->_path . '/views/css/calculator.css');
                    $this->smarty->assign(
                        array(
                            'min_amount' => Configuration::get('CETELEM_MIN_AMOUNT')
                        )
                    );
                    $this->context->controller->addJS($this->_path . '/views/js/controller-calculator.js');
                }
            }
            if ($this->context->controller->php_self == 'cms') {
                Media::addJsDef(
                    array(
                        'url_cms' => self::CETELEM_URL_CMS,
                        'url_cms_plain' => self::CETELEM_URL_CMS_PLAIN,
                        'center_code' => Configuration::get('CETELEM_CLIENT_ID')
                    )
                );
                $this->context->controller->addJS($this->_path . '/views/js/postscribe.min.js');
                $this->context->controller->addJS($this->_path . '/views/js/calc-cms.js');
            }
        }

        $server_url_cetelem = self::CETELEM_URL_SCRIPT;
        $center_code = Configuration::get('CETELEM_CLIENT_ID');
        if (!Configuration::get('CETELEM_ENV')) {
            $server_url_cetelem = self::CETELEM_URL_TEST_SCRIPT;
            $center_code = 'PRUEBAS';
        }

        $ct = (int)Configuration::get('CETELEM_CALC_TYPE');
        $calc_show = $cs = (int)Configuration::get('CETELEM_CALC_SHOW');

        $calc_type = $this->getCalcTypeScript($ct);
        if (!$this->showCetelem) {
            $calc_type = '';
        }
        if ($this->showEnquotas && ($calc_show == 1 || $calc_show == 2 || $calc_show == 3)) {
            if ($productPrice) {
                $maxAmount = (float)Tools::file_get_contents('https://www.cetelem.es/addons/importe_enc.txt');
                if ($productPrice < $maxAmount) {
                    if ($cs == 2) {
                        $calc_type = $this->getCalcTypeScript(3);
                        // echo 'A'.$calc_type.'<br/>';
                    } else {
                        $calc_type = $this->getCalcTypeScript($ct);
                        //echo 'B'.$calc_type.'<br/>';
                    }
                    if ($calc_show == 2) {
                        $calc_show = 1;
                    }
                } else if ($cs == 1) {
                    $calc_type = '';
                } else if ($cs == 3) {
                    $calc_show = 2;
                }
            }
        }
        $material = '';
        if (Configuration::get('CETELEM_PRODUCTS') && Tools::getValue('id_product')) {
            $arrayProducts = explode(',', Configuration::get('CETELEM_PRODUCTS'));
            if (in_array(Tools::getValue('id_product'), $arrayProducts)) {
                $material = '323';
            }
        }
        $this->smarty->assign(
            array(
                'server_url_cetelem' => $server_url_cetelem,
                'center_code' => $center_code,
                'material' => $material,
                'text_color_cetelem' => Configuration::get('CETELEM_TEXT_COLOR'),
                'cetelem_amount_block' => Configuration::get('CETELEM_AMOUNT_BLOCK'),
                'font_size_cetelem' => Configuration::get('FONT_SIZE_CETELEM'),
                'calc_type' => $calc_type,
                'calc_show' => $calc_show
            )
        );
        return $this->display(__FILE__, 'header.tpl');
    }

    public function hookPaymentOptions($params)
    {
        $id_language = Tools::strtoupper($this->context->language->iso_code);
        $cart = $params['cart'];
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

        $transact_id = $ano . str_pad($day_julian, 3, '0', STR_PAD_LEFT) . str_pad($cart->id, 9, '0', STR_PAD_LEFT);
        $this->context->cookie->__set('cetelem_transact_id', $transact_id);
        $amount = str_replace('.', '', number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

        $address = new Address($this->context->cart->id_address_invoice);
        $loaded_address = Validate::isLoadedObject($address);

        $addressWithoutNum = preg_replace('/[0-9]+/', '', $address->address1);

        $addressText = str_replace('\\', ' ', $addressWithoutNum);
        $addressText = str_replace('/', ' ', $addressText);
        $addressText = str_replace(',', ' ', $addressText);
        $addressText = str_replace('.', ' ', $addressText);
        $addressText = str_replace('º', ' ', $addressText);
        $addressText = str_replace('ª', ' ', $addressText);

        //Customer data for Cetelem application
        $customer = new Customer($this->context->customer->id);
        $loaded_customer = Validate::isLoadedObject($customer);
        $gender = $this->context->customer->id_gender;
        if ($gender == 1) {
            $gender = 'SR';
        } else {
            $gender = 'SRA';
        }
        $birthday = date('d/m/Y', strtotime($this->context->customer->birthday));
        $loaded_address = Validate::isLoadedObject($address);
        $fields = array(
            'cart' => $cart,
            'transact_id' => $transact_id,
            'amount' => $amount,
            'timestamp1' => $timestamp1,
            'loaded_customer' => $loaded_customer,
            'gender' => $gender,
            'address' => $address,
            'birthday' => $birthday,
            'addressText' => $addressText,
            'loaded_address' => $loaded_address,
            'id_language' => $id_language
        );
        /* or ask for confirmation */
        $payment_options = array();

        $orderTotal = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        if ($orderTotal >= Configuration::get('CETELEM_MIN_AMOUNT') && $this->showCetelem)
            $payment_options[] = $this->getGPaymentOption($fields);
        if ($this->showEnquotas && $orderTotal >= Configuration::get('CETELEM_MIN_ENQUOTAS')) {

            $maxAmount = 1000;
            $dateUpd = Db::getInstance()->getValue('
                SELECT date_upd 
                FROM `' . _DB_PREFIX_ . 'configuration`
                WHERE name="CETELEM_MAX_AMOUNT"');
            if ($dateUpd) {
                $dateUpd = strtotime($dateUpd);
                $now = strtotime("now");
                $diff = $now - $dateUpd;
                $days = round($diff / (60 * 60 * 24));
                $maxAmount = (float)Tools::file_get_contents('https://www.cetelem.es/addons/importe_enc.txt');
                /*if($days>2){
                    $maxAmount = (float)Tools::file_get_contents('https://www.cetelem.es/addons/importe_enc.txt');
                     Configuration::updateValue('CETELEM_MAX_AMOUNT',$maxAmount);    
                 } else {
                     $maxAmount = (float)Configuration::get('CETELEM_MAX_AMOUNT');
                 }*/
                if ($maxAmount == 0) {
                    $maxAmount = (float)Tools::file_get_contents('https://www.cetelem.es/addons/importe_enc.txt');
                }
            } else {
                $maxAmount = (float)Tools::file_get_contents('https://www.cetelem.es/addons/importe_enc.txt');
                Configuration::updateValue('CETELEM_MAX_AMOUNT', $maxAmount);
            }




            //$fields['mode'] = 3;
            if ($orderTotal < $maxAmount && $this->showEnquotas) {
                $newpayment = $this->getGPaymentOption($fields, true);
                $newpayment->setCallToActionText($this->l('Paga enCuotas: financiación inmediata'));
                $newpayment->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/logo2.svg'));
                if (is_array($payment_options) && sizeof($payment_options) > 0) {

                    $this->context->smarty->assign(array('addCetelemScript' => false));
                }
                $this->context->smarty->assign(array('encuotas' => true));
                $newpayment->setAdditionalInformation($this->fetch('module:cetelempayment/views/templates/front/payment_infos.tpl'));
                //$newpayment->setAdditionalInformation('');
                $conex_url = (Configuration::get(
                    'CETELEM_ENV'
                )) ? self::CETELEM_URL_NEWCONNECTION : self::CETELEM_URL_TEST_NEWCONNECTION;
                $form_next_url = Configuration::get('CETELEM_ORDER_CREATION') ? $this->context->link->getModuleLink(
                    $this->name,
                    'payment2',
                    array('securekey' => Context::getContext()->customer->secure_key, 'encuotas' => 1),
                    true
                ) : $conex_url;
                $newpayment->setAction($form_next_url);
                $payment_options[] = $newpayment;
            }
        }

        $onepagecheckoutps = Module::isInstalled('onepagecheckoutps');
        if ($onepagecheckoutps) {
            if (Module::isEnabled('onepagecheckoutps')) {
                $this->context->smarty->assign(
                    array(
                        'onepagecheckoutps_enabled' => true,
                    )
                );
            } else {
                $this->context->smarty->assign(
                    array(
                        'onepagecheckoutps_enabled' => false,
                    )
                );
            }
        } else {
            $this->context->smarty->assign(
                array(
                    'onepagecheckoutps_enabled' => false,
                )
            );
        }

        //is not usefull to get the nom_pago here, we are not using here
        if ((Tools::strtoupper($this->context->language->iso_code) == 'ES') && (Configuration::get(
            'CETELEM_LEGAL_NOM_PAGO'
        ) != '')) {
            $this->context->smarty->assign(
                array(
                    'name_payment' => Configuration::get('CETELEM_LEGAL_NOM_PAGO'),
                )
            );
        }


        if (is_array($payment_options)) {
            return $payment_options;
        } else {
            return false;
        }
    }
    private function shouldOverridePostalCode($id_carrier)
    {
        $selected_carriers = json_decode(Configuration::get('CETELEM_CARRIERS'), true);
        if (!is_array($selected_carriers)) {
            return false;
        }
    
        return in_array($id_carrier, $selected_carriers);
    }
    
    public function getGPaymentOption($fields, $encuotas = false)
    {
        $arrayPayments = array();
        //new calculator
        $cetelem_module = Module::getInstanceByName('cetelempayment');

        $calc_type = $cetelem_module->getCalcTypeScript(Configuration::get('CETELEM_CALC_TYPE'));

        $server_url_cetelem = $cetelem_module::CETELEM_URL_SCRIPT;
        if (!Configuration::get('CETELEM_ENV')) {
            $server_url_cetelem = self::CETELEM_URL_TEST_SCRIPT;
        }

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
                'calc_type' => $calc_type,
                'hidecalc' => false,
                'encuotas' => false
            )
        );

        $externalOption = new PaymentOption();
        $conex_url = (Configuration::get(
            'CETELEM_ENV'
        )) ? self::CETELEM_URL_CONNECTION : self::CETELEM_URL_TEST_CONNECTION;

        // Redirigimos a payment2 siempre, aun que el check "Create order when access to Cetelem Environment" este activo o no 
        $form_next_url = $this->context->link->getModuleLink(
            $this->name,
            'payment2',
            array('securekey' => Context::getContext()->customer->secure_key),
            true
        );

        $this->smarty->assign(
            $this->getTemplateVars()
        );
        $payment_text = '';

        $payment_text = $this->l('Finance with Cetelem');
        $material = '499';
        if ($this->hasSpecialProducts()) {
            $material = '323';
        } else if ($this->hasSpecialMaterial()) {
            $material = '333';
        }
        $callback = $this->context->link->getModuleLink( $this->name, 'callback');
        if ($encuotas) {
            $callback = $this->context->link->getModuleLink( $this->name, 'callback', array('encuotas' => 1));
        }
        $this->context->smarty->assign(array('addCetelemScript' => true, 'material' => $material));
        $externalOption->setCallToActionText($payment_text)
            ->setModuleName($this->name)
            ->setAction($form_next_url)
            ->setAdditionalInformation($this->fetch('module:cetelempayment/views/templates/front/payment_infos.tpl'))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/logo-checkout.png'))
            ->setInputs(
                [
                    'COMANDO' => [
                        'name' => 'COMANDO',
                        'type' => 'hidden',
                        'value' => 'INICIO',
                    ],
                    'Material' => [
                        'name' => 'Material',
                        'type' => 'hidden',
                        //'value' => '499',
                        'value' => $material,
                    ],
                    /*'CodProducto' => [
                        'name' => 'CodProducto',
                        'type' => 'hidden',
                        'value' => $codproduct,
                    ]
                    ,*/
                    'IdTransaccion' => [
                        'name' => 'IdTransaccion',
                        'type' => 'hidden',
                        'value' => $fields['transact_id'],
                    ],
                    'CodCentro' => [
                        'name' => 'CodCentro',
                        'type' => 'hidden',
                        'value' => Configuration::get('CETELEM_CLIENT_ID'),
                    ],
                    'Importe' => [
                        'name' => 'Importe',
                        'type' => 'hidden',
                        'value' => $fields['amount'],
                    ],
                    /*'Modalidad' => [
                        'name' => 'Modalidad',
                        'type' => 'hidden',
                        'value' => $mode,
                    ]
                    ,*/
                    'ReturnOK' => [
                        'name' => 'ReturnOK',
                        'type' => 'hidden',
                        'value' => $callback,
                    ],
                    'ReturnURL' => [
                        'name' => 'ReturnURL',
                        'type' => 'hidden',
                        'value' => $this->context->link->getModuleLink( $this->name, 'validation'),
                    ],
                    'Tratamiento' => [
                        'name' => 'Tratamiento',
                        'type' => 'hidden',
                        //'value' => $fields['loaded_customer'] ? CetelemFieldValidator::validateGender($fields['gender']) : '',
                        'value' => $fields['gender'] ? $fields['gender'] : '',
                    ],
                    'Nombre' => [
                        'name' => 'Nombre',
                        'type' => 'hidden',
                        //'value' => $fields['loaded_address'] ? CetelemFieldValidator::validateFirstLastName($fields['address']->firstname) : '',
                        'value' => $fields['address']->firstname ? $fields['address']->firstname : '',
                    ],
                    'Apellidos' => [
                        'name' => 'Apellidos',
                        'type' => 'hidden',
                        //'value' => $fields['loaded_address'] ? CetelemFieldValidator::validateFirstLastName($fields['address']->lastname) : '',
                        'value' => $fields['address']->lastname ? $fields['address']->lastname : '',
                    ],
                    'FechaNacimiento' => [
                        'name' => 'FechaNacimiento',
                        'type' => 'hidden',
                        //'value' => $fields['loaded_customer'] ? CetelemFieldValidator::validateBirthday($fields['birthday']) : '',
                        'value' => $fields['birthday'] ? $fields['birthday'] : '',
                    ],
                    'Direccion' => [
                        'name' => 'Direccion',
                        'type' => 'hidden',
                        //'value' => $fields['loaded_address'] ? CetelemFieldValidator::validateAddress($fields['addressText']) : '',
                        'value' => $fields['addressText'] ? $fields['addressText'] : '',
                    ],
                    'Localidad' => [
                        'name' => 'Localidad',
                        'type' => 'hidden',
                        //'value' => $fields['loaded_address'] ? CetelemFieldValidator::validateCity($fields['address']->city) : '',
                        'value' => $fields['address']->city ? $fields['address']->city : '',
                    ],
                'CodigoPostalEnvio' => [
                    'name' => 'CodigoPostalEnvio',
                    'type' => 'hidden',
                    'value' => $this->shouldOverridePostalCode($this->context->cart->id_carrier) ? '00000' : $fields['address']->postcode,
                ],
                    'Email' => [
                        'name' => 'Email',
                        'type' => 'hidden',
                        //'value' => $fields['loaded_address'] ? CetelemFieldValidator::validateEmail($this->context->customer->email) : '',
                        'value' => $this->context->customer->email ? $this->context->customer->email : '',
                    ],
                ]
            );
          
        return $externalOption;
    }

    public function hookDisplayPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }
        $order = $params['order'];

        $currency = new Currency((int)$order->id_currency);

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(
            array(
                'id_order' => $order->id,
                'reference' => $order->reference,
                'params' => $params,
                'total' => Tools::displayPrice($order->getOrdersTotalPaid(), $currency, false),
            )
        );

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    public function hookDisplayReassurance($params)
    {
        if (!Configuration::get('CETELEM_CALC_POSITION')) {
            return $this->calculatorHookRenderer();
        } else {
            return;
        }
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        if (Configuration::get('CETELEM_CALC_POSITION')) {
            return $this->calculatorHookRenderer();
        } else {
            return;
        }
    }

    public function calculatorHookRenderer()
    {
        if (Configuration::get('CETELEM_DISPLAY_CALC')) {
            //RetroCompatibility with version before 1.5.6.2
            if (method_exists(
                $this->context->controller,
                'getProduct'
            ) && ($product = $this->context->controller->getProduct())) {
                $product = $this->context->controller->getProduct();
            } else {
                $id_lang = (int)$this->context->language->id;
                $product = new Product(Tools::getValue('id_product'), false, $id_lang);
            }

            //RetroCompatibility with version before 1.6.1.0
            $id_group = (int)Group::getCurrent()->id;
            $group_reduction = GroupReduction::getValueForProduct($product->id, $id_group);

            if ($group_reduction === false) {
                $group_reduction = Group::getReduction((int)$this->context->cookie->id_customer) / 100;
            }

            $this->smarty->assign(
                array(
                    'customer_group_without_tax_cetelem' => Group::getPriceDisplayMethod(
                        $this->context->customer->id_default_group
                    ),
                    'group_reduction_cetelem' => $group_reduction,
                    'productObjCetelem' => $product,
                    'ecotaxTax_rate_cetelem' => (float)Tax::getProductEcotaxRate(
                        $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}
                    ),
                )
            );

            if ($product->hasAttributes() > 0) {
                $amount = $product->getPrice(true, Product::getDefaultAttribute($product->id));
            } else {
                $amount = $product->getPrice();
            }

            $this->smarty->assign(
                array(
                    'text_color' => Configuration::get('CETELEM_TEXT_COLOR'),
                    'font_size_cetelem' => Configuration::get('FONT_SIZE_CETELEM'),
                    'cetelem_amount_block' => Configuration::get('CETELEM_AMOUNT_BLOCK'),
                    'background_color' => Configuration::get('CETELEM_BACKGROUND_COLOR'),
                    'border_color' => Configuration::get('CETELEM_BORDER_COLOR'),
                    'fee_color' => Configuration::get('CETELEM_FEE_COLOR'),
                    'info_text' => htmlspecialchars_decode(
                        Configuration::get('CETELEM_INFO_CALC_TEXT', $this->context->language->id)
                    ),
                    'amount' => $amount,
                    'tin' => Configuration::get('CETELEM_NORMAL_TIN'),
                    'tae' => Configuration::get('CETELEM_NORMAL_TAE'),
                    'comision' => 0,
                    'fecha' => Configuration::get('CETELEM_NORMAL_FECHA_VALIDEZ'),
                    'ej_meses' => 0,
                )
            );

            if (Configuration::get('CETELEM_DISPLAY_CALC') && Configuration::get(
                'CETELEM_MIN_AMOUNT'
            ) <= $product->getPrice(true)) {
                return $this->displayCalculator();
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function hookDisplayCustomized($params)
    {
        return $this->calculatorHookRenderer();
    }

    protected function displayCalculator()
    {
        return $this->display(__FILE__, 'views/templates/hook/product-calc.tpl');
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $order_state = isset($params['newOrderStatus']) ? $params['newOrderStatus'] : $params['orderStatus'];
        switch ($order_state->id) {
            case $this->cetelemStates->StateCetelemPreApproved():
                $this->sendCetelemEmail('cetelem_preapproved_credit', $params['id_order'], $params['newOrderStatus']);
                break;
            case $this->cetelemStates->StateCetelemApproved():
                $this->sendCetelemEmail('cetelem_credit_ok', $params['id_order'], $params['newOrderStatus']);
                break;
            case $this->cetelemStates->StateCetelemDenied():
                $this->sendCetelemEmail('cetelem_credit_ko', $params['id_order'], $params['newOrderStatus']);
                /* To restore the quantity of each product and / or combination */
                //(discommented again) we commented that on 2.5.0 version because we do it already in the overrides (check and get sure, otherwise we may be have to uncomment this again)
                /*$order = new Order($params['id_order']);
                $order_details = $order->getProductsDetail();
                $moduleCetelem = Module::getInstanceByName('cetelem');
                foreach ($order_details as $order_detail) {
                  $temp_ord_detail = new OrderDetail($order_detail['id_order_detail']);
                  $moduleCetelem->setQuantityReinjection($temp_ord_detail, $temp_ord_detail->product_quantity);
                }*/
                break;
        }
    }

    /**
     *
     * @param type $template
     * @param type $id_order
     * @param OrderState $order_state
     */
    protected function sendCetelemEmail($template, $id_order, $order_state)
    {
        $order = new Order($id_order);
        $customer = new Customer($order->id_customer);
        $data = array(
            '{lastname}' => $customer->lastname,
            '{firstname}' => $customer->firstname,
        );

        Mail::Send(
            $customer->id_lang,
            $template,
            $order_state->name,
            $data,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . $this->name . '/mails/',
            false,
            (int)$order->id_shop
        );
    }

    public function hookActionValidateOrder($params)
    {
        $this->hookActionOrderStatusPostUpdate($params);
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $tmp_order = new Order((int)$params['id_order']);
        $tmp_payment = $tmp_order->payment;
        if ($tmp_payment == 'cetelem' && (int)$tmp_order->current_state == (int)Configuration::get('PS_OS_CANCELED')) {
            Tools::redirectAdmin($_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function getCalcTypeScript($calc_type)
    {
        $calc_type = (int)$calc_type;
        switch ($calc_type) {
            case 0:
                //combo1
                return '/eCommerceCalculadora/resources/js/mix/eCalculadoraCetelemMix.js';

            case 1:
                //combo2
                return '/eCommerceCalculadora/resources/js/mix/eCalculadoraCetelemMixModelo2.js';

            case 2:
                //slider
                return '/eCommerceCalculadora/resources/js/mix/eCalculadoraCetelemMixModelo2.js';
            case 3:
                //encuotas
                return '/eCommerceCalculadora/resources/js/mix/eCalculadoraCetelemMixModelo2.js';

            default:
                //combo1
                return '/eCommerceCalculadora/resources/js/mix/eCalculadoraCetelemMix.js';
        }
    }

    public function setQuantityReinjection($order_detail, $qty_cancel_product, $delete = false)
    {
        $reinjectable_quantity = (int)$order_detail->product_quantity - (int)$order_detail->product_quantity_reinjected;
        $quantity_to_reinject = $qty_cancel_product > $reinjectable_quantity ? $reinjectable_quantity : $qty_cancel_product;
        // @since 1.5.0 : Advanced Stock Management

        $product = new Product(
            $order_detail->product_id,
            false,
            (int)$this->context->language->id,
            (int)$order_detail->id_shop
        );

        if (
            Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
            $product->advanced_stock_management && $order_detail->id_warehouse != 0
        ) {
            $manager = StockManagerFactory::getManager();
            $movements = StockMvt::getNegativeStockMvts(
                $order_detail->id_order,
                $order_detail->product_id,
                $order_detail->product_attribute_id,
                $quantity_to_reinject
            );
            $left_to_reinject = $quantity_to_reinject;
            foreach ($movements as $movement) {
                if ($left_to_reinject > $movement['physical_quantity']) {
                    $quantity_to_reinject = $movement['physical_quantity'];
                }

                $left_to_reinject -= $quantity_to_reinject;
                if (Pack::isPack((int)$product->id)) {
                    // Gets items
                    if ($product->pack_stock_type == 1 || $product->pack_stock_type == 2 || ($product->pack_stock_type == 3 && Configuration::get(
                        'PS_PACK_STOCK_TYPE'
                    ) > 0)) {
                        $products_pack = Pack::getItems((int)$product->id, (int)Configuration::get('PS_LANG_DEFAULT'));
                        // Foreach item
                        foreach ($products_pack as $product_pack) {
                            if ($product_pack->advanced_stock_management == 1) {
                                $manager->addProduct(
                                    $product_pack->id,
                                    $product_pack->id_pack_product_attribute,
                                    new Warehouse($movement['id_warehouse']),
                                    $product_pack->pack_quantity * $quantity_to_reinject,
                                    null,
                                    $movement['price_te'],
                                    true
                                );
                            }
                        }
                    }
                    if (
                        $product->pack_stock_type == 0 || $product->pack_stock_type == 2 ||
                        ($product->pack_stock_type == 3 && (Configuration::get(
                            'PS_PACK_STOCK_TYPE'
                        ) == 0 || Configuration::get('PS_PACK_STOCK_TYPE') == 2))
                    ) {
                        $manager->addProduct(
                            $order_detail->product_id,
                            $order_detail->product_attribute_id,
                            new Warehouse($movement['id_warehouse']),
                            $quantity_to_reinject,
                            null,
                            $movement['price_te'],
                            true
                        );
                    }
                } else {
                    $manager->addProduct(
                        $order_detail->product_id,
                        $order_detail->product_attribute_id,
                        new Warehouse($movement['id_warehouse']),
                        $quantity_to_reinject,
                        null,
                        $movement['price_te'],
                        true
                    );
                }
            }

            $id_product = $order_detail->product_id;
            if ($delete) {
                $order_detail->delete();
            }
            StockAvailable::synchronize($id_product);
        } elseif ($order_detail->id_warehouse == 0) {
            StockAvailable::updateQuantity(
                $order_detail->product_id,
                $order_detail->product_attribute_id,
                $quantity_to_reinject,
                $order_detail->id_shop
            );

            if ($delete) {
                $order_detail->delete();
            }
        } else {
            $this->errors[] = Tools::displayError('This product cannot be re-stocked.');
        }
    }

    public function getTemplateVars()
    {
        $cart = $this->context->cart;
        $text_payment = '';
        if ((Tools::strtoupper($this->context->language->iso_code) == 'ES') && (Configuration::get(
            'CETELEM_LEGAL_CHECKOUT'
        ) != '')) {
            $text_payment = Configuration::get('CETELEM_LEGAL_CHECKOUT');
        }

        $total = $this->trans(
            '%amount% (tax incl.)',
            array(
                '%amount%' => Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH)),
            ),
            'Modules.Cetelem.Admin'
        );

        return [
            'checkTotal' => $total,
            'text_payment' => $text_payment,
        ];
    }

    public function hookActionCronJob()
    {
        //Exemple basique on va créer un fichier de log et insérer un contenu dès que la tache cron est appellée
        $fp = fopen(dirname(__FILE__) . '/cron.log', 'a+');
        fputs($fp, 'CALLED at ' . date('Y-m-d H:i:s'));
        fclose($fp);

        //Exemple plus avancé, on souhaite effectuer des taches différentes en fonction de l'heure
        $hour = date('H');

        if ($hour > 8 && $hour <= 10) {
            $url = self::URL_CSV_CALC_LEGAL . Configuration::get('CETELEM_CLIENT_ID');
            $arrContextOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'ciphers' => 'HIGH:!SSLv2:!SSLv3',
                    'disable_compression' => true,
                ]
            ];
            if (filter_var($url, FILTER_VALIDATE_URL)) {
            $content = Tools::file_get_contents($url, 0, stream_context_create($arrContextOptions));
            }
            if ($content) {
                $xml = new SimpleXMLElement($content);

                if ($xml->nomPago != '') {
                    Configuration::updateValue('CETELEM_LEGAL_NOM_PAGO', $xml->nomPago);
                }

                if ($xml->legalCheckout != '') {
                    Configuration::updateValue('CETELEM_LEGAL_CHECKOUT', $xml->legalCheckout);
                }
            }
        }
    }

    /**
     * Information sur la fréquence des taches cron du module
     * Granularité maximume à l'heure
     */
    public function getCronFrequency()
    {
        $hour = rand(8, 10); //they want the cron to be called minimum at 8 and maximum at 10
        return array(
            'hour' => $hour,
            'day' => -1,
            'month' => -1,
            'day_of_week' => -1
        );
    }

    private function updateaprovedState()
    {
        //updateaproved_state
        $aproved_state = new OrderState((int)$this->cetelemStates->StateCetelemApproved());
        if ((int)Configuration::get('CETELEM_PREORDER_STOCK')) {
            $aproved_state->logable = false;
            $aproved_state->update();
        } else {
            $aproved_state->logable = true;
            $aproved_state->update();
        }
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->active) {
            return;
        }
        //  $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->calculatorHookRenderer();
    }

    public function hasSpecialMaterial()
    {
        //return true;
        if (Configuration::get('CETELEM_SPEC_MATERIAL')) {
            if ($this->context->cart->getOrderTotal() >= 3000) {
                return true;
            } else {
                $i = 0;
                $products = $this->context->cart->getProducts();
                foreach ($products as $product_) {
                    if ($product_['price'] >= 600) {
                        $i++;
                    }
                    if ($i >= 2) {
                        return true;
                    }
                }
                return false;
            }
        } else {
            return false;
        }
    }

    public function hasSpecialProducts()
    {
        if (Configuration::get('CETELEM_PRODUCTS')) {
            $arrayProducts = explode(',', Configuration::get('CETELEM_PRODUCTS'));
            $products = $this->context->cart->getProducts();
            foreach ($products as $product_) {
                if (in_array($product_['id_product'], $arrayProducts)) {
                    return true;
                }
            }
        }
        return false;
    }
}
