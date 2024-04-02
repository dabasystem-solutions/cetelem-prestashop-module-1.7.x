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
        <p class="payment_module" id="cetelem_payment_button">
            {if Configuration::get('CETELEM_ORDER_CREATION')}
            <a id="confirm_cetelem_order" href="ajaxCetelem.createOrder();"
               title="{l s='Buy with a Cetelem credit' mod='cetelem'}">
                {else}
                <a href="javascript:$('#cetelem_form').submit();" href="ajaxCetelem.createOrder();"
                   title="{l s='Buy with a Cetelem credit' mod='cetelem'}">
                    {/if}
                    {if isset($name_payment) && $name_payment != '' && $mode == 'G'}
                        {*if $onepagecheckoutps_enabled}
                            <strong class="clearfix">{l s='Pay in easy installments:' mod='cetelem'}</strong>
                             {$name_payment|escape:'html':'UTF-8'}
                        {else*}
                        {$name_payment|escape:'html':'UTF-8'}
                        {*/if*}
                    {else}
                        {l s='Pay in easy installments: Cetelem Finance your purchase' mod='cetelem'}
                    {/if}
                    {if isset($text_payment) && $mode == 'G'}<span
                            id="extraInfoPayment">{$text_payment|escape:'html':'UTF-8'}</span>{/if}
                </a>
        </p>

        <form action="{$conex_url}" method="post" name="cetelem_form" id="cetelem_form">
            <input type="hidden" name="COMANDO" value="INICIO"/>
            <input type="hidden" name="Material" value="499"/>
            {*if $mode == 'G'}
                <input type="hidden" name="CodProducto" value="PMG" />
            {else}
                <input type="hidden" name="CodProducto" value="PM" />
            {/if*}
            <input type="hidden" name="IdTransaccion" value="{$transact_id}"/>
            <input type="hidden" name="CodCentro" value="{$center_code}"/>
            <input type="hidden" name="Importe" value="{$amount}"/>
            <input type="hidden" name="Modalidad" value="{$mode}"/>
            <input type="hidden" name="ReturnOK" value="{$url_ok}"/>
            <input type="hidden" name="ReturnURL" value="{$url}"/>
            {if $gender && $gender != ''}
                <input type="hidden" name="Tratamiento" value="{$gender}"/>
            {/if}
            {if $firstname && $firstname != ''}
                <input type="hidden" name="Nombre" value="{$firstname}"/>
            {/if}
            {if $lastname && $lastname != ''}
                <input type="hidden" name="Apellidos" value="{$lastname}"/>
            {/if}
            {if $dni && $dni != ''}
                <input type="hidden" name="NIF" value="{$dni}"/>
            {/if}
            {if $birthday && $birthday != ''}
                <input type="hidden" name="FechaNacimiento" value="{$birthday}"/>
            {/if}
            {if $address && $address != ''}
                <input type="hidden" name="Direccion" value="{$address}"/>
            {/if}
            {if $city && $city != ''}
                <input type="hidden" name="Localidad" value="{$city}"/>
            {/if}
            {if $zip && $zip != ''}
                <input type="hidden" name="CodPostal" value="{$zip}"/>
            {/if}
            {if $email && $email != ''}
                <input type="hidden" name="Email" value="{$email}"/>
            {/if}
            {if $phone1 && $phone1 != ''}
                <input type="hidden" name="Telefono1" value="{$phone1}"/>
            {/if}
            {if $phone2 && $phone2 != ''}
                <input type="hidden" name="Telefono2" value="{$phone2}"/>
            {/if}
            <input type="hidden" id="cetelem_albaran" name="Albaran" value="0"/>
        </form>
    </div>
</div>
