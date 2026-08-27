//=====================================================
// CoDevPro Technology
// Archivo: js/adm_cuentas_bancarias.js
// Módulo: Cuentas Bancarias
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActualCuentas = 1;

let registrosPorPaginaCuentas = 5;

let totalRegistrosCuentas = 0;

let totalPaginasCuentas = 1;

let temporizadorBusquedaCuenta = null;

let solicitudCuentasActual = null;

let solicitudKpiActual = null;

let solicitudCuentaEditarActual = null;

let cargandoCuentaEditar = false;

let registrandoCuenta = false;

let actualizandoCuenta = false;

let cambiandoEstadoCuenta = false;

let tipoCambioEstadoCuenta = null;

let idCuentaCambioEstado = null;

//=====================================================
// CONFIGURACIÓN AJAX
//=====================================================

const URL_OBTENER_CUENTAS = "ajax/obtener_cuentas_bancarias.php";

const URL_OBTENER_KPI = "ajax/obtener_kpis_cuentas_bancarias.php";

const URL_REGISTRAR_CUENTA = "ajax/registrar_cuenta_bancaria.php";

const URL_OBTENER_CUENTA = "ajax/obtener_cuenta_bancaria.php";

const URL_EDITAR_CUENTA = "ajax/editar_cuenta_bancaria.php";

const URL_ELIMINAR_CUENTA = "ajax/eliminar_cuenta_bancaria.php";

const URL_RESTAURAR_CUENTA = "ajax/restaurar_cuenta_bancaria.php";

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarModuloCuentasBancarias();
});

//=====================================================
// INICIALIZAR MÓDULO
//=====================================================

function inicializarModuloCuentasBancarias() {
  console.log("Módulo Cuentas Bancarias iniciado.");

  //=================================================
  // CARGA INICIAL
  //=================================================

  cargarKpiCuentas();

  cargarCuentasBancarias();

  //=================================================
  // BUSCADOR
  //=================================================

  const inputBuscar = document.getElementById("buscarCuenta");

  if (inputBuscar) {
    inputBuscar.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaCuenta);

      temporizadorBusquedaCuenta = setTimeout(function () {
        paginaActualCuentas = 1;

        cargarCuentasBancarias();
      }, 350);
    });
  }

  //=================================================
  // FILTRO ESTADO
  //=================================================

  const filtroEstado = document.getElementById("filtroEstadoCuenta");

  if (filtroEstado) {
    filtroEstado.addEventListener("change", function () {
      paginaActualCuentas = 1;

      cargarCuentasBancarias();
    });
  }

  //=================================================
  // ORDEN
  //=================================================

  const orden = document.getElementById("ordenCuentas");

  if (orden) {
    orden.addEventListener("change", function () {
      paginaActualCuentas = 1;

      cargarCuentasBancarias();
    });
  }

  //=================================================
  // LIMPIAR FILTROS
  //=================================================

  const btnLimpiar = document.getElementById("btnLimpiarFiltros");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function () {
      limpiarFiltrosCuentas();
    });
  }

  //=================================================
  // EVENTOS DINÁMICOS TABLA
  //=================================================

  const tabla = document.getElementById("tablaCuentasBancarias");

  if (tabla) {
    tabla.addEventListener("click", manejarAccionesCuenta);
  }

  //=================================================
  // MODALES
  //=================================================

  inicializarEventosModales();

  //=================================================
  // FORMULARIO REGISTRAR
  //=================================================

  inicializarFormularioRegistrarCuenta();

  //=================================================
  // FORMULARIO EDITAR
  //=================================================

  inicializarFormularioEditarCuenta();

  //=================================================
  // MODAL CAMBIAR ESTADO
  //=================================================

  inicializarModalCambiarEstado();
}

//=====================================================
// INICIALIZAR FORMULARIO REGISTRAR
//=====================================================

function inicializarFormularioRegistrarCuenta() {
  const formulario = document.getElementById("formRegistrarCuenta");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", function (evento) {
    evento.preventDefault();

    registrarCuentaBancaria();
  });

  const nombre = document.getElementById("nombreCuenta");

  if (nombre) {
    nombre.addEventListener("input", function () {
      quitarErrorCampo(nombre);

      ocultarMensajeRegistrarCuenta();
    });
  }

  const balance = document.getElementById("balanceCuenta");

  if (balance) {
    balance.addEventListener("input", function () {
      quitarErrorCampo(balance);

      ocultarMensajeRegistrarCuenta();
    });
  }
}

//=====================================================
// INICIALIZAR FORMULARIO EDITAR
//=====================================================

function inicializarFormularioEditarCuenta() {
  const formulario = document.getElementById("formEditarCuenta");

  if (!formulario) {
    return;
  }

  formulario.addEventListener("submit", function (evento) {
    evento.preventDefault();

    actualizarCuentaBancaria();
  });

  const nombre = document.getElementById("nombreCuentaEditar");

  if (nombre) {
    nombre.addEventListener("input", function () {
      quitarErrorCampo(nombre);

      ocultarMensajeEditarCuenta();
    });
  }

  const balance = document.getElementById("balanceCuentaEditar");

  if (balance) {
    balance.addEventListener("input", function () {
      quitarErrorCampo(balance);

      ocultarMensajeEditarCuenta();
    });
  }

  const estado = document.getElementById("estadoCuentaEditar");

  if (estado) {
    estado.addEventListener("change", function () {
      quitarErrorCampo(estado);

      ocultarMensajeEditarCuenta();
    });
  }
}

//=====================================================
// REGISTRAR CUENTA BANCARIA
//=====================================================

