{*
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module {if isset($name_payment)}campaign-on{/if}" id="cetelem_payment_button">
            <a href="{$link->getModuleLink('cetelem','payment',array(),Configuration::get('PS_SSL_ENABLED'))}"
               title="{l s='Buy with a Cetelem credit' mod='cetelem'}">
                {if isset($name_payment)}
                    {$name_payment|escape:'html':'UTF-8'}
                {else}
                    {l s='Pay in easy installments: Cetelem Finance your purchase' mod='cetelem'}
                {/if}
            </a>
        </p>
    </div>
</div>
