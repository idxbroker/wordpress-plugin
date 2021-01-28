import React, { Component } from 'react'
import styled from 'styled-components'

const CellContainer = styled.div`
  box-sizing: border-box;
  height: 207px;
  width: 199px;
  padding: 1px;
  position: relative;
  &:hover .button-container, &:hover .button-container .removal-button {
    display: flex;
  }
`
const ButtonSection = styled.div`
  position: absolute;
  top: 0;
  z-index: 1;
  height: 137px;
  width: 100%;
  display: none;
  justify-content: flex-end;
  background: linear-gradient(rgba(0, 0, 0, 0) -100%, rgba(0, 0, 0, 0.25) 100%);
  margin-left: -2px;
`
const RemoveButton = styled.button`
  display: none;
  border-color: white;
  border-radius: 50px;
  border-style: solid;
  border-width: 3px;
  height: 28px;
  width: 28px;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  background: none;
  color: white;
  margin: 5px;
  &:focus {
    outline: none;
  }
  &:active {
    background-color: tomato;
  }
`
const TitleLabel = styled.div`
  color: #00a0d2;
  font-size: 18px;
  margin-top: 15px;
  font-weight: bold;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  height: 20px;
`
const SubLabel = styled.div`
  color: #262B30;
  font-size: 18px;
  font-weight: lighter;
  padding-top: 5px;
`
const LabelsContainer = styled.div`
  height: 59px;
  width: 100%;
`
const PhotoArea = styled.img`
  background-color: #f5f7f9;
  height: 137px;
  width: 100%;
  pointer-events: none;
`

class PostCell extends Component {
  render () {
    return (
      <CellContainer
        className='post-cell'
        onClick={(event) => this.props.updatePostEditor(event, this.props)}
        onDragStart={(event) => this.props.cellDrag(event, this.props)}
        draggable
      >
        <ButtonSection className='button-container'>
          <RemoveButton className='removal-button' onClick={(event) => this.props.removalHandler(event, this.props)}>
            <span className='dashicons dashicons-no' />
          </RemoveButton>
        </ButtonSection>
        <PhotoArea src={this.props.imageUrl ? this.props.imageUrl : null} />
        <LabelsContainer>
          <TitleLabel>{this.props.title}</TitleLabel>
          <SubLabel>{this.props.date}</SubLabel>
        </LabelsContainer>
      </CellContainer>
    )
  }
}

export default PostCell
