// Require path.
const path = require('path');

module.exports = {
  mode: 'production',
  // Create the entry points.
  // One for frontend and one for the admin area.
  entry: {
    'google-my-business-settings': './apps/GoogleMyBusinessSettings.js'
  },

  // Create the output files.
  // One for each of our entry points.
  output: {
    // [name] allows for the entry object keys to be used as file names.
    filename: '[name].min.js',
    // Specify the path to the JS files.
    path: path.join(__dirname, '/../../assets/js')
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader"
        }
      },
    ]
  }
};
