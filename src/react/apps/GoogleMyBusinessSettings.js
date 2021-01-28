import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import SettingsPageWrapper from '../components/SettingsPageWrapper.js'
import NotificationBanner from '../components/NotificationBanner.js'
import GrowlerNotification from '../components/GrowlerNotification.js'
import PostEditor from '../components/PostEditor.js'
import SchedulePane from '../components/SchedulePane.js'
import SavedPostsPane from '../components/SavedPostsPane.js'
import ListingPostsPane from '../components/ListingPostsPane.js'
import axios from 'axios'
import styled from 'styled-components'

/* global impressGmbAdmin, ajaxurl, FormData, confirm */
/* eslint no-undef: "error" */

const HorizontalRule = styled.hr`
  border-bottom: 1px solid #ACACAC;
`
const PostEditorWrapper = styled.div`
  flex: 1 0 auto;
`
const SettingsContainer = styled.div`
  background-color: white;
  min-height: 500px;
`
const SettingsRow = styled.div`
  display: flex;
  justify-content: space-between;
  max-width: 1717px;
  @media (max-width: 1400px) {
    flex-direction: column;
    padding: 30px;
  }
  @media (min-width: 1401px) {
    padding: 30px;
  }
  @media (min-width: 1900px) {
    padding: 37px 98px 63px 93px;
  }
  @media (min-width: 2000px) {
    justify-content: space-around;
    margin: auto;
  }
`

class GoogleMyBusinessSettings extends Component {
  constructor () {
    super()
    let initialSliderValue
    switch (impressGmbAdmin['auto-post-frequency']) {
      case 'weekly':
        initialSliderValue = 0
        break
      case 'biweekly':
        initialSliderValue = 1
        break
      case 'monthly':
        initialSliderValue = 2
        break
      default:
        initialSliderValue = 1
    }
    this.state = {
      bannerDismissed: impressGmbAdmin['instruction-banner-dismissed'],
      postDataReady: false,
      savingToServer: false,
      growlerText: '',
      postingDateSettings: {
        postFrequency: impressGmbAdmin['auto-post-frequency'],
        nextPostTimestamp: impressGmbAdmin['next-scheduled-post-date'],
        frequencySliderValue: initialSliderValue
      },
      formData: {
        id: null,
        title: '',
        imageUrl: '',
        postUrl: '',
        summary: '',
        editingMode: false
      },
      posts: {
        byId: {},
        allIds: [],
        scheduledIds: []
      },
      screenSize: {
        height: window.innerHeight,
        width: window.innerWidth
      }
    }
    this.updatePostEditor = this.updatePostEditor.bind(this)
    this.dismissBanner = this.dismissBanner.bind(this)
    this.removeFromSchedule = this.removeFromSchedule.bind(this)
    this.deletePost = this.deletePost.bind(this)
    this.handleChange = this.handleChange.bind(this)
    this.handleSubmit = this.handleSubmit.bind(this)
    this.cellDrag = this.cellDrag.bind(this)
    this.cellDrop = this.cellDrop.bind(this)
    this.addScheduleCells = this.addScheduleCells.bind(this)
    this.updateSchedule = this.updateSchedule.bind(this)
    this.savePost = this.savePost.bind(this)
    this.postNow = this.postNow.bind(this)
    this.updateFrequency = this.updateFrequency.bind(this)
    this.updateWindowDimensions = this.updateWindowDimensions.bind(this)
    this.editorRef = React.createRef()
    this.getPostsData()
  }

  componentDidMount () {
    this.updateWindowDimensions()
    window.addEventListener('resize', this.updateWindowDimensions)
  }

  componentWillUnmount () {
    window.removeEventListener('resize', this.updateWindowDimensions)
  }

  updateWindowDimensions () {
    this.setState({
      screenSize: {
        width: window.innerWidth,
        height: window.innerHeight
      }
    })
  }

  getPostsData () {
    const formData = new FormData()
    formData.append('action', 'impress_gmb_get_posts_data')
    formData.append('nonce', impressGmbAdmin['nonce-gmb-get-posts-data'])
    axios.post(ajaxurl, formData)
      .then((response) => {
        // Return early for non-200 status
        if (response.status !== 200) {
          this.setState({
            postDataReady: true
          })
          return
        }
        this.setState({
          posts: response.data,
          postDataReady: true
        })

        if (response.data.scheduledIds.length < 4) {
          const currentSchedule = response.data.scheduledIds
          while (currentSchedule.length < 4) currentSchedule.push('-')
          this.setState({
            posts: {
              ...this.state.posts,
              scheduledIds: currentSchedule
            }
          })
        }
      }, (error) => {
        // If error occurs, log to console for troubleshooting
        this.setState({
          postDataReady: true
        })
        console.log(error)
      })
  }

