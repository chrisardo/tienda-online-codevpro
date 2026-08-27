//=========================================================
// CoDevPro Technology
// js/ofertas.js
//=========================================================

"use strict";

let swiperOfertas = null;

/*=========================================================
=            CARGAR OFERTAS
=========================================================*/

document.addEventListener("DOMContentLoaded", function () {
  cargarOfertas();
});

function cargarOfertas() {
  fetch("ajax/cargar_ofertas.php")
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("sliderOfertas").innerHTML = html;

      iniciarSwiper();
    })

    .catch((error) => {
      console.error(error);
    });
}

/*=========================================================
=            SWIPER
=========================================================*/

function iniciarSwiper() {
  if (swiperOfertas) {
    swiperOfertas.destroy(true, true);
  }

  swiperOfertas = new Swiper(".ofertasSwiper", {
    loop: true,

    speed: 700,

    spaceBetween: 20,

    grabCursor: true,

    autoplay: {
      delay: 3000,

      disableOnInteraction: false,

      pauseOnMouseEnter: true,
    },

    navigation: {
      nextEl: ".swiper-button-next",

      prevEl: ".swiper-button-prev",
    },

    pagination: {
      el: ".swiper-pagination",

      clickable: true,
    },

    breakpoints: {
      0: {
        slidesPerView: 1,
      },

      576: {
        slidesPerView: 2,
      },

      992: {
        slidesPerView: 3,
      },

      1200: {
        slidesPerView: 4,
      },
    },
  });
}
