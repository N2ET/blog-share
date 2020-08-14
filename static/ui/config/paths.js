'use strict';

const commonConfig = require('./common');

const {
  moduleFileExtensions,
  resolveModule,
  resolveApp,
} = commonConfig;


// config after eject: we're in ./config/
module.exports = {
  dotenv: resolveApp('.env'),
  appPath: resolveApp('.'),

  appPackageJson: resolveApp('package.json'),
  appSrc: resolveApp('src'),
  appTsConfig: resolveApp('tsconfig.json'),
  appJsConfig: resolveApp('jsconfig.json'),
  yarnLockFile: resolveApp('yarn.lock'),
  testsSetup: resolveModule(resolveApp, 'src/setupTests'),
  proxySetup: resolveApp('src/setupProxy.js'),
  appNodeModules: resolveApp('node_modules'),

  ...commonConfig.config.paths
};



module.exports.moduleFileExtensions = moduleFileExtensions;
