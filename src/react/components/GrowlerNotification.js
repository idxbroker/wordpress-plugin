import React, { Component } from 'react'
import styled from 'styled-components'

const GrowlerContainer = styled.div`
  height: ${props => props.textSet ? '35px' : '0px'};
  min-width: 200px;
  padding: 3px 8px;
  margin-bottom: -41px;
  transition: ${props => props.startingTransition ? 'margin .5s' : 'margin 1.7s'};
  background-color: #00a0d2;
  position: fixed;
  bottom: 0;
  right: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  color: white;
  font-size: 18px;
  z-index: 99;
  &.growler-active {
    margin-bottom: 0px;
  }
`

class GrowlerNotification extends Component {
  render () {
    return (
      <GrowlerContainer className={this.props.showGrowler ? 'growler-active' : ''} startingTransition={this.props.showGrowler} textSet={this.props.text}>
        {this.props.text}
      </GrowlerContainer>
    )
  }
}

export default GrowlerNotification
