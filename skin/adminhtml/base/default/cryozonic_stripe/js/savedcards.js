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

var Cryozonic_SaveNewCard = function()
{
    var saveButton = document.getElementById('cryozonic-savecard-button');
    var wait = document.getElementById('cryozonic-savecard-please-wait');
    saveButton.style.display = "none";
    wait.style.display = "block";

    if (typeof Stripe != 'undefined')
    {
        createStripeToken(function(err)
        {
            if (err)
            {
                alert(err);
                saveButton.style.display = "block";
                wait.style.display = "none";
            }
            else
                document.getElementById("new-card").submit();
        });
        return false;
    }

    return true;
}
