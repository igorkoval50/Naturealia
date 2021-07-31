import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'slider-image-text',
    label: 'sw-cms.blocks.delightBlock.label',
    category: 'text-image',
    component: 'sw-cms-block-slider-image-text',
    previewComponent: 'sw-cms-preview-slider-image-text',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed'
    },
    slots: {
        imageSliderTxt: 'image-slider-text'
    }
});

