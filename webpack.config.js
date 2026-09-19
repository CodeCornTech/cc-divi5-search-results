const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  entry: {
    bundle: './src/index.ts',
  },
  externals: {
    react: ['vendor', 'React'],
    'react-dom': ['vendor', 'ReactDOM'],
    '@wordpress/hooks': ['vendor', 'wp', 'hooks'],
    '@divi/module': ['divi', 'module'],
    '@divi/module-library': ['divi', 'moduleLibrary'],
  },
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        use: {
          loader: 'ts-loader',
          options: {
            transpileOnly: false,
          },
        },
        exclude: /node_modules/,
      },
      {
        test: /\.s?css$/i,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({ filename: '../styles/vb-[name].css' }),
    new CopyWebpackPlugin({
      patterns: [
        {
          from: '**/module.json',
          context: 'src/components',
          to: path.resolve(__dirname, 'modules-json'),
        },
      ],
    }),
  ],
  resolve: {
    extensions: ['.js', '.jsx', '.tsx', '.ts', '.json'],
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'scripts'),
  },
};
