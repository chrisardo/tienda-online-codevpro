//=====================================================
// CoDevPro Technology
// Archivo: js/adm_lista_pagos.js
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActualPagos = 1;

let registrosPorPaginaPagos = 10;

let temporizadorBusquedaPago = null;

let solicitudPagosActual = null;

let modalRegistrarPago = null;

let modalEditarPagoEmpleado = null;

let flatpickrPeriodoInicio = null;

let flatpickrPeriodoFin = null;

let flatpickrFechaPago = null;

let flatpickrFiltroFechaInicioPago = null;

let flatpickrFiltroFechaFinPago = null;

// Evita ejecutar dos veces la acción de marcar como pagado
let pagosActualizando = new Set();

//=====================================================
// RUTAS AJAX
//=====================================================

const URL_LISTAR_PAGOS = "ajax/listar_pagos_empleado.php";

const URL_KPI_PAGOS = "ajax/obtener_kpi_pagos_empleado.php";

const URL_REGISTRAR_PAGO = "ajax/registrar_pago_empleado.php";

const URL_ACTUALIZAR_ESTADO_PAGO = "ajax/actualizar_estado_pago_empleado.php";

const URL_OBTENER_PAGO = "ajax/obtener_pago_empleado.php";

const URL_ACTUALIZAR_PAGO = "ajax/actualizar_pago_empleado.php";

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarModuloPagos();
});

//=====================================================
// INICIALIZAR MÓDULO
//=====================================================

function inicializarModuloPagos() {
  //=================================================
  // MODAL REGISTRAR
  //=================================================

  const elementoModal = document.getElementById("modalRegistrarPago");

  if (elementoModal && typeof bootstrap !== "undefined") {
    modalRegistrarPago = new bootstrap.Modal(elementoModal);
  }

  //=================================================
  // MODAL EDITAR
  //=================================================

  const elementoModalEditar = document.getElementById(
    "modalEditarPagoEmpleado",
  );

  if (elementoModalEditar && typeof bootstrap !== "undefined") {
    modalEditarPagoEmpleado = new bootstrap.Modal(elementoModalEditar);
  }

  //=================================================
  // FECHAS
  //=================================================

  inicializarFechas();

  //=================================================
  // EVENTOS
  //=================================================

  configurarEventos();

  //=================================================
  // DATOS INICIALES
  //=================================================

  cargarKPI();

  cargarPagos();
}

//=====================================================
// INICIALIZAR FLATPICKR
//=====================================================

function inicializarFechas() {
  if (typeof flatpickr === "undefined") {
    console.warn("Flatpickr no está disponible.");

    return;
  }

  //=================================================
  // FILTRO FECHA INICIO
  //=================================================

  const inputFechaInicioPago = document.getElementById("filtroFechaInicioPago");

  if (inputFechaInicioPago) {
    flatpickrFiltroFechaInicioPago = flatpickr(inputFechaInicioPago, {
      dateFormat: "Y-m-d",

      locale: "es",

      allowInput: true,

      onChange: function () {
        paginaActualPagos = 1;

        cargarPagos();
      },
    });
  }

  //=================================================
  // FILTRO FECHA FIN
  //=================================================

  const inputFechaFinPago = document.getElementById("filtroFechaFinPago");

  if (inputFechaFinPago) {
    flatpickrFiltroFechaFinPago = flatpickr(inputFechaFinPago, {
      dateFormat: "Y-m-d",

      locale: "es",

      allowInput: true,

      onChange: function () {
        paginaActualPagos = 1;

        cargarPagos();
      },
    });
  }

  //=================================================
  // PERIODO INICIO
  //=================================================

  const inputInicio = document.getElementById("pagoPeriodoInicio");

  if (inputInicio) {
    flatpickrPeriodoInicio = flatpickr(inputInicio, {
      dateFormat: "Y-m-d",

      locale: "es",

      allowInput: true,

      onChange: function () {
        validarPeriodo();
      },
    });
  }

  //=================================================
  // PERIODO FIN
  //=================================================

  const inputFin = document.getElementById("pagoPeriodoFin");

  if (inputFin) {
    flatpickrPeriodoFin = flatpickr(inputFin, {
      dateFormat: "Y-m-d",

      locale: "es",

      allowInput: true,

      onChange: function () {
        validarPeriodo();
      },
    });
  }

  //=================================================
  // FECHA DE PAGO
  //=================================================

  const inputFechaPago = document.getElementById("fechaPago");

  if (inputFechaPago) {
    flatpickrFechaPago = flatpickr(inputFechaPago, {
      dateFormat: "Y-m-d",

      locale: "es",

      allowInput: true,

      defaultDate: new Date(),
    });
  }
}

//=====================================================
// CONFIGURAR EVENTOS
//=====================================================

function configurarEventos() {
  //=================================================
  // NUEVO PAGO
  //=================================================

  const btnNuevoPago = document.getElementById("btnNuevoPago");

  if (btnNuevoPago) {
    btnNuevoPago.addEventListener("click", abrirModalRegistrarPago);
  }

  //=================================================
  // FORMULARIO EDITAR
  //=================================================

  const formularioEditar = document.getElementById("formEditarPagoEmpleado");

  if (formularioEditar) {
    formularioEditar.addEventListener("submit", actualizarPagoEmpleado);
  }

  //=================================================
  // FORMULARIO REGISTRAR
  //=================================================

  const formulario = document.getElementById("formRegistrarPago");

  if (formulario) {
    formulario.addEventListener("submit", registrarPago);
  }

  //=================================================
  // BUSCADOR
  //=================================================

  const buscar = document.getElementById("buscarPagoEmpleado");

  if (buscar) {
    buscar.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaPago);

      temporizadorBusquedaPago = setTimeout(function () {
        paginaActualPagos = 1;

        cargarPagos();
      }, 300);
    });
  }

  //=================================================
  // ESTADO
  //=================================================

  const estado = document.getElementById("filtroEstadoPago");

  if (estado) {
    estado.addEventListener("change", function () {
      paginaActualPagos = 1;

      cargarPagos();
    });
  }

  //=================================================
  // FECHA INICIO
  //=================================================

  const fechaInicio = document.getElementById("filtroFechaInicioPago");

  if (fechaInicio) {
    fechaInicio.addEventListener("change", function () {
      paginaActualPagos = 1;

      cargarPagos();
    });
  }

  //=================================================
  // FECHA FIN
  //=================================================

  const fechaFin = document.getElementById("filtroFechaFinPago");

  if (fechaFin) {
    fechaFin.addEventListener("change", function () {
      paginaActualPagos = 1;

      cargarPagos();
    });
  }

  //=================================================
  // RESTABLECER FILTROS
  //=================================================

  const btnRestablecer = document.getElementById("btnRestablecerFiltrosPagos");

  if (btnRestablecer) {
    btnRestablecer.addEventListener("click", limpiarFiltrosPagos);
  }

  //=================================================
  // EMPLEADO
  //=================================================

  const empleado = document.getElementById("pagoEmpleado");

  if (empleado) {
    empleado.addEventListener("change", cargarSueldoEmpleado);
  }

  //=================================================
  // BONIFICACIONES
  //=================================================

  const bonificaciones = document.getElementById("bonificaciones");

  if (bonificaciones) {
    bonificaciones.addEventListener("input", calcularMontoTotal);
  }

  //=================================================
  // DESCUENTOS
  //=================================================

  const descuentos = document.getElementById("descuentos");

  if (descuentos) {
    descuentos.addEventListener("input", calcularMontoTotal);
  }

  //=================================================
  // CAMPOS EDITAR
  //=================================================

  const camposEditar = [
    "editar_monto_base",
    "editar_bonificaciones",
    "editar_descuentos",
  ];

  camposEditar.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (elemento) {
      elemento.addEventListener("input", calcularTotalPagoEditar);
    }
  });
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosPagos() {
  if (temporizadorBusquedaPago) {
    clearTimeout(temporizadorBusquedaPago);

    temporizadorBusquedaPago = null;
  }

  if (solicitudPagosActual) {
    solicitudPagosActual.abort();

    solicitudPagosActual = null;
  }

  const buscar = document.getElementById("buscarPagoEmpleado");

  if (buscar) {
    buscar.value = "";
  }

  const estado = document.getElementById("filtroEstadoPago");

  if (estado) {
    estado.value = "";
  }

  if (flatpickrFiltroFechaInicioPago) {
    flatpickrFiltroFechaInicioPago.clear();
  } else {
    const fechaInicio = document.getElementById("filtroFechaInicioPago");

    if (fechaInicio) {
      fechaInicio.value = "";
    }
  }

  if (flatpickrFiltroFechaFinPago) {
    flatpickrFiltroFechaFinPago.clear();
  } else {
    const fechaFin = document.getElementById("filtroFechaFinPago");

    if (fechaFin) {
      fechaFin.value = "";
    }
  }

  paginaActualPagos = 1;

  cargarKPI();

  cargarPagos();
}

