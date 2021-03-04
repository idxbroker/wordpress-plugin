import instance from './instance'

class GenericRepositoryClass {
    constructor (endpoint) {
        this.endpoint = endpoint
        this.instance = instance
    }

    get (path = '') {
        return this.instance.get(`${this.endpoint}/${path}`)
    }

    post (payload, path = '') {
        return this.instance.post(`${this.endpoint}/${path}`, payload)
    }
}

export default GenericRepositoryClass