async function registrarCuentaBancaria() {
  if (registrandoCuenta) {
    return;
  }

  const formulario = document.getElementById("formRegistrarCuenta");

  const inputNombre = document.getElementById("nombreCuenta");

  const inputBalance = document.getElementById("balanceCuenta");

  const boton = document.getElementById("btnRegistrarCuenta");

  if (!formulario || !inputNombre || !inputBalance) {
    console.error("No se encontraron los elementos del formulario de cuenta.");

    return;
  }

  const nombre = inputNombre.value.trim();

  const balance = inputBalance.value.trim();

  quitarErrorCampo(inputNombre);

  quitarErrorCampo(inputBalance);

  ocultarMensajeRegistrarCuenta();

  //=================================================
  // VALIDAR NOMBRE
  //=================================================

  if (nombre === "") {
    marcarErrorCampo(inputNombre, "Ingresa el nombre de la cuenta.");

    inputNombre.focus();

    return;
  }

  if (nombre.length < 2) {
    marcarErrorCampo(
      inputNombre,
      "El nombre de la cuenta debe tener al menos 2 caracteres.",
    );

    inputNombre.focus();

    return;
  }

  if (nombre.length > 100) {
    marcarErrorCampo(
      inputNombre,
      "El nombre de la cuenta no puede superar los 100 caracteres.",
    );

    inputNombre.focus();

    return;
  }

  //=================================================
  // VALIDAR BALANCE
  //=================================================

  if (balance === "") {
    marcarErrorCampo(inputBalance, "Ingresa el balance inicial.");

    inputBalance.focus();

    return;
  }

  const balanceNormalizado = balance.replace(",", ".");

  const balanceNumero = Number(balanceNormalizado);

  if (!Number.isFinite(balanceNumero)) {
    marcarErrorCampo(inputBalance, "El balance ingresado no es válido.");

    inputBalance.focus();

    return;
  }

  if (balanceNumero < 0) {
    marcarErrorCampo(inputBalance, "El balance no puede ser negativo.");

    inputBalance.focus();

    return;
  }

  const partesBalance = balanceNormalizado.split(".");

  if (partesBalance.length === 2 && partesBalance[1].length > 2) {
    marcarErrorCampo(
      inputBalance,
      "El balance solo puede tener hasta 2 decimales.",
    );

    inputBalance.focus();

    return;
  }

  //=================================================
  // PROCESANDO
  //=================================================

  registrandoCuenta = true;

  if (boton) {
    boton.disabled = true;

    boton.dataset.textoOriginal = boton.innerHTML;

    boton.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-1"
        role="status"
        aria-hidden="true">
      </span>
      Registrando...
    `;
  }

  mostrarMensajeRegistrarCuenta(
    "info",
    `
      <i class="bi bi-hourglass-split me-1"></i>
      Registrando la cuenta bancaria...
    `,
  );

  try {
    const datos = new FormData();

    datos.append("nombre", nombre);

    datos.append("balance", balanceNumero.toFixed(2));

    const respuesta = await fetch(URL_REGISTRAR_CUENTA, {
      method: "POST",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },

      body: datos,

      cache: "no-store",
    });

    const textoRespuesta = await respuesta.text();

    let datosRespuesta;

    try {
      datosRespuesta = JSON.parse(textoRespuesta);
    } catch (errorJSON) {
      console.error("Respuesta no válida del servidor:", textoRespuesta);

      throw new Error("El servidor devolvió una respuesta no válida.");
    }

    if (!respuesta.ok) {
      throw new Error(
        datosRespuesta?.mensaje || "Ocurrió un error en el servidor.",
      );
    }

    if (!datosRespuesta || datosRespuesta.success !== true) {
      throw new Error(
        datosRespuesta?.mensaje || "No se pudo registrar la cuenta bancaria.",
      );
    }

    mostrarMensajeRegistrarCuenta(
      "success",
      `
        <i class="bi bi-check-circle-fill me-1"></i>
        ${escaparHTML(
          datosRespuesta.mensaje ||
            "La cuenta bancaria fue registrada correctamente.",
        )}
      `,
    );

    formulario.reset();

    inputBalance.value = "0.00";

    paginaActualCuentas = 1;

    await Promise.all([cargarKpiCuentas(), cargarCuentasBancarias()]);

    setTimeout(function () {
      const modal = document.getElementById("modalRegistrarCuenta");

      if (modal) {
        const instancia =
          bootstrap.Modal.getInstance(modal) ||
          bootstrap.Modal.getOrCreateInstance(modal);

        instancia.hide();
      }

      ocultarMensajeRegistrarCuenta();
    }, 900);
  } catch (error) {
    console.error("Error al registrar cuenta bancaria:", error);

    mostrarMensajeRegistrarCuenta(
      "danger",
      `
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        ${escaparHTML(
          error.message || "Ocurrió un error al registrar la cuenta.",
        )}
      `,
    );
  } finally {
    registrandoCuenta = false;

    if (boton) {
      boton.disabled = false;

      boton.innerHTML =
        boton.dataset.textoOriginal ||
        `
          <i class="bi bi-check-circle me-1"></i>
          Registrar cuenta
        `;
    }
  }
}

//=====================================================
// ACTUALIZAR CUENTA BANCARIA
//=====================================================

async function actualizarCuentaBancaria() {
  if (actualizandoCuenta) {
    return;
  }

  const formulario = document.getElementById("formEditarCuenta");

  const inputId = document.getElementById("idCuentaBancariaEditar");

  const inputNombre = document.getElementById("nombreCuentaEditar");

  const inputBalance = document.getElementById("balanceCuentaEditar");

  const selectEstado = document.getElementById("estadoCuentaEditar");

  const boton =
    document.getElementById("btnGuardarCambiosCuenta") ||
    document.getElementById("btnEditarCuenta") ||
    formulario?.querySelector('button[type="submit"]');

  if (
    !formulario ||
    !inputId ||
    !inputNombre ||
    !inputBalance ||
    !selectEstado
  ) {
    console.error(
      "No se encontraron los elementos necesarios para actualizar la cuenta.",
    );

    return;
  }

  const idCuenta = parseInt(inputId.value, 10);

  const nombre = inputNombre.value.trim();

  const balance = inputBalance.value.trim();

  const estado = parseInt(selectEstado.value, 10);

  quitarErrorCampo(inputId);

  quitarErrorCampo(inputNombre);

  quitarErrorCampo(inputBalance);

  quitarErrorCampo(selectEstado);

  ocultarMensajeEditarCuenta();

  //=================================================
  // VALIDAR ID
  //=================================================

  if (!Number.isInteger(idCuenta) || idCuenta <= 0) {
    mostrarMensajeEditarCuenta(
      "danger",
      `
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        El ID de la cuenta bancaria no es válido.
      `,
    );

    return;
  }

  //=================================================
  // VALIDAR NOMBRE
  //=================================================

  if (nombre === "") {
    marcarErrorCampo(inputNombre, "Ingresa el nombre de la cuenta bancaria.");

    inputNombre.focus();

    return;
  }

  if (nombre.length < 2) {
    marcarErrorCampo(
      inputNombre,
      "El nombre de la cuenta debe tener al menos 2 caracteres.",
    );

    inputNombre.focus();

    return;
  }

  if (nombre.length > 100) {
    marcarErrorCampo(
      inputNombre,
      "El nombre de la cuenta no puede superar los 100 caracteres.",
    );

    inputNombre.focus();

    return;
  }

  //=================================================
  // VALIDAR BALANCE
  //=================================================

  if (balance === "") {
    marcarErrorCampo(inputBalance, "Ingresa el balance de la cuenta.");

    inputBalance.focus();

    return;
  }

  const balanceNormalizado = balance.replace(",", ".");

  const balanceNumero = Number(balanceNormalizado);

  if (!Number.isFinite(balanceNumero)) {
    marcarErrorCampo(inputBalance, "El balance ingresado no es válido.");

    inputBalance.focus();

    return;
  }

  if (balanceNumero < 0) {
    marcarErrorCampo(inputBalance, "El balance no puede ser negativo.");

    inputBalance.focus();

    return;
  }

  const partesBalance = balanceNormalizado.split(".");

  if (partesBalance.length === 2 && partesBalance[1].length > 2) {
    marcarErrorCampo(
      inputBalance,
      "El balance solo puede tener hasta 2 decimales.",
    );

    inputBalance.focus();

    return;
  }

  //=================================================
  // VALIDAR ESTADO
  //=================================================

  if (estado !== 0 && estado !== 1) {
    marcarErrorCampo(selectEstado, "Selecciona un estado válido.");

    selectEstado.focus();

    return;
  }

  //=================================================
  // ACTIVAR PROCESO
  //=================================================

  actualizandoCuenta = true;

  inputNombre.disabled = true;

  inputBalance.disabled = true;

  selectEstado.disabled = true;

  if (boton) {
    boton.disabled = true;

    boton.dataset.textoOriginal = boton.innerHTML;

    boton.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-1"
        role="status"
        aria-hidden="true">
      </span>
      Guardando...
    `;
  }

  mostrarMensajeEditarCuenta(
    "info",
    `
      <i class="bi bi-hourglass-split me-1"></i>
      Actualizando la cuenta bancaria...
    `,
  );

  try {
    const datos = new FormData();

    datos.append("id_cuenta_bancaria", String(idCuenta));

    datos.append("nombre", nombre);

    datos.append("balance", balanceNumero.toFixed(2));

    datos.append("estado", String(estado));

    const respuesta = await fetch(URL_EDITAR_CUENTA, {
      method: "POST",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },

      body: datos,

      cache: "no-store",
    });

    const textoRespuesta = await respuesta.text();

    let datosRespuesta;

    try {
      datosRespuesta = JSON.parse(textoRespuesta);
    } catch (errorJSON) {
      console.error("Respuesta no válida del servidor:", textoRespuesta);

      throw new Error("El servidor devolvió una respuesta no válida.");
    }

    if (!respuesta.ok) {
      throw new Error(
        datosRespuesta?.mensaje || "Ocurrió un error en el servidor.",
      );
    }

    if (!datosRespuesta || datosRespuesta.success !== true) {
      throw new Error(
        datosRespuesta?.mensaje || "No se pudo actualizar la cuenta bancaria.",
      );
    }

    mostrarMensajeEditarCuenta(
      "success",
      `
        <i class="bi bi-check-circle-fill me-1"></i>
        ${escaparHTML(
          datosRespuesta.mensaje ||
            "La cuenta bancaria fue actualizada correctamente.",
        )}
      `,
    );

    paginaActualCuentas = 1;

    await Promise.all([cargarKpiCuentas(), cargarCuentasBancarias()]);

    setTimeout(function () {
      const modal = document.getElementById("modalEditarCuenta");

      if (modal) {
        const instancia =
          bootstrap.Modal.getInstance(modal) ||
          bootstrap.Modal.getOrCreateInstance(modal);

        instancia.hide();
      }

      ocultarMensajeEditarCuenta();
    }, 900);
  } catch (error) {
    console.error("Error al actualizar cuenta bancaria:", error);

    mostrarMensajeEditarCuenta(
      "danger",
      `
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        ${escaparHTML(
          error.message || "Ocurrió un error al actualizar la cuenta bancaria.",
        )}
      `,
    );
  } finally {
    actualizandoCuenta = false;

    inputNombre.disabled = false;

    inputBalance.disabled = false;

    selectEstado.disabled = false;

    if (boton) {
      boton.disabled = false;

      boton.innerHTML =
        boton.dataset.textoOriginal ||
        `
          <i class="bi bi-check-circle me-1"></i>
          Guardar cambios
        `;
    }
  }
}

