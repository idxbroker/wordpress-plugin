import React, { Component } from 'react'
import styled from 'styled-components'

const BannerContainer = styled.div`
  background-color: #0073AA;
  color: white;
  height: 105px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0px 30px 0px 39px;
`
const BannerLabel = styled.div`
  font-size: 1.85em;
  font-weight: 200;
  @media (max-width: 500px) {
    font-size: 1.5em;
    width: 80%;
  }
`
const Button = styled.button`
  align-items: center;
  background: none;
  border: none;
  display: flex;
  height: 54px;
  justify-content: center;
  width: 54px;
  &:focus {
   outline: none;
  }
  &:active svg circle {
    fill: white;
    fill-opacity: 1;
  }
  &:active svg line {
    stroke: #0073AA;
  }
`
const ButtonIcon = styled.svg`
  overflow: visible;
`
const Circle = styled.circle`
  stroke: white;
`
const Line = styled.line`
  stroke: white;
  stroke-width: 4;
`

class NotificationBanner extends Component {
  render () {
    return (
      <BannerContainer>
        <BannerLabel>Create a new post or drag a listing to &ldquo;Scheduled Posts&rdquo; to get started.</BannerLabel>
        <Button onClick={() => this.props.dismissBanner()}>
          <ButtonIcon height='18' width='18'>
            <Circle cx='9' cy='9' r='27' strokeWidth='3' fillOpacity='0' />
            <Line x1='0' y1='0' x2='18' y2='18' strokeLinecap='round' />
            <Line x1='18' y1='0' x2='0' y2='18' strokeLinecap='round' />
          </ButtonIcon>
        </Button>
      </BannerContainer>
    )
  }
}

export default NotificationBanner
