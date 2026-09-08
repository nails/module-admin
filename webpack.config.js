const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const {VueLoaderPlugin} = require('vue-loader');
const path = require('path');
const webpack = require('webpack');

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
    resolve: {
        alias: {
            vue: path.resolve(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js')
        }
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
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '../css/[name].min.css'
        }),
        new VueLoaderPlugin(),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: JSON.stringify(true),
            __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
            __VUE_PROD_HYDRATE_MISMATCH_DETAILS__: JSON.stringify(false)
        })
    ],
    devServer: {
        port: 9000,
    }
};