//=====================================================
// MENSAJE REGISTRAR
//=====================================================

function mostrarMensajeRegistrarCuenta(tipo, mensaje) {
  const contenedor = document.getElementById("mensajeRegistrarCuenta");

  if (!contenedor) {
    return;
  }

  contenedor.className = "alert alert-" + tipo + " mt-3";

  contenedor.innerHTML = mensaje;

  contenedor.style.display = "block";
}

function ocultarMensajeRegistrarCuenta() {
  const contenedor = document.getElementById("mensajeRegistrarCuenta");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = "";

  contenedor.style.display = "none";
}

//=====================================================
// MENSAJE EDITAR
//=====================================================

function mostrarMensajeEditarCuenta(tipo, mensaje) {
  const contenedor = document.getElementById("mensajeEditarCuenta");

  if (!contenedor) {
    return;
  }

  contenedor.className = "alert alert-" + tipo + " mt-3";

  contenedor.innerHTML = mensaje;

  contenedor.style.display = "block";
}

function ocultarMensajeEditarCuenta() {
  const contenedor = document.getElementById("mensajeEditarCuenta");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = "";

  contenedor.style.display = "none";
}

//=====================================================
// MARCAR ERROR
//=====================================================

function marcarErrorCampo(elemento, mensaje) {
  if (!elemento) {
    return;
  }

  elemento.classList.add("is-invalid");

  let feedback = elemento.parentElement?.querySelector(".invalid-feedback");

  if (!feedback) {
    feedback = document.createElement("div");

    feedback.className = "invalid-feedback";

    if (elemento.parentElement) {
      elemento.parentElement.appendChild(feedback);
    }
  }

  feedback.textContent = mensaje;
}

//=====================================================
// QUITAR ERROR
//=====================================================

function quitarErrorCampo(elemento) {
  if (!elemento) {
    return;
  }

  elemento.classList.remove("is-invalid");

  const feedback = elemento.parentElement?.querySelector(".invalid-feedback");

  if (feedback) {
    feedback.remove();
  }
}

//=====================================================
// CARGAR KPI
//=====================================================

