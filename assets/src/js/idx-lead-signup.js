document.addEventListener('DOMContentLoaded', function(){

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

    function findBlankFields(input) {
        if(input.getAttribute('required') !== null && (input.value === '' || input.value === ' ')) {
            input.className = 'input-error';
            throw "One or more fields is empty";
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
});
