//=====================================================
// CoDevPro Technology
// Archivo: js/adm_estadisticas_ventas.js
//=====================================================

"use strict";

//=====================================================
// CONFIGURACIÓN
//=====================================================

const CONFIG_ESTADISTICAS_VENTAS = {
  // Endpoint para obtener las estadísticas
  ajaxUrl: "ajax/adm_estadisticas_ventas.php",

  // Endpoint para exportar Excel mediante PHP
  exportarUrl: "ajax/adm_exportar_ventas.php",

  // Cantidad de registros por página
  registrosPorPagina: 10,

  // Moneda
  moneda: "S/",

  // Formato de fecha recibido desde PHP
  formatoFechaServidor: "YYYY-MM-DD",
};

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let graficoVentas = null;

let graficoMetodosPago = null;

let graficoCategorias = null;

let graficoSucursales = null;

//=====================================================
// ESTADO ACTUAL DEL REPORTE
//=====================================================

let estadoReporte = {
  pagina: 1,

  totalPaginas: 1,

  totalRegistros: 0,

  filtros: {
    fechaDesde: "",
    fechaHasta: "",
    sucursal: "",
    metodoPago: "",
    estado: "",
    empleado: "",
    cliente: "",
    categoria: "",
  },

  periodoGrafico: "dia",
};

//=====================================================
// CONTROL AJAX
//=====================================================

let ajaxEstadisticasEnCurso = false;

let ajaxExportacionEnCurso = false;

//=====================================================
// ÚLTIMOS DATOS RECIBIDOS
//=====================================================

let datosEstadisticas = null;

//=====================================================
// DOCUMENT READY
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarModuloEstadisticasVentas();
});

//=====================================================
// INICIALIZAR MÓDULO
//=====================================================

function inicializarModuloEstadisticasVentas() {
  inicializarFlatpickr();

  inicializarEventosFiltros();

  inicializarBotonLimpiar();

  inicializarBotonAplicar();

  inicializarPeriodoGrafico();

  inicializarExportacion();

  inicializarPaginacion();

  inicializarEstadoInicial();

  cargarFiltrosIniciales();
}

//=====================================================
// FLATPICKR
//=====================================================

function inicializarFlatpickr() {
  const elementoFechaDesde = document.getElementById("fechaDesde");

  const elementoFechaHasta = document.getElementById("fechaHasta");

  if (!elementoFechaDesde || !elementoFechaHasta) {
    return;
  }

  if (
    typeof flatpickr === "undefined" ||
    typeof flatpickr.l10ns === "undefined"
  ) {
    console.warn("Flatpickr no está disponible.");

    return;
  }

  flatpickr.localize(flatpickr.l10ns.es);

  flatpickr(elementoFechaDesde, {
    dateFormat: "d/m/Y",

    allowInput: true,

    altInput: false,

    disableMobile: true,

    onChange: function () {
      actualizarTextoPeriodo();

      actualizarPeriodoExportacion();
    },
  });

  flatpickr(elementoFechaHasta, {
    dateFormat: "d/m/Y",

    allowInput: true,

    altInput: false,

    disableMobile: true,

    onChange: function () {
      actualizarTextoPeriodo();

      actualizarPeriodoExportacion();
    },
  });
}

//=====================================================
// EVENTOS DE FILTROS
//=====================================================

function inicializarEventosFiltros() {
  const idsFiltros = [
    "filtroSucursal",
    "filtroMetodoPago",
    "filtroEstado",
    "filtroEmpleado",
    "filtroCliente",
    "filtroCategoria",
  ];

  idsFiltros.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (!elemento) {
      return;
    }

    elemento.addEventListener("change", function () {
      actualizarTextoPeriodo();

      actualizarPeriodoExportacion();
    });
  });
}

//=====================================================
// BOTÓN APLICAR FILTROS
//=====================================================

function inicializarBotonAplicar() {
  const boton = document.getElementById("btnAplicarFiltros");

  if (!boton) {
    return;
  }

  boton.addEventListener("click", function () {
    if (ajaxEstadisticasEnCurso) {
      return;
    }

    estadoReporte.pagina = 1;

    cargarEstadisticasVentas();
  });
}

//=====================================================
// BOTÓN LIMPIAR
//=====================================================

function inicializarBotonLimpiar() {
  const boton = document.getElementById("btnLimpiarFiltros");

  if (!boton) {
    return;
  }

  boton.addEventListener("click", function () {
    limpiarFiltros();
  });
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltros() {
  const fechaDesde = document.getElementById("fechaDesde");

  const fechaHasta = document.getElementById("fechaHasta");

  //===================================================
  // FECHA DESDE
  //===================================================

  if (fechaDesde) {
    if (fechaDesde._flatpickr) {
      fechaDesde._flatpickr.clear();
    } else {
      fechaDesde.value = "";
    }
  }

  //===================================================
  // FECHA HASTA
  //===================================================

  if (fechaHasta) {
    if (fechaHasta._flatpickr) {
      fechaHasta._flatpickr.clear();
    } else {
      fechaHasta.value = "";
    }
  }

  //===================================================
  // SELECTS
  //===================================================

  const filtrosSelect = [
    "filtroSucursal",
    "filtroMetodoPago",
    "filtroEstado",
    "filtroEmpleado",
    "filtroCliente",
    "filtroCategoria",
  ];

  filtrosSelect.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (!elemento) {
      return;
    }

    elemento.value = "";
  });

  //===================================================
  // RESTABLECER PÁGINA
  //===================================================

  estadoReporte.pagina = 1;

  //===================================================
  // RESTABLECER PERÍODO DEL GRÁFICO
  //===================================================

  const periodoGrafico = document.getElementById("periodoGrafico");

  if (periodoGrafico) {
    periodoGrafico.value = "dia";
  }

  estadoReporte.periodoGrafico = "dia";

  //===================================================
  // ACTUALIZAR INTERFAZ
  //===================================================

  actualizarTextoPeriodo();

  actualizarPeriodoExportacion();

  //===================================================
  // CONSULTAR
  //===================================================

  cargarEstadisticasVentas();
}

