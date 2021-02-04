const glob = require('glob');
const path = require('path');
const autoprefixer = require('autoprefixer');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const StatsPlugin = require('webpack-stats-plugin').StatsWriterPlugin;
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const createWriteWpAssetManifest = require('./webpack/wpAssets');

module.exports = (env, argv) => {
  const { mode } = argv;

  return {
    devtool: mode === 'production'
      ? 'source-map'
      : 'cheap-module-eval-source-map',
    entry: {
      cloneReplace: './client/index.jsx',
    },
    module: {
      rules: [
        {
          exclude: /node_modules/,
          test: /.jsx?$/,
          use: [
            'babel-loader',
            'eslint-loader',
          ],
        },
        {
          test: /\.(sa|sc|c)ss$/,
          loaders: [
            'style-loader',
            'css-loader',
            {
              loader: 'postcss-loader',
              options: {
                plugins: [autoprefixer()],
              },
            },
            'resolve-url-loader',
            'sass-loader',
          ],
        },
      ],
    },
    output: {
      filename: mode === 'production'
        ? '[name].bundle.min.js'
        : '[name].js',
      path: path.join(__dirname, 'build'),
    },
    plugins: [
      new DependencyExtractionWebpackPlugin(),
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
    ],
    resolve: {
      extensions: ['.js', '.jsx'],
    },
  };
};
