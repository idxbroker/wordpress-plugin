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
import pageGuard from '@/mixins/pageGuard'
export default {
    mixins: [pageGuard],
    components: {
        SocialProForm,
        TwoColumn
    },
    created () {
        this.module = 'socialPro'
    }
}
</script>
