import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'product-four-column',
    label: 'sw-cms.blocks.textImage.imageTextGallerys.labels',
    category: 'commerce',
    component: 'sw-cms-block-product-four-column',
    previewComponent: 'sw-cms-preview-product-four-column',
    defaultConfig: {
        marginBottom: '0px',
        marginTop: '0px',
        marginLeft: '0px',
        marginRight: '0px',
        sizingMode: 'boxed'
    },
    slots: {
        left: 'product-box',
        center: 'product-box',
        right: 'product-box',
        new: 'product-box'
    }
});
