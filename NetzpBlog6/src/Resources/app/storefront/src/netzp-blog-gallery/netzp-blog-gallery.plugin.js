import Plugin from 'src/plugin-system/plugin.class';
import LightGallery from '../../../../../../node_modules/lightgallery.js';
import LightGalleryVideo from '../../../../../../node_modules/lg-video.js';
import LightGalleryZoom from '../../../../../../node_modules/lg-zoom.js';
// https://sachinchoolur.github.io/lightgallery.js/docs/#main-features

export default class Netzp6BlogGallery extends Plugin {
    static options = {
        selectorId: "",
        selector: "",
        download: false,
        counter: true,
        captionFromTitleOrAlt: true
    };

    init() {
        lightGallery(document.getElementById(this.options.selectorId), {
            download: this.options.download,
            counter: this.options.counter,
            getCaptionFromTitleOrAlt: this.options.captionFromTitleOrAlt,
            selector: this.options.selector,
            zoom: true
        });
    }
}
