<template>
    <idx-block className="form-content form-content__api-key">
        <idx-form-group
            :customClass="{
                'needs-validation': true,
                'was-validated': error || success
            }"
            novalidate
        >
            <idx-form-label customClass="form-content__label" for="ApiKey">API Key</idx-form-label>
            <idx-form-input
                type="text"
                id="ApiKey"
                :placeholder="placeholder"
                :customClass="{
                    'is-invalid': error,
                    'is-valid': success,
                    'is-loading': loading
                }"
                :invalid="error"
                :valid="success"
                :value="apiKey"
                @change="$emit('form-field-update', { key: 'apiKey', value: $event.target.value })"
                required
            />
            <idx-block className="spinner-border" role="status" v-if="loading">
                <idx-block tag="span" className="visually-hidden">Loading...</idx-block>
            </idx-block>
            <idx-block className="invalid-feedback" v-if="error">We couldn't find an account with the provided API key</idx-block>
        </idx-form-group>
    </idx-block>
</template>

<script>

export default {
    name: 'ApiKey',
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
        },
        apiKey: {
            type: String,
            default: ''
        }
    }
}
</script>
