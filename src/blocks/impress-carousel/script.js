const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { InspectorControls } = wp.blockEditor
const { SelectControl, CheckboxControl, TextControl, Panel, PanelBody } = wp.components

registerBlockType(
  'idx-broker-platinum/impress-carousel-block', {
    title: 'IMPress Carousel',
    icon: 'admin-multisite',
    category: 'idx-category',
    attributes: {
      max: {
        type: 'int',
        default: 15
      },
      display: {
        type: 'int',
        default: 3
      },
      autoplay: {
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
      styles: {
        type: 'int',
        default: 1
      },
      new_window: {
        type: 'int',
        default: 0
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
      }
    },
    edit: ({ attributes, setAttributes }) => {
      const propertiesToFeature = [{ label: 'Featured', value: 'featured' }, { label: 'Sold/Pending', value: 'soldpending' }, { label: 'Active Supplemental', value: 'supplementalactive' }, { label: 'Sold/Pending Supplemental', value: 'supplementalsoldpending' }, { label: 'All Supplemental', value: 'supplementalall' }, { label: 'Use Saved Link', value: 'savedlinks' }]
      const sortOptions = [{ label: 'Default', value: 'default' }, { label: 'Highest to Lowest Price', value: 'high-low' }, { label: 'Lowest to Highest Price', value: 'low-high' }]
      return (
        <div>
          <div className='idx-block-placeholder-container'>
            <img src={impress_carousel_image_url} />
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
                  options={(impress_carousel_saved_links || [{ label: 'All', value: '' }])}
                  onChange={(value) => { setAttributes({ saved_link_id: value }) }}
                />
                <SelectControl
                  label={__('Limit by Agent:', 'idx-broker-platinum')}
                  value={attributes.agent_id}
                  options={(impress_carousel_agent_list || [{ label: 'All', value: '' }])}
                  onChange={(value) => { setAttributes({ agent_id: value }) }}
                />
                <CheckboxControl
                  label={__('Include colistings for selected agent?', 'idx-broker-platinum')}
                  value={attributes.colistings}
                  checked={(attributes.colistings > 0)}
                  onChange={(value) => { setAttributes({ colistings: (value > 0 ? 1 : 0) }) }}
                />
                <TextControl
                  label={__('Listings to show without scrolling:', 'idx-broker-platinum')}
                  value={attributes.display}
                  type='number'
                  onChange={(value) => { setAttributes({ display: value }) }}
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
                  label={__('Autoplay?', 'idx-broker-platinum')}
                  value={attributes.autoplay}
                  checked={(attributes.autoplay > 0)}
                  onChange={(value) => { setAttributes({ autoplay: (value > 0 ? 1 : 0) }) }}
                />
                <CheckboxControl
                  label={__('Open Listings in a New Window?', 'idx-broker-platinum')}
                  value={attributes.new_window}
                  checked={(attributes.new_window > 0)}
                  onChange={(value) => { setAttributes({ new_window: (value > 0 ? 1 : 0) }) }}
                />
                <CheckboxControl
                  label={__('Default Styles?', 'idx-broker-platinum')}
                  value={attributes.styles}
                  checked={(attributes.styles > 0)}
                  onChange={(value) => { setAttributes({ styles: (value > 0 ? 1 : 0) }) }}
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
