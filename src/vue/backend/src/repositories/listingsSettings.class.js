import GenericRepositoryClass from './generic.class'

class ListingsSettingsRepositoryClass extends GenericRepositoryClass {
    constructor () {
        super()
        this.endpoint = 'settings/listings'
    }
}

export default ListingsSettingsRepositoryClass
