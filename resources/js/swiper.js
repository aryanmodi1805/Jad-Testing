import { Autoplay,Navigation, Pagination } from 'swiper/modules';

register();


export default function swiperComponent({
                    swiperParams
  })
    {
    return {
        swiper: null,

        init: function () {
            const swiperEl = this.$refs.container;
            // swiper parameters

            const params = {
                modules: [Autoplay, Navigation, Pagination],
                // inject modules styles to shadow DOM

            };

            // merge swiper parameters array with params
            swiperParams = Object.assign(params, swiperParams);

            // now we need to assign all parameters to Swiper element
            Object.assign(swiperEl, swiperParams);

            // and now initialize it
            swiperEl.initialize();

            const parent = this.$refs.parent;
            //remove css hidden class
            parent.classList.remove('invisible');

        },

    }
}