//=====================================================
// CARGAR KPI
//=====================================================

async function cargarKPI() {
  try {
    const respuesta = await fetch(URL_KPI_PAGOS, {
      method: "GET",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const texto = await respuesta.text();

    console.log("Respuesta AJAX KPI:", texto);

    let data;

    try {
      data = JSON.parse(texto);
    } catch (error) {
      throw new Error("El servidor devolvió una respuesta inválida.");
    }

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible obtener los KPI.");
    }

    const kpi = data.datos || {};

    const totalPagado = Number(kpi.total_pagado || 0);

    actualizarElemento("kpiTotalPagado", formatearMoneda(totalPagado));

    const totalPendiente = Number(kpi.total_pendiente || 0);

    actualizarElemento("kpiPendiente", formatearMoneda(totalPendiente));

    const totalMesActual = Number(kpi.total_mes_actual || 0);

    actualizarElemento("kpiMesActual", formatearMoneda(totalMesActual));
  } catch (error) {
    console.error("Error al cargar KPI:", error);

    actualizarElemento("kpiTotalPagado", formatearMoneda(0));

    actualizarElemento("kpiPendiente", formatearMoneda(0));

    actualizarElemento("kpiMesActual", formatearMoneda(0));
  }
}

//=====================================================
// ACTUALIZAR ELEMENTO
//=====================================================

function actualizarElemento(id, valor) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  elemento.textContent = valor;
}

//=====================================================
// CARGAR PAGOS
//=====================================================

