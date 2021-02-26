<template>
    <TwoColumn title="Social Pro Syndication Settings">
        <SocialProForm />
        <template #related>
            <div>Related Links Here.</div>
        </template>
    </TwoColumn>
</template>
<script>
import SocialProForm from '@/templates/socialProForm'
import TwoColumn from '@/templates/layout/TwoColumn'
export default {
    components: {
        SocialProForm,
        TwoColumn
    }
}
</script>
