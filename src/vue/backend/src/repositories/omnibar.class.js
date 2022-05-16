import GenericRepositoryClass from './generic.class'

class omnibarRepositoryClass extends GenericRepositoryClass {
    constructor () {
        super()
        this.endpoint = 'settings/omnibar'
    }
}

export default omnibarRepositoryClass
