document.addEventListener('DOMContentLoaded', function(){

    //Helper function.
    function forEach(array, callback, scope) {
      for (var i = 0; i < array.length; i++) {
        callback.call(scope, array[i], i);
      }
    }

    //Add event listener to each signup widget
    function listenToSignupForms() {
        forEach(
            document.querySelectorAll('.impress-lead-signup'), 
            function(form) {
                form.addEventListener('submit', validateSignup);
            }
        );
    }

    //On submit, validate fields. If error, display it.
    function validateSignup(event) {
        var form = event.target;

        if(typeof grecaptcha.getResponse == 'function') {
            var captcha = grecaptcha.getResponse();
            if(captcha.length == 0) {
                var err = "You can't leave Captcha Code empty";
                displayErr(err, form);
                event.preventDefault();
            }
        }

        try {
            forEach(form.querySelectorAll('input'),
                function(input) {
                    findBlankFields(input);
                }
            );
        } catch(err) {
            displayErr(err, form);
            event.preventDefault();
        }

    }

    //Prevent blank values from non-required fields.
    function findBlankFields(input) {
        if(input.getAttribute('required') !== null && (input.value === '' || input.value === ' ')) {
            input.className = 'input-error';
            throw "One or more fields are empty.";
        }
    }

    //Check for Errors from URL for IDX Pages.
    function checkForErrors() {
        var error = getQueryStringValue('error');
        if(error === 'lead') {
            return window.location = idxLeadLoginUrl;
        } else if(error === 'true') {
            forEach(document.querySelectorAll('.impress-lead-signup'),
                function(form) {
                    displayErr("There is an error in the form. Please double check that your email address is valid.", form);
                }
            );
        }
    }

    //Get the value of the entered query string.
    function getQueryStringValue(name) {
        var url = window.location.href;
        name = name.replace(/[\[\]]/g, "\\$&");
        //Only select last occurrence of query string for latest error.
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)(?!.*" + name + ")", "i"),
        results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    //If error div already exists, use it. Else create it and display the error.
    function displayErr(err, form) {
        var errDiv = form.querySelectorAll('.error');
        if(typeof errDiv[0] !== 'undefined'){
            return errDiv[0].innerHTML = err;
        }
        var errMessage = document.createElement('div');
        errMessage.className = 'error';
        errMessage.innerHTML = err;
        form.insertBefore(errMessage, form.firstChild);
    }

    //Check for previous Signup errors on page load.
    checkForErrors();
    //Listen for more errors on submit of forms.
    listenToSignupForms();
});
