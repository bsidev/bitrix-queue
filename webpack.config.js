/* eslint-disable */
const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('install/js/bsi.queue/')
    .setManifestKeyPrefix('app')
    .setPublicPath('/bitrix/js/bsi.queue')
    .addEntry('app', './assets/index.js')
    .splitEntryChunks()
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(config => {
        config.plugins.push([
            'component', {
                libraryName: 'element-ui',
                styleLibraryName: 'theme-chalk'
            }]);
    }, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .enableVueLoader(() => {}, { runtimeCompilerBuild: false })
    .enableSassLoader()
    .enablePostCssLoader()
    .addLoader({
        enforce: 'pre',
        test: /\.(js|vue)$/,
        loader: 'eslint-loader',
        exclude: /node_modules/,
        options: {
            emitError: true,
            emitWarning: true
        }
    })
    .addExternals({
        bitrix: 'BX'
    });

module.exports = Encore.getWebpackConfig();
