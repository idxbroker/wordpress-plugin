<template>
    <idx-block className="social-pro-form">
        <b>Social Pro</b>
        <div>Detailed sentence or two describing Social Pro General Interest Articles. Lorem ipsum dolor sit amet.</div>
        <idx-block className="social-pro-form__syndication">
            {{ toggleLabel }}
            <idx-toggle-slider
                uncheckedState="No"
                checkedState="Yes"
                @toggle="socialProStateChange({ key: 'enableSyndication', value: !enableSyndication })"
                :active="enableSyndication"
                :label="toggleLabel"
            ></idx-toggle-slider>
        </idx-block>
        <idx-block className="social-pro-form__settings">
            <b>General Interest Article Settings</b>
            <div>General Interest Articles come from Elevate writers and contain rich content related to homes and home life.</div>
            <idx-block className="social-pro-form__field">
                <label>Autopublish General Interest Articles</label>
                <idx-custom-select
                    uniqueID="autopublish"
                    ariaLabel="Select Autopublish setting"
                    :selected="autopublishArticles"
                    :options="autopublishOptions"
                    @selected-item="socialProStateChange({ key: 'autopublishArticles', value: $event })"
                ></idx-custom-select>
            </idx-block>
            <idx-block className="social-pro-form__field">
                <label>General Interest Article Post Day of the Week</label>
                <idx-custom-select
                    uniqueID="post-day"
                    ariaLabel="Select post day"
                    :selected="postDay"
                    :options="postDayOptions"
                    @selected-item="socialProStateChange({ key: 'postDay', value: $event })"
                ></idx-custom-select>
            </idx-block>
            <idx-block className="social-pro-form__field">
                <label>General Interest Article Post Type</label>
                <idx-custom-select
                    uniqueID="post-type"
                    ariaLabel="Select post type"
                    :selected="postType"
                    :options="postTypeOptions"
                    @selected-item="socialProStateChange({ key: 'postType', value: $event })"
                ></idx-custom-select>
            </idx-block>
        </idx-block>
    </idx-block>
</template>
<script>
import { mapState, mapActions } from 'vuex'
export default {
    name: 'social-pro-form',
    data () {
        return {
            toggleLabel: 'Enable General Interest Article Syndication',
            enable: false,
            autopublishOptions: [
                { name: 'Autopublish' },
                { name: 'Draft' }
            ],
            postDayOptions: [
                { name: 'Sunday' },
                { name: 'Monday' },
                { name: 'Tuesday' },
                { name: 'Wednesday' },
                { name: 'Thursday' },
                { name: 'Friday' },
                { name: 'Saturday' }
            ],
            postTypeOptions: [
                { name: 'Post' }
            ]
        }
    },
    computed: {
        ...mapState({
            enableSyndication: state => state.socialPro.enableSyndication,
            autopublishArticles: state => state.socialPro.autopublishArticles,
            postDay: state => state.socialPro.postDay,
            postType: state => state.socialPro.postType
        })
    },
    methods: {
        ...mapActions({
            socialProStateChange: 'socialPro/socialProStateChange'
        })
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/toggleSlider.scss';
@import '~@idxbrokerllc/idxstrap/dist/styles/components/customSelect.scss';
.social-pro-form {
    font-size: 1rem;
    color: $gray-875;
    &__syndication {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        background-color: $gray-150;
        @media (max-width: 630px) {
            flex-direction: column;
            align-items: end;
            grid-gap: 10px;
        }
    }
    &__settings {
        margin-top: 25px;
    }
    &__field {
        margin-top: 25px;
        label {
            width: 100%;
        }
    }
}
</style>
