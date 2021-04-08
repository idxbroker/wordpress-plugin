<template>
    <idx-block className="social-pro__upgrade">
        <p>
            A Social Pro subscription is required to access this feature.
            Once you’ve enabled Social Pro from your IDX Broker account,
            you can push automated blog posts and listing updates to your
            WordPress pages and social media sites.
        </p>
        <idx-block
            tag="a"
            className="wp-btn btn-primary btn-lg"
            href="https://middleware.idxbroker.com/mgmt/upgrade#socialPro"
            target="_blank"
            role="button"
        >
            Check it out
        </idx-block>
    </idx-block>
</template>
<script>
export default {
    name: 'social-pro-upgrade'
}
</script>
<style lang="scss">
.social-pro__upgrade {
    a {
        height: 50px;
        line-height: 1.5;
        text-transform: uppercase;
        letter-spacing: 0.1rem;
        font-weight: 200;
        &:hover {
            text-decoration: none;
        }
    }
}
</style>
