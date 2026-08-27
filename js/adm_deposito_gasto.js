//=====================================================
// CoDevPro Technology
// Archivo: js/adm_deposito_gasto.js
// Módulo: Ingresos y Gastos
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActualMovimientos = 1;

const registrosPorPaginaMovimientos = 5;

let solicitudMovimientos = null;

let temporizadorBusquedaMovimientos = null;

let cargandoMovimientos = false;

let cargandoMovimientoEditar = false;

let solicitudMovimientoEditar = null;

//=====================================================
// INICIALIZAR MÓDULO
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarDepositoGasto();
});

//=====================================================
// FUNCIÓN PRINCIPAL
//=====================================================

function inicializarDepositoGasto() {
  //=================================================
  // INICIALIZAR FECHAS
  //=================================================

  inicializarFechas();

  //=================================================
  // CARGAR FILTROS
  //=================================================

  obtenerCuentasBancariasFiltro();

  obtenerCategoriasFiltro();

  obtenerProveedoresFiltro();

  obtenerMetodosPagoFiltro();

  //=================================================
  // CARGAR DATOS
  //=================================================

  obtenerKpiDepositoGasto();

  obtenerMovimientos(1);

  //=================================================
  // EVENTOS
  //=================================================

  registrarEventosFiltros();

  registrarMovimiento();

  registrarEventosModalNuevoMovimiento();

  registrarEventosModalEditarMovimiento();

  registrarEventosTabla();

  registrarEventoActualizar();
  registrarEventosModalEliminarMovimiento();
  //=================================================
  // MODAL NUEVO MOVIMIENTO
  //=================================================

  const modalNuevoMovimiento = document.getElementById("modalNuevoMovimiento");

  if (modalNuevoMovimiento) {
    modalNuevoMovimiento.addEventListener("show.bs.modal", function () {
      obtenerCuentasBancarias();

      obtenerCategorias();

      obtenerProveedores();

      obtenerMetodosPago();

      inicializarFechaMovimiento();
    });
  }
}

//=====================================================
// INICIALIZAR FECHAS
//=====================================================

function inicializarFechas() {
  if (typeof flatpickr === "undefined") {
    console.warn("Flatpickr no está cargado.");

    return;
  }

  //=================================================
  // FECHA DESDE
  //=================================================

  const fechaDesde = document.getElementById("filtroFechaDesde");

  if (fechaDesde && !fechaDesde._flatpickr) {
    flatpickr(fechaDesde, {
      locale: "es",

      dateFormat: "d/m/Y",

      allowInput: false,

      clickOpens: true,
    });
  }

  //=================================================
  // FECHA HASTA
  //=================================================

  const fechaHasta = document.getElementById("filtroFechaHasta");

  if (fechaHasta && !fechaHasta._flatpickr) {
    flatpickr(fechaHasta, {
      locale: "es",

      dateFormat: "d/m/Y",

      allowInput: false,

      clickOpens: true,
    });
  }

  //=================================================
  // FECHA NUEVO MOVIMIENTO
  //=================================================

  inicializarFechaMovimiento();

  //=================================================
  // FECHA EDITAR MOVIMIENTO
  //=================================================

  inicializarFechaEditarMovimiento();
}

//=====================================================
// INICIALIZAR FECHA NUEVO MOVIMIENTO
//=====================================================

function inicializarFechaMovimiento() {
  const campoFecha = document.getElementById("fechaMovimiento");

  if (!campoFecha) {
    return;
  }

  if (typeof flatpickr === "undefined") {
    return;
  }

  if (campoFecha._flatpickr) {
    if (!campoFecha.value) {
      campoFecha._flatpickr.setDate(new Date(), true);
    }

    return;
  }

  flatpickr(campoFecha, {
    locale: "es",

    dateFormat: "d/m/Y",

    defaultDate: new Date(),

    allowInput: false,

    clickOpens: true,
  });
}

//=====================================================
// INICIALIZAR FECHA EDITAR MOVIMIENTO
//=====================================================

function inicializarFechaEditarMovimiento() {
  const campoFecha = document.getElementById("editarFecha");

  if (!campoFecha) {
    return;
  }

  if (typeof flatpickr === "undefined") {
    return;
  }

  if (campoFecha._flatpickr) {
    return;
  }

  flatpickr(campoFecha, {
    locale: "es",

    dateFormat: "d/m/Y",

    allowInput: false,

    clickOpens: true,
  });
}

//=====================================================
// OBTENER CUENTAS BANCARIAS DEL MODAL NUEVO
//=====================================================

function obtenerCuentasBancarias() {
  cargarSelectGenerico(
    "idCuentaBancaria",
    "obtenerCuentasBancarias",
    "Seleccionar cuenta",
    "No hay cuentas bancarias registradas",
    function (cuenta) {
      return {
        value: cuenta.id_cuenta_bancaria,

        text: cuenta.nombre,

        balance: cuenta.balance,
      };
    },
  );
}

//=====================================================
// OBTENER CUENTAS BANCARIAS DEL FILTRO
//=====================================================

function obtenerCuentasBancariasFiltro() {
  cargarSelectGenerico(
    "filtroCuenta",
    "obtenerCuentasBancarias",
    "Todas las cuentas",
    "No hay cuentas bancarias registradas",
    function (cuenta) {
      return {
        value: cuenta.id_cuenta_bancaria,

        text: cuenta.nombre,

        balance: cuenta.balance,
      };
    },
  );
}

//=====================================================
// OBTENER CATEGORÍAS DEL MODAL NUEVO
//=====================================================

function obtenerCategorias() {
  cargarSelectGenerico(
    "idCategoria",
    "obtenerCategorias",
    "Seleccionar categoría",
    "No hay categorías registradas",
    function (categoria) {
      return {
        value: categoria.id_categorias,

        text: categoria.nombre,
      };
    },
  );
}

//=====================================================
// OBTENER CATEGORÍAS DEL FILTRO
//=====================================================

function obtenerCategoriasFiltro() {
  cargarSelectGenerico(
    "filtroCategoria",
    "obtenerCategorias",
    "Todas las categorías",
    "No hay categorías registradas",
    function (categoria) {
      return {
        value: categoria.id_categorias,

        text: categoria.nombre,
      };
    },
  );
}

//=====================================================
// OBTENER PROVEEDORES DEL MODAL NUEVO
//=====================================================

function obtenerProveedores() {
  cargarSelectGenerico(
    "idProveedor",
    "obtenerProveedores",
    "Seleccionar proveedor",
    "No hay proveedores registrados",
    function (proveedor) {
      return {
        value: proveedor.id_provedor,

        text: proveedor.nombre,
      };
    },
  );
}

//=====================================================
// OBTENER PROVEEDORES DEL FILTRO
//=====================================================

function obtenerProveedoresFiltro() {
  cargarSelectGenerico(
    "filtroProveedor",
    "obtenerProveedores",
    "Todos los proveedores",
    "No hay proveedores registrados",
    function (proveedor) {
      return {
        value: proveedor.id_provedor,

        text: proveedor.nombre,
      };
    },
  );
}

