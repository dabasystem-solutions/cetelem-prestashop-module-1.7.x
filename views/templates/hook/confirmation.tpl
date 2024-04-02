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

{if $status == 'ok'}
    <section id="aprovedblock" class="container">
        <h3 class="col-md-10 col-md-offset-1">{l s='Thank you for making your request for funding in our shop %s.' sprintf=[$shop.name] mod='cetelem'}</h3>
        <div class="clearfix"></div>
        <p class="col-md-8 col-md-offset-2">
            {l s='An email has been sent with this information.' mod='cetelem'}
            <br/><br/>{l s='If you have questions, comments or concerns, please contact our' mod='cetelem'}<br/>
            <a href="{$urls.pages.contact}">{l s='expert customer support team.' mod='cetelem'}</a>
        </p>
    </section>
{/if}
