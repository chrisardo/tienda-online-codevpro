//=====================================================
// CoDevPro Technology
// Archivo: js/adm_notificaciones.js
// Módulo: Notificaciones
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActualNotificaciones = 1;

const registrosPorPaginaNotificaciones = 6;

let cargandoNotificaciones = false;
let cargandoKpisNotificaciones = false;
let guardandoNotificacion = false;
let cargandoClientesNotificacion = false;

let temporizadorBusquedaNotificaciones = null;

//=====================================================
// URLs AJAX
//=====================================================

const URL_KPIS_NOTIFICACIONES = "ajax/obtener_kpis_notificaciones.php";

const URL_NOTIFICACIONES = "ajax/obtener_notificaciones.php";

const URL_GUARDAR_NOTIFICACION = "ajax/registrar_notificacion.php";

const URL_OBTENER_NOTIFICACION = "ajax/obtener_notificacion.php";

const URL_ELIMINAR_NOTIFICACION = "ajax/eliminar_notificacion.php";

const URL_CLIENTES_NOTIFICACION = "ajax/obtener_clientes_notificacion.php";

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarModuloNotificaciones();
});

//=====================================================
// INICIALIZAR MÓDULO
//=====================================================

function inicializarModuloNotificaciones() {
  console.log("Inicializando módulo de notificaciones...");

  inicializarEventosNotificaciones();

  inicializarModalNotificacion();

  inicializarModalVerNotificacion();

  inicializarVistaIconoNotificacion();

  cargarKpisNotificaciones();

  cargarNotificaciones(1);
}

//=====================================================
// EVENTOS
//=====================================================

function inicializarEventosNotificaciones() {
  //---------------------------------------------------
  // BUSCADOR
  //---------------------------------------------------

  const buscar = document.getElementById("buscarNotificacion");

  if (buscar) {
    buscar.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaNotificaciones);

      temporizadorBusquedaNotificaciones = setTimeout(function () {
        paginaActualNotificaciones = 1;

        cargarNotificaciones(1);
      }, 300);
    });
  }

  //---------------------------------------------------
  // FILTRO TIPO
  //---------------------------------------------------

  const filtroTipo = document.getElementById("filtroTipoNotificacion");

  if (filtroTipo) {
    filtroTipo.addEventListener("change", function () {
      paginaActualNotificaciones = 1;

      cargarNotificaciones(1);
    });
  }

  //---------------------------------------------------
  // FILTRO ESTADO
  //---------------------------------------------------

  const filtroEstado = document.getElementById("filtroEstadoNotificacion");

  if (filtroEstado) {
    filtroEstado.addEventListener("change", function () {
      paginaActualNotificaciones = 1;

      cargarNotificaciones(1);
    });
  }

  //---------------------------------------------------
  // FILTRO FECHA
  //---------------------------------------------------

  const filtroFecha = document.getElementById("filtroFechaNotificacion");

  if (filtroFecha) {
    filtroFecha.addEventListener("change", function () {
      paginaActualNotificaciones = 1;

      cargarNotificaciones(1);
    });
  }

  //---------------------------------------------------
  // LIMPIAR FILTROS
  //---------------------------------------------------

  const btnLimpiar = document.getElementById("btnLimpiarFiltros");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", limpiarFiltrosNotificaciones);
  }

  //---------------------------------------------------
  // NUEVA NOTIFICACIÓN
  //---------------------------------------------------

  const btnNueva = document.getElementById("btnNuevaNotificacion");

  if (btnNueva) {
    btnNueva.addEventListener("click", abrirModalNuevaNotificacion);
  }

  //---------------------------------------------------
  // GUARDAR
  //---------------------------------------------------

  const btnGuardar = document.getElementById("btnGuardarNotificacion");

  if (btnGuardar) {
    btnGuardar.addEventListener("click", guardarNotificacion);
  }

  //---------------------------------------------------
  // TABLA
  //---------------------------------------------------

  const tabla = document.getElementById("tablaNotificaciones");

  if (tabla) {
    tabla.addEventListener("click", manejarAccionesNotificacion);
  }
}

//=====================================================
// CARGAR KPIs
//=====================================================

async function cargarKpisNotificaciones() {
  if (cargandoKpisNotificaciones) {
    return;
  }

  cargandoKpisNotificaciones = true;

  const elementoTotal = document.getElementById("kpiTotalNotificaciones");

  const elementoNoLeidas = document.getElementById("kpiNoLeidas");

  const elementoLeidas = document.getElementById("kpiLeidas");

  const elementoHoy = document.getElementById("kpiNotificacionesHoy");

  //---------------------------------------------------
  // LOADING
  //---------------------------------------------------

  [elementoTotal, elementoNoLeidas, elementoLeidas, elementoHoy].forEach(
    function (elemento) {
      if (elemento) {
        elemento.textContent = "...";
      }
    },
  );

  try {
    const respuesta = await fetch(
      URL_KPIS_NOTIFICACIONES + "?_=" + Date.now(),
      {
        method: "GET",

        credentials: "same-origin",

        cache: "no-store",

        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      },
    );

    const datos = await obtenerJSONRespuesta(
      respuesta,
      "obtener_kpis_notificaciones.php",
    );

    if (!respuesta.ok || !datos || datos.success !== true) {
      throw new Error(datos?.mensaje || "No se pudieron obtener los KPIs.");
    }

    const kpis =
      datos.kpis && typeof datos.kpis === "object" ? datos.kpis : datos;

    const total = obtenerNumero(kpis.total_notificaciones, kpis.total, 0);

    const noLeidas = obtenerNumero(
      kpis.no_leidas,
      kpis.noLeidas,
      kpis.no_leidas_notificaciones,
      0,
    );

    const leidas = obtenerNumero(
      kpis.leidas,
      kpis.leidas_notificaciones,
      Math.max(0, total - noLeidas),
    );

    const hoy = obtenerNumero(
      kpis.notificaciones_hoy,
      kpis.hoy,
      kpis.total_hoy,
      0,
    );

    if (elementoTotal) {
      elementoTotal.textContent = formatearNumero(total);
    }

    if (elementoNoLeidas) {
      elementoNoLeidas.textContent = formatearNumero(noLeidas);
    }

    if (elementoLeidas) {
      elementoLeidas.textContent = formatearNumero(leidas);
    }

    if (elementoHoy) {
      elementoHoy.textContent = formatearNumero(hoy);
    }

    console.log("KPIs cargados:", {
      total,
      noLeidas,
      leidas,
      hoy,
    });
  } catch (error) {
    console.error("Error cargando KPIs:", error);

    if (elementoTotal) {
      elementoTotal.textContent = "0";
    }

    if (elementoNoLeidas) {
      elementoNoLeidas.textContent = "0";
    }

    if (elementoLeidas) {
      elementoLeidas.textContent = "0";
    }

    if (elementoHoy) {
      elementoHoy.textContent = "0";
    }
  } finally {
    cargandoKpisNotificaciones = false;
  }
}

