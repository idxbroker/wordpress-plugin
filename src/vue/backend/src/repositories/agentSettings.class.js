import GenericRepositoryClass from './generic.class'

class AgentSettingsRepositoryClass extends GenericRepositoryClass {
    constructor () {
        super()
        this.endpoint = 'settings/agents'
    }
}

export default AgentSettingsRepositoryClass
