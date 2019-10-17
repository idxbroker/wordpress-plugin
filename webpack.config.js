// Require path.
const path = require('path');

// Configuration object.
const config = {
  // Create the entry points.
  // One for frontend and one for the admin area.
  entry: {
    // frontend and admin will replace the [name] portion of the output config below.
    'idx-wrapper-tags-block': './src/blocks/idx-wrapper-tags/script.js',
    'idx-widgets-block': './src/blocks/idx-widgets/script.js',
    'impress-carousel-block': './src/blocks/impress-carousel/script.js',
    'impress-city-links-block': './src/blocks/impress-city-links/script.js',
    'impress-lead-login-block': './src/blocks/impress-lead-login/script.js',
    'impress-lead-signup-block': './src/blocks/impress-lead-signup/script.js',
    'impress-omnibar-block': './src/blocks/impress-omnibar/script.js',
		'impress-showcase-block': './src/blocks/impress-showcase/script.js',
	  'test-block': './src/blocks/test-block/script.js'
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
					presets: ['@babel/preset-env'],
					plugins: [
						["babel-plugin-transform-react-jsx"]
				]
        }
      }
    ]
  }
}

// Export the config object.
module.exports = config;