//=====================================================
// CARGAR NOTIFICACIONES
//=====================================================

async function cargarNotificaciones(pagina = 1) {
  if (cargandoNotificaciones) {
    return;
  }

  cargandoNotificaciones = true;

  pagina = parseInt(pagina, 10) || 1;

  paginaActualNotificaciones = Math.max(1, pagina);

  const tabla = document.getElementById("tablaNotificaciones");

  mostrarCargandoTablaNotificaciones(tabla);

  try {
    //------------------------------------------------
    // FILTROS
    //------------------------------------------------

    const buscar = obtenerValor("buscarNotificacion");

    const tipo = obtenerValor("filtroTipoNotificacion");

    const estado = obtenerValor("filtroEstadoNotificacion");

    const fecha = obtenerValor("filtroFechaNotificacion");

    //------------------------------------------------
    // PARÁMETROS
    //------------------------------------------------

    const parametros = new URLSearchParams();

    parametros.set("pagina", paginaActualNotificaciones);

    parametros.set("limite", registrosPorPaginaNotificaciones);

    if (buscar !== "") {
      parametros.set("buscar", buscar);
    }

    if (tipo !== "") {
      parametros.set("tipo", tipo);
    }

    if (estado !== "") {
      parametros.set("estado", estado);
    }

    if (fecha !== "") {
      parametros.set("fecha", fecha);
    }

    parametros.set("_", Date.now());

    const respuesta = await fetch(
      URL_NOTIFICACIONES + "?" + parametros.toString(),
      {
        method: "GET",

        credentials: "same-origin",

        cache: "no-store",

        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      },
    );

    const datos = await obtenerJSONRespuesta(
      respuesta,
      "obtener_notificaciones.php",
    );

    if (!respuesta.ok || !datos || datos.success !== true) {
      throw new Error(
        datos?.mensaje || "No se pudieron obtener las notificaciones.",
      );
    }

    //------------------------------------------------
    // LISTA
    //------------------------------------------------

    let notificaciones = [];

    if (Array.isArray(datos.notificaciones)) {
      notificaciones = datos.notificaciones;
    } else if (Array.isArray(datos.data)) {
      notificaciones = datos.data;
    }

    //------------------------------------------------
    // RENDER
    //------------------------------------------------

    renderizarNotificaciones(notificaciones);

    //------------------------------------------------
    // TOTAL
    //------------------------------------------------

    const total = obtenerNumero(
      datos.total,
      datos.total_registros,
      datos.totalNotificaciones,
      0,
    );

    //------------------------------------------------
    // TOTAL PÁGINAS
    //------------------------------------------------

    const totalPaginas = Math.max(
      1,
      obtenerNumero(
        datos.total_paginas,
        datos.totalPaginas,
        datos.paginas,
        Math.ceil(total / registrosPorPaginaNotificaciones) || 1,
      ),
    );

    //------------------------------------------------
    // PÁGINA ACTUAL
    //------------------------------------------------

    paginaActualNotificaciones = Math.min(
      Math.max(1, obtenerNumero(datos.pagina, paginaActualNotificaciones, 1)),
      totalPaginas,
    );

    //------------------------------------------------
    // PAGINACIÓN
    //------------------------------------------------

    renderizarPaginacionNotificaciones(
      paginaActualNotificaciones,
      totalPaginas,
    );

    //------------------------------------------------
    // DESDE
    //------------------------------------------------

    const desde =
      total > 0
        ? obtenerNumero(
            datos.desde,
            (paginaActualNotificaciones - 1) *
              registrosPorPaginaNotificaciones +
              1,
          )
        : 0;

    //------------------------------------------------
    // HASTA
    //------------------------------------------------

    const hasta =
      total > 0
        ? obtenerNumero(
            datos.hasta,
            Math.min(
              paginaActualNotificaciones * registrosPorPaginaNotificaciones,
              total,
            ),
          )
        : 0;

    actualizarInfoPaginacionNotificaciones(desde, hasta, total);
  } catch (error) {
    console.error("Error cargando notificaciones:", error);

    mostrarErrorTablaNotificaciones(tabla, error.message);

    actualizarInfoPaginacionNotificaciones(0, 0, 0);

    renderizarPaginacionNotificaciones(1, 1);
  } finally {
    cargandoNotificaciones = false;
  }
}

//=====================================================
// RENDERIZAR NOTIFICACIONES
//=====================================================

