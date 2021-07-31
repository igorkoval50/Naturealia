import template from './sw-cms-el-image-slider.html.twig';
import './sw-cms-el-image-slider.scss';

const { Component, Mixin } = Shopware;

Component.register('sw-cms-el-image-slider-text', {
    template,

    mixins: [
        Mixin.getByName('cms-element')
    ],

    props: {
        activeMedia: {
            type: [Object, null],
            required: false,
            default: null
        }
    },

    data() {
        return {
            columnCount: 7,
            columnWidth: 90,
            sliderPos: 0,
            imgPath: '/administration/static/img/cms/preview_mountain_large.jpg',
            imgSrc: '',
            imgText: [],
            defaultSlideContent: `
                        <h2>${this.$tc('sw-cms.elements.defaultSlideContent.title')}</h2>
                        <p>${this.$tc('sw-cms.elements.defaultSlideContent.text')}</p>
                        <p><a target="_self" href="#" class="btn btn-primary">${this.$tc('sw-cms.elements.defaultSlideContent.buttonText')}</a><br></p>
                    `.trim(),
            counter: 0
        };
    },

    computed: {
        gridAutoRows() {
            return `grid-auto-rows: ${this.columnWidth}`;
        },

        uploadTag() {
            return `cms-element-media-config-${this.element.id}`;
        },

        sliderItems() {
            if (this.element.data && this.element.data.sliderItems && this.element.data.sliderItems.length > 0) {
                return this.element.data.sliderItems;
            }

            return [];
        },

        positionClass() {
            return `is--${this.element.config.position.value}`;
        },

        outsideNavArrows() {
            if (this.element.config.navigationArrows.value === 'outside') {
                return 'has--outside-arrows';
            }

            return null;
        },

        contextAssetPath() {
            return Shopware.Context.api.assetsPath;
        }
    },

    watch: {
        'element.data.sliderItems': {
            handler() {
                if (this.sliderItems.length > 0) {
                    this.imgSrc = this.sliderItems[0].media.url;
                    this.$emit('active-image-change', this.sliderItems[0].media);
                    this.imgText = this.element.config.sliderItems.value;
                    for (let index = 0; index < this.imgText.length; index++) {
                        if (typeof(this.imgText[index].content) == "undefined" || this.imgText[index].content.length <= 0) {
                            this.imgText[index].content = this.defaultSlideContent;
                        }
                    }
                } else {
                    this.imgSrc = `${this.contextAssetPath}${this.imgPath}`;
                }
            },
            deep: true
        },

        activeMedia() {
            this.sliderPos = this.activeMedia.sliderIndex;
            this.imgSrc = this.activeMedia.url;
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('image-slider-text');
            this.initElementData('image-slider-text');

            if (this.element.data && this.element.data.sliderItems && this.element.data.sliderItems.length > 0) {
                this.imgSrc = this.sliderItems[0].media.url;
                this.$emit('active-image-change', this.sliderItems[this.sliderPos].media);
            } else {
                this.imgSrc = `${this.contextAssetPath}${this.imgPath}`;
            }
        },

        setSliderItem(mediaItem, index) {
            this.imgSrc = mediaItem.url;
            this.$emit('active-image-change', mediaItem, index);
        },

        activeButtonClass(url) {
            return {
                'is--active': this.imgSrc === url
            };
        },

        setSliderArrowItem(direction = 1) {
            if (this.sliderItems.length < 2) {
                return;
            }

            this.sliderPos += direction;

            if (this.sliderPos < 0) {
                this.counter = 0;
                this.sliderPos = this.sliderItems.length - 1;
                this.counter = this.sliderItems.length;
            }
            this.counter--;

            this.imgSrc = this.sliderItems[this.sliderPos].media.url;
            this.$emit('active-image-change', this.sliderItems[this.sliderPos].media, this.sliderPos);
        },

        onBlur() {
            this.$emit('element-update', this.element);
        },

        onInput() {
            this.$emit('element-update', this.element);
        },

        setSliderArrowItemRight(direction = 1) {
            if (this.sliderItems.length < 2) {
                return;
            }
            this.sliderPos += direction;

            if (this.sliderPos > this.sliderItems.length - 1) {
                this.counter = 0;
                this.sliderPos = 0;
                this.counter--;
            }
            this.counter++;
            this.imgSrc = this.sliderItems[this.sliderPos].media.url;
            this.$emit('active-image-change', this.sliderItems[this.sliderPos].media, this.sliderPos);
        }
    }
});
