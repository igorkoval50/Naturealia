import Plugin from 'src/plugin-system/plugin.class';
import Swiper from '@swiper/bundle';

export default class AllcoSwiperSlider extends Plugin {
    init() {

        let isSwiperExist = document.querySelector('.swiper-container');
        if (isSwiperExist === null)
        {
            console.log('null');
            return;
        }


        if (document.querySelectorAll('.swiper-container-category').length > 0) {
            let swipers = new Swiper('.swiper-container-category', {
                slidesPerView: 4.5,
                spaceBetween: 40,
                navigation: {
                    nextEl: '.swiper-button-next',
                },
                breakpoints: {
                    0: {
                        slidesPerView: 1.5,
                        spaceBetween: 15,
                    },
                    480: {
                        slidesPerView: 2.5,
                        spaceBetween: 15,
                    },
                    768: {
                        slidesPerView: 2.5,
                        spaceBetween: 20,
                    },
                    769: {
                        slidesPerView: 3.5,
                        spaceBetween: 20,
                    },
                    1200: {
                        slidesPerView: 3.5,
                        spaceBetween: 30,
                    },
                    1380: {
                        slidesPerView: 4.5,
                        spaceBetween: 30,
                    },
                    1600: {
                        slidesPerView: 4.5,
                        spaceBetween: 40,
                    },
                },
                loop: true,
            });
        }

        if (document.querySelectorAll('.swiper-container-product').length > 0) {
            let swipers = new Swiper('.swiper-container-product', {
                slidesPerView: 4.5,
                spaceBetween: 40,
                navigation: {
                    nextEl: '.swiper-button-next',
                },
                breakpoints: {
                    0: {
                        slidesPerView: 1.5,
                        spaceBetween: 15,
                    },
                    480: {
                        slidesPerView: 2.5,
                        spaceBetween: 15,
                    },
                    768: {
                        slidesPerView: 2.5,
                        spaceBetween: 20,
                    },
                    769: {
                        slidesPerView: 3.5,
                        spaceBetween: 20,
                    },
                    1000: {
                        slidesPerView: 3.5,
                        spaceBetween: 20,
                    },
                    1200: {
                        slidesPerView: 3.5,
                        spaceBetween: 30,
                    },
                    1380: {
                        slidesPerView: 4.5,
                        spaceBetween: 30,
                    },
                    1600: {
                        slidesPerView: 4.5,
                        spaceBetween: 40,
                    },
                },
                loop: true,
            });
        }
        if (document.querySelectorAll('.swiper-container-vertical').length > 0) {
            let swiper = new Swiper('.swiper-container-vertical', {
                direction: 'vertical',
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });
        }
    }
}
