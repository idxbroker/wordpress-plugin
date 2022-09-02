const path = require('path')
const devPort = 8123
const utilityPath = `@idxbrokerllc/idxstrap/dist/Utilities/`
module.exports = {
    transpileDependencies: [
        '@idxbrokerllc/idxstrap'
    ],
    devServer: {
        hot: true,
        writeToDisk: true,
        liveReload: false,
        sockPort: devPort,
        port: devPort,
        progress: false,
        headers: { 'Access-Control-Allow-Origin': '*' }
    },
    outputDir:  process.env.NODE_ENV === 'production' ? path.resolve(__dirname, '../../../assets/vue/backend') : path.resolve(__dirname, '../../../assets/vue-dev/backend'),
    publicPath: process.env.NODE_ENV === 'production' ? '../wp-content/plugins/idx-broker-platinum/assets/vue/backend' : `http://localhost:${devPort}/`,
    configureWebpack: {
        output: {
            filename: 'admin.js',
            hotUpdateChunkFilename: 'hot/hot-update.js',
            hotUpdateMainFilename: 'hot/hot-update.json'
        },
        optimization: {
            splitChunks: false
        },
        resolve: {
            alias: {
                '@utilityPath': utilityPath
            }
        }
    },
    filenameHashing: true,
    css: {
        loaderOptions: {
            sass: {
                prependData: '@import "@/styles/globalVariables.scss";'
            }
        },
        extract: {
            filename: 'admin.css'
        }
    }
}
