const path = require("path");
const fs = require('fs');
const https = fs.existsSync('./../../../../127.0.0.1+1-key.pem')
    ? {
        key: fs.readFileSync('./../../../../127.0.0.1+1-key.pem'),
        cert: fs.readFileSync('./../../../../127.0.0.1+1.pem'),
    }
    : {};
module.exports = {
    publicPath: '/inc/admin/',
    devServer: {
        host: '0.0.0.0',
        port: 8081,
        public: 'localhost:8081',
        disableHostCheck: true,
        https
    },
    outputDir: path.resolve(__dirname, './../../public/inc/admin'),
    configureWebpack: {
        output: {
            path: path.resolve(__dirname, './../../public/inc/admin'),
            filename: '[name].js',
            chunkFilename: '[name].js'
        }
    },
    chainWebpack: config => {
        config
            .plugin('provide')
            .use(require('webpack').ProvidePlugin, [{
                Main: path.resolve(path.join(__dirname, 'Main.js')),
            }])
        if(config.plugins.has('extract-css')) {
            const extractCSSPlugin = config.plugin('extract-css')
            extractCSSPlugin && extractCSSPlugin.tap(() => [{
                filename: '[name].css',
                chunkFilename: '[name].css'
            }])
        }
    }
}
