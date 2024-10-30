(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
	 *
	 * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    window.cGalerry = {
        el: {
            window: $(window),
            document: $(document),
            owl_view_image: $(".comm-owl-carousel"),
            owl_view_image_thumb: $(".comm-owl-carousel-thumb"),
            slidesPerPage: 4,
            number: 0,
        },
        fn: {
            syncPosition :function (el) {
                $(".comm-owl-carousel-thumb .owl-item").each(function () {
                    $(this).removeClass("current active")
                });

                $(".comm-owl-carousel-thumb")
                    .find(".owl-item").eq(el.item.index).removeClass("current active").addClass("current active");
            },
            initializeViewGallery: function () {
                $(".comm-owl-carousel").owlCarousel({
                    items: 1,
                    slideSpeed: 2000,
                    nav: true,
                    autoplay: false,
                    dots: false,
                    loop: false,
                    responsiveRefreshRate: 200,
                    navText: ['<svg width="50%" height="50%" viewBox="0 0 11 20"><path style="fill:none;stroke-width: 1px;stroke: #616161;" d="M9.554,1.001l-8.607,8.607l8.607,8.606"/></svg>', '<svg width="50%" height="50%" viewBox="0 0 11 20" version="1.1"><path style="fill:none;stroke-width: 1px;stroke: #616161;" d="M1.054,18.214l8.606,-8.606l-8.606,-8.607"/></svg>'],
                }).on('changed.owl.carousel', cGalerry.fn.syncPosition);
            },
            initializeGalleryThumb: function () {
                $(".comm-owl-carousel-thumb").owlCarousel({
                    items: $('.comm-owl-carousel-thumb .owl-item').length,
                    dots: false,
                    nav: false,
                    smartSpeed: 200,
                    slideSpeed: 500,
                    // slideBy: 4,
                    responsiveRefreshRate: 100,
                });
                $(".comm-owl-carousel-thumb")
                    .find(".owl-item:first-child").addClass("current");
            },
            owl_carousel_click: function () {
                $(".comm-owl-carousel-thumb").on("click", ".owl-item", function (e) {
                    e.preventDefault();
                    cGalerry.el.number = $(this).index();
                    $(".comm-owl-carousel-thumb .owl-item").each(function () {
                        $(this).removeClass("current active")
                    });

                    $(".comm-owl-carousel-thumb")
                        .find(".owl-item").eq(cGalerry.el.number).addClass("current active");
                    $(".comm-owl-carousel").data('owl.carousel').to(cGalerry.el.number, 300, true);
                });
            }
        },
        run: function () {
            //WINDOW LOAD
            cGalerry.el.window.on("load", function () {
            });

            cGalerry.el.document.ready(function () {
                cGalerry.fn.initializeViewGallery();
                cGalerry.fn.initializeGalleryThumb();
                cGalerry.fn.owl_carousel_click();
            });


        }
    };
    cGalerry.run();
})(jQuery);