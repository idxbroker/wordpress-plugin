jQuery(document).ready(function () {
  jQuery('#listing-tabs').tabs()
	jQuery('#contact-tabs').tabs()
	jQuery('#inquiry-form').validate()
	jQuery('.iframe-wrap').fitVids()
})

document.getElementById('LeadSignup').addEventListener('submit', signupSubmission)

function signupSubmission () {
  jQuery('#LeadSignup').hide()
  jQuery('#signup-notification').text('Signup Submission Complete!')
  jQuery('#signup-notification').show()
  jQuery('#contact-tabs').tabs('option', 'active', 0)
}

function listingInquiry (event, postid) {
  event.preventDefault()
  jQuery('#submit-inquiry-button').hide()
  jQuery('#loading-icon-container').show()
  jQuery.post(
    ajaxurl, {
      action: 'listing_inquiry_request',
      nonce: impressSingleListing['nonce-listing-inquiry'],
      formdata: jQuery('#listing-inquiry-form').serialize()
    }, function (response) {
      jQuery('#loading-icon-container').hide()
      // Nonce or form data verification failure
      if (response === 'Nonce or Formdata Check Failed') {
        jQuery('#submit-inquiry-button').val('Submission failed')
        jQuery('#submit-inquiry-button').show()
        setTimeout(function () { jQuery('#submit-inquiry-button').val('Submit') }, 3000)
      }
      // If no lead is found, prepate signup form
      if (response === 'No Lead Match') {
        jQuery('#submit-inquiry-button').show()
        jQuery('#signup-notification').show()
        jQuery('#contact-tabs').tabs('option', 'active', 1)
        // Populate signup form
        jQuery('#impress-widgetfirstName').val(jQuery('#inquiryFirstname').val())
        jQuery('#impress-widgetlastName').val(jQuery('#inquiryLastname').val())
        jQuery('#impress-widgetemail').val(jQuery('#inquiryEmail').val())
        jQuery('#impress-widgetemail').focus()
      }
      // Successful inquiry
      if (response === 'Success') {
        document.getElementById('listing-inquiry-form').reset()
        jQuery('#submit-inquiry-button').val('Inquiry Submitted')
        jQuery('#submit-inquiry-button').show()
        setTimeout(function () { jQuery('#submit-inquiry-button').val('Submit') }, 3000)
      }
    }
  )
}


