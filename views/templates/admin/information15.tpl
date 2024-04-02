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

<div id="module-content" class="clearfix">

    <div class="tab-content col-lg-12">

        <div class="row">
            <div class="panel panel-success">
                <h3 class="panel-heading"><i class="icon icon-credit-card"></i> {l s='Cetelem' mod='cetelem'}</h3>
            </div>
            <div class="col-xs-6 col-md-4">
                <p><strong>{l s='Now your customers can buy and with a credit!' mod='cetelem'}</strong></p>
                <p>
                    {l s='Please fill the information below' mod='cetelem'}
                </p>
            </div>
            <div class="col-xs-12 col-md-4 pull-right text-right">
                <img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo-229x130.png"
                     class="col-xs-6 col-md-4 pull-right" id="payment-logo"/>
            </div>
        </div>
        {$connection_data}
        {$config_data}
        {*$campaign*}
    </div>
</div>
{literal}
    <script src="../modules/cetelem/views/js/bootstrap.min.js"></script>
{/literal}
