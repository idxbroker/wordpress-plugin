// Require path.
const path = require('path')

// Configuration object.
const config = {
  mode: 'production',
  // Create the entry points.
  // One for frontend and one for the admin area.
  entry: {
    // frontend and admin will replace the [name] portion of the output config below.
    'idx-wrapper-tags-block': './idx-wrapper-tags/script.js',
    'idx-widgets-block': './idx-widgets/script.js',
    'impress-carousel-block': './impress-carousel/script.js',
    'impress-city-links-block': './impress-city-links/script.js',
    'impress-lead-login-block': './impress-lead-login/script.js',
    'impress-lead-signup-block': './impress-lead-signup/script.js',
    'impress-omnibar-block': './impress-omnibar/script.js',
    'impress-showcase-block': './impress-showcase/script.js',
  },

  // Create the output files.
  // One for each of our entry points.
  output: {
    // [name] allows for the entry object keys to be used as file names.
    filename: '[name].min.js',
    // Specify the path to the JS files.
    path: path.join(__dirname, '/../../assets/js')
  },

  // Setup a loader to transpile down the latest and great JavaScript so older browsers
  // can understand it.
  module: {
    rules: [
      {
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['babel-preset-env']
          }
        }
      }
    ]
  }
}

// Export the config object.
module.exports = config