function renderizarNotificaciones(notificaciones) {
  const tabla = document.getElementById("tablaNotificaciones");

  if (!tabla) {
    return;
  }

  if (!Array.isArray(notificaciones) || notificaciones.length === 0) {
    tabla.innerHTML = `

            <tr>

                <td
                    colspan="7"
                    class="text-center py-5">

                    <div class="text-muted">

                        <i
                            class="
                                bi
                                bi-bell-slash
                                fs-1
                                d-block
                                mb-3
                            ">
                        </i>

                        <h6 class="fw-semibold">
                            No hay notificaciones para mostrar
                        </h6>

                        <p class="mb-0">
                            No se encontraron notificaciones
                            con los filtros seleccionados.
                        </p>

                    </div>

                </td>

            </tr>

        `;

    return;
  }

  let html = "";

  notificaciones.forEach(function (notificacion, indice) {
    const id = obtenerNumero(
      notificacion.id_notificacion,
      notificacion.idNotificacion,
      notificacion.id,
      0,
    );

    const cliente = obtenerTexto(
      notificacion.nombre_cliente,
      notificacion.nombre_completo,
      notificacion.cliente,
      "Cliente no disponible",
    );

    const dni = obtenerTexto(notificacion.dni_o_ruc, notificacion.dni, "");

    const titulo = obtenerTexto(notificacion.titulo, "Sin título");

    const mensaje = obtenerTexto(notificacion.mensaje, "");

    const tipo = obtenerTexto(notificacion.tipo, "SISTEMA").toUpperCase();

    const icono = obtenerIconoSeguro(notificacion.icono);

    const color = obtenerColorBootstrap(notificacion.color);

    const fecha = obtenerTexto(
      notificacion.fecha,
      notificacion.fecha_creacion,
      notificacion.fecha_creado,
      "",
    );

    const leido = Number(notificacion.leido) === 1;

    //------------------------------------------------
    // FILA
    //------------------------------------------------

    const claseFila = leido ? "" : "table-warning";

    //------------------------------------------------
    // ESTADO
    //------------------------------------------------

    const estadoHTML = leido
      ? `

                        <span class="badge bg-success">

                            <i
                                class="
                                    bi
                                    bi-envelope-open
                                    me-1
                                ">
                            </i>

                            Leída

                        </span>

                    `
      : `

                        <span
                            class="
                                badge
                                bg-warning
                                text-dark
                            ">

                            <i
                                class="
                                    bi
                                    bi-envelope-fill
                                    me-1
                                ">
                            </i>

                            No leída

                        </span>

                    `;

    //------------------------------------------------
    // TIPO
    //------------------------------------------------

    const tipoHTML = `

                <span
                    class="badge bg-${color}">

                    ${escapeHTML(tipo)}

                </span>

            `;

    //------------------------------------------------
    // MENSAJE
    //------------------------------------------------

    const mensajeCorto = truncarTexto(mensaje, 90);

    //------------------------------------------------
    // FECHA
    //------------------------------------------------

    const fechaFormateada = formatearFecha(fecha);

    //------------------------------------------------
    // CLIENTE
    //------------------------------------------------

    const clienteHTML = `

                <div
                    class="
                        d-flex
                        align-items-center
                    ">

                    <div
                        class="
                            bg-primary
                            bg-opacity-10
                            text-primary
                            rounded-circle
                            d-flex
                            align-items-center
                            justify-content-center
                            me-2
                        "
                        style="
                            width:40px;
                            height:40px;
                        ">

                        <i
                            class="
                                bi
                                bi-person-fill
                            ">
                        </i>

                    </div>

                    <div>

                        <div class="fw-semibold">

                            ${escapeHTML(cliente)}

                        </div>

                        ${
                          dni !== ""
                            ? `

                                    <small
                                        class="text-muted">

                                        ${escapeHTML(dni)}

                                    </small>

                                `
                            : ""
                        }

                    </div>

                </div>

            `;

    //------------------------------------------------
    // NOTIFICACIÓN
    //------------------------------------------------

    const notificacionHTML = `

                <div
                    class="
                        d-flex
                        align-items-start
                    ">

                    <div
                        class="
                            bg-${color}
                            bg-opacity-10
                            text-${color}
                            rounded-3
                            p-2
                            me-3
                        ">

                        <i
                            class="
                                bi
                                ${icono}
                            ">
                        </i>

                    </div>

                    <div class="min-w-0">

                        <div class="fw-semibold">

                            ${escapeHTML(titulo)}

                        </div>

                        <small
                            class="text-muted">

                            ${escapeHTML(mensajeCorto)}

                        </small>

                    </div>

                </div>

            `;

    //------------------------------------------------
    // ACCIONES
    //------------------------------------------------

    const accionesHTML = `

                <div
                    class="
                        d-flex
                        justify-content-center
                        gap-1
                    ">

                    <button
                        type="button"
                        class="
                            btn
                            btn-sm
                            btn-outline-primary
                            btn-ver-notificacion
                        "
                        data-id="${id}"
                        title="Ver notificación">

                        <i class="bi bi-eye"></i>

                    </button>

                    <button
                        type="button"
                        class="
                            btn
                            btn-sm
                            btn-outline-secondary
                            btn-editar-notificacion
                        "
                        data-id="${id}"
                        title="Editar notificación">

                        <i class="bi bi-pencil"></i>

                    </button>

                    <button
                        type="button"
                        class="
                            btn
                            btn-sm
                            btn-outline-danger
                            btn-eliminar-notificacion
                        "
                        data-id="${id}"
                        title="Eliminar notificación">

                        <i class="bi bi-trash"></i>

                    </button>

                </div>

            `;

    //------------------------------------------------
    // HTML FILA
    //------------------------------------------------

    const numero =
      (paginaActualNotificaciones - 1) * registrosPorPaginaNotificaciones +
      indice +
      1;

    html += `

                <tr
                    class="${claseFila}"
                    data-id="${id}">

                    <td class="ps-4">

                        <span class="fw-semibold">

                            ${numero}

                        </span>

                    </td>

                    <td>

                        ${clienteHTML}

                    </td>

                    <td>

                        ${notificacionHTML}

                    </td>

                    <td>

                        ${tipoHTML}

                    </td>

                    <td>

                        ${estadoHTML}

                    </td>

                    <td>

                        <small
                            class="text-muted">

                            <i
                                class="
                                    bi
                                    bi-calendar3
                                    me-1
                                ">
                            </i>

                            ${escapeHTML(fechaFormateada)}

                        </small>

                    </td>

                    <td class="text-center">

                        ${accionesHTML}

                    </td>

                </tr>

            `;
  });

  tabla.innerHTML = html;
}

//=====================================================
// MANEJAR ACCIONES
//=====================================================

function manejarAccionesNotificacion(evento) {
  const boton = evento.target.closest("button[data-id]");

  if (!boton) {
    return;
  }

  const id = parseInt(boton.dataset.id || "0", 10);

  if (id <= 0) {
    return;
  }

  if (boton.classList.contains("btn-ver-notificacion")) {
    verNotificacion(id);

    return;
  }

  if (boton.classList.contains("btn-editar-notificacion")) {
    editarNotificacion(id);

    return;
  }

  if (boton.classList.contains("btn-eliminar-notificacion")) {
    eliminarNotificacion(id);

    return;
  }
}

