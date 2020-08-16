const { createProxyMiddleware } = require('http-proxy-middleware');

const commonConfig = require('../config/common');
const proxyInfo = {
    target: 'http://localhost:3001'
};

if (!commonConfig.isDevMode || commonConfig.target !== 'loader') {

    module.exports = function () {};

} else {

    module.exports = function(app) {

        app.use('/main/', createProxyMiddleware({
            target: proxyInfo.target,
            changeOrigin: true,
            pathRewrite (urlPath, req) {

                if (req.headers.host.match(/3001$/)) {
                    return urlPath;
                }

                return urlPath.replace('/main/', '/');
            }
        }));

        app.use('/static/', createProxyMiddleware((urlPath, req) => {

            let enable =  (req.headers.referer || '').match(/\/main\/index.html/);

            return enable;
        }, {
            target: proxyInfo.target,
            changeOrigin: true,
            logLevel: 'debug',
            pathRewrite (urlPath, req) {
                let ret = urlPath;

                return ret;
            },
        }))
    };

}