//=====================================================
// PERÍODO DEL GRÁFICO
//=====================================================

function inicializarPeriodoGrafico() {
  const elemento = document.getElementById("periodoGrafico");

  if (!elemento) {
    return;
  }

  elemento.addEventListener("change", function () {
    estadoReporte.periodoGrafico = this.value || "dia";

    estadoReporte.pagina = 1;

    /*
     * El período del gráfico se envía al servidor.
     * Por lo tanto, debemos volver a consultar.
     */

    cargarEstadisticasVentas();
  });
}

//=====================================================
// TEXTO PERÍODO
//=====================================================

function actualizarTextoPeriodo() {
  const elemento = document.getElementById("textoPeriodoActual");

  if (!elemento) {
    return;
  }

  const fechaDesde = document.getElementById("fechaDesde")?.value || "";

  const fechaHasta = document.getElementById("fechaHasta")?.value || "";

  if (!fechaDesde && !fechaHasta) {
    elemento.innerHTML = "Selecciona un período para consultar las ventas.";

    return;
  }

  if (fechaDesde && fechaHasta) {
    elemento.innerHTML =
      `Período seleccionado: ` +
      `<strong>${escapeHtml(fechaDesde)}</strong> ` +
      `al ` +
      `<strong>${escapeHtml(fechaHasta)}</strong>`;

    return;
  }

  if (fechaDesde) {
    elemento.innerHTML = `Ventas desde <strong>${escapeHtml(fechaDesde)}</strong>`;

    return;
  }

  if (fechaHasta) {
    elemento.innerHTML = `Ventas hasta <strong>${escapeHtml(fechaHasta)}</strong>`;
  }
}

//=====================================================
// ACTUALIZAR PERÍODO EXPORTACIÓN
//=====================================================

function actualizarPeriodoExportacion() {
  /*
   * Esta función mantiene sincronizado el estado
   * de los filtros utilizados por la exportación.
   *
   * No realiza ninguna petición.
   */

  estadoReporte.filtros = obtenerFiltros();
}

//=====================================================
// OBTENER FILTROS
//=====================================================

function obtenerFiltros() {
  return {
    fechaDesde: convertirFechaParaServidor(obtenerValor("fechaDesde")),

    fechaHasta: convertirFechaParaServidor(obtenerValor("fechaHasta")),

    sucursal: obtenerValor("filtroSucursal"),

    metodoPago: obtenerValor("filtroMetodoPago"),

    estado: obtenerValor("filtroEstado"),

    empleado: obtenerValor("filtroEmpleado"),

    cliente: obtenerValor("filtroCliente"),

    categoria: obtenerValor("filtroCategoria"),

    periodoGrafico: estadoReporte.periodoGrafico || "dia",

    pagina: estadoReporte.pagina || 1,

    limite: CONFIG_ESTADISTICAS_VENTAS.registrosPorPagina,
  };
}

//=====================================================
// OBTENER VALOR
//=====================================================

function obtenerValor(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return "";
  }

  return elemento.value || "";
}

//=====================================================
// CONVERTIR FECHA
// DD/MM/YYYY -> YYYY-MM-DD
//=====================================================

function convertirFechaParaServidor(fecha) {
  if (!fecha) {
    return "";
  }

  const partes = String(fecha).split("/");

  if (partes.length !== 3) {
    return "";
  }

  const dia = partes[0].padStart(2, "0");

  const mes = partes[1].padStart(2, "0");

  const anio = partes[2];

  if (
    anio.length !== 4 ||
    isNaN(Number(dia)) ||
    isNaN(Number(mes)) ||
    isNaN(Number(anio))
  ) {
    return "";
  }

  return `${anio}-${mes}-${dia}`;
}

//=====================================================
// CARGAR ESTADÍSTICAS
//=====================================================

async function cargarEstadisticasVentas() {
  if (ajaxEstadisticasEnCurso) {
    return;
  }

  ajaxEstadisticasEnCurso = true;

  const filtros = obtenerFiltros();

  estadoReporte.filtros = filtros;

  mostrarCargando();

  try {
    const parametros = new URLSearchParams();

    parametros.append("accion", "obtener_estadisticas");

    Object.keys(filtros).forEach(function (clave) {
      parametros.append(clave, filtros[clave] ?? "");
    });

    const respuesta = await fetch(CONFIG_ESTADISTICAS_VENTAS.ajaxUrl, {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",

        "X-Requested-With": "XMLHttpRequest",
      },

      body: parametros.toString(),
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos || datos.success !== true) {
      throw new Error(
        datos?.mensaje || "No fue posible obtener las estadísticas.",
      );
    }

    datosEstadisticas = datos;

    procesarRespuestaEstadisticas(datos);
  } catch (error) {
    console.error("Error en estadísticas de ventas:", error);

    mostrarError(
      error.message || "Ocurrió un error al consultar las estadísticas.",
    );
  } finally {
    ajaxEstadisticasEnCurso = false;

    ocultarCargando();
  }
}

//=====================================================
// PROCESAR RESPUESTA
//=====================================================

