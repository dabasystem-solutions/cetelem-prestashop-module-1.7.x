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
<style type="text/css">
    {literal}
    div#eCalculadoraCetelemDiv *, #financiacionCtl, #importeMensualidadCtl, #infoFinanciacionCtl, #infoTextCalculator *, .label_financiacion, .label_cuota {
        color: {/literal}{$text_color} !important;
    {literal}
    }

    div.e_backgroundColorCtl, div.rangoCtl.e_backgroundColorIzdoCtl, div.rangoCtl.info {
        background-color: {/literal}{$background_color} !important;
    {literal};
        border: 1px solid {/literal}{$border_color} !important;
    {literal};
    }

    div.bloqueCuotaCtl {
        background-color: {/literal}{$background_color} !important;
    {literal};
    }

    div#infoFinanciacionCtl * {
        color: {/literal}{$fee_color} !important;
    {literal}
    }

    {/literal}
</style>
<div class="clearfix"></div>
<div id="calc_container">
    {if $calc_show eq 0}
    <div id="eCalculadoraCetelemDiv"></div>
    {else if $calc_show eq 1}
    <div id="eCalculadoraCetelemEnCuotas"></div>
    {else if $calc_show eq 3}
    <div id="eCalculadoraCetelemEnCuotas"></div>
    <div id="eCalculadoraCetelemDiv"></div>
    
    {else}
    <div id="eCalculadoraCetelemDiv"></div>
    {/if}
</div>
<div class="clearfix"></div>
{if isset($info_text) && $info_text != ''}
    <section id='infoTextCalculator' class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        {$info_text|cleanHtml nofilter}
    </section>
{/if}
<div class="clearfix"></div>
