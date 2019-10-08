(function (blocks, element) {
  var el = element.createElement
  var registerBlockType = blocks.registerBlockType
  var icon = el('i', { class: 'fas fa-code fa-2x' }, null)

  // setCategory() is a workaround to prevent the custom category from throwing an console warning
  function setCategory () {
    if (window.location.href.indexOf('wp-admin') !== -1) {
      return 'idx-category'
    } else {
      return 'widgets'
    }
  }

  registerBlockType('idx-broker-platinum/idx-wrapper-tags-block', {
    title: 'IDX Broker Wrapper Tags',
    icon: icon,
    category: setCategory(),

    edit: function (props) {
      return [
        el('div', {
          class: 'idx-block-placeholder-container',
        }, el('img', {
          src: idx_wrapper_tags_image_url
        }))
      ]
    },

    save: function (props) {
      return el(
        element.RawHTML,
        null,
        '<div id="idxStart"></div><div id="idxStop"></div>' 
      )
    }
  })
})(
  window.wp.blocks,
  window.wp.element
)
