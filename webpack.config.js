// Require path.
const path = require('path');

// Configuration object.
const config = {
  // Create the entry points.
  // One for frontend and one for the admin area.
  entry: {
    // frontend and admin will replace the [name] portion of the output config below.
    idxWrapperTagsBlock: './src/blocks/idx-wrapper-tags/script.js',
    idxWidgetsBlock: './src/blocks/idx-widgets/script.js',
    impressCarouselBlock: './src/blocks/impress-carousel/script.js',
    impressCityLinksBlock: './src/blocks/impress-city-links/script.js',
    impressLeadLoginBlock: './src/blocks/impress-lead-login/script.js',
    impressLeadSignupBlock: './src/blocks/impress-lead-signup/script.js',
    impressOmnibarBlock: './src/blocks/impress-omnibar/script.js',
    impressShowcaseBlock: './src/blocks/impress-showcase/script.js'
  },

  // Create the output files.
  // One for each of our entry points.
  output: {
    // [name] allows for the entry object keys to be used as file names.
    filename: 'js/[name].min.js',
    // Specify the path to the JS files.
    path: path.resolve(__dirname, 'assets')
  },

  // Setup a loader to transpile down the latest and great JavaScript so older browsers
  // can understand it.
  module: {
    rules: [
      {
        // Look for any .js files.
        test: /\.js$/,
        // Exclude the node_modules folder.
        exclude: /node_modules/,
        // Use babel loader to transpile the JS files.
        loader: 'babel-loader',
        options: {
          babelrc: false,
          presets: ['@babel/preset-env']
        }
      }
    ]
  }
}

// Export the config object.
module.exports = config;