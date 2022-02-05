const changedModules = (state) => {
    const changes = []
    for (const mod in state) {
        if (mod !== 'guidedSetupSteps' && mod !== 'hasChanges') {
            if (Object.keys(state[mod].changes).length) {
                changes.push(state[mod])
            }
        }
    }
    return changes
}
export default {
    changedModules
}