async function cargarKpiCuentas() {
  if (solicitudKpiActual) {
    solicitudKpiActual.abort();
  }

  solicitudKpiActual = new AbortController();

  const solicitud = solicitudKpiActual;

  try {
    const respuesta = await fetch(URL_OBTENER_KPI, {
      method: "GET",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },

      signal: solicitud.signal,

      cache: "no-store",
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos || datos.success !== true) {
      throw new Error(datos?.mensaje || "No se pudieron obtener los KPI.");
    }

    actualizarKpiCuentas(datos);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar KPI de cuentas:", error);

    mostrarErrorKpi();
  }
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKpiCuentas(datos) {
  const kpiTotal = document.getElementById("kpiTotalCuentas");

  const kpiActivas = document.getElementById("kpiCuentasActivas");

  const kpiInactivas = document.getElementById("kpiCuentasInactivas");

  const kpiSaldo = document.getElementById("kpiSaldoTotal");

  const total = obtenerNumeroSeguro(datos.total_cuentas ?? datos.total ?? 0);

  const activas = obtenerNumeroSeguro(
    datos.cuentas_activas ?? datos.activas ?? 0,
  );

  const inactivas = obtenerNumeroSeguro(
    datos.cuentas_inactivas ?? datos.inactivas ?? 0,
  );

  const saldo = obtenerNumeroSeguro(
    datos.saldo_total ?? datos.balance_total ?? 0,
  );

  if (kpiTotal) {
    kpiTotal.textContent = formatearNumero(total);
  }

  if (kpiActivas) {
    kpiActivas.textContent = formatearNumero(activas);
  }

  if (kpiInactivas) {
    kpiInactivas.textContent = formatearNumero(inactivas);
  }

  if (kpiSaldo) {
    kpiSaldo.textContent = formatearMoneda(saldo);
  }
}

//=====================================================
// ERROR KPI
//=====================================================

function mostrarErrorKpi() {
  const elementos = [
    "kpiTotalCuentas",
    "kpiCuentasActivas",
    "kpiCuentasInactivas",
    "kpiSaldoTotal",
  ];

  elementos.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (!elemento) {
      return;
    }

    if (id === "kpiSaldoTotal") {
      elemento.textContent = "S/. 0.00";
    } else {
      elemento.textContent = "0";
    }
  });
}

//=====================================================
// CARGAR CUENTAS
//=====================================================

async function cargarCuentasBancarias() {
  //=================================================
  // CANCELAR SOLICITUD ANTERIOR
  //=================================================

  if (solicitudCuentasActual) {
    solicitudCuentasActual.abort();
  }

  solicitudCuentasActual = new AbortController();

  const solicitud = solicitudCuentasActual;

  const filtros = obtenerFiltrosCuentas();

  mostrarLoadingTabla();

  try {
    const parametros = new URLSearchParams();

    parametros.append("pagina", String(paginaActualCuentas));

    parametros.append("registrosPorPagina", String(registrosPorPaginaCuentas));

    parametros.append("buscar", filtros.buscar);

    parametros.append("estado", filtros.estado);

    parametros.append("orden", filtros.orden);

    const respuesta = await fetch(
      URL_OBTENER_CUENTAS + "?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        signal: solicitud.signal,

        cache: "no-store",
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos || datos.success !== true) {
      throw new Error(datos?.mensaje || "No se pudieron obtener las cuentas.");
    }

    totalRegistrosCuentas = obtenerNumeroSeguro(
      datos.total_registros ?? datos.total ?? 0,
    );

    totalPaginasCuentas = obtenerNumeroSeguro(
      datos.total_paginas ??
        datos.paginas ??
        calcularTotalPaginas(totalRegistrosCuentas),
    );

    if (totalPaginasCuentas < 1) {
      totalPaginasCuentas = 1;
    }

    if (paginaActualCuentas > totalPaginasCuentas) {
      paginaActualCuentas = totalPaginasCuentas;

      return cargarCuentasBancarias();
    }

    const cuentas = Array.isArray(datos.cuentas) ? datos.cuentas : [];

    renderizarTablaCuentas(cuentas);

    actualizarInformacionRegistros();

    renderizarPaginacion();
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar cuentas bancarias:", error);

    mostrarErrorTabla(error.message);

    actualizarInformacionRegistros(true);
  }
}

//=====================================================
// OBTENER FILTROS
//=====================================================

function obtenerFiltrosCuentas() {
  const inputBuscar = document.getElementById("buscarCuenta");

  const filtroEstado = document.getElementById("filtroEstadoCuenta");

  const orden = document.getElementById("ordenCuentas");

  return {
    buscar: inputBuscar ? inputBuscar.value.trim() : "",

    estado: filtroEstado ? filtroEstado.value : "",

    orden: orden ? orden.value : "nombre_asc",
  };
}

//=====================================================
// RENDERIZAR TABLA
//=====================================================

function renderizarTablaCuentas(cuentas) {
  const tabla = document.getElementById("tablaCuentasBancarias");

  if (!tabla) {
    return;
  }

  if (!Array.isArray(cuentas) || cuentas.length === 0) {
    tabla.innerHTML = `
      <tr>
        <td
          colspan="4"
          class="text-center cuentas-empty-state">

          <div class="cuentas-empty-icon">
            <i class="bi bi-bank"></i>
          </div>

          <h6 class="mb-2">
            No se encontraron cuentas
          </h6>

          <p class="mb-3">
            No existen cuentas que coincidan
            con los filtros seleccionados.
          </p>

          <button
            type="button"
            class="btn btn-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#modalRegistrarCuenta">

            <i class="bi bi-plus-circle me-1"></i>

            Registrar cuenta

          </button>

        </td>
      </tr>
    `;

    return;
  }

  tabla.innerHTML = cuentas.map(construirFilaCuenta).join("");
}

//=====================================================
// CONSTRUIR FILA
//=====================================================

