<template>
    <checkbox-label
        :option="option"
        @checked="$emit('listing-selected', [$event.data, property.listingID])"
    >
        <template v-slot:content>
            <!-- Prop bindings will need to be reevaluated once we have the property shape -->
            <idx-single-property-card
                customClass="import-listing__card"
                :listingPrice="property.listingPrice"
                :bedrooms="property.bedrooms"
                :totalBaths="property.totalBaths"
                :sqFt="property.sqFt"
                :address="property.address"
                :cityName="property.cityName"
                :courtesy="property.courtesy"
                :image="property.image"
                :primaryPhoto="property.image"
            >
            </idx-single-property-card>
        </template>
    </checkbox-label>
</template>
<script>
export default {
    name: 'listings-card',
    props: {
        property: {
            type: Object,
            default: () => {}
        }
    },
    data () {
        return {
            option: {
                value: 'selected',
                label: 'selected'
            }
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
.property-card__details {
    text-shadow: none;
}
.checkbox-label {
    position: relative;
    width: 309px;
    &__custom {
        position: absolute;
        top: 0px;
        right: 16px;
        z-index: 1;
        border-radius: 2px;
    }
}
</style>
