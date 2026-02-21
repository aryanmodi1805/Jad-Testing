import PhotoSwipeLightbox from 'photoswipe/lightbox';
import PhotoSwipe from 'photoswipe';
import PhotoSwipeSlideshow from './photoswipe-slideshow.esm.min.js';
import PhotoSwipeFullscreen from './photoswipe-fullscreen.esm.min.js';
import 'photoswipe/style.css';

const backEasing = {
    in: 'cubic-bezier(0.6, -0.28, 0.7, 1)',
    out: 'cubic-bezier(0.3, 0, 0.32, 1.275)',
    inOut: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'
};



export default function lightbox({
            showItemsInContainer = false,
            manualThumbs = false,
            data = [],
  })
    {
    return {
        lightbox: null,

        init: function () {

            const options = {
                dataSource: manualThumbs ? undefined  : data,
                imageClickAction: 'next',
                pswpModule: PhotoSwipe,
                showHideAnimationType: 'zoom',
                showAnimationDuration: 1000,
                hideAnimationDuration: 1000,
                mainClass: 'lightbox-container',
                wheelToZoom: true,
                children: showItemsInContainer ? 'a' : undefined ,
                closeOnVerticalDrag: false,
                pinchToClose: false,
                escKey: true,
                thumbSelector: true,
                gallery: showItemsInContainer ? this.$refs.container : undefined ,
            };

            this.lightbox = new PhotoSwipeLightbox(options);

            const box = this.lightbox;

            const _slideshowPlugin = new PhotoSwipeSlideshow(this.lightbox, {
                defaultDelayMs: 4000, // 4 sec
            });

            const fullscreenPlugin = new PhotoSwipeFullscreen(this.lightbox);

            box.on('firstUpdate', () => {
                box.pswp.options.easing = backEasing.out;
            });
            box.on('initialZoomInEnd', () => {
                box.pswp.options.easing = backEasing.inOut;
            });
            box.on('close', () => {
                box.pswp.options.easing = backEasing.in;
            });
            box.on('uiRegister', function() {
                            box.pswp.ui.registerElement({
                                name: 'lightbox-previews',
                                className: 'lightbox-previews',
                                appendTo: 'wrapper',
                                onInit: (el, pswp) => {
                                    const previews = [];
                                    let preview;
                                    let prevIndex = -1;

                                    for (let i = 0; i < pswp.getNumItems(); i++) {
                                        preview = document.createElement('img');
                                        preview.src = data.at(i).thumb;
                                        preview.onclick = (e) => {
                                            pswp.goTo(i);
                                        };
                                        el.appendChild(preview);
                                        previews.push(preview);
                                    }

                                    pswp.on('change', (a,) => {
                                        if (prevIndex >= 0) {
                                            previews[prevIndex].classList.remove('preview-active');
                                        }
                                        const selector = previews[pswp.currIndex];

                                        selector.classList.add('preview-active');

                                        setTimeout(() => {
                                            window.scrollToElementIfNotVisible(selector,
                                        el)
                                        }, 50)
                                        prevIndex = pswp.currIndex;
                                    });
                                }
                            });
                            box.pswp.ui.registerElement({
                                name: 'download-button',
                                order: 8,
                                isButton: true,
                                tagName: 'a',

                                // SVG with outline
                                html: {
                                    isCustomSVG: true,
                                    inner: '<path d="M20.5 14.3 17.1 18V10h-2.2v7.9l-3.4-3.6L10 16l6 6.1 6-6.1ZM23 23H9v2h14Z" id="pswp__icn-download"/>',
                                    outlineID: 'pswp__icn-download'
                                },

                                onInit: (el, pswp) => {
                                    el.setAttribute('download', '');
                                    el.setAttribute('target', '_blank');
                                    el.setAttribute('rel', 'noopener');

                                    pswp.on('change', () => {
                                        el.href = pswp.currSlide.data.src;
                                    });
                                }
                            });
                        });

            box.init();

            if(!manualThumbs) {
                if (showItemsInContainer) {
                    data.forEach((item) => {
                        const element = document.createElement('a');
                        element.href = item.src;
                        element.setAttribute('data-pswp-width', item.width);
                        element.setAttribute('data-pswp-height', item.height);
                        element.setAttribute('data-cropped', true);
                        element.target = '_blank';
                        element.innerHTML = '<img src="' + item.thumb + '" alt="" class="lightbox-image">';
                        this.$refs.container.appendChild(element);
                    });
                } else {
                    this.$refs.container.onclick = () => {
                        box.loadAndOpen(0); // defines start slide index
                    };
                }
            }
        },

    }
}