//=====================================================
// VER NOTIFICACIÓN
//=====================================================

function verNotificacion(id) {
  if (!id || id <= 0) {
    return;
  }

  const modalElement = document.getElementById("modalVerNotificacion");

  if (!modalElement) {
    console.warn("No existe #modalVerNotificacion.");

    return;
  }

  const fila = document.querySelector(
    `#tablaNotificaciones tr[data-id="${id}"]`,
  );

  if (!fila) {
    return;
  }

  const cliente =
    fila.querySelector("td:nth-child(2)")?.innerText.trim() || "-";

  const contenido = fila.querySelector("td:nth-child(3)");

  const titulo =
    contenido?.querySelector(".fw-semibold")?.innerText.trim() || "-";

  const mensaje = contenido?.querySelector("small")?.innerText.trim() || "-";

  const tipo = fila.querySelector("td:nth-child(4)")?.innerText.trim() || "-";

  const estado = fila.querySelector("td:nth-child(5)")?.innerText.trim() || "-";

  const fecha = fila.querySelector("td:nth-child(6)")?.innerText.trim() || "-";

  colocarTextoSiExiste(["detalleClienteNotificacion"], cliente);

  colocarTextoSiExiste(["detalleTituloNotificacion"], titulo);

  colocarTextoSiExiste(["detalleMensajeNotificacion"], mensaje);

  colocarTextoSiExiste(["detalleFechaNotificacion"], fecha);

  const elementoTipo = document.getElementById("detalleTipoNotificacion");

  if (elementoTipo) {
    elementoTipo.textContent = tipo;

    elementoTipo.className = "badge bg-primary";
  }

  const elementoEstado = document.getElementById("detalleEstadoNotificacion");

  if (elementoEstado) {
    elementoEstado.textContent = estado;

    if (estado.toLowerCase().includes("no leída")) {
      elementoEstado.className = "badge bg-warning text-dark";
    } else {
      elementoEstado.className = "badge bg-success";
    }
  }

  abrirModalBootstrap(modalElement);
}

//=====================================================
// EDITAR NOTIFICACIÓN
//=====================================================

async function editarNotificacion(id) {
  if (!id || id <= 0) {
    return;
  }

  const modalElement = document.getElementById("modalNotificacion");

  if (!modalElement) {
    console.warn("No existe #modalNotificacion.");

    return;
  }

  limpiarFormularioNotificacion();

  colocarValorSiExiste(["idNotificacion"], id);

  colocarTextoSiExiste(["modalNotificacionLabel"], "Editar notificación");

  try {
    //------------------------------------------------
    // CARGAR CLIENTES
    //------------------------------------------------

    await cargarClientesNotificacion();

    //------------------------------------------------
    // OBTENER NOTIFICACIÓN
    //------------------------------------------------

    const parametros = new URLSearchParams();

    parametros.set("id", id);

    parametros.set("idNotificacion", id);

    parametros.set("_", Date.now());

    const respuesta = await fetch(
      URL_OBTENER_NOTIFICACION + "?" + parametros.toString(),
      {
        method: "GET",

        credentials: "same-origin",

        cache: "no-store",

        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      },
    );

    const datos = await obtenerJSONRespuesta(
      respuesta,
      "obtener_notificacion.php",
    );

    if (!respuesta.ok || !datos || datos.success !== true) {
      throw new Error(datos?.mensaje || "No se pudo obtener la notificación.");
    }

    const notificacion = datos.notificacion || datos.data || {};

    //------------------------------------------------
    // ID
    //------------------------------------------------

    colocarValorSiExiste(
      ["idNotificacion"],
      obtenerNumero(
        notificacion.id_notificacion,
        notificacion.idNotificacion,
        notificacion.id,
        id,
      ),
    );

    //------------------------------------------------
    // CLIENTE
    //------------------------------------------------

    const idCliente = obtenerNumero(
      notificacion.idCliente,
      notificacion.id_cliente,
      notificacion.cliente_id,
      0,
    );

    colocarValorSiExiste(
      ["idClienteNotificacion"],
      idCliente > 0 ? String(idCliente) : "",
    );

    //------------------------------------------------
    // TÍTULO
    //------------------------------------------------

    colocarValorSiExiste(
      ["tituloNotificacion"],
      obtenerTexto(notificacion.titulo, ""),
    );

    //------------------------------------------------
    // MENSAJE
    //------------------------------------------------

    colocarValorSiExiste(
      ["mensajeNotificacion"],
      obtenerTexto(notificacion.mensaje, ""),
    );

    //------------------------------------------------
    // TIPO
    //------------------------------------------------

    colocarValorSiExiste(
      ["tipoNotificacion"],
      obtenerTexto(notificacion.tipo, "SISTEMA"),
    );

    //------------------------------------------------
    // ICONO
    //------------------------------------------------

    colocarValorSiExiste(
      ["iconoNotificacion"],
      obtenerTexto(notificacion.icono, "bi-bell-fill"),
    );

    //------------------------------------------------
    // COLOR
    //------------------------------------------------

    colocarValorSiExiste(
      ["colorNotificacion"],
      obtenerColorBootstrap(notificacion.color),
    );

    //------------------------------------------------
    // URL
    //------------------------------------------------

    colocarValorSiExiste(
      ["urlNotificacion"],
      obtenerTexto(notificacion.url, notificacion.url_notificacion, ""),
    );

    //------------------------------------------------
    // LEÍDO
    //------------------------------------------------

    const checkboxLeido = document.getElementById("notificacionLeida");

    if (checkboxLeido) {
      checkboxLeido.checked = Number(notificacion.leido) === 1;
    }

    //------------------------------------------------
    // ACTUALIZAR ICONO
    //------------------------------------------------

    actualizarVistaIconoNotificacion();

    //------------------------------------------------
    // ABRIR
    //------------------------------------------------

    abrirModalBootstrap(modalElement);
  } catch (error) {
    console.error("Error editando notificación:", error);

    mostrarAlerta("error", "Error", error.message);
  }
}

//=====================================================
// NUEVA NOTIFICACIÓN
//=====================================================

