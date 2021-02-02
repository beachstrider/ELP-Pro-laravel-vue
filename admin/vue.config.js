module.exports = {
    lintOnSave: false,
    pwa: {
        iconPaths: {
            favicon32: './favicon.png',
            favicon16: './favicon.png',
            appleTouchIcon: './favicon.png',
            maskIcon: './favicon.png',
            msTileImage: './favicon.png',
        },
    },
    css: {
        loaderOptions: {
            less: {
                javascriptEnabled: true,
            },
        },
    }
}
