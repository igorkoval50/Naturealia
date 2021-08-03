import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'netzp-blog6-structure',
    label: 'sw-cms.elements.netzp-blog6-structure.label',
    component: 'sw-cms-el-netzp-blog6-structure',
    configComponent: 'sw-cms-el-config-netzp-blog6-structure',
    previewComponent: 'sw-cms-el-preview-netzp-blog6-structure',

    defaultConfig: {
        type: {
            source: 'static',
            value: 'categories'
        }
    }
});
