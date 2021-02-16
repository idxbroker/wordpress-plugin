<template>
    <idx-block className="app"></idx-block>
</template>

<style lang="scss">
    .app {
        // Spacing
        --space-4: 16px;
        --space-6: 24px;
        --space-8: 32px;
        --space-9: 36px;
        --space-10: 40px;
        --space-12: 48px;
        --space-15: 60px;
        --space-18: 72px;
        // Typography
        --font-size-h1: 31px;
        --font-size-h2: 25px;
        --font-size-p: 16px;
        --font-size-p-large: 18px;
        --line-height-h1: 28px;
        --line-height-h2: 28px;
        --line-height-p: 28px;
        --line-height-p-large: 28px;
        font-size: var(--font-size-p);
        height: 100%;
        line-height: var(--line-height-p);
        margin-left: -20px;
        min-height: 100vh;
        position: relative;
        width: calc(100% + 20px);

        h1,h2,h3,h4,h5,h6 {
            // reset styles
            color: inherit;
            display: block;
            float: none;
        }

        h1 {
            font-size: var(--font-size-h1);
            line-height: var(--line-height-h1);
        }

        h2 {
            font-size: var(--font-size-h2);
            line-height: var(--line-height-h2);
        }

        p {
            font-size: inherit;
            line-height: inherit;
        }
    }
</style>
