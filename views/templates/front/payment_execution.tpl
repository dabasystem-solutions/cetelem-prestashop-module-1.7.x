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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2021 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='cetelem'}">{l s='Checkout' mod='cetelem'}</a><span class="navigation-pipe">></span>{l s='Cetelelm payment' mod='cetelem'}
{/capture*}

{*assign var='current_step' value='payment'*}
{*include file="$tpl_dir./order-steps.tpl"*}
{*<div class="col-xs-12 cetelem-confirmation">
    <p class="info">
        {$name_payment|escape:'html':'UTF-8'}
    </p>
    <hr>
    <p class="text">
        {$text_payment|escape:'html':'UTF-8'}
    </p>
</div>
<br />*}
<h3 id="confirm_cetelem_order_controller">{l s='You are getting redirect to Cetelem platform, please, wait a moment' mod='cetelem'}</h3>
{*<p class="cart_navigation clearfix">
    {if Configuration::get('CETELEM_ORDER_CREATION')}
        <a id="confirm_cetelem_order_controller" title="Financiación con Cetelem" class="button btn btn-default standard-checkout button-medium" title="{l s='Pay with Cetelem' mod='cetelem'}">
    {else}
        <a href="javascript:$('#cetelem_form-campaign').submit();" title="Financiación con Cetelem" class="button btn btn-default standard-checkout button-medium" title="{l s='Pay with Cetelem' mod='cetelem'}">
    {/if}
        <span>{l s='Pay with Cetelem' mod='cetelem'}<i class="icon-chevron-right right"></i></span>
    </a>
     <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive btn btn-default" title="{l s='Other payment methods' mod='cetelem'}">
            <i class="icon-chevron-left"></i>{l s='Other payment methods' mod='cetelem'}
    </a>
</p>*}

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
    {if $albaran}
        <input type="hidden" id="cetelem_albaran_new" name="Albaran" value="{$albaran}"/>
    {/if}
</form>
{if $albaran || $mode == 'N'}
    <script type="text/javascript">document.getElementById("cetelem_form").submit();</script>
{/if}

<style type="text/css">
    .sameheight1, .sameheight2 {
        min-height: 110px;
        display: table;
    }

    .sameheight2 p {
        display: table-cell;
        vertical-align: middle;
    }
</style>

<script type="text/javascript">

    center_code = '{$center_code}';

    codCentro = center_code;//

    totalpriceCetelem = {$total_price};//Math.round(priceWithDiscountsDisplay * 100, 2) / 100;//

    cantidad = '' + totalpriceCetelem;//

    last_cantidad = cantidad;//

    server = '{$server_url_cetelem}';//'https://www.cetelem.es';//server_url_cetelem;//

    color = '{$color}';//'#000000';//text_color_cetelem;//

    bloquearImporte = false,//cetelem_amount_block;//FALTA

        fontSize = {$fontSize};//12;//font_size_cetelem;//

    calc_type = '{$calc_type}';//'/eCommerceCalculadora/resources/js/eCalculadoraCetelemCombo.js';

    $(window).load(function () {
        $('body').append("<script type=\"text\/javascript\" src=\"" + server + calc_type + "\" async><\/script>");
    });
</script>