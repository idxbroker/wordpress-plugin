import * as types from '../../common/mutationTypes'

const progressStepperUpdate = ({ commit, state }, payload) => {
    // Make a deep copy of the state to maintain name, total, and icon
    // Replace the active step with the new active step
    const updatedSteps = state.guidedSetupSteps.map((step, key) => {
        return {
            name: step.name,
            icon: step.icon,
            total: step.total,
            active: payload[key],
            hideProgress: step.hideProgress
        }
    })
    commit(types.SET_ITEM, {
        key: 'guidedSetupSteps',
        value: updatedSteps
    })
}
export default {
    progressStepperUpdate
}
