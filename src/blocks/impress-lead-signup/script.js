(function (blocks, element, editor, components) {
  var el = element.createElement
  var registerBlockType = blocks.registerBlockType
  var InspectorControls = editor.InspectorControls
  var TextControl = components.TextControl
  var Checkbox = components.CheckboxControl
  var SelectControl = components.SelectControl
  var icon = el('i', { class: 'fas fa-user-plus fa-2x' }, null)

  // setCategory() is a workaround to prevent the custom category from throwing an console warning
  function setCategory () {
    if (window.location.href.indexOf('wp-admin') !== -1) {
      return 'idx-category'
    } else {
      return 'widgets'
    }
  }

  registerBlockType('idx-broker-platinum/impress-lead-signup-block', {
    title: 'IMPress Lead Signup',
    icon: icon,
    category: setCategory(),

    attributes: {
      phone: {
        type: 'int',
        default: 0
      },
      styles: {
        type: 'int',
        default: 1
      },
      new_window: {
        type: 'int',
        default: 0
      },
      agent_id: {
        type: 'string'
      },
      password_field: {
        type: 'bool',
        default: false
      },
      button_text: {
        type: 'string',
        default: 'Sign Up!'
      }
    },

    edit: function (props) {
      return [
        el('div', {
          class: 'idx-block-placeholder-container'
        }, el('img', {
          src: lead_signup_image_url
        })),

        el(InspectorControls, {},
          el(Checkbox, {
            label: 'Show phone number field?',
            value: props.attributes.phone,
            checked: (props.attributes.phone > 0),
            onChange: (value) => { props.setAttributes({ phone: (value > 0 ? 1 : 0) }) }
          })
        ),

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
        ),

        el(InspectorControls, {},
          el(TextControl, {
            label: 'Sign up button text:',
            value: props.attributes.button_text,
            onChange: (value) => { props.setAttributes({ button_text: value }) }
          })
        ),

        el(InspectorControls, {},
          el(SelectControl, {
            label: 'Route to Agent:',
            value: props.attributes.agent_id,
            options: (lead_signup_agent_list || [ { label: 'All', value: '' } ]),
            onChange: (value) => { props.setAttributes({ agent_id: value }) }
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
