const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { InspectorControls } = wp.editor
const { CheckboxControl } = wp.components
const icon = () => (<i className='fas fa-users fa-2x' />)

// workaround to prevent the custom category from throwing an console warning
function setCategory () {
  if (window.location.href.indexOf('wp-admin') !== -1) {
    return 'idx-category'
  } else {
    return 'widgets'
  }
}

registerBlockType(
  'idx-broker-platinum/impress-lead-login-block', {
    title: __('IMPress Lead Login', 'idx-broker-platinum'),
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
    edit: ({ attributes, setAttributes }) => {
      return (
        <div>
          <div className='idx-block-placeholder-container'>
            <img src={lead_login_image_url} />
          </div>

          <InspectorControls>
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
          </InspectorControls>
        </div>
      )
    },
    save: () => {
      return null
    }
  }
)
