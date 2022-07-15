import React, { useState, useEffect, useRef } from 'react'
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

function GoogleMyBusinessSettings (props) {
  const getInitialSliderValue = (currentSetting) => {
    switch (currentSetting) {
      case 'weekly':
        return 0
      case 'biweekly':
        return 1
      case 'monthly':
        return 2
      default:
        return 1
    }
  }

  const editorRef = useRef(null)
  const [bannerDismissed, setBannerDismissed] = useState(impressGmbAdmin['instruction-banner-dismissed'])
  const [postDataReady, setPostDataReady] = useState(false)
  const [savingToServer, setSavingToServer] = useState(false)
  const [growlerText, setGrowlerText] = useState('')
  const [postingDateSettings, setPostingDateSettings] = useState({
    postFrequency: impressGmbAdmin['auto-post-frequency'],
    nextPostTimestamp: impressGmbAdmin['next-scheduled-post-date'],
    frequencySliderValue: getInitialSliderValue(impressGmbAdmin['auto-post-frequency'])
  })
  const [formData, setFormData] = useState({
    id: null,
    title: '',
    imageUrl: '',
    postUrl: '',
    summary: '',
    editingMode: false
  })
  const [posts, setPosts] = useState({
    byId: {},
    allIds: [],
    scheduledIds: []
  })
  const [screenSize, setScreenSize] = useState({
    height: window.innerHeight,
    width: window.innerWidth
  })

  useEffect(() => {
    updateWindowDimensions()
    window.addEventListener('resize', updateWindowDimensions())
    getPostsData()
  }, [])

  const updateWindowDimensions = () => {
    setScreenSize({
      width: window.innerWidth,
      height: window.innerHeight
    })
  }

  const getPostsData = () => {
    const requestFormData = new FormData()
    requestFormData.append('action', 'impress_gmb_get_posts_data')
    requestFormData.append('nonce', impressGmbAdmin['nonce-gmb-get-posts-data'])
    axios.post(ajaxurl, requestFormData)
      .then((response) => {
        // Return early for non-200 status
        if (response.status !== 200) {
          setPostDataReady(true)
          return
        }
        // setState actions are async, so wait for the posts to be set before continuing
        setPosts(response.data, function() {
          if (response.data.scheduledIds.length < 4) {
            const currentSchedule = response.data.scheduledIds
            while (currentSchedule.length < 4) currentSchedule.push('-')
            setPosts({
              ...posts,
              scheduledIds: currentSchedule
            }, function(){
              setPostDataReady(true)
            })
          } else {
            setPostDataReady(true)
          }
        })
      }, (error) => {
        // If error occurs, log to console for troubleshooting
        setPostDataReady(true)
        console.log(error)
      })
  }

  const cellDrag = (event, cellData) => {
    event.dataTransfer.setData('cellData', JSON.stringify(cellData))
  }

  const cellDrop = (event, targetIndex) => {
    if (savingToServer) {
      return
    }
    const { id, dragOperationType, positionIndex, title, postUrl, imageUrl, summary, editingMode } = JSON.parse(event.dataTransfer.getData('cellData'))

    if (dragOperationType === 'scheduleReorder') {
      const currentSchedule = posts.scheduledIds
      const destinationItem = currentSchedule[targetIndex]
      currentSchedule[targetIndex] = currentSchedule[positionIndex]
      currentSchedule[positionIndex] = destinationItem
      setPosts({
        ...posts,
        scheduledIds: currentSchedule
      })
      updateSchedule(currentSchedule)
    }

    if (dragOperationType === 'addToSchedule') {
      const currentSchedule = posts.scheduledIds
      currentSchedule[targetIndex] = id
      setPosts({
        ...posts,
        scheduledIds: currentSchedule
      })
      updateSchedule(currentSchedule)
    }

    if (dragOperationType === 'createFromListing') {
      updatePostEditor(event, { title, postUrl, imageUrl, summary, editingMode })
    }
  }

  const addScheduleCells = () => {
    setPosts({
      ...posts,
      scheduledIds: posts.scheduledIds.concat(['-', '-', '-', '-'])
    })
  }

  const updateSchedule = (schedule) => {
    setSavingToServer(true)
    setGrowlerText('Saving...')
    const requestFormData = new FormData()
    requestFormData.append('action', 'impress_gmb_update_scheduled_posts')
    requestFormData.append('nonce', impressGmbAdmin['nonce-gmb-update-scheduled-posts'])
    requestFormData.append('scheduled_posts', schedule)
    axios.post(ajaxurl, requestFormData)
      .then((response) => {
        // Return early for non-200 status
        if (response.status !== 200) {
          setSavingToServer(false)
          setGrowlerText('Save failed')
          return
        }
        // Update state on success
        setSavingToServer(false)
        setGrowlerText('Saved!')
        setPosts({
          ...posts,
          scheduledIds: response.data
        })
      }, (error) => {
        setSavingToServer(false)
        setGrowlerText('Error: ' + error.response)
      })
  }

  const updateFrequency = (event) => {
    setPostingDateSettings({
      ...postingDateSettings,
      frequencySliderValue: event.target.value,
      postFrequency: getFrequencyString(event.target.value)
    })
    const requestFormData = new FormData()
    requestFormData.append('action', 'impress_gmb_change_posting_frequency')
    requestFormData.append('nonce', impressGmbAdmin['nonce-gmb-update-post-frequency'])
    requestFormData.append('interval', event.target.value)
    axios.post(ajaxurl, requestFormData)
      .then((response) => {
      }, (error) => {
        console.log(error)
      })
  }

  const getFrequencyString = (value) => {
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

  const handleChange = (event) => {
    setFormData({
      ...formData,
      [event.target.name]: event.target.value
    })
  }

  const handleSubmit = (event) => {
    event.preventDefault()
  }

  const removeFromSchedule = (event, cellData) => {
    event.stopPropagation()
    if (savingToServer) {
      return
    }

    const { positionIndex } = cellData
    const currentSchedule = posts.scheduledIds

    if (posts.scheduledIds[parseInt(positionIndex)] !== '-') {
      currentSchedule.splice(parseInt(positionIndex), 1, '-')
    } else {
      currentSchedule.splice(parseInt(positionIndex), 1)
    }

    setSavingToServer(true)
    setGrowlerText('Removing...')
    setPosts({
      ...posts,
      scheduledIds: currentSchedule
    })
    const requestFormData = new FormData()
    requestFormData.append('action', 'impress_gmb_remove_from_schedule')
    requestFormData.append('nonce', impressGmbAdmin['nonce-gmb-remove-from-schedule'])
    requestFormData.append('index', positionIndex)
    axios.post(ajaxurl, requestFormData)
      .then((response) => {
        // Return early for non-200 status
        if (response.status !== 200) {
          setSavingToServer(false)
          setGrowlerText('Change failed')
          // Get fresh copy of server data in case of failure
          getPostsData()
          return
        }
        // Update state on success
        setSavingToServer(false)
        setGrowlerText('Removed!')
      }, (error) => {
        setSavingToServer(false)
        setGrowlerText('Error: ' + error.response)
        // Get fresh copy of server data in case of failure
        getPostsData()
      })
  }

  const deletePost = (event, cellData) => {
    event.stopPropagation()
    if (savingToServer) {
      return
    }
    const { id } = cellData
    const confirmation = confirm('Delete post?')
    if (confirmation) {
      const currentPosts = posts.byId
      delete currentPosts[parseInt(id)]

      // Clear form if deleted cell happened to be in the editor at the time.
      if (formData.id === id) {
        setFormData({
          id: null,
          title: '',
          imageUrl: '',
          postUrl: '',
          summary: '',
          editingMode: false
        })
      }

      setSavingToServer(true)
      setGrowlerText('Deleting post...')
      setPosts({
        ...posts,
        byId: currentPosts,
        allIds: posts.allIds.filter(i => i.toString() !== id),
        scheduledIds: posts.scheduledIds.filter(i => i.toString() !== id)
      })

      const requestFormData = new FormData()
      requestFormData.append('action', 'impress_gmb_delete_custom_post')
      requestFormData.append('nonce', impressGmbAdmin['nonce-gmb-delete-custom-post'])
      requestFormData.append('postId', parseInt(id))
      axios.post(ajaxurl, requestFormData)
        .then((response) => {
          // Return early for non-200 status
          if (response.status !== 200) {
            setSavingToServer(false)
            setGrowlerText('Deletion failed')
            // Get fresh copy of server data in case of failure
            getPostsData()
            return
          }
          // Update state on success
          setSavingToServer(false)
          setGrowlerText('Deleted!')
        }, (error) => {
          setSavingToServer(false)
          setGrowlerText('Error: ' + error.response)
          // Get fresh copy of server data in case of failure
          getPostsData()
        })
    }
  }

  const savePost = (event) => {
    const { id, title, imageUrl, postUrl, summary } = formData
    if (!title || !imageUrl || !postUrl || !summary) {
      return
    }
    setSavingToServer(true)
    setGrowlerText('Saving post...')
    const requestFormData = new FormData()
    requestFormData.append('action', 'impress_gmb_save_custom_post')
    requestFormData.append('nonce', impressGmbAdmin['nonce-gmb-save-custom-post'])
    requestFormData.append('title', title)
    requestFormData.append('postUrl', postUrl)
    requestFormData.append('imageUrl', imageUrl)
    requestFormData.append('summary', summary)
    if (id) {
      requestFormData.append('id', id)
    }
    axios.post(ajaxurl, requestFormData)
      .then((response) => {
        // Return early for non-200 status
        if (response.status !== 200) {
          setSavingToServer(false)
          setGrowlerText('Save failed')
          return
        }
        // Update state on success
        let updatedScheduleIds
        if (!formData.editingMode) {
          const blankEntryIndex = posts.scheduledIds.indexOf('-')
          if (blankEntryIndex === -1) {
            updatedScheduleIds = [...posts.scheduledIds, [response.data.id]]
          } else {
            updatedScheduleIds = posts.scheduledIds
            updatedScheduleIds[blankEntryIndex] = response.data.id
          }
        }
        setSavingToServer(false)
        setGrowlerText('Saved!')
        setFormData({
          id: null,
          title: '',
          imageUrl: '',
          postUrl: '',
          summary: '',
          editingMode: false
        })
        setPosts({
          ...posts,
          byId: { ...posts.byId, [response.data.id]: { title: response.data.title, postUrl: response.data.postUrl, imageUrl: response.data.imageUrl, summary: response.data.summary } },
          allIds: [...posts.allIds, [response.data.id]],
          scheduledIds: updatedScheduleIds || posts.scheduledIds
        })
      }, (error) => {
        setSavingToServer(false)
        setGrowlerText('Error: ' + error.response)
      })
  }

  const postNow = (event) => {
    const { id, title, imageUrl, postUrl, summary, editingMode } = formData
    if (!title || !imageUrl || !postUrl || !summary) {
      return
    }
    const confirmation = confirm('Post now?')
    if (confirmation) {
      setSavingToServer(true)
      setGrowlerText('Submiting Post...')
      const requestFormData = new FormData()
      requestFormData.append('action', 'impress_gmb_post_now')
      requestFormData.append('nonce', impressGmbAdmin['nonce-gmb-post-now'])
      requestFormData.append('title', title)
      requestFormData.append('postUrl', postUrl)
      requestFormData.append('imageUrl', imageUrl)
      requestFormData.append('summary', summary)
      if (id && editingMode) {
        requestFormData.append('id', id)
      }

      axios.post(ajaxurl, requestFormData)
        .then((response) => {
          // Return early for non-200 status
          if (response.status !== 200) {
            setSavingToServer(true)
            setGrowlerText('Submission failed: check Listings > Settings > IDX > Google My Business for more details')
            return
          }
          // Update state on success
          setSavingToServer(false)
          setGrowlerText('Submission complete!')
          setFormData({
            id: null,
            title: '',
            imageUrl: '',
            postUrl: '',
            summary: '',
            editingMode: false
          })
        }, (error) => {
          setSavingToServer(false)
          setGrowlerText('Error: ' + error.response)
        })
    }
  }

  const dismissBanner = () => {
    setBannerDismissed(true)
    setSavingToServer(true)
    const requestFormData = new FormData()
    requestFormData.append('action', 'impress_gmb_dismiss_banner')
    requestFormData.append('nonce', impressGmbAdmin['nonce-gmb-dismiss-banner'])
    axios.post(ajaxurl, requestFormData)
      .then((response) => {
        setSavingToServer(true)
      }, (error) => {
        console.log(error)
      })
  }

  const updatePostEditor = (event, cellData) => {
    event.preventDefault()
    event.stopPropagation()
    const { id, title, imageUrl, postUrl, summary, editingMode } = cellData
    setFormData({
      id,
      title,
      imageUrl,
      postUrl,
      summary,
      editingMode
    })
    window.scroll({ top: 70, left: 0, behavior: 'smooth' })
  }

  return (
    <div>
      <SettingsPageWrapper title='IMPress Listings - Google My Business Settings'>
        <SettingsContainer>
          {bannerDismissed ? null : <NotificationBanner dismissBanner={dismissBanner} />}
          <SettingsRow>
            <PostEditorWrapper ref={editorRef}>
              <PostEditor
                formData={formData}
                savingToServer={savingToServer}
                onFormChange={handleChange}
                onFormSubmit={handleSubmit}
                savePost={savePost}
                postNow={postNow}
              />
            </PostEditorWrapper>
            <SchedulePane
              posts={posts}
              removeFromSchedule={removeFromSchedule}
              updatePostEditor={updatePostEditor}
              updateFrequency={updateFrequency}
              postingDateSettings={postingDateSettings}
              cellDrag={cellDrag}
              cellDrop={cellDrop}
              addScheduleCells={addScheduleCells}
              isLoaded={postDataReady}
            />
          </SettingsRow>
          <HorizontalRule />
          <SettingsRow>
            <SavedPostsPane
              posts={posts}
              isLoaded={postDataReady}
              updatePostEditor={updatePostEditor}
              deletePost={deletePost}
              cellDrag={cellDrag}
              screenSize={screenSize}
            />
          </SettingsRow>
          <HorizontalRule />
          <SettingsRow>
            <ListingPostsPane updatePostEditor={updatePostEditor} cellDrag={cellDrag} />
          </SettingsRow>
        </SettingsContainer>
      </SettingsPageWrapper>
      <GrowlerNotification text={growlerText} showGrowler={savingToServer} />
    </div>
  )

}

export default GoogleMyBusinessSettings

ReactDOM.render(<GoogleMyBusinessSettings />, document.getElementById('gmb-settings-app'))
