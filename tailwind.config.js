const preline = require('preline/plugin');

module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    'node_modules/preline/**/*.js',
  ],
  theme: {
    extend: {},
  },
  plugins: [
    preline,
  ],
}
