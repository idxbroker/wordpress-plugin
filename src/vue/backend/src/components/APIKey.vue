<template>
    <idx-form-group
        :customClass="{
            'needs-validation': true,
            'was-validated': error || success
        }"
        novalidate
    >
        <idx-form-label for="APIKey">API Key</idx-form-label>
        <idx-form-input
            type="text"
            id="APIKey"
            :placeholder="placeholder"
            :customClass="{
                'is-invalid': error,
                'is-valid': success,
                'is-loading': loading
            }"
            :invalid="error"
            :valid="success"
            :value="apiKey"
            @change="generalSettingsStateChange({ key: 'apiKey', value: $event.target.value })"
            required
        />
        <idx-block className="spinner-border" role="status" v-if="loading">
            <idx-block tag="span" className="visually-hidden">Loading...</idx-block>
        </idx-block>
        <idx-block className="invalid-feedback" v-if="error">
            We couldn't find an account with the provided API key
        </idx-block>
    </idx-form-group>
</template>

<script>
import { mapState, mapActions } from 'vuex'
export default {
    name: 'APIKey',
    props: {
        placeholder: {
            type: String,
            default: 'Enter Your API Key'
        },
        error: {
            type: Boolean,
            default: false
        },
        loading: {
            type: Boolean,
            default: false
        },
        success: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        ...mapState({
            apiKey: state => state.general.apiKey
        })
    },
    methods: {
        ...mapActions({
            generalSettingsStateChange: 'general/generalSettingsStateChange'
        })
    }
}
</script>

<style scoped lang="scss">
    @import '~bootstrap/scss/forms';

    .form-group {
        --space-1: 4px;
        --space-6: 24px;
        margin-bottom: var(--space-6);
        position: relative;

        input[type=text] {
            border: 1px solid $gray-250;
            border-radius: var(--space-1);
            color: $gray-875;
            line-height: 1.5;
            padding: 0.625rem 1.25rem;
        }
    }

    .invalid-feedback {
        font-size: 1em;
    }

    @-webkit-keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }

    .spinner-border {
        animation: 0.75s linear infinite spinner-border;
        border: 2px solid currentColor;
        border-right-color: $primary;
        border-radius: 50%;
        bottom: 15px;
        color: #acd9ee;
        display: inline-block;
        height: 1rem;
        position: absolute;
        right: 1rem;
        vertical-align: text-bottom;
        width: 1rem;
    }

    .visually-hidden,
    .visually-hidden-focusable:not(:focus) {
        border: 0 !important;
        clip: rect(0, 0, 0, 0) !important;
        height: 1px !important;
        margin: -1px !important;
        overflow: hidden !important;
        padding: 0 !important;
        position: absolute !important;
        white-space: nowrap !important;
        width: 1px !important;
    }
</style>
