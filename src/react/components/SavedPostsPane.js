import React from 'react'
import PostCell from './PostCell.js'
import LoadingSpinner from './LoadingSpinner.js'
import styled from 'styled-components'

const HeaderContainer = styled.div`
  display: flex;
`
const Header = styled.div`
  align-items: center;
  color: #008abe;
  display: flex;
  font-size: 24px;
  font-weight: 500;
  height: 42px;
  margin-bottom: 27px;
  width: 100%;
`
const SavedPostsContainer = styled.div`
  display: flex;
  flex-direction: column;
  width: 100%;
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
const CarouselContainer = styled.div`
  align-items: center;
  display: flex;
  width: 100%;
`
const CustomPostCellsContainer = styled.div`
  display: flex;
  flex: 1;
  margin: ${props => props.hideButtons ? '0px 28px' : '0px 102px'};
  overflow-x: auto;
  overflow-y: hidden;
  & > .post-cell {
    flex: 0 0 199px;
    margin-right: 50px;
  }
`
const ButtonIcon = styled.svg`
  stroke: #00a0d2;
  overflow: visible;
`
const Circle = styled.circle`
  fill-opacity: 0;
  stroke-width: 4;
`
const Line = styled.line`
  stroke-width: 4;
  stroke-linecap: round;
`

function SavedPostsPane (props) {
  const cellScrollerRef = React.createRef()

  const leftArrowPressed = () => {
    cellScrollerRef.current.scrollBy({
      left: -400,
      behavior: 'smooth'
    })
  }

  const rightArrowPressed = () => {
    cellScrollerRef.current.scrollBy({
      left: 400,
      behavior: 'smooth'
    })
  }

  const hideButtons = () => {
    const cellCount = props.posts.allIds.length
    // '249' is cell width (199px) + right margin (50px).
    const combinedCellWidth = cellCount * 249

    if (!cellCount) {
      return true
    }

    // Account for WP sidebar (360px) on screens above 959px wide.
    if (props.screenSize.width > 959) {
      if ((props.screenSize.width - 360) < combinedCellWidth) {
        return false
      } else {
        return true
      }
    } else {
      if (props.screenSize.width < combinedCellWidth) {
        return false
      } else {
        return true
      }
    }
  }

  const { isLoaded, updatePostEditor, deletePost, cellDrag } = props
  const posts = props.posts.byId

  const leftButton = () => {
    if (!hideButtons()) {
      return (
        <Button onClick={() => leftArrowPressed()}>
          <ButtonIcon height='18' width='18'>
            <Circle cx='18' cy='18' r='31.5' />
            <Line x1='8' y1='18' x2='24' y2='3' />
            <Line x1='8' y1='18' x2='24' y2='33' />
          </ButtonIcon>
        </Button>
      )
    }
  }

  const rightButton = () => {
    if (!hideButtons()) {
      return (
        <Button onClick={() => rightArrowPressed()}>
          <ButtonIcon height='18' width='18'>
            <Circle cx='18' cy='18' r='31' strokeWidth='4' />
            <Line x1='28' y1='18' x2='12' y2='3' strokeLinecap='round' />
            <Line x1='28' y1='18' x2='12' y2='33' strokeLinecap='round' />
          </ButtonIcon>
        </Button>
      )
    }
  }

  return (
    <>
      <SavedPostsContainer>
        <HeaderContainer>
          <Header>Saved Posts</Header>
        </HeaderContainer>
        {(!isLoaded) ? <LoadingSpinner /> : ''}
        <CarouselContainer>
          {leftButton()}
          <CustomPostCellsContainer ref={cellScrollerRef} hideButtons={hideButtons()}>
            {Object.keys(posts).reverse().map(id => (
              <PostCell
                key={id}
                id={id}
                title={posts[id].title}
                postUrl={posts[id].postUrl}
                imageUrl={posts[id].imageUrl}
                summary={posts[id].summary}
                date={posts[id].lastPublished}
                updatePostEditor={updatePostEditor}
                removalHandler={deletePost}
                cellDrag={cellDrag}
                dragOperationType='addToSchedule'
                editingMode
              />
            ))}
          </CustomPostCellsContainer>
          {rightButton()}
        </CarouselContainer>
      </SavedPostsContainer>
    </>
  )
}

export default SavedPostsPane
