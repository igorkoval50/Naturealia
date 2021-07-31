import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'image-slider-text',
    label: 'sw-cms.blocks.delightBlock.label',
    component: 'sw-cms-el-image-slider-text',
    configComponent: 'sw-cms-el-config-image-slider-text',
    previewComponent: 'sw-cms-el-preview-image-slider-text',
    defaultConfig: {
        sliderItems: {
            source: 'static',
            value: [],
            required: true,
            entity: {
                name: 'media'
            }
        },
        navigationArrows: {
            source: 'static',
            value: 'outside'
        },
        navigationDots: {
            source: 'static',
            value: null
        },
        position: {
            source: 'static',
            value: 'left_top'
        }
    },
    enrich: function enrich(elem, data) {
        if (Object.keys(data).length < 1) {
            return;
        }

        Object.keys(elem.config).forEach((configKey) => {
            const entity = elem.config[configKey].entity;

            if (!entity) {
                return;
            }

            const entityKey = entity.name;
            if (!data[`entity-${entityKey}`]) {
                return;
            }

            elem.data[configKey] = [];
            elem.config[configKey].value.forEach((sliderItem) => {
                elem.data[configKey].push({
                    url: sliderItem.url,
                    media: data[`entity-${entityKey}`].get(sliderItem.mediaId)
                });
            });
        });
    }
});
