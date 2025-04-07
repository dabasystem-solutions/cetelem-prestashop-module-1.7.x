/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(window).load(function () {
    initial_var_price_set = true;
    initial_price = parseFloat(productPriceC);
    //initial_base_price = parseFloat(productPriceCbase);
    //On amount's input changes

    if (typeof priceWithDiscountsDisplay == 'undefined') {
        priceWithDiscountsDisplay = productPriceC;
        initial_var_price_set = false;
    }

    getCetelemCombPrice(false);

// if(cetelem_months !== false) {
    listadoMeses = cetelem_months;
// }

    codCentro = center_code;//

    totalpriceCetelem = Math.round(priceWithDiscountsDisplay * 100, 2) / 100;//

    cantidad = '' + totalpriceCetelem;//

    last_cantidad = cantidad;//

    server = server_url_cetelem;//

    color = text_color_cetelem;//

    bloquearImporte = cetelem_amount_block;//FALTA

    fontSize = font_size_cetelem;//

    if(calc_type!='')
    	$('body').append("<script type=\"text\/javascript\" src=\"" + server + calc_type + "\" async><\/script>");

    /*if ($('.current-price').length > 0)
    {
        $("body").on('DOMSubtreeModified', ".our_price_display", function() {
            refreshCetelem();
            console.log('changed1');
        });
        $("body").on('DOMSubtreeModified', ".product-actions", function() {
            //refreshCetelem();
            console.log('changed1');
        });
        $('body').on('change', '.product-variants [data-product-attribute]', function () {
            //(0, _jquery2['default'])("input[name$='refresh']").click();
            //refreshCetelem();
            console.log('changed2');
        });
    }*/
});

function getcurrentpriceCetelem() {
    if (typeof priceWithDiscountsDisplay !== undefined) {
        priceWithDiscountsDisplay = Math.round(priceWithDiscountsDisplay * 100, 2) / 100;
        cetelemUpdatedPrice = 0;
        if (last_cantidad == priceWithDiscountsDisplay) {
            if (selectedCombination !== undefined && !initial_var_price_set) {
                getCetelemCombPrice(false);
            }
            priceWithDiscountsDisplay = Math.round(priceWithDiscountsDisplay * 100, 2) / 100;
        }
        return Math.round(priceWithDiscountsDisplay * 100, 2) / 100;
    }
}

function refreshCetelem() {
    //totalpriceCetelem = getcurrentpriceCetelem(false);
    totalpriceCetelem = priceWithDiscountsDisplay;
    cantidad = '' + totalpriceCetelem;

    //we commented the condition due 1.7 remove always the content of this hook (productbuttons) so we have to load again the script, always
    //if (last_cantidad !== cantidad)
    //{
    $('body').append("<script type=\"text\/javascript\" src=\"" + server + calc_type + "\" async><\/script>");
    last_cantidad = cantidad;
    //}
}

function getCetelemCombPrice(newprice) {
    /*if ($('#idCombination').length > 0)
    {
        var cetelemCombID = parseFloat($('#idCombination').val());
        if(isNaN(cetelemCombID))
        {
            cetelemCombID = 0;
        }
        priceWithDiscountsDisplay = parseFloat(cetelemCombPrices[cetelemCombID]);
    }*/
    //console.log(priceWithDiscountsDisplay);
    if (newprice) {
        priceWithDiscountsDisplay = newprice;
    } else {
        if ($('body').find('.current-price span[itemprop="price"]').length > 0) {
            priceWithDiscountsDisplay = $('body').find('.current-price span[itemprop="price"]').attr("content");
        }
    }
}

$(function () {
    /*$(document).on('change', '.product-variants [data-product-attribute]', function (event) {
        var query = $(event.target.form).serialize() + '&ajax=1&action=productrefresh';
        var actionURL = $(event.target.form).attr('action');
        console.log($(event.target));
        $.post(actionURL, query, null, 'json').then(function (resp) {
            var productUrl = resp.productUrl;
            $.post(productUrl, {ajax: '1',action: 'refresh' }, null, 'json').then(function (resp) {
                var idProductAttribute = resp.id_product_attribute;
 
                // your own code to perform some action on combination change
                //
                //
                console.log(idProductAttribute);
            });
        });
    });*/
    //var resp_ = 1;
    /*prestashop.emit(
      'product updated',
      {
        //dataForm: someSelector.serializeArray(),
        //productOption: 3
        resp : resp_
      }
    );*/
    prestashop.on('updatedProduct', function (event) {
        //console.log(event.product_prices);
        var newprices = $(event.product_prices);
        var newprice = newprices.find('.current-price span[itemprop="price"]').attr("content");
        if (typeof newprice == "undefined") {
            newprice = newprices.find('.product-price[itemprop="price"]').attr("content");
        }
        getCetelemCombPrice(newprice);
        refreshCetelem();
        /*console.log(event.reason.productUrl);
        console.log('updateddddddddddd');
        console.log(prestashop);
        console.log(prestashop.updatedProduct);

        var query = $(event.target).serialize() + '&ajax=1&action=productrefresh';
        var actionURL = event.reason.productUrl;
        console.log(query);
        console.log($(event.target));
        $.post(actionURL, query, null, 'json').then(function (resp) {
            var productUrl = resp.productUrl;
            $.post(productUrl, {ajax: '1',action: 'refresh' }, null, 'json').then(function (resp) {
                var idProductAttribute = resp.id_product_attribute;
 
                // your own code to perform some action on combination change
                //
                //
                console.log(idProductAttribute);
            });
        });*/


    });
});