async function cargarPagos() {
  if (solicitudPagosActual) {
    solicitudPagosActual.abort();
  }

  solicitudPagosActual = new AbortController();

  const parametros = new URLSearchParams();

  parametros.append("pagina", paginaActualPagos);

  parametros.append("registros", registrosPorPaginaPagos);

  const buscar = document.getElementById("buscarPagoEmpleado");

  if (buscar) {
    parametros.append("buscar", buscar.value.trim());
  }

  const estado = document.getElementById("filtroEstadoPago");

  if (estado) {
    parametros.append("estado", estado.value);
  }

  const fechaInicio = document.getElementById("filtroFechaInicioPago");

  if (fechaInicio) {
    parametros.append("fecha_inicio", fechaInicio.value);
  }

  const fechaFin = document.getElementById("filtroFechaFinPago");

  if (fechaFin) {
    parametros.append("fecha_fin", fechaFin.value);
  }

  const tabla = document.getElementById("tablaPagos");

  if (tabla) {
    tabla.innerHTML = `
      <tr>
        <td
          colspan="9"
          class="text-center py-5">

          <div
            class="spinner-border text-primary"
            role="status">
          </div>

          <div class="mt-2 text-muted">
            Cargando pagos...
          </div>

        </td>
      </tr>
    `;
  }

  try {
    const respuesta = await fetch(
      URL_LISTAR_PAGOS + "?" + parametros.toString(),
      {
        method: "GET",

        cache: "no-store",

        signal: solicitudPagosActual.signal,

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    console.log("Respuesta listar pagos:", data);

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible cargar los pagos.");
    }

    //=================================================
    // OBTENER ARRAY DE PAGOS
    //=================================================

    const pagos = data.datos?.pagos || data.pagos || null;

    //=================================================
    // IMPORTANTE:
    // SI EXISTE ARRAY, USAMOS renderizarPagos()
    //
    // Esto garantiza que el botón Editar sea
    // generado por este JS.
    //=================================================

    if (Array.isArray(pagos)) {
      renderizarPagos(pagos);
    } else if (data.datos && Array.isArray(data.datos.registros)) {
      renderizarPagos(data.datos.registros);
    } else if (Array.isArray(data.registros)) {
      renderizarPagos(data.registros);
    } else if (tabla && data.tabla !== undefined) {
      // Compatibilidad con el PHP actual
      tabla.innerHTML = data.tabla;
    } else if (tabla) {
      renderizarPagos([]);
    }

    //=================================================
    // TOTAL REGISTROS
    //=================================================

    const totalRegistros = Number(
      data.total_registros ??
        data.datos?.total_registros ??
        data.pagination?.total_registros ??
        0,
    );

    renderizarPaginacion(totalRegistros);

    actualizarInfoPaginacion(totalRegistros);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar pagos:", error);

    mostrarErrorTabla(error.message || "Ocurrió un error al cargar los pagos.");
  }
}

//=====================================================
// RENDERIZAR PAGOS
//=====================================================

function renderizarPagos(pagos) {
  const tabla = document.getElementById("tablaPagos");

  if (!tabla) {
    return;
  }

  if (!Array.isArray(pagos) || pagos.length === 0) {
    tabla.innerHTML = `
      <tr>
        <td
          colspan="9"
          class="text-center py-5">

          <i
            class="bi bi-wallet2 fs-1 text-muted">
          </i>

          <div class="mt-2 text-muted">
            No se encontraron pagos.
          </div>

        </td>
      </tr>
    `;

    return;
  }

  let html = "";

  pagos.forEach(function (pago) {
    const estado = String(pago.estado || "")
      .trim()
      .toUpperCase();

    const idPago = Number(pago.id_pago || pago.idPago || 0);

    const empleado =
      pago.empleado ||
      ((pago.nombre || "") + " " + (pago.apellido || "")).trim();

    //=================================================
    // BOTÓN EDITAR
    //=================================================

    let botonEditar = "";

    if (estado === "PENDIENTE" && idPago > 0) {
      botonEditar = `
          <button
            type="button"
            class="btn btn-sm btn-outline-warning"
            title="Editar pago"
            aria-label="Editar pago"
            onclick="editarPago(${idPago})">

            <i
              class="bi bi-pencil-square">
            </i>

          </button>
        `;
    }

    //=================================================
    // BOTÓN PAGAR
    //=================================================

    let botonPagado = "";

    if (estado === "PENDIENTE" && idPago > 0) {
      botonPagado = `
          <button
            type="button"
            class="btn btn-sm btn-outline-success btn-marcar-pagado"
            title="Marcar como pagado"
            aria-label="Marcar como pagado"
            data-id-pago="${idPago}"
            onclick="marcarPagoPagado(${idPago})">

            <i
              class="bi bi-check-circle">
            </i>

          </button>
        `;
    }

    //=================================================
    // FILA
    //=================================================

    html += `
        <tr>

          <!--=========================================
              EMPLEADO
          ==========================================-->

          <td>

            <div class="fw-semibold">
              ${escapeHTML(empleado)}
            </div>

            ${
              pago.dni
                ? `
                  <small
                    class="text-muted">

                    DNI:
                    ${escapeHTML(pago.dni)}

                  </small>
                `
                : ""
            }

          </td>

          <!--=========================================
              PERIODO
          ==========================================-->

          <td>

            <small>

              ${formatearFecha(pago.periodo_inicio)}

              <br>

              ${formatearFecha(pago.periodo_fin)}

            </small>

          </td>

          <!--=========================================
              MONTO BASE
          ==========================================-->

          <td>
            ${formatearMoneda(pago.monto_base)}
          </td>

          <!--=========================================
              BONIFICACIONES
          ==========================================-->

          <td
            class="text-success">

            +
            ${formatearMoneda(pago.bonificaciones)}

          </td>

          <!--=========================================
              DESCUENTOS
          ==========================================-->

          <td
            class="text-danger">

            -
            ${formatearMoneda(pago.descuentos)}

          </td>

          <!--=========================================
              TOTAL
          ==========================================-->

          <td
            class="fw-bold">

            ${formatearMoneda(pago.monto_total)}

          </td>

          <!--=========================================
              FECHA PAGO
          ==========================================-->

          <td>

            ${formatearFecha(pago.fecha_pago)}

          </td>

          <!--=========================================
              ESTADO
          ==========================================-->

          <td>

            ${obtenerBadgeEstado(estado)}

          </td>

          <!--=========================================
              ACCIONES
          ==========================================-->

          <td
            class="text-center">

            <div
              class="btn-group"
              role="group">

              <!-- VER -->

              <button
                type="button"
                class="btn btn-sm btn-outline-primary"
                title="Ver pago"
                aria-label="Ver pago"
                onclick="verPago(${idPago})">

                <i
                  class="bi bi-eye">
                </i>

              </button>

              ${botonEditar}

              ${botonPagado}

            </div>

          </td>

        </tr>
      `;
  });

  tabla.innerHTML = html;
}

//=====================================================
// EDITAR PAGO
//=====================================================

async function editarPago(idPago) {
  idPago = Number(idPago);

  if (!Number.isInteger(idPago) || idPago <= 0) {
    mostrarToast("El identificador del pago no es válido.", "error");

    return;
  }

  const formulario = document.getElementById("formEditarPagoEmpleado");

  if (!formulario) {
    mostrarToast("No se encontró el formulario de edición.", "error");

    return;
  }

  const modalElemento = document.getElementById("modalEditarPagoEmpleado");

  //=================================================
  // CARGANDO
  //=================================================

  Swal.fire({
    title: "Cargando pago...",

    html: `
      <div class="mt-3">

        <div
          class="spinner-border text-primary"
          role="status">
        </div>

        <p
          class="text-muted mt-3 mb-0">

          Obteniendo información
          del pago...

        </p>

      </div>
    `,

    allowOutsideClick: false,

    allowEscapeKey: false,

    showConfirmButton: false,
  });

  try {
    //=================================================
    // OBTENER PAGO
    //=================================================

    const parametros = new URLSearchParams();

    parametros.append("id_pago", String(idPago));

    const respuesta = await fetch(
      URL_OBTENER_PAGO + "?" + parametros.toString(),
      {
        method: "GET",

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    console.log("Respuesta obtener pago:", data);

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible obtener el pago.");
    }

    const pago = data.datos?.pago || data.pago;

    if (!pago) {
      throw new Error("No se encontró información del pago.");
    }

    //=================================================
    // ESTADO
    //=================================================

    const estado = String(pago.estado || "")
      .trim()
      .toUpperCase();

    //=================================================
    // NO EDITAR PAGADO
    //=================================================

    if (estado === "PAGADO") {
      Swal.close();

      await Swal.fire({
        icon: "warning",

        title: "Pago ya realizado",

        text: "Los pagos que ya fueron marcados como pagados no pueden modificarse.",

        confirmButtonText: "Aceptar",
      });

      return;
    }

    //=================================================
    // NO EDITAR ANULADO
    //=================================================

    if (estado === "ANULADO") {
      Swal.close();

      await Swal.fire({
        icon: "warning",

        title: "Pago anulado",

        text: "Los pagos anulados no pueden modificarse.",

        confirmButtonText: "Aceptar",
      });

      return;
    }

    //=================================================
    // CARGAR CUENTAS Y MÉTODOS
    //=================================================

    await Promise.all([
      cargarCuentasBancariasEditar(),

      cargarMetodosPagoEditar(),
    ]);

    //=================================================
    // LIMPIAR FORMULARIO
    //=================================================

    formulario.reset();

    //=================================================
    // ID
    //=================================================

    const inputId = document.getElementById("editar_id_pago");

    if (inputId) {
      inputId.value = pago.id_pago || idPago;
    }

    //=================================================
    // EMPLEADO
    //=================================================

    const empleado = document.getElementById("editar_empleado");

    if (empleado) {
      empleado.value =
        pago.empleado ||
        ((pago.nombre || "") + " " + (pago.apellido || "")).trim();
    }

    //=================================================
    // ESTADO
    //=================================================

    const estadoSelect = document.getElementById("editar_estado");

    if (estadoSelect) {
      estadoSelect.value = estado || "PENDIENTE";
    }

    //=================================================
    // PERIODO INICIO
    //=================================================

    const periodoInicio = document.getElementById("editar_periodo_inicio");

    if (periodoInicio) {
      periodoInicio.value = pago.periodo_inicio || "";
    }

    //=================================================
    // PERIODO FIN
    //=================================================

    const periodoFin = document.getElementById("editar_periodo_fin");

    if (periodoFin) {
      periodoFin.value = pago.periodo_fin || "";
    }

    //=================================================
    // MONTO BASE
    //=================================================

    const montoBase = document.getElementById("editar_monto_base");

    if (montoBase) {
      montoBase.value = Number(pago.monto_base || 0).toFixed(2);
    }

    //=================================================
    // BONIFICACIONES
    //=================================================

    const bonificaciones = document.getElementById("editar_bonificaciones");

    if (bonificaciones) {
      bonificaciones.value = Number(pago.bonificaciones || 0).toFixed(2);
    }

    //=================================================
    // DESCUENTOS
    //=================================================

    const descuentos = document.getElementById("editar_descuentos");

    if (descuentos) {
      descuentos.value = Number(pago.descuentos || 0).toFixed(2);
    }

    //=================================================
    // TOTAL
    //=================================================

    calcularTotalPagoEditar();

    //=================================================
    // FECHA
    //=================================================

    const fechaPago = document.getElementById("editar_fecha_pago");

    if (fechaPago) {
      fechaPago.value = pago.fecha_pago || "";
    }

    //=================================================
    // CUENTA
    //=================================================

    const cuenta = document.getElementById("editar_id_cuenta_bancaria");

    if (cuenta) {
      cuenta.value = pago.id_cuenta_bancaria || "";
    }

    //=================================================
    // MÉTODO
    //=================================================

    const metodo = document.getElementById("editar_id_metodo_pago");

    if (metodo) {
      metodo.value = pago.id_metodo_pago || "";
    }

    //=================================================
    // OBSERVACIÓN
    //=================================================

    const observacion = document.getElementById("editar_observacion");

    if (observacion) {
      observacion.value = pago.observacion || "";
    }

    //=================================================
    // CERRAR CARGANDO
    //=================================================

    Swal.close();

    //=================================================
    // CREAR MODAL
    //=================================================

    if (
      !modalEditarPagoEmpleado &&
      modalElemento &&
      typeof bootstrap !== "undefined"
    ) {
      modalEditarPagoEmpleado = new bootstrap.Modal(modalElemento);
    }

    //=================================================
    // MOSTRAR MODAL
    //=================================================

    if (modalEditarPagoEmpleado) {
      modalEditarPagoEmpleado.show();
    } else {
      throw new Error("No fue posible inicializar el modal de edición.");
    }
  } catch (error) {
    console.error("Error editar pago:", error);

    Swal.close();

    await Swal.fire({
      icon: "error",

      title: "No se pudo cargar",

      text: error.message || "No fue posible cargar la información del pago.",

      confirmButtonText: "Aceptar",
    });
  }
}

//=====================================================
// CARGAR CUENTAS BANCARIAS - EDITAR
//=====================================================

async function cargarCuentasBancariasEditar() {
  const select = document.getElementById("editar_id_cuenta_bancaria");

  if (!select) {
    return;
  }

  select.innerHTML = `
    <option value="">
      Cargando cuentas bancarias...
    </option>
  `;

  select.disabled = true;

  try {
    const respuesta = await fetch("ajax/obtener_cuentas_bancarias.php", {
      method: "GET",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data.success) {
      throw new Error(
        data.mensaje || "No fue posible cargar las cuentas bancarias.",
      );
    }

    const cuentas = data.cuentas || data.datos?.cuentas || [];

    select.innerHTML = `
      <option value="">
        Seleccione una cuenta bancaria
      </option>
    `;

    if (!Array.isArray(cuentas) || cuentas.length === 0) {
      select.innerHTML = `
        <option value="">
          No hay cuentas bancarias disponibles
        </option>
      `;

      return;
    }

    cuentas.forEach(function (cuenta) {
      const option = document.createElement("option");

      option.value = cuenta.id_cuenta_bancaria;

      const nombre = cuenta.nombre || "Cuenta bancaria";

      const balance = parseFloat(cuenta.balance || 0);

      option.textContent = `${nombre} — Saldo: ${formatearMoneda(balance)}`;

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error cuentas editar:", error);

    select.innerHTML = `
      <option value="">
        Error al cargar cuentas bancarias
      </option>
    `;

    throw error;
  } finally {
    select.disabled = false;
  }
}

//=====================================================
// CARGAR MÉTODOS - EDITAR
//=====================================================

async function cargarMetodosPagoEditar() {
  const select = document.getElementById("editar_id_metodo_pago");

  if (!select) {
    return;
  }

  select.innerHTML = `
    <option value="">
      Cargando métodos...
    </option>
  `;

  try {
    const respuesta = await fetch("ajax/obtener_metodos_pago_empleado.php", {
      method: "GET",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible cargar los métodos.");
    }

    const metodos = data.datos?.metodos || data.metodos || [];

    select.innerHTML = `
      <option value="">
        Seleccionar método
      </option>
    `;

    metodos.forEach(function (metodo) {
      const option = document.createElement("option");

      option.value = metodo.id_metodo_pago;

      option.textContent = metodo.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error métodos editar:", error);

    select.innerHTML = `
      <option value="">
        Error al cargar métodos
      </option>
    `;

    throw error;
  }
}

//=====================================================
// CALCULAR TOTAL PAGO EDITAR
//=====================================================

function calcularTotalPagoEditar() {
  const montoBase =
    parseFloat(document.getElementById("editar_monto_base")?.value || 0) || 0;

  const bonificaciones =
    parseFloat(document.getElementById("editar_bonificaciones")?.value || 0) ||
    0;

  const descuentos =
    parseFloat(document.getElementById("editar_descuentos")?.value || 0) || 0;

  let total = montoBase + bonificaciones - descuentos;

  total = Math.max(total, 0);

  const montoTotal = document.getElementById("editar_monto_total");

  if (montoTotal) {
    montoTotal.value = total.toFixed(2);
  }

  return total;
}

//=====================================================
// MARCAR PAGO COMO PAGADO
//=====================================================

async function marcarPagoPagado(idPago) {
  idPago = Number(idPago);

  if (!Number.isInteger(idPago) || idPago <= 0) {
    mostrarToast("El identificador del pago no es válido.", "error");

    return;
  }

  if (pagosActualizando.has(idPago)) {
    return;
  }

  const confirmacion = await Swal.fire({
    title: "¿Marcar pago como pagado?",

    html: `
        <div class="text-start">

          <p class="mb-3">

            ¿Confirmas que deseas
            marcar este pago como
            <strong>PAGADO</strong>?

          </p>

          <div
            class="alert alert-success">

            <i
              class="bi bi-check-circle me-2">
            </i>

            El monto será descontado
            de la cuenta bancaria
            asociada al pago.

          </div>

          <p
            class="text-muted small mb-0">

            Esta operación no podrá
            ejecutarse nuevamente
            sobre el mismo pago.

          </p>

        </div>
      `,

    icon: "question",

    showCancelButton: true,

    confirmButtonText: `
        <i
          class="bi bi-check-circle me-1">
        </i>

        Sí, marcar como pagado
      `,

    cancelButtonText: `
        <i
          class="bi bi-x-circle me-1">
        </i>

        Cancelar
      `,

    confirmButtonColor: "#198754",

    cancelButtonColor: "#6c757d",

    reverseButtons: true,

    focusCancel: true,

    allowOutsideClick: false,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  pagosActualizando.add(idPago);

  const boton = document.querySelector(
    `.btn-marcar-pagado[data-id-pago="${idPago}"]`,
  );

  const textoOriginal = boton ? boton.innerHTML : "";

  if (boton) {
    boton.disabled = true;

    boton.innerHTML = `
      <span
        class="spinner-border spinner-border-sm"
        role="status">
      </span>
    `;
  }

  try {
    Swal.fire({
      title: "Actualizando pago...",

      html: `
        <div class="mt-3">

          <div
            class="spinner-border text-success"
            role="status">
          </div>

          <p
            class="text-muted mt-3 mb-0">

            Actualizando el pago
            y la cuenta bancaria...

          </p>

        </div>
      `,

      allowOutsideClick: false,

      allowEscapeKey: false,

      showConfirmButton: false,
    });

    const formData = new FormData();

    formData.append("id_pago", String(idPago));

    formData.append("estado", "PAGADO");

    const respuesta = await fetch(URL_ACTUALIZAR_ESTADO_PAGO, {
      method: "POST",

      body: formData,

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const texto = await respuesta.text();

    console.log("Respuesta actualizar estado pago:", texto);

    let data;

    try {
      data = JSON.parse(texto);
    } catch (error) {
      throw new Error("El servidor devolvió una respuesta inválida.");
    }

    if (!data.success) {
      throw new Error(
        data.mensaje || "No fue posible marcar el pago como pagado.",
      );
    }

    Swal.close();

    await Swal.fire({
      icon: "success",

      title: "¡Pago actualizado!",

      text: data.mensaje || "El pago fue marcado como pagado correctamente.",

      confirmButtonText: "Aceptar",

      confirmButtonColor: "#198754",
    });

    await cargarKPI();

    await cargarPagos();
  } catch (error) {
    console.error("Error al marcar pago:", error);

    Swal.close();

    await Swal.fire({
      icon: "error",

      title: "No se pudo actualizar",

      text:
        error.message || "Ocurrió un error al actualizar el estado del pago.",

      confirmButtonText: "Aceptar",
    });

    if (boton) {
      boton.disabled = false;

      boton.innerHTML = textoOriginal;
    }
  } finally {
    pagosActualizando.delete(idPago);
  }
}

//=====================================================
// REGISTRAR PAGO
//=====================================================

async function registrarPago(evento) {
  evento.preventDefault();

  const formulario = document.getElementById("formRegistrarPago");

  if (!formulario) {
    return;
  }

  const idEmpleado = document.getElementById("pagoEmpleado")?.value;

  const idSueldo = document.getElementById("pagoSueldoId")?.value;

  const periodoInicio = document.getElementById("pagoPeriodoInicio")?.value;

  const periodoFin = document.getElementById("pagoPeriodoFin")?.value;

  const montoBase = obtenerNumero("montoBase");

  const bonificaciones = obtenerNumero("bonificaciones");

  const descuentos = obtenerNumero("descuentos");

  const fechaPago = document.getElementById("fechaPago")?.value;

  const idCuenta = document.getElementById("cuentaBancaria")?.value;

  const idMetodo = document.getElementById("metodoPago")?.value;

  const estado = document.getElementById("estadoPago")?.value || "PENDIENTE";

  const observacion =
    formulario.querySelector('[name="observacion"]')?.value.trim() || "";

  //=================================================
  // VALIDACIONES
  //=================================================

  if (!idEmpleado) {
    mostrarToast("Seleccione un empleado.", "warning");

    return;
  }

  if (!idSueldo) {
    mostrarToast(
      "El empleado no tiene un sueldo válido seleccionado.",
      "warning",
    );

    return;
  }

  if (!periodoInicio || !periodoFin) {
    mostrarToast("Seleccione el período del pago.", "warning");

    return;
  }

  if (!validarPeriodo()) {
    return;
  }

  if (montoBase <= 0) {
    mostrarToast("El monto base debe ser mayor que cero.", "warning");

    return;
  }

  if (bonificaciones < 0) {
    mostrarToast("Las bonificaciones no pueden ser negativas.", "warning");

    return;
  }

  if (descuentos < 0) {
    mostrarToast("Los descuentos no pueden ser negativos.", "warning");

    return;
  }

  const montoTotal = montoBase + bonificaciones - descuentos;

  if (montoTotal <= 0) {
    mostrarToast("El monto total debe ser mayor que cero.", "warning");

    return;
  }

  if (!fechaPago) {
    mostrarToast("Seleccione la fecha de pago.", "warning");

    return;
  }

  if (!idCuenta) {
    mostrarToast("Seleccione una cuenta bancaria.", "warning");

    return;
  }

  if (!idMetodo) {
    mostrarToast("Seleccione un método de pago.", "warning");

    return;
  }

  //=================================================
  // CONFIRMACIÓN
  //=================================================

  const confirmacion = await Swal.fire({
    title: "¿Registrar pago?",

    html: `
        <div class="text-start">

          <p class="mb-2">

            <strong>
              Empleado:
            </strong>

            ${escapeHTML(obtenerTextoSelect("pagoEmpleado"))}

          </p>

          <p class="mb-2">

            <strong>
              Periodo:
            </strong>

            ${formatearFecha(periodoInicio)}

            -

            ${formatearFecha(periodoFin)}

          </p>

          <p class="mb-0">

            <strong>
              Total:
            </strong>

            ${formatearMoneda(montoTotal)}

          </p>

        </div>
      `,

    icon: "question",

    showCancelButton: true,

    confirmButtonText: "Sí, registrar",

    cancelButtonText: "Cancelar",

    reverseButtons: true,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  const boton = document.getElementById("btnGuardarPago");

  const textoOriginal = boton ? boton.innerHTML : "";

  if (boton) {
    boton.disabled = true;

    boton.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-2">
      </span>

      Registrando...
    `;
  }

  const formData = new FormData();

  formData.append("id_empleado", idEmpleado);

  formData.append("id_sueldo", idSueldo);

  formData.append("periodo_inicio", periodoInicio);

  formData.append("periodo_fin", periodoFin);

  formData.append("monto_base", montoBase.toFixed(2));

  formData.append("bonificaciones", bonificaciones.toFixed(2));

  formData.append("descuentos", descuentos.toFixed(2));

  formData.append("monto_total", montoTotal.toFixed(2));

  formData.append("fecha_pago", fechaPago);

  formData.append("id_cuenta_bancaria", idCuenta);

  formData.append("id_metodo_pago", idMetodo);

  formData.append("estado", estado);

  formData.append("observacion", observacion);

  try {
    const respuesta = await fetch(URL_REGISTRAR_PAGO, {
      method: "POST",

      body: formData,

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const texto = await respuesta.text();

    console.log("Respuesta registrar pago:", texto);

    let data;

    try {
      data = JSON.parse(texto);
    } catch (error) {
      throw new Error("El servidor devolvió una respuesta inválida.");
    }

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible registrar el pago.");
    }

    if (modalRegistrarPago) {
      modalRegistrarPago.hide();
    }

    await Swal.fire({
      icon: "success",

      title: "Pago registrado",

      text: data.mensaje || "El pago se registró correctamente.",

      confirmButtonText: "Aceptar",
    });

    paginaActualPagos = 1;

    await cargarKPI();

    await cargarPagos();
  } catch (error) {
    console.error("Error registrar pago:", error);

    Swal.fire({
      icon: "error",

      title: "No se pudo registrar",

      text: error.message || "Ocurrió un error inesperado.",

      confirmButtonText: "Aceptar",
    });
  } finally {
    if (boton) {
      boton.disabled = false;

      boton.innerHTML = textoOriginal;
    }
  }
}

//=====================================================
// ACTUALIZAR PAGO
//=====================================================

async function actualizarPagoEmpleado(evento) {
  evento.preventDefault();

  const formulario = document.getElementById("formEditarPagoEmpleado");

  if (!formulario) {
    return;
  }

  const idPago = document.getElementById("editar_id_pago")?.value;

  const estado = document.getElementById("editar_estado")?.value;

  const periodoInicio = document.getElementById("editar_periodo_inicio")?.value;

  const periodoFin = document.getElementById("editar_periodo_fin")?.value;

  const montoBase =
    parseFloat(document.getElementById("editar_monto_base")?.value || 0) || 0;

  const bonificaciones =
    parseFloat(document.getElementById("editar_bonificaciones")?.value || 0) ||
    0;

  const descuentos =
    parseFloat(document.getElementById("editar_descuentos")?.value || 0) || 0;

  const fechaPago = document.getElementById("editar_fecha_pago")?.value;

  const idCuenta = document.getElementById("editar_id_cuenta_bancaria")?.value;

  const idMetodo = document.getElementById("editar_id_metodo_pago")?.value;

  const observacion =
    document.getElementById("editar_observacion")?.value.trim() || "";

  //=================================================
  // VALIDACIONES
  //=================================================

  if (!idPago) {
    mostrarToast("El pago no tiene un identificador válido.", "error");

    return;
  }

  if (!periodoInicio || !periodoFin) {
    mostrarToast("Debe indicar el período del pago.", "warning");

    return;
  }

  if (periodoInicio > periodoFin) {
    mostrarToast(
      "La fecha inicial no puede ser posterior a la fecha final.",
      "warning",
    );

    return;
  }

  if (montoBase <= 0) {
    mostrarToast("El monto base debe ser mayor que cero.", "warning");

    return;
  }

  if (bonificaciones < 0) {
    mostrarToast("Las bonificaciones no pueden ser negativas.", "warning");

    return;
  }

  if (descuentos < 0) {
    mostrarToast("Los descuentos no pueden ser negativos.", "warning");

    return;
  }

  const montoTotal = montoBase + bonificaciones - descuentos;

  if (montoTotal <= 0) {
    mostrarToast("El monto total debe ser mayor que cero.", "warning");

    return;
  }

  if (!fechaPago) {
    mostrarToast("Seleccione la fecha de pago.", "warning");

    return;
  }

  if (!idCuenta) {
    mostrarToast("Seleccione una cuenta bancaria.", "warning");

    return;
  }

  if (!idMetodo) {
    mostrarToast("Seleccione un método de pago.", "warning");

    return;
  }

  //=================================================
  // CONFIRMACIÓN
  //=================================================

  const confirmacion = await Swal.fire({
    title: "¿Guardar cambios?",

    html: `
        <div class="text-start">

          <p class="mb-2">

            Se actualizará la información
            del pago seleccionado.

          </p>

          <div
            class="alert alert-info mb-0">

            <strong>
              Nuevo monto total:
            </strong>

            ${formatearMoneda(montoTotal)}

          </div>

        </div>
      `,

    icon: "question",

    showCancelButton: true,

    confirmButtonText:
      '<i class="bi bi-check-circle me-1"></i> Guardar cambios',

    cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancelar',

    reverseButtons: true,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  //=================================================
  // BOTÓN
  //=================================================

  const boton = document.getElementById("btnActualizarPagoEmpleado");

  const textoOriginal = boton ? boton.innerHTML : "";

  if (boton) {
    boton.disabled = true;

    boton.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-2">
      </span>

      Guardando...
    `;
  }

  //=================================================
  // FORM DATA
  //=================================================

  const formData = new FormData();

  formData.append("id_pago", idPago);

  formData.append("periodo_inicio", periodoInicio);

  formData.append("periodo_fin", periodoFin);

  formData.append("monto_base", montoBase.toFixed(2));

  formData.append("bonificaciones", bonificaciones.toFixed(2));

  formData.append("descuentos", descuentos.toFixed(2));

  formData.append("monto_total", montoTotal.toFixed(2));

  formData.append("fecha_pago", fechaPago);

  formData.append("id_cuenta_bancaria", idCuenta);

  formData.append("id_metodo_pago", idMetodo);

  formData.append("estado", estado);

  formData.append("observacion", observacion);

  try {
    const respuesta = await fetch(URL_ACTUALIZAR_PAGO, {
      method: "POST",

      body: formData,

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const texto = await respuesta.text();

    console.log("Respuesta actualizar pago:", texto);

    let data;

    try {
      data = JSON.parse(texto);
    } catch (error) {
      throw new Error("El servidor devolvió una respuesta inválida.");
    }

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible actualizar el pago.");
    }

    if (modalEditarPagoEmpleado) {
      modalEditarPagoEmpleado.hide();
    }

    await Swal.fire({
      icon: "success",

      title: "¡Pago actualizado!",

      text: data.mensaje || "El pago fue actualizado correctamente.",

      confirmButtonText: "Aceptar",
    });

    await cargarKPI();

    await cargarPagos();
  } catch (error) {
    console.error("Error actualizar pago:", error);

    await Swal.fire({
      icon: "error",

      title: "No se pudo actualizar",

      text: error.message || "Ocurrió un error al actualizar el pago.",

      confirmButtonText: "Aceptar",
    });
  } finally {
    if (boton) {
      boton.disabled = false;

      boton.innerHTML = textoOriginal;
    }
  }
}

//=====================================================
// VER PAGO
//=====================================================

async function verPago(idPago) {
  idPago = Number(idPago);

  if (!Number.isInteger(idPago) || idPago <= 0) {
    mostrarToast("El identificador del pago no es válido.", "error");

    return;
  }

  try {
    const parametros = new URLSearchParams();

    parametros.append("id_pago", idPago);

    const respuesta = await fetch(
      URL_OBTENER_PAGO + "?" + parametros.toString(),
      {
        method: "GET",

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible obtener el pago.");
    }

    const pago = data.datos?.pago || data.pago;

    if (!pago) {
      throw new Error("No se encontró información del pago.");
    }

    await Swal.fire({
      title: "Detalle del pago",

      width: 650,

      html: `
        <div class="text-start">

          <div class="row g-3">

            <div class="col-md-6">

              <small
                class="text-muted">
                Empleado
              </small>

              <div
                class="fw-semibold">

                ${escapeHTML(pago.empleado || "")}

              </div>

            </div>

            <div class="col-md-6">

              <small
                class="text-muted">
                Estado
              </small>

              <div>

                ${obtenerBadgeEstado(pago.estado)}

              </div>

            </div>

            <div class="col-md-6">

              <small
                class="text-muted">
                Periodo
              </small>

              <div>

                ${formatearFecha(pago.periodo_inicio)}

                -

                ${formatearFecha(pago.periodo_fin)}

              </div>

            </div>

            <div class="col-md-6">

              <small
                class="text-muted">
                Fecha de pago
              </small>

              <div>

                ${formatearFecha(pago.fecha_pago)}

              </div>

            </div>

            <div class="col-md-4">

              <small
                class="text-muted">
                Monto base
              </small>

              <div>

                ${formatearMoneda(pago.monto_base)}

              </div>

            </div>

            <div class="col-md-4">

              <small
                class="text-muted">
                Bonificaciones
              </small>

              <div
                class="text-success">

                +
                ${formatearMoneda(pago.bonificaciones)}

              </div>

            </div>

            <div class="col-md-4">

              <small
                class="text-muted">
                Descuentos
              </small>

              <div
                class="text-danger">

                -
                ${formatearMoneda(pago.descuentos)}

              </div>

            </div>

            <div class="col-12">

              <div
                class="p-3 bg-light rounded">

                <small
                  class="text-muted">
                  Monto total
                </small>

                <div
                  class="fs-3 fw-bold">

                  ${formatearMoneda(pago.monto_total)}

                </div>

              </div>

            </div>

            ${
              pago.observacion
                ? `
                  <div
                    class="col-12">

                    <small
                      class="text-muted">

                      Observación

                    </small>

                    <div>

                      ${escapeHTML(pago.observacion)}

                    </div>

                  </div>
                `
                : ""
            }

          </div>

        </div>
      `,

      confirmButtonText: "Cerrar",
    });
  } catch (error) {
    console.error("Error detalle pago:", error);

    mostrarToast(error.message || "No fue posible consultar el pago.", "error");
  }
}

//=====================================================
// CARGAR EMPLEADOS
//=====================================================

async function cargarEmpleadosPago() {
  const select = document.getElementById("pagoEmpleado");

  if (!select) {
    return;
  }

  select.innerHTML = `
    <option value="">
      Cargando empleados...
    </option>
  `;

  try {
    const respuesta = await fetch("ajax/obtener_empleados_pago.php", {
      method: "GET",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible cargar empleados.");
    }

    const empleados = data.datos?.empleados || data.empleados || [];

    select.innerHTML = `
      <option value="">
        Seleccione un empleado
      </option>
    `;

    empleados.forEach(function (empleado) {
      const option = document.createElement("option");

      option.value = empleado.id_empleado;

      option.textContent = (
        (empleado.nombre || "") +
        " " +
        (empleado.apellido || "")
      ).trim();

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error empleados:", error);

    select.innerHTML = `
      <option value="">
        Error al cargar empleados
      </option>
    `;

    mostrarToast(error.message || "No fue posible cargar empleados.", "error");
  }
}

//=====================================================
// CARGAR SUELDO
//=====================================================

async function cargarSueldoEmpleado() {
  const empleado = document.getElementById("pagoEmpleado");

  const sueldo = document.getElementById("pagoSueldo");

  const sueldoId = document.getElementById("pagoSueldoId");

  const tipoPago = document.getElementById("pagoTipoPago");

  const montoBase = document.getElementById("montoBase");

  if (sueldo) {
    sueldo.value = "0.00";
  }

  if (sueldoId) {
    sueldoId.value = "";
  }

  if (tipoPago) {
    tipoPago.textContent = "";
  }

  if (montoBase) {
    montoBase.value = "0.00";
  }

  if (flatpickrPeriodoInicio) {
    flatpickrPeriodoInicio.clear();
  }

  if (flatpickrPeriodoFin) {
    flatpickrPeriodoFin.clear();
  }

  const idEmpleado = empleado?.value || "";

  if (!idEmpleado) {
    calcularMontoTotal();

    return;
  }

  try {
    const parametros = new URLSearchParams();

    parametros.append("id_empleado", idEmpleado);

    const respuesta = await fetch(
      "ajax/obtener_sueldo_empleado.php?" + parametros.toString(),
      {
        method: "GET",

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible obtener el sueldo.");
    }

    const sueldos = data.datos?.sueldos || data.sueldos || [];

    if (!Array.isArray(sueldos) || sueldos.length === 0) {
      mostrarToast("El empleado no tiene un sueldo activo.", "warning");

      calcularMontoTotal();

      return;
    }

    const sueldoActual = sueldos[0];

    const monto = parseFloat(sueldoActual.sueldo_base || 0);

    if (sueldo) {
      sueldo.value = monto.toFixed(2);
    }

    if (sueldoId) {
      sueldoId.value = sueldoActual.id_sueldo || "";
    }

    if (tipoPago) {
      tipoPago.textContent = sueldoActual.tipo_pago
        ? "Tipo de pago: " + sueldoActual.tipo_pago
        : "";
    }

    if (montoBase) {
      montoBase.value = monto.toFixed(2);
    }

    establecerPeriodoPago(sueldoActual.tipo_pago);

    calcularMontoTotal();
  } catch (error) {
    console.error("Error sueldo:", error);

    mostrarToast(error.message || "No fue posible obtener el sueldo.", "error");
  }
}

//=====================================================
// ESTABLECER PERIODO
//=====================================================

function establecerPeriodoPago(tipoPago) {
  if (!tipoPago) {
    return;
  }

  tipoPago = String(tipoPago).trim().toUpperCase();

  const fechaActual = new Date();

  let inicio = new Date(fechaActual);

  let fin = new Date(fechaActual);

  if (tipoPago === "MENSUAL") {
    inicio = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1);

    fin = new Date(fechaActual.getFullYear(), fechaActual.getMonth() + 1, 0);
  } else if (tipoPago === "QUINCENAL") {
    if (fechaActual.getDate() <= 15) {
      inicio = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1);

      fin = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 15);
    } else {
      inicio = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 16);

      fin = new Date(fechaActual.getFullYear(), fechaActual.getMonth() + 1, 0);
    }
  } else if (tipoPago === "SEMANAL") {
    const dia = fechaActual.getDay();

    const diferencia = dia === 0 ? 6 : dia - 1;

    inicio = new Date(fechaActual);

    inicio.setDate(fechaActual.getDate() - diferencia);

    fin = new Date(inicio);

    fin.setDate(inicio.getDate() + 6);
  } else if (tipoPago === "DIARIO") {
    inicio = new Date(fechaActual);

    fin = new Date(fechaActual);
  }

  if (flatpickrPeriodoInicio) {
    flatpickrPeriodoInicio.setDate(formatearFechaISO(inicio), true);
  }

  if (flatpickrPeriodoFin) {
    flatpickrPeriodoFin.setDate(formatearFechaISO(fin), true);
  }
}

//=====================================================
// FECHA ISO
//=====================================================

function formatearFechaISO(fecha) {
  const año = fecha.getFullYear();

  const mes = String(fecha.getMonth() + 1).padStart(2, "0");

  const dia = String(fecha.getDate()).padStart(2, "0");

  return año + "-" + mes + "-" + dia;
}

//=====================================================
// VALIDAR PERIODO
//=====================================================

function validarPeriodo() {
  const inicio = document.getElementById("pagoPeriodoInicio")?.value;

  const fin = document.getElementById("pagoPeriodoFin")?.value;

  if (!inicio || !fin) {
    return true;
  }

  if (inicio > fin) {
    mostrarToast(
      "La fecha inicial no puede ser posterior a la fecha final.",
      "warning",
    );

    return false;
  }

  return true;
}

//=====================================================
// CALCULAR MONTO
//=====================================================

function calcularMontoTotal() {
  const base = obtenerNumero("montoBase");

  const bonificaciones = obtenerNumero("bonificaciones");

  const descuentos = obtenerNumero("descuentos");

  const total = base + bonificaciones - descuentos;

  const totalFinal = Math.max(total, 0);

  const montoTotal = document.getElementById("montoTotal");

  const texto = document.getElementById("montoTotalTexto");

  if (montoTotal) {
    montoTotal.value = totalFinal.toFixed(2);
  }

  if (texto) {
    texto.textContent = formatearMoneda(totalFinal);
  }
}

//=====================================================
// ABRIR MODAL REGISTRAR
//=====================================================

function abrirModalRegistrarPago() {
  const formulario = document.getElementById("formRegistrarPago");

  if (formulario) {
    formulario.reset();
  }

  limpiarFormularioPago();

  const estado = document.getElementById("estadoPago");

  if (estado) {
    estado.value = "PENDIENTE";
  }

  if (flatpickrFechaPago) {
    flatpickrFechaPago.setDate(new Date(), true);
  }

  cargarEmpleadosPago();

  cargarCuentasBancarias();

  cargarMetodosPago();

  if (!modalRegistrarPago) {
    const elemento = document.getElementById("modalRegistrarPago");

    if (elemento && typeof bootstrap !== "undefined") {
      modalRegistrarPago = new bootstrap.Modal(elemento);
    }
  }

  if (modalRegistrarPago) {
    modalRegistrarPago.show();
  }
}

//=====================================================
// CARGAR CUENTAS
//=====================================================

async function cargarCuentasBancarias() {
  const select = document.getElementById("cuentaBancaria");

  if (!select) {
    return;
  }

  select.innerHTML = `
    <option value="">
      Cargando cuentas bancarias...
    </option>
  `;

  select.disabled = true;

  try {
    const respuesta = await fetch("ajax/obtener_cuentas_bancarias.php", {
      method: "GET",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data.success) {
      throw new Error(
        data.mensaje || "No fue posible cargar las cuentas bancarias.",
      );
    }

    const cuentas = data.cuentas || data.datos?.cuentas || [];

    select.innerHTML = `
      <option value="">
        Seleccione una cuenta bancaria
      </option>
    `;

    if (!Array.isArray(cuentas) || cuentas.length === 0) {
      select.innerHTML = `
        <option value="">
          No hay cuentas bancarias disponibles
        </option>
      `;

      return;
    }

    cuentas.forEach(function (cuenta) {
      const option = document.createElement("option");

      option.value = cuenta.id_cuenta_bancaria;

      const nombre = cuenta.nombre || "Cuenta bancaria";

      const balance = parseFloat(cuenta.balance || 0);

      option.textContent = `${nombre} — Saldo: ${formatearMoneda(balance)}`;

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error cuentas:", error);

    select.innerHTML = `
      <option value="">
        Error al cargar cuentas bancarias
      </option>
    `;

    mostrarToast(
      error.message || "No fue posible cargar las cuentas bancarias.",
      "error",
    );
  } finally {
    select.disabled = false;
  }
}

//=====================================================
// CARGAR MÉTODOS
//=====================================================

async function cargarMetodosPago() {
  const select = document.getElementById("metodoPago");

  if (!select) {
    return;
  }

  select.innerHTML = `
    <option value="">
      Cargando métodos...
    </option>
  `;

  try {
    const respuesta = await fetch("ajax/obtener_metodos_pago_empleado.php", {
      method: "GET",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data.success) {
      throw new Error(data.mensaje || "No fue posible cargar métodos.");
    }

    const metodos = data.datos?.metodos || data.metodos || [];

    select.innerHTML = `
      <option value="">
        Seleccione un método
      </option>
    `;

    metodos.forEach(function (metodo) {
      const option = document.createElement("option");

      option.value = metodo.id_metodo_pago;

      option.textContent = metodo.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error métodos:", error);

    select.innerHTML = `
      <option value="">
        Error al cargar métodos
      </option>
    `;

    mostrarToast("No fue posible cargar los métodos de pago.", "error");
  }
}

//=====================================================
// PAGINACIÓN
//=====================================================

function renderizarPaginacion(totalRegistros) {
  const contenedor = document.getElementById("paginacionPagos");

  if (!contenedor) {
    return;
  }

  totalRegistros = Number(totalRegistros) || 0;

  const totalPaginas = Math.ceil(totalRegistros / registrosPorPaginaPagos);

  if (totalPaginas <= 1) {
    contenedor.innerHTML = "";

    return;
  }

  let html = "";

  html += `
    <li
      class="page-item ${paginaActualPagos <= 1 ? "disabled" : ""}">

      <button
        type="button"
        class="page-link"
        ${paginaActualPagos <= 1 ? "disabled" : ""}
        onclick="cambiarPaginaPagos(${paginaActualPagos - 1})">

        <i
          class="bi bi-chevron-left">
        </i>

      </button>

    </li>
  `;

  const inicio = Math.max(1, paginaActualPagos - 2);

  const fin = Math.min(totalPaginas, paginaActualPagos + 2);

  for (let i = inicio; i <= fin; i++) {
    html += `
      <li
        class="page-item ${i === paginaActualPagos ? "active" : ""}">

        <button
          type="button"
          class="page-link"
          onclick="cambiarPaginaPagos(${i})">

          ${i}

        </button>

      </li>
    `;
  }

  html += `
    <li
      class="page-item ${paginaActualPagos >= totalPaginas ? "disabled" : ""}">

      <button
        type="button"
        class="page-link"
        ${paginaActualPagos >= totalPaginas ? "disabled" : ""}
        onclick="cambiarPaginaPagos(${paginaActualPagos + 1})">

        <i
          class="bi bi-chevron-right">
        </i>

      </button>

    </li>
  `;

  contenedor.innerHTML = html;
}

//=====================================================
// INFORMACIÓN PAGINACIÓN
//=====================================================

function actualizarInfoPaginacion(totalRegistros) {
  const elemento = document.getElementById("infoPaginacion");

  if (!elemento) {
    return;
  }

  totalRegistros = Number(totalRegistros) || 0;

  if (totalRegistros === 0) {
    elemento.textContent = "Mostrando 0 registros";

    return;
  }

  const inicio = (paginaActualPagos - 1) * registrosPorPaginaPagos + 1;

  const fin = Math.min(
    paginaActualPagos * registrosPorPaginaPagos,
    totalRegistros,
  );

  elemento.textContent = `Mostrando ${inicio} - ${fin} de ${totalRegistros} registros`;
}

//=====================================================
// CAMBIAR PÁGINA
//=====================================================

function cambiarPaginaPagos(pagina) {
  pagina = Number(pagina);

  if (!Number.isInteger(pagina) || pagina < 1) {
    return;
  }

  paginaActualPagos = pagina;

  cargarPagos();
}

//=====================================================
// LIMPIAR FORMULARIO PAGO
//=====================================================

function limpiarFormularioPago() {
  const empleado = document.getElementById("pagoEmpleado");

  if (empleado) {
    empleado.innerHTML = `
      <option value="">
        Seleccione un empleado
      </option>
    `;
  }

  const sueldo = document.getElementById("pagoSueldo");

  if (sueldo) {
    sueldo.value = "0.00";
  }

  const sueldoId = document.getElementById("pagoSueldoId");

  if (sueldoId) {
    sueldoId.value = "";
  }

  const tipoPago = document.getElementById("pagoTipoPago");

  if (tipoPago) {
    tipoPago.textContent = "";
  }

  if (flatpickrPeriodoInicio) {
    flatpickrPeriodoInicio.clear();
  }

  if (flatpickrPeriodoFin) {
    flatpickrPeriodoFin.clear();
  }

  const montoBase = document.getElementById("montoBase");

  if (montoBase) {
    montoBase.value = "0.00";
  }

  const bonificaciones = document.getElementById("bonificaciones");

  if (bonificaciones) {
    bonificaciones.value = "0.00";
  }

  const descuentos = document.getElementById("descuentos");

  if (descuentos) {
    descuentos.value = "0.00";
  }

  const montoTotal = document.getElementById("montoTotal");

  if (montoTotal) {
    montoTotal.value = "0.00";
  }

  const montoTexto = document.getElementById("montoTotalTexto");

  if (montoTexto) {
    montoTexto.textContent = "S/ 0.00";
  }
}

//=====================================================
// COMPATIBILIDAD
//=====================================================

function limpiarFiltros() {
  limpiarFiltrosPagos();
}

//=====================================================
// OBTENER NÚMERO
//=====================================================

function obtenerNumero(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return 0;
  }

  const numero = parseFloat(String(elemento.value || "0").replace(",", "."));

  return Number.isFinite(numero) ? numero : 0;
}

//=====================================================
// OBTENER TEXTO SELECT
//=====================================================

function obtenerTextoSelect(id) {
  const select = document.getElementById(id);

  if (!select || select.selectedIndex < 0) {
    return "";
  }

  return select.options[select.selectedIndex].textContent.trim();
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  const numero = parseFloat(valor) || 0;

  return new Intl.NumberFormat("es-PE", {
    style: "currency",

    currency: "PEN",

    minimumFractionDigits: 2,
  }).format(numero);
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "-";
  }

  const texto = String(fecha);

  const partes = texto.split("-");

  if (partes.length === 3) {
    return partes[2].substring(0, 2) + "/" + partes[1] + "/" + partes[0];
  }

  return texto;
}

//=====================================================
// BADGE ESTADO
//=====================================================

function obtenerBadgeEstado(estado) {
  estado = String(estado || "")
    .trim()
    .toUpperCase();

  switch (estado) {
    case "PAGADO":
      return `
        <span
          class="badge bg-success">

          <i
            class="bi bi-check-circle me-1">
          </i>

          Pagado

        </span>
      `;

    case "PENDIENTE":
      return `
        <span
          class="badge bg-warning text-dark">

          <i
            class="bi bi-clock me-1">
          </i>

          Pendiente

        </span>
      `;

    case "ANULADO":
      return `
        <span
          class="badge bg-danger">

          <i
            class="bi bi-x-circle me-1">
          </i>

          Anulado

        </span>
      `;

    default:
      return `
        <span
          class="badge bg-secondary">

          ${escapeHTML(estado || "DESCONOCIDO")}

        </span>
      `;
  }
}

//=====================================================
// ERROR TABLA
//=====================================================

function mostrarErrorTabla(mensaje) {
  const tabla = document.getElementById("tablaPagos");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `
    <tr>

      <td
        colspan="9"
        class="text-center py-5">

        <i
          class="bi bi-exclamation-triangle text-danger fs-1">
        </i>

        <h6 class="mt-3">

          No fue posible cargar
          los pagos

        </h6>

        <p class="text-muted">

          ${escapeHTML(mensaje)}

        </p>

        <button
          type="button"
          class="btn btn-primary btn-sm"
          onclick="cargarPagos()">

          <i
            class="bi bi-arrow-clockwise me-1">
          </i>

          Reintentar

        </button>

      </td>

    </tr>
  `;
}

//=====================================================
// TOAST
//=====================================================

function mostrarToast(mensaje, icono = "info") {
  if (typeof Swal === "undefined") {
    alert(mensaje);

    return;
  }

  Swal.fire({
    toast: true,

    position: "top-end",

    icon: icono,

    title: mensaje,

    showConfirmButton: false,

    timer: 3000,

    timerProgressBar: true,
  });
}

//=====================================================
// ESCAPE HTML
//=====================================================

function escapeHTML(texto) {
  if (texto === null || texto === undefined) {
    return "";
  }

  return String(texto)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

//=====================================================
// EXPONER FUNCIONES GLOBALMENTE
//=====================================================

window.cargarPagos = cargarPagos;

window.cargarKPI = cargarKPI;

window.cambiarPaginaPagos = cambiarPaginaPagos;

window.abrirModalRegistrarPago = abrirModalRegistrarPago;

window.verPago = verPago;

window.editarPago = editarPago;

window.marcarPagoPagado = marcarPagoPagado;

window.limpiarFiltros = limpiarFiltros;

window.limpiarFiltrosPagos = limpiarFiltrosPagos;

window.calcularMontoTotal = calcularMontoTotal;

window.calcularTotalPagoEditar = calcularTotalPagoEditar;

window.actualizarPagoEmpleado = actualizarPagoEmpleado;
