<template>
    <checkbox-label
        :option="option"
        customClass="agent-card"
        @checked="$emit('agent-selected', [$event.data, agent.id])"
    >
        <template v-slot:content>
            <idx-block :className="{
                    'agent-card__image-wrap': true,
                    'agent-card__image--placeholder': !primaryImage
                }">
                <img v-if="primaryImage" :src="agent.image">
                <div v-else>
                    {{ agentInitials }}
                </div>
            </idx-block>
            <idx-block className="agent-card__content">
                <idx-block className="agent-card__name">{{ agent.name }}</idx-block>
                <idx-block className="agent-card__title">{{ agent.title }}</idx-block>
                <idx-block className="agent-card__email">{{ agent.email }}</idx-block>
                <idx-block className="agent-card__id">#{{ agent.id }}</idx-block>
            </idx-block>
            <idx-block v-if="agent.imported" className="agent-card__import-flag">
                <idx-block className="agent-card__imported">
                    Imported <img :src="check">
                </idx-block>
                <idx-block @click.native.stop="$emit('removeAgent', agent.id)" className="agent-card__delete">
                    <img :src="deleteIcon">
                </idx-block>
            </idx-block>
        </template>
    </checkbox-label>
</template>
<script>
import deleteIcon from '@/assets/trash-light.svg'
import check from '@/assets/check-light-white.svg'
export default {
    name: 'agent-card',
    data () {
        return {
            option: {
                value: 'selected',
                label: 'selected'
            },
            deleteIcon,
            check
        }
    },
    props: {
        agent: {
            type: Object,
            default: () => {}
        }
    },
    computed: {
        primaryImage () {
            return this.agent.image !== (null || '')
        },
        agentInitials () {
            const matches = this.agent.name.match(/\b(\w)/g)
            return matches.join('')
        }
    }
}
</script>
<style lang="scss">
@import '~@idxbrokerllc/idxstrap/dist/styles/components/checkBoxLabel.scss';
.agent-card {
    display: grid;
    grid-template-areas: "image content check";
    grid-gap: 15px;
    position: relative;
    padding: 15px;
    width: 420px;
    height: 157px;
    color: $gray-800;
    border: 1px solid $gray-250;
    border-radius: 4px;
    background-color: $white;
    @media (max-width: 767px) {
        height: 100%;
        width: 100%;
        min-width: 200px;
    }
    @media (max-width: 630px) {
        grid-template-areas:
            "image check"
            "content content"
            "imported imported";
    }
    &__image-wrap {
        grid-area: image;
        position: relative;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        width: 125px;
        height: 125px;
        z-index: 1;

        img {
            position: absolute;
            left: 50%;
            top: 50%;
            max-width: 100%;
            width: 100%;
            height: auto;
            min-height: 100%;
            z-index: 0;
            transform: translate3d(-50%, -50%, 0);
            border: 0;
        }
    }
    &__image--placeholder {
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 30px;
        letter-spacing: 1.6px;
        background-color: $gray-800;
        color: $white;
    }
    &__content {
        grid-area: content;
        align-self: flex-start;
    }
    &__name {
        font-size: 21px;
    }
    &__title {
        letter-spacing: 1.3px;
        color: #788088; // We will need to add this to the VCL, as it is pill gray
        text-transform: uppercase;
    }
    &__import-flag {
        display: flex;
        position: absolute;
        right: 6px;
        bottom: 15px;
        @media (max-width: 630px) {
            bottom: 0;
            position: unset;
            grid-area: imported;
        }
    }
    &__imported {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 25px;
        padding: 5px 10px;
        margin-right: 2px;
        background-color: $gray-875;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 1.1px;
        color: $white;
        img {
            width: 10px;
            margin-left: 8px;
        }
    }
    &__delete {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 25px;
        width: 30px;
        background-color: $gray-875;
        cursor: pointer;
        img {
            height: 11px;
            widows: 10px;
        }
        &:active {
            background-color: $black;
        }
    }
    .checkbox-label__custom {
        grid-area: check;
        align-self: flex-start;
        justify-self: end;
    }
}
</style>
