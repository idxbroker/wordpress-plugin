<template>
    <TwoColumn title="Social Pro Syndication Settings">
        <idx-block className="form-content">
            <div>
                <b>Social Pro</b>
                <div>Detailed sentence or two describing Social Pro General Interest Articles. Lorem ipsum dolor sit amet.</div>
                <idx-block className="form-content__toggle">
                    {{ toggleLabel }}
                    <idx-toggle-slider
                        uncheckedState="No"
                        checkedState="Yes"
                        @toggle="setItem({ key: 'enabled', value: !enabled })"
                        :active="enabled"
                        :disabled="formDisabled"
                        :label="toggleLabel"
                    ></idx-toggle-slider>
                </idx-block>
            </div>
            <div v-show="enabled">
                <SocialProForm
                    :formDisabled="formDisabled"
                    v-bind="localStateValues"
                    @form-field-update="formUpdate"
                />
                <idx-button
                    size="lg"
                    @click="saveAction"
                >
                    Save
                </idx-button>
            </div>
        </idx-block>
        <template #related>
            <RelatedLinks :relatedLinks="relatedLinks"/>
        </template>
    </TwoColumn>
</template>
<script>
import { mapActions, mapState } from 'vuex'
import SocialProForm from '@/templates/socialProForm'
import RelatedLinks from '@/components/RelatedLinks.vue'
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
export default {
    mixins: [pageGuard],
    components: {
        SocialProForm,
        TwoColumn,
        RelatedLinks
    },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            enabled: state => state.socialPro.enabled,
            autopublish: state => state.socialPro.autopublish,
            postDay: state => state.socialPro.postDay,
            postType: state => state.socialPro.postType
        })
    },
    methods: {
        ...mapActions({
            setItem: 'socialPro/setItem'
        })
    },
    created () {
        this.module = 'socialPro'
        this.toggleLabel = 'Enable General Interest Article Syndication'
        this.relatedLinks = [
            {
                text: 'Social Pro with IDX Broker',
                href: '#'
            },
            {
                text: 'IDX Broker Middleware',
                href: 'https://middleware.idxbroker.com/mgmt/'
            },
            {
                text: 'Sign up for IDX Broker',
                href: '#signUp'
            }
        ]
    }
}
</script>