function procesarRespuestaEstadisticas(datos) {
  const resumen = datos.resumen || {};

  const graficos = datos.graficos || {};

  const rankings = datos.rankings || {};

  const tabla = datos.tabla || {};

  actualizarResumen(resumen);

  actualizarGraficoVentas(graficos.evolucionVentas || []);

  actualizarGraficoMetodosPago(graficos.metodosPago || []);

  actualizarGraficoCategorias(graficos.categorias || []);

  actualizarGraficoSucursales(graficos.sucursales || []);

  actualizarRankingProductos(rankings.productos || []);

  actualizarRankingClientes(rankings.clientes || []);

  actualizarTablaVentas(tabla.registros || []);

  actualizarPaginacion(tabla);

  actualizarTotalRegistros(tabla);

  actualizarEstadoGraficos();

  actualizarPeriodoExportacion();
}

//=====================================================
// ACTUALIZAR RESUMEN
//=====================================================

function actualizarResumen(resumen) {
  establecerTexto("totalVentas", formatearNumero(resumen.totalVentas || 0));

  establecerTexto(
    "ingresosTotales",
    formatearMoneda(resumen.ingresosTotales || 0),
  );

  establecerTexto(
    "productosVendidos",
    formatearNumero(resumen.productosVendidos || 0),
  );

  establecerTexto(
    "ticketPromedio",
    formatearMoneda(resumen.ticketPromedio || 0),
  );

  establecerTexto(
    "utilidadEstimada",
    formatearMoneda(resumen.utilidadEstimada || 0),
  );

  establecerTexto(
    "margenEstimado",
    formatearPorcentaje(resumen.margenEstimado || 0),
  );

  establecerTexto(
    "clientesAtendidos",
    formatearNumero(resumen.clientesAtendidos || 0),
  );

  establecerTexto(
    "productosDiferentes",
    formatearNumero(resumen.productosDiferentes || 0),
  );

  actualizarComparaciones(resumen);
}

//=====================================================
// COMPARACIONES
//=====================================================

function actualizarComparaciones(resumen) {
  const comparaciones = document.querySelectorAll(".estadistica-comparacion");

  if (!comparaciones.length) {
    return;
  }

  const valores = [
    resumen.variacionVentas,

    resumen.variacionIngresos,

    resumen.variacionProductos,

    resumen.variacionTicket,
  ];

  comparaciones.forEach(function (elemento, indice) {
    const valor = Number(valores[indice] || 0);

    const span = elemento.querySelector("span");

    if (span) {
      span.textContent = formatearPorcentaje(valor);
    }

    elemento.classList.remove("positiva", "negativa", "neutral");

    const icono = elemento.querySelector("i");

    if (valor > 0) {
      elemento.classList.add("positiva");

      if (icono) {
        icono.className = "bi bi-arrow-up-short";
      }
    } else if (valor < 0) {
      elemento.classList.add("negativa");

      if (icono) {
        icono.className = "bi bi-arrow-down-short";
      }
    } else {
      elemento.classList.add("neutral");

      if (icono) {
        icono.className = "bi bi-dash";
      }
    }
  });
}

//=====================================================
// GRÁFICO EVOLUCIÓN
//=====================================================

function actualizarGraficoVentas(datos) {
  const canvas = document.getElementById("graficoVentas");

  if (!canvas) {
    return;
  }

  if (typeof Chart === "undefined") {
    console.warn("Chart.js no está disponible.");

    return;
  }

  const contexto = canvas.getContext("2d");

  const etiquetas = datos.map(function (item) {
    return item.etiqueta || "";
  });

  const valores = datos.map(function (item) {
    return Number(item.total || 0);
  });

  if (graficoVentas) {
    graficoVentas.destroy();

    graficoVentas = null;
  }

  graficoVentas = new Chart(contexto, {
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
              return "Ventas: " + formatearMoneda(context.parsed.y);
            },
          },
        },
      },

      scales: {
        y: {
          beginAtZero: true,

          ticks: {
            callback: function (valor) {
              return (
                CONFIG_ESTADISTICAS_VENTAS.moneda +
                " " +
                formatearNumeroDecimal(valor)
              );
            },
          },
        },
      },
    },
  });

  controlarEstadoGrafico("estadoGraficoVentas", datos.length > 0);
}

//=====================================================
// OBTENER DATOS DEL GRÁFICO
//=====================================================

function obtenerDatosGraficoVentas() {
  if (!datosEstadisticas) {
    return [];
  }

  const graficos = datosEstadisticas.graficos || {};

  return graficos.evolucionVentas || [];
}

//=====================================================
// GRÁFICO MÉTODOS DE PAGO
//=====================================================

function actualizarGraficoMetodosPago(datos) {
  const canvas = document.getElementById("graficoMetodosPago");

  if (!canvas) {
    return;
  }

  if (typeof Chart === "undefined") {
    return;
  }

  const contexto = canvas.getContext("2d");

  const etiquetas = datos.map(function (item) {
    return item.nombre || "Sin nombre";
  });

  const valores = datos.map(function (item) {
    return Number(item.total || 0);
  });

  if (graficoMetodosPago) {
    graficoMetodosPago.destroy();

    graficoMetodosPago = null;
  }

  graficoMetodosPago = new Chart(contexto, {
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
              return context.label + ": " + formatearMoneda(context.parsed);
            },
          },
        },
      },
    },
  });

  controlarEstadoGrafico("estadoGraficoMetodos", datos.length > 0);
}

//=====================================================
// GRÁFICO CATEGORÍAS
//=====================================================

