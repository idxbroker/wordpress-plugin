import instance from './instance'

class GenericRepositoryClass {
    constructor (endpoint) {
        this.endpoint = endpoint
        this.instance = instance
    }

    get () {
        return this.instance.get(this.endpoint)
    }

    post (payload) {
        return this.instance.post(this.endpoint, payload)
    }
}

export default GenericRepositoryClass
