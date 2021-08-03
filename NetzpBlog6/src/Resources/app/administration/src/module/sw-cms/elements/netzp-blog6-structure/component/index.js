import { Component, Mixin } from 'src/core/shopware';
import template from './sw-cms-el-netzp-blog6-structure.html.twig';
import './sw-cms-el-netzp-blog6-structure.scss';

Component.register('sw-cms-el-netzp-blog6-structure', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('netzp-blog6-structure');
        }
    }
});
