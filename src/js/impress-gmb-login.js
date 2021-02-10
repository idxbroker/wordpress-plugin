// Login lightbox functions
function agreeToTermsChecked (element) {
  if (element.checked) {
    document.getElementById('agree-to-terms-button').classList.remove('disabled')
  } else {
    document.getElementById('agree-to-terms-button').classList.add('disabled')
  }
}

function cancelLoginClicked () {
  hideLightbox()
}

function showLightbox () {
  document.getElementById('terms-lightbox').classList.add('lightbox-active')
}

function hideLightbox () {
  document.getElementById('terms-lightbox').classList.remove('lightbox-active')
}

jQuery(document).ready(function ($) {
  // Google My Business oauth handling
  if (window.location.href.indexOf('refresh_token=') !== -1 && window.location.href.indexOf('access_token=') !== -1) {
    jQuery('.wpl-gmb-login-button-container').html('<span class="dashicons dashicons-update wpl-dashicon"></span>').fadeIn('fast')
    var currentUrl = new URL(window.location.href)
    var accessToken = currentUrl.searchParams.get('access_token')
    var refreshToken = currentUrl.searchParams.get('refresh_token')
    jQuery.post(
      ajaxurl, {
        action: 'wpl_gmb_set_initial_tokens',
        refresh_token: refreshToken,
        access_token: accessToken,
        nonce: impressGmbAdmin['nonce-gmb-initial-tokens']
      }, function (response) {
        // remove parameters after post.
        if (window.location.href.split('&code=')[0]) {
          window.location = window.location.href.split('&refresh_token=')[0]
        }
      }
    )
  }
})
