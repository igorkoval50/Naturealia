import template from './np-media-list-selection-item-v2.html.twig';
import './np-media-list-selection-item-v2.scss';

/**
 * @private
 * @description Component which renders an image.
 * @status ready
 */
Shopware.Component.register('np-media-list-selection-item-v2', {
    template,

    props: {
        item: {
            required: true
        },

        hideActions: {
            type: Boolean,
            required: false,
            default: false
        },

        hideTooltip: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    computed: {
        isPlaceholder() {
            return !!this.item.isPlaceholder;
        },

        productImageClasses() {
            return {
                'is--placeholder': this.isPlaceholder
            };
        },

        sourceId() {
            return this.item.mediaId || this.item.targetId || this.item.id;
        }
    }
});
