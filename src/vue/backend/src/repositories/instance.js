import axios from 'axios'
const { wpApiSettings: { nonce, root } } = window
const instance = axios.create({
    baseURL: `${root}idxbroker/v1/admin`,
    withCredentials: true,
    headers: {
        'X-WP-Nonce': nonce
    }
})

export default instance
