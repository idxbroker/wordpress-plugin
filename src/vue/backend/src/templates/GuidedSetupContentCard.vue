<template>
    <idx-block className="gs-contentCard">
        <idx-block className="gs-contentCard__stepper">
            <h1>progress stepper</h1>
        </idx-block>
        <idx-block className="gs-contentCard__content">
            <h1>content</h1>
        </idx-block>
        <idx-block className="gs-contentCard__sidebar">
            <h1>sidebar</h1>
        </idx-block>
        <idx-block className="gs-contentCard__footer">
            <h1>footer</h1>
        </idx-block>
    </idx-block>
</template>

<script>
export default {
    name: 'GuidedSetupContentCard'
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
.gs-contentCard {
    margin: 40px 0 0;
}
</style>