function construirFilaCuenta(cuenta) {
  const id = escaparHTML(cuenta.id_cuenta_bancaria ?? cuenta.id_cuenta ?? "");

  const nombre = escaparHTML(cuenta.nombre ?? "Sin nombre");

  const balance = obtenerNumeroSeguro(cuenta.balance ?? 0);

  const eliminado = String(cuenta.Eliminado ?? cuenta.eliminado ?? 0);

  const activa = eliminado === "0";

  const estadoHTML = activa
    ? `
        <span
          class="cuenta-estado-badge cuenta-estado-activa">

          <i class="bi bi-circle-fill"></i>

          ACTIVA

        </span>
      `
    : `
        <span
          class="cuenta-estado-badge cuenta-estado-inactiva">

          <i class="bi bi-circle-fill"></i>

          INACTIVA

        </span>
      `;

  const accionesHTML = activa
    ? `
        <div class="cuenta-acciones">

          <button
            type="button"
            class="btn-cuenta-accion btn-cuenta-editar"
            data-accion="editar"
            data-id="${id}"
            title="Editar cuenta"
            aria-label="Editar cuenta">

            <i class="bi bi-pencil-square"></i>

          </button>

          <button
            type="button"
            class="btn-cuenta-accion btn-cuenta-eliminar"
            data-accion="eliminar"
            data-id="${id}"
            title="Desactivar cuenta"
            aria-label="Desactivar cuenta">

            <i class="bi bi-trash"></i>

          </button>

        </div>
      `
    : `
        <div class="cuenta-acciones">

          <!-- <button
            type="button"
            class="btn-cuenta-accion btn-cuenta-ver"
            data-accion="ver"
            data-id="${id}"
            title="Ver movimientos"
            aria-label="Ver movimientos">

            <i class="bi bi-eye"></i>

          </button>-->

          <button
            type="button"
            class="btn-cuenta-accion btn-cuenta-editar"
            data-accion="editar"
            data-id="${id}"
            title="Editar cuenta"
            aria-label="Editar cuenta">

            <i class="bi bi-pencil-square"></i>

          </button>

          <button
            type="button"
            class="btn-cuenta-accion btn-cuenta-restaurar"
            data-accion="restaurar"
            data-id="${id}"
            title="Activar cuenta"
            aria-label="Activar cuenta">

            <i class="bi bi-arrow-counterclockwise"></i>

          </button>

        </div>
      `;

  return `
    <tr
      data-id-cuenta="${id}">

      <td>

        <div class="cuenta-info">

          <div class="cuenta-icono">

            <i class="bi bi-bank"></i>

          </div>

          <div>

            <p class="cuenta-nombre">
              ${nombre}
            </p>

            <div class="cuenta-id">
              ID: ${id}
            </div>

          </div>

        </div>

      </td>

      <td>

        <span class="cuenta-balance">
          ${formatearMoneda(balance)}
        </span>

      </td>

      <td>
        ${estadoHTML}
      </td>

      <td class="text-end">
        ${accionesHTML}
      </td>

    </tr>
  `;
}

//=====================================================
// MANEJAR ACCIONES
//=====================================================

function manejarAccionesCuenta(evento) {
  const boton = evento.target.closest("[data-accion]");

  if (!boton) {
    return;
  }

  const accion = boton.dataset.accion;

  const id = boton.dataset.id;

  if (!id) {
    return;
  }

  switch (accion) {
    case "ver":
      verCuenta(id);

      break;

    case "editar":
      editarCuenta(id);

      break;

    case "eliminar":
      abrirModalCambiarEstado(id, "desactivar");

      break;

    case "restaurar":
      abrirModalCambiarEstado(id, "activar");

      break;

    default:
      console.warn("Acción de cuenta no reconocida:", accion);

      break;
  }
}

//=====================================================
// VER CUENTA
//=====================================================

function verCuenta(idCuenta) {
  console.log("Ver movimientos de cuenta:", idCuenta);
}

//=====================================================
// EDITAR CUENTA
//=====================================================

async function editarCuenta(idCuenta) {
  if (cargandoCuentaEditar) {
    return;
  }

  const modal = document.getElementById("modalEditarCuenta");

  if (!modal) {
    console.warn("No existe #modalEditarCuenta.");

    return;
  }

  const inputId = document.getElementById("idCuentaBancariaEditar");

  const inputNombre = document.getElementById("nombreCuentaEditar");

  const inputBalance = document.getElementById("balanceCuentaEditar");

  const selectEstado = document.getElementById("estadoCuentaEditar");

  if (!inputId || !inputNombre || !inputBalance || !selectEstado) {
    console.error("No se encontraron los campos del modal de edición.");

    return;
  }

  if (solicitudCuentaEditarActual) {
    solicitudCuentaEditarActual.abort();
  }

  solicitudCuentaEditarActual = new AbortController();

  const solicitud = solicitudCuentaEditarActual;

  cargandoCuentaEditar = true;

  inputId.value = idCuenta;

  inputNombre.value = "";

  inputBalance.value = "0.00";

  selectEstado.value = "0";

  ocultarMensajeEditarCuenta();

  inputNombre.disabled = true;

  inputBalance.disabled = true;

  selectEstado.disabled = true;

  mostrarMensajeEditarCuenta(
    "info",
    `
      <span
        class="spinner-border spinner-border-sm me-1"
        role="status"
        aria-hidden="true">
      </span>

      Cargando información de la cuenta...
    `,
  );

  const instancia = bootstrap.Modal.getOrCreateInstance(modal);

  instancia.show();

  try {
    const parametros = new URLSearchParams();

    parametros.append("id_cuenta_bancaria", idCuenta);

    const respuesta = await fetch(
      URL_OBTENER_CUENTA + "?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        signal: solicitud.signal,

        cache: "no-store",
      },
    );

    const textoRespuesta = await respuesta.text();

    let datos;

    try {
      datos = JSON.parse(textoRespuesta);
    } catch (errorJSON) {
      console.error("Respuesta no válida:", textoRespuesta);

      throw new Error("El servidor devolvió una respuesta no válida.");
    }

    if (!respuesta.ok) {
      throw new Error(datos?.mensaje || "Error HTTP " + respuesta.status);
    }

    if (!datos || datos.success !== true) {
      throw new Error(
        datos?.mensaje || "No se pudo obtener la cuenta bancaria.",
      );
    }

    const cuenta = datos.cuenta;

    if (!cuenta) {
      throw new Error("El servidor no devolvió la información de la cuenta.");
    }

    inputId.value = cuenta.id_cuenta_bancaria ?? idCuenta;

    inputNombre.value = cuenta.nombre ?? "";

    const balance = obtenerNumeroSeguro(cuenta.balance ?? 0);

    inputBalance.value = balance.toFixed(2);

    selectEstado.value = String(cuenta.Eliminado ?? cuenta.eliminado ?? 0);

    ocultarMensajeEditarCuenta();
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al obtener cuenta bancaria:", error);

    mostrarMensajeEditarCuenta(
      "danger",
      `
        <i class="bi bi-exclamation-triangle-fill me-1"></i>

        ${escaparHTML(
          error.message || "No se pudo cargar la información de la cuenta.",
        )}
      `,
    );
  } finally {
    cargandoCuentaEditar = false;

    inputNombre.disabled = false;

    inputBalance.disabled = false;

    selectEstado.disabled = false;
  }
}

//=====================================================
// INICIALIZAR MODAL CAMBIAR ESTADO
//=====================================================

function inicializarModalCambiarEstado() {
  const botonConfirmar = document.getElementById(
    "btnConfirmarCambiarEstadoCuenta",
  );

  if (botonConfirmar) {
    botonConfirmar.addEventListener("click", function () {
      confirmarCambioEstadoCuenta();
    });
  }

  const modal = document.getElementById("modalCambiarEstadoCuenta");

  if (modal) {
    modal.addEventListener("hidden.bs.modal", function () {
      limpiarModalCambiarEstado();
    });
  }
}

