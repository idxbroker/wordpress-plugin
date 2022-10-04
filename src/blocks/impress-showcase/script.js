const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { InspectorControls } = wp.blockEditor
const { SelectControl, CheckboxControl, TextControl, Panel, PanelBody } = wp.components

const icon = () => (<i className='fas fa-home fa-2x' />)

registerBlockType(
  'idx-broker-platinum/impress-showcase-block', {
    title: __('IMPress Showcase', 'idx-broker-platinum'),
    icon: icon,
    category: 'idx-category',
    attributes: {
      max: {
        type: 'int',
        default: 4
      },
      use_rows: {
        type: 'int',
        default: 1
      },
      num_per_row: {
        type: 'int',
        default: 4
      },
      show_image: {
        type: 'int',
        default: 1
      },
      order: {
        type: 'string',
        default: 'default'
      },
      property_type: {
        type: 'string',
        default: 'featured'
      },
      saved_link_id: {
        type: 'string',
        default: ''
      },
      agent_id: {
        type: 'string',
        default: ''
      },
      colistings: {
        type: 'int',
        default: 1
      },
      styles: {
        type: 'int',
        default: 1
      },
      new_window: {
        type: 'int',
        default: 0
      }
    },
    edit: ({ attributes, setAttributes }) => {
      const propertiesToFeature = [{ label: 'Featured', value: 'featured' }, { label: 'Sold/Pending', value: 'soldpending' }, { label: 'Active Supplemental', value: 'supplementalactive' }, { label: 'Sold/Pending Supplemental', value: 'supplementalsoldpending' }, { label: 'All Supplemental', value: 'supplementalall' }, { label: 'Use Saved Link', value: 'savedlinks' }]
      const sortOptions = [{ label: 'Default', value: 'default' }, { label: 'Highest to Lowest Price', value: 'high-low' }, { label: 'Lowest to Highest Price', value: 'low-high' }]
      return (
        <div>
          <div className='idx-block-placeholder-container'>
            <img src={impress_showcase_image_url} />
          </div>

          <InspectorControls>
            <Panel>
              <PanelBody title='Settings' initialOpen={true}>
                <SelectControl
                  label={__('Properties to Display:', 'idx-broker-platinum')}
                  value={attributes.property_type}
                  options={propertiesToFeature}
                  onChange={(value) => { setAttributes({ property_type: value }) }}
                />
                <SelectControl
                  label={__('Choose a saved link (if selected above):', 'idx-broker-platinum')}
                  value={attributes.saved_link_id}
                  options={(impress_showcase_saved_links || [ { label: 'All', value: '' } ])}
                  onChange={(value) => { setAttributes({ saved_link_id: value }) }}
                />
                <SelectControl
                  label={__('Limit by Agent:', 'idx-broker-platinum')}
                  value={attributes.agent_id}
                  options={(impress_showcase_agent_list || [ { label: 'All', value: '' } ])}
                  onChange={(value) => { setAttributes({ agent_id: value }) }}
                />
                <CheckboxControl
                  label={__('Include colistings for selected agent?', 'idx-broker-platinum')}
                  value={attributes.colistings}
                  checked={(attributes.colistings > 0)}
                  onChange={(value) => { setAttributes({ colistings: (value > 0 ? 1 : 0) }) }}
                />
                <CheckboxControl
                  label={__('Show image?', 'idx-broker-platinum')}
                  value={attributes.show_image}
                  checked={(attributes.show_image > 0)}
                  onChange={(value) => { setAttributes({ show_image: (value > 0 ? 1 : 0) }) }}
                />
                <CheckboxControl
                  label={__('Use rows?', 'idx-broker-platinum')}
                  value={attributes.use_rows}
                  checked={(attributes.use_rows > 0)}
                  onChange={(value) => { setAttributes({ use_rows: (value > 0 ? 1 : 0) }) }}
                />
                <TextControl
                  label={__('Listings per row', 'idx-broker-platinum')}
                  value={attributes.num_per_row}
                  type='number'
                  min='0'
                  max='4'
                  onChange={(value) => { setAttributes({ num_per_row: value }) }}
                />
                <TextControl
                  label={__('Max number of listings to show:', 'idx-broker-platinum')}
                  value={attributes.max}
                  type='number'
                  onChange={(value) => { setAttributes({ max: value }) }}
                />
                <SelectControl
                  label={__('Sort Order:', 'idx-broker-platinum')}
                  value={attributes.order}
                  options={sortOptions}
                  onChange={(value) => { setAttributes({ order: value }) }}
                />
                <CheckboxControl
                  label={__('Default Styles?', 'idx-broker-platinum')}
                  value={attributes.styles}
                  checked={(attributes.styles > 0)}
                  onChange={(value) => { setAttributes({ styles: (value > 0 ? 1 : 0) }) }}
                />
                <CheckboxControl
                  label={__('Open Listings in a New Window?', 'idx-broker-platinum')}
                  value={attributes.new_window}
                  checked={(attributes.new_window > 0)}
                  onChange={(value) => { setAttributes({ new_window: (value > 0 ? 1 : 0) }) }}
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
