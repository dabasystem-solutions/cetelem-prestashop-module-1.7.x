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
    <section class="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-1 col-lg-10">
        <div class="row gris">
            <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                <p>{l s='How much would you fund?' mod='cetelem'}</p>
                <span>{l s='Total amount to be financed' mod='cetelem'}</span>
            </div>
            <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                <input type="text" name="totalAmount" id="totalAmount"
                       value="{$amount|round:2|floatval}"/><span> €</span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                <p>{l s='How many months you want to pay?' mod='cetelem'}</p>
                <span>{l s='Duration of funding' mod='cetelem'}</span>
            </div>
            <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                <select name="mesesFinanciacion" id="mesesFinanciacion"></select>
                <span>{l s='months' mod='cetelem'}</span>
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
        <p class="warning cetelem_warning"
           style="display:none; color: red;">{l s='El total a financiar no puede ser menor que ' mod='cetelem'}{$min_amount}
            €</p>
    </section>
    <section class="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-1 col-lg-10">
        <div class="row gris">
            <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                <p>{l s='Monthly payment:' mod='cetelem'}</p>
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
                <input type="text" id="amountLastPayment" name="amountLastPayment" readonly="readonly"><span> €</span>
            </div>
        </div>
        <div class="row gris">
            <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                <p>{l s='Total cost:' mod='cetelem'}</p>
                <span id="messageByTin">{l s='Of financing' mod='cetelem'}</span>
            </div>
            <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                <input type="text" id="costeTotal" name="cuotaMes" readonly="readonly"><span> €</span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-8 col-sm-6 col-md-6 col-lg-6">
                <p>{l s='Total to pay:' mod='cetelem'}</p>
                <span>{l s='Total amount due' mod='cetelem'}</span>
            </div>
            <div class="col-xs-4 col-sm-6 col-md-6 col-lg-6 inputContainer">
                <input type="text" id="impAdeudado" name="cuotaMes" readonly="readonly"><span> €</span>
            </div>
        </div>
    </section>

    {*        <p id="infoBanco">
                {l s='Financing offered by' mod='cetelem'} Banco Cetelem SA&nbsp;
                {l s='after studying the documentation and signing of contract.' mod='cetelem'}&nbsp; 
            </p>
    *}
    <section id="legalInfo" class="col-xs-12 col-sm-12 col-md-offset-1 col-md-10 col-lg-offset-1 col-lg-10 hidden">
        <p class="hidden" id="textoCalc">{$texto_calc|escape:'html':'UTF-8'} <span id="minimalfinancialcampaign"></span>. {l s='Financing offered by' mod='cetelem'}
            Banco Cetelem SAU. </p>
        {*<p class="hidden" id="textoTinTae">{l s='TIN' mod='cetelem'} <span id="tinactual"></span> {l s='TAE' mod='cetelem'} <span id="taeactual"></span> <span id="minimalfinancial"></span></p>*}
        <p class="hidden" id="infoBanco">{l s='Financing offered by' mod='cetelem'} Banco Cetelem
            SAU. {l s='La primera mensualidad podrá ser superior o inferior al resto debido al ajuste de los intereses devengados en función de los días transcurridos desde la fecha de disposición y la fecha de primera mensualidad del préstamo.' mod='cetelem'}
            <span id="minimalfinancial"></span></p>
        <p>{l s='Date of validity of the offer until' mod='cetelem'} <span id="fechaLimite"></span></p>
    </section>
</section>
<script type="text/javascript">
    var amount ={$amount|round:2|floatval};
    //var array_months={$array_months|@json_encode};
    var free_financing_string ={$free_financing_string};
    var messageByTin0 ={l s='Fee of formalitzation' mod='cetelem'};
    var messageByTin1 ={l s='Of financing' mod='cetelem'};
    var min_amount ={$min_amount|round:2|floatval};
</script>

<div id="calc_container" style="display:none;">
    <div id="eCalculadoraCetelemDiv"></div>
</div>
<div class="clearfix"></div>
{if isset($info_text) && $info_text != ''}
    <section id='infoTextCalculator' class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        {$info_text}
    </section>
{/if}
<div class="cet-loader-container">
    <div class="cet-loader"></div>
    <span>{l s='Calculando...' mod='cetelem'}</span>
</div>