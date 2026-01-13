const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { InspectorControls } = wp.blockEditor
const { CheckboxControl, Panel, PanelBody } = wp.components
const icon = () => (<i className='fas fa-search fa-2x' />)

registerBlockType(
  'idx-broker-platinum/impress-omnibar-block', {
    title: __('IMPress Omnibar Search', 'idx-broker-platinum'),
    icon: icon,
    category: 'idx-category',
    attributes: {
      styles: {
        type: 'int',
        default: 1
      },
      extra: {
        type: 'int',
        default: 0
      },
      min_price: {
        type: 'int',
        default: 0
      },
      remove_price_validation: {
        type: 'int',
        default: 0
      }
    },
    edit: ({ attributes, setAttributes }) => {
      return (
        <div>
          <div className='idx-block-placeholder-container'>
            <img src={impress_omnibar_image_url} />
          </div>

          <InspectorControls>
            <Panel>
              <PanelBody title='Settings' initialOpen={true}>
                <CheckboxControl
                  label={__('Default Styles?', 'idx-broker-platinum')}
                  value={attributes.styles}
                  checked={(attributes.styles > 0)}
                  onChange={(value) => { setAttributes({ styles: (value > 0 ? 1 : 0) }) }}
                />
                <CheckboxControl
                  label={__('Extra Fields?', 'idx-broker-platinum')}
                  value={attributes.extra}
                  checked={(attributes.extra > 0)}
                  onChange={(value) => { setAttributes({ extra: (value > 0 ? 1 : 0) }) }}
                />
                <CheckboxControl
                  label={__('Include Min Price? (If Extra Fields is enabled)', 'idx-broker-platinum')}
                  value={attributes.min_price}
                  checked={(attributes.min_price > 0)}
                  onChange={(value) => { setAttributes({ min_price: (value > 0 ? 1 : 0) }) }}
                />
                <CheckboxControl
                  label={__('Remove Price Validation (min/step attributes)?', 'idx-broker-platinum')}
                  value={attributes.remove_price_validation}
                  checked={(attributes.remove_price_validation > 0)}
                  onChange={(value) => { setAttributes({ remove_price_validation: (value > 0 ? 1 : 0) }) }}
                />
              </PanelBody>
            </Panel>
          </InspectorControls>
        </div>
      )
    },
    save: () => {
      return null
    }
  }
)
