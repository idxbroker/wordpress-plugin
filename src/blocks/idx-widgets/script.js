const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const { InspectorControls } = wp.blockEditor
const { SelectControl } = wp.components
const icon = () => (<i className='fas fa-cog fa-2x' />)

registerBlockType(
  'idx-broker-platinum/idx-widgets-block', {
    title: __('IDX Broker Widgets', 'idx-broker-platinum'),
    icon: icon,
    category: 'idx-category',
    attributes: {
      id: {
        type: 'string',
        default: null
      }
    },
    edit: ({ attributes, setAttributes }) => {
      return (
        <div>
          <div className='idx-block-placeholder-container'>
            <img src={idx_widget_block_image_url} />
          </div>

          <InspectorControls>
            <SelectControl
              label='Select a Widget:'
              value={attributes.id}
              options={idx_widgets_list ? idx_widgets_list : [{ label: 'All', value: '' }]}
              onChange={(value) => { setAttributes({ id: value }) }}
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
