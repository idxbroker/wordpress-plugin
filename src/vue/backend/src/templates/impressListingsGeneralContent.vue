<template>
    <idx-block
        tag="fieldset"
        :className="{
            'form-content': true,
            'form-content--disabled': formDisabled
        }">
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Default State</idx-block>
            <p>
                You can enter a default state that will automatically be output on template pages
                and widgets that show the state. When you create a listing and leave the state field
                empty, the default below will be shown. You can override the default on each listing
                by entering a value into the state field.
            </p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}default-state`">Choose Default State</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}default-state`"
                placeholder="Enter your default state"
                :value="defaultState"
                @change="$emit('form-field-update', { key: 'defaultState', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Default Currency</idx-block>
            <p>Select a default currency symbol and optional currency code to display on listings.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">Currency Symbol</idx-form-label>
            <idx-custom-select
                ariaLabel="Currency Symbol"
                placeholder="None"
                :disabled="formDisabled"
                :selected="currencySymbolSelected"
                :options="currency.currencySymbols"
                @selected-item="$emit('form-field-update', { key: 'currencySymbolSelected', value: $event.value })"
            />
        </idx-form-group>
        <idx-form-group>
            <idx-form-label customClass="form-content__label">Currency Code</idx-form-label>
            <idx-custom-select
                ariaLabel="Currency Code"
                placeholder="None"
                :disabled="formDisabled"
                :selected="currencyCodeSelected"
                :options="currency.currencyCodes"
                @selected-item="$emit('form-field-update', { key: 'currencyCodeSelected', value: $event.value })"
            />
        </idx-form-group>
        <idx-form-group customClass="form-content__toggle">
            Display Currency Code on Listings
            <idx-toggle-slider
                label="Display Currency Code on Listings"
                uncheckedState="No"
                checkedState="Yes"
                :active="displayCurrencyCode"
                :disabled="formDisabled"
                @toggle="$emit('form-field-update', { key: 'displayCurrencyCode', value: !displayCurrencyCode })"
            ></idx-toggle-slider>
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Default Number of Posts</idx-block>
            <p>
                The default number of posts displayed on a listing archive page is 9. Here you can set a custom number. Enter -1 to display all listing posts.
                <i>If you have more than 20-30 posts, it's not recommended to show all or your page will load slow.</i>
            </p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}default-posts`">Number of Posts on Listing Archive Page</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}default-posts`"
                :value="numberOfPosts"
                @change="$emit('form-field-update', { key: 'numberOfPosts', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Default Disclaimer</idx-block>
            <p>Optionally enter a disclaimer to show on single listings. This can be overridden on individual listings.</p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}default-disclaimer`">Default Disclaimer</idx-form-label>
            <idx-textarea
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}default-disclaimer`"
                placeholder="Disclaimer text"
                rows="3"
                :value="defaultDisclaimer"
                @change="$emit('form-field-update', { key: 'defaultDisclaimer', value: $event.target.value })"
            />
        </idx-form-group>
        <idx-block className="form-content__header">
            <idx-block tag="h2" className="form-content__title">Listings Slug</idx-block>
            <p>
                Optionally change the slug of the listing post type. Don't forget to
                <a href="../wp-admin/options-permalink.php" target="_blank">
                    reset your permalinks
                </a>
                if you change the slug!
            </p>
        </idx-block>
        <idx-form-group>
            <idx-form-label customClass="form-content__label" :target="`${$idxStrap.prefix}listings-slug`">Listings Slug</idx-form-label>
            <idx-form-input
                type="text"
                :disabled="formDisabled"
                :id="`${$idxStrap.prefix}listings-slug`"
                :value="listingSlug"
                @change="$emit('form-field-update', { key: 'listingSlug', value: $event.target.value })"
            />
        </idx-form-group>
    </idx-block>
</template>
<script>
import currency from '@/data/currency'
export default {
    name: 'ListingsGeneral',
    inheritAttrs: false,
    props: {
        currencyCodeSelected: {
            type: String,
            default: 'none'
        },
        currencySymbolSelected: {
            type: String,
            default: 'none'
        },
        displayCurrencyCode: {
            type: Boolean,
            default: false
        },
        defaultDisclaimer: {
            type: String,
            default: ''
        },
        numberOfPosts: {
            type: [Number, String],
            default: '9'
        },
        listingSlug: {
            type: String,
            default: 'listings'
        },
        defaultState: {
            type: String,
            default: ''
        },
        formDisabled: {
            type: Boolean,
            default: false
        }
    },
    created () {
        this.currency = currency
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
</style>
