<template>
    <idx-block className="social-pro__upgrade">
        <p>
            Cupcake ipsum dolor sit. Amet chupa chups croissant dessert
            tootsie roll sugar plum sweet roll gummi bears pie. Jelly
            donut sugar plum brownie chocolate bar tootsie roll bear claw.
        </p>
        <idx-block
            tag="a"
            className="wp-btn btn-primary btn-lg"
            href="#"
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
    text-align: center;
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
