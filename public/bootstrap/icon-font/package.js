Package.describe({
    name: 'uninite:app-icon-font',
    summary: 'Font icon generate from http://fontello.com',
    version: '0.0.1',
    git: '',
    documentation: 'README.md'
});

Package.onUse(function (api) {
    api.versionsFrom('1.0');

    api.addFiles([
        'client/font/fontello.eot',
        'client/font/fontello.svg',
        'client/font/fontello.ttf',
        'client/font/fontello.woff',
        'client/font/fontello.woff2'
    ], 'client', {isAsset: true});

    api.addFiles([
        'client/css/fontello.css',
        'client/css/fontello-ie7.css'
    ], 'client');


});
