import GenericRepositoryClass from './generic.class'

class SocialProRepositoryClass extends GenericRepositoryClass {
    constructor () {
        super()
        this.endpoint = 'settings/social-pro'
    }
}

export default SocialProRepositoryClass
