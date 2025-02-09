// SendForm.js
// Sends the e-mail form data to the server and returns
// a reply.  This script must be included in the page with
// the e-mail form.  The form must have the class
// php-email-form defined.  The loading, error-message
// and sent-messages must be defined.
//

(function () {
  // Immediately invoke the code.
  "use strict";

  document.querySelector('.php-email-form').addEventListener('submit', function(event) {
      event.preventDefault(); // Override the default actions of the form.

      let thisForm = this;

      let action = thisForm.getAttribute('action'); // The server side script to execute.

      thisForm.querySelector('.loading').classList.add('d-block');  // Show loading and hide error and sent message elements
      thisForm.querySelector('.error-message').classList.remove('d-block');
      thisForm.querySelector('.sent-message').classList.remove('d-block');

      let formData = new FormData( thisForm );

      php_email_form_submit(thisForm, action, formData);
    });

  function php_email_form_submit(thisForm, action, formData) {
    fetch(action, { // POST the request to the server and wait for a response.
      method: 'POST',
      body: formData,
      headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
      if( response.ok ) { // Waiting for the response
        return response.text();
      } else {  // Server didn't like it.
        throw new Error(`${response.status} ${response.statusText} ${response.url}`);
      }
    })
    .then(data => {
      thisForm.querySelector('.loading').classList.remove('d-block'); // Hide loading element.
 //     if (data.trim() == 'OK') {  // Will return OK if properly executed.
        thisForm.querySelector('.sent-message').classList.add('d-block'); // Show that the e-mail was sent correctly.
        thisForm.reset(); // Empty all the fields of the form.
//      } else {  // Some type of error.
//        throw new Error(data ? data : 'Form submission failed and no error message returned from: ' + action);
//      }
    })
    .catch((error) => {
      displayError(thisForm, error);
    });
  }

  function displayError(thisForm, error) {
    // Show the error element and hide the loading.
    thisForm.querySelector('.loading').classList.remove('d-block');
    thisForm.querySelector('.error-message').innerHTML = error;
    thisForm.querySelector('.error-message').classList.add('d-block');
  }

})();
