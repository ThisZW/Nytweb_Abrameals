/**
 * Cryozonic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Single Domain License
 * that is available through the world-wide-web at this URL:
 * http://cryozonic.com/licenses/stripe.html
 * If you are unable to obtain it through the world-wide-web,
 * please send an email to info@cryozonic.com so we can send
 * you a copy immediately.
 *
 * @category   Cryozonic
 * @package    Cryozonic_Stripe
 * @copyright  Copyright (c) Cryozonic Ltd (http://cryozonic.com)
 */

var stripeTokens = {};

var initStripe = function(apiKey)
{
    // Load Stripe.js dynamically
    if (typeof Stripe == "undefined")
    {
        var resource = document.createElement('script');
        resource.src = "https://js.stripe.com/v2/";
        var script = document.getElementsByTagName('script')[0];
        script.parentNode.insertBefore(resource, script);

        setTimeout(function(){
            Stripe.setPublishableKey(apiKey);
        }, 500);
    }
    else
        Stripe.setPublishableKey(apiKey);

    // Disable server side card validation when Stripe.js is enabled
    if (typeof AdminOrder != 'undefined' && AdminOrder.prototype.loadArea && typeof AdminOrder.prototype._loadArea == 'undefined')
    {
        AdminOrder.prototype._loadArea = AdminOrder.prototype.loadArea;
        AdminOrder.prototype.loadArea = function(area, indicator, params)
        {
            if (typeof area == "object" && area.indexOf('card_validation') >= 0)
                area = area.splice(area.indexOf('card_validation'), 0);

            if (area.length > 0)
                this._loadArea(area, indicator, params);
        }
    }
};

var createStripeToken = function(done)
{
    // First check if the "Use new card" radio is selected, return if not
    var newCardRadio = document.getElementById('new_card');
    if (newCardRadio && !newCardRadio.checked) return done();

    // Validate the card
    var cardName = document.getElementById('cryozonic_stripe_cc_owner');
    var cardNumber = document.getElementById('cryozonic_stripe_cc_number');
    var cardCvc = document.getElementById('cryozonic_stripe_cc_cid');
    var cardExpMonth = document.getElementById('cryozonic_stripe_expiration');
    var cardExpYear = document.getElementById('cryozonic_stripe_expiration_yr');

    var isValid = cardName && cardName.value && cardNumber && cardNumber.value && cardCvc && cardCvc.value && cardExpMonth && cardExpMonth.value && cardExpYear && cardExpYear.value;

    if (!isValid) return done('Invalid card details');

    var cardDetails = {
        name: cardName.value,
        number: cardNumber.value,
        cvc: cardCvc.value,
        exp_month: cardExpMonth.value,
        exp_year: cardExpYear.value
    };

    // AVS
    if (typeof avs_address_line1 != 'undefined')
    {
        cardDetails.address_line1 = avs_address_line1;
        cardDetails.address_zip = avs_address_zip;
    }
    else if (avs_enabled)
    {
        // Scenario 1: When an admin places a MOTO order for a guest who has no saved address yet
        var elementAddressStreet =  document.getElementById('order-billing_address_street0');
        var elementAddressZip =  document.getElementById('order-billing_address_postcode');

        if (typeof elementAddressStreet != 'undefined' && elementAddressStreet != null &&
            typeof elementAddressZip != 'undefined' && elementAddressZip != null)
        {
            cardDetails.address_line1 = elementAddressStreet.value;
            cardDetails.address_zip = elementAddressZip.value;
        }
        else
        {
            // Scenario 2: When adding a saved card from the front-end and the customer has no billing address
            return done('You must first enter your billing address.')
        }
    }

    var cardKey = JSON.stringify(cardDetails);

    if (stripeTokens[cardKey])
    {
        setStripeToken(stripeTokens[cardKey]);
        return done();
    }

    try { checkout.setLoadWaiting('payment'); } catch (e) {}
    Stripe.card.createToken(cardDetails, function (status, response)
    {
        try { checkout.setLoadWaiting(false); } catch (e) {}
        if (response.error)
        {
            if (typeof IWD != "undefined")
            {
                IWD.OPC.Checkout.hideLoader();
                IWD.OPC.Checkout.xhr = null;
                IWD.OPC.Checkout.unlockPlaceOrder();
            }
            done(response.error.message);
        }
        else
        {
            var token = response.id + ':' + response.card.brand + ':' + response.card.last4;
            stripeTokens[cardKey] = token;
            setStripeToken(token);
            done();
        }
    });
};

function setStripeToken(token)
{
    try
    {
        var input, inputs = document.getElementsByClassName('cryozonic-stripejs-token');
        if (inputs && inputs[0]) input = inputs[0];
        else input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "payment[cc_stripejs_token]");
        input.setAttribute("class", 'cryozonic-stripejs-token');
        input.setAttribute("value", token);
        input.disabled = false; // Gets disabled when the user navigates back to shipping method
        var form = document.getElementById('payment_form_cryozonic_stripe');
        if (!form) form = document.getElementById('co-payment-form');
        if (!form) form = document.getElementById('order-billing_method_form');
        if (!form) form = document.getElementById('onestepcheckout-form');
        if (!form && typeof payment != 'undefined') form = document.getElementById(payment.formId);
        if (!form)
        {
            form = document.getElementById('new-card');
            input.setAttribute("name", "newcard[cc_stripejs_token]");
        }
        form.appendChild(input);
        disableInputs(true);
    } catch (e) {}
}

function disableInputs(disabled)
{
    var elements = document.getElementsByClassName('stripe-input');
    for (var i = 0; i < elements.length; i++)
    {
        // Don't disable the save cards checkbox
        if (elements[i].type == "checkbox") continue;

        // Don't disable the stripejs token
        if (elements[i].type == "hidden" && disabled) continue;

        elements[i].disabled = disabled;
    }
}

var enableInputs = function()
{
    disableInputs(false);
};

// Multi-shipping form support for Stripe.js
var multiShippingForm = null, multiShippingFormSubmitButton = null;

function submitMultiShippingForm(e)
{
    if (payment.currentMethod != 'cryozonic_stripe')
        return true;

    if (e.preventDefault) e.preventDefault();

    if (!multiShippingFormSubmitButton) multiShippingFormSubmitButton = document.getElementById('payment-continue');
    if (multiShippingFormSubmitButton) multiShippingFormSubmitButton.disabled = true;

    createStripeToken(function(err)
    {
        if (err)
            alert(err);
        else
        {
            if (multiShippingFormSubmitButton) multiShippingFormSubmitButton.disabled = false;
            multiShippingForm.submit();
        }
    });

    return false;
}

// Multi-shipping form
var initMultiShippingForm = function()
{
    if (typeof payment == 'undefined' || payment.formId != 'multishipping-billing-form') return;

    multiShippingForm = document.getElementById(payment.formId);
    if (!multiShippingForm) return;

    if (multiShippingForm.attachEvent)
        multiShippingForm.attachEvent("submit", submitMultiShippingForm);
    else
        multiShippingForm.addEventListener("submit", submitMultiShippingForm);
};
