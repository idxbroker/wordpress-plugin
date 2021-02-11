<template>
    <idx-form-group customClass="needs-validation {'was-validated': success|error}" novalidate>
        <idx-form-label for="APIKey" customClass="visually-hidden">API Key</idx-form-label>
        <idx-form-input
            type="text"
            id="APIKey"
            :placeholder="placeholder"
            customClass="{
                'is-invalid': error,
                'is-valid': success,
                'is-loading': loading
            }"
            :invalid="error"
            :valid="success"
            required
        />
        <idx-block className="invalid-feedback" v-if="error">
            We couldn't find an account with the provided API key
        </idx-block>
        <idx-block className="idx-wp-spinner-border" role="status" v-if="loading">
            <idx-block tag="span" className="visually-hidden">Loading...</idx-block>
        </idx-block>
    </idx-form-group>
</template>

<script>
export default {
    name: 'APIKey',
    props: {
        placeholder: {
            type: String,
            default: 'Enter Your API Key'
        },
        error: {
            type: Boolean,
            default: true
        },
        loading: {
            type: Boolean,
            default: false
        },
        success: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style scoped lang="scss">
    // TODO: move global and base to main.js
    @import '~@idxbrokerllc/idxstrap/dist/styles/globalVariables.scss';
    @import '~@idxbrokerllc/idxstrap/dist/styles/base.scss';

    .form-group {
        position: relative;
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
        color: #acd9ee;
        display: inline-block;
        height: 1rem;
        position: absolute;
        right: 1rem;
        top: 15px;
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
