const idxConfig = require('./idx.config')
module.exports = {
    plugins: {
        autoprefixer: {},
        'postcss-prefixer': {
            prefix: `${idxConfig.options.prefix}${idxConfig.options.separator}`,
            ignore: [
            	'router-link'
            ]
        }
    }
}