async function abrirModalNuevaNotificacion() {
  const modalElement = document.getElementById("modalNotificacion");

  if (!modalElement) {
    console.warn("No existe #modalNotificacion.");

    return;
  }

  limpiarFormularioNotificacion();

  colocarValorSiExiste(["idNotificacion"], "0");

  colocarTextoSiExiste(["modalNotificacionLabel"], "Nueva notificación");

  colocarValorSiExiste(["tipoNotificacion"], "SISTEMA");

  colocarValorSiExiste(["iconoNotificacion"], "bi-bell-fill");

  colocarValorSiExiste(["colorNotificacion"], "primary");

  try {
    await cargarClientesNotificacion();
  } catch (error) {
    console.error("Error cargando clientes:", error);
  }

  actualizarVistaIconoNotificacion();

  abrirModalBootstrap(modalElement);

  document.dispatchEvent(new CustomEvent("nuevaNotificacion"));
}

//=====================================================
// GUARDAR NOTIFICACIÓN
//=====================================================

async function guardarNotificacion() {
  if (guardandoNotificacion) {
    return;
  }

  const formulario = document.getElementById("formNotificacion");

  if (!formulario) {
    console.error("No existe #formNotificacion.");

    return;
  }

  //---------------------------------------------------
  // VALIDACIÓN
  //---------------------------------------------------

  if (!formulario.checkValidity()) {
    formulario.reportValidity();

    return;
  }

  //---------------------------------------------------
  // DATOS
  //---------------------------------------------------

  const id = obtenerValor("idNotificacion") || "0";

  const idCliente = obtenerValor("idClienteNotificacion");

  const titulo = obtenerValor("tituloNotificacion");

  const mensaje = obtenerValor("mensajeNotificacion");

  const tipo = obtenerValor("tipoNotificacion") || "SISTEMA";

  const icono = obtenerValor("iconoNotificacion") || "bi-bell-fill";

  const color = obtenerColorBootstrap(obtenerValor("colorNotificacion"));

  const url = obtenerValor("urlNotificacion");

  const checkboxLeido = document.getElementById("notificacionLeida");

  const leido = checkboxLeido && checkboxLeido.checked ? "1" : "0";

  //---------------------------------------------------
  // VALIDAR CLIENTE
  //---------------------------------------------------

  if (!idCliente || Number(idCliente) <= 0) {
    mostrarAlerta("warning", "Cliente requerido", "Selecciona un cliente.");

    return;
  }

  //---------------------------------------------------
  // VALIDAR TÍTULO
  //---------------------------------------------------

  if (titulo === "") {
    mostrarAlerta(
      "warning",
      "Título requerido",
      "Ingresa el título de la notificación.",
    );

    return;
  }

  //---------------------------------------------------
  // VALIDAR MENSAJE
  //---------------------------------------------------

  if (mensaje === "") {
    mostrarAlerta(
      "warning",
      "Mensaje requerido",
      "Ingresa el mensaje de la notificación.",
    );

    return;
  }

  //---------------------------------------------------
  // FORM DATA
  //---------------------------------------------------

  const datosFormulario = new FormData();

  datosFormulario.append("idNotificacion", id);

  datosFormulario.append("idCliente", idCliente);

  datosFormulario.append("titulo", titulo);

  datosFormulario.append("mensaje", mensaje);

  datosFormulario.append("tipo", tipo);

  datosFormulario.append("icono", icono);

  datosFormulario.append("color", color);

  datosFormulario.append("url", url);

  datosFormulario.append("leido", leido);

  //---------------------------------------------------
  // BOTÓN
  //---------------------------------------------------

  const boton = document.getElementById("btnGuardarNotificacion");

  const textoOriginal = boton ? boton.innerHTML : "";

  if (boton) {
    boton.disabled = true;

    boton.innerHTML = `

            <span
                class="
                    spinner-border
                    spinner-border-sm
                    me-2
                ">
            </span>

            Guardando...

        `;
  }

  guardandoNotificacion = true;

  try {
    const respuesta = await fetch(URL_GUARDAR_NOTIFICACION, {
      method: "POST",

      body: datosFormulario,

      credentials: "same-origin",

      cache: "no-store",

      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    const datos = await obtenerJSONRespuesta(
      respuesta,
      "registrar_notificacion.php",
    );

    if (!respuesta.ok || !datos || datos.success !== true) {
      throw new Error(datos?.mensaje || "No se pudo guardar la notificación.");
    }

    //------------------------------------------------
    // CERRAR MODAL
    //------------------------------------------------

    cerrarModalBootstrap("modalNotificacion");

    //------------------------------------------------
    // RECARGAR
    //------------------------------------------------

    await cargarNotificaciones(paginaActualNotificaciones);

    await cargarKpisNotificaciones();

    //------------------------------------------------
    // MENSAJE
    //------------------------------------------------

    mostrarAlerta(
      "success",
      "¡Éxito!",
      datos.mensaje || "La notificación se guardó correctamente.",
    );
  } catch (error) {
    console.error("Error guardando notificación:", error);

    mostrarAlerta("error", "Error", error.message);
  } finally {
    guardandoNotificacion = false;

    if (boton) {
      boton.disabled = false;

      boton.innerHTML =
        textoOriginal ||
        `
                    <i
                        class="
                            bi
                            bi-check-circle
                            me-2
                        ">
                    </i>

                    Guardar notificación
                `;
    }
  }
}

//=====================================================
// ELIMINAR NOTIFICACIÓN
//=====================================================

async function eliminarNotificacion(id) {
  if (!id || id <= 0) {
    return;
  }

  let confirmado = false;

  if (typeof Swal !== "undefined") {
    const resultado = await Swal.fire({
      title: "¿Eliminar notificación?",

      text: "La notificación será eliminada.",

      icon: "warning",

      showCancelButton: true,

      confirmButtonText: "Sí, eliminar",

      cancelButtonText: "Cancelar",

      reverseButtons: true,
    });

    confirmado = resultado.isConfirmed;
  } else {
    confirmado = window.confirm("¿Deseas eliminar esta notificación?");
  }

  if (!confirmado) {
    return;
  }

  const datosFormulario = new FormData();

  datosFormulario.append("idNotificacion", id);

  try {
    const respuesta = await fetch(URL_ELIMINAR_NOTIFICACION, {
      method: "POST",

      body: datosFormulario,

      credentials: "same-origin",

      cache: "no-store",

      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    const datos = await obtenerJSONRespuesta(
      respuesta,
      "eliminar_notificacion.php",
    );

    if (!respuesta.ok || !datos || datos.success !== true) {
      throw new Error(datos?.mensaje || "No se pudo eliminar la notificación.");
    }

    //------------------------------------------------
    // RECARGAR
    //------------------------------------------------

    await cargarNotificaciones(paginaActualNotificaciones);

    await cargarKpisNotificaciones();

    mostrarAlerta(
      "success",
      "Notificación eliminada",
      datos.mensaje || "La notificación fue eliminada correctamente.",
    );
  } catch (error) {
    console.error("Error eliminando notificación:", error);

    mostrarAlerta("error", "Error", error.message);
  }
}

//=====================================================
// CARGAR CLIENTES
//=====================================================

async function cargarClientesNotificacion() {
  if (cargandoClientesNotificacion) {
    return;
  }

  const select = document.getElementById("idClienteNotificacion");

  if (!select) {
    return;
  }

  cargandoClientesNotificacion = true;

  const valorActual = select.value;

  select.disabled = true;

  select.innerHTML = `

        <option value="">
            Cargando clientes...
        </option>

    `;

  try {
    const respuesta = await fetch(
      URL_CLIENTES_NOTIFICACION + "?_=" + Date.now(),
      {
        method: "GET",

        credentials: "same-origin",

        cache: "no-store",

        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      },
    );

    const datos = await obtenerJSONRespuesta(
      respuesta,
      "obtener_clientes_notificacion.php",
    );

    //------------------------------------------------
    // VALIDAR
    //------------------------------------------------

    if (!respuesta.ok || !datos || datos.success !== true) {
      throw new Error(datos?.mensaje || "No se pudieron cargar los clientes.");
    }

    //------------------------------------------------
    // OBTENER CLIENTES
    //------------------------------------------------

    let clientes = [];

    if (Array.isArray(datos.clientes)) {
      clientes = datos.clientes;
    } else if (Array.isArray(datos.data)) {
      clientes = datos.data;
    }

    //------------------------------------------------
    // LIMPIAR SELECT
    //------------------------------------------------

    select.innerHTML = "";

    const opcionInicial = document.createElement("option");

    opcionInicial.value = "";

    opcionInicial.textContent =
      clientes.length > 0
        ? "Seleccione un cliente"
        : "No hay clientes disponibles";

    select.appendChild(opcionInicial);

    //------------------------------------------------
    // AGREGAR CLIENTES
    //------------------------------------------------

    clientes.forEach(function (cliente) {
      const id = obtenerNumero(
        cliente.idCliente,
        cliente.id_cliente,
        cliente.id,
        0,
      );

      if (id <= 0) {
        return;
      }

      const nombre = obtenerTexto(
        cliente.nombre_completo,
        cliente.nombre,
        cliente.cliente,
        "Cliente",
      );

      const dni = obtenerTexto(cliente.dni, cliente.dni_o_ruc, "");

      const option = document.createElement("option");

      option.value = String(id);

      option.textContent = dni !== "" ? `${nombre} - ${dni}` : nombre;

      select.appendChild(option);
    });

    //------------------------------------------------
    // RESTAURAR SELECCIÓN
    //------------------------------------------------

    if (valorActual !== "") {
      const existe = Array.from(select.options).some(function (option) {
        return option.value === String(valorActual);
      });

      if (existe) {
        select.value = String(valorActual);
      }
    }
  } catch (error) {
    console.error("Error cargando clientes:", error);

    select.innerHTML = `

            <option value="">
                No se pudieron cargar los clientes
            </option>

        `;

    throw error;
  } finally {
    select.disabled = false;

    cargandoClientesNotificacion = false;
  }
}

//=====================================================
// LIMPIAR FORMULARIO
//=====================================================

function limpiarFormularioNotificacion() {
  const formulario = document.getElementById("formNotificacion");

  if (!formulario) {
    return;
  }

  formulario.reset();

  colocarValorSiExiste(["idNotificacion"], "0");

  colocarValorSiExiste(["tipoNotificacion"], "SISTEMA");

  colocarValorSiExiste(["iconoNotificacion"], "bi-bell-fill");

  colocarValorSiExiste(["colorNotificacion"], "primary");

  const leido = document.getElementById("notificacionLeida");

  if (leido) {
    leido.checked = false;
  }

  actualizarVistaIconoNotificacion();
}

//=====================================================
// MODAL NOTIFICACIÓN
//=====================================================

function inicializarModalNotificacion() {
  const modal = document.getElementById("modalNotificacion");

  if (!modal) {
    return;
  }

  modal.addEventListener("hidden.bs.modal", function () {
    limpiarFormularioNotificacion();
  });
}

//=====================================================
// MODAL VER
//=====================================================

function inicializarModalVerNotificacion() {
  const modal = document.getElementById("modalVerNotificacion");

  if (!modal) {
    return;
  }

  modal.addEventListener("hidden.bs.modal", function () {
    colocarTextoSiExiste(
      [
        "detalleClienteNotificacion",
        "detalleTituloNotificacion",
        "detalleMensajeNotificacion",
        "detalleFechaNotificacion",
      ],
      "-",
    );

    colocarTextoSiExiste(
      ["detalleTipoNotificacion", "detalleEstadoNotificacion"],
      "-",
    );
  });
}

//=====================================================
// ICONO
//=====================================================

function inicializarVistaIconoNotificacion() {
  const input = document.getElementById("iconoNotificacion");

  if (!input) {
    return;
  }

  input.addEventListener("input", actualizarVistaIconoNotificacion);

  input.addEventListener("change", actualizarVistaIconoNotificacion);

  actualizarVistaIconoNotificacion();
}

//=====================================================
// ACTUALIZAR VISTA ICONO
//=====================================================

function actualizarVistaIconoNotificacion() {
  const input = document.getElementById("iconoNotificacion");

  const vista = document.getElementById("vistaIconoNotificacion");

  if (!input || !vista) {
    return;
  }

  const icono = obtenerIconoSeguro(input.value);

  vista.className = "bi " + icono;
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosNotificaciones() {
  const buscar = document.getElementById("buscarNotificacion");

  const tipo = document.getElementById("filtroTipoNotificacion");

  const estado = document.getElementById("filtroEstadoNotificacion");

  const fecha = document.getElementById("filtroFechaNotificacion");

  if (buscar) {
    buscar.value = "";
  }

  if (tipo) {
    tipo.value = "";
  }

  if (estado) {
    estado.value = "";
  }

  if (fecha) {
    fecha.value = "";
  }

  paginaActualNotificaciones = 1;

  cargarNotificaciones(1);
}

//=====================================================
// PAGINACIÓN
//=====================================================

function renderizarPaginacionNotificaciones(pagina, totalPaginas) {
  const contenedor = document.getElementById("paginacionNotificaciones");

  if (!contenedor) {
    return;
  }

  pagina = parseInt(pagina, 10) || 1;

  totalPaginas = parseInt(totalPaginas, 10) || 1;

  totalPaginas = Math.max(1, totalPaginas);

  pagina = Math.max(1, Math.min(pagina, totalPaginas));

  let html = "";

  //---------------------------------------------------
  // ANTERIOR
  //---------------------------------------------------

  html += `

        <li
            class="
                page-item
                ${pagina <= 1 ? "disabled" : ""}
            ">

            <button
                type="button"
                class="page-link"
                data-pagina="${pagina - 1}"
                ${pagina <= 1 ? "disabled" : ""}
                aria-label="Anterior">

                <i
                    class="
                        bi
                        bi-chevron-left
                    ">
                </i>

            </button>

        </li>

    `;

  //---------------------------------------------------
  // NÚMEROS
  //---------------------------------------------------

  const paginas = generarRangoPaginas(pagina, totalPaginas);

  paginas.forEach(function (numero) {
    if (numero === "...") {
      html += `

                    <li
                        class="
                            page-item
                            disabled
                        ">

                        <span
                            class="page-link">

                            ...

                        </span>

                    </li>

                `;

      return;
    }

    html += `

                <li
                    class="
                        page-item
                        ${Number(numero) === pagina ? "active" : ""}
                    ">

                    <button
                        type="button"
                        class="page-link"
                        data-pagina="${numero}">

                        ${numero}

                    </button>

                </li>

            `;
  });

  //---------------------------------------------------
  // SIGUIENTE
  //---------------------------------------------------

  html += `

        <li
            class="
                page-item
                ${pagina >= totalPaginas ? "disabled" : ""}
            ">

            <button
                type="button"
                class="page-link"
                data-pagina="${pagina + 1}"
                ${pagina >= totalPaginas ? "disabled" : ""}
                aria-label="Siguiente">

                <i
                    class="
                        bi
                        bi-chevron-right
                    ">
                </i>

            </button>

        </li>

    `;

  contenedor.innerHTML = html;

  //---------------------------------------------------
  // EVENTOS
  //---------------------------------------------------

  contenedor.querySelectorAll("button[data-pagina]").forEach(function (boton) {
    boton.addEventListener("click", function () {
      const nuevaPagina = parseInt(boton.dataset.pagina, 10);

      if (
        !nuevaPagina ||
        nuevaPagina < 1 ||
        nuevaPagina > totalPaginas ||
        nuevaPagina === paginaActualNotificaciones
      ) {
        return;
      }

      cargarNotificaciones(nuevaPagina);
    });
  });
}

//=====================================================
// RANGO DE PÁGINAS
//=====================================================

function generarRangoPaginas(paginaActual, totalPaginas) {
  if (totalPaginas <= 7) {
    return Array.from(
      {
        length: totalPaginas,
      },
      function (_, indice) {
        return indice + 1;
      },
    );
  }

  const paginas = [];

  paginas.push(1);

  if (paginaActual > 4) {
    paginas.push("...");
  }

  const inicio = Math.max(2, paginaActual - 1);

  const fin = Math.min(totalPaginas - 1, paginaActual + 1);

  for (let i = inicio; i <= fin; i++) {
    paginas.push(i);
  }

  if (paginaActual < totalPaginas - 3) {
    paginas.push("...");
  }

  paginas.push(totalPaginas);

  return paginas;
}

//=====================================================
// INFO PAGINACIÓN
//=====================================================

function actualizarInfoPaginacionNotificaciones(desde, hasta, total) {
  const elemento = document.getElementById("infoPaginacionNotificaciones");

  if (!elemento) {
    return;
  }

  desde = parseInt(desde, 10) || 0;

  hasta = parseInt(hasta, 10) || 0;

  total = parseInt(total, 10) || 0;

  if (total === 0) {
    elemento.textContent = "Mostrando 0 de 0 notificaciones";

    return;
  }

  elemento.textContent =
    `Mostrando ${formatearNumero(desde)} - ` +
    `${formatearNumero(hasta)} de ` +
    `${formatearNumero(total)} notificaciones`;
}

//=====================================================
// TABLA LOADING
//=====================================================

function mostrarCargandoTablaNotificaciones(tabla) {
  if (!tabla) {
    return;
  }

  tabla.innerHTML = `

        <tr>

            <td
                colspan="7"
                class="text-center py-5">

                <div
                    class="
                        spinner-border
                        text-primary
                        mb-3
                    "
                    role="status">

                    <span
                        class="visually-hidden">

                        Cargando...

                    </span>

                </div>

                <div class="text-muted">

                    Cargando notificaciones...

                </div>

            </td>

        </tr>

    `;
}

//=====================================================
// TABLA ERROR
//=====================================================

function mostrarErrorTablaNotificaciones(tabla, mensaje) {
  if (!tabla) {
    return;
  }

  tabla.innerHTML = `

        <tr>

            <td
                colspan="7"
                class="text-center py-5">

                <div class="text-danger">

                    <i
                        class="
                            bi
                            bi-exclamation-triangle
                            fs-1
                            d-block
                            mb-3
                        ">
                    </i>

                    <h6 class="fw-semibold">

                        No se pudieron cargar
                        las notificaciones

                    </h6>

                    <p class="small mb-3">

                        ${escapeHTML(
                          mensaje ||
                            "Ocurrió un error al consultar el servidor.",
                        )}

                    </p>

                    <button
                        type="button"
                        class="
                            btn
                            btn-outline-primary
                            btn-sm
                        "
                        onclick="
                            cargarNotificaciones(
                                ${paginaActualNotificaciones}
                            )
                        ">

                        <i
                            class="
                                bi
                                bi-arrow-clockwise
                                me-1
                            ">
                        </i>

                        Reintentar

                    </button>

                </div>

            </td>

        </tr>

    `;
}

//=====================================================
// OBTENER JSON
//=====================================================

async function obtenerJSONRespuesta(respuesta, nombreArchivo) {
  const texto = await respuesta.text();

  if (!texto || texto.trim() === "") {
    throw new Error(
      `El archivo ${nombreArchivo} no devolvió ninguna respuesta.`,
    );
  }

  try {
    return JSON.parse(texto);
  } catch (error) {
    console.error(`JSON inválido de ${nombreArchivo}:`, texto);

    throw new Error(
      `El servidor no devolvió un JSON válido. Revisa ${nombreArchivo}.`,
    );
  }
}

//=====================================================
// ABRIR MODAL BOOTSTRAP
//=====================================================

function abrirModalBootstrap(elemento) {
  if (!elemento) {
    return;
  }

  if (typeof bootstrap === "undefined") {
    console.error("Bootstrap no está disponible.");

    return;
  }

  const instancia = bootstrap.Modal.getOrCreateInstance(elemento);

  instancia.show();
}

//=====================================================
// CERRAR MODAL BOOTSTRAP
//=====================================================

function cerrarModalBootstrap(idModal) {
  const elemento = document.getElementById(idModal);

  if (!elemento || typeof bootstrap === "undefined") {
    return;
  }

  const instancia = bootstrap.Modal.getInstance(elemento);

  if (instancia) {
    instancia.hide();
  }
}

//=====================================================
// COLOCAR TEXTO
//=====================================================

function colocarTextoSiExiste(ids, valor) {
  if (!Array.isArray(ids)) {
    return;
  }

  ids.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (elemento) {
      elemento.textContent = valor ?? "";
    }
  });
}

//=====================================================
// COLOCAR VALOR
//=====================================================

function colocarValorSiExiste(ids, valor) {
  if (!Array.isArray(ids)) {
    return;
  }

  ids.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (elemento) {
      elemento.value = valor ?? "";
    }
  });
}

//=====================================================
// OBTENER VALOR
//=====================================================

function obtenerValor(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return "";
  }

  return String(elemento.value ?? "").trim();
}

