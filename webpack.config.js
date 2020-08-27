const webpack = require('webpack');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const StatsPlugin = require('webpack-stats-plugin').StatsWriterPlugin;
const createWriteWpAssetManifest = require('./webpack/wpAssets');
const getDevServer = require('./config/devServer');
const getEntries = require('./config/getEntries');
const paths = require('./webpack/paths');

// Set variables from .env file.
require('dotenv').config();

module.exports = (env, argv) => {
  const { mode } = argv;
  const { PROXY_URL = 'https://0.0.0.0:8080' } = process.env;

  // The base entries used in production mode.
  const entries = {
    block: './src'
  };

  return {
    devtool: mode === 'production' ? 'source-map'
      : 'cheap-module-eval-source-map',
    entry: getEntries(mode, entries),
    devServer: getDevServer(mode),
    module: {
      rules: [
        {
          exclude: /node_modules/,
          test: /.jsx?$/,
          use: [
            'react-hot-loader/webpack',
            'babel-loader',
            'eslint-loader',
          ],
        },
      ],
    },
    output: {
      filename: '[name].bundle.min.js',
      path: paths.build,
      publicPath: (mode === 'development') ? `${PROXY_URL}/build/`
        : paths.build,
    },
    plugins: [
      new StatsPlugin({
        transform: createWriteWpAssetManifest,
        fields: ['assetsByChunkName', 'hash'],
        filename: 'assetMap.json',
      }),
      ...(mode === 'production'
        ? [
          new CleanWebpackPlugin(),
        ] : []
      ),
      ...(mode === 'development'
        ? [
          new webpack.HotModuleReplacementPlugin({
            multiStep: true,
          }),
        ] : []
      )
    ],
    resolve: {
      extensions: ['.js', '.jsx'],
      alias: {
        'react-dom': '@hot-loader/react-dom',
      },
    },
    externals: {
      react: 'React',
      'react-dom': 'ReactDOM',
    },
  };
};
