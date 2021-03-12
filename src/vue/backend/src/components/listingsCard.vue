<template>
    <checkbox-label
        customClass="import-listings"
        :option="option"
        @checked="$emit('listing-selected', [$event.data, listing])"
    >
        <template v-slot:content>
            <!-- Prop bindings will need to be reevaluated once we have the property shape -->
            <idx-single-property-card
                customClass="import-listing__card"
                v-bind="listing"
            >
            <template v-slot:propertyCardImage>
                    <img class="property-card__image" :src="listing.image">
                </template>
            </idx-single-property-card>
        </template>
    </checkbox-label>
</template>
<script>
export default {
    name: 'listings-card',
    props: {
        listing: {
            type: Object,
            default: () => {}
        }
    },
    created () {
        this.option = {
            value: 'selected',
            label: 'selected'
        }
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/checkBoxLabel.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/_singlePropertyCard.scss';
.import-listing__card.property-card {
    height: 193px;
    width: 309px;
}
.property-card {
    &__details {
        text-shadow: none;
    }
    &__listing-price.property-card__single-item {
        color: $white;
    }
}
.import-listings.checkbox-label {
    position: relative;
    width: 309px;
    .checkbox-label__custom {
        position: absolute;
        top: 0px;
        right: 16px;
        z-index: 1;
        border-radius: 2px;
    }
}
</style>
