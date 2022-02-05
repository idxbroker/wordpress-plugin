import GenericRepositoryClass from './generic.class'

class ImportContentRepositoryClass extends GenericRepositoryClass {
    constructor () {
        super()
        this.endpoint = 'import'
    }
}

export default ImportContentRepositoryClass
