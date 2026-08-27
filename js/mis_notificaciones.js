//======================================================
// CoDevPro Technology
// js/mis_notificaciones.js
//======================================================

"use strict";

/*======================================================
=            VARIABLES GLOBALES
======================================================*/

let paginaActual = 1;
let tipoActual = "";
let leidoActual = "";

/*======================================================
=            INICIO DEL SISTEMA
======================================================*/

document.addEventListener("DOMContentLoaded", () => {
  cargarNotificaciones();

  inicializarFiltros();

  inicializarBotones();
});

/*======================================================
=            CARGAR NOTIFICACIONES
======================================================*/

async function cargarNotificaciones() {
  try {
    let url = "ajax/obtener_mis_notificaciones.php?";

    url += "pagina=" + paginaActual;

    if (tipoActual !== "") {
      url += "&tipo=" + tipoActual;
    }

    if (leidoActual !== "") {
      url += "&leido=" + leidoActual;
    }

    const respuesta = await fetch(url);

    const json = await respuesta.json();

    if (json.estado !== "ok") {
      return;
    }

    mostrarNotificaciones(json.notificaciones);

    construirPaginacion(json.totalPaginas, json.pagina);

    actualizarEstadisticas(json);
  } catch (error) {
    console.error(error);
  }
}

/*======================================================
=            MOSTRAR NOTIFICACIONES
======================================================*/

function mostrarNotificaciones(notificaciones) {
  const contenedor = document.getElementById("contenedorMisNotificaciones");

  if (!contenedor) {
    return;
  }

  if (notificaciones.length === 0) {
    contenedor.innerHTML = `

            <div class="text-center py-5">

                <i class="bi bi-bell-slash fs-1 text-muted"></i>

                <p class="mt-3 text-muted">

                    No tienes notificaciones.

                </p>

            </div>

        `;

    return;
  }

  let html = "";

  notificaciones.forEach((n) => {
    const badge =
      n.leido === 0
        ? '<span class="badge bg-danger ms-2">Sin leer</span>'
        : '<span class="badge bg-success ms-2">Leída</span>';

    let botonVerMas = "";

    if (n.url && n.url.trim() !== "" && n.url !== "#") {
      botonVerMas = `

                <a
                    href="${n.url}"
                    class="btn btn-primary btn-sm btnVerMas"
                    data-id="${n.id}"
                    data-url="${n.url}">

                    Ver más

                </a>

            `;
    }

    html += `

            <div
    class="card mb-3 shadow-sm itemNotificacion"
    data-id="${n.id}"
    data-url="${n.url}"
    data-tiene-url="${n.url && n.url !== "#" ? 1 : 0}">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h5 class="fw-bold">

                                <i class="bi ${n.icono} text-${n.color}"></i>

                                ${n.titulo}

                                ${badge}

                            </h5>

                            <small class="text-muted">

                                ${n.fecha}

                            </small>

                        </div>


                        <div>

                            <button
                                class="btn btn-outline-danger btn-sm btnEliminarNotificacion"
                                data-id="${n.id}">

                                <i class="bi bi-trash"></i>

                            </button>

                        </div>

                    </div>


                    <hr>


                    <p class="mb-3">

                        ${n.mensaje}

                    </p>


                    <div class="d-flex justify-content-between align-items-center">

                        <span class="badge bg-secondary">

                            ${n.tipo}

                        </span>


                        ${botonVerMas}


                    </div>

                </div>

            </div>

        `;
  });

  contenedor.innerHTML = html;
}

/*======================================================
=            PAGINACIÓN
======================================================*/

function construirPaginacion(totalPaginas, pagina) {
  const paginacion = document.getElementById("paginacionNotificaciones");

  paginacion.innerHTML = "";

  for (let i = 1; i <= totalPaginas; i++) {
    paginacion.innerHTML += `

            <li class="page-item ${i === pagina ? "active" : ""}">

                <a
                    href="#"
                    class="page-link btnPagina"
                    data-pagina="${i}">

                    ${i}

                </a>

            </li>

        `;
  }
}

/*======================================================
=            CAMBIAR PÁGINA
======================================================*/

document.addEventListener("click", (e) => {
  const pagina = e.target.closest(".btnPagina");

  if (!pagina) {
    return;
  }

  e.preventDefault();

  paginaActual = parseInt(pagina.dataset.pagina);

  cargarNotificaciones();
});

