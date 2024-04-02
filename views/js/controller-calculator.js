/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {

    //On amount's input changes
    $("button#calculateButton").click(function () {
        $('.cet-loader-container').fadeIn();
        refreshCetelem();
    });
    $('#mesesFinanciacion').on('change', function () {
        $('#financiacionCtl').val($('#mesesFinanciacion').val()).change();
        $('.cet-loader-container').fadeIn();
        setTimeout(function () {
            getMonthsPriceTimeoutFromSelect();
        }, 2200);
    });
});

$(window).load(function () {
    //getMonthsPrice();
    initializeCetelem();
});

function getMonthsPrice() {
    setTimeout(function () {
        getMonthsPriceTimeout();
    }, 2200);
}

function getMonthsPriceTimeout() {
    $('#mesesFinanciacion').html($('#financiacionCtl').html());
    $('#cuotaMensual').val($('#importeMensualidadCtl').html());
    $('#amountLastPayment').val($('#cetelemUltimaCuota').html());
    $('#impAdeudado').val($('#cetelemTotalAdeudado').html());
    $('#costeTotal').val($('#cetelemTotalFinanciar').html());
    $('#tinactual').html($('#tinCetelem').html());
    $('#taeactual').html($('.taeCetelem').html());
    $('.cet-loader-container').fadeOut();
}

function getMonthsPriceTimeoutFromSelect() {
    //$('#mesesFinanciacion').html($('#financiacionCtl').html());
    $('#cuotaMensual').val($('#importeMensualidadCtl').html());
    $('#amountLastPayment').val($('#cetelemUltimaCuota').html());
    $('#impAdeudado').val($('#cetelemTotalAdeudado').html());
    $('#costeTotal').val($('#cetelemTotalFinanciar').html());
    $('#tinactual').html($('#tinCetelem').html());
    $('#taeactual').html($('.taeCetelem').html());
    $('.cet-loader-container').fadeOut();
}

function getcurrentpriceCetelem() {
    if (typeof priceWithDiscountsDisplay !== undefined) {
        return Math.round(priceWithDiscountsDisplay * 100, 2) / 100;
    }
}

function refreshCetelem() {
    totalpriceCetelem = $('#totalAmount').val();
    if (totalpriceCetelem < min_amount) {
        $('.cetelem_warning').show();
        return;
    } else {
        $('.cetelem_warning').hide();
    }
    cantidad = '' + totalpriceCetelem;

    if (last_cantidad !== cantidad) {
        //$('body').append("<script type=\"text\/javascript\" src=\""+server+"/eCommerceCalculadora/resources/js/eCalculadoraCetelemCombo.js\" async><\/script>");
        last_cantidad = cantidad;
        getCetelemScript();
    }
}

function initializeCetelem() {
    codCentro = center_code;//

    totalpriceCetelem = Math.round(min_amount * 100, 2) / 100;//

    cantidad = '' + totalpriceCetelem;//

    last_cantidad = cantidad;//

    server = server_url_cetelem;//

    color = text_color_cetelem;//

    bloquearImporte = cetelem_amount_block;//FALTA

    fontSize = font_size_cetelem;//

    //codPromocion = cod_promocion_cetelem;

    //$('body').append("<script type=\"text\/javascript\" src=\""+server+"/eCommerceCalculadora/resources/js/eCalculadoraCetelemCombo.js\" async><\/script>");

    getCetelemScript();
}

function getCetelemScript() {
    cetelem_script = server + "/eCommerceCalculadora/resources/js/eCalculadoraCetelemCombo.js";
    $.ajax({
        async: false,
        type: 'GET',
        url: cetelem_script,
        data: null,
        //success: getMonthsPrice,
        dataType: 'script'
    }).done(function () {
        getMonthsPrice();
    });
}