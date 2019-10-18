const { __ } = wp.i18n
const { registerBlockType } = wp.blocks
const icon = () => (<i className='fas fa-code fa-2x'/>)

// workaround to prevent the custom category from throwing an console warning
function setCategory () {
  if (window.location.href.indexOf('wp-admin') !== -1) {
    return 'idx-category'
  } else {
    return 'widgets'
  }
}

registerBlockType(
  'idx-broker-platinum/idx-wrapper-tags-block', {
    title: __('IDX Broker Wrapper Tags', 'idx-broker-platinum'),
    icon: icon,
    category: setCategory(),
    edit: () => {
      return (
        <div className='idx-block-placeholder-container'>
          <img src={idx_wrapper_tags_image_url} />
        </div>
      )
    },
    save: () => {
      return (
        <div><div id='idxStart' /><div id='idxStop' /></div>
      )
    }
  }
)
