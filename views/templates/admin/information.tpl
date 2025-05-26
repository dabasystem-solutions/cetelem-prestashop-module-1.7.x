{*
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
*}

<div id="module-content" class="clearfix">
    <div class="tab-content col-lg-12">
        <div class="panel panel-default shadow-sm rounded p-4 mb-4">
            <div class="panel-heading d-flex justify-content-between align-items-center mb-3"
                style="border-bottom: 1px solid #ddd;">

                <img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo-229x130.png" alt="Cetelem Logo"
                    class="img-fluid" style="max-height: 80px;">
            </div>

            <div class="row">
                <div class="col-md-8">
                    <p class="lead mb-2">
                        <h2>{l s='Now your customers can buy and pay with a credit!' mod='cetelempayment'}</h2>
                    </p>
                    <p class="text-muted">
                        <h4>{l s='Please fill the information below' mod='cetelempayment'}</h4>
                    </p>
                     
                </div>
                <div class="col-md-4">
                <img src="{$module_dir|escape:'html':'UTF-8'}views/img/imagen_cetelem.png" alt="Cetelem Logo"
                    class="img-fluid" style="max-height: 150px;">
                </div>
            </div>
        </div>

        {$connection_data}
        {$config_data}
    </div>
</div>