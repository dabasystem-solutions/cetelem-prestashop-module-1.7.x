<?php
/**
 * 2007-2016 PrestaShop
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
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class CetelemCalculatorModuleFrontController extends ModuleFrontController
{

    public function init()
    {
        parent::init();
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->display_column_left = true;
            $this->display_column_right = true;
        } else {
            $this->display_column_left = false;
            $this->display_column_right = false;
        }
    }

    /*public function setMedia() {
        parent::setMedia();

        if (version_compare(_PS_VERSION_, '1.6', '<'))
            $this->addJS(_MODULE_DIR_ . '/cetelem/views/js/calc.js');
        else
            $this->addJS('modules/cetelem/views/js/calc.js');
    }*/

    public function initContent()
    {
        parent::initContent();

        $id_language = Tools::strtoupper($this->context->language->iso_code);
        $amount = 0;


        //Displayed months for the campaign limit date
        if (Configuration::get('CETELEM_CAMPAIGN_ON')) {
            $january = $this->module->l('January', 'calculator');
            $february = $this->module->l('February', 'calculator');
            $march = $this->module->l('March', 'calculator');
            $april = $this->module->l('April', 'calculator');
            $may = $this->module->l('May', 'calculator');
            $june = $this->module->l('June', 'calculator');
            $july = $this->module->l('July', 'calculator');
            $august = $this->module->l('August', 'calculator');
            $september = $this->module->l('September', 'calculator');
            $october = $this->module->l('October', 'calculator');
            $november = $this->module->l('November', 'calculator');
            $december = $this->module->l('December', 'calculator');
            $monthNames = array(
                $january,
                $february,
                $march,
                $may,
                $april,
                $june,
                $july,
                $august,
                $september,
                $october,
                $november,
                $december
            );
            //Campaign date limit text on calculator
            $limit = date_parse(Configuration::get('CETELEM_CAMPAIGN_DATE_LIMIT'));
            $day = $limit['day'];
            //Converting month number to month name
            $monthNumber = $limit['month'];
            $month = $monthNames[($monthNumber - 1)];
            $year = $limit['year'];
            if ($id_language == 'EN') {
                $limitDate = $day . ' ' . $month . ' ' . $year . '.';
            } else {
                $limitDate = $day . ' de ' . $month . ' de ' . $year . '.';
            }
        }

        if (Tools::isSubmit('amount')) {
            $amount = Tools::getValue('amount');
        } else {
            $amount = Context::getContext()->cart->getordertotal(true);
        }

        $string_interest_free = $this->module->l('Interest free');

        $min_amount = Configuration::get('CETELEM_MIN_AMOUNT');

        if (Configuration::get('CETELEM_CAMPAIGN_ON') && Configuration::get(
            'CETELEM_CAMPAIGN_TIN_INDEX_' . $id_language
        ) != '') {
            $this->context->smarty->assign(
                array(
                    'text_color' => Configuration::get('CETELEM_TEXT_COLOR'),
                    'background_color' => Configuration::get('CETELEM_BACKGROUND_COLOR'),
                    'border_color' => Configuration::get('CETELEM_BORDER_COLOR'),
                    'fee_color' => Configuration::get('CETELEM_FEE_COLOR'),
                    'amount' => $amount > $min_amount ? $amount : $min_amount,
                    'info_text' => htmlspecialchars_decode(
                        Configuration::get('CETELEM_INFO_CALC_TEXT', $this->context->language->id)
                    ),
                    'tin' => Configuration::get('CETELEM_CAMPAIGN_TIN_INDEX_' . Tools::strtoupper($id_language)),
                    'tae' => Configuration::get('CETELEM_CAMPAIGN_TAE_INDEX_' . Tools::strtoupper($id_language)),
                    'comision' => Configuration::get(
                        'CETELEM_CSV_CAMPAIGN_COMISION_' . Tools::strtoupper($id_language)
                    ),
                    'texto_calc' => Configuration::get(
                        'CETELEM_CAMPAIGN_TEXTO_CALC_' . Tools::strtoupper($id_language)
                    ),
                    'fecha' => $limitDate,
                    'ej_meses' => (int)(Configuration::get(
                        'CETELEM_CAMPAIGN_EJ_MESES_' . Tools::strtoupper($id_language)
                    )),
                    //'array_months' => $this->module->getDisplayMonthsArray(true),
                    'free_financing_string' => $string_interest_free,
                    'campaign_txt' => Configuration::get(
                        'CETELEM_CAMPAIGN_TEXTO_PAGO_' . Tools::strtoupper($id_language)
                    ),
                )
            );
        } else {
            $this->context->smarty->assign(
                array(
                    'text_color' => Configuration::get('CETELEM_TEXT_COLOR'),
                    'background_color' => Configuration::get('CETELEM_BACKGROUND_COLOR'),
                    'border_color' => Configuration::get('CETELEM_BORDER_COLOR'),
                    'fee_color' => Configuration::get('CETELEM_FEE_COLOR'),
                    'amount' => $amount > $min_amount ? $amount : $min_amount,
                    'info_text' => htmlspecialchars_decode(
                        Configuration::get('CETELEM_INFO_CALC_TEXT', $this->context->language->id)
                    ),
                    'tin' => Configuration::get('CETELEM_NORMAL_TIN'),
                    'tae' => Configuration::get('CETELEM_NORMAL_TAE'),
                    'comision' => 0,
                    'fecha' => Configuration::get('CETELEM_NORMAL_FECHA_VALIDEZ'),
                    'ej_meses' => 0,
                    //'array_months' => $this->module->getDisplayMonthsArray(false),
                    'free_financing_string' => $string_interest_free,
                    'campaign_txt' => Configuration::get(
                        'CETELEM_CAMPAIGN_TEXTO_PAGO_' . Tools::strtoupper($id_language)
                    ),
                )
            );
        }
        $this->context->smarty->assign(
            array(
                'min_amount' => $min_amount
            )
        );
        return $this->setTemplate('calculator.tpl');
    }
}
