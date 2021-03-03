<template>
    <idx-block tag="fieldset" className="listings-general form-content">
        <idx-block className="form-content__header">
            <idx-block tag="h3" className="form-content__title">Default State</idx-block>
            Description of the Default State setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ac purus eu ex lacinia placerat.
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="default-state">Choose Default State</idx-form-label>
            <idx-form-input
                type="text"
                id="default-state"
                placeholder="Enter your default state"
                :value="defaultState"
                @change="setItem({ key: 'defaultState', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header"><idx-block tag="h3" className="form-content__title">Default Currency</idx-block> Description of the Default Currency setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce ac purus eu ex lacinia placerat.</idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">Currency Symbol</idx-form-label>
            <idx-custom-select
                ariaLabel="Currency Symbol"
                placeholder="None"
                :selected="currencySymbolSelected"
                :options="currency.currencySymbols"
                @selected-item="setItem({ key: 'currencySymbolSelected', value: $event.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">Currency Code</idx-form-label>
            <idx-custom-select
                ariaLabel="Currency Code"
                placeholder="None"
                :selected="currencyCodeSelected"
                :options="currency.currencyCodes"
                @selected-item="setItem({ key: 'currencyCodeSelected', value: $event.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header"><idx-block tag="h3" className="form-content__title">Default Number of Posts</idx-block> Description of the Default Number of Posts setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="default-posts">Number of Posts on Listing Archive Page</idx-form-label>
            <idx-form-input
                type="text"
                id="default-posts"
                :value="numberOfPosts"
                @change="setItem({ key: numberOfPosts, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header"><idx-block tag="h3" className="form-content__title">Default Disclaimer</idx-block> Description of the Default Disclaimer setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="default-disclaimer">Default Disclaimer</idx-form-label>
            <idx-textarea
                type="text"
                id="default-disclaimer"
                customClass="listings-general__default-disclaimer"
                placeholder="Disclaimer text"
                rows="3"
                @change="setItem({ key: defaultDisclaimer, value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header"><idx-block tag="h3" className="form-content__title">Listings Slug</idx-block> Description of the Listings Slug setting in IMPress Listings. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" for="listings-slug">Listings Slug</idx-form-label>
            <idx-form-input
                type="text"
                id="listings-slug"
                :value="listingSlug"
                @change="setItem({ key: listingSlug, value: $event.target.value })"
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
            setItem: 'listingsSettings/setItem'
        })
    }
}
</script>
<style lang="scss">
@import '../styles/formContentStyles.scss';
@import '~bootstrap/scss/forms';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
.listings-general__default-disclaimer {
    border-radius: 4px;
}
</style>
