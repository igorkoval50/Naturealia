import AllcoSwiperSlider
    from './allco-swiper-slider/allco-swiper-slider.plugin';

window.PluginManager.register(
    'AllcoSwiperSlider',
    AllcoSwiperSlider,
    '[data-swag-allco-swiper-slider]'
);
if (module.hot) {
    module.hot.accept();
}
