<template>
  <idx-block className="app">
    <idx-block className="nav">
      <router-link to="/">Home</router-link> |
      <router-link to="/warnings">Warnings</router-link>
    </idx-block>
    <router-view/>
  </idx-block>
</template>

<style lang="scss">
  @import url('https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,400;0,500;0,600;1,700&display=swap');
  @import '~@idxbrokerllc/idxstrap/dist/styles/globalVariables.scss';
  @import '~@idxbrokerllc/idxstrap/dist/styles/base.scss';
  @import '~@idxbrokerllc/idxstrap/dist/styles/components/buttons';
  :root {
    --font-family: 'Mulish', "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    --font-size-h3: 21px;
    --font-size-p-large: 18px;
    --letter-spacing-h3: 2.1px;
    --line-height-h3: 22px;
    --line-height-p-large: 28px;
    --space-1: 4px;
    --space-2: 8px;
    --space-3: 12px;
    --space-4: 16px;
    --space-5: 20px;
    --space-6: 24px;
    --space-7: 28px;
    --space-8: 32px;
    --space-9: 36px;
    --space-10: 40px;
  }
  div.app {
    font-family: var(--font-family);
  }
</style>