/*======================================================
=            FILTROS
======================================================*/

function inicializarFiltros() {
  const filtros = document.querySelectorAll(".filtroNotificacion");

  filtros.forEach((boton) => {
    boton.addEventListener("click", () => {
      paginaActual = 1;

      const tipo = boton.dataset.tipo;

      tipoActual = "";
      leidoActual = "";

      if (tipo === "todas") {
        tipoActual = "";
      } else if (tipo === "sin_leer") {
        leidoActual = 0;
      } else {
        tipoActual = tipo;
      }

      cargarNotificaciones();
    });
  });
}

/*======================================================
=            BOTONES
======================================================*/

function inicializarBotones() {
  const btnActualizar = document.getElementById("btnActualizarNotificaciones");

  const btnMarcarTodas = document.getElementById("btnMarcarTodas");

  const btnEliminarLeidas = document.getElementById("btnEliminarLeidas");

  if (btnActualizar) {
    btnActualizar.addEventListener("click", cargarNotificaciones);
  }

  if (btnMarcarTodas) {
    btnMarcarTodas.addEventListener("click", marcarTodasComoLeidas);
  }

  if (btnEliminarLeidas) {
    btnEliminarLeidas.addEventListener("click", eliminarLeidas);
  }
}

/*======================================================
=            ELIMINAR NOTIFICACIÓN
======================================================*/

document.addEventListener("click", async (e) => {
  const boton = e.target.closest(".btnEliminarNotificacion");

  if (!boton) {
    return;
  }

  const id = boton.dataset.id;

  const datos = new FormData();

  datos.append("id_notificacion", id);

  await fetch("ajax/eliminar_notificacion_cliente.php", {
    method: "POST",
    body: datos,
  });

  cargarNotificaciones();
});

/*======================================================
=            MARCAR TODAS COMO LEÍDAS
======================================================*/

async function marcarTodasComoLeidas() {
  await fetch("ajax/marcar_todas_notificaciones_leidas.php");

  cargarNotificaciones();
}

/*======================================================
=            ELIMINAR LEÍDAS
======================================================*/

async function eliminarLeidas() {
  await fetch("ajax/eliminar_notificaciones_leidas.php");

  cargarNotificaciones();
}

/*======================================================
=            VER MÁS
======================================================*/

document.addEventListener("click", async (e) => {
  const boton = e.target.closest(".btnVerMas");

  if (!boton) {
    return;
  }

  e.preventDefault();

  const id = boton.dataset.id;

  const url = boton.dataset.url;

  const datos = new FormData();

  datos.append("id_notificacion", id);

  try {
    await fetch("ajax/marcar_notificacion_leida.php", {
      method: "POST",
      body: datos,
    });

    window.location.href = url;
  } catch (error) {
    console.error(error);
  }
});

/*======================================================
=            ESTADÍSTICAS
======================================================*/

function actualizarEstadisticas(json) {
  //document.getElementById("estadisticaTotal").textContent = json.total;

  document.getElementById("cantidadTotalNotificaciones").textContent =
    json.total + " Notificaciones";
}

/*======================================================
=            CLICK EN TODA LA NOTIFICACIÓN
======================================================*/

/*======================================================
= CLICK EN TODA LA NOTIFICACIÓN
======================================================*/

document.addEventListener("click", async (e) => {
  const tarjeta = e.target.closest(".itemNotificacion");

  if (!tarjeta) {
    return;
  }

  // NO EJECUTAR SI HICIERON CLICK EN LOS BOTONES

  if (
    e.target.closest(".btnEliminarNotificacion") ||
    e.target.closest(".btnVerMas") ||
    e.target.closest(".btnPagina")
  ) {
    return;
  }

  const id = tarjeta.dataset.id;

  const url = tarjeta.dataset.url;

  const tieneURL = tarjeta.dataset.tieneUrl;

  // Marcar como leída

  const datos = new FormData();

  datos.append("id_notificacion", id);

  try {
    await fetch("ajax/marcar_notificacion_leida.php", {
      method: "POST",
      body: datos,
    });

    // Si NO tiene URL solamente actualizar la página.

    if (tieneURL === "0") {
      cargarNotificaciones();

      return;
    }

    // Si tiene URL redireccionar.

    window.location.href = url;
  } catch (error) {
    console.error(error);
  }
});
