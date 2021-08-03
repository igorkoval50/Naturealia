import { Component, Mixin } from 'src/core/shopware';
import './sw-cms-el-config-netzp-blog6.scss';
const { Criteria, EntityCollection } = Shopware.Data;

import template from './sw-cms-el-config-netzp-blog6.html.twig';

Component.register('sw-cms-el-config-netzp-blog6', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('cms-element')
    ],

    data() {
        return {
            repositoryCategories: null,
            repositoryAuthors: null,
            repositoryTags: null,
            categories: [],
            authors: [],
            tagsCollection: null
        };
    },

    created() {
        this.tagsCollection = new EntityCollection('/tags', 'tag', Shopware.Context.api);
        this.repositoryCategories = this.repositoryFactory.create('s_plugin_netzp_blog_category');
        this.repositoryAuthors = this.repositoryFactory.create('s_plugin_netzp_blog_author');
        this.repositoryTags = this.repositoryFactory.create('tag');
        this.getCategories();
        this.getAuthors();
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('netzp-blog6');

            if (! this.element.config.tags.value || this.element.config.tags.value.length <= 0) {
                return;
            }

            var criteria = new Criteria(1, 100);
            criteria.setIds(this.element.config.tags.value);

            this.repositoryTags
                .search(criteria, Object.assign({}, Shopware.Context.api))
                .then((result) => {
                    this.tagsCollection = result;
                });
        },

        getCategories() {
            this.repositoryCategories.search(new Criteria(), Shopware.Context.api).then((result) => {
                this.categories = result;
                this.categories.unshift({
                    id: '00000000000000000000000000000000',
                    title: '---'
                })
            });
        },

        getAuthors() {
            this.repositoryAuthors.search(new Criteria(), Shopware.Context.api).then((result) => {
                this.authors = result;
                this.authors.unshift({
                    id: '00000000000000000000000000000000',
                    name: '---'
                })
            });
        },

        onTagsChange() {
            this.element.config.tags.value = this.tagsCollection.getIds();
        }
    }
});
