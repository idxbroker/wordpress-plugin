<template>
    <TwoColumn title="IMPress Agents Settings">
        <idx-block className="form-content">
            <idx-block className="form-content__toggle">
                Enable
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    @toggle="refreshPage"
                    :active="localStateValues.enabled"
                    :disabled="formDisabled"
                    label="Enable IMPress Listings"
                ></idx-toggle-slider>
            </idx-block>
            <template v-if="enabled">
<<<<<<< HEAD
                <AgentsSettings
                    :formDisabled="formDisabled"
                    v-bind="localStateValues"
                    @form-field-update="formUpdate"
                />
                <idx-button theme="primary" @click="saveHandler">Save</idx-button>
=======
                <AgentsSettings v-bind="localStateValues" @form-field-update="formUpdate" />
                <idx-button size="lg" @click="saveHandler">Save</idx-button>
>>>>>>> release/3.0.0
            </template>
        </idx-block>
        <template #related>
            <RelatedLinks :relatedLinks="links" />
        </template>
    </TwoColumn>
</template>
<script>
import { mapState } from 'vuex'
import { PRODUCT_REFS } from '@/data/productTerms'
import AgentsSettings from '@/templates/AgentsSettings'
import TwoColumn from '@/templates/layout/TwoColumn'
import pageGuard from '@/mixins/pageGuard'
import RelatedLinks from '@/components/RelatedLinks.vue'
export default {
    inject: [PRODUCT_REFS.agentSettings.repo],
    mixins: [pageGuard],
    components: {
        AgentsSettings,
        RelatedLinks,
        TwoColumn
    },
    data () {
        return {
            formDisabled: false
        }
    },
    computed: {
        ...mapState({
            enabled: (state) => state.agentSettings.enabled
        })
    },
    methods: {
        async refreshPage () {
            this.formUpdate({ key: 'enabled', value: !this.enabled })
            const { status } = await this.agentSettingsRepository.post({ enabled: !this.enabled }, 'enable')
            if (status === (204 || 200)) {
                location.reload()
            }
        },
        async saveHandler () {
            this.formDisabled = true
            const { status } = await this.agentSettingsRepository.post(this.formChanges)
            this.formDisabled = false
            if (status === 200) {
                this.saveAction()
            }
        }
    },
    async created () {
        this.module = 'agentSettings'
        this.links = [
            { text: 'IMPress Agents Features', href: '#' },
            { text: 'IDX Broker Middleware', href: 'https://middleware.idxbroker.com/mgmt/' },
            { text: 'Sign up for IDX Broker', href: 'https://signup.idxbroker.com/' } // Marketing may want a different entry
        ]
        const { data } = await this.agentSettingsRepository.get()
        this.updateState(data)
    }
}
</script>
