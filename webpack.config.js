const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");
// const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const {fsReadFile} = require("ts-loader/dist/utils");
// const ImageminWebpWebpackPlugin = require("imagemin-webp-webpack-plugin");

const pubStaticPath = path.resolve(__dirname, 'pub/static');
const versionFile = path.resolve(pubStaticPath + '/deployed_version.txt');


let version = fsReadFile(versionFile);
if (version === undefined) {
    throw new Error('Version not defined');
}
version = version.replace(/\n/gm, "");
const buildPath = path.resolve(pubStaticPath + '/v' + version + '/');
console.log(buildPath);
module.exports = (env, argv) => {

    const IS_DEV = argv.mode === 'development';//development | production
    return {

        entry: {
            // TODO: separate files automatically by entry typescript files so we don't have to add entries here manually
            'main': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/js/main.ts',
            'hero': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/js/hero.ts',
            'demo': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/js/demo.ts',
            'contact': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/js/contact.ts',

            'components/form': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/js/components/form.ts',
            'components/site': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/js/components/site.ts',
            'components/faq': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/js/components/faq.ts',

            'styles': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/css/main.scss',
            'critical': './vendor/echron/liquid/app/design/frontend/Liquid/Default/web/css/critical.scss',
        },
        output: {
            path: buildPath,
            filename: 'js/[name].js',
            // filename: '[name].js',
            // chunkFilename: '[name]-[chunkhash].js',
            pathinfo: false,
            clean: true
        },
        module: {
            rules: [
                //Styles
                {
                    test: /\.s[ac]ss$/i,
                    use: [
                        {
                            loader: MiniCssExtractPlugin.loader,
                        },
                        // 'style-loader',
                        {
                            loader: 'css-loader',
                            options: {
                                sourceMap: IS_DEV,
                                esModule: false,
                                // minimize: !IS_DEV
                                // exportType: "css-style-sheet",
                            }
                        },
                        {
                            loader: 'sass-loader',
                            options: {
                                sourceMap: IS_DEV,
                                sassOptions: {
                                    outputStyle: 'compressed',
                                },
                                webpackImporter: false,
                                // exportType: "css-style-sheet",
                            }
                        }
                    ]
                },
                //Typescript
                {
                    test: /\.tsx?$/,
                    loader: 'ts-loader',
                    exclude: path.resolve(__dirname, '/node_modules')
                },
                //Fonts and images

                {
                    test: /\.(jpe?g|svg|png|gif|ico|eot|ttf|woff2?)(\?v=\d+\.\d+\.\d+)?$/i,
                    type: 'asset/resource',
                }


            ]
        },
        plugins: [
            new RemoveEmptyScriptsPlugin(),
            new MiniCssExtractPlugin({
                filename: 'css/[name].css',
                chunkFilename: '[id].css',
                runtime: false,
                ignoreOrder: false,
            }),
            // new CopyWebpackPlugin({
            //         patterns: [
            //             {
            //                 context: './app/frontend',
            //                 from: '**/*.{png,jpg,svg,pdf,ico,eot,ttf,woff2}'
            //             },
            //         ],
            //
            //     }
            // ),
            new CleanWebpackPlugin(),
            // new ImageminWebpWebpackPlugin()
        ],
        resolve: {
            alias: {
                './components/form': path.resolve(__dirname, 'app/frontend/js/components/form.ts'),
                './components/site': path.resolve(__dirname, 'app/frontend/js/components/site.ts'),
                './site': path.resolve(__dirname, 'app/frontend/js/components/site.ts'),
                './components/faq': path.resolve(__dirname, 'app/frontend/js/components/faq.ts'),
                jquery: "jquery/dist/jquery.js",
                '@sentry': false,
            },
            fallback: {
                util: false,
                fs: false
            },
            // extensions: ['.ts', '.js'],
        },
        mode: "production",
        optimization: {
            splitChunks: {
                chunks: 'all',
                name: 'vendor',
                minChunks: 1,
                hidePathInfo: true
            },
            usedExports: true,
            emitOnErrors: true, // NoEmitOnErrorsPlugin
            concatenateModules: true, //ModuleConcatenationPlugin
            minimize: true,
            minimizer: [
                new TerserPlugin({
                    // cache: false,
                    parallel: true,
                    // sourceMap: IS_DEV,
                    // minify: TerserPlugin.uglifyJsMinify,
                    terserOptions: {
                        ecma: 6,
                        mangle: true,
                        module: false,
                        sourceMap: IS_DEV,
                        // output: {
                        //     // comments: false,
                        //     beautify: false,
                        // },
                        format: {
                            comments: false,
                        },
                    },
                    extractComments: false,
                    // test: /\.js(\?.*)?$/i,
                }),
                new CssMinimizerPlugin({
                    minify: CssMinimizerPlugin.cssnanoMinify,
                })
            ],
        }
    };

};
