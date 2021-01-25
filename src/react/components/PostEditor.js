import React, { Component } from 'react'
import styled from 'styled-components'
import UploadImageIcon from './UploadImageIcon.js'

const Button = styled.button`
  background-color: #00A0D2;
  border: 1px solid #008ABE;
  border-radius: 6px;
  color: white;
  font-size: 17px;
  font-weight: 100;
  height: 53px;
  margin: 1px 28px 1px 0px;
  width: 164px;
  &:focus {
    outline: none;
  }
  &:active {
    color: #00a0d2;
    background-color: white;
  }
  &:disabled {
    opacity: 0.5;
  }
`
const ButtonContainer = styled.div`
  height: 55px;
  margin-top: 43px;
  width: 100%;
`
const EditorContainer = styled.div`
  flex: 1 0 auto;
  max-width: 440px;
  height: 815px;
  background-color: #f7f7f7;
  padding: 0px 30px;
  @media (max-width: 1400px) {
    margin: auto;
  }
`
const Header = styled.div`
  align-items: center;
  background-color: #a0a5aa;
  color: white;
  display: flex;
  font-size: 20px;
  font-weight: 500;
  height: 66px;
  justify-content: center;
  margin: 0px -30px;
`
const ImageSelectionWrapper = styled.div`
  border: 1px solid #EFEFEF;
  border-radius: 0px;
  height: 238px;
  width: 100%;
  background-color: white;
  margin-top: 43px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  position: relative;
  &:hover > .upload-photo-icon {
    z-index: 1;
  }
`
const TextInputField = styled.input`
  border: 1px solid #EFEFEF !important;
  border-radius: 0px !important;
  font-size: 18px;
  height: 54px;
  width: 100%;
  margin-top: 30px;
  padding-left: 18px !important;
`
const TextAreaField = styled.textarea`
  border: 1px solid #EFEFEF;
  border-radius: 0px;
  font-size: 18px;
  height: 125px;
  margin-top: 30px;
  padding: 15px 18px;
  resize: none;
  width: 100%;
`
const ImageSelectLabel = styled.div`
  font-size: 18px;
  font-weight: bold;
  margin-top: 14px;
  padding-right: 10px;
`
const SelectedImage = styled.img`
  height: 100%;
  width: 100%;
  position: absolute;
  top: 0;
  left: 0;
  object-fit: cover;
  &[src=""]{
    display: none;
  }
`

class PostEditor extends Component {

  chooseImage () {
    const imageFrame = wp.media({
      title: 'Select Media',
      multiple: false,
      library: {
        type: 'image'
      }
    })

    imageFrame.on('close', () => {
      // On close, get selections and save to the hidden input
      // plus other AJAX stuff to refresh the image preview
      if (imageFrame.state().get('selection').first()) {
        const event = {
          target: {
            name: 'imageUrl',
            value: imageFrame.state().get('selection').first().toJSON().url
          }
        }
        this.props.onFormChange(event)
      }
    })

    imageFrame.open()
  }

  render () {
    const { title, imageUrl, postUrl, summary, editingMode } = this.props.formData

    return (
      <EditorContainer>
        <Header>{editingMode ? 'Edit Post' : 'Create a Post'}</Header>
        <form onSubmit={(event) => this.props.onFormSubmit(event)}>
          <ImageSelectionWrapper onClick={() => this.chooseImage()}>
            <UploadImageIcon style={imageUrl ? { fill: 'white', paddingTop: '44px' } : { fill: '#b4b9be' }} />
            <ImageSelectLabel>Upload Image</ImageSelectLabel>
            <SelectedImage src={imageUrl} />
          </ImageSelectionWrapper>

          <TextInputField id='post-title' type='text' placeholder='Title' name='title' value={title} onChange={(event) => this.props.onFormChange(event)} required />
          <TextInputField type='url' placeholder='URL' name='postUrl' value={postUrl} onChange={(event) => this.props.onFormChange(event)} required />
          <TextAreaField maxLength='1500' placeholder='Description' name='summary' value={summary} onChange={(event) => this.props.onFormChange(event)} required />

          <ButtonContainer>
            <Button onClick={(event) => this.props.savePost(event)} type='submit' disabled={this.props.savingToServer}>Save Post</Button>
            <Button onClick={(event) => this.props.postNow(event)} type='submit' disabled={this.props.savingToServer}>Post Now</Button>
          </ButtonContainer>
        </form>
      </EditorContainer>
    )
  }
}

export default PostEditor
