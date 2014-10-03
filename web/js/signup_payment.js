jQuery(document).ready(function ($) {
    var formlang = 'en';

    $('.card-number').keyup(function () {
        var brand = detectCreditcardBranding($('.card-number').val());
        brand = brand.replace(' ', '-');
        $(".card-number")[0].className = $(".card-number")[0].className.replace(/paymill-card-number-.*/g, '');
        if (brand !== 'unknown') {
            $('#card-number').addClass("paymill-card-number-" + brand);
        }

        if (brand !== 'maestro') {
            VALIDATE_CVC = true;
        } else {
            VALIDATE_CVC = false;
        }
    });

    function PaymillResponseHandler(error, result) {
        if (error) {
            $(".payment_errors").text(translation["en"]["error"][error.apierror]);
            $(".payment_errors").css("display", "inline-block");
        } else {
            $(".payment_errors").html("&nbsp;");
            var form = $("#signUp-form");
            if (!form.length) {
                form = $("#update-billing-form");
            }

            // Token
            var token = result.token;
            form.append("<input type='hidden' name='paymillToken' value='" + token + "'/>");
            form.get(0).submit();
        }
    }

    $("#signUp-form, #update-billing-form").submit(function (event) {

        var paymentErrors = $(".payment_errors");
        paymentErrors.text('');
        if (false === paymill.validateHolder($('#card_name').val())) {
            paymentErrors.text(translation[formlang]["error"]["invalid-card-holdername"]);
            paymentErrors.css("display", "inline-block");
            return false;
        }

        if ((false === paymill.validateCvc($('#card_security').val()))) {
            if (VALIDATE_CVC) {
                paymentErrors.text(translation[formlang]["error"]["invalid-card-cvc"]);
                paymentErrors.css("display", "inline-block");
                return false;
            } else {
                $('#card_security').val("000");
            }
        }

        if (false === paymill.validateCardNumber($('#card_number').val())) {
            paymentErrors.text(translation[formlang]["error"]["invalid-card-number"]);
            paymentErrors.css("display", "inline-block");
            return false;
        }

        var expiry = [];
        expiry[0] = $('#card_exp_month').val();
        expiry[1] = $('#card_exp_year').val();

        if (false === paymill.validateExpiry(expiry[0], expiry[1])) {
            paymentErrors.text(translation[formlang]["error"]["invalid-card-expiry-date"]);
            paymentErrors.css("display", "inline-block");
            return false;
        }

        var params = {
            amount_int: $('#pay_amount').val() * 100,
            currency: 'USD',
            number: $('#card_number').val(),
            exp_month: expiry[0],
            exp_year: expiry[1],
            cvc: $('#card_security').val(),
            cardholder: $('#card_name').val()
        };

        paymill.createToken(params, PaymillResponseHandler);
        return false;
    });
});