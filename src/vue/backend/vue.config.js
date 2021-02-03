const path = require('path')
const devPort = 8123

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
    outputDir: path.resolve(__dirname, '../../../assets/vue/backend'),
    publicPath: process.env.NODE_ENV === 'production' ? path.resolve(__dirname, '../../../assets/vue/backend') || '/' : `http://localhost:${devPort}/`,
    configureWebpack: {
        output: {
            filename: 'admin.js',
            hotUpdateChunkFilename: 'hot/hot-update.js',
            hotUpdateMainFilename: 'hot/hot-update.json'
        },
        optimization: {
            splitChunks: false
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
