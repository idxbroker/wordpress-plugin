import React, { Component } from 'react'
import FrequencySlider from './FrequencySlider.js'
import ScheduleCellsContainer from './ScheduleCellsContainer.js'
import styled from 'styled-components'

const SchedulePaneContainer = styled.div`
  margin-left: 80px;
  flex: 0 1 976px;
  @media (max-width: 1670px) {
    margin-left: 50px;
  }
  @media (max-width: 1400px) {
    margin: auto;
    width: 100%;
  }
`
const Header = styled.div`
  align-items: center;
  color: #008abe;
  display: flex;
  font-size: 24px;
  font-weight: 500;
  height: 66px;
`
const SubHeader = styled.div`
  color: #32373c;
  height: 32px;
  font-size: 16px;
  margin-top: 8px;
`
const Label = styled.div`
  font-size: 18px;
  font-weight: 500;
  margin-top: 12px;
`

class SchedulePane extends Component {
  render () {
    const { frequencySliderValue } = this.props.postingDateSettings
    return (
      <SchedulePaneContainer>
        <Header>Scheduled Posts</Header>
        <Label>Frequency</Label>
        <SubHeader>Posts expire in 7 days.</SubHeader>
        <FrequencySlider updateFrequency={this.props.updateFrequency} frequencySliderValue={frequencySliderValue} />
        <ScheduleCellsContainer
          posts={this.props.posts}
          updatePostEditor={this.props.updatePostEditor}
          removeFromSchedule={this.props.removeFromSchedule}
          postingDateSettings={this.props.postingDateSettings}
          cellDrag={this.props.cellDrag}
          cellDrop={this.props.cellDrop}
          addScheduleCells={this.props.addScheduleCells}
          isLoaded={this.props.isLoaded}
        />
      </SchedulePaneContainer>
    )
  }
}

export default SchedulePane
