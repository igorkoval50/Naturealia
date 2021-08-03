import { Component, Mixin } from 'src/core/shopware';
const { Criteria, EntityCollection } = Shopware.Data;

import template from './sw-cms-el-config-netzp-blog6-structure.html.twig';

Component.register('sw-cms-el-config-netzp-blog6-structure', {
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
