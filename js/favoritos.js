//======================================================
// FAVORITOS - CoDevPro Technology
// Toda esta parte es de js/favoritos
//======================================================

"use strict";

/*======================================================
=            CONFIGURACIÓN
======================================================*/

const URL_FAVORITOS = {
  agregar: "ajax/agregar_favorito.php",
  contador: "ajax/obtener_contador_favoritos.php",
  cargar: "ajax/cargar_favoritos.php",
  eliminar: "ajax/eliminar_favorito.php",
};

/*======================================================
=            SWEETALERT (opcional reutilizable)
======================================================*/

function mensaje(tipo, titulo, texto, tiempo = 1800) {
  Swal.fire({
    icon: tipo,
    title: titulo,
    text: texto,
    timer: tiempo,
    showConfirmButton: false,
  });
}

/*======================================================
=            CONTADOR FAVORITOS
======================================================*/

function actualizarContadorFavoritos(total) {
  const badge = document.getElementById("contadorFavoritos");

  if (!badge) return;

  badge.innerText = total;

  if (parseInt(total) <= 0) {
    badge.classList.add("d-none");
  } else {
    badge.classList.remove("d-none");
  }
}

/*======================================================
=            OBTENER CONTADOR FAVORITOS
======================================================*/

function obtenerContadorFavoritos() {
  fetch(URL_FAVORITOS.contador)
    .then((res) => res.json())
    .then((data) => {
      if (data.estado) {
        actualizarContadorFavoritos(data.contador);
      }
    })
    .catch(() => {
      console.error("No se pudo obtener favoritos.");
    });
}
/*======================================================
=            CARGAR FAVORITOS
======================================================*/

function cargarFavoritos() {
  const contenedor = document.getElementById("contenedorFavoritos");

  if (!contenedor) return;

  fetch(URL_FAVORITOS.cargar)
    .then((res) => res.text())

    .then((html) => {
      contenedor.innerHTML = html;
      // IMPORTANTE
      aplicarFiltrosFavoritos();
    })

    .catch(() => {
      contenedor.innerHTML = `

                <div class="alert alert-danger text-center">

                    No fue posible cargar los favoritos.

                </div>

            `;
    });
}

/*======================================================
=            AGREGAR / QUITAR FAVORITO
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnFavorito");

  if (!boton) return;

  const icono = boton.querySelector("i");

  // ¿Ya está agregado?
  const agregado = icono.classList.contains("bi-heart-fill");

  const url = agregado ? URL_FAVORITOS.eliminar : URL_FAVORITOS.agregar;

  let datos = new FormData();

  datos.append("idProducto", boton.dataset.id);

  fetch(url, {
    method: "POST",

    body: datos,
  })
    .then((res) => res.json())

    .then((data) => {
      if (!data.estado) {
        //mensaje("warning", "Atención", data.mensaje);
        //obtenerContadorFavoritos();
        const tarjeta = boton.closest(
          ".col-xl-3, .col-lg-4, .col-md-6, .col-lg-3, .col-md-4",
        );

        if (tarjeta && document.getElementById("contenedorFavoritos")) {
          tarjeta.style.transition = ".35s";

          tarjeta.style.opacity = "0";

          tarjeta.style.transform = "scale(.90)";

          setTimeout(() => {
            tarjeta.remove();

            if (
              document.querySelectorAll("#contenedorFavoritos .product-card")
                .length === 0
            ) {
              cargarFavoritos();
            }
          }, 350);
        }
        return;
      }

      obtenerContadorFavoritos();

      if (data.accion === "agregado") {
        icono.classList.remove("bi-heart");

        icono.classList.add("bi-heart-fill");

        icono.classList.add("text-danger");
      }

      if (data.accion === "eliminado") {
        icono.classList.remove("bi-heart-fill");

        icono.classList.remove("text-danger");

        icono.classList.add("bi-heart");

        // Si estamos en favoritos.php
        const tarjeta = boton.closest(
          ".col-xl-3, .col-lg-4, .col-md-6, .col-lg-3, .col-md-4",
        );

        if (tarjeta && document.getElementById("contenedorFavoritos")) {
          tarjeta.style.transition = ".35s";

          tarjeta.style.opacity = "0";

          tarjeta.style.transform = "scale(.90)";

          setTimeout(() => {
            tarjeta.remove();

            if (
              document.querySelectorAll("#contenedorFavoritos .product-card")
                .length === 0
            ) {
              cargarFavoritos();
            }
          }, 350);
        }
      }

      mensaje("success", "Favoritos", data.mensaje);
    })

    .catch(() => {
      mensaje("error", "Error", "No fue posible procesar la solicitud.");
    });
});

/*======================================================
=            BUSCAR
======================================================*/

document.addEventListener("keyup", function (e) {
  if (e.target.id === "buscarFavorito") {
    aplicarFiltrosFavoritos();
  }
});
/*======================================================
=            CAMBIAR ORDEN
======================================================*/

document.addEventListener("change", function (e) {
  if (e.target.id === "ordenFavorito") {
    aplicarFiltrosFavoritos();
  }
});
/*======================================================
=            BOTÓN BUSCAR
======================================================*/

document.addEventListener("click", function (e) {
  if (e.target.id === "btnBuscarFavoritos") {
    aplicarFiltrosFavoritos();
  }
});
/*======================================================
=            FILTROS FAVORITOS
======================================================*/

function aplicarFiltrosFavoritos() {
  const texto = document
    .getElementById("buscarFavorito")
    .value.toLowerCase()
    .trim();

  const orden = document.getElementById("ordenFavorito").value;

  const contenedor = document.getElementById("contenedorFavoritos");

  if (!contenedor) return;

  const columnas = Array.from(
    contenedor.querySelectorAll(
      ".col-xl-3, .col-lg-4, .col-md-6, .col-lg-3, .col-md-4",
    ),
  );

  /*=====================================
  BUSCAR
  =====================================*/

  columnas.forEach((columna) => {
    const card = columna.querySelector(".product-card");

    if (!card) return;

    const nombre = card.querySelector("h5").innerText.toLowerCase();

    columna.style.display = nombre.includes(texto) ? "" : "none";
  });

  /*=====================================
  ORDENAR
  =====================================*/

  columnas.sort((a, b) => {
    const cardA = a.querySelector(".product-card");
    const cardB = b.querySelector(".product-card");

    const nombreA = cardA.querySelector("h5").innerText.toLowerCase();

    const nombreB = cardB.querySelector("h5").innerText.toLowerCase();

    // Obtener precio
    const precioA = parseFloat(cardA.dataset.precio || 0);

    const precioB = parseFloat(cardB.dataset.precio || 0);

    switch (orden) {
      case "precio_asc":
        return precioA - precioB;

      case "precio_desc":
        return precioB - precioA;

      case "nombre_asc":
        return nombreA.localeCompare(nombreB);

      case "nombre_desc":
        return nombreB.localeCompare(nombreA);

      case "recientes":
      default:
        return 0;
    }
  });

  // Volver a insertar las tarjetas
  columnas.forEach((item) => {
    contenedor.appendChild(item);
  });
}
/*======================================================
=            INICIALIZACIÓN
======================================================*/

document.addEventListener("DOMContentLoaded", function () {
  obtenerContadorFavoritos();
  cargarFavoritos();
});
