import React, { Component } from 'react'
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

class SavedPostsPane extends Component {
  constructor () {
    super()
    this.cellScrollerRef = React.createRef()
  }

  leftArrowPressed () {
    this.cellScrollerRef.current.scrollBy({
      left: -400,
      behavior: 'smooth'
    })
  }

  rightArrowPressed () {
    this.cellScrollerRef.current.scrollBy({
      left: 400,
      behavior: 'smooth'
    })
  }

  hideButtons () {
    const cellCount = this.props.posts.allIds.length
    // '249' is cell width (199px) + right margin (50px).
    const combinedCellWidth = cellCount * 249

    if (!cellCount) {
      return true
    }

    // Account for WP sidebar (360px) on screens above 959px wide.
    if (this.props.screenSize.width > 959) {
      if ((this.props.screenSize.width - 360) < combinedCellWidth) {
        return false
      } else {
        return true
      }
    } else {
      if (this.props.screenSize.width < combinedCellWidth) {
        return false
      } else {
        return true
      }
    }

  }

  render () {
    const { isLoaded, updatePostEditor, deletePost, cellDrag } = this.props
    const posts = this.props.posts.byId

    let loadingIndicator, leftButton, rightButton

    if (!isLoaded) {
      loadingIndicator = <LoadingSpinner />
    }

    if (!this.hideButtons()) {
      leftButton =
        <Button onClick={() => this.leftArrowPressed()}>
          <ButtonIcon height='18' width='18'>
            <Circle cx='18' cy='18' r='31.5' />
            <Line x1='8' y1='18' x2='24' y2='3' />
            <Line x1='8' y1='18' x2='24' y2='33' />
          </ButtonIcon>
        </Button>

      rightButton =
        <Button onClick={() => this.rightArrowPressed()}>
          <ButtonIcon height='18' width='18'>
            <Circle cx='18' cy='18' r='31' strokeWidth='4' />
            <Line x1='28' y1='18' x2='12' y2='3' strokeLinecap='round' />
            <Line x1='28' y1='18' x2='12' y2='33' strokeLinecap='round' />
          </ButtonIcon>
        </Button>
    }

    return (
      <>
        <SavedPostsContainer>
          <HeaderContainer>
            <Header>Saved Posts</Header>
          </HeaderContainer>
          {loadingIndicator}
          <CarouselContainer>
            {leftButton}
            <CustomPostCellsContainer ref={this.cellScrollerRef} hideButtons={this.hideButtons()}>
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
            {rightButton}
          </CarouselContainer>
        </SavedPostsContainer>
      </>
    )
  }
}

export default SavedPostsPane