//=====================================================
// ABRIR MODAL CAMBIAR ESTADO
//=====================================================

async function abrirModalCambiarEstado(idCuenta, tipo) {
  const id = parseInt(idCuenta, 10);

  if (!Number.isInteger(id) || id <= 0) {
    console.error("ID de cuenta no válido:", idCuenta);

    return;
  }

  if (tipo !== "activar" && tipo !== "desactivar") {
    console.error("Tipo de cambio de estado no válido:", tipo);

    return;
  }

  const modal = document.getElementById("modalCambiarEstadoCuenta");

  if (!modal) {
    console.error("No existe el modal #modalCambiarEstadoCuenta.");

    return;
  }

  idCuentaCambioEstado = id;

  tipoCambioEstadoCuenta = tipo;

  configurarModalCambiarEstado(tipo);

  //=================================================
  // OBTENER DATOS DE LA FILA
  //=================================================

  const fila = document.querySelector(`tr[data-id-cuenta="${id}"]`);

  if (fila) {
    const nombre = fila.querySelector(".cuenta-nombre");

    const elementoID = document.getElementById("idCuentaCambiarEstado");

    const elementoNombre = document.getElementById("nombreCuentaCambiarEstado");

    if (elementoNombre) {
      elementoNombre.textContent = nombre
        ? nombre.textContent.trim()
        : "Cuenta bancaria";
    }

    if (elementoID) {
      elementoID.textContent = String(id);
    }
  }

  const instancia = bootstrap.Modal.getOrCreateInstance(modal);

  instancia.show();
}

//=====================================================
// CONFIGURAR MODAL CAMBIAR ESTADO
//=====================================================

function configurarModalCambiarEstado(tipo) {
  const titulo = document.getElementById("textoTituloCambiarEstadoCuenta");

  const alerta = document.getElementById("alertaCambiarEstadoCuenta");

  const mensaje = document.getElementById("mensajeCambiarEstadoCuenta");

  const boton = document.getElementById("btnConfirmarCambiarEstadoCuenta");

  const textoBoton = document.getElementById(
    "textoConfirmarCambiarEstadoCuenta",
  );

  const iconoTitulo = document.getElementById("iconoModalCambiarEstadoCuenta");

  const iconoBoton = document.getElementById(
    "iconoConfirmarCambiarEstadoCuenta",
  );

  if (tipo === "desactivar") {
    if (titulo) {
      titulo.textContent = "Desactivar cuenta";
    }

    if (mensaje) {
      mensaje.textContent =
        "¿Deseas desactivar esta cuenta bancaria? La cuenta dejará de aparecer como activa.";
    }

    if (textoBoton) {
      textoBoton.textContent = "Desactivar";
    }

    if (boton) {
      boton.className = "btn btn-warning";
    }

    if (iconoTitulo) {
      iconoTitulo.className = "bi bi-exclamation-circle me-2";
    }

    if (iconoBoton) {
      iconoBoton.className = "bi bi-pause-circle me-1";
    }

    if (alerta) {
      alerta.className = "alert alert-warning";
    }
  } else {
    if (titulo) {
      titulo.textContent = "Activar cuenta";
    }

    if (mensaje) {
      mensaje.textContent = "¿Deseas activar nuevamente esta cuenta bancaria?";
    }

    if (textoBoton) {
      textoBoton.textContent = "Activar";
    }

    if (boton) {
      boton.className = "btn btn-success";
    }

    if (iconoTitulo) {
      iconoTitulo.className = "bi bi-check-circle me-2";
    }

    if (iconoBoton) {
      iconoBoton.className = "bi bi-check-circle me-1";
    }

    if (alerta) {
      alerta.className = "alert alert-success";
    }
  }

  if (alerta) {
    alerta.classList.remove("d-none");
  }

  ocultarMensajeCambioEstado();
}

//=====================================================
// CONFIRMAR CAMBIO ESTADO
//=====================================================

async function confirmarCambioEstadoCuenta() {
  if (cambiandoEstadoCuenta) {
    return;
  }

  const id = parseInt(idCuentaCambioEstado, 10);

  if (!Number.isInteger(id) || id <= 0) {
    mostrarMensajeCambioEstado(
      "danger",
      `
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        El ID de la cuenta no es válido.
      `,
    );

    return;
  }

  if (
    tipoCambioEstadoCuenta !== "activar" &&
    tipoCambioEstadoCuenta !== "desactivar"
  ) {
    return;
  }

  const boton = document.getElementById("btnConfirmarCambiarEstadoCuenta");

  const textoBoton = document.getElementById(
    "textoConfirmarCambiarEstadoCuenta",
  );

  const iconoBoton = document.getElementById(
    "iconoConfirmarCambiarEstadoCuenta",
  );

  const url =
    tipoCambioEstadoCuenta === "activar"
      ? URL_RESTAURAR_CUENTA
      : URL_ELIMINAR_CUENTA;

  const textoOriginal = textoBoton ? textoBoton.textContent : "Confirmar";

  const iconoOriginal = iconoBoton ? iconoBoton.className : "";

  cambiandoEstadoCuenta = true;

  if (boton) {
    boton.disabled = true;
  }

  if (iconoBoton) {
    iconoBoton.className = "spinner-border spinner-border-sm me-1";
  }

  if (textoBoton) {
    textoBoton.textContent =
      tipoCambioEstadoCuenta === "activar" ? "Activando..." : "Desactivando...";
  }

  mostrarMensajeCambioEstado(
    "info",
    `
      <span
        class="spinner-border spinner-border-sm me-1"
        role="status"
        aria-hidden="true">
      </span>

      Procesando cambio de estado...
    `,
  );

  try {
    const datos = new FormData();

    datos.append("id_cuenta_bancaria", String(id));

    const respuesta = await fetch(url, {
      method: "POST",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },

      body: datos,

      cache: "no-store",
    });

    const textoRespuesta = await respuesta.text();

    let resultado;

    try {
      resultado = JSON.parse(textoRespuesta);
    } catch (errorJSON) {
      console.error("Respuesta no válida:", textoRespuesta);

      throw new Error("El servidor devolvió una respuesta no válida.");
    }

    if (!respuesta.ok) {
      throw new Error(resultado?.mensaje || "Error HTTP " + respuesta.status);
    }

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje ||
          (tipoCambioEstadoCuenta === "activar"
            ? "No se pudo activar la cuenta bancaria."
            : "No se pudo desactivar la cuenta bancaria."),
      );
    }

    mostrarMensajeCambioEstado(
      "success",
      `
        <i class="bi bi-check-circle-fill me-1"></i>

        ${escaparHTML(
          resultado.mensaje ||
            (tipoCambioEstadoCuenta === "activar"
              ? "La cuenta bancaria fue activada correctamente."
              : "La cuenta bancaria fue desactivada correctamente."),
        )}
      `,
    );

    paginaActualCuentas = 1;

    await Promise.all([cargarKpiCuentas(), cargarCuentasBancarias()]);

    //=================================================
    // CERRAR MODAL
    //=================================================

    setTimeout(function () {
      const modal = document.getElementById("modalCambiarEstadoCuenta");

      if (modal) {
        const instancia =
          bootstrap.Modal.getInstance(modal) ||
          bootstrap.Modal.getOrCreateInstance(modal);

        instancia.hide();
      }
    }, 800);
  } catch (error) {
    console.error("Error al cambiar estado de cuenta:", error);

    mostrarMensajeCambioEstado(
      "danger",
      `
        <i class="bi bi-exclamation-triangle-fill me-1"></i>

        ${escaparHTML(
          error.message ||
            "Ocurrió un error al cambiar el estado de la cuenta.",
        )}
      `,
    );
  } finally {
    cambiandoEstadoCuenta = false;

    if (boton) {
      boton.disabled = false;
    }

    if (iconoBoton) {
      iconoBoton.className = iconoOriginal || "bi bi-check-circle me-1";
    }

    if (textoBoton) {
      textoBoton.textContent = textoOriginal || "Confirmar";
    }
  }
}

