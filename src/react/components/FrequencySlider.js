import React, { Component } from 'react'
import styled from 'styled-components'

const LabelsContainer = styled.div`
  display: flex;
  justify-content: space-between;
  height: 40px;
  margin-top: 9px;
  width: 100%;
`
const Label = styled.div`
  font-size: 16px;
  font-weight: lighter;
`
const Slider = styled.input`
  border: 2px solid #a0a5aa;
  height: 0px;
  width: 100%;
  -webkit-appearance: none;
  border-right-width: 0px;
  border-left-width: 0px;
  &:focus {
    outline: none;
  }
  &::-webkit-slider-thumb {
    position: relative;
    z-index: 1;
    -webkit-appearance: none;
    height: 26px;
    width: 26px;
    border-radius: 100%;
    border: solid 2px #008abe;
    background-color: #00a0d2;
    margin-bottom: 1px;
  }
`
const DecorationContainer = styled.div`
  postition: absolute;
  pointer-events: none;
  height: 26px;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  justify-content: space-between;
  padding: 0px;
  margin: -19.5px -1px 0px 1px;
`
const SliderDecoration = styled.span`
  box-sizing: border-box;
  position: relative;
  display: flex;
  justify-content: center;
  width: 26px;
  background-color: #b4b9be;
  border: solid 2px #a0a5aa;
  height: 26px;
  border-radius: 100%;
  line-height: 50px;
  margin-bottom: 20px;
`

class FrequencySlider extends Component {
  render () {
    return (
      <>
        <Slider type='range' min='0' max='2' step='1' onChange={(event) => this.props.updateFrequency(event)} value={this.props.frequencySliderValue}/>
        <DecorationContainer>
          <SliderDecoration />
          <SliderDecoration />
          <SliderDecoration />
        </DecorationContainer>
        <LabelsContainer>
          <Label>Weekly</Label>
          <Label>Bi-weekly</Label>
          <Label>Monthly</Label>
        </LabelsContainer>
      </>
    )
  }
}

export default FrequencySlider