//=====================================================
// OBTENER TEXTO
//=====================================================

function obtenerTexto(...valores) {
  for (const valor of valores) {
    if (valor !== undefined && valor !== null && String(valor).trim() !== "") {
      return String(valor).trim();
    }
  }

  return "";
}

//=====================================================
// OBTENER NÚMERO
//=====================================================

function obtenerNumero(...valores) {
  if (valores.length === 0) {
    return 0;
  }

  for (let i = 0; i < valores.length; i++) {
    const valor = Number(valores[i]);

    if (!Number.isNaN(valor) && Number.isFinite(valor)) {
      return valor;
    }
  }

  return 0;
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(numero) {
  numero = Number(numero) || 0;

  return numero.toLocaleString("es-PE");
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "";
  }

  const texto = String(fecha).trim();

  if (texto === "") {
    return "";
  }

  //---------------------------------------------------
  // DATETIME MYSQL
  //---------------------------------------------------

  const coincidencia = texto.match(
    /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/,
  );

  if (coincidencia) {
    const anio = coincidencia[1];

    const mes = coincidencia[2];

    const dia = coincidencia[3];

    let resultado = `${dia}/${mes}/${anio}`;

    if (coincidencia[4] && coincidencia[5]) {
      resultado += ` ${coincidencia[4]}:${coincidencia[5]}`;
    }

    return resultado;
  }

  return texto;
}

