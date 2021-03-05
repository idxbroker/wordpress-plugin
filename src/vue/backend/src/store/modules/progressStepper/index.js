import actions from './actions'
const state = {
    guidedSetupSteps: [
        { name: '1. Connect', icon: 'link', total: 4, active: 0 },
        { name: '2. Your Listings', icon: 'list', total: 5, active: 0 },
        { name: '3. Agents', icon: 'users', total: 3, active: 0 },
        { name: '4. Social', icon: 'thumbs-up', total: 1, active: 0 }
    ]
}

export default {
    namespaced: true,
    state,
    actions
}
