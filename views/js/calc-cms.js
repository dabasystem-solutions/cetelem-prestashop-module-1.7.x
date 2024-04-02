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
$(function () {
    if ($('#cetelem-financiacion').length) {
       var typeCetelem = $('#cetelem-financiacion').html();
        $('#cetelem-financiacion').html('');
        if(typeCetelem=='Cetelem')
        	postscribe('#cetelem-financiacion', "<script  id=\"cetelem_texts\" partner=\""+center_code+"\" ecredit=\"1\"  src=\"" + url_cms + "\" ><\/script>");
        else if(typeCetelem=='Encuotas')
        	postscribe('#cetelem-financiacion', "<script  id=\"cetelem_texts\" partner=\""+center_code+"\" encuotas=\"1\" src=\"" + url_cms + "\" ><\/script>");
        else if(typeCetelem=='Cetelem,Encuotas')
        	postscribe('#cetelem-financiacion', "<script  id=\"cetelem_texts\" partner=\""+center_code+"\" ecredit=\"1\" encuotas=\"1\" src=\"" + url_cms + "\" ><\/script>");
        
        
        
        	//postscribe('#cetelem-financiacion', "<script type=\"text\/javascript\" src=\"" + url_cms + center_code + "\" ><\/script>");
    }
    if ($('#cetelem-financiacion-plain').length) {
        $('#cetelem-financiacion-plain').html('');
        postscribe('#cetelem-financiacion-plain', "<script type=\"text\/javascript\" src=\"" + url_cms_plain + center_code + "\" ><\/script>");
    }
});