//=====================================================
// TRUNCAR TEXTO
//=====================================================

function truncarTexto(texto, maximo) {
  texto = String(texto ?? "").trim();

  maximo = Number(maximo) || 90;

  if (texto.length <= maximo) {
    return texto;
  }

  return texto.substring(0, maximo).trim() + "...";
}

//=====================================================
// COLOR BOOTSTRAP
//=====================================================

function obtenerColorBootstrap(color) {
  const coloresPermitidos = [
    "primary",
    "secondary",
    "success",
    "danger",
    "warning",
    "info",
    "light",
    "dark",
  ];

  color = String(color ?? "")
    .trim()
    .toLowerCase();

  if (coloresPermitidos.includes(color)) {
    return color;
  }

  return "primary";
}

//=====================================================
// ICONO SEGURO
//=====================================================

function obtenerIconoSeguro(icono) {
  icono = String(icono ?? "").trim();

  if (icono === "" || !icono.startsWith("bi-")) {
    return "bi-bell-fill";
  }

  icono = icono.replace(/[^a-zA-Z0-9\-_]/g, "");

  if (!icono.startsWith("bi-")) {
    return "bi-bell-fill";
  }

  return icono;
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escapeHTML(valor) {
  return String(valor ?? "").replace(/[&<>"']/g, function (caracter) {
    const entidades = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };

    return entidades[caracter];
  });
}

//=====================================================
// ALERTAS
//=====================================================

function mostrarAlerta(icono, titulo, texto) {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: icono,

      title: titulo,

      text: texto,

      confirmButtonText: "Aceptar",
    });

    return;
  }

  window.alert(titulo + "\n\n" + texto);
}

//=====================================================
// EXPONER FUNCIONES GLOBALES
//=====================================================

window.cargarNotificaciones = cargarNotificaciones;

window.cargarKpisNotificaciones = cargarKpisNotificaciones;

window.limpiarFiltrosNotificaciones = limpiarFiltrosNotificaciones;

window.verNotificacion = verNotificacion;

window.editarNotificacion = editarNotificacion;

window.eliminarNotificacion = eliminarNotificacion;

window.abrirModalNuevaNotificacion = abrirModalNuevaNotificacion;

window.guardarNotificacion = guardarNotificacion;

//=====================================================
// FIN
//=====================================================
