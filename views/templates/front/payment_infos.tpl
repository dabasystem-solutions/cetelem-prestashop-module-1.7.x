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

<section>
    <p>
    <dl>
        {*<p>{$text_payment}</p>*}
       
        <div class="row">
            
            <div class="col-xs-12 col-sm-6 sameheight1">
                <p>
                <div id="calc_container">
                 {if isset($encuotas) && $encuotas}
                     <div id="eCalculadoraCetelemEnCuotas"></div>
                  {else}
                  	<div id="eCalculadoraCetelemDiv"></div>   
                  {/if}  
                </div>
                </p>
            </div>
            
            {*<div class="col-xs-12 col-sm-6 sameheight2">
                <p class="info">
                        {$name_payment|escape:'html':'UTF-8'}
                </p>*}
                {*<hr>*}
               {* <p class="text">
                    <span>{l s='Y si ya eres cliente de Encuotas y tienes disponible en tu línea de crédito, aplazar del pago de tu compra será' mod='cetelem'}</span>
                    <span style="text-decoration:underline;">{l s='inmediato' mod='cetelem'}</span>
                </p>
            </div>*}
        </div>
        
       {* <dt>{l s='Amount' mod='cetelem'}</dt>
        <dd>{$checkTotal}</dd>
        <dt>{l s='You will be redirect to Cetelem platform to finish the payment' mod='cetelem'}</dt>*}
    </dl>
    </p>
</section>

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
{if $addCetelemScript}
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
    
    material = "{$material}";

    //$(window).load(function() {
    //$('body').append("<script type=\"text\/javascript\" src=\""+server+calc_type+"\" async><\/script>");
    //console.log($('.sameheight1').height());
    //});
    //console.log(calc_type);
    //var node = document.createElement("<script type=\"text\/javascript\" src=\""+server+calc_type+"\" async><\/script>");                 // Create a <li> node
    //document.getElementById("checkout").appendChild(node);
    //var cetelem_script = "<script type=\"text\/javascript\" src=\""+server+calc_type+"\" async><\/script>";
    var cetelem_script = server + calc_type;
    var head = document.getElementsByTagName('body')[0];
    var cetelem_script_final = document.createElement('script');
    cetelem_script_final.type = 'text/javascript';
    cetelem_script_final.src = cetelem_script;
    head.appendChild(cetelem_script_final);
</script>
{/if}
