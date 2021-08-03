import './component';
import './preview';

Shopware.Service('cmsService').registerCmsBlock({
    name: 'image-text-row-two',
    label: 'sw-cms.blocks.imageText.imageTextInside.label',
    category: 'text-image',
    component: 'sw-cms-block-image-text-row-two',
    previewComponent: 'sw-cms-preview-image-text-row-two',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '0px',
        marginRight: '0px',
        sizingMode: 'boxed'
    },
    slots: {
        'left-image': {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'cover' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_camera_large.jpg'
                    }
                }
            }
        },
        'center-image': {
            type: 'image',
            default: {
                config: {
                    displayMode: { source: 'static', value: 'cover' }
                },
                data: {
                    media: {
                        url: '/administration/static/img/cms/preview_plant_large.jpg'
                    }
                }
            }
        },
        'center-text': {
            type: 'text',
            default: {
                config: {
                    content: {
                        source: 'static',
                        value: `
                        <p>Headline Information</p>
                        <a href="#" class="btn btn-primary">Show more</a>
                        `.trim()
                    }
                }
            }
        }
    }
});
