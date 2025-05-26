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
 * Class CetelemStates
 *
 * @author Alejandro Vázquez
 */
class CetelemStates
{
    public $name = 'cetelempayment';
    public function createCetelemPrepprovedState()
    {
        $tmp_o_state = new OrderState((int) Configuration::getGlobalValue('PS_OS_CETELEM_PREAPPROVED'));

        if (!Configuration::getGlobalValue('PS_OS_CETELEM_PREAPPROVED') || !$tmp_o_state->name) {
            $languages = Language::getLanguages(false);

            $names = [];
            foreach ($languages as $language) {
                $names[$language['id_lang']] = 'CETELEM - Crédito Preaprobado';
            }

            return $this->createCetelemState($names, 'PS_OS_CETELEM_PREAPPROVED', '#ffff00', 'cetelem-preapproved');
        }

        if ($tmp_o_state->color !== '#ffff00') {
            $tmp_o_state->color = '#ffff00';
            $tmp_o_state->update();
        }

        return true;
    }

    public function createCetelemStandByState()
    {
        $tmp_o_state = new OrderState((int) Configuration::getGlobalValue('PS_OS_CETELEM_STANDBY'));

        if (!Configuration::getGlobalValue('PS_OS_CETELEM_STANDBY') || !$tmp_o_state->name) {
            $languages = Language::getLanguages(false);

            $names = [];
            foreach ($languages as $language) {
                $names[$language['id_lang']] = 'CETELEM - Solicitud Pendiente';
            }

            return $this->createCetelemState($names, 'PS_OS_CETELEM_STANDBY', '#4169E1', 'cetelem-preapproved');
        }

        if ($tmp_o_state->color !== '#4169E1') {
            $tmp_o_state->color = '#4169E1';
            $tmp_o_state->update();
        }

        return true;
    }

    public function createCetelemApprovedState()
    {
        $tmp_o_state = new OrderState((int) Configuration::getGlobalValue('PS_OS_CETELEM_APPROVED'));

        if (!Configuration::getGlobalValue('PS_OS_CETELEM_APPROVED') || !$tmp_o_state->name) {
            $languages = Language::getLanguages(false);

            $names = [];
            foreach ($languages as $language) {
                $names[$language['id_lang']] = 'CETELEM - CREDITO FINANCIADO';
            }

            return $this->createCetelemState(
                $names,
                'PS_OS_CETELEM_APPROVED',
                '#32CD32',
                'cetelem-approved',
                true
            );
        }

        if ($tmp_o_state->color !== '#32CD32') {
            $tmp_o_state->color = '#32CD32';
            $tmp_o_state->update();
        }

        return true;
    }

    public function createCetelemDeniedState()
    {
        $tmp_o_state = new OrderState((int) Configuration::getGlobalValue('PS_OS_CETELEM_DENIED'));

        if (!Configuration::getGlobalValue('PS_OS_CETELEM_DENIED') || !$tmp_o_state->name) {
            $languages = Language::getLanguages(false);

            $names = [];
            foreach ($languages as $language) {
                $names[$language['id_lang']] = 'CETELEM - Crédito denegado';
            }

            return $this->createCetelemState($names, 'PS_OS_CETELEM_DENIED', '#DC143C', 'cetelem-denied');
        }

        if ($tmp_o_state->color !== '#DC143C') {
            $tmp_o_state->color = '#DC143C';
            $tmp_o_state->update();
        }

        return true;
    }

/**
* @param array  $name_translations
* @param string $config_variable
* @param string $state_color
* @param string $icon_name
* @param bool   $logable_false
*
* @return bool
*/
    public function createCetelemState(
        array $name_translations,
        string $config_variable,
        string $state_color,
        string $icon_name,
        bool $logable_false = false
    ) {
        $invoice = ($config_variable === 'PS_OS_CETELEM_APPROVED') ? 1 : 0;
        // Create orders
        $OrderState = new OrderState();
        $OrderState->name = $name_translations;
        $OrderState->send_email = false;
        $OrderState->invoice = $invoice;
        $OrderState->logable = $logable_false;
        $OrderState->color = $state_color;
        $OrderState->module_name = $this->name;

        if ($OrderState->add()) {
            Configuration::updateGlobalValue($config_variable, $OrderState->id);
            copy(
                _PS_MODULE_DIR_ . $this->name . '/views/img/' . $icon_name . '.gif',
                _PS_IMG_DIR_ . 'os/' . $OrderState->id . '.gif'
            );
        } else {
            return false;
        }

        return true;
    }

    public function StateCetelemApproved()
    {
        return $this->getValueState('PS_OS_CETELEM_APPROVED');
    }

    public function StateCetelemPreApproved()
    {
        return $this->getValueState('PS_OS_CETELEM_PREAPPROVED');
    }

    public function StateCetelemDenied()
    {
        return $this->getValueState('PS_OS_CETELEM_DENIED');
    }

    public function StateCetelemStanBy()
    {
        return $this->getValueState('PS_OS_CETELEM_STANDBY');
    }

    private function getValueState($value)
    {
        return Configuration::getGlobalValue($value);
    }
}
