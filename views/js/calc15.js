/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(window).load(function () {
    $('body').append("<script type=\"text\/javascript\" src=\"" + server + calc_type + "\" async><\/script>");
});
$(document).ready(function () {
    //On amount's input changes

    codCentro = center_code;//

    totalpriceCetelem = Math.round(productPrice * 100, 2) / 100;//

    cantidad = '' + totalpriceCetelem;//

    last_cantidad = cantidad;//

    server = server_url_cetelem;//

    color = text_color_cetelem;//

    bloquearImporte = cetelem_amount_block;//

    //console.log('block is ' + bloquearImporte);

    fontSize = font_size_cetelem;//

    //codPromocion = cod_promocion_cetelem;

    if ($('#our_price_display').length > 0) {
        $("#our_price_display").change(function () {
            refreshCetelem();
        });
    }
});

function getcurrentpriceCetelem() {
    if (typeof productPrice !== undefined) {
        //console.log(productPrice);
        return Math.round(productPrice * 100, 2) / 100;
    }
}

function refreshCetelem() {
    totalpriceCetelem = getcurrentpriceCetelem();
    cantidad = '' + totalpriceCetelem;

    if (last_cantidad !== cantidad) {
        $('body').append("<script type=\"text\/javascript\" src=\"" + server + calc_type + "\" async><\/script>");
        last_cantidad = cantidad;
    }
}