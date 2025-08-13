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

        <div class="faq-section mb-4">
    <div class="faq-section-header mb-4">
        <div class="d-flex align-items-center faq-header-inner">
           
            <h3 class="faq-title"><i class="icon-gear"></i> {l s='Frequently Asked Questions' mod='cetelempayment'}</h3>
        </div>
    </div>

    <div class="accordion" id="faqAccordion">
        <div class="card mb-3">
            <div class="card-header bg-white" id="headingOne">
                <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                        data-target="#faq1" aria-expanded="false" aria-controls="faq1"
                        style="font-weight: 500; color: #007bff;">
                    {l s='What is Cetelem and how does it work?' mod='cetelempayment'}
                </button>
            </div>
            <div id="faq1" class="collapse" aria-labelledby="headingOne" data-parent="#faqAccordion">
                <div class="card-body">
                    {l s='Cetelem allows customers to finance their purchases. The order is automatically validated after approval.' mod='cetelempayment'}
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white" id="headingTwo">
                <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                        data-target="#faq2" aria-expanded="false" aria-controls="faq2"
                        style="font-weight: 500; color: #007bff;">
                    {l s='When is the invoice generated?' mod='cetelempayment'}
                </button>
            </div>
            <div id="faq2" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                <div class="card-body">
                    {l s='The invoice is automatically generated when the financing is approved and the order status is updated.' mod='cetelempayment'}
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white" id="headingThree">
                <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                        data-target="#faq3" aria-expanded="false" aria-controls="faq3"
                        style="font-weight: 500; color: #007bff;">
                    {l s='What is a pickup point?' mod='cetelempayment'}
                </button>
            </div>
            <div id="faq3" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                <div class="card-body">
                    {l s='The pickup point corresponds to the carrier selected as the delivery location. Therefore, it is not considered home delivery.' mod='cetelempayment'}
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header bg-white" id="headingThree">
                <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                        data-target="#faq4" aria-expanded="false" aria-controls="faq4"
                        style="font-weight: 500; color: #007bff;">
                    {l s='What is the purpose of Creating Order when accessing the Cetelem Environment?' mod='cetelempayment'}
                </button>
            </div>
            <div id="faq4" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                <div class="card-body">
                    {l s='This option allows an order to be created as soon as a customer selects Cetelem as their payment method and is redirected to the Cetelem environment.' mod='cetelempayment'}
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white" id="headingThree">
                <button class="btn btn-link w-100 text-left collapsed" type="button" data-toggle="collapse"
                        data-target="#faq5" aria-expanded="false" aria-controls="faq4"
                        style="font-weight: 500; color: #007bff;">
                    {l s='the calculator is not showing on the product page?' mod='cetelempayment'}
                </button>
            </div>
            <div id="faq5" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                <div class="card-body">
                   {l s="If the calculator is not displayed on the product page, make sure your theme has the hooks or has the module inserted in the hooks: DisplayReassurance or ProductAdditionalInfo, otherwise you can add a custom hook in your theme: {'{hook h=\'displayCalculadoraCetelem\'}'}" mod='cetelempayment'}
                </div>
            </div>
        </div>

    </div>
</div>



    </div>
</div>
<style>
    .faq-section {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 2rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }

    .faq-section .card {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .faq-section .card-header {
        background-color: #f1f3f5;
        padding: 1rem 1.25rem;
    }

    .faq-section .btn-link {
        font-size: 1rem;
        font-weight: 600;
        text-decoration: none;
        color: #343a40;
    }

    .faq-section .btn-link:hover {
        color: #0056b3;
    }

    .faq-section .card-body {
        background-color: #ffffff;
        padding: 1rem 1.25rem;
        border-top: 1px solid #dee2e6;
    }

    .faq-section .faq-header-inner {
        border-bottom: 2px solid #007bff !important;
        padding-bottom: 10px !important;
    }

    .faq-section .faq-icon {
        font-size: 1.8rem !important;
        color: #007bff !important;
        margin-right: 10px !important;
    }

    .faq-section .faq-title {
        font-weight: 600 !important;
        font-size: 1.5rem !important;
        color: #333 !important;
        margin: 0 !important;
        border-bottom: none !important;
    }.faq-title i {
  margin-top: -2px;
  margin-right: 1px;
  font-size: 25px;
  color: #6c868e;
}
</style>