function actualizarGraficoCategorias(datos) {
  const canvas = document.getElementById("graficoCategorias");

  if (!canvas) {
    return;
  }

  if (typeof Chart === "undefined") {
    return;
  }

  const contexto = canvas.getContext("2d");

  const etiquetas = datos.map(function (item) {
    return item.nombre || "";
  });

  const valores = datos.map(function (item) {
    return Number(item.total || 0);
  });

  if (graficoCategorias) {
    graficoCategorias.destroy();

    graficoCategorias = null;
  }

  graficoCategorias = new Chart(contexto, {
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

      indexAxis: "y",

      plugins: {
        legend: {
          display: false,
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              return "Ventas: " + formatearMoneda(context.parsed.x);
            },
          },
        },
      },

      scales: {
        x: {
          beginAtZero: true,
        },
      },
    },
  });

  controlarEstadoGrafico("estadoGraficoCategorias", datos.length > 0);
}

//=====================================================
// GRÁFICO SUCURSALES
//=====================================================

function actualizarGraficoSucursales(datos) {
  const canvas = document.getElementById("graficoSucursales");

  if (!canvas) {
    return;
  }

  if (typeof Chart === "undefined") {
    return;
  }

  const contexto = canvas.getContext("2d");

  const etiquetas = datos.map(function (item) {
    return item.nombre || "";
  });

  const valores = datos.map(function (item) {
    return Number(item.total || 0);
  });

  if (graficoSucursales) {
    graficoSucursales.destroy();

    graficoSucursales = null;
  }

  graficoSucursales = new Chart(contexto, {
    type: "bar",

    data: {
      labels: etiquetas,

      datasets: [
        {
          label: "Ingresos",

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
              return "Ingresos: " + formatearMoneda(context.parsed.y);
            },
          },
        },
      },

      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });

  controlarEstadoGrafico("estadoGraficoSucursales", datos.length > 0);
}

//=====================================================
// ESTADO GRÁFICOS
//=====================================================

function actualizarEstadoGraficos() {
  if (!datosEstadisticas) {
    return;
  }

  const graficos = datosEstadisticas.graficos || {};

  controlarEstadoGrafico(
    "estadoGraficoVentas",

    (graficos.evolucionVentas || []).length > 0,
  );

  controlarEstadoGrafico(
    "estadoGraficoMetodos",

    (graficos.metodosPago || []).length > 0,
  );

  controlarEstadoGrafico(
    "estadoGraficoCategorias",

    (graficos.categorias || []).length > 0,
  );

  controlarEstadoGrafico(
    "estadoGraficoSucursales",

    (graficos.sucursales || []).length > 0,
  );
}

//=====================================================
// CONTROLAR ESTADO DEL GRÁFICO
//=====================================================

function controlarEstadoGrafico(id, tieneDatos) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  elemento.style.display = tieneDatos ? "none" : "flex";
}

//=====================================================
// RANKING PRODUCTOS
//=====================================================

function actualizarRankingProductos(productos) {
  const contenedor = document.getElementById("rankingProductos");

  if (!contenedor) {
    return;
  }

  if (!productos.length) {
    contenedor.innerHTML = `
      <div class="ranking-empty">
        <i class="bi bi-box-seam"></i>

        <span>
          No hay productos para mostrar.
        </span>
      </div>
    `;

    return;
  }

  contenedor.innerHTML = productos
    .map(function (producto, indice) {
      return `
            <div class="ranking-item">

              <div class="ranking-position">
                ${indice + 1}
              </div>

              <div class="ranking-item-info">

                <strong>
                  ${escapeHtml(producto.nombre || "Producto")}
                </strong>

                <span>
                  ${formatearNumero(producto.cantidad || 0)}
                  unidades
                </span>

              </div>

              <div class="ranking-item-value">

                ${formatearMoneda(producto.total || 0)}

              </div>

            </div>
          `;
    })
    .join("");
}

//=====================================================
// RANKING CLIENTES
//=====================================================

function actualizarRankingClientes(clientes) {
  const contenedor = document.getElementById("rankingClientes");

  if (!contenedor) {
    return;
  }

  if (!clientes.length) {
    contenedor.innerHTML = `
      <div class="ranking-empty">

        <i class="bi bi-people"></i>

        <span>
          No hay clientes para mostrar.
        </span>

      </div>
    `;

    return;
  }

  contenedor.innerHTML = clientes
    .map(function (cliente, indice) {
      return `
            <div class="ranking-item">

              <div class="ranking-position">
                ${indice + 1}
              </div>

              <div class="ranking-item-info">

                <strong>
                  ${escapeHtml(cliente.nombre || "Cliente")}
                </strong>

                <span>
                  ${formatearNumero(cliente.ventas || 0)}
                  ventas
                </span>

              </div>

              <div class="ranking-item-value">

                ${formatearMoneda(cliente.total || 0)}

              </div>

            </div>
          `;
    })
    .join("");
}

//=====================================================
// TABLA DE VENTAS
//=====================================================

