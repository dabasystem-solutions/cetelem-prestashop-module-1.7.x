/*
* 2007-2018 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
$(document).ready(function(){
	$(document).on('click', '#confirm_cetelem_order_controller', function(e){
		e.preventDefault();
		ajaxCetelem.createOrderCampaign();
	});
	$(document).on('click', '#confirm_cetelem_order', function(e){
		e.preventDefault();
		ajaxCetelem.createOrder();
	});
});

//JS Object : update the cart by ajax actions
var ajaxCetelem = {
	nb_total_products: 0,
	// Fix display when using back and previous browsers buttons
	createOrderCampaign : function(){
		$.ajax({
			type: 'POST',
			headers: { "cache-control": "no-cache" },
			url: baseUri + '?rand=' + new Date().getTime(),
			async: true,
			cache: false,
			dataType : "json",
			data: 'fc=module&module=cetelem&controller=preorder&ajax=true&token=' + static_token,
			success: function(res)
			{
				/*alert(res.albaran);
				console.log(res.albaran);*/
				$('#cetelem_albaran').val(res.albaran);
//				console.log($('#cetelem_albaran').val());
				$('#cetelem_form-campaign').submit();
			}
		});
	},
        createOrder : function(){
		$.ajax({
			type: 'POST',
			headers: { "cache-control": "no-cache" },
			url: baseUri + '?rand=' + new Date().getTime(),
			async: true,
			cache: false,
			dataType : "json",
			data: 'fc=module&module=cetelem&controller=preorder&ajax=true&token=' + static_token,
			success: function(res)
			{
				/*alert(res.albaran);
				console.log(res.albaran);*/
				$('#cetelem_albaran').val(res.albaran);
			//console.log($('#cetelem_albaran').val());
				$('#cetelem_form').submit();
			}
		});
	}
};