(function (blocks, element, editor, components) {
  var el = element.createElement
  var registerBlockType = blocks.registerBlockType
  var InspectorControls = editor.InspectorControls
  var SelectControl = components.SelectControl
  var icon = el('i', { class: 'fas fa-cog fa-2x' }, null)

  // setCategory() is a workaround to prevent the custom category from throwing an console warning
  function setCategory () {
    if (window.location.href.indexOf('wp-admin') !== -1) {
      return 'idx-category'
    } else {
      return 'widgets'
    }
  }

  registerBlockType('idx-broker-platinum/idx-widgets-block', {
    title: 'IDX Broker Widgets',
    icon: icon,
    category: setCategory(),

    attributes: {
      id: {
        type: 'string',
        default: null
      }
    },

    edit: function (props) {
      return [
        el('div', {
          class: 'idx-block-placeholder-container'
        }, el('img', {
          src: idx_widget_block_image_url
        })),

        el(InspectorControls, {},
          el(SelectControl, {
            label: 'Select a Widget:',
            value: props.attributes.id,
            options: (idx_widgets_list ? idx_widgets_list : [{ label: 'All', value: '' }]),
            onChange: (value) => {
              props.setAttributes({ id: value })
            }
          })
        )
      ]
    },

    save: function (props) {
      return null
    }

  })
})(
  window.wp.blocks,
  window.wp.element,
  window.wp.editor,
  window.wp.components
)