function actualizarTablaVentas(registros) {
  const tbody = document.getElementById("tablaVentasBody");

  if (!tbody) {
    return;
  }

  if (!registros.length) {
    tbody.innerHTML = `
      <tr class="tabla-empty-row">

        <td
          colspan="8"
          class="text-center">

          <div class="tabla-empty">

            <div class="tabla-empty-icon">
              <i class="bi bi-receipt"></i>
            </div>

            <strong>
              No hay ventas para mostrar
            </strong>

            <span>
              No existen operaciones que
              coincidan con los filtros.
            </span>

          </div>

        </td>

      </tr>
    `;

    return;
  }

  tbody.innerHTML = registros
    .map(function (venta) {
      return `
          <tr>

            <td>
              ${escapeHtml(venta.fecha || "-")}
            </td>

            <td>
              <strong>
                ${escapeHtml(venta.comprobante || "-")}
              </strong>
            </td>

            <td>
              ${escapeHtml(venta.cliente || "Cliente general")}
            </td>

            <td>
              ${escapeHtml(venta.empleado || "-")}
            </td>

            <td>
              ${escapeHtml(venta.metodoPago || "-")}
            </td>

            <td class="text-center">
              ${formatearNumero(venta.productos || 0)}
            </td>

            <td class="text-end">
              <strong>
                ${formatearMoneda(venta.total || 0)}
              </strong>
            </td>

            <td class="text-center">
              ${crearBadgeEstado(venta.estado)}
            </td>

          </tr>
        `;
    })
    .join("");
}

//=====================================================
// BADGE ESTADO
//=====================================================

function crearBadgeEstado(estado) {
  const valor = String(estado || "")
    .trim()
    .toUpperCase();

  const nombres = {
    PENDIENTE: "Pendiente",

    CONFIRMADO: "Confirmado",

    PREPARANDO: "Preparando",

    ASIGNADO: "Asignado",

    OBTENIDO: "Obtenido",

    ENTREGADO: "Entregado",

    NO_ENTREGADO: "No entregado",

    CANCELADO: "Cancelado",
  };

  const clases = {
    PENDIENTE: "estado-pendiente",

    CONFIRMADO: "estado-confirmado",

    PREPARANDO: "estado-preparando",

    ASIGNADO: "estado-asignado",

    OBTENIDO: "estado-obtenido",

    ENTREGADO: "estado-entregado",

    NO_ENTREGADO: "estado-no-entregado",

    CANCELADO: "estado-cancelado",
  };

  const texto = nombres[valor] || valor || "Sin estado";

  const clase = clases[valor] || "estado-default";

  return `
    <span
      class="badge estado-badge ${clase}">
      ${escapeHtml(texto)}
    </span>
  `;
}

//=====================================================
// PAGINACIÓN
//=====================================================

function inicializarPaginacion() {
  const contenedor = document.getElementById("paginacionVentas");

  if (!contenedor) {
    return;
  }

  contenedor.addEventListener("click", function (event) {
    const boton = event.target.closest("button[data-pagina]");

    if (!boton) {
      return;
    }

    if (boton.disabled) {
      return;
    }

    const pagina = parseInt(boton.dataset.pagina, 10);

    if (!pagina || pagina < 1) {
      return;
    }

    if (pagina === estadoReporte.pagina) {
      return;
    }

    estadoReporte.pagina = pagina;

    cargarEstadisticasVentas();
  });
}

//=====================================================
// ACTUALIZAR PAGINACIÓN
//=====================================================