  cellDrag (event, cellData) {
    event.dataTransfer.setData('cellData', JSON.stringify(cellData))
  }

  cellDrop (event, targetIndex) {
    if (this.state.savingToServer) {
      return
    }
    const { id, dragOperationType, positionIndex, title, postUrl, imageUrl, summary, editingMode } = JSON.parse(event.dataTransfer.getData('cellData'))

    if (dragOperationType === 'scheduleReorder') {
      const currentSchedule = this.state.posts.scheduledIds
      const destinationItem = currentSchedule[targetIndex]
      currentSchedule[targetIndex] = currentSchedule[positionIndex]
      currentSchedule[positionIndex] = destinationItem
      this.setState({
        posts: {
          ...this.state.posts,
          scheduledIds: currentSchedule
        }
      })
      this.updateSchedule(currentSchedule)
    }

    if (dragOperationType === 'addToSchedule') {
      const currentSchedule = this.state.posts.scheduledIds
      currentSchedule[targetIndex] = id
      this.setState({
        posts: {
          ...this.state.posts,
          scheduledIds: currentSchedule
        }
      })
      this.updateSchedule(currentSchedule)
    }

    if (dragOperationType === 'createFromListing') {
      this.updatePostEditor(event, { title, postUrl, imageUrl, summary, editingMode })
    }
  }

  addScheduleCells () {
    this.setState({
      posts: {
        ...this.state.posts,
        scheduledIds: this.state.posts.scheduledIds.concat(['-', '-', '-', '-'])
      }
    })
  }

  updateSchedule (schedule) {
    this.setState({
      savingToServer: true,
      growlerText: 'Saving...'
    })
    const formData = new FormData()
    formData.append('action', 'impress_gmb_update_scheduled_posts')
    formData.append('nonce', impressGmbAdmin['nonce-gmb-update-scheduled-posts'])
    formData.append('scheduled_posts', schedule)
    axios.post(ajaxurl, formData)
      .then((response) => {
        // Return early for non-200 status
        if (response.status !== 200) {
          this.setState({
            savingToServer: false,
            growlerText: 'Save failed'
          })
          return
        }
        // Update state on success
        this.setState({
          savingToServer: false,
          growlerText: 'Saved!',
          posts: {
            ...this.state.posts,
            scheduledIds: response.data
          }
        })
      }, (error) => {
        this.setState({
          savingToServer: false,
          growlerText: 'Error: ' + error.response
        })
      })
  }

  updateFrequency (event) {
    this.setState({
      postingDateSettings: {
        ...this.state.postingDateSettings,
        frequencySliderValue: event.target.value,
        postFrequency: this.getFrequencyString(event.target.value)
      }
    })
    const formData = new FormData()
    formData.append('action', 'impress_gmb_change_posting_frequency')
    formData.append('nonce', impressGmbAdmin['nonce-gmb-update-post-frequency'])
    formData.append('interval', event.target.value)
    axios.post(ajaxurl, formData)
      .then((response) => {
      }, (error) => {
        console.log(error)
      })
  }

  getFrequencyString (value) {
    switch (parseInt(value)) {
      case 0:
        return 'weekly'
      case 1:
        return 'biweekly'
      case 2:
        return 'monthly'
      default:
        return 'weekly'
    }
  }

  handleChange (event) {
    this.setState({
      formData: {
        ...this.state.formData,
        [event.target.name]: event.target.value
      }
    })
  }

  handleSubmit (event) {
    event.preventDefault()
  }

