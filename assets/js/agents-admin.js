jQuery(document).ready(function ($) {
  /* === Begin term image JS. === */

  /* If the <img> source has a value, show it.  Otherwise, hide. */
  if ($('.impa-term-image-url').attr('src')) {
    $('.impa-term-image-url').show()
  } else {
    $('.impa-term-image-url').hide()
  }

  /* If there's a value for the term image input. */
  if ($('input#impa-term-image').val()) {
    /* Hide the 'set term image' link. */
    $('.impa-add-media-text').hide()

    /* Show the 'remove term image' link, the image. */
    $('.impa-remove-media, .impa-term-image-url').show()
  }

  /* Else, if there's not a value for the term image input. */
  else {
    /* Show the 'set term image' link. */
    $('.impa-add-media-text').show()

    /* Hide the 'remove term image' link, the image. */
    $('.impa-remove-media, .impa-term-image-url').hide()
  }

  /* When the 'remove term image' link is clicked. */
  $('.impa-remove-media').click(
    function (j) {
      /* Prevent the default link behavior. */
      j.preventDefault()

      /* Set the term image input value to nothing. */
      $('#impa-term-image').val('')

      /* Show the 'set term image' link. */
      $('.impa-add-media-text').show()

      /* Hide the 'remove term image' link, the image. */
      $('.impa-remove-media, .impa-term-image-url, .impa-errors').hide()
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
  var impa_term_image_frame

  /* When the 'set term image' link is clicked. */
  $('.impa-add-media').click(

    function (j) {
      /* Prevent the default link behavior. */
      j.preventDefault()

      /* If the frame already exists, open it. */
      if (impa_term_image_frame) {
        impa_term_image_frame.open()
        return
      }

      /* Creates a custom media frame. */
      impa_term_image_frame = wp.media.frames.impa_term_image_frame = wp.media(
        {
          className: 'media-frame', // Custom CSS class name
          frame: 'select', // Frame type (post, select)
          multiple: false, // Allow selection of multiple images
          title: impa_term_image.title, // Custom frame title

          library: {
            type: 'image' // Media types allowed
          },

          button: {
            text: impa_term_image.button // Custom insert button text
          }
        }
      )

      /*
       * The following handles the image data and sending it back to the meta box once an
       * an image has been selected via the media frame.
       */
      impa_term_image_frame.on('select',

        function () {
          /* Construct a JSON representation of the model. */
          var media_attachment = impa_term_image_frame.state().get('selection').toJSON()

          /* If the custom term image size is available, use it. */
          /* Note the 'width' is contrained by $content_width. */
          if (media_attachment[0].sizes.impa_term_image) {
            var impa_media_url = media_attachment[0].sizes.impa_term_image.url
            var impa_media_width = media_attachment[0].sizes.impa_term_image.width
            var impa_media_height = media_attachment[0].sizes.impa_term_image.height
          }

          /* Else, use the full size b/c it will always be available. */
          else {
            var impa_media_url = media_attachment[0].sizes.full.url
            var impa_media_width = media_attachment[0].sizes.full.width
            var impa_media_height = media_attachment[0].sizes.full.height
          }

          /* === Begin image dimensions error impacks. === */

          var impa_errors = ''

          /*
           * Note that we must use the "full" size width in some error impacks
           * b/c I haven't found a way around WordPress constraining the image
           * size via the $content_width global. This means that the error
           * impacking isn't 100%, but it should do fine for the most part since
           * we're using a custom image size. If not, the error impacking is good
           * on the PHP side once the data is saved.
           */
          if (impa_term_image.min_width > media_attachment[0].sizes.full.width && impa_term_image.min_height > impa_media_height) {
            impa_errors = impa_term_image.min_width_height_error
          } else if (impa_term_image.max_width < impa_media_width && impa_term_image.max_height < impa_media_height) {
            impa_errors = impa_term_image.max_width_height_error
          } else if (impa_term_image.min_width > media_attachment[0].sizes.full.width) {
            impa_errors = impa_term_image.min_width_error
          } else if (impa_term_image.min_height > impa_media_height) {
            impa_errors = impa_term_image.min_height_error
          } else if (impa_term_image.max_width < impa_media_width) {
            impa_errors = impa_term_image.max_width_error
          } else if (impa_term_image.max_height < impa_media_height) {
            impa_errors = impa_term_image.max_height_error
          }

          /* If there are error strings, show them. */
          if (impa_errors) {
            $('.impa-errors p').text(impa_errors)
            $('.impa-errors').show()
          }

          /* If no error strings, make sure the errors <div> is hidden. */
          else {
            $('.impa-errors').hide()
          }

          /* === End image dimensions error impacks. === */

          /* Add the image attachment ID to our hidden form field. */
          $('#impa-term-image').val(media_attachment[0].id)

          /* Change the 'src' attribute so the image will display in the meta box. */
          $('.impa-term-image-url').attr('src', impa_media_url)

          /* Hides the add image link. */
          $('.impa-add-media-text').hide()

          /* Displays the term image and remove image link. */
          $('.impa-term-image-url, .impa-remove-media').show()
        }
      )

      /* Open up the frame. */
      impa_term_image_frame.open()
    }
  )

  /* === End term image JS. === */
})

function impressAgentsDataCollectionOptOut (element) {
  jQuery.post(
    ajaxurl, {
      action: 'impress_agents_data_optout',
      nonce: impressAgentsAdmin['nonce-impress-agents-data-optout'],
      optout: element.checked
    }, function (response) {
      if (response !== 'success') {
        console.error(response)
      }
    }
  )
}