function actualizarPaginacion(tabla) {
  const contenedor = document.getElementById("paginacionVentas");

  if (!contenedor) {
    return;
  }

  const total = Number(tabla.totalRegistros || 0);

  const paginaActual = Number(tabla.pagina || estadoReporte.pagina || 1);

  const limite = Number(
    tabla.limite || CONFIG_ESTADISTICAS_VENTAS.registrosPorPagina,
  );

  const totalPaginas = Math.max(1, Math.ceil(total / limite));

  estadoReporte.pagina = paginaActual;

  estadoReporte.totalPaginas = totalPaginas;

  estadoReporte.totalRegistros = total;

  if (total === 0) {
    contenedor.innerHTML = `
      <li class="page-item disabled">

        <button
          class="page-link"
          type="button"
          disabled>

          <i class="bi bi-chevron-left"></i>

        </button>

      </li>

      <li class="page-item active">

        <button
          class="page-link"
          type="button">

          1

        </button>

      </li>

      <li class="page-item disabled">

        <button
          class="page-link"
          type="button"
          disabled>

          <i class="bi bi-chevron-right"></i>

        </button>

      </li>
    `;

    return;
  }

  let html = "";

  //===================================================
  // ANTERIOR
  //===================================================

  html += `
    <li
      class="page-item ${paginaActual <= 1 ? "disabled" : ""}">

      <button
        class="page-link"
        type="button"
        data-pagina="${paginaActual - 1}"
        ${paginaActual <= 1 ? "disabled" : ""}>

        <i class="bi bi-chevron-left"></i>

      </button>

    </li>
  `;

  //===================================================
  // NÚMEROS
  //===================================================

  const paginas = generarPaginas(paginaActual, totalPaginas);

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
          class="page-item ${pagina === paginaActual ? "active" : ""}">

          <button
            class="page-link"
            type="button"
            data-pagina="${pagina}">

            ${pagina}

          </button>

        </li>
      `;
  });

  //===================================================
  // SIGUIENTE
  //===================================================

  html += `
    <li
      class="page-item ${paginaActual >= totalPaginas ? "disabled" : ""}">

      <button
        class="page-link"
        type="button"
        data-pagina="${paginaActual + 1}"
        ${paginaActual >= totalPaginas ? "disabled" : ""}>

        <i class="bi bi-chevron-right"></i>

      </button>

    </li>
  `;

  contenedor.innerHTML = html;
}

//=====================================================
// GENERAR PAGINACIÓN
//=====================================================

function generarPaginas(actual, total) {
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

  const paginas = [1];

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
// ACTUALIZAR TOTAL REGISTROS
//=====================================================

function actualizarTotalRegistros(tabla) {
  const total = Number(tabla.totalRegistros || 0);

  const pagina = Number(tabla.pagina || estadoReporte.pagina || 1);

  const limite = Number(
    tabla.limite || CONFIG_ESTADISTICAS_VENTAS.registrosPorPagina,
  );

  const inicio = total > 0 ? (pagina - 1) * limite + 1 : 0;

  const fin = Math.min(pagina * limite, total);

  establecerTexto(
    "totalRegistrosTabla",

    `${formatearNumero(total)} ${total === 1 ? "registro" : "registros"}`,
  );

  establecerTexto(
    "rangoRegistros",

    `${formatearNumero(inicio)} - ${formatearNumero(fin)}`,
  );

  establecerTexto(
    "totalRegistros",

    formatearNumero(total),
  );
}

//=====================================================
// EXPORTACIÓN
//=====================================================

function inicializarExportacion() {
  //===================================================
  // EXPORTAR TABLA
  //===================================================

  const botonTabla = document.getElementById("btnExportarTabla");

  if (botonTabla) {
    botonTabla.addEventListener("click", function () {
      exportarVentas("detalle");
    });
  }

  //===================================================
  // EXPORTAR ESTADÍSTICAS
  //===================================================

  const botonModal = document.getElementById("btnExportarEstadisticasVentas");

  if (botonModal) {
    botonModal.addEventListener("click", function () {
      exportarEstadisticas();
    });
  }
}

//=====================================================
// EXPORTAR TABLA DE VENTAS
//=====================================================

function exportarTablaVentas() {
  exportarVentas("detalle");
}

//=====================================================
// EXPORTAR ESTADÍSTICAS
//=====================================================

function exportarEstadisticas() {
  const modal = document.getElementById("modalExportarEstadisticasVentas");

  let tipoExportacion = "completo";

  /*
   * Si existen los radios del modal,
   * respetamos la selección realizada
   * por el usuario.
   */

  const radioSeleccionado = document.querySelector(
    'input[name="tipoExportacion"]:checked',
  );

  if (radioSeleccionado && radioSeleccionado.value) {
    tipoExportacion = radioSeleccionado.value;
  }

  /*
   * Compatibilidad con los IDs usados
   * en el modal actual.
   */

  const exportarResumen = document.getElementById("exportarResumen");

  const exportarDetalle = document.getElementById("exportarDetalle");

  const exportarGraficos = document.getElementById("exportarGraficos");

  if (exportarResumen && exportarDetalle && exportarGraficos) {
    if (
      exportarResumen.checked &&
      !exportarDetalle.checked &&
      !exportarGraficos.checked
    ) {
      tipoExportacion = "resumen";
    } else if (
      !exportarResumen.checked &&
      exportarDetalle.checked &&
      !exportarGraficos.checked
    ) {
      tipoExportacion = "detalle";
    } else if (
      !exportarResumen.checked &&
      !exportarDetalle.checked &&
      exportarGraficos.checked
    ) {
      tipoExportacion = "graficos";
    } else if (
      exportarResumen.checked &&
      exportarDetalle.checked &&
      exportarGraficos.checked
    ) {
      tipoExportacion = "completo";
    }
  }

  exportarVentas(tipoExportacion);
}

//=====================================================
// EXPORTAR VENTAS MEDIANTE PHP
//=====================================================

async function exportarVentas(tipoExportacion) {
  if (ajaxExportacionEnCurso) {
    return;
  }

  /*
   * Verificamos que ya exista una consulta.
   */

  if (!datosEstadisticas) {
    mostrarAdvertencia(
      "Primero debes consultar las estadísticas antes de exportar.",
    );

    return;
  }

  ajaxExportacionEnCurso = true;

  const botonesExportacion = document.querySelectorAll(
    "#btnExportarTabla, #btnExportarEstadisticasVentas",
  );

  botonesExportacion.forEach(function (boton) {
    boton.disabled = true;
  });

  try {
    const filtros = obtenerFiltros();

    /*
     * Para la exportación NO usamos la página actual.
     *
     * El PHP debe exportar todos los registros
     * que coincidan con los filtros.
     */

    filtros.pagina = 1;

    filtros.limite = 0;

    const parametros = new URLSearchParams();

    parametros.append("accion", "exportar");

    parametros.append("tipo", tipoExportacion || "completo");

    Object.keys(filtros).forEach(function (clave) {
      parametros.append(clave, filtros[clave] ?? "");
    });

    mostrarCargandoExportacion(true);

    const respuesta = await fetch(CONFIG_ESTADISTICAS_VENTAS.exportarUrl, {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",

        "X-Requested-With": "XMLHttpRequest",
      },

      body: parametros.toString(),
    });

    /*
     * El endpoint puede devolver:
     *
     * 1. Archivo XLSX
     * 2. JSON con error
     */

    const contentType = respuesta.headers.get("Content-Type") || "";

    if (!respuesta.ok || contentType.includes("application/json")) {
      let mensaje = "No fue posible generar el archivo Excel.";

      try {
        const datosError = await respuesta.json();

        mensaje = datosError?.mensaje || datosError?.error || mensaje;
      } catch (error) {
        /*
         * Si no es JSON,
         * conservamos el mensaje genérico.
         */
      }

      throw new Error(mensaje);
    }

    if (
      !contentType.includes("spreadsheetml") &&
      !contentType.includes("application/vnd.ms-excel") &&
      !contentType.includes("application/octet-stream")
    ) {
      /*
       * Algunos servidores pueden enviar
       * application/octet-stream.
       *
       * No bloqueamos la descarga si la
       * respuesta es correcta.
       */

      console.warn("Content-Type inesperado:", contentType);
    }

    const blob = await respuesta.blob();

    if (!blob || blob.size === 0) {
      throw new Error("El archivo generado está vacío.");
    }

    const nombreArchivo =
      obtenerNombreArchivoRespuesta(respuesta) ||
      generarNombreArchivo(obtenerPrefijoExportacion(tipoExportacion));

    descargarBlob(blob, nombreArchivo);

    mostrarExitoExportacion(nombreArchivo);
  } catch (error) {
    console.error("Error exportando estadísticas:", error);

    mostrarError(error.message || "No fue posible generar el archivo Excel.");
  } finally {
    ajaxExportacionEnCurso = false;

    mostrarCargandoExportacion(false);

    botonesExportacion.forEach(function (boton) {
      boton.disabled = false;
    });
  }
}

//=====================================================
// OBTENER PREFIJO EXPORTACIÓN
//=====================================================

function obtenerPrefijoExportacion(tipo) {
  switch (tipo) {
    case "resumen":
      return "resumen_ventas";

    case "detalle":
      return "detalle_ventas";

    case "graficos":
      return "graficos_ventas";

    case "completo":
    default:
      return "estadisticas_ventas";
  }
}

//=====================================================
// DESCARGAR BLOB
//=====================================================

function descargarBlob(blob, nombreArchivo) {
  const url = window.URL.createObjectURL(blob);

  const enlace = document.createElement("a");

  enlace.href = url;

  enlace.download = nombreArchivo;

  enlace.style.display = "none";

  document.body.appendChild(enlace);

  enlace.click();

  enlace.remove();

  window.setTimeout(function () {
    window.URL.revokeObjectURL(url);
  }, 1000);
}

//=====================================================
// OBTENER NOMBRE DEL ARCHIVO
//=====================================================

function obtenerNombreArchivoRespuesta(respuesta) {
  const encabezado = respuesta.headers.get("Content-Disposition");

  if (!encabezado) {
    return "";
  }

  /*
   * filename*=UTF-8''
   */

  const coincidenciaUtf8 = encabezado.match(/filename\*=UTF-8''([^;]+)/i);

  if (coincidenciaUtf8 && coincidenciaUtf8[1]) {
    try {
      return decodeURIComponent(coincidenciaUtf8[1].replace(/^"|"$/g, ""));
    } catch (error) {
      console.warn("No se pudo decodificar el nombre del archivo:", error);
    }
  }

  /*
   * filename="archivo.xlsx"
   */

  const coincidencia = encabezado.match(/filename="?([^";]+)"?/i);

  if (coincidencia && coincidencia[1]) {
    return coincidencia[1].trim();
  }

  return "";
}

//=====================================================
// MOSTRAR CARGANDO EXPORTACIÓN
//=====================================================

function mostrarCargandoExportacion(cargando) {
  const boton = document.getElementById("btnExportarEstadisticasVentas");

  if (!boton) {
    return;
  }

  if (cargando) {
    if (!boton.dataset.textoOriginalExportacion) {
      boton.dataset.textoOriginalExportacion = boton.innerHTML;
    }

    boton.disabled = true;

    boton.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-2"
        role="status"
        aria-hidden="true">
      </span>

      Generando...
    `;
  } else {
    boton.disabled = false;

    if (boton.dataset.textoOriginalExportacion) {
      boton.innerHTML = boton.dataset.textoOriginalExportacion;
    }
  }
}

