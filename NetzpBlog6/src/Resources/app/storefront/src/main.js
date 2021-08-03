import Netzp6BlogGallery from './netzp-blog-gallery/netzp-blog-gallery.plugin';
var PluginManager = window.PluginManager;
PluginManager.register('Netzp6BlogGallery', Netzp6BlogGallery, '[data-netzp-blog-gallery]');

if (module.hot) {
    module.hot.accept();
}
