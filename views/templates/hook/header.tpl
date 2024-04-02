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
<script type="text/javascript">
    var server_url_cetelem = '{$server_url_cetelem|escape:'html':'UTF-8'}';
    var center_code = '{$center_code|escape:'html':'UTF-8'}';
    var codCentro = '{$center_code|escape:'html':'UTF-8'}';
    var calc_type = '{$calc_type|escape:'html':'UTF-8'}';
    var text_color_cetelem = '{$text_color_cetelem|escape:'html':'UTF-8'}';
    var font_size_cetelem = '{$font_size_cetelem|escape:'html':'UTF-8'}';
    var cetelem_amount_block = '{$cetelem_amount_block|escape:'html':'UTF-8'}';
    {if isset($productPriceC)}
    var productPriceC = '{$productPriceC|escape:'html':'UTF-8'|string_format:"%.2f"}';
    
    {/if}
    {if isset($cetelemCombPrices) && !empty($cetelemCombPrices)}
    var cetelemCombPrices = [];
    {foreach from=$cetelemCombPrices item='cprice'}
    cetelemCombPrices.push({$cprice});
    {/foreach}
    {else}
    var cetelemCombPrices = false;
    {/if}
    {if isset($months_cetelem_display) && !empty($months_cetelem_display)}
    var cetelem_months ={$months_cetelem_display};
    {else}
    var cetelem_months = false;
    {/if}
    var material = "{$material}";
</script>