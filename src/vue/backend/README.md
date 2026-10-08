# backend

## Required build runtime

This project builds with Node.js 12.22.12 and its bundled npm 6.14.16. Newer Node
versions fail to compile the `node-sass` dependency.

```
nvm install 12.22.12
nvm use 12.22.12
```

## Project setup
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Run your unit tests
```
npm run test:unit
```

### Run your end-to-end tests
```
npm run test:e2e
```

### Lints and fixes files
```
npm run lint
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).