  removeFromSchedule (event, cellData) {
    event.stopPropagation()
    if (this.state.savingToServer) {
      return
    }

    const { positionIndex } = cellData
    const currentSchedule = this.state.posts.scheduledIds

    if (this.state.posts.scheduledIds[parseInt(positionIndex)] !== '-') {
      currentSchedule.splice(parseInt(positionIndex), 1, '-')
    } else {
      currentSchedule.splice(parseInt(positionIndex), 1)
    }

    this.setState({
      savingToServer: true,
      growlerText: 'Removing...',
      posts: {
        ...this.state.posts,
        scheduledIds: currentSchedule
      }
    })
    const formData = new FormData()
    formData.append('action', 'impress_gmb_remove_from_schedule')
    formData.append('nonce', impressGmbAdmin['nonce-gmb-remove-from-schedule'])
    formData.append('index', positionIndex)
    axios.post(ajaxurl, formData)
      .then((response) => {
        // Return early for non-200 status
        if (response.status !== 200) {
          this.setState({
            savingToServer: false,
            growlerText: 'Change failed'
          })
          // Get fresh copy of server data in case of failure
          this.getPostsData()
          return
        }
        // Update state on success
        this.setState({
          savingToServer: false,
          growlerText: 'Removed!'
        })
      }, (error) => {
        this.setState({
          savingToServer: false,
          growlerText: 'Error: ' + error.response
        })
        // Get fresh copy of server data in case of failure
        this.getPostsData()
      })
  }

  deletePost (event, cellData) {
    event.stopPropagation()
    if (this.state.savingToServer) {
      return
    }
    const { id } = cellData
    const confirmation = confirm('Delete post?')
    if (confirmation) {
      const posts = this.state.posts.byId
      delete posts[parseInt(id)]

      // Clear form if deleted cell happened to be in the editor at the time.
      if (this.state.formData.id === id) {
        this.setState({
          formData: {
            id: null,
            title: '',
            imageUrl: '',
            postUrl: '',
            summary: '',
            editingMode: false
          }
        })
      }

      this.setState({
        savingToServer: true,
        growlerText: 'Deleting post...',
        posts: {
          ...this.state.posts,
          byId: posts,
          allIds: this.state.posts.allIds.filter(i => i.toString() !== id),
          scheduledIds: this.state.posts.scheduledIds.filter(i => i.toString() !== id)
        }
      })
      const formData = new FormData()
      formData.append('action', 'impress_gmb_delete_custom_post')
      formData.append('nonce', impressGmbAdmin['nonce-gmb-delete-custom-post'])
      formData.append('postId', parseInt(id))
      axios.post(ajaxurl, formData)
        .then((response) => {
          // Return early for non-200 status
          if (response.status !== 200) {
            this.setState({
              savingToServer: false,
              growlerText: 'Deletion failed'
            })
            // Get fresh copy of server data in case of failure
            this.getPostsData()
            return
          }
          // Update state on success
          this.setState({
            savingToServer: false,
            growlerText: 'Deleted!'
          })
        }, (error) => {
          this.setState({
            savingToServer: false,
            growlerText: 'Error: ' + error.response
          })
          // Get fresh copy of server data in case of failure
          this.getPostsData()
        })
    }
  }

  savePost (event) {
    const { id, title, imageUrl, postUrl, summary } = this.state.formData
    if (!title || !imageUrl || !postUrl || !summary) {
      return
    }
    this.setState({
      savingToServer: true,
      growlerText: 'Saving post...'
    })
    const formData = new FormData()
    formData.append('action', 'impress_gmb_save_custom_post')
    formData.append('nonce', impressGmbAdmin['nonce-gmb-save-custom-post'])
    formData.append('title', title)
    formData.append('postUrl', postUrl)
    formData.append('imageUrl', imageUrl)
    formData.append('summary', summary)
    if (id) {
      formData.append('id', id)
    }
    axios.post(ajaxurl, formData)
      .then((response) => {
        // Return early for non-200 status
        if (response.status !== 200) {
          this.setState({
            savingToServer: false,
            growlerText: 'Save failed'
          })
          return
        }
        // Update state on success
        let updatedScheduleIds
        if (!this.state.formData.editingMode) {
          const blankEntryIndex = this.state.posts.scheduledIds.indexOf('-')
          if (blankEntryIndex === -1) {
            updatedScheduleIds = [...this.state.posts.scheduledIds, [response.data.id]]
          } else {
            updatedScheduleIds = this.state.posts.scheduledIds
            updatedScheduleIds[blankEntryIndex] = response.data.id
          }
        }
        this.setState({
          savingToServer: false,
          growlerText: 'Saved!',
          formData: {
            id: null,
            title: '',
            imageUrl: '',
            postUrl: '',
            summary: '',
            editingMode: false
          },
          posts: {
            ...this.state.posts,
            byId: { ...this.state.posts.byId, [response.data.id]: { title: response.data.title, postUrl: response.data.postUrl, imageUrl: response.data.imageUrl, summary: response.data.summary } },
            allIds: [...this.state.posts.allIds, [response.data.id]],
            scheduledIds: updatedScheduleIds || this.state.posts.scheduledIds
          }
        })
      }, (error) => {
        this.setState({
          savingToServer: false,
          growlerText: 'Error: ' + error.response
        })
      })
  }

