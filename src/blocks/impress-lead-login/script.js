(function (blocks, element, editor, components) {
  var el = element.createElement
  var registerBlockType = blocks.registerBlockType
  var InspectorControls = editor.InspectorControls
  var Checkbox = components.CheckboxControl
  var icon = el('i', { class: 'fa fa-users fa-2x' }, null)

  // setCategory() is a workaround to prevent the custom category from throwing an console warning
  function setCategory () {
    if (window.location.href.indexOf('wp-admin') !== -1) {
      return 'idx-category'
    } else {
      return 'widgets'
    }
  }

  registerBlockType('idx-broker-platinum/impress-lead-login-block', {
    title: 'IMPress Lead Login',
    icon: icon,
    category: setCategory(),

    attributes: {
      styles: {
        type: 'int',
        default: 1
      },
      new_window: {
        type: 'int',
        default: 0
      },
      password_field: {
        type: 'bool',
        default: false
      }
    },

    edit: function (props) {
      return [
        el('div', {
          class: 'idx-block-placeholder-container'
        }, el('img', {
          src: lead_login_image_url
        })),

        el(InspectorControls, {},
          el(Checkbox, {
            label: 'Default Styles?',
            value: props.attributes.styles,
            checked: (props.attributes.styles > 0),
            onChange: (value) => { props.setAttributes({ styles: (value > 0 ? 1 : 0) }) }
          })
        ),

        el(InspectorControls, {},
          el(Checkbox, {
            label: 'Open in a New Window?',
            value: props.attributes.new_window,
            checked: (props.attributes.new_window > 0),
            onChange: (value) => { props.setAttributes({ new_window: (value > 0 ? 1 : 0) }) }
          })
        ),

        el(InspectorControls, {},
          el(Checkbox, {
            label: 'Add password form field?',
            value: props.attributes.password_field,
            checked: (!!props.attributes.password_field),
            onChange: (value) => { props.setAttributes({ password_field: value }) }
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
