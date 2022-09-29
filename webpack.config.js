const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const {VueLoaderPlugin} = require('vue-loader');
const path = require('path');

const mode = process.env.NODE_ENV || 'development';

module.exports = {
    mode: mode,
    entry: {
        'admin': './assets/js/admin.js',
        'admin.forms': './assets/js/admin.forms.js',
        'admin.logs.site': './assets/js/admin.logs.site.js',
        'admin.plugins': './assets/js/admin.plugins.js',
        'admin.ui': './assets/js/admin.ui.js',
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'assets/js/'),
        publicPath: '/assets/js/'
    },
    module: {
        rules: [
            {
                test: /\.(css|scss|sass)$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            url: false
                        }
                    },
                    'postcss-loader',
                    'sass-loader'
                ]
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.svg$/,
                use: ['babel-loader', 'vue-svg-loader'],
            },
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '../css/[name].min.css'
        }),
        new VueLoaderPlugin()
    ],
    devServer: {
        port: 9000,
    }
};