  postNow (event) {
    const { id, title, imageUrl, postUrl, summary, editingMode } = this.state.formData
    if (!title || !imageUrl || !postUrl || !summary) {
      return
    }
    const confirmation = confirm('Post now?')
    if (confirmation) {
      this.setState({
        savingToServer: true,
        growlerText: 'Submiting Post...'
      })
      const formData = new FormData()
      formData.append('action', 'impress_gmb_post_now')
      formData.append('nonce', impressGmbAdmin['nonce-gmb-post-now'])
      formData.append('title', title)
      formData.append('postUrl', postUrl)
      formData.append('imageUrl', imageUrl)
      formData.append('summary', summary)
      if (id && editingMode) {
        formData.append('id', id)
      }

      axios.post(ajaxurl, formData)
        .then((response) => {
          // Return early for non-200 status
          if (response.status !== 200) {
            this.setState({
              savingToServer: false,
              growlerText: 'Submission failed: check Listings > Settings > IDX > Google My Business for more details'
            })
            return
          }
          // Update state on success
          this.setState({
            savingToServer: false,
            growlerText: 'Submission complete!',
            formData: {
              id: null,
              title: '',
              imageUrl: '',
              postUrl: '',
              summary: '',
              editingMode: false
            }
          })
        }, (error) => {
          this.setState({
            savingToServer: false,
            growlerText: 'Error: ' + error.response
          })
        })
    }
  }

  dismissBanner () {
    this.setState({
      bannerDismissed: true,
      savingToServer: true
    })
    const formData = new FormData()
    formData.append('action', 'impress_gmb_dismiss_banner')
    formData.append('nonce', impressGmbAdmin['nonce-gmb-dismiss-banner'])
    axios.post(ajaxurl, formData)
      .then((response) => {
        this.setState({
          savingToServer: true
        })
      }, (error) => {
        console.log(error)
      })
  }

  updatePostEditor (event, cellData) {
    event.preventDefault()
    event.stopPropagation()
    const { id, title, imageUrl, postUrl, summary, editingMode } = cellData
    this.setState({
      formData: {
        id,
        title,
        imageUrl,
        postUrl,
        summary,
        editingMode
      }
    })
    window.scroll({ top: 70, left: 0, behavior: 'smooth' })
  }

  render () {
    return (
      <div>
        <SettingsPageWrapper title='IMPress Listings - Google My Business Settings'>
          <SettingsContainer>
            {this.state.bannerDismissed ? null : <NotificationBanner dismissBanner={this.dismissBanner} />}
            <SettingsRow>
              <PostEditorWrapper ref={this.editorRef}>
                <PostEditor
                  formData={this.state.formData}
                  savingToServer={this.state.savingToServer}
                  onFormChange={this.handleChange}
                  onFormSubmit={this.handleSubmit}
                  savePost={this.savePost}
                  postNow={this.postNow}
                />
              </PostEditorWrapper>
              <SchedulePane
                posts={this.state.posts}
                removeFromSchedule={this.removeFromSchedule}
                updatePostEditor={this.updatePostEditor}
                updateFrequency={this.updateFrequency}
                postingDateSettings={this.state.postingDateSettings}
                cellDrag={this.cellDrag}
                cellDrop={this.cellDrop}
                addScheduleCells={this.addScheduleCells}
                isLoaded={this.state.postDataReady}
              />
            </SettingsRow>
            <HorizontalRule />
            <SettingsRow>
              <SavedPostsPane
                posts={this.state.posts}
                isLoaded={this.state.postDataReady}
                updatePostEditor={this.updatePostEditor}
                deletePost={this.deletePost}
                cellDrag={this.cellDrag}
                screenSize={this.state.screenSize}
              />
            </SettingsRow>
            <HorizontalRule />
            <SettingsRow>
              <ListingPostsPane updatePostEditor={this.updatePostEditor} cellDrag={this.cellDrag} />
            </SettingsRow>
          </SettingsContainer>
        </SettingsPageWrapper>
        <GrowlerNotification text={this.state.growlerText} showGrowler={this.state.savingToServer} />
      </div>
    )
  }
}

export default GoogleMyBusinessSettings

ReactDOM.render(<GoogleMyBusinessSettings />, document.getElementById('gmb-settings-app'))
