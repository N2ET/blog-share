const path = require('path');
const fs = require('fs');
const getPublicUrlOrPath = require('react-dev-utils/getPublicUrlOrPath');

const appDirectory = fs.realpathSync(process.cwd());
const resolveApp = relativePath => path.resolve(appDirectory, relativePath);

const isDevMode = process.env.NODE_ENV === 'development';

const moduleFileExtensions = [
    'web.mjs',
    'mjs',
    'web.js',
    'js',
    'web.ts',
    'ts',
    'web.tsx',
    'tsx',
    'json',
    'web.jsx',
    'jsx',
];

const resolveModule = (resolveFn, filePath) => {
    const extension = moduleFileExtensions.find(extension =>
        fs.existsSync(resolveFn(`${filePath}.${extension}`))
    );

    if (extension) {
        return resolveFn(`${filePath}.${extension}`);
    }

    return resolveFn(`${filePath}.js`);
};

// Make sure any symlinks in the project folder are resolved:
// https://github.com/facebook/create-react-app/issues/637
// const appDirectory = fs.realpathSync(process.cwd());
// const resolveApp = relativePath => path.resolve(appDirectory, relativePath);

// We use `PUBLIC_URL` environment variable or "homepage" field to infer
// "public path" at which the app is served.
// webpack needs to know it to put the right <script> hrefs into HTML even in
// single-page apps that may serve index.html for nested URLs like /todos/42.
// We can't use a relative path in HTML because we don't want to load something
// like /todos/42/static/js/bundle.7289d.js. We have to know the root.
const publicUrlOrPath = getPublicUrlOrPath(
    isDevMode,
    require(resolveApp('package.json')).homepage,
    process.env.PUBLIC_URL
);


const mainConfig = {
    paths: {
        appBuild: resolveApp('build/main'),
        appPublic: resolveApp('public/main'),
        appHtml: resolveApp('public/main/index.html'),
        appIndexJs: resolveModule(resolveApp, 'src/index'),

        publicUrlOrPath
    },
    output: {

    }
};

const framePaths = {
    appBuild: resolveApp('build/loader'),
    appPublic: resolveApp('public/loader'),
    appHtml: resolveApp('public/loader/index.html'),

    // filePath写成'src/frame/index.tsx' 会提示找不到文件 src/frame/index.tsx.js
    appIndexJs: resolveModule(resolveApp, 'src/frame/index'),

    publicUrlOrPath
};

const frameConfig = {
    paths: framePaths,

    output: {
        // path: framePaths.appBuild,
        filename: 'static/js/BlogShare.js',
        chunkFilename: undefined,
        library: 'BlogShare',
        libraryTarget: 'umd'
    },

    splitChunks: false
};

const CONFIG = {
    main: mainConfig,
    loader: frameConfig
}[process.env.target || 'main'];

module.exports = {
    // publicUrlOrPath,
    appDirectory,
    moduleFileExtensions,
    resolveApp,
    resolveModule,

    config: CONFIG,

    isDevMode,
    target: process.env.target
};