//=====================================================
// CoDevPro Technology
// Archivo: js/adm_estadisticas_proveedores.js
// Módulo: Estadísticas de Proveedores
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let graficoVentasProveedores = null;
let graficoDistribucionProveedores = null;
let graficoEvolucionProveedores = null;

let solicitudEstadisticasProveedores = null;

let temporizadorFiltrosProveedores = null;

//=====================================================
// DATOS ACTUALES DE ESTADÍSTICAS
//=====================================================
//
// Se conservan los datos recibidos del AJAX para poder
// utilizarlos posteriormente en la exportación.
//

let estadisticasProveedoresActuales = {
  kpi: {},

  grafico_ventas: {
    etiquetas: [],
    valores: [],
  },

  grafico_distribucion: {
    etiquetas: [],
    valores: [],
  },

  grafico_evolucion: {
    etiquetas: [],
    valores: [],
  },

  ranking_proveedores: [],

  productos_mas_vendidos: [],

  gastos: [],

  total_gastos: 0,
};

//=====================================================
// CONFIGURACIÓN
//=====================================================

const CONFIG_ESTADISTICAS_PROVEEDORES = {
  ajax: "ajax/obtener_estadisticas_proveedores.php",

  moneda: "S/",

  locale: "es-PE",

  limiteRanking: 10,

  limiteProductos: 10,
};

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarEstadisticasProveedores();
});

//=====================================================
// FUNCIÓN PRINCIPAL
//=====================================================

function inicializarEstadisticasProveedores() {
  inicializarFechas();

  inicializarEventos();

  cargarProveedores();

  cargarEstadisticasProveedores();
}

//=====================================================
// INICIALIZAR FECHAS
//=====================================================

function inicializarFechas() {
  const fechaInicio = document.getElementById("filtroFechaInicioProveedor");

  const fechaFin = document.getElementById("filtroFechaFinProveedor");

  if (!fechaInicio || !fechaFin) {
    return;
  }

  //=================================================
  // FECHA INICIO
  //=================================================

  flatpickr(fechaInicio, {
    locale: "es",

    dateFormat: "Y-m-d",

    altInput: true,

    altFormat: "d/m/Y",

    allowInput: true,

    disableMobile: true,

    onChange: function () {
      aplicarFiltrosAutomaticamente();
    },
  });

  //=================================================
  // FECHA FIN
  //=================================================

  flatpickr(fechaFin, {
    locale: "es",

    dateFormat: "Y-m-d",

    altInput: true,

    altFormat: "d/m/Y",

    allowInput: true,

    disableMobile: true,

    onChange: function () {
      aplicarFiltrosAutomaticamente();
    },
  });
}

//=====================================================
// INICIALIZAR EVENTOS
//=====================================================

function inicializarEventos() {
  const btnActualizar = document.getElementById(
    "btnActualizarEstadisticasProveedores",
  );

  const btnExportar = document.getElementById(
    "btnExportarEstadisticasProveedores",
  );
  const btnConfirmarExportacion = document.getElementById(
    "btnConfirmarExportacionProveedores",
  );
  const btnLimpiar = document.getElementById(
    "btnLimpiarFiltrosEstadisticasProveedores",
  );

  const filtroProveedor = document.getElementById(
    "filtroProveedorEstadisticas",
  );

  const filtroEstado = document.getElementById(
    "filtroEstadoProveedorEstadisticas",
  );

  const fechaInicio = document.getElementById("filtroFechaInicioProveedor");

  const fechaFin = document.getElementById("filtroFechaFinProveedor");

  //=================================================
  // ACTUALIZAR
  //=================================================

  if (btnActualizar) {
    btnActualizar.addEventListener("click", function () {
      cargarEstadisticasProveedores();
    });
  }

  //=================================================
  // EXPORTAR
  //=================================================

  if (btnExportar) {
    btnExportar.addEventListener("click", function () {
      exportarEstadisticasProveedores();
    });
  }

  //=================================================
  // CONFIRMAR EXPORTACIÓN
  //=================================================

  if (btnConfirmarExportacion) {
    btnConfirmarExportacion.addEventListener("click", function () {
      confirmarExportacionProveedores();
    });
  }

  //=================================================
  // LIMPIAR
  //=================================================

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function () {
      limpiarFiltrosProveedores();
    });
  }

  //=================================================
  // CAMBIO DE PROVEEDOR
  //=================================================

  if (filtroProveedor) {
    filtroProveedor.addEventListener("change", function () {
      aplicarFiltrosAutomaticamente();
    });
  }

  //=================================================
  // CAMBIO DE ESTADO
  //=================================================

  if (filtroEstado) {
    filtroEstado.addEventListener("change", function () {
      aplicarFiltrosAutomaticamente();
    });
  }
}
//=====================================================
// APLICAR FILTROS AUTOMÁTICAMENTE
//=====================================================

function aplicarFiltrosAutomaticamente() {
  clearTimeout(temporizadorFiltrosProveedores);

  temporizadorFiltrosProveedores = setTimeout(function () {
    cargarEstadisticasProveedores();
  }, 300);
}

//=====================================================
// CARGAR PROVEEDORES
//=====================================================

async function cargarProveedores() {
  const select = document.getElementById("filtroProveedorEstadisticas");

  if (!select) {
    return;
  }

  try {
    select.disabled = true;

    const respuesta = await fetch(
      CONFIG_ESTADISTICAS_PROVEEDORES.ajax + "?accion=proveedores",
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("No se pudo cargar la lista de proveedores.");
    }

    const data = await respuesta.json();

    if (!data || data.success !== true) {
      throw new Error(data?.mensaje || "No se pudo obtener los proveedores.");
    }
    select.innerHTML = "";

    const opcionTodos = document.createElement("option");

    opcionTodos.value = "";

    opcionTodos.textContent = "Todos los proveedores";

    select.appendChild(opcionTodos);

    if (Array.isArray(data.proveedores)) {
      data.proveedores.forEach(function (proveedor) {
        const opcion = document.createElement("option");

        opcion.value = proveedor.id_provedor;

        opcion.textContent = proveedor.nombre;

        select.appendChild(opcion);
      });
    }
  } catch (error) {
    console.error("Error al cargar proveedores:", error);

    mostrarErrorCarga("No se pudo cargar la lista de proveedores.");
  } finally {
    select.disabled = false;
  }
}