//=====================================================
// MENSAJE CAMBIO ESTADO
//=====================================================

function mostrarMensajeCambioEstado(tipo, mensaje) {
  const contenedor = document.getElementById(
    "mensajeProcesoCambiarEstadoCuenta",
  );

  if (!contenedor) {
    return;
  }

  contenedor.className = "alert alert-" + tipo + " mt-3";

  contenedor.innerHTML = mensaje;

  contenedor.classList.remove("d-none");
}

//=====================================================
// OCULTAR MENSAJE CAMBIO ESTADO
//=====================================================

function ocultarMensajeCambioEstado() {
  const contenedor = document.getElementById(
    "mensajeProcesoCambiarEstadoCuenta",
  );

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = "";

  contenedor.className = "alert mt-3 d-none";
}

//=====================================================
// LIMPIAR MODAL CAMBIO ESTADO
//=====================================================

function limpiarModalCambiarEstado() {
  idCuentaCambioEstado = null;

  tipoCambioEstadoCuenta = null;

  cambiandoEstadoCuenta = false;

  const nombre = document.getElementById("nombreCuentaCambiarEstado");

  const id = document.getElementById("idCuentaCambiarEstado");

  const boton = document.getElementById("btnConfirmarCambiarEstadoCuenta");

  const textoBoton = document.getElementById(
    "textoConfirmarCambiarEstadoCuenta",
  );

  const iconoBoton = document.getElementById(
    "iconoConfirmarCambiarEstadoCuenta",
  );

  if (nombre) {
    nombre.textContent = "Cuenta bancaria";
  }

  if (id) {
    id.textContent = "-";
  }

  if (boton) {
    boton.disabled = false;

    boton.className = "btn btn-warning";
  }

  if (textoBoton) {
    textoBoton.textContent = "Confirmar";
  }

  if (iconoBoton) {
    iconoBoton.className = "bi bi-check-circle me-1";
  }

  ocultarMensajeCambioEstado();
}

//=====================================================
// COMPATIBILIDAD
//=====================================================

async function eliminarCuenta(idCuenta) {
  abrirModalCambiarEstado(idCuenta, "desactivar");
}

async function restaurarCuenta(idCuenta) {
  abrirModalCambiarEstado(idCuenta, "activar");
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosCuentas() {
  const buscar = document.getElementById("buscarCuenta");

  const estado = document.getElementById("filtroEstadoCuenta");

  const orden = document.getElementById("ordenCuentas");

  if (buscar) {
    buscar.value = "";
  }

  if (estado) {
    estado.value = "";
  }

  if (orden) {
    orden.value = "nombre_asc";
  }

  paginaActualCuentas = 1;

  cargarCuentasBancarias();
}

//=====================================================
// LOADING TABLA
//=====================================================

function mostrarLoadingTabla() {
  const tabla = document.getElementById("tablaCuentasBancarias");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `
    <tr>

      <td
        colspan="4"
        class="cuentas-loading">

        <div
          class="spinner-border text-primary"
          role="status">

          <span class="visually-hidden">
            Cargando...
          </span>

        </div>

        <div class="mt-3">
          Cargando cuentas bancarias...
        </div>

      </td>

    </tr>
  `;
}

//=====================================================
// ERROR TABLA
//=====================================================

function mostrarErrorTabla(mensaje = "") {
  const tabla = document.getElementById("tablaCuentasBancarias");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `
    <tr>

      <td
        colspan="4"
        class="text-center py-5">

        <div class="text-danger mb-3">

          <i
            class="bi bi-exclamation-triangle-fill fs-2">
          </i>

        </div>

        <h6 class="fw-bold">
          No se pudieron cargar las cuentas
        </h6>

        <p class="text-muted small mb-3">

          ${escaparHTML(
            mensaje || "Ocurrió un error al consultar la información.",
          )}

        </p>

        <button
          type="button"
          class="btn btn-outline-primary btn-sm"
          onclick="cargarCuentasBancarias()">

          <i class="bi bi-arrow-clockwise me-1"></i>

          Intentar nuevamente

        </button>

      </td>

    </tr>
  `;
}

//=====================================================
// INFORMACIÓN REGISTROS
//=====================================================

function actualizarInformacionRegistros(error = false) {
  const registros = document.getElementById("registrosMostrados");

  const texto = document.getElementById("textoRegistros");

  if (error) {
    if (registros) {
      registros.textContent = "0";
    }

    if (texto) {
      texto.textContent = "No se pudieron cargar los registros";
    }

    return;
  }

  const inicio =
    totalRegistrosCuentas === 0
      ? 0
      : (paginaActualCuentas - 1) * registrosPorPaginaCuentas + 1;

  const fin = Math.min(
    paginaActualCuentas * registrosPorPaginaCuentas,
    totalRegistrosCuentas,
  );

  const cantidadPagina = totalRegistrosCuentas === 0 ? 0 : fin - inicio + 1;

  if (registros) {
    registros.textContent = cantidadPagina;
  }

  if (texto) {
    texto.textContent = `Mostrando ${inicio} - ${fin} de ${totalRegistrosCuentas} cuentas`;
  }
}

//=====================================================
// CALCULAR PÁGINAS
//=====================================================

function calcularTotalPaginas(total) {
  if (!total || total <= 0) {
    return 1;
  }

  return Math.ceil(total / registrosPorPaginaCuentas);
}

//=====================================================
// PAGINACIÓN
//=====================================================

function renderizarPaginacion() {
  const contenedor = document.getElementById("paginacionCuentas");

  if (!contenedor) {
    return;
  }

  const total = totalPaginasCuentas;

  let html = "";

  html += `
    <li
      class="page-item ${paginaActualCuentas <= 1 ? "disabled" : ""}">

      <button
        class="page-link"
        type="button"
        data-pagina="${paginaActualCuentas - 1}"
        ${paginaActualCuentas <= 1 ? "disabled" : ""}>

        <i class="bi bi-chevron-left"></i>

      </button>

    </li>
  `;

  const paginas = obtenerPaginasVisibles(paginaActualCuentas, total);

  paginas.forEach(function (pagina) {
    if (pagina === "...") {
      html += `
          <li class="page-item disabled">

            <span class="page-link">
              ...
            </span>

          </li>
        `;

      return;
    }

    html += `
        <li
          class="page-item ${pagina === paginaActualCuentas ? "active" : ""}">

          <button
            class="page-link"
            type="button"
            data-pagina="${pagina}">

            ${pagina}

          </button>

        </li>
      `;
  });

  html += `
    <li
      class="page-item ${paginaActualCuentas >= total ? "disabled" : ""}">

      <button
        class="page-link"
        type="button"
        data-pagina="${paginaActualCuentas + 1}"
        ${paginaActualCuentas >= total ? "disabled" : ""}>

        <i class="bi bi-chevron-right"></i>

      </button>

    </li>
  `;

  contenedor.innerHTML = html;

  contenedor.querySelectorAll("[data-pagina]").forEach(function (boton) {
    boton.addEventListener("click", function () {
      const pagina = parseInt(this.dataset.pagina, 10);

      if (
        !pagina ||
        pagina < 1 ||
        pagina > total ||
        pagina === paginaActualCuentas
      ) {
        return;
      }

      paginaActualCuentas = pagina;

      cargarCuentasBancarias();

      const tabla = document.querySelector(".cuentas-table-wrapper");

      if (tabla) {
        tabla.scrollIntoView({
          behavior: "smooth",

          block: "start",
        });
      }
    });
  });
}

//=====================================================
// PÁGINAS VISIBLES
//=====================================================

function obtenerPaginasVisibles(actual, total) {
  if (total <= 7) {
    return Array.from(
      {
        length: total,
      },
      function (_, indice) {
        return indice + 1;
      },
    );
  }

  const paginas = [];

  paginas.push(1);

  if (actual > 4) {
    paginas.push("...");
  }

  const inicio = Math.max(2, actual - 1);

  const fin = Math.min(total - 1, actual + 1);

  for (let pagina = inicio; pagina <= fin; pagina++) {
    paginas.push(pagina);
  }

  if (actual < total - 3) {
    paginas.push("...");
  }

  paginas.push(total);

  return paginas;
}

//=====================================================
// EVENTOS MODALES
//=====================================================

function inicializarEventosModales() {
  const modalRegistrar = document.getElementById("modalRegistrarCuenta");

  if (modalRegistrar) {
    modalRegistrar.addEventListener("hidden.bs.modal", function () {
      limpiarFormularioModal(modalRegistrar);
    });
  }

  const modalEditar = document.getElementById("modalEditarCuenta");

  if (modalEditar) {
    modalEditar.addEventListener("hidden.bs.modal", function () {
      limpiarFormularioModal(modalEditar);
    });
  }
}

//=====================================================
// LIMPIAR FORMULARIO MODAL
//=====================================================

function limpiarFormularioModal(modal) {
  if (!modal) {
    return;
  }

  const formulario = modal.querySelector("form");

  if (!formulario) {
    return;
  }

  formulario.reset();

  const balanceRegistrar = formulario.querySelector("#balanceCuenta");

  if (balanceRegistrar) {
    balanceRegistrar.value = "0.00";
  }

  const balanceEditar = formulario.querySelector("#balanceCuentaEditar");

  if (balanceEditar) {
    balanceEditar.value = "0.00";
  }

  formulario.querySelectorAll(".alert").forEach(function (alerta) {
    alerta.innerHTML = "";

    alerta.style.display = "none";
  });

  formulario.querySelectorAll(".is-invalid").forEach(function (elemento) {
    elemento.classList.remove("is-invalid");
  });

  formulario.querySelectorAll(".invalid-feedback").forEach(function (elemento) {
    elemento.remove();
  });

  formulario
    .querySelectorAll("input, select, button")
    .forEach(function (elemento) {
      elemento.disabled = false;
    });
}

//=====================================================
// RECARGAR TODO
//=====================================================

function recargarCuentasBancarias() {
  paginaActualCuentas = 1;

  cargarKpiCuentas();

  cargarCuentasBancarias();
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  const numero = obtenerNumeroSeguro(valor);

  return (
    "S/. " +
    numero.toLocaleString("es-PE", {
      minimumFractionDigits: 2,

      maximumFractionDigits: 2,
    })
  );
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(valor) {
  const numero = obtenerNumeroSeguro(valor);

  return numero.toLocaleString("es-PE");
}

//=====================================================
// OBTENER NÚMERO SEGURO
//=====================================================

function obtenerNumeroSeguro(valor) {
  const numero = Number(valor);

  return Number.isFinite(numero) ? numero : 0;
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escaparHTML(valor) {
  const texto = String(valor ?? "");

  return texto
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

//=====================================================
// EXPONER FUNCIONES
//=====================================================

window.cargarCuentasBancarias = cargarCuentasBancarias;

window.cargarKpiCuentas = cargarKpiCuentas;

window.recargarCuentasBancarias = recargarCuentasBancarias;

window.limpiarFiltrosCuentas = limpiarFiltrosCuentas;

window.registrarCuentaBancaria = registrarCuentaBancaria;

window.editarCuenta = editarCuenta;

window.actualizarCuentaBancaria = actualizarCuentaBancaria;

window.eliminarCuenta = eliminarCuenta;

window.restaurarCuenta = restaurarCuenta;

window.abrirModalCambiarEstado = abrirModalCambiarEstado;

window.confirmarCambioEstadoCuenta = confirmarCambioEstadoCuenta;
