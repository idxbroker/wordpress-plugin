/* global jQuery, wp_listings_adminL10n, idxImportListingObj, ajaxurl, confirm, wp */
/* eslint no-undef: "error" */

jQuery(document).ready(function ($) {
  // Save dismiss state
  $('.notice.is-dismissible').on('click', '.notice-dismiss', function (event) {
    event.preventDefault()
    const $this = $(this)
    if (!$this.parent().data('key')) {
      return
    }
    $.post(wp_listings_adminL10n.ajaxurl, {
      action: 'wp_listings_admin_notice',
      url: wp_listings_adminL10n.ajaxurl,
      nag: $this.parent().data('key'),
      nonce: wp_listings_adminL10n.nonce || ''
    })
  })

  // Make notices dismissible - backward compatabity -4.2 - copied from WordPress 4.2
  $('.notice.is-dismissible').each(function () {
    if (wp_listings_adminL10n.wp_version) {
      return
    }

    const $this = $(this)
    const $button = $('<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>')
    const btnText = wp_listings_adminL10n.dismiss || ''

    // Ensure plain text
    $button.find('.screen-reader-text').text(btnText)

    $this.append($button)

    $button.on('click.wp-dismiss-notice', function (event) {
      event.preventDefault()
      $this.fadeTo(100, 0, function () {
        $(this).slideUp(100, function () {
          $(this).remove()
        })
      })
    })
  })

  /* === Begin term image JS. === */

  /* If the <img> source has a value, show it.  Otherwise, hide. */
  if ($('.wpl-term-image-url').attr('src')) {
    $('.wpl-term-image-url').show()
  } else {
    $('.wpl-term-image-url').hide()
  }

  /* If there's a value for the term image input. */
  if ($('input#wpl-term-image').val()) {
    /* Hide the 'set term image' link. */
    $('.wpl-add-media-text').hide()

    /* Show the 'remove term image' link, the image. */
    $('.wpl-remove-media, .wpl-term-image-url').show()
  }

  /* Else, if there's not a value for the term image input. */
  else {
    /* Show the 'set term image' link. */
    $('.wpl-add-media-text').show()

    /* Hide the 'remove term image' link, the image. */
    $('.wpl-remove-media, .wpl-term-image-url').hide()
  }

  /* When the 'remove term image' link is clicked. */
  $('.wpl-remove-media').click(
    function (j) {
      /* Prevent the default link behavior. */
      j.preventDefault()

      /* Set the term image input value to nothing. */
      $('#wpl-term-image').val('')

      /* Show the 'set term image' link. */
      $('.wpl-add-media-text').show()

      /* Hide the 'remove term image' link, the image. */
      $('.wpl-remove-media, .wpl-term-image-url, .wpl-errors').hide()
    }
  )

  /*
     * The following code deals with the custom media modal frame for the term image.  It is a
     * modified version of Thomas Griffin's New Media Image Uploader example plugin.
     *
     * @link      https://github.com/thomasgriffin/New-Media-Image-Uploader
     * @license   http://www.opensource.org/licenses/gpl-license.php
     * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
     * @copyright Copyright 2013 Thomas Griffin
     */

  /* Prepare the variable that holds our custom media manager. */
  let wpl_term_image_frame

  /* When the 'set term image' link is clicked. */
  $('.wpl-add-media').click(

    function (j) {
      /* Prevent the default link behavior. */
      j.preventDefault()

      /* If the frame already exists, open it. */
      if (wpl_term_image_frame) {
        wpl_term_image_frame.open()
        return
      }

      /* Creates a custom media frame. */
      wpl_term_image_frame = wp.media.frames.wpl_term_image_frame = wp.media(
        {
          className: 'media-frame', // Custom CSS class name
          frame: 'select', // Frame type (post, select)
          multiple: false, // Allow selection of multiple images
          title: wpl_term_image.title, // Custom frame title

          library: {
            type: 'image' // Media types allowed
          },

          button: {
            text: wpl_term_image.button // Custom insert button text
          }
        }
      )

      /*
             * The following handles the image data and sending it back to the meta box once an
             * an image has been selected via the media frame.
             */
      wpl_term_image_frame.on('select',

        function () {
          /* Construct a JSON representation of the model. */
          const media_attachment = wpl_term_image_frame.state().get('selection').toJSON()

          /* If the custom term image size is available, use it. */
          /* Note the 'width' is contrained by $content_width. */
          if (media_attachment[0].sizes.wpl_term_image) {
            const wpl_media_url = media_attachment[0].sizes.wpl_term_image.url
            const wpl_media_width = media_attachment[0].sizes.wpl_term_image.width
            const wpl_media_height = media_attachment[0].sizes.wpl_term_image.height
          }

          /* Else, use the full size b/c it will always be available. */
          else {
            const wpl_media_url = media_attachment[0].sizes.full.url
            const wpl_media_width = media_attachment[0].sizes.full.width
            const wpl_media_height = media_attachment[0].sizes.full.height
          }

          /* === Begin image dimensions error wplcks. === */

          let wpl_errors = ''

          /*
                     * Note that we must use the "full" size width in some error wplcks
                     * b/c I haven't found a way around WordPress constraining the image
                     * size via the $content_width global. This means that the error
                     * wplcking isn't 100%, but it should do fine for the most part since
                     * we're using a custom image size. If not, the error wplcking is good
                     * on the PHP side once the data is saved.
                     */
          if (wpl_term_image.min_width > media_attachment[0].sizes.full.width && wpl_term_image.min_height > wpl_media_height) {
            wpl_errors = wpl_term_image.min_width_height_error
          } else if (wpl_term_image.max_width < wpl_media_width && wpl_term_image.max_height < wpl_media_height) {
            wpl_errors = wpl_term_image.max_width_height_error
          } else if (wpl_term_image.min_width > media_attachment[0].sizes.full.width) {
            wpl_errors = wpl_term_image.min_width_error
          } else if (wpl_term_image.min_height > wpl_media_height) {
            wpl_errors = wpl_term_image.min_height_error
          } else if (wpl_term_image.max_width < wpl_media_width) {
            wpl_errors = wpl_term_image.max_width_error
          } else if (wpl_term_image.max_height < wpl_media_height) {
            wpl_errors = wpl_term_image.max_height_error
          }

          /* If there are error strings, show them. */
          if (wpl_errors) {
            $('.wpl-errors p').text(wpl_errors)
            $('.wpl-errors').show()
          }

          /* If no error strings, make sure the errors <div> is hidden. */
          else {
            $('.wpl-errors').hide()
          }

          /* === End image dimensions error wplcks. === */

          /* Add the image attachment ID to our hidden form field. */
          $('#wpl-term-image').val(media_attachment[0].id)

          /* Change the 'src' attribute so the image will display in the meta box. */
          $('.wpl-term-image-url').attr('src', wpl_media_url)

          /* Hides the add image link. */
          $('.wpl-add-media-text').hide()

          /* Displays the term image and remove image link. */
          $('.wpl-term-image-url, .wpl-remove-media').show()
        }
      )

      /* Open up the frame. */
      wpl_term_image_frame.open()
    }
  )

  /* === End term image JS. === */

  /* Import Listings button */
  jQuery(document).on('click', '.submit-imports-button', function (event) {
    event.preventDefault()
    const all = jQuery('.selected').not('.imported').contents()
    const listings = []
    for (let i = 0; i < all.length; i++) {
      if (all[i].id) {
        listings.push(all[i].id)
      }
    }
    const listingsUrlString = listings.join(',')
    jQuery.ajax({
      type: 'get',
      dataType: 'json',
      url: idxImportListingObj.url + listingsUrlString,
      data: {
        listings: listingsUrlString
      },
      beforeSend: function (xhr) {
        xhr.setRequestHeader('X-WP-Nonce', idxImportListingObj.nonce)
      },
      success: function (response) {
        window.location.reload()
      },
      error: function (response) {
        console.error(response)
      }
    })
  })

  /* Google My Business Settings */
  jQuery(document).on('click', '#wpl-gmb-clear-settings-button', function (event) {
    event.preventDefault()
    const confirmation = confirm('Logout of Google My Business?')
    if (confirmation) {
      jQuery.get(
        ajaxurl, {
          action: 'impress_gmb_logout',
          nonce: wp_listings_adminL10n['nonce-gmb-logout']
        }, function (response) {
          window.location.reload()
        }
      )
    }
  })

  jQuery(document).on('click', '#wpl-reset-next-post-time-button', function (event) {
    event.preventDefault()
    const confirmation = confirm('Reset next scheduled post time to 12 hours from now?')
    if (confirmation) {
      const currentText = jQuery('#wpl-gmb-next-post-label').text()
      jQuery('#wpl-gmb-next-post-label').html('<span class="dashicons dashicons-update wpl-dashicon"></span>').fadeIn('fast')
      jQuery.get(
        ajaxurl, {
          action: 'wpl_reset_next_post_time_request',
          nonce: wp_listings_adminL10n['nonce-gmb-reset-post-time']
        }, function (response) {
          if (response) {
            jQuery('#wpl-gmb-next-post-label').text(response)
          } else {
            jQuery('#wpl-gmb-next-post-label').text(currentText)
          }
        }
      )
    }
  })
})

