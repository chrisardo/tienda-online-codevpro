//totda esta parte pertenece a js/tienda.js
"use strict";

/*=========================================================
=           CARGAR PRODUCTOS MEDIANTE AJAX
=========================================================*/

function cargarProductos() {
  let datos = new FormData();

  /*==============================
    BUSCADOR
    ==============================*/

  datos.append("buscar", document.getElementById("buscarProducto").value);

  /*==============================
    CATEGORÍAS
    ==============================*/

  document.querySelectorAll(".filtroCategoria:checked").forEach((item) => {
    datos.append("categorias[]", item.value);
  });

  /*==============================
    MARCAS
    ==============================*/

  document.querySelectorAll(".filtroMarca:checked").forEach((item) => {
    datos.append("marcas[]", item.value);
  });

  /*==============================
    PRECIOS
    ==============================*/

  datos.append("precioMin", document.getElementById("precioMin").value);

  datos.append("precioMax", document.getElementById("precioMax").value);

  /*==============================
    STOCK
    ==============================*/

  datos.append(
    "stock",
    document.getElementById("stockDisponible").checked ? 1 : 0,
  );

  /*==============================
    ORDEN
    ==============================*/

  datos.append("orden", document.getElementById("ordenar").value);

  /*==============================
    LOADER
    ==============================*/

  document.getElementById("contenedorProductos").innerHTML = `

        <div class="col-12 text-center py-5">

            <div class="spinner-border text-primary"></div>

        </div>

    `;

  /*==============================
    AJAX
    ==============================*/

  fetch("ajax/filtrar_productos.php", {
    method: "POST",

    body: datos,
  })
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("contenedorProductos").innerHTML = html;

      //iniciarEventosVistaRapida();
    })

    .catch(() => {
      document.getElementById("contenedorProductos").innerHTML = `

            <div class="col-12">

                <div class="alert alert-danger">

                    Error al cargar productos.

                </div>

            </div>

        `;
    });
}
/*=========================================================
=           VISTA RÁPIDA (DELEGACIÓN)
=========================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnVista");

  if (!boton) return;

  e.preventDefault();

  const id = boton.dataset.id;

  fetch("ajax/vista_producto.php?id=" + id)
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("contenidoProducto").innerHTML = html;

      const modal = new bootstrap.Modal(
        document.getElementById("modalProducto"),
      );

      modal.show();
    })

    .catch((error) => {
      console.error(error);

      Swal.fire("Error", "No se pudo cargar el producto.", "error");
    });
});
/*=========================================================
=           BUSCADOR EN TIEMPO REAL
=========================================================*/

const txtBuscar = document.getElementById("buscarProducto");

if (txtBuscar) {
  txtBuscar.addEventListener("keyup", function () {
    cargarProductos();
  });
}

/*=========================================================
=           BOTÓN APLICAR FILTROS
=========================================================*/

const btnAplicar = document.getElementById("btnAplicarFiltros");

if (btnAplicar) {
  btnAplicar.onclick = cargarProductos;
}

/*=========================================================
=           ORDENAR
=========================================================*/

const ordenar = document.getElementById("ordenar");

if (ordenar) {
  ordenar.onchange = cargarProductos;
}

/*=========================================================
=           CHECKBOX CATEGORÍAS
=========================================================*/

document.querySelectorAll(".filtroCategoria").forEach((item) => {
  item.onchange = cargarProductos;
});

/*=========================================================
=           CHECKBOX MARCAS
=========================================================*/

document.querySelectorAll(".filtroMarca").forEach((item) => {
  item.onchange = cargarProductos;
});

/*=========================================================
=           STOCK
=========================================================*/

const stock = document.getElementById("stockDisponible");

if (stock) {
  stock.onchange = cargarProductos;
}

/*=========================================================
=           PRECIOS
=========================================================*/

const minimo = document.getElementById("precioMin");

const maximo = document.getElementById("precioMax");

if (minimo) {
  minimo.onchange = cargarProductos;
}

if (maximo) {
  maximo.onchange = cargarProductos;
}

/*=========================================================
=           LIMPIAR FILTROS
=========================================================*/

const limpiar = document.getElementById("btnLimpiar");

if (limpiar) {
  limpiar.onclick = function () {
    document.getElementById("buscarProducto").value = "";

    document.querySelectorAll(".filtroCategoria").forEach((c) => {
      c.checked = false;
    });

    document.querySelectorAll(".filtroMarca").forEach((m) => {
      m.checked = false;
    });

    document.getElementById("stockDisponible").checked = false;

    cargarProductos();
  };
}

/*=========================================================
=           INICIO
=========================================================*/

/*document.addEventListener("DOMContentLoaded", function () {
  iniciarEventosVistaRapida();
});*/
document.addEventListener("DOMContentLoaded", function () {
  if (document.getElementById("buscarProducto").value.trim() != "") {
    cargarProductos();
  }
});
