import React, { Component } from 'react'

class SettingsPageWrapper extends Component {
  render () {
    return (
      <div className='wrap'>
        <h1>{this.props.title}</h1>
        <br />
        <div id='poststuff' className='metabox-holder'>
          <div id='post-body'>
            <div id='post-body-content'>
              {this.props.children}
            </div>
          </div>
        </div>
      </div>
    )
  }
}

export default SettingsPageWrapper