function locationToggled () {
  const locationSelections = {}
  document.querySelectorAll('.wpl-gmb-location-tag input').forEach(function (element) {
    locationSelections[element.id] = {
      share_to_location: (element.checked ? 1 : 0)
    }
  })
  jQuery.post(
    ajaxurl, {
      action: 'impress_gmb_update_location_settings',
      nonce: wp_listings_adminL10n['nonce-gmb-update-location-settings'],
      locations: locationSelections
    }, function (response) {
      if (response !== 'success') {
        window.location.reload()
      }
    }
  )
}

function clearLastPostStatus (event) {
  event.preventDefault()
  const confirmation = confirm('Clear last post status?')
  if (confirmation) {
    jQuery.post(
      ajaxurl, {
        action: 'wpl_clear_last_post_status',
        nonce: wp_listings_adminL10n['nonce-gmb-clear-last-post-status']
      }, function (response) {
        window.location.reload()
      }
    )
  }
}

function impressListingsDataCollectionOptOut () {
  jQuery.post(
    ajaxurl, {
      action: 'impress_listings_data_optout',
      nonce: wp_listings_adminL10n['nonce-impress-listings-data-optout'],
      optout: document.querySelector('#impress-data-optout-checkbox').checked
    }, function (response) {
      if (response !== 'success') {
        console.error(response)
      }
    }
  )
}
