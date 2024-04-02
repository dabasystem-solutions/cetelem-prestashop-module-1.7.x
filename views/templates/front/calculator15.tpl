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
{capture name=path}{l s='Financing' mod='cetelem'}{/capture}
<section id="calcPageCetelem">
    <header>
        <h1 class="page-heading">{l s='Calculate your monthly fee' mod='cetelem'}</h1>
    </header>
    {if isset($info_text) && $info_text != ''}
        <section id='infoTextCalculator' class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            {$info_text}
        </section>
    {/if}
    <div id="cete_total"
         style="border: 1px solid #d6d4d4;padding-right: 15px;padding-top: 5px;padding-left: 5px;padding-bottom:5px;">
        <section class="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-1 col-lg-10">
            <div class="row gris">
                <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                    <p style="font-weight:bold;">{l s='How much would you fund?' mod='cetelem'}</p>
                    <span>{l s='Total amount to be financed' mod='cetelem'}</span>
                </div>
                <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                    <input type="text" name="totalAmount" id="totalAmount"
                           value="{$amount|round:2|floatval}"/><span> €</span>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                    <p style="font-weight:bold;">{l s='How many months you want to pay?' mod='cetelem'}</p>
                    <span>{l s='Duration of funding' mod='cetelem'}</span>
                </div>
                <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                    <select name="mesesFinanciacion" id="mesesFinanciacion"></select>
                    <span style="margin-left:10px;">{l s='months' mod='cetelem'}</span>
                </div>
            </div>
        </section>
        <section id="calculateSection" class="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-1 col-lg-10">
            <button id="calculateButton">{l s='Calculate fee' mod='cetelem'}</button>
            <p>
                <span>{l s='TIN:' mod='cetelem'}</span>
                <span id="tinactual"></span>
                <span>{l s='TAE:' mod='cetelem'}</span>
                <span id="taeactual"></span>
            </p>
        </section>
        <section class="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-1 col-lg-10">
            <div class="row gris">
                <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                    <p style="font-weight:bold;">{l s='Monthly payment:' mod='cetelem'}</p>
                    <span>{l s='What you pay each month' mod='cetelem'}</span>
                </div>
                <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                    <input type="text" id="cuotaMensual" name="cuotaMes" readonly="readonly"><span> €</span>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                    <p>{l s='Last payment:' mod='cetelem'}</p>
                    <span>{l s='Import last payment' mod='cetelem'}</span>
                </div>
                <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                    <input type="text" id="amountLastPayment" name="amountLastPayment"
                           readonly="readonly"><span> €</span>
                </div>
            </div>
            <div class="row gris">
                <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                    <p style="font-weight:bold;">{l s='Total cost:' mod='cetelem'}</p>
                    <span id="messageByTin">{l s='Of financing' mod='cetelem'}</span>
                </div>
                <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                    <input type="text" id="costeTotal" name="cuotaMes" readonly="readonly"><span> €</span>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                    <p style="font-weight:bold;">{l s='Total to pay:' mod='cetelem'}</p>
                    <span>{l s='Total amount due' mod='cetelem'}</span>
                </div>
                <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                    <input type="text" id="impAdeudado" name="cuotaMes" readonly="readonly"><span> €</span>
                </div>
            </div>
        </section>
    </div>
    <section id="legalInfo" class="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-1 col-lg-10">
        <p id="infoBanco">
            {l s='Financing offered by' mod='cetelem'} Banco Cetelem SA&nbsp;
            {l s='after studying the documentation and signing of contract.' mod='cetelem'}&nbsp;

            <span>{l s='Date of validity of the offer until' mod='cetelem'} </span><span id="fechaLimite"></span>

        </p>
    </section>
</section>
{literal}
<script>
    amount = parseFloat({/literal}{$amount|round:2|floatval}{literal}).toFixed(2);
    //array_months = '{/literal}{$array_months|@json_encode}{literal}';
    free_financing_string = '{/literal}{$free_financing_string}{literal}';
    messageByTin0 = '{/literal}{l s='Fee of formalitzation' mod='cetelem'}{literal}';
    messageByTin1 = '{/literal}{l s='Of financing' mod='cetelem'}{literal}';
</script>
{/literal}
