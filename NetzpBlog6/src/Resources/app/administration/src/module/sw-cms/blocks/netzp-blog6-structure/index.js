import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'netzp-blog6-structure-block',
    label: 'sw-cms.blocks.netzp-blog6-structure.label',
    category: 'netzp-blog6',
    component: 'sw-cms-block-netzp-blog6-structure',
    previewComponent: 'sw-cms-preview-netzp-blog6-structure',

    defaultConfig: {
        marginBottom: '20px',
        marginTop:    '20px',
        marginLeft:   '20px',
        marginRight:  '20px',
        sizingMode:   'boxed'
    },

    slots: {
        content: {
            type: 'netzp-blog6-structure'
        }
    }
});
