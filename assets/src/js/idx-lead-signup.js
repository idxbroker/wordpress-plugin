(function(){

    function forEach(array, callback, scope) {
      for (var i = 0; i < array.length; i++) {
        callback.call(scope, array[i], i);
      }
    }

    //Add event listener to each signup widget
    function listenToSignupForms() {
        forEach(
            document.querySelectorAll('.impress-idx-signup-widget'), 
            function(form) {
                form.addEventListener('submit', validateSignup);
            }
        );
    }

    //Check for hash. If one, redirect to lead login page.
    function checkForHashError() {
        if(window.location.hash === '#LeadSignup') {
            //Prevent hitting back in browser from redirecting again
            window.location.hash = '';
            // window.location.href = idxLeadLoginUrl;
        }
    }

    //On submit, validate fields. If error, display it.
    function validateSignup(event) {
        var form = event.target;

        try {
            forEach(form.querySelectorAll('input'),
                function(input) {
                    findBlankFields(input);
                    validateEmail(input);
                }
            );
        } catch(err) {
            displayErr(err, form);
            event.preventDefault();
        }

    }

    function findBlankFields(input) {
        if(input.value === '' || input.value === ' ') {
            input.className = 'input-error';
            throw "One or more fields is empty";
        }
    }

    //Run validation to avoid loopholes in email input validation
    function validateEmail(input) {
        if(input.id === 'impress-widgetemail') {

            var emailExpression = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

            if(emailExpression.test(input.value)) {
                input.className = 'input-error';
                throw "Invalid Email Address";
            }
        }
    }

    //If error div already exists, use it. Else create it.
    function displayErr(err, form) {
        var errDiv = form.querySelectorAll('.error')[0];
        if(typeof errDiv !== 'undefined'){
            return errDiv.innerHTML = err;
        }
        var errMessage = document.createElement('div');
        errMessage.className = 'error';
        errMessage.innerHTML = err;
        form.insertBefore(errMessage, form.firstChild);
    }

    listenToSignupForms();
    checkForHashError();
})();
