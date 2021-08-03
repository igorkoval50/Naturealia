const { Application } = Shopware;

import './np-media-list-selection-item-v2';
import './np-media-list-selection-v2';

import './page/netzp-blog6-list';
import './page/netzp-blog6-create';
import './page/netzp-blog6-detail';

import deDE from './snippet/de-DE';
import enGB from './snippet/en-GB';

import searchBarTemplate from './sw-search-bar-item.html.twig';
Shopware.Component.override('sw-search-bar-item', {
    template: searchBarTemplate
});

Shopware.Module.register('netzp-blog6', {
    type: 'plugin',
    name: 'Blog',
    title: 'netzp-blog6.main.menuLabel',
    description: 'netzp-blog6.main.menuDescription',
    color: '#ccb2fb',
    icon: 'default-documentation-paper-pencil',
    entity: 's_plugin_netzp_blog',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        list: {
            component: 'netzp-blog6-list',
            path: 'list'
        },
        detail: {
            component: 'netzp-blog6-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'netzp.blog6.list'
            }
        },
        create: {
            component: 'netzp-blog6-create',
            path: 'create',
            meta: {
                parentPath: 'netzp.blog6.list'
            }
        }
    },

    navigation: [{
        label: 'netzp-blog6.main.menuLabel',
        color: '#82b1ff',
        path: 'netzp.blog6.list',
        icon: 'default-symbol-content',
        parent: 'sw-content',
        position: 100
    }]
});

const swVersion = Shopware.Context.app.config.version.split('.');
let searchTypeOptions = {
    entityName: 's_plugin_netzp_blog',
    entity: 's_plugin_netzp_blog',
    placeholderSnippet: 'netzp-blog6.main.placeholderSearchBar',
    listingRoute: 'netzp.blog6.list'
};
if(swVersion[0] == '6' && swVersion[1] == '3') {
    searchTypeOptions['entityService'] = 'sPluginNetzpBlogService';
}

Application.addServiceProviderDecorator('searchTypeService', searchTypeService => {
    searchTypeService.upsertType('s_plugin_netzp_blog', searchTypeOptions);
    return searchTypeService;
});




