function onSubmit(token) {
    document.getElementById("LeadSignup").submit();
}

document.addEventListener('DOMContentLoaded', function(event) {
    var idxRecaptchaTimer = [];

    function fetchCaptchaToken(tokenElement) {
        grecaptcha.execute("6LcUhOYUAAAAAF694SR5_qDv-ZdRHv77I6ZmSiij", {action: tokenElement.attr("data-action")}).then(function(token) {
            jQuery(tokenElement).val(token);
        });
    }

    function recaptchaFormInputFocused(event) {
        var form = jQuery(this).closest("form");

        var tokenElement = form.find(".IDX-recaptchaToken");
        form.addClass("IDX-clearCaptchaTimer").data("tokenElement", tokenElement);

        var elementID = tokenElement.attr("id");
        if (idxRecaptchaTimer[elementID] != undefined) {
            return;
        }

        // Every 2 minutes.
        var tokenExpiration = 2 * 60 * 1000;

        fetchCaptchaToken(tokenElement);
        idxRecaptchaTimer[elementID] = setInterval(fetchCaptchaToken, tokenExpiration, tokenElement);
    }

    // Listen to focus events.
    jQuery(".IDX-recaptchaToken").closest("form").find(":input:not([type=hidden])").on("focus", recaptchaFormInputFocused);
});
