const path = require ('path')
const CleanWebpackPlugin = require('clean-webpack-plugin')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const ExtractTextPlugin = require('extract-text-webpack-plugin')
const UglifyJsPlugin = require('uglifyjs-webpack-plugin')
const MinifyPlugin = require("babel-minify-webpack-plugin")

let pathsToClean = [
  './www/include/core/menu/templates/*.(js|tpl)$/',
]

module.exports = {
  entry: {
    index: './www/index.js',
    another: './www/include/configuration/configWizard/js/index.js'
  },
  output: {
    filename: '[name].[chunkhash].js',
    publicPath: './include/core/menu/templates/',
    path: path.resolve(__dirname, './www/include/core/menu/templates')
  },
  plugins: [
    new CleanWebpackPlugin(pathsToClean),
    new HtmlWebpackPlugin({
      filename: 'webpack.tpl',
      title: 'Caching'
    }),
    new ExtractTextPlugin({
      filename: '[name].[contenthash].css',
    }),
    new UglifyJsPlugin({
      test: /\.js($|\?)/i
    }),
  ],
  module: {
    rules: [
      {
        test: /\.less$/,
        use: ExtractTextPlugin.extract({
          use: ['css-loader', 'less-loader']
        }),
      },
      {
        test: /\.js$/,
        exclude: /(node_modules)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              "env",
              "react",
              "stage-2"
            ],
          },
        }
      },
      {
        test: /\.(css|scss)$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: '[name].css',
              outputPath: '../../../../Themes/assets/css/'
            }
          },
          {
            loader: 'style-loader/url'
          },
          {
            loader: 'css-loader'
          }
        ]
      },
      {
        test: /\.jpe?g$|\.gif$|\.png$|\.ttf$|\.eot$|\.svg$/,
        use: 'file-loader?name=[name].[ext]?[hash]'
      },
      {
        test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/,
        loader: 'url-loader?limit=10000&mimetype=application/fontwoff'
      },
    ]
  },
};
