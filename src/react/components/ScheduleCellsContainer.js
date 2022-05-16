import React, { useCallback } from 'react'
import PostCell from './PostCell.js'
import ScheduleCell from './ScheduleCell.js'
import LoadingSpinner from './LoadingSpinner.js'
import styled from 'styled-components'

const CellContainer = styled.div`
  margin-top: 39px;
  display: grid;
  grid-template-columns: repeat(4, 200px);
  grid-row-gap: 30px;
  justify-content: space-between;
  @media (max-width: 1600px) {
    grid-template-columns: repeat(3, 200px);
  }
  @media (max-width: 1400px) {
    grid-template-columns: repeat(4, 200px);
  }
  @media (max-width: 1120px) {
    grid-template-columns: repeat(3, 200px);
  }
  @media (max-width: 720px) {
    grid-template-columns: repeat(2, 200px);
  }
  @media (max-width: 525px) {
    grid-template-columns: repeat(1, 200px);
    justify-content: center;
  }
  & > *:hover .button-container,  & > *:hover .button-container .removal-button {
    display: flex;
  }
`
const ButtonRow = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 42px;
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
    fill: #00a0d2;
    fill-opacity: 1;
  }
  &:active svg line {
    stroke: white;
  }
`
const ButtonIcon = styled.svg`
  stroke: #00a0d2;
  overflow: visible;
`
const Circle = styled.circle`
  fill-opacity: 0;
`
const Line = styled.line`
  stroke-width: 5;
`

function ScheduleCellsContainer (props) {
  
  const generateDate = useCallback((index, nextPostTimestamp) => {
    let step
    switch (props.postingDateSettings.postFrequency) {
      case 'weekly':
        step = 604800
        break
      case 'biweekly':
        step = 604800 * 2
        break
      case 'monthly':
        step = 604800 * 4
        break
      default:
        step = 604800
    }

    let projectedTimeStamp
    if (index === 0) {
      projectedTimeStamp = nextPostTimestamp * 1000
    } else {
      projectedTimeStamp = (nextPostTimestamp + (index * step)) * 1000
    }

    const date = new Date(projectedTimeStamp)
    const year = date.getUTCFullYear()
    const month = date.getUTCMonth() + 1
    const day = date.getUTCDate()
    const formattedDate = month + '/' + day + '/' + year
    return formattedDate
  }, [props.postingDateSettings.postFrequency])

  const { updatePostEditor, removeFromSchedule, cellDrag } = props
  const posts = props.posts.byId
  const scheduledIds = props.posts.scheduledIds

  const addRowButton = () => {
    if (props.isLoaded) {
      return (
        <Button onClick={(event) => props.addScheduleCells(event)}>
          <ButtonIcon height='18' width='18'>
            <Circle cx='16' cy='16' r='31.5' strokeWidth='5' />
            <Line x1='16' y1='0' x2='16' y2='32' strokeLinecap='round' />
            <Line x1='0' y1='16' x2='32' y2='16' strokeLinecap='round' />
          </ButtonIcon>
        </Button>
      )
    } else {
      return <LoadingSpinner />
    }
  }

  return (
    <>
      <CellContainer>
        {
          scheduledIds.map((id, index) => {
            let postCell
            if (id !== '-') {
              postCell =
                <PostCell
                  key={id}
                  id={id}
                  positionIndex={index}
                  title={posts[id].title}
                  postUrl={posts[id].postUrl}
                  imageUrl={posts[id].imageUrl}
                  summary={posts[id].summary}
                  date={generateDate(index, parseInt(props.postingDateSettings.nextPostTimestamp))}
                  updatePostEditor={updatePostEditor}
                  removalHandler={removeFromSchedule}
                  dragOperationType='scheduleReorder'
                  cellDrag={cellDrag}
                  editingMode
                />
            }
            return (
              <ScheduleCell
                key={index}
                positionIndex={index}
                date={generateDate(index, parseInt(props.postingDateSettings.nextPostTimestamp))}
                cellDrop={(event) => props.cellDrop(event, index)}
                removalHandler={removeFromSchedule}
                updatePostEditor={updatePostEditor}
              >
                {postCell}
              </ScheduleCell>
            )
          })
        }
      </CellContainer>
      <ButtonRow>
        {addRowButton()}
      </ButtonRow>
    </>
  )

}

export default ScheduleCellsContainer
