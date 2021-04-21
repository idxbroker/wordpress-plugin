import React from 'react'

function SettingsPageWrapper (props) {
  return (
    <div className='wrap'>
      <h1>{props.title}</h1>
      <br />
      <div id='poststuff' className='metabox-holder'>
        <div id='post-body'>
          <div id='post-body-content'>
            {props.children}
          </div>
        </div>
      </div>
    </div>
  )
}

export default SettingsPageWrapper