//=====================================================
// MENSAJE ÉXITO EXPORTACIÓN
//=====================================================

function mostrarExitoExportacion(nombreArchivo) {
  /*
   * No mostramos SweetAlert por defecto
   * para no interrumpir la descarga.
   *
   * Si existe Swal, mostramos una alerta
   * pequeña y automática.
   */

  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: "success",

      title: "Exportación completada",

      text: "El archivo " + nombreArchivo + " fue generado correctamente.",

      timer: 2200,

      showConfirmButton: false,

      position: "top-end",

      toast: true,
    });
  }
}

//=====================================================
// GENERAR NOMBRE ARCHIVO DE RESPALDO
//=====================================================

function generarNombreArchivo(prefijo) {
  const fecha = new Date();

  const anio = fecha.getFullYear();

  const mes = String(fecha.getMonth() + 1).padStart(2, "0");

  const dia = String(fecha.getDate()).padStart(2, "0");

  const hora = String(fecha.getHours()).padStart(2, "0");

  const minuto = String(fecha.getMinutes()).padStart(2, "0");

  const segundo = String(fecha.getSeconds()).padStart(2, "0");

  return (
    prefijo + "_" + anio + mes + dia + "_" + hora + minuto + segundo + ".xlsx"
  );
}

//=====================================================
// CARGAR FILTROS INICIALES
//=====================================================

async function cargarFiltrosIniciales() {
  try {
    const parametros = new URLSearchParams();

    parametros.append("accion", "cargar_filtros");

    const respuesta = await fetch(CONFIG_ESTADISTICAS_VENTAS.ajaxUrl, {
      method: "POST",

      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",

        "X-Requested-With": "XMLHttpRequest",
      },

      body: parametros.toString(),
    });

    if (!respuesta.ok) {
      throw new Error("No se pudieron cargar los filtros.");
    }

    const datos = await respuesta.json();

    if (!datos || datos.success !== true) {
      throw new Error(datos?.mensaje || "No se pudieron cargar los filtros.");
    }

    cargarSelect(
      "filtroSucursal",

      datos.filtros?.sucursales || [],

      "id_sucursal",

      "nombre",

      "Todas las sucursales",
    );

    cargarSelect(
      "filtroMetodoPago",

      datos.filtros?.metodosPago || [],

      "id_metodo_pago",

      "nombre",

      "Todos los métodos",
    );

    cargarSelect(
      "filtroEmpleado",

      datos.filtros?.empleados || [],

      "id_empleado",

      "nombre",

      "Todos los empleados",
    );

    cargarSelect(
      "filtroCliente",

      datos.filtros?.clientes || [],

      "idCliente",

      "nombre",

      "Todos los clientes",
    );

    cargarSelect(
      "filtroCategoria",

      datos.filtros?.categorias || [],

      "id_categorias",

      "nombre",

      "Todas las categorías",
    );

    //=================================================
    // PRIMERA CONSULTA
    //=================================================

    cargarEstadisticasVentas();
  } catch (error) {
    console.error("Error cargando filtros iniciales:", error);

    /*
     * Aunque los filtros fallen,
     * intentamos cargar las estadísticas.
     */

    cargarEstadisticasVentas();
  }
}

