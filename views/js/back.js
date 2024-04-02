/*
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
*/
$(document).ready(function () {
    $('#CETELEM_CAMPAIGN_DATE_LIMIT').addClass('campaign_depending');
    if ($('#CETELEM_DISPLAY_CALC_off').is(':checked')) {
        depending_classes('calc_depending');
    }
    if ($('#CETELEM_CAMPAIGN_ON_off').is(':checked')) {
        depending_classes('campaign_depending');
    }
//    $(document).on('click', '#configuration_form_submit_btn', function (e) {
//        e.preventDefault();
//        $('#configuration_form').submit();
//        $('#configuration_form_1').submit();
//        $('#configuration_form_2').submit();
//    });
//    
//    $(document).on('click', '#configuration_form_submit_btn_1', function (e) {
//        e.preventDefault();
//        $('#configuration_form').submit();
//        $('#configuration_form_1').submit();
//        $('#configuration_form_2').submit();
//    });
//    
//    $(document).on('click', '#configuration_form_submit_btn_2', function (e) {
//        e.preventDefault();
//        $('#configuration_form').submit();
//        $('#configuration_form_1').submit();
//        $('#configuration_form_2').submit();
//    });

    $(document).on('click', 'input[name=CETELEM_DISPLAY_CALC]', function (e) {

        //console.log($('#CETELEM_DISPLAY_CALC_off').is(':checked'));
        depending_classes('calc_depending');
        //$('#CETELEM_DISPLAY_CALC_off').val().toggle();
        //$('input[name=CETELEM_DISPLAY_CALC]').prop('checked', false);
    });
    $(document).on('click', 'input[name=CETELEM_CAMPAIGN_ON]', function (e) {

        //console.log($('#CETELEM_DISPLAY_CALC_off').is(':checked'));
        depending_classes('campaign_depending');
        //$('#CETELEM_DISPLAY_CALC_off').val().toggle();
        //$('input[name=CETELEM_DISPLAY_CALC]').prop('checked', false);
    });

});

function depending_classes(depending_class) {
    depending_class = '.' + depending_class;
    //if ($('#CETELEM_DISPLAY_CALC_off').is(':checked'))
    //{
    $(depending_class).parents('.form-group').toggle();
    //}
}