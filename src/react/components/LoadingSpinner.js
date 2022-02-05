import React from 'react'
import styled, { keyframes } from 'styled-components'

const LoadingContainer = styled.div`
  display: flex;
  width: 100%;
  justify-content: center;
  align-items: center;
`
const rotate = keyframes`
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
`
const LoadingIcon = styled.span`
  animation: ${rotate} 1s linear infinite;
  color: #008abe;
  font-size: 50px;
  height: 50px;
  width: 50px;
`

function LoadingSpinner () {
  return (
    <LoadingContainer>
      <LoadingIcon className='dashicons dashicons-update' />
    </LoadingContainer>
  )
}

export default LoadingSpinner