//=====================================================
// OBTENER FILTROS
//=====================================================

function obtenerFiltrosEstadisticasProveedores() {
  const proveedor = document.getElementById("filtroProveedorEstadisticas");

  const fechaInicio = document.getElementById("filtroFechaInicioProveedor");

  const fechaFin = document.getElementById("filtroFechaFinProveedor");

  const estado = document.getElementById("filtroEstadoProveedorEstadisticas");

  return {
    id_provedor: proveedor ? proveedor.value : "",

    fecha_inicio: fechaInicio ? fechaInicio.value : "",

    fecha_fin: fechaFin ? fechaFin.value : "",

    estado: estado ? estado.value : "todos",
  };
}

//=====================================================
// VALIDAR FECHAS
//=====================================================

function validarFechasEstadisticas() {
  const filtros = obtenerFiltrosEstadisticasProveedores();

  if (filtros.fecha_inicio && filtros.fecha_fin) {
    if (filtros.fecha_inicio > filtros.fecha_fin) {
      mostrarErrorCarga(
        "La fecha de inicio no puede ser mayor que la fecha de fin.",
      );

      return false;
    }
  }

  return true;
}

//=====================================================
// CARGAR ESTADÍSTICAS
//=====================================================

async function cargarEstadisticasProveedores() {
  if (!validarFechasEstadisticas()) {
    return;
  }

  mostrarEstadoCarga(true);

  //=================================================
  // CANCELAR PETICIÓN ANTERIOR
  //=================================================

  if (solicitudEstadisticasProveedores) {
    try {
      solicitudEstadisticasProveedores.abort();
    } catch (error) {
      console.warn("No se pudo cancelar la solicitud anterior.", error);
    }
  }

  const controlador = new AbortController();

  solicitudEstadisticasProveedores = controlador;

  const filtros = obtenerFiltrosEstadisticasProveedores();

  try {
    const parametros = new URLSearchParams();

    parametros.append("accion", "estadisticas");

    parametros.append("id_provedor", filtros.id_provedor);

    parametros.append("fecha_inicio", filtros.fecha_inicio);

    parametros.append("fecha_fin", filtros.fecha_fin);

    parametros.append("estado", filtros.estado);

    const respuesta = await fetch(
      CONFIG_ESTADISTICAS_PROVEEDORES.ajax + "?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        signal: controlador.signal,
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data || data.success !== true) {
      throw new Error(
        data?.mensaje || "No se pudieron cargar las estadísticas.",
      );
    }
    //=================================================
    // GUARDAR ESTADÍSTICAS ACTUALES
    //=================================================

    estadisticasProveedoresActuales = {
      kpi: data.kpi || {},

      grafico_ventas: data.grafico_ventas || {
        etiquetas: [],
        valores: [],
      },

      grafico_distribucion: data.grafico_distribucion || {
        etiquetas: [],
        valores: [],
      },

      grafico_evolucion: data.grafico_evolucion || {
        etiquetas: [],
        valores: [],
      },

      ranking_proveedores: Array.isArray(data.ranking_proveedores)
        ? data.ranking_proveedores
        : [],

      productos_mas_vendidos: Array.isArray(data.productos_mas_vendidos)
        ? data.productos_mas_vendidos
        : [],

      gastos: Array.isArray(data.gastos) ? data.gastos : [],

      total_gastos: Number(data.total_gastos) || 0,
    };
    //=================================================
    // ACTUALIZAR KPI
    //=================================================

    actualizarKPIProveedores(data.kpi || {});

    //=================================================
    // ACTUALIZAR GRÁFICOS
    //=================================================

    actualizarGraficoVentasProveedores(data.grafico_ventas || {});

    actualizarGraficoDistribucionProveedores(data.grafico_distribucion || {});

    actualizarGraficoEvolucionProveedores(data.grafico_evolucion || {});

    //=================================================
    // RANKING
    //=================================================

    actualizarRankingProveedores(data.ranking_proveedores || []);

    //=================================================
    // PRODUCTOS
    //=================================================

    actualizarProductosMasVendidos(data.productos_mas_vendidos || []);

    //=================================================
    // GASTOS
    //=================================================

    actualizarGastosProveedores(data.gastos || []);

    actualizarTotalGastos(data.total_gastos || 0);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar estadísticas de proveedores:", error);

    mostrarErrorCarga(
      error.message || "No se pudieron cargar las estadísticas.",
    );

    mostrarDatosVacios();
  } finally {
    if (solicitudEstadisticasProveedores === controlador) {
      solicitudEstadisticasProveedores = null;

      mostrarEstadoCarga(false);
    }
  }
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKPIProveedores(kpi) {
  establecerTexto(
    "kpiTotalProveedores",
    formatearNumero(kpi.total_proveedores || 0),
  );

  establecerTexto(
    "kpiProveedoresActivos",
    formatearNumero(kpi.proveedores_activos || 0),
  );

  establecerTexto(
    "kpiProductosProveedores",
    formatearNumero(kpi.productos_asociados || 0),
  );

  establecerTexto(
    "kpiValorInventarioProveedores",
    formatearMoneda(kpi.valor_inventario || 0),
  );

  establecerTexto(
    "kpiUnidadesVendidasProveedor",
    formatearNumero(kpi.unidades_vendidas || 0),
  );

  establecerTexto(
    "kpiVentasProveedores",
    formatearMoneda(kpi.ventas_generadas || 0),
  );

  establecerTexto(
    "kpiCostoProductosProveedores",
    formatearMoneda(kpi.costo_productos_vendidos || 0),
  );

  establecerTexto(
    "kpiMargenProveedores",
    formatearMoneda(kpi.margen_generado || 0),
  );
}

//=====================================================
// GRÁFICO - VENTAS POR PROVEEDOR
//=====================================================

function actualizarGraficoVentasProveedores(datos) {
  const canvas = document.getElementById("graficoVentasProveedores");

  if (!canvas) {
    return;
  }

  destruirGrafico(graficoVentasProveedores);

  const etiquetas = Array.isArray(datos.etiquetas) ? datos.etiquetas : [];

  const valores = Array.isArray(datos.valores) ? datos.valores : [];

  graficoVentasProveedores = new Chart(canvas, {
    type: "bar",

    data: {
      labels: etiquetas,

      datasets: [
        {
          label: "Ventas",

          data: valores,

          borderWidth: 1,

          borderRadius: 6,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      plugins: {
        legend: {
          display: false,
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              return " " + formatearMoneda(context.raw || 0);
            },
          },
        },
      },

      scales: {
        y: {
          beginAtZero: true,

          ticks: {
            callback: function (value) {
              return (
                CONFIG_ESTADISTICAS_PROVEEDORES.moneda +
                " " +
                formatearNumero(value)
              );
            },
          },
        },
      },
    },
  });
}

//=====================================================
// GRÁFICO - DISTRIBUCIÓN
//=====================================================

function actualizarGraficoDistribucionProveedores(datos) {
  const canvas = document.getElementById("graficoDistribucionProveedores");

  if (!canvas) {
    return;
  }

  destruirGrafico(graficoDistribucionProveedores);

  const etiquetas = Array.isArray(datos.etiquetas) ? datos.etiquetas : [];

  const valores = Array.isArray(datos.valores) ? datos.valores : [];

  graficoDistribucionProveedores = new Chart(canvas, {
    type: "doughnut",

    data: {
      labels: etiquetas,

      datasets: [
        {
          data: valores,

          borderWidth: 2,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      cutout: "65%",

      plugins: {
        legend: {
          position: "bottom",
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              return (
                " " + context.label + ": " + formatearMoneda(context.raw || 0)
              );
            },
          },
        },
      },
    },
  });
}

//=====================================================
// GRÁFICO - EVOLUCIÓN
//=====================================================

function actualizarGraficoEvolucionProveedores(datos) {
  const canvas = document.getElementById("graficoEvolucionProveedores");

  if (!canvas) {
    return;
  }

  destruirGrafico(graficoEvolucionProveedores);

  const etiquetas = Array.isArray(datos.etiquetas) ? datos.etiquetas : [];

  const valores = Array.isArray(datos.valores) ? datos.valores : [];

  graficoEvolucionProveedores = new Chart(canvas, {
    type: "line",

    data: {
      labels: etiquetas,

      datasets: [
        {
          label: "Ventas",

          data: valores,

          borderWidth: 3,

          tension: 0.35,

          fill: true,

          pointRadius: 4,

          pointHoverRadius: 6,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      interaction: {
        intersect: false,

        mode: "index",
      },

      plugins: {
        legend: {
          display: false,
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              return " Ventas: " + formatearMoneda(context.raw || 0);
            },
          },
        },
      },

      scales: {
        y: {
          beginAtZero: true,

          ticks: {
            callback: function (value) {
              return (
                CONFIG_ESTADISTICAS_PROVEEDORES.moneda +
                " " +
                formatearNumero(value)
              );
            },
          },
        },
      },
    },
  });
}

//=====================================================
// RANKING DE PROVEEDORES
//=====================================================

function actualizarRankingProveedores(proveedores) {
  const tbody = document.getElementById("tablaRankingProveedores");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  if (!Array.isArray(proveedores) || proveedores.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td
                    colspan="4"
                    class="text-center text-muted py-4">
                    No hay información disponible.
                </td>
            </tr>
        `;

    return;
  }

  proveedores
    .slice(0, CONFIG_ESTADISTICAS_PROVEEDORES.limiteRanking)
    .forEach(function (proveedor, indice) {
      const tr = document.createElement("tr");

      tr.innerHTML = `

                    <td>
                        <span class="fw-semibold">
                            ${indice + 1}
                        </span>
                    </td>

                    <td>
                        <div class="fw-semibold">
                            ${escaparHTML(proveedor.nombre || "Sin nombre")}
                        </div>
                    </td>

                    <td class="text-end">
                        ${formatearNumero(proveedor.productos || 0)}
                    </td>

                    <td class="text-end fw-semibold">
                        ${formatearMoneda(proveedor.ventas || 0)}
                    </td>

                `;

      tbody.appendChild(tr);
    });
}

//=====================================================
// PRODUCTOS MÁS VENDIDOS
//=====================================================

function actualizarProductosMasVendidos(productos) {
  const tbody = document.getElementById("tablaProductosMasVendidosProveedor");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  if (!Array.isArray(productos) || productos.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td
                    colspan="4"
                    class="text-center text-muted py-4">
                    No hay información disponible.
                </td>
            </tr>
        `;

    return;
  }

  productos
    .slice(0, CONFIG_ESTADISTICAS_PROVEEDORES.limiteProductos)
    .forEach(function (producto) {
      const tr = document.createElement("tr");

      tr.innerHTML = `

                    <td>
                        <div class="fw-semibold">
                            ${escaparHTML(producto.nombre || "Sin nombre")}
                        </div>
                    </td>

                    <td>
                        ${escaparHTML(producto.proveedor || "Sin proveedor")}
                    </td>

                    <td class="text-end">
                        ${formatearNumero(producto.unidades || 0)}
                    </td>

                    <td class="text-end fw-semibold">
                        ${formatearMoneda(producto.ventas || 0)}
                    </td>

                `;

      tbody.appendChild(tr);
    });
}

//=====================================================
// GASTOS DE PROVEEDORES
//=====================================================

function actualizarGastosProveedores(gastos) {
  const tbody = document.getElementById("tablaGastosProveedores");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = "";

  if (!Array.isArray(gastos) || gastos.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td
                    colspan="6"
                    class="text-center text-muted py-5">
                    No hay información disponible.
                </td>
            </tr>
        `;

    return;
  }

  gastos.forEach(function (gasto) {
    const tr = document.createElement("tr");

    tr.innerHTML = `

                <td>
                    ${formatearFecha(gasto.fecha)}
                </td>

                <td>
                    <span class="fw-semibold">
                        ${escaparHTML(gasto.proveedor || "Sin proveedor")}
                    </span>
                </td>

                <td>
                    ${escaparHTML(gasto.concepto || "-")}
                </td>

                <td>
                    ${escaparHTML(gasto.metodo_pago || "-")}
                </td>

                <td>
                    <span
                        class="badge bg-light text-dark border">
                        ${escaparHTML(gasto.tipo || "-")}
                    </span>
                </td>

                <td class="text-end fw-semibold">
                    ${formatearMoneda(gasto.monto || 0)}
                </td>

            `;

    tbody.appendChild(tr);
  });
}

//=====================================================
// TOTAL GASTOS
//=====================================================

function actualizarTotalGastos(total) {
  establecerTexto("totalGastosProveedores", formatearMoneda(total || 0));
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosProveedores() {
  const proveedor = document.getElementById("filtroProveedorEstadisticas");

  const estado = document.getElementById("filtroEstadoProveedorEstadisticas");

  const fechaInicio = document.getElementById("filtroFechaInicioProveedor");

  const fechaFin = document.getElementById("filtroFechaFinProveedor");

  if (proveedor) {
    proveedor.value = "";
  }

  if (estado) {
    estado.value = "todos";
  }

  //=================================================
  // LIMPIAR FLATPICKR
  //=================================================

  if (fechaInicio && fechaInicio._flatpickr) {
    fechaInicio._flatpickr.clear();
  } else if (fechaInicio) {
    fechaInicio.value = "";
  }

  if (fechaFin && fechaFin._flatpickr) {
    fechaFin._flatpickr.clear();
  } else if (fechaFin) {
    fechaFin.value = "";
  }

  cargarEstadisticasProveedores();
}

//=====================================================
// EXPORTAR ESTADÍSTICAS
//=====================================================

function exportarEstadisticasProveedores() {
  abrirModalExportacionProveedores();
}
//=====================================================
// ABRIR MODAL DE EXPORTACIÓN
//=====================================================

function abrirModalExportacionProveedores() {
  const modalElement = document.getElementById(
    "modalExportarEstadisticasProveedores",
  );

  if (!modalElement) {
    console.error(
      "No se encontró el modal de exportación de estadísticas de proveedores.",
    );

    return;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

  //=================================================
  // OBTENER FILTROS ACTUALES
  //=================================================

  const filtros = obtenerFiltrosEstadisticasProveedores();

  //=================================================
  // MOSTRAR FILTROS EN EL MODAL
  //=================================================

  const textoProveedor = document.getElementById("exportarFiltroProveedor");

  const textoFecha = document.getElementById("exportarFiltroFecha");

  const textoEstado = document.getElementById("exportarFiltroEstado");

  //=================================================
  // PROVEEDOR
  //=================================================

  if (textoProveedor) {
    const selectProveedor = document.getElementById(
      "filtroProveedorEstadisticas",
    );

    if (selectProveedor && selectProveedor.selectedIndex >= 0) {
      textoProveedor.textContent =
        selectProveedor.options[selectProveedor.selectedIndex].text;
    } else {
      textoProveedor.textContent = "Todos los proveedores";
    }
  }

  //=================================================
  // FECHAS
  //=================================================

  if (textoFecha) {
    if (filtros.fecha_inicio || filtros.fecha_fin) {
      textoFecha.textContent =
        (filtros.fecha_inicio
          ? formatearFecha(filtros.fecha_inicio)
          : "Inicio") +
        " - " +
        (filtros.fecha_fin ? formatearFecha(filtros.fecha_fin) : "Actualidad");
    } else {
      textoFecha.textContent = "Todo el período";
    }
  }

  //=================================================
  // ESTADO
  //=================================================

  if (textoEstado) {
    const estados = {
      todos: "Todos",
      activo: "Activos",
      inactivo: "Inactivos",
    };

    textoEstado.textContent = estados[filtros.estado] || "Todos";
  }

  //=================================================
  // ABRIR MODAL
  //=================================================

  modal.show();
}
//=====================================================
// EJECUTAR EXPORTACIÓN
//=====================================================

function ejecutarExportacionProveedores(tipo, formato, filtros) {
  //=================================================
  // VALIDAR XLSX
  //=================================================

  if (typeof XLSX === "undefined") {
    mostrarErrorCarga(
      "No se pudo cargar la librería de exportación. Verifica la conexión a Internet.",
    );

    return;
  }

  //=================================================
  // VALIDAR DATOS
  //=================================================

  const tieneDatos =
    estadisticasProveedoresActuales &&
    (Object.keys(estadisticasProveedoresActuales.kpi || {}).length > 0 ||
      estadisticasProveedoresActuales.ranking_proveedores.length > 0 ||
      estadisticasProveedoresActuales.productos_mas_vendidos.length > 0 ||
      estadisticasProveedoresActuales.gastos.length > 0);

  if (!tieneDatos) {
    Swal.fire({
      icon: "warning",
      title: "Sin información",
      text: "No hay estadísticas disponibles para exportar.",
      confirmButtonText: "Aceptar",
    });

    return;
  }

  //=================================================
  // VALIDAR FECHAS
  //=================================================

  if (!validarFechasEstadisticas()) {
    return;
  }

  //=================================================
  // MOSTRAR CARGANDO
  //=================================================

  Swal.fire({
    title: "Generando reporte",
    text: "Preparando la información para exportar...",
    allowOutsideClick: false,
    allowEscapeKey: false,
    didOpen: function () {
      Swal.showLoading();
    },
  });

  //=================================================
  // PEQUEÑO RETRASO PARA MOSTRAR EL LOADING
  //=================================================

  setTimeout(function () {
    try {
      if (formato === "excel") {
        exportarExcelProveedores(tipo, filtros);
      } else if (formato === "csv") {
        exportarCSVProveedores(tipo, filtros);
      } else {
        throw new Error("Formato de exportación no válido.");
      }

      Swal.close();

      Swal.fire({
        icon: "success",

        title: "Exportación completada",

        text:
          "El reporte de " +
          obtenerNombreTipoExportacion(tipo) +
          " se descargó correctamente.",

        confirmButtonText: "Aceptar",
      });
    } catch (error) {
      console.error("Error al exportar estadísticas:", error);

      Swal.close();

      Swal.fire({
        icon: "error",

        title: "Error al exportar",

        text: error.message || "No se pudo generar el archivo de exportación.",

        confirmButtonText: "Aceptar",
      });
    }
  }, 150);
}
//=====================================================
// EXPORTAR EXCEL
//=====================================================

function exportarExcelProveedores(tipo, filtros) {
  const workbook = XLSX.utils.book_new();

  //=================================================
  // INFORMACIÓN DE FILTROS
  //=================================================

  const informacionFiltros = obtenerInformacionFiltrosExportacion(filtros);

  //=================================================
  // REPORTE COMPLETO
  //=================================================

  if (tipo === "completo") {
    agregarHojaResumenProveedores(workbook, informacionFiltros);

    agregarHojaRankingProveedores(workbook);

    agregarHojaProductosProveedores(workbook);

    agregarHojaGastosProveedores(workbook);

    agregarHojaEvolucionProveedores(workbook);
  }

  //=================================================
  // RESUMEN
  //=================================================
  else if (tipo === "resumen") {
    agregarHojaResumenProveedores(workbook, informacionFiltros);
  }

  //=================================================
  // RANKING
  //=================================================
  else if (tipo === "ranking") {
    agregarHojaRankingProveedores(workbook);
  }

  //=================================================
  // PRODUCTOS
  //=================================================
  else if (tipo === "productos") {
    agregarHojaProductosProveedores(workbook);
  }

  //=================================================
  // GASTOS
  //=================================================
  else if (tipo === "gastos") {
    agregarHojaGastosProveedores(workbook);
  }

  //=================================================
  // NOMBRE DEL ARCHIVO
  //=================================================

  const nombreArchivo = generarNombreArchivoProveedores(tipo, "xlsx");

  //=================================================
  // DESCARGAR
  //=================================================

  XLSX.writeFile(workbook, nombreArchivo);
}
//=====================================================
// CONFIRMAR EXPORTACIÓN
//=====================================================

function confirmarExportacionProveedores() {
  //=================================================
  // OBTENER TIPO DE EXPORTACIÓN
  //=================================================

  const selectTipo = document.getElementById("tipoExportacionProveedores");

  if (!selectTipo) {
    console.error("No se encontró el selector de tipo de exportación.");

    return;
  }

  const tipo = selectTipo.value;

  //=================================================
  // OBTENER FORMATO
  //=================================================

  const radioFormato = document.querySelector(
    'input[name="formatoExportacionProveedores"]:checked',
  );

  if (!radioFormato) {
    Swal.fire({
      icon: "warning",
      title: "Selecciona un formato",
      text: "Debes seleccionar Excel o CSV.",
      confirmButtonText: "Aceptar",
    });

    return;
  }

  const formato = radioFormato.value;

  //=================================================
  // OBTENER FILTROS ACTUALES
  //=================================================

  const filtros = obtenerFiltrosEstadisticasProveedores();

  //=================================================
  // VALIDAR FECHAS
  //=================================================

  if (!validarFechasEstadisticas()) {
    return;
  }

  //=================================================
  // CERRAR MODAL
  //=================================================

  const modalElement = document.getElementById(
    "modalExportarEstadisticasProveedores",
  );

  if (modalElement) {
    const modal = bootstrap.Modal.getInstance(modalElement);

    if (modal) {
      modal.hide();
    }
  }

  //=================================================
  // OBTENER INFORMACIÓN DE FILTROS
  //=================================================

  const informacionFiltros = obtenerInformacionFiltrosExportacion(filtros);

  //=================================================
  // EJECUTAR EXPORTACIÓN
  //=================================================

  ejecutarExportacionProveedores(tipo, formato, informacionFiltros);
}
//=====================================================
// HOJA - RESUMEN
//=====================================================

function agregarHojaResumenProveedores(workbook, filtros) {
  const kpi = estadisticasProveedoresActuales.kpi || {};

  const datos = [
    ["ESTADÍSTICAS DE PROVEEDORES"],
    [],
    ["FILTROS APLICADOS"],
    ["Proveedor", filtros.proveedor],
    ["Período", filtros.periodo],
    ["Estado", filtros.estado],
    [],
    ["INDICADORES"],
    ["Indicador", "Valor"],
    ["Total proveedores", Number(kpi.total_proveedores) || 0],
    ["Proveedores activos", Number(kpi.proveedores_activos) || 0],
    ["Productos asociados", Number(kpi.productos_asociados) || 0],
    ["Valor del inventario", Number(kpi.valor_inventario) || 0],
    ["Unidades vendidas", Number(kpi.unidades_vendidas) || 0],
    ["Ventas generadas", Number(kpi.ventas_generadas) || 0],
    ["Costo de productos vendidos", Number(kpi.costo_productos_vendidos) || 0],
    ["Margen generado", Number(kpi.margen_generado) || 0],
    ["Total gastos", Number(estadisticasProveedoresActuales.total_gastos) || 0],
  ];

  const hoja = XLSX.utils.aoa_to_sheet(datos);

  //=================================================
  // ANCHOS
  //=================================================

  hoja["!cols"] = [
    {
      wch: 35,
    },
    {
      wch: 30,
    },
  ];

  //=================================================
  // FORMATO MONEDA
  //=================================================

  const filasMoneda = [13, 14, 15, 16, 17, 18];

  filasMoneda.forEach(function (fila) {
    const celda = hoja["B" + fila];

    if (celda) {
      celda.z = '"S/" #,##0.00';
    }
  });

  XLSX.utils.book_append_sheet(workbook, hoja, "Resumen");
}
//=====================================================
// HOJA - RANKING
//=====================================================

function agregarHojaRankingProveedores(workbook) {
  const datos = [["Posición", "Proveedor", "Productos", "Ventas"]];

  const ranking = estadisticasProveedoresActuales.ranking_proveedores || [];

  ranking.forEach(function (proveedor, indice) {
    datos.push([
      indice + 1,

      proveedor.nombre || "Sin nombre",

      Number(proveedor.productos) || 0,

      Number(proveedor.ventas) || 0,
    ]);
  });

  if (ranking.length === 0) {
    datos.push(["", "No hay información disponible.", "", ""]);
  }

  const hoja = XLSX.utils.aoa_to_sheet(datos);

  hoja["!cols"] = [
    {
      wch: 12,
    },
    {
      wch: 35,
    },
    {
      wch: 15,
    },
    {
      wch: 18,
    },
  ];

  //=================================================
  // FORMATO MONEDA
  //=================================================

  for (let fila = 2; fila <= datos.length; fila++) {
    const celda = hoja["D" + fila];

    if (celda) {
      celda.z = '"S/" #,##0.00';
    }
  }

  XLSX.utils.book_append_sheet(workbook, hoja, "Ranking");
}
//=====================================================
// HOJA - PRODUCTOS
//=====================================================

function agregarHojaProductosProveedores(workbook) {
  const datos = [["Producto", "Proveedor", "Unidades", "Ventas"]];

  const productos =
    estadisticasProveedoresActuales.productos_mas_vendidos || [];

  productos.forEach(function (producto) {
    datos.push([
      producto.nombre || "Sin nombre",

      producto.proveedor || "Sin proveedor",

      Number(producto.unidades) || 0,

      Number(producto.ventas) || 0,
    ]);
  });

  if (productos.length === 0) {
    datos.push(["No hay información disponible.", "", "", ""]);
  }

  const hoja = XLSX.utils.aoa_to_sheet(datos);

  hoja["!cols"] = [
    {
      wch: 40,
    },
    {
      wch: 35,
    },
    {
      wch: 15,
    },
    {
      wch: 18,
    },
  ];

  //=================================================
  // FORMATO MONEDA
  //=================================================

  for (let fila = 2; fila <= datos.length; fila++) {
    const celda = hoja["D" + fila];

    if (celda) {
      celda.z = '"S/" #,##0.00';
    }
  }

  XLSX.utils.book_append_sheet(workbook, hoja, "Productos");
}
//=====================================================
// HOJA - GASTOS
//=====================================================

function agregarHojaGastosProveedores(workbook) {
  const datos = [
    ["Fecha", "Proveedor", "Concepto", "Método de pago", "Tipo", "Monto"],
  ];

  const gastos = estadisticasProveedoresActuales.gastos || [];

  gastos.forEach(function (gasto) {
    datos.push([
      gasto.fecha || "",

      gasto.proveedor || "Sin proveedor",

      gasto.concepto || "-",

      gasto.metodo_pago || "-",

      gasto.tipo || "-",

      Number(gasto.monto) || 0,
    ]);
  });

  if (gastos.length === 0) {
    datos.push(["", "No hay información disponible.", "", "", "", 0]);
  }

  const hoja = XLSX.utils.aoa_to_sheet(datos);

  hoja["!cols"] = [
    {
      wch: 15,
    },
    {
      wch: 30,
    },
    {
      wch: 40,
    },
    {
      wch: 25,
    },
    {
      wch: 18,
    },
    {
      wch: 18,
    },
  ];

  //=================================================
  // FORMATO MONEDA
  //=================================================

  for (let fila = 2; fila <= datos.length; fila++) {
    const celda = hoja["F" + fila];

    if (celda) {
      celda.z = '"S/" #,##0.00';
    }
  }

  XLSX.utils.book_append_sheet(workbook, hoja, "Gastos");
}
//=====================================================
// HOJA - EVOLUCIÓN
//=====================================================

function agregarHojaEvolucionProveedores(workbook) {
  const datos = [["Período", "Ventas"]];

  const evolucion = estadisticasProveedoresActuales.grafico_evolucion || {};

  const etiquetas = Array.isArray(evolucion.etiquetas)
    ? evolucion.etiquetas
    : [];

  const valores = Array.isArray(evolucion.valores) ? evolucion.valores : [];

  const cantidad = Math.min(etiquetas.length, valores.length);

  for (let i = 0; i < cantidad; i++) {
    datos.push([etiquetas[i], Number(valores[i]) || 0]);
  }

  if (cantidad === 0) {
    datos.push(["No hay información disponible.", 0]);
  }

  const hoja = XLSX.utils.aoa_to_sheet(datos);

  hoja["!cols"] = [
    {
      wch: 25,
    },
    {
      wch: 20,
    },
  ];

  for (let fila = 2; fila <= datos.length; fila++) {
    const celda = hoja["B" + fila];

    if (celda) {
      celda.z = '"S/" #,##0.00';
    }
  }

  XLSX.utils.book_append_sheet(workbook, hoja, "Evolución");
}
//=====================================================
// INFORMACIÓN DE FILTROS PARA EXPORTACIÓN
//=====================================================

function obtenerInformacionFiltrosExportacion(filtros) {
  const selectProveedor = document.getElementById(
    "filtroProveedorEstadisticas",
  );

  let nombreProveedor = "Todos los proveedores";

  if (
    selectProveedor &&
    selectProveedor.selectedIndex >= 0 &&
    selectProveedor.value !== ""
  ) {
    nombreProveedor =
      selectProveedor.options[selectProveedor.selectedIndex].text;
  }

  let periodo = "Todo el período";

  if (filtros.fecha_inicio || filtros.fecha_fin) {
    periodo =
      (filtros.fecha_inicio ? formatearFecha(filtros.fecha_inicio) : "Inicio") +
      " - " +
      (filtros.fecha_fin ? formatearFecha(filtros.fecha_fin) : "Actualidad");
  }

  const estados = {
    todos: "Todos",

    activo: "Activos",

    inactivo: "Inactivos",
  };

  return {
    proveedor: nombreProveedor,

    periodo: periodo,

    estado: estados[filtros.estado] || "Todos",
  };
}
//=====================================================
// EXPORTAR CSV
//=====================================================

function exportarCSVProveedores(tipo, filtros) {
  let datos = [];

  //=================================================
  // RESUMEN
  //=================================================

  if (tipo === "resumen") {
    const kpi = estadisticasProveedoresActuales.kpi || {};

    datos = [
      ["ESTADÍSTICAS DE PROVEEDORES"],
      [],
      ["Proveedor", filtros.proveedor],
      ["Período", filtros.periodo],
      ["Estado", filtros.estado],
      [],
      ["Indicador", "Valor"],
      ["Total proveedores", Number(kpi.total_proveedores) || 0],
      ["Proveedores activos", Number(kpi.proveedores_activos) || 0],
      ["Productos asociados", Number(kpi.productos_asociados) || 0],
      ["Valor del inventario", Number(kpi.valor_inventario) || 0],
      ["Unidades vendidas", Number(kpi.unidades_vendidas) || 0],
      ["Ventas generadas", Number(kpi.ventas_generadas) || 0],
      [
        "Costo de productos vendidos",
        Number(kpi.costo_productos_vendidos) || 0,
      ],
      ["Margen generado", Number(kpi.margen_generado) || 0],
      [
        "Total gastos",
        Number(estadisticasProveedoresActuales.total_gastos) || 0,
      ],
    ];
  }

  //=================================================
  // RANKING
  //=================================================
  else if (tipo === "ranking") {
    datos = [["Posición", "Proveedor", "Productos", "Ventas"]];

    (estadisticasProveedoresActuales.ranking_proveedores || []).forEach(
      function (proveedor, indice) {
        datos.push([
          indice + 1,

          proveedor.nombre || "Sin nombre",

          Number(proveedor.productos) || 0,

          Number(proveedor.ventas) || 0,
        ]);
      },
    );
  }

  //=================================================
  // PRODUCTOS
  //=================================================
  else if (tipo === "productos") {
    datos = [["Producto", "Proveedor", "Unidades", "Ventas"]];

    (estadisticasProveedoresActuales.productos_mas_vendidos || []).forEach(
      function (producto) {
        datos.push([
          producto.nombre || "Sin nombre",

          producto.proveedor || "Sin proveedor",

          Number(producto.unidades) || 0,

          Number(producto.ventas) || 0,
        ]);
      },
    );
  }

  //=================================================
  // GASTOS
  //=================================================
  else if (tipo === "gastos") {
    datos = [
      ["Fecha", "Proveedor", "Concepto", "Método de pago", "Tipo", "Monto"],
    ];

    (estadisticasProveedoresActuales.gastos || []).forEach(function (gasto) {
      datos.push([
        gasto.fecha || "",

        gasto.proveedor || "Sin proveedor",

        gasto.concepto || "-",

        gasto.metodo_pago || "-",

        gasto.tipo || "-",

        Number(gasto.monto) || 0,
      ]);
    });
  }

  //=================================================
  // COMPLETO
  //=================================================
  else if (tipo === "completo") {
    // Para CSV no podemos tener varias hojas.
    // Por eso generamos un reporte consolidado.

    const kpi = estadisticasProveedoresActuales.kpi || {};

    datos = [
      ["ESTADÍSTICAS DE PROVEEDORES"],
      [],
      ["Proveedor", filtros.proveedor],
      ["Período", filtros.periodo],
      ["Estado", filtros.estado],
      [],
      ["RESUMEN"],
      ["Indicador", "Valor"],
      ["Total proveedores", Number(kpi.total_proveedores) || 0],
      ["Proveedores activos", Number(kpi.proveedores_activos) || 0],
      ["Productos asociados", Number(kpi.productos_asociados) || 0],
      ["Valor del inventario", Number(kpi.valor_inventario) || 0],
      ["Unidades vendidas", Number(kpi.unidades_vendidas) || 0],
      ["Ventas generadas", Number(kpi.ventas_generadas) || 0],
      [
        "Costo de productos vendidos",
        Number(kpi.costo_productos_vendidos) || 0,
      ],
      ["Margen generado", Number(kpi.margen_generado) || 0],
      [
        "Total gastos",
        Number(estadisticasProveedoresActuales.total_gastos) || 0,
      ],
      [],
      ["RANKING DE PROVEEDORES"],
      ["Posición", "Proveedor", "Productos", "Ventas"],
    ];

    (estadisticasProveedoresActuales.ranking_proveedores || []).forEach(
      function (proveedor, indice) {
        datos.push([
          indice + 1,

          proveedor.nombre || "Sin nombre",

          Number(proveedor.productos) || 0,

          Number(proveedor.ventas) || 0,
        ]);
      },
    );

    datos.push([]);

    datos.push(["PRODUCTOS MÁS VENDIDOS"]);

    datos.push(["Producto", "Proveedor", "Unidades", "Ventas"]);

    (estadisticasProveedoresActuales.productos_mas_vendidos || []).forEach(
      function (producto) {
        datos.push([
          producto.nombre || "Sin nombre",

          producto.proveedor || "Sin proveedor",

          Number(producto.unidades) || 0,

          Number(producto.ventas) || 0,
        ]);
      },
    );

    datos.push([]);

    datos.push(["GASTOS DE PROVEEDORES"]);

    datos.push([
      "Fecha",
      "Proveedor",
      "Concepto",
      "Método de pago",
      "Tipo",
      "Monto",
    ]);

    (estadisticasProveedoresActuales.gastos || []).forEach(function (gasto) {
      datos.push([
        gasto.fecha || "",

        gasto.proveedor || "Sin proveedor",

        gasto.concepto || "-",

        gasto.metodo_pago || "-",

        gasto.tipo || "-",

        Number(gasto.monto) || 0,
      ]);
    });
  }

  //=================================================
  // CREAR CSV
  //=================================================

  const hoja = XLSX.utils.aoa_to_sheet(datos);

  const csv = XLSX.utils.sheet_to_csv(hoja, {
    FS: ",",
  });

  //=================================================
  // BOM UTF-8
  //=================================================

  const blob = new Blob(["\uFEFF" + csv], {
    type: "text/csv;charset=utf-8;",
  });

  const url = URL.createObjectURL(blob);

  const enlace = document.createElement("a");

  enlace.href = url;

  enlace.download = generarNombreArchivoProveedores(tipo, "csv");

  document.body.appendChild(enlace);

  enlace.click();

  document.body.removeChild(enlace);

  URL.revokeObjectURL(url);
}
//=====================================================
// GENERAR NOMBRE DEL ARCHIVO
//=====================================================

function generarNombreArchivoProveedores(tipo, extension) {
  const fecha = new Date();

  const anio = fecha.getFullYear();

  const mes = String(fecha.getMonth() + 1).padStart(2, "0");

  const dia = String(fecha.getDate()).padStart(2, "0");

  const hora = String(fecha.getHours()).padStart(2, "0");

  const minuto = String(fecha.getMinutes()).padStart(2, "0");

  const segundo = String(fecha.getSeconds()).padStart(2, "0");

  const nombres = {
    completo: "reporte_completo_proveedores",

    resumen: "resumen_proveedores",

    ranking: "ranking_proveedores",

    productos: "productos_mas_vendidos_proveedores",

    gastos: "gastos_proveedores",
  };

  const nombre = nombres[tipo] || "estadisticas_proveedores";

  return (
    nombre +
    "_" +
    anio +
    mes +
    dia +
    "_" +
    hora +
    minuto +
    segundo +
    "." +
    extension
  );
}
//=====================================================
// NOMBRE DEL TIPO DE EXPORTACIÓN
//=====================================================

function obtenerNombreTipoExportacion(tipo) {
  const nombres = {
    completo: "completo",

    resumen: "resumen general",

    ranking: "ranking de proveedores",

    productos: "productos más vendidos",

    gastos: "gastos de proveedores",
  };

  return nombres[tipo] || "estadísticas";
}
//=====================================================
// ESTADO DE CARGA
//=====================================================

function mostrarEstadoCarga(mostrar) {
  const contenedor = document.getElementById(
    "estadoCargaEstadisticasProveedores",
  );

  if (!contenedor) {
    return;
  }

  if (mostrar) {
    contenedor.classList.remove("d-none");
  } else {
    contenedor.classList.add("d-none");
  }
}

//=====================================================
// MOSTRAR ERROR
//=====================================================

function mostrarErrorCarga(mensaje) {
  if (typeof Swal === "undefined") {
    console.error(mensaje);

    return;
  }

  Swal.fire({
    icon: "error",

    title: "Error",

    text: mensaje,

    confirmButtonText: "Aceptar",
  });
}

//=====================================================
// MOSTRAR DATOS VACÍOS
//=====================================================

function mostrarDatosVacios() {
  actualizarKPIProveedores({});

  actualizarGraficoVentasProveedores({});

  actualizarGraficoDistribucionProveedores({});

  actualizarGraficoEvolucionProveedores({});

  actualizarRankingProveedores([]);

  actualizarProductosMasVendidos([]);

  actualizarGastosProveedores([]);

  actualizarTotalGastos(0);
}

//=====================================================
// DESTRUIR GRÁFICO
//=====================================================

function destruirGrafico(grafico) {
  if (grafico) {
    grafico.destroy();
  }
}

//=====================================================
// ESTABLECER TEXTO
//=====================================================

function establecerTexto(id, valor) {
  const elemento = document.getElementById(id);

  if (elemento) {
    elemento.textContent = valor;
  }
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(valor) {
  const numero = Number(valor) || 0;

  return numero.toLocaleString("es-PE", {
    minimumFractionDigits: 0,

    maximumFractionDigits: 2,
  });
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  const numero = Number(valor) || 0;

  return (
    CONFIG_ESTADISTICAS_PROVEEDORES.moneda +
    " " +
    numero.toLocaleString("es-PE", {
      minimumFractionDigits: 2,

      maximumFractionDigits: 2,
    })
  );
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "-";
  }

  const partes = String(fecha).split("-");

  if (partes.length !== 3) {
    return fecha;
  }

  return partes[2] + "/" + partes[1] + "/" + partes[0];
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escaparHTML(valor) {
  const div = document.createElement("div");

  div.textContent = valor == null ? "" : String(valor);

  return div.innerHTML;
}

//=====================================================
// FIN DEL ARCHIVO
//=====================================================