//=====================================================
// CARGAR SELECT
//=====================================================

function cargarSelect(id, datos, campoId, campoNombre, textoInicial) {
  const select = document.getElementById(id);

  if (!select) {
    return;
  }

  select.innerHTML = "";

  const opcionInicial = document.createElement("option");

  opcionInicial.value = "";

  opcionInicial.textContent = textoInicial;

  select.appendChild(opcionInicial);

  if (!Array.isArray(datos)) {
    return;
  }

  datos.forEach(function (item) {
    const opcion = document.createElement("option");

    opcion.value = item[campoId] ?? "";

    opcion.textContent = item[campoNombre] ?? "";

    select.appendChild(opcion);
  });
}

//=====================================================
// ESTADO INICIAL
//=====================================================

function inicializarEstadoInicial() {
  actualizarTextoPeriodo();

  establecerTexto("totalVentas", "0");

  establecerTexto("ingresosTotales", formatearMoneda(0));

  establecerTexto("productosVendidos", "0");

  establecerTexto("ticketPromedio", formatearMoneda(0));

  establecerTexto("utilidadEstimada", formatearMoneda(0));

  establecerTexto("margenEstimado", formatearPorcentaje(0));

  establecerTexto("clientesAtendidos", "0");

  establecerTexto("productosDiferentes", "0");

  establecerTexto("totalRegistros", "0");

  establecerTexto("totalRegistrosTabla", "0 registros");

  establecerTexto("rangoRegistros", "0 - 0");
}

//=====================================================
// MOSTRAR CARGANDO
//=====================================================

function mostrarCargando() {
  const boton = document.getElementById("btnAplicarFiltros");

  if (boton) {
    if (!boton.dataset.textoOriginal) {
      boton.dataset.textoOriginal = boton.innerHTML;
    }

    boton.disabled = true;

    boton.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-2"
        role="status"
        aria-hidden="true">
      </span>

      Consultando...
    `;
  }

  const tablaBody = document.getElementById("tablaVentasBody");

  if (tablaBody) {
    tablaBody.innerHTML = `
      <tr>

        <td
          colspan="8"
          class="text-center py-5">

          <div
            class="d-flex
                   flex-column
                   align-items-center
                   gap-2">

            <div
              class="spinner-border text-primary"
              role="status">

              <span
                class="visually-hidden">
                Cargando...
              </span>

            </div>

            <span>
              Consultando estadísticas...
            </span>

          </div>

        </td>

      </tr>
    `;
  }
}

//=====================================================
// OCULTAR CARGANDO
//=====================================================

function ocultarCargando() {
  const boton = document.getElementById("btnAplicarFiltros");

  if (boton) {
    boton.disabled = false;

    if (boton.dataset.textoOriginal) {
      boton.innerHTML = boton.dataset.textoOriginal;
    }
  }
}

//=====================================================
// SWEET ALERT - ERROR
//=====================================================

function mostrarError(mensaje) {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: "error",

      title: "Error",

      text: mensaje || "Ocurrió un error inesperado.",

      confirmButtonText: "Aceptar",
    });

    return;
  }

  alert(mensaje || "Ocurrió un error inesperado.");
}

//=====================================================
// SWEET ALERT - ADVERTENCIA
//=====================================================

function mostrarAdvertencia(mensaje) {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: "warning",

      title: "Atención",

      text: mensaje || "Revisa la información.",

      confirmButtonText: "Aceptar",
    });

    return;
  }

  alert(mensaje || "Revisa la información.");
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(valor) {
  const numero = Number(valor) || 0;

  return new Intl.NumberFormat("es-PE", {
    maximumFractionDigits: 0,
  }).format(numero);
}

//=====================================================
// FORMATEAR NÚMERO DECIMAL
//=====================================================

function formatearNumeroDecimal(valor) {
  const numero = Number(valor) || 0;

  return new Intl.NumberFormat("es-PE", {
    minimumFractionDigits: 2,

    maximumFractionDigits: 2,
  }).format(numero);
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  const numero = Number(valor) || 0;

  return (
    CONFIG_ESTADISTICAS_VENTAS.moneda +
    " " +
    new Intl.NumberFormat("es-PE", {
      minimumFractionDigits: 2,

      maximumFractionDigits: 2,
    }).format(numero)
  );
}

//=====================================================
// FORMATEAR PORCENTAJE
//=====================================================

function formatearPorcentaje(valor) {
  const numero = Number(valor) || 0;

  return (
    new Intl.NumberFormat("es-PE", {
      minimumFractionDigits: 2,

      maximumFractionDigits: 2,
    }).format(numero) + "%"
  );
}

//=====================================================
// ESTABLECER TEXTO
//=====================================================

function establecerTexto(id, texto) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  elemento.textContent = texto;
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