//=====================================================
// OBTENER MÉTODOS DE PAGO DEL MODAL NUEVO
//=====================================================

function obtenerMetodosPago() {
  cargarSelectGenerico(
    "idMetodoPago",
    "obtenerMetodosPago",
    "Seleccionar método",
    "No hay métodos de pago registrados",
    function (metodo) {
      return {
        value: metodo.id_metodo_pago,

        text: metodo.nombre,
      };
    },
  );
}

//=====================================================
// OBTENER MÉTODOS DE PAGO DEL FILTRO
//=====================================================

function obtenerMetodosPagoFiltro() {
  cargarSelectGenerico(
    "filtroMetodoPago",
    "obtenerMetodosPago",
    "Todos",
    "No hay métodos de pago registrados",
    function (metodo) {
      return {
        value: metodo.id_metodo_pago,

        text: metodo.nombre,
      };
    },
  );
}

//=====================================================
// CARGAR SELECT GENÉRICO
//=====================================================

function cargarSelectGenerico(
  idSelect,
  accion,
  textoInicial,
  textoVacio,
  transformar,
) {
  const select = document.getElementById(idSelect);

  if (!select) {
    console.warn("No se encontró el select #" + idSelect);

    return Promise.resolve();
  }

  const valorActual = select.value;

  select.innerHTML = "";

  const optionCarga = document.createElement("option");

  optionCarga.value = "";

  optionCarga.textContent = "Cargando...";

  select.appendChild(optionCarga);

  select.disabled = true;

  const formData = new FormData();

  formData.append("accion", accion);

  return fetch("ajax/adm_deposito_gasto.ajax.php", {
    method: "POST",
    body: formData,
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (respuesta) {
      if (!respuesta || respuesta.status !== "success") {
        throw new Error(
          respuesta?.mensaje || "No se pudieron obtener los datos.",
        );
      }

      select.innerHTML = "";

      const optionInicial = document.createElement("option");

      optionInicial.value = "";

      optionInicial.textContent = textoInicial;

      select.appendChild(optionInicial);

      if (!Array.isArray(respuesta.datos) || respuesta.datos.length === 0) {
        const optionVacio = document.createElement("option");

        optionVacio.value = "";

        optionVacio.textContent = textoVacio;

        optionVacio.disabled = true;

        select.appendChild(optionVacio);

        return;
      }

      respuesta.datos.forEach(function (item) {
        const datos = transformar(item);

        const option = document.createElement("option");

        option.value = datos.value;

        option.textContent = datos.text;

        if (datos.balance !== undefined) {
          option.dataset.balance = datos.balance;
        }

        select.appendChild(option);
      });

      if (
        valorActual !== "" &&
        Array.from(select.options).some(function (option) {
          return option.value === String(valorActual);
        })
      ) {
        select.value = valorActual;
      }
    })

    .catch(function (error) {
      console.error("Error AJAX " + accion + ":", error);

      select.innerHTML = "";

      const optionError = document.createElement("option");

      optionError.value = "";

      optionError.textContent = "Error al cargar datos";

      select.appendChild(optionError);
    })

    .finally(function () {
      select.disabled = false;
    });
}

//=====================================================
// OBTENER KPI
//=====================================================

function obtenerKpiDepositoGasto() {
  const formData = new FormData();

  formData.append("accion", "obtenerKpi");

  fetch("ajax/adm_deposito_gasto.ajax.php", {
    method: "POST",
    body: formData,
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (respuesta) {
      if (!respuesta || respuesta.status !== "success") {
        throw new Error(
          respuesta?.mensaje || "No se pudieron obtener los KPI.",
        );
      }

      actualizarKpiDepositoGasto(respuesta.datos || {});
    })

    .catch(function (error) {
      console.error("Error AJAX KPI:", error);
    });
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKpiDepositoGasto(datos) {
  const tarjetas = document.querySelectorAll(".resumen-card");

  if (tarjetas.length < 4) {
    console.warn("No se encontraron las tarjetas KPI.");

    return;
  }

  const totalIngresos = Number(datos.total_ingresos || 0);

  const totalGastos = Number(datos.total_gastos || 0);

  const balance = Number(datos.balance || 0);

  const totalMovimientos = Number(datos.total_movimientos || 0);

  const formatoMoneda = new Intl.NumberFormat("es-PE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });

  const valorIngresos = tarjetas[0].querySelector(".resumen-value");

  if (valorIngresos) {
    valorIngresos.textContent = "S/ " + formatoMoneda.format(totalIngresos);
  }

  const valorGastos = tarjetas[1].querySelector(".resumen-value");

  if (valorGastos) {
    valorGastos.textContent = "S/ " + formatoMoneda.format(totalGastos);
  }

  const valorBalance = tarjetas[2].querySelector(".resumen-value");

  if (valorBalance) {
    valorBalance.textContent = "S/ " + formatoMoneda.format(balance);
  }

  const valorMovimientos = tarjetas[3].querySelector(".resumen-value");

  if (valorMovimientos) {
    valorMovimientos.textContent = totalMovimientos.toLocaleString("es-PE");
  }
}

//=====================================================
// OBTENER MOVIMIENTOS
//=====================================================

function obtenerMovimientos(pagina = paginaActualMovimientos) {
  if (cargandoMovimientos) {
    if (solicitudMovimientos) {
      solicitudMovimientos.abort();
    }
  }

  paginaActualMovimientos = Number(pagina) || 1;

  const tabla = document.getElementById("tablaMovimientos");

  if (!tabla) {
    console.warn("No se encontró #tablaMovimientos.");

    return;
  }

  mostrarEstadoCargaMovimientos();

  const formData = construirFiltrosMovimientos();

  formData.append("pagina", paginaActualMovimientos);

  formData.append("registrosPorPagina", registrosPorPaginaMovimientos);

  solicitudMovimientos = new AbortController();

  cargandoMovimientos = true;

  fetch("ajax/adm_lista_movimientos.php", {
    method: "POST",
    body: formData,
    signal: solicitudMovimientos.signal,
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (respuesta) {
      if (!respuesta || respuesta.status !== "success") {
        throw new Error(
          respuesta?.mensaje || "No se pudieron obtener los movimientos.",
        );
      }

      renderizarMovimientos(respuesta.datos || []);

      renderizarPaginacionMovimientos(respuesta.paginacion || {});

      actualizarInfoMovimientos(respuesta.paginacion || {});
    })

    .catch(function (error) {
      if (error.name === "AbortError") {
        return;
      }

      console.error("Error AJAX movimientos:", error);

      mostrarErrorMovimientos(error.message);
    })

    .finally(function () {
      cargandoMovimientos = false;

      solicitudMovimientos = null;
    });
}

//=====================================================
// CONSTRUIR FILTROS
//=====================================================

function construirFiltrosMovimientos() {
  const formData = new FormData();

  const tipo = document.getElementById("filtroTipo");

  formData.append("tipo", tipo ? tipo.value : "");

  const cuenta = document.getElementById("filtroCuenta");

  formData.append("id_cuenta_bancaria", cuenta ? cuenta.value : "");

  const categoria = document.getElementById("filtroCategoria");

  formData.append("id_categoria", categoria ? categoria.value : "");

  const metodoPago = document.getElementById("filtroMetodoPago");

  formData.append("id_metodo_pago", metodoPago ? metodoPago.value : "");

  const proveedor = document.getElementById("filtroProveedor");

  formData.append("id_proveedor", proveedor ? proveedor.value : "");

  const fechaDesde = document.getElementById("filtroFechaDesde");

  formData.append("fecha_desde", fechaDesde ? fechaDesde.value : "");

  const fechaHasta = document.getElementById("filtroFechaHasta");

  formData.append("fecha_hasta", fechaHasta ? fechaHasta.value : "");

  const busqueda = document.getElementById("filtroBusqueda");

  formData.append("busqueda", busqueda ? busqueda.value.trim() : "");

  return formData;
}

//=====================================================
// MOSTRAR CARGA
//=====================================================

function mostrarEstadoCargaMovimientos() {
  const tabla = document.getElementById("tablaMovimientos");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-5">

                <div class="d-flex flex-column
                            align-items-center
                            justify-content-center
                            gap-2">

                    <div
                        class="spinner-border text-primary"
                        role="status">
                    </div>

                    <span class="text-muted">
                        Cargando movimientos...
                    </span>

                </div>

            </td>
        </tr>
    `;
}

//=====================================================
// MOSTRAR ERROR
//=====================================================

function mostrarErrorMovimientos(mensaje) {
  const tabla = document.getElementById("tablaMovimientos");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-5">

                <div class="empty-state">

                    <div
                        class="empty-state-icon text-danger">

                        <i class="bi bi-exclamation-triangle"></i>

                    </div>

                    <h6>
                        No se pudieron cargar los movimientos
                    </h6>

                    <p class="text-muted">
                        ${escapeHtml(mensaje || "Ocurrió un error inesperado.")}
                    </p>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        onclick="obtenerMovimientos(1)">

                        <i class="bi bi-arrow-clockwise me-1"></i>

                        Reintentar

                    </button>

                </div>

            </td>
        </tr>
    `;
}

//=====================================================
// RENDERIZAR MOVIMIENTOS
//=====================================================

function renderizarMovimientos(movimientos) {
  const tabla = document.getElementById("tablaMovimientos");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = "";

  if (!Array.isArray(movimientos) || movimientos.length === 0) {
    tabla.innerHTML = `
            <tr id="estadoVacioMovimientos">

                <td
                    colspan="9"
                    class="text-center">

                    <div class="empty-state">

                        <div class="empty-state-icon">

                            <i class="bi bi-arrow-left-right"></i>

                        </div>

                        <h6>
                            No hay movimientos registrados
                        </h6>

                        <p>
                            No existen movimientos que coincidan
                            con los filtros seleccionados.
                        </p>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalNuevoMovimiento">

                            <i class="bi bi-plus-lg me-1"></i>

                            Registrar movimiento

                        </button>

                    </div>

                </td>

            </tr>
        `;

    return;
  }

  movimientos.forEach(function (movimiento) {
    const fila = document.createElement("tr");

    fila.dataset.id = movimiento.id_deposito;

    //=========================================
    // FECHA
    //=========================================

    const tdFecha = document.createElement("td");

    tdFecha.textContent =
      movimiento.fecha_formateada || formatearFecha(movimiento.fecha);

    //=========================================
    // TIPO
    //=========================================

    const tdTipo = document.createElement("td");

    const badgeTipo = document.createElement("span");

    const tipo = String(movimiento.tipo || "").toUpperCase();

    badgeTipo.className =
      "badge " + (tipo === "INGRESO" ? "bg-success" : "bg-danger");

    badgeTipo.textContent = tipo === "INGRESO" ? "Ingreso" : "Gasto";

    tdTipo.appendChild(badgeTipo);

    //=========================================
    // CONCEPTO
    //=========================================

    const tdConcepto = document.createElement("td");

    const concepto = document.createElement("div");

    concepto.className = "fw-semibold";

    concepto.textContent = movimiento.concepto || "Sin concepto";

    tdConcepto.appendChild(concepto);

    if (movimiento.descripcion) {
      const descripcion = document.createElement("small");

      descripcion.className = "text-muted d-block";

      descripcion.textContent = movimiento.descripcion;

      tdConcepto.appendChild(descripcion);
    }

    //=========================================
    // CUENTA
    //=========================================

    const tdCuenta = document.createElement("td");

    tdCuenta.textContent = movimiento.cuenta_bancaria || "Sin cuenta";

    //=========================================
    // CATEGORÍA
    //=========================================

    const tdCategoria = document.createElement("td");

    tdCategoria.textContent = movimiento.categoria || "Sin categoría";

    //=========================================
    // PROVEEDOR
    //=========================================

    const tdProveedor = document.createElement("td");

    tdProveedor.textContent = movimiento.proveedor || "Sin proveedor";

    //=========================================
    // MÉTODO
    //=========================================

    const tdMetodoPago = document.createElement("td");

    tdMetodoPago.textContent = movimiento.metodo_pago || "Sin método";

    //=========================================
    // MONTO
    //=========================================

    const tdMonto = document.createElement("td");

    tdMonto.className = "text-end fw-semibold";

    tdMonto.textContent = "S/ " + formatearMoneda(movimiento.monto_pago);

    if (tipo === "INGRESO") {
      tdMonto.classList.add("text-success");
    } else {
      tdMonto.classList.add("text-danger");
    }

    //=========================================
    // ACCIONES
    //=========================================

    const tdAcciones = document.createElement("td");

    tdAcciones.className = "text-center";

    tdAcciones.innerHTML = `
                <div class="btn-group btn-group-sm">

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-editar-movimiento"
                        title="Editar movimiento"
                        data-id="${escapeHtml(movimiento.id_deposito)}">

                        <i class="bi bi-pencil"></i>

                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-danger btn-eliminar-movimiento"
                        title="Eliminar movimiento"
                        data-id="${escapeHtml(movimiento.id_deposito)}">

                        <i class="bi bi-trash"></i>

                    </button>

                </div>
            `;

    fila.appendChild(tdFecha);

    fila.appendChild(tdTipo);

    fila.appendChild(tdConcepto);

    fila.appendChild(tdCuenta);

    fila.appendChild(tdCategoria);

    fila.appendChild(tdProveedor);

    fila.appendChild(tdMetodoPago);

    fila.appendChild(tdMonto);

    fila.appendChild(tdAcciones);

    tabla.appendChild(fila);
  });
}

//=====================================================
// RENDERIZAR PAGINACIÓN
//=====================================================

function renderizarPaginacionMovimientos(paginacion) {
  const nav = document.querySelector(".movimientos-footer nav");

  if (!nav) {
    return;
  }

  const totalPaginas = Number(paginacion.total_paginas || 1);

  const paginaActual = Number(
    paginacion.pagina_actual || paginaActualMovimientos,
  );

  const hayAnterior = Boolean(paginacion.hay_anterior);

  const haySiguiente = Boolean(paginacion.hay_siguiente);

  let html = `
        <ul class="pagination pagination-sm mb-0">
    `;

  html += `
        <li class="page-item ${!hayAnterior ? "disabled" : ""}">

            <a
                class="page-link btn-pagina-movimiento"
                href="#"
                data-pagina="${paginaActual - 1}"
                aria-label="Anterior">

                <i class="bi bi-chevron-left"></i>

            </a>

        </li>
    `;

  let inicio = Math.max(1, paginaActual - 2);

  let fin = Math.min(totalPaginas, paginaActual + 2);

  if (paginaActual <= 2) {
    fin = Math.min(totalPaginas, 5);
  }

  if (paginaActual >= totalPaginas - 1) {
    inicio = Math.max(1, totalPaginas - 4);
  }

  if (inicio > 1) {
    html += `
            <li class="page-item">

                <a
                    class="page-link btn-pagina-movimiento"
                    href="#"
                    data-pagina="1">

                    1

                </a>

            </li>
        `;

    if (inicio > 2) {
      html += `
                <li class="page-item disabled">

                    <span class="page-link">
                        ...
                    </span>

                </li>
            `;
    }
  }

  for (let pagina = inicio; pagina <= fin; pagina++) {
    html += `
            <li class="page-item ${pagina === paginaActual ? "active" : ""}">

                <a
                    class="page-link btn-pagina-movimiento"
                    href="#"
                    data-pagina="${pagina}">

                    ${pagina}

                </a>

            </li>
        `;
  }

  if (fin < totalPaginas) {
    if (fin < totalPaginas - 1) {
      html += `
                <li class="page-item disabled">

                    <span class="page-link">
                        ...
                    </span>

                </li>
            `;
    }

    html += `
            <li class="page-item">

                <a
                    class="page-link btn-pagina-movimiento"
                    href="#"
                    data-pagina="${totalPaginas}">

                    ${totalPaginas}

                </a>

            </li>
        `;
  }

  html += `
        <li class="page-item ${!haySiguiente ? "disabled" : ""}">

            <a
                class="page-link btn-pagina-movimiento"
                href="#"
                data-pagina="${paginaActual + 1}"
                aria-label="Siguiente">

                <i class="bi bi-chevron-right"></i>

            </a>

        </li>
    `;

  html += `
        </ul>
    `;

  nav.innerHTML = html;
}

//=====================================================
// ACTUALIZAR INFORMACIÓN
//=====================================================

function actualizarInfoMovimientos(paginacion) {
  const contenedor = document.querySelector(".movimientos-info");

  if (!contenedor) {
    return;
  }

  const desde = Number(paginacion.desde || 0);

  const hasta = Number(paginacion.hasta || 0);

  const total = Number(paginacion.total_registros || 0);

  if (total === 0) {
    contenedor.innerHTML = `
            Mostrando
            <strong>0</strong>
            movimientos
        `;

    return;
  }

  contenedor.innerHTML = `
        Mostrando
        <strong>${desde}</strong>
        -
        <strong>${hasta}</strong>
        de
        <strong>${total}</strong>
        movimientos
    `;
}

//=====================================================
// EVENTOS FILTROS
//=====================================================

function registrarEventosFiltros() {
  const btnAplicar = document.getElementById("btnAplicarFiltros");

  if (btnAplicar) {
    btnAplicar.addEventListener("click", function () {
      paginaActualMovimientos = 1;

      obtenerMovimientos(1);
    });
  }

  const btnLimpiar = document.getElementById("btnLimpiarFiltros");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function () {
      limpiarFiltrosMovimientos();
    });
  }

  const campoBusqueda = document.getElementById("filtroBusqueda");

  if (campoBusqueda) {
    campoBusqueda.addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();

        clearTimeout(temporizadorBusquedaMovimientos);

        paginaActualMovimientos = 1;

        obtenerMovimientos(1);
      }
    });

    campoBusqueda.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaMovimientos);

      temporizadorBusquedaMovimientos = setTimeout(function () {
        paginaActualMovimientos = 1;

        obtenerMovimientos(1);
      }, 400);
    });
  }
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosMovimientos() {
  const filtroTipo = document.getElementById("filtroTipo");

  if (filtroTipo) {
    filtroTipo.value = "";
  }

  const filtroCuenta = document.getElementById("filtroCuenta");

  if (filtroCuenta) {
    filtroCuenta.value = "";
  }

  const filtroCategoria = document.getElementById("filtroCategoria");

  if (filtroCategoria) {
    filtroCategoria.value = "";
  }

  const filtroMetodoPago = document.getElementById("filtroMetodoPago");

  if (filtroMetodoPago) {
    filtroMetodoPago.value = "";
  }

  const filtroProveedor = document.getElementById("filtroProveedor");

  if (filtroProveedor) {
    filtroProveedor.value = "";
  }

  const filtroBusqueda = document.getElementById("filtroBusqueda");

  if (filtroBusqueda) {
    filtroBusqueda.value = "";
  }

  const fechaDesde = document.getElementById("filtroFechaDesde");

  if (fechaDesde && fechaDesde._flatpickr) {
    fechaDesde._flatpickr.clear();
  } else if (fechaDesde) {
    fechaDesde.value = "";
  }

  const fechaHasta = document.getElementById("filtroFechaHasta");

  if (fechaHasta && fechaHasta._flatpickr) {
    fechaHasta._flatpickr.clear();
  } else if (fechaHasta) {
    fechaHasta.value = "";
  }

  paginaActualMovimientos = 1;

  obtenerKpiDepositoGasto();

  obtenerMovimientos(1);
}

//=====================================================
// EVENTOS TABLA + PAGINACIÓN
//=====================================================

function registrarEventosTabla() {
  document.addEventListener("click", function (event) {
    //=========================================
    // PAGINACIÓN
    //=========================================

    const botonPagina = event.target.closest(".btn-pagina-movimiento");

    if (botonPagina) {
      event.preventDefault();

      const elemento = botonPagina.closest(".page-item");

      if (elemento && elemento.classList.contains("disabled")) {
        return;
      }

      const pagina = Number(botonPagina.dataset.pagina);

      if (pagina >= 1) {
        obtenerMovimientos(pagina);
      }

      return;
    }

    //=========================================
    // EDITAR
    //=========================================

    const botonEditar = event.target.closest(".btn-editar-movimiento");

    if (botonEditar) {
      const id = Number(botonEditar.dataset.id);

      abrirModalEditarMovimiento(id);

      return;
    }

    //=========================================
    // ELIMINAR
    //=========================================

    const botonEliminar = event.target.closest(".btn-eliminar-movimiento");

    if (botonEliminar) {
      const id = Number(botonEliminar.dataset.id);

      abrirModalEliminarMovimiento(id);
    }
  });
}

//=====================================================
// ABRIR MODAL EDITAR
//=====================================================

function abrirModalEditarMovimiento(id) {
  id = Number(id);

  if (!id || id <= 0) {
    mostrarMensajeError("ID del movimiento no válido.");

    return;
  }

  const modalElemento = document.getElementById("modalEditarMovimiento");

  if (!modalElemento) {
    console.warn("No se encontró #modalEditarMovimiento.");

    return;
  }

  //=================================================
  // GUARDAR ID
  //=================================================

  modalElemento.dataset.idMovimiento = String(id);

  const inputId = document.getElementById("editarIdDeposito");

  if (inputId) {
    inputId.value = id;
  }

  //=================================================
  // LIMPIAR FORMULARIO
  //=================================================

  prepararModalEditarMovimiento();

  //=================================================
  // ABRIR MODAL
  //=================================================

  if (typeof bootstrap === "undefined") {
    return;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(modalElemento);

  modal.show();

  //=================================================
  // OBTENER DATOS
  //=================================================

  obtenerMovimientoParaEditar(id);
}

//=====================================================
// PREPARAR MODAL EDITAR
//=====================================================

function prepararModalEditarMovimiento() {
  const formulario = document.getElementById("formEditarMovimiento");

  if (formulario) {
    formulario.reset();
  }

  const selectTipo = document.getElementById("editarTipo");

  if (selectTipo) {
    selectTipo.value = "INGRESO";
  }

  const selects = [
    "editarCuenta",
    "editarCategoria",
    "editarProveedor",
    "editarMetodoPago",
  ];

  selects.forEach(function (id) {
    const select = document.getElementById(id);

    if (!select) {
      return;
    }

    select.innerHTML = `
                <option value="">
                    Cargando...
                </option>
            `;

    select.disabled = true;
  });

  const campoFecha = document.getElementById("editarFecha");

  if (campoFecha) {
    if (campoFecha._flatpickr) {
      campoFecha._flatpickr.clear();
    } else {
      campoFecha.value = "";
    }
  }
}

//=====================================================
// OBTENER MOVIMIENTO PARA EDITAR
//=====================================================

function obtenerMovimientoParaEditar(id) {
  if (cargandoMovimientoEditar) {
    if (solicitudMovimientoEditar) {
      solicitudMovimientoEditar.abort();
    }
  }

  cargandoMovimientoEditar = true;

  solicitudMovimientoEditar = new AbortController();

  const formData = new FormData();

  formData.append("id_deposito", id);

  fetch("ajax/adm_obtener_movimiento.php", {
    method: "POST",
    body: formData,
    signal: solicitudMovimientoEditar.signal,
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (respuesta) {
      console.log("Respuesta movimiento editar:", respuesta);

      if (!respuesta || respuesta.status !== "success") {
        throw new Error(
          respuesta?.mensaje || "No se pudo obtener el movimiento.",
        );
      }

      cargarDatosModalEditar(respuesta.datos || {});
    })

    .catch(function (error) {
      if (error.name === "AbortError") {
        return;
      }

      console.error("Error obteniendo movimiento:", error);

      mostrarMensajeError(error.message || "No se pudo cargar el movimiento.");
    })

    .finally(function () {
      cargandoMovimientoEditar = false;

      solicitudMovimientoEditar = null;
    });
}

//=====================================================
// CARGAR DATOS EN MODAL EDITAR
//=====================================================

async function cargarDatosModalEditar(movimiento) {
  const inputId = document.getElementById("editarIdDeposito");

  const tipo = document.getElementById("editarTipo");

  const concepto = document.getElementById("editarConcepto");

  const monto = document.getElementById("editarMonto");

  const descripcion = document.getElementById("editarDescripcion");

  if (inputId) {
    inputId.value = movimiento.id_deposito || "";
  }

  if (tipo) {
    const tipoMovimiento = String(movimiento.tipo || "").toUpperCase();

    tipo.value = tipoMovimiento === "GASTO" ? "GASTO" : "INGRESO";
  }

  if (concepto) {
    concepto.value = movimiento.concepto || "";
  }

  if (monto) {
    monto.value =
      movimiento.monto_pago !== undefined && movimiento.monto_pago !== null
        ? movimiento.monto_pago
        : "";
  }

  if (descripcion) {
    descripcion.value = movimiento.descripcion || "";
  }

  //=================================================
  // CARGAR SELECTS
  //=================================================

  await Promise.all([
    cargarSelectEditar(
      "editarCuenta",
      "obtenerCuentasBancarias",
      "Seleccionar cuenta",
      "No hay cuentas bancarias registradas",
      function (item) {
        return {
          value: item.id_cuenta_bancaria,
          text: item.nombre,
          balance: item.balance,
        };
      },
    ),

    cargarSelectEditar(
      "editarCategoria",
      "obtenerCategorias",
      "Seleccionar categoría",
      "No hay categorías registradas",
      function (item) {
        return {
          value: item.id_categorias,
          text: item.nombre,
        };
      },
    ),

    cargarSelectEditar(
      "editarProveedor",
      "obtenerProveedores",
      "Seleccionar proveedor",
      "No hay proveedores registrados",
      function (item) {
        return {
          value: item.id_provedor,
          text: item.nombre,
        };
      },
    ),

    cargarSelectEditar(
      "editarMetodoPago",
      "obtenerMetodosPago",
      "Seleccionar método",
      "No hay métodos de pago registrados",
      function (item) {
        return {
          value: item.id_metodo_pago,
          text: item.nombre,
        };
      },
    ),
  ]);

  //=================================================
  // SELECCIONAR VALORES ACTUALES
  //=================================================

  seleccionarValorSelect("editarCuenta", movimiento.id_cuenta_bancaria);

  seleccionarValorSelect("editarCategoria", movimiento.id_categoria);

  seleccionarValorSelect("editarProveedor", movimiento.id_proveedor);

  seleccionarValorSelect("editarMetodoPago", movimiento.id_metodo_pago);

  //=================================================
  // FECHA
  //=================================================

  establecerFechaEditar(movimiento.fecha_formateada || movimiento.fecha || "");
}

//=====================================================
// CARGAR SELECT PARA EDITAR
//=====================================================

function cargarSelectEditar(
  idSelect,
  accion,
  textoInicial,
  textoVacio,
  transformar,
) {
  const select = document.getElementById(idSelect);

  if (!select) {
    return Promise.resolve();
  }

  select.innerHTML = `
        <option value="">
            Cargando...
        </option>
    `;

  select.disabled = true;

  const formData = new FormData();

  formData.append("accion", accion);

  return fetch("ajax/adm_deposito_gasto.ajax.php", {
    method: "POST",
    body: formData,
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (respuesta) {
      if (!respuesta || respuesta.status !== "success") {
        throw new Error(
          respuesta?.mensaje || "No se pudieron cargar las opciones.",
        );
      }

      select.innerHTML = "";

      const optionInicial = document.createElement("option");

      optionInicial.value = "";

      optionInicial.textContent = textoInicial;

      select.appendChild(optionInicial);

      if (!Array.isArray(respuesta.datos) || respuesta.datos.length === 0) {
        const optionVacio = document.createElement("option");

        optionVacio.value = "";

        optionVacio.textContent = textoVacio;

        optionVacio.disabled = true;

        select.appendChild(optionVacio);

        return;
      }

      respuesta.datos.forEach(function (item) {
        const datos = transformar(item);

        const option = document.createElement("option");

        option.value = String(datos.value);

        option.textContent = datos.text;

        if (datos.balance !== undefined) {
          option.dataset.balance = datos.balance;
        }

        select.appendChild(option);
      });
    })

    .catch(function (error) {
      console.error("Error cargando " + idSelect + ":", error);

      select.innerHTML = `
                <option value="">
                    Error al cargar datos
                </option>
            `;
    })

    .finally(function () {
      select.disabled = false;
    });
}

//=====================================================
// SELECCIONAR VALOR SELECT
//=====================================================

function seleccionarValorSelect(idSelect, valor) {
  const select = document.getElementById(idSelect);

  if (!select) {
    return;
  }

  if (
    valor === null ||
    valor === undefined ||
    valor === "" ||
    Number(valor) === 0
  ) {
    select.value = "";

    return;
  }

  const valorTexto = String(valor);

  const existe = Array.from(select.options).some(function (option) {
    return option.value === valorTexto;
  });

  if (existe) {
    select.value = valorTexto;
  } else {
    console.warn("No se encontró el valor " + valorTexto + " en #" + idSelect);

    select.value = "";
  }
}

//=====================================================
// ESTABLECER FECHA EDITAR
//=====================================================

function establecerFechaEditar(fecha) {
  const campoFecha = document.getElementById("editarFecha");

  if (!campoFecha) {
    return;
  }

  if (!fecha) {
    if (campoFecha._flatpickr) {
      campoFecha._flatpickr.clear();
    } else {
      campoFecha.value = "";
    }

    return;
  }

  let fechaFormateada = "";

  const fechaTexto = String(fecha).trim();

  //=================================================
  // FORMATO Y-M-D
  //=================================================

  if (/^\d{4}-\d{2}-\d{2}$/.test(fechaTexto)) {
    const partes = fechaTexto.split("-");

    fechaFormateada = partes[2] + "/" + partes[1] + "/" + partes[0];
  } else {
    //=============================================
    // YA VIENE DD/MM/YYYY
    //=============================================

    fechaFormateada = fechaTexto;
  }

  if (campoFecha._flatpickr) {
    campoFecha._flatpickr.setDate(fechaFormateada, true, "d/m/Y");
  } else {
    campoFecha.value = fechaFormateada;
  }
}

//=====================================================
// EVENTOS MODAL EDITAR
//=====================================================

function registrarEventosModalEditarMovimiento() {
  const formulario = document.getElementById("formEditarMovimiento");

  if (!formulario) {
    console.warn("No se encontró #formEditarMovimiento.");

    return;
  }

  //=================================================
  // EVITAR DOBLE EVENTO
  //=================================================

  if (formulario.dataset.eventoRegistrado === "1") {
    return;
  }

  formulario.dataset.eventoRegistrado = "1";

  //=================================================
  // SUBMIT
  //=================================================

  formulario.addEventListener("submit", function (event) {
    event.preventDefault();

    guardarMovimientoEditado(formulario);
  });

  //=================================================
  // LIMPIAR AL CERRAR
  //=================================================

  const modal = document.getElementById("modalEditarMovimiento");

  if (modal) {
    modal.addEventListener("hidden.bs.modal", function () {
      formulario.reset();

      const campoFecha = document.getElementById("editarFecha");

      if (campoFecha && campoFecha._flatpickr) {
        campoFecha._flatpickr.clear();
      }

      modal.dataset.idMovimiento = "";
    });
  }
}

//=====================================================
// GUARDAR MOVIMIENTO EDITADO
//=====================================================

function guardarMovimientoEditado(formulario) {
  //=================================================
  // VALIDAR FORMULARIO
  //=================================================

  if (!formulario.checkValidity()) {
    formulario.reportValidity();

    return;
  }

  //=================================================
  // ID
  //=================================================

  const idDeposito = document.getElementById("editarIdDeposito");

  if (!idDeposito || !Number(idDeposito.value)) {
    mostrarMensajeError("No se pudo identificar el movimiento.");

    return;
  }

  //=================================================
  // TIPO
  //=================================================

  const tipo = document.getElementById("editarTipo");

  if (!tipo || !tipo.value) {
    mostrarMensajeError("Debe seleccionar el tipo de movimiento.");

    return;
  }

  //=================================================
  // CUENTA
  //=================================================

  const cuenta = document.getElementById("editarCuenta");

  //=================================================
  // CATEGORÍA
  //=================================================

  const categoria = document.getElementById("editarCategoria");

  //=================================================
  // PROVEEDOR
  //=================================================

  const proveedor = document.getElementById("editarProveedor");

  //=================================================
  // MÉTODO DE PAGO
  //=================================================

  const metodoPago = document.getElementById("editarMetodoPago");

  //=================================================
  // FECHA
  //=================================================

  const fecha = document.getElementById("editarFecha");

  //=================================================
  // MONTO
  //=================================================

  const monto = document.getElementById("editarMonto");

  if (!monto || Number(monto.value) <= 0) {
    mostrarMensajeError("El monto debe ser mayor a 0.");

    return;
  }

  //=================================================
  // BOTÓN
  //=================================================

  const botonGuardar = document.querySelector(
    "#modalEditarMovimiento button[type='submit']",
  );

  const textoOriginal = botonGuardar ? botonGuardar.innerHTML : "";

  if (botonGuardar) {
    botonGuardar.disabled = true;

    botonGuardar.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true">
            </span>

            Guardando...
        `;
  }

  //=================================================
  // FORM DATA
  //=================================================

  const formData = new FormData();

  formData.append("id_deposito", idDeposito.value);

  formData.append("tipo", tipo.value);

  formData.append("id_cuenta_bancaria", cuenta ? cuenta.value : "");

  formData.append("id_categoria", categoria ? categoria.value : "");

  formData.append("id_proveedor", proveedor ? proveedor.value : "");

  formData.append("id_metodo_pago", metodoPago ? metodoPago.value : "");

  formData.append("fecha", convertirFechaAFormatoSQL(fecha ? fecha.value : ""));

  formData.append("concepto", obtenerValorInput("editarConcepto"));

  formData.append("monto_pago", monto.value);

  formData.append("descripcion", obtenerValorInput("editarDescripcion"));

  //=================================================
  // AJAX
  //=================================================

  fetch("ajax/adm_actualizar_movimiento.php", {
    method: "POST",
    body: formData,
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (respuesta) {
      console.log("Respuesta actualizar movimiento:", respuesta);

      if (!respuesta || respuesta.status !== "success") {
        throw new Error(
          respuesta?.mensaje || "No se pudo actualizar el movimiento.",
        );
      }

      //=========================================
      // CERRAR MODAL
      //=========================================

      const modalElemento = document.getElementById("modalEditarMovimiento");

      if (modalElemento && typeof bootstrap !== "undefined") {
        const modal = bootstrap.Modal.getInstance(modalElemento);

        if (modal) {
          modal.hide();
        }
      }

      //=========================================
      // ACTUALIZAR KPI
      //=========================================

      obtenerKpiDepositoGasto();

      //=========================================
      // ACTUALIZAR CUENTAS
      //=========================================

      obtenerCuentasBancariasFiltro();

      //=========================================
      // ACTUALIZAR TABLA
      //=========================================

      paginaActualMovimientos = 1;

      obtenerMovimientos(1);

      //=========================================
      // MENSAJE
      //=========================================

      mostrarMensajeExito(
        respuesta.mensaje || "Movimiento actualizado correctamente.",
      );
    })

    .catch(function (error) {
      console.error("Error actualizar movimiento:", error);

      mostrarMensajeError(
        error.message || "Ocurrió un error al actualizar el movimiento.",
      );
    })

    .finally(function () {
      if (botonGuardar) {
        botonGuardar.disabled = false;

        botonGuardar.innerHTML = textoOriginal;
      }
    });
}

//=====================================================
// OBTENER VALOR INPUT
//=====================================================

function obtenerValorInput(id) {
  const elemento = document.getElementById(id);

  return elemento ? elemento.value.trim() : "";
}

//=====================================================
// CONVERTIR FECHA DD/MM/YYYY
// A YYYY-MM-DD
//=====================================================

function convertirFechaAFormatoSQL(fecha) {
  if (!fecha) {
    return "";
  }

  const texto = String(fecha).trim();

  //=================================================
  // YA ES YYYY-MM-DD
  //=================================================

  if (/^\d{4}-\d{2}-\d{2}$/.test(texto)) {
    return texto;
  }

  //=================================================
  // DD/MM/YYYY
  //=================================================

  const partes = texto.split("/");

  if (partes.length === 3) {
    return (
      partes[2] +
      "-" +
      partes[1].padStart(2, "0") +
      "-" +
      partes[0].padStart(2, "0")
    );
  }

  return texto;
}

//=====================================================
// ABRIR MODAL ELIMINAR
//=====================================================

function abrirModalEliminarMovimiento(id) {
  id = Number(id);

  //=================================================
  // VALIDAR ID
  //=================================================

  if (!id || id <= 0) {
    mostrarMensajeError("ID del movimiento no válido.");

    return;
  }

  //=================================================
  // MODAL
  //=================================================

  const modalElemento = document.getElementById("modalEliminarMovimiento");

  if (!modalElemento) {
    console.warn("No se encontró #modalEliminarMovimiento.");

    return;
  }

  //=================================================
  // GUARDAR ID EN EL MODAL
  //=================================================

  modalElemento.dataset.idMovimiento = String(id);

  //=================================================
  // INPUT HIDDEN
  //=================================================

  const inputId = document.getElementById("eliminarIdDeposito");

  if (inputId) {
    inputId.value = String(id);
  }

  //=================================================
  // ABRIR MODAL
  //=================================================

  if (typeof bootstrap === "undefined") {
    console.warn("Bootstrap no está disponible.");

    return;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(modalElemento);

  modal.show();
}
//=====================================================
// REGISTRAR EVENTOS MODAL ELIMINAR
//=====================================================

function registrarEventosModalEliminarMovimiento() {
  const boton = document.getElementById("btnConfirmarEliminar");

  if (!boton) {
    console.warn("No se encontró #btnConfirmarEliminar.");

    return;
  }

  //=================================================
  // EVITAR DOBLE EVENTO
  //=================================================

  if (boton.dataset.eventoRegistrado === "1") {
    return;
  }

  boton.dataset.eventoRegistrado = "1";

  //=================================================
  // CLICK
  //=================================================

  boton.addEventListener("click", function () {
    eliminarMovimiento();
  });
  //=================================================
  // LIMPIAR AL CERRAR
  //=================================================

  const modal = document.getElementById("modalEliminarMovimiento");

  if (modal) {
    modal.addEventListener("hidden.bs.modal", function () {
      const inputId = document.getElementById("eliminarIdDeposito");

      if (inputId) {
        inputId.value = "";
      }

      modal.dataset.idMovimiento = "";
    });
  }
}
//=====================================================
// ELIMINAR MOVIMIENTO
//=====================================================

function eliminarMovimiento() {
  //=================================================
  // OBTENER ID
  //=================================================

  const inputId = document.getElementById("eliminarIdDeposito");

  if (!inputId) {
    mostrarMensajeError("No se pudo identificar el movimiento.");

    return;
  }

  const idDeposito = Number(inputId.value);

  //=================================================
  // VALIDAR ID
  //=================================================

  if (!idDeposito || idDeposito <= 0) {
    mostrarMensajeError("El ID del movimiento no es válido.");

    return;
  }

  //=================================================
  // BOTÓN
  //=================================================

  const boton = document.getElementById("btnConfirmarEliminar");

  if (!boton) {
    return;
  }

  const textoOriginal = boton.innerHTML;

  //=================================================
  // BLOQUEAR BOTÓN
  //=================================================

  boton.disabled = true;

  boton.innerHTML = `
        <span
            class="spinner-border spinner-border-sm me-2"
            role="status"
            aria-hidden="true">
        </span>

        Eliminando...
    `;

  //=================================================
  // FORM DATA
  //=================================================

  const formData = new FormData();

  formData.append("id_deposito", String(idDeposito));

  //=================================================
  // AJAX
  //=================================================

  fetch("ajax/adm_eliminar_movimiento.php", {
    method: "POST",
    body: formData,
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (respuesta) {
      console.log("Respuesta eliminar movimiento:", respuesta);

      //=================================================
      // VALIDAR RESPUESTA
      //=================================================

      if (!respuesta || respuesta.status !== "success") {
        throw new Error(
          respuesta?.mensaje || "No se pudo eliminar el movimiento.",
        );
      }

      //=================================================
      // CERRAR MODAL
      //=================================================

      const modalElemento = document.getElementById("modalEliminarMovimiento");

      if (modalElemento && typeof bootstrap !== "undefined") {
        const modal = bootstrap.Modal.getInstance(modalElemento);

        if (modal) {
          modal.hide();
        }
      }

      //=================================================
      // LIMPIAR ID
      //=================================================

      if (inputId) {
        inputId.value = "";
      }

      if (modalElemento) {
        modalElemento.dataset.idMovimiento = "";
      }

      //=================================================
      // ACTUALIZAR KPI
      //=================================================

      obtenerKpiDepositoGasto();

      //=================================================
      // ACTUALIZAR CUENTAS
      //=================================================

      obtenerCuentasBancariasFiltro();

      //=================================================
      // ACTUALIZAR TABLA
      //=================================================

      paginaActualMovimientos = 1;

      obtenerMovimientos(1);

      //=================================================
      // MENSAJE
      //=================================================

      mostrarMensajeExito(
        respuesta.mensaje || "Movimiento eliminado correctamente.",
      );
    })

    .catch(function (error) {
      console.error("Error eliminar movimiento:", error);

      mostrarMensajeError(
        error.message || "Ocurrió un error al eliminar el movimiento.",
      );
    })

    .finally(function () {
      //=================================================
      // RESTAURAR BOTÓN
      //=================================================

      boton.disabled = false;

      boton.innerHTML = textoOriginal;
    });
}
//=====================================================
// EVENTOS MODAL NUEVO
//=====================================================

function registrarEventosModalNuevoMovimiento() {
  const modal = document.getElementById("modalNuevoMovimiento");

  if (!modal) {
    return;
  }

  modal.addEventListener("hidden.bs.modal", function () {
    const formulario = document.getElementById("formNuevoMovimiento");

    if (!formulario) {
      return;
    }

    formulario.reset();

    const tipoIngreso = document.getElementById("tipoIngreso");

    if (tipoIngreso) {
      tipoIngreso.checked = true;
    }

    const fecha = document.getElementById("fechaMovimiento");

    if (fecha && fecha._flatpickr) {
      fecha._flatpickr.setDate(new Date(), true);
    }
  });
}

//=====================================================
// REGISTRAR MOVIMIENTO
//=====================================================

function registrarMovimiento() {
  const formulario = document.getElementById("formNuevoMovimiento");

  if (!formulario) {
    console.warn("No se encontró #formNuevoMovimiento.");

    return;
  }

  if (formulario.dataset.eventoRegistrado === "1") {
    return;
  }

  formulario.dataset.eventoRegistrado = "1";

  formulario.addEventListener("submit", function (event) {
    event.preventDefault();

    if (!formulario.checkValidity()) {
      formulario.reportValidity();

      return;
    }

    const tipoSeleccionado = document.querySelector(
      'input[name="tipoMovimiento"]:checked',
    );

    if (!tipoSeleccionado) {
      mostrarMensajeError("Debe seleccionar el tipo de movimiento.");

      return;
    }

    const botonGuardar = document.querySelector(
      "#modalNuevoMovimiento button[type='submit']",
    );

    const textoOriginal = botonGuardar ? botonGuardar.innerHTML : "";

    if (botonGuardar) {
      botonGuardar.disabled = true;

      botonGuardar.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-2"
                        role="status"
                        aria-hidden="true">
                    </span>

                    Guardando...
                `;
    }

    const formData = new FormData(formulario);

    formData.set("tipoMovimiento", tipoSeleccionado.value);

    fetch("ajax/adm_registrar_movimiento.php", {
      method: "POST",
      body: formData,
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Error HTTP: " + response.status);
        }

        return response.json();
      })

      .then(function (respuesta) {
        if (!respuesta || respuesta.status !== "success") {
          throw new Error(
            respuesta?.mensaje || "No se pudo registrar el movimiento.",
          );
        }

        const modalElemento = document.getElementById("modalNuevoMovimiento");

        if (modalElemento && typeof bootstrap !== "undefined") {
          const modal = bootstrap.Modal.getInstance(modalElemento);

          if (modal) {
            modal.hide();
          }
        }

        formulario.reset();

        const tipoIngreso = document.getElementById("tipoIngreso");

        if (tipoIngreso) {
          tipoIngreso.checked = true;
        }

        const campoFecha = document.getElementById("fechaMovimiento");

        if (campoFecha && campoFecha._flatpickr) {
          campoFecha._flatpickr.setDate(new Date(), true);
        }

        obtenerKpiDepositoGasto();

        obtenerCuentasBancarias();

        obtenerCuentasBancariasFiltro();

        paginaActualMovimientos = 1;

        obtenerMovimientos(1);

        mostrarMensajeExito(
          respuesta.mensaje || "Movimiento registrado correctamente.",
        );
      })

      .catch(function (error) {
        console.error("Error registrar movimiento:", error);

        mostrarMensajeError(
          error.message || "Ocurrió un error al registrar el movimiento.",
        );
      })

      .finally(function () {
        if (botonGuardar) {
          botonGuardar.disabled = false;

          botonGuardar.innerHTML = textoOriginal;
        }
      });
  });
}

//=====================================================
// ACTUALIZAR MOVIMIENTOS
//=====================================================

function registrarEventoActualizar() {
  const boton = document.getElementById("btnActualizarMovimientos");

  if (!boton) {
    return;
  }

  if (boton.dataset.eventoRegistrado === "1") {
    return;
  }

  boton.dataset.eventoRegistrado = "1";

  boton.addEventListener("click", function () {
    paginaActualMovimientos = 1;

    obtenerKpiDepositoGasto();

    obtenerMovimientos(1);

    obtenerCuentasBancariasFiltro();

    obtenerCategoriasFiltro();

    obtenerProveedoresFiltro();

    obtenerMetodosPagoFiltro();
  });
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  const numero = Number(valor) || 0;

  return new Intl.NumberFormat("es-PE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(numero);
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "";
  }

  const partes = String(fecha).split("-");

  if (partes.length === 3) {
    return partes[2] + "/" + partes[1] + "/" + partes[0];
  }

  return fecha;
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escapeHtml(valor) {
  if (valor === null || valor === undefined) {
    return "";
  }

  return String(valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

//=====================================================
// MENSAJE ÉXITO
//=====================================================

function mostrarMensajeExito(mensaje) {
  mostrarToastMovimiento(mensaje, "success");
}

//=====================================================
// MENSAJE ERROR
//=====================================================

function mostrarMensajeError(mensaje) {
  mostrarToastMovimiento(mensaje, "danger");
}

//=====================================================
// TOAST
//=====================================================

function mostrarToastMovimiento(mensaje, tipo) {
  let contenedor = document.getElementById("contenedorToastDepositoGasto");

  if (!contenedor) {
    contenedor = document.createElement("div");

    contenedor.id = "contenedorToastDepositoGasto";

    contenedor.className = "toast-container position-fixed top-0 end-0 p-3";

    contenedor.style.zIndex = "9999";

    document.body.appendChild(contenedor);
  }

  const toast = document.createElement("div");

  toast.className = "toast align-items-center text-bg-" + tipo + " border-0";

  toast.setAttribute("role", "alert");

  toast.setAttribute("aria-live", "assertive");

  toast.setAttribute("aria-atomic", "true");

  toast.innerHTML = `
        <div class="d-flex">

            <div class="toast-body">
                ${escapeHtml(mensaje)}
            </div>

            <button
                type="button"
                class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast"
                aria-label="Cerrar">
            </button>

        </div>
    `;

  contenedor.appendChild(toast);

  if (typeof bootstrap !== "undefined") {
    const instancia = new bootstrap.Toast(toast, {
      delay: 3500,
    });

    instancia.show();

    toast.addEventListener("hidden.bs.toast", function () {
      toast.remove();
    });
  } else {
    setTimeout(function () {
      toast.remove();
    }, 3500);
  }
}

//=====================================================
// EXPONER FUNCIONES GLOBALMENTE
//=====================================================

window.obtenerMovimientos = obtenerMovimientos;

window.obtenerKpiDepositoGasto = obtenerKpiDepositoGasto;

window.limpiarFiltrosMovimientos = limpiarFiltrosMovimientos;

window.abrirModalEditarMovimiento = abrirModalEditarMovimiento;

window.abrirModalEliminarMovimiento = abrirModalEliminarMovimiento;
