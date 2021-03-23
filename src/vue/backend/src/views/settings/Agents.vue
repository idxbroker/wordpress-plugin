<template>
    <TwoColumn title="IMPress Agents Settings">
        <idx-block className="form-content">
            <idx-block className="form-content__toggle">
                Enable
                <idx-toggle-slider
                    uncheckedState="No"
                    checkedState="Yes"
                    :active="localStateValues.enabled"
                    :disabled="formDisabled"
                    label="Enable IMPress Listings"
                    @toggle="enablePlugin"
                ></idx-toggle-slider>
            </idx-block>
            <template v-if="enabled">
                <AgentsSettings
                    :formDisabled="formDisabled"
                    v-bind="localStateValues"
                    @form-field-update="formUpdate"
                />
                <idx-button size="lg" @click="save">Save</idx-button>
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
import standaloneSettingsActions from '@/mixins/standaloneSettingsActions'
import RelatedLinks from '@/components/RelatedLinks.vue'
const { agentSettings: { repo } } = PRODUCT_REFS
export default {
    inject: [repo],
    mixins: [pageGuard, standaloneSettingsActions],
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
            enabled: state => state.agentSettings.enabled
        })
    },
    methods: {
        enablePlugin () {
            this.enablePluginAction(this[repo])
        },
        save () {
            this.saveHandler(this[repo])
        }
    },
    created () {
        this.module = 'agentSettings'
        this.links = [
            { text: 'IMPress Agents Features', href: '#' },
            { text: 'IDX Broker Middleware', href: 'https://middleware.idxbroker.com/mgmt/' },
            { text: 'Sign up for IDX Broker', href: 'https://signup.idxbroker.com/' } // Marketing may want a different entry
        ]
        if (this.enabled) {
            this.loadData(this[repo])
        }
    }
}
</script>
