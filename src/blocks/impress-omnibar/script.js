const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { InspectorControls } = wp.blockEditor
const { SelectControl, CheckboxControl, Panel, PanelBody } = wp.components
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
      sort_by: {
        type: 'string',
        default: ''
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
                <SelectControl
                  label={__('Default Sort Order', 'idx-broker-platinum')}
                  value={attributes.sort_by}
                  options={[
                    { label: '', value: '' },
                    { label: 'Newest to oldest', value: 'newest' },
                    { label: 'Oldest to newest', value: 'oldest' },
                    { label: 'Least expensive to most', value: 'pra' },
                    { label: 'Most expensive to least', value: 'prd' },
                    { label: 'Bedrooms (least to most)', value: 'bda' },
                    { label: 'Bedrooms (most to least)', value: 'bdd' },
                    { label: 'Bathrooms (least to most)', value: 'tba' },
                    { label: 'Bathrooms (most to least)', value: 'tbd' },
                    { label: 'Square feet (smallest to largest)', value: 'sqfta' },
                    { label: 'Square feet (largest to smallest', value: 'sqftd' }
                  ]}
                  onChange={(value) => { setAttributes({ sort_by: value})}}
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
