//====================================================
// CoDevPro Technology
// js/ofertas_pagina.js
//====================================================

"use strict";

/*=========================================
VARIABLES
=========================================*/

let pagina = 1;
let orden = "recientes";
let cargando = false;

/*=========================================
AL CARGAR LA PÁGINA
=========================================*/

document.addEventListener("DOMContentLoaded", function () {
  actualizarTextoOrden();

  cargarProductos(true);
});

/*=========================================
CARGAR PRODUCTOS
=========================================*/

function cargarProductos(reiniciar = false) {
  if (cargando) return;

  cargando = true;

  if (reiniciar) {
    pagina = 1;

    document.getElementById("contenedorOfertas").innerHTML = `
            <div class="col-12 text-center py-5">

                <div class="spinner-border text-primary"></div>

                <p class="mt-3">

                    Cargando productos...

                </p>

            </div>
        `;
  }

  const datos = new FormData();

  datos.append("pagina", pagina);
  datos.append("orden", orden);

  /*=========================
FILTROS
=========================*/

  datos.append(
    "categoria",
    document.getElementById("filtroCategoria")?.value || "",
  );

  datos.append("marca", document.getElementById("filtroMarca")?.value || "");

  datos.append("precioMin", document.getElementById("precioMin")?.value || "");

  datos.append("precioMax", document.getElementById("precioMax")?.value || "");

  datos.append("stock", document.getElementById("soloStock")?.checked ? 1 : 0);

  datos.append(
    "envioGratis",
    document.getElementById("envioGratis")?.checked ? 1 : 0,
  );

  fetch("ajax/cargar_productos_oferta.php", {
    method: "POST",

    body: datos,
  })
    .then((res) => res.text())

    .then((html) => {
      if (reiniciar) {
        document.getElementById("contenedorOfertas").innerHTML = html;
      } else {
        document
          .getElementById("contenedorOfertas")
          .insertAdjacentHTML("beforeend", html);
      }

      actualizarContador();

      generarPaginacion();

      cargando = false;
    })

    .catch((error) => {
      console.error(error);

      cargando = false;

      Swal.fire({
        icon: "error",

        title: "Error",

        text: "No fue posible cargar las ofertas.",
      });
    });
}

/*=========================================
ACTUALIZAR CONTADOR
=========================================*/

function actualizarContador() {
  const total = document.getElementById("totalProductosAjax");

  if (!total) return;

  document.getElementById("totalProductos").innerHTML =
    total.value + " productos encontrados";
}

/*=========================================
ORDENAR
=========================================*/

const ordenar = document.getElementById("ordenarProductos");

if (ordenar) {
  ordenar.addEventListener("change", function () {
    orden = this.value;

    pagina = 1;

    actualizarTextoOrden();

    cargarProductos(true);
  });
}

/*=========================================
CARGAR MÁS
=========================================*/

/*const btnMas = document.getElementById("btnCargarMas");

if (btnMas) {
  btnMas.addEventListener("click", function () {
    pagina++;

    this.disabled = true;

    this.innerHTML = `

            <span class="spinner-border spinner-border-sm"></span>

            Cargando...

        `;

    cargarProductos(false);

    setTimeout(() => {
      this.disabled = false;

      this.innerHTML = "Cargar más productos";
    }, 600);
  });
}*/

/*=========================================
CAMBIAR VISTA
=========================================*/

const btnGrid = document.getElementById("vistaGrid");

const btnLista = document.getElementById("vistaLista");

if (btnGrid) {
  btnGrid.addEventListener("click", function () {
    document.getElementById("contenedorOfertas").classList.remove("lista");
  });
}

if (btnLista) {
  btnLista.addEventListener("click", function () {
    document.getElementById("contenedorOfertas").classList.add("lista");
  });
}
/*=========================================
PAGINACIÓN
=========================================*/

function generarPaginacion() {
  const totalPaginas = parseInt(
    document.getElementById("totalPaginasAjax")?.value || 1,
  );

  const paginacion = document.getElementById("paginacionOfertas");

  if (!paginacion) return;

  paginacion.innerHTML = "";

  /*------------------------------
    Anterior
    ------------------------------*/

  let anterior = document.createElement("li");

  anterior.className = "page-item " + (pagina == 1 ? "disabled" : "");

  anterior.innerHTML = `<a class="page-link" href="#">Anterior</a>`;

  anterior.onclick = function (e) {
    e.preventDefault();

    if (pagina > 1) {
      pagina--;

      cargarProductos(true);
    }
  };

  paginacion.appendChild(anterior);

  /*------------------------------
    Números
    ------------------------------*/

  for (let i = 1; i <= totalPaginas; i++) {
    let li = document.createElement("li");

    li.className = "page-item " + (i == pagina ? "active" : "");

    li.innerHTML = `<a class="page-link" href="#">${i}</a>`;

    li.onclick = function (e) {
      e.preventDefault();

      pagina = i;

      cargarProductos(true);
    };

    paginacion.appendChild(li);
  }

  /*------------------------------
    Siguiente
    ------------------------------*/

  let siguiente = document.createElement("li");

  siguiente.className =
    "page-item " + (pagina == totalPaginas ? "disabled" : "");

  siguiente.innerHTML = `<a class="page-link" href="#">Siguiente</a>`;

  siguiente.onclick = function (e) {
    e.preventDefault();

    if (pagina < totalPaginas) {
      pagina++;

      cargarProductos(true);
    }
  };

  paginacion.appendChild(siguiente);
}
function actualizarTextoOrden() {
  const textos = {
    recientes: "Más recientes",

    vendidos: "Más vendidos",

    precio_asc: "Precio: Menor a Mayor",

    precio_desc: "Precio: Mayor a Menor",

    descuento: "Mayor descuento",

    destacados: "Productos destacados",

    nombre_asc: "Nombre A - Z",

    nombre_desc: "Nombre Z - A",
  };

  const texto = document.getElementById("textoOrden");

  if (texto) {
    texto.textContent = textos[orden];
  }
}
/*=========================================
APLICAR FILTROS
=========================================*/

const btnAplicar = document.getElementById("btnAplicarFiltros");

if (btnAplicar) {
  btnAplicar.addEventListener("click", function () {
    pagina = 1;

    cargarProductos(true);
  });
}

/*=========================================
LIMPIAR FILTROS
=========================================*/

const btnLimpiar = document.getElementById("btnLimpiarFiltros");

if (btnLimpiar) {
  btnLimpiar.addEventListener("click", function () {
    document.getElementById("filtroCategoria").value = "";

    document.getElementById("filtroMarca").value = "";

    document.getElementById("precioMin").value = "";

    document.getElementById("precioMax").value = "";

    document.getElementById("soloStock").checked = false;

    document.getElementById("envioGratis").checked = false;

    pagina = 1;

    cargarProductos(true);
  });
}
/*=========================================
VISTA RÁPIDA PRODUCTO OFERTAS
=========================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnVista");

  if (!boton) return;

  e.preventDefault();

  const idProducto = boton.dataset.id;

  fetch("ajax/vista_producto.php?id=" + idProducto)
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("contenidoProducto").innerHTML = html;

      const modalElemento = document.getElementById("modalProducto");

      const modal = new bootstrap.Modal(modalElemento);

      modal.show();
    })

    .catch((error) => {
      console.error(error);

      Swal.fire({
        icon: "error",

        title: "Error",

        text: "No se pudo cargar el producto.",
      });
    });
});
