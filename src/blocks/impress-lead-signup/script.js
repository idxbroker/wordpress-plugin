const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { InspectorControls } = wp.editor
const { SelectControl, CheckboxControl, TextControl } = wp.components
const icon = () => (<i className='fas fa-user-plus fa-2x' />)

// workaround to prevent the custom category from throwing an console warning
function setCategory () {
  if (window.location.href.indexOf('wp-admin') !== -1) {
    return 'idx-category'
  } else {
    return 'widgets'
  }
}

registerBlockType(
  'idx-broker-platinum/impress-lead-signup-block', {
    title: __('IMPress Lead Signup', 'idx-broker-platinum'),
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
    edit: ({ attributes, setAttributes }) => {
      return (
        <div>
          <div className='idx-block-placeholder-container'>
            <img src={lead_signup_image_url} />
          </div>
          <InspectorControls>
            <CheckboxControl
              label={__('Show phone number field?', 'idx-broker-platinum')}
              value={attributes.phone}
              checked={(attributes.phone > 0)}
              onChange={(value) => { setAttributes({ phone: (value > 0 ? 1 : 0) }) }}
            />
            <CheckboxControl
              label={__('Default Styles?', 'idx-broker-platinum')}
              value={attributes.styles}
              checked={(attributes.styles > 0)}
              onChange={(value) => { setAttributes({ styles: (value > 0 ? 1 : 0) }) }}
            />
            <CheckboxControl
              label={__('Open in a New Window?', 'idx-broker-platinum')}
              value={attributes.new_window}
              checked={(attributes.new_window > 0)}
              onChange={(value) => { setAttributes({ new_window: (value > 0 ? 1 : 0) }) }}
            />
            <CheckboxControl
              label={__('Add password form field?', 'idx-broker-platinum')}
              value={attributes.password_field}
              checked={(!!attributes.password_field)}
              onChange={(value) => { setAttributes({ password_field: value }) }}
            />
            <TextControl
              label={__('Sign up button text:', 'idx-broker-platinum')}
              value={attributes.button_text}
              onChange={(value) => { setAttributes({ button_text: value }) }}
            />
            <SelectControl
              label={__('Route to Agent:', 'idx-broker-platinum')}
              value={attributes.agent_id}
              options={(lead_signup_agent_list || [{ label: 'All', value: '' }])}
              onChange={(value) => { setAttributes({ agent_id: value }) }}
            />
          </InspectorControls>
        </div>
      )
    },
    save: () => {
      return null
    }
  }
)
