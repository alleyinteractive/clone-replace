const path = require('path');

module.exports = (env, argv) => {
  const { mode } = argv;

  return {
    devtool: 'development' === mode
      ? 'cheap-module-eval-source-map'
      : 'source-map',
    entry: {
      cloneReplace: './client/index.js',
    },
    module: {
      rules: [
        {
          exclude: /node_modules/,
          test: /.js$/,
          use: [
            'babel-loader',
            'eslint-loader',
          ],
        },
        {
          test: /\.scss$/,
          use: [{
            loader: 'style-loader',
          },
          {
            loader: 'css-loader',
          },
          {
            loader: 'resolve-url-loader',
          },
          {
            loader: 'sass-loader',
            options: {
              sassOptions: {
                sourceMap: true,
                sourceMapContents: false,
              },
            },
          },
          ],
        },
      ],
    },
    output: {
      filename: '[name].js',
      path: path.join(__dirname, 'build'),
    },
  };
};
