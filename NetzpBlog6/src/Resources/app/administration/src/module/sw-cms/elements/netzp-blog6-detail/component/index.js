import { Component, Mixin } from 'src/core/shopware';
import template from './sw-cms-el-netzp-blog6-detail.html.twig';
import './sw-cms-el-netzp-blog6-detail.scss';

Component.register('sw-cms-el-netzp-blog6-detail', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    computed: {
        imageStyle() {
            var s = 'height: ' + this.element.config.height.value + 'rem;';
            if(this.element.config.imageMode.value == 'full') {
                s += 'width: 100% !important; object-fit: cover; object-position: center center;';
            }

            return s;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('netzp-blog6-detail');
        }
    }
});
