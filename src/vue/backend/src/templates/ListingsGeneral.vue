<template>
    <idx-block tag="fieldset" className="fieldset listings-general">
        <idx-form-group>
            <idx-block className="control-label"><strong>Default State</strong><br> Description of the Default State setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ac purus eu ex lacinia placerat.</idx-block>
            <idx-form-label for="default-state">Choose Default State</idx-form-label>
            <idx-form-input
                type="text"
                id="default-state"
                :value="defaultState"
                @change="listingsSettingsStateChange({ key: defaultState, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Default Currency</strong><br> Description of the Default Currency setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ac purus eu ex lacinia placerat.</idx-block>
            <idx-form-label>Currency Symbol</idx-form-label>
            <idx-custom-select
                ariaLabel="Currency Symbol"
                :selected="currencySymbolSelected"
                :options="currency.currencySymbols"
                @selected-item="listingsSettingsStateChange({ key: 'currencySymbolSelected', value: $event })"
            />
            <idx-form-label>Currency Code</idx-form-label>
            <idx-custom-select
                ariaLabel="Currency Code"
                :selected="currencyCodeSelected"
                :options="currency.currencyCodes"
                @selected-item="listingsSettingsStateChange({ key: 'currencyCodeSelected', value: $event })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Default Number of Posts</strong><br> Description of the Default Number of Posts setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
            <idx-form-label for="default-posts">Number of Posts on Listing Archive Page</idx-form-label>
            <idx-form-input
                type="text"
                id="default-posts"
                :value="numberOfPosts"
                @change="listingsSettingsStateChange({ key: numberOfPosts, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Default Disclaimer</strong><br> Description of the Default Disclaimer setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
            <idx-form-label for="default-disclaimer">Default Disclaimer</idx-form-label>
            <idx-textarea
                type="text"
                id="default-disclaimer"
                rows="3"
                @change="listingsSettingsStateChange({ key: defaultDisclaimer, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-block className="control-label"><strong>Listings Slug</strong><br> Description of the Listings Slug setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
            <idx-form-label for="listings-slug">Listings Slug</idx-form-label>
            <idx-form-input
                type="text"
                id="listings-slug"
                :value="listingSlug"
                @change="listingsSettingsStateChange({ key: listingSlug, value: $event.target.value })"
            />
        </idx-form-group>
    </idx-block>
</template>
<script>
import { mapState, mapActions } from 'vuex'
import currency from '@/data/currency'
export default {
    name: 'ListingsGeneral',
    computed: {
        ...mapState({
            currencyCodeSelected: state => state.listingsSettings.currencyCodeSelected,
            currencySymbolSelected: state => state.listingsSettings.currencySymbolSelected,
            defaultDisclaimer: state => state.listingsSettings.defaultDisclaimer,
            numberOfPosts: state => state.listingsSettings.numberOfPosts,
            listingSlug: state => state.listingsSettings.listingSlug,
            defaultState: state => state.listingsSettings.defaultState
        })
    },
    data () {
        return {
            currency
        }
    },
    methods: {
        ...mapActions({
            listingsSettingsStateChange: 'listingsSettings/listingsSettingsStateChange'
        })
    }
}
</script>
<style lang="scss">
@import '~bootstrap/scss/forms';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
.fieldset {
    --font-size-p: 16px;
    --line-height-p: 28px;
    font-size: var(--font-size-p);
    line-height: var(--line-height-p);
}
</style>
