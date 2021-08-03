import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';
import './page/pixup-wishlist-overview';
Shopware.Module.register('pixup-wishlist', {
    type: 'plugin',
    name: 'Custom',
    title: 'pixup-wishlist.title',
    description: 'pixup-wishlist.description',
    color: '#ff4821',
    icon: 'default-shopping-plastic-bag',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        overview: {
            component: 'pixup-wishlist-overview',
            path: 'overview'
        },
    },
    navigation: [{
        label: 'pixup-wishlist.page.overview',
        color: '#ff4821',
        path: 'pixup-wishlist.overview',
        icon: 'default-shopping-plastic-bag'
    }]
});
