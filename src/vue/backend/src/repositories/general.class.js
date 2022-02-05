import GenericRepositoryClass from './generic.class'

class GeneralRepositoryClass extends GenericRepositoryClass {
    constructor () {
        super()
        this.endpoint = 'settings/general'
    }
}

export default GeneralRepositoryClass
