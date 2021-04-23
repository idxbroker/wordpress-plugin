import React from 'react'
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

function SchedulePane (props) {
  const { frequencySliderValue } = props.postingDateSettings
  return (
    <SchedulePaneContainer>
      <Header>Scheduled Posts</Header>
      <Label>Frequency</Label>
      <SubHeader>Posts expire in 7 days.</SubHeader>
      <FrequencySlider updateFrequency={props.updateFrequency} frequencySliderValue={frequencySliderValue} />
      <ScheduleCellsContainer
        posts={props.posts}
        updatePostEditor={props.updatePostEditor}
        removeFromSchedule={props.removeFromSchedule}
        postingDateSettings={props.postingDateSettings}
        cellDrag={props.cellDrag}
        cellDrop={props.cellDrop}
        addScheduleCells={props.addScheduleCells}
        isLoaded={props.isLoaded}
      />
    </SchedulePaneContainer>
  )
}

export default SchedulePane
