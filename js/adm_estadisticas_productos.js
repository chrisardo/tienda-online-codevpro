//=====================================================
// CoDevPro Technology
// Archivo: js/adm_estadisticas_productos.js
// Módulo: Estadísticas de Productos
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let graficoVentasProductos = null;
let graficoProductosVendidos = null;
let graficoIngresosProductos = null;
let graficoVentasCategorias = null;

let temporizadorBusquedaProducto = null;

let paginaActualProductos = 1;

const registrosPorPaginaProductos = 10;

let solicitudEstadisticasActual = null;

//=====================================================
// CONFIGURACIÓN
//=====================================================

const URL_ESTADISTICAS_PRODUCTOS = "ajax/estadisticas_productos.php";

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarEstadisticasProductos();
});

//=====================================================
// INICIALIZAR MÓDULO
//=====================================================

function inicializarEstadisticasProductos() {
  configurarFechas();

  configurarFiltros();

  configurarBotones();

  configurarExportacion();
  configurarSeleccionExportacion();
  cargarFiltros();

  cargarEstadisticasProductos();
}

//=====================================================
// CONFIGURAR FECHAS
//=====================================================

function configurarFechas() {
  const fechaInicio = document.getElementById("fechaInicioProducto");

  const fechaFin = document.getElementById("fechaFinProducto");

  //-------------------------------------------------
  // FECHA INICIO
  //-------------------------------------------------

  if (fechaInicio && typeof flatpickr !== "undefined") {
    flatpickr(fechaInicio, {
      dateFormat: "Y-m-d",

      locale: "es",

      allowInput: true,
    });
  }

  //-------------------------------------------------
  // FECHA FIN
  //-------------------------------------------------

  if (fechaFin && typeof flatpickr !== "undefined") {
    flatpickr(fechaFin, {
      dateFormat: "Y-m-d",

      locale: "es",

      allowInput: true,
    });
  }
}

//=====================================================
// CONFIGURAR FILTROS
//=====================================================

function configurarFiltros() {
  //-------------------------------------------------
  // BUSCAR PRODUCTO
  //-------------------------------------------------

  const buscar = document.getElementById("buscarProductoEstadistica");

  if (buscar) {
    buscar.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaProducto);

      temporizadorBusquedaProducto = setTimeout(function () {
        paginaActualProductos = 1;

        cargarEstadisticasProductos();
      }, 400);
    });
  }

  //-------------------------------------------------
  // CATEGORÍA
  //-------------------------------------------------

  const categoria = document.getElementById("filtroCategoriaProducto");

  if (categoria) {
    categoria.addEventListener("change", function () {
      paginaActualProductos = 1;

      cargarEstadisticasProductos();
    });
  }

  //-------------------------------------------------
  // MARCA
  //-------------------------------------------------

  const marca = document.getElementById("filtroMarcaProducto");

  if (marca) {
    marca.addEventListener("change", function () {
      paginaActualProductos = 1;

      cargarEstadisticasProductos();
    });
  }

  //-------------------------------------------------
  // SUCURSAL
  //-------------------------------------------------

  const sucursal = document.getElementById("filtroSucursalProducto");

  if (sucursal) {
    sucursal.addEventListener("change", function () {
      paginaActualProductos = 1;

      cargarEstadisticasProductos();
    });
  }

  //-------------------------------------------------
  // TIPO
  //-------------------------------------------------

  const tipo = document.getElementById("filtroTipoProducto");

  if (tipo) {
    tipo.addEventListener("change", function () {
      paginaActualProductos = 1;

      cargarEstadisticasProductos();
    });
  }

  //-------------------------------------------------
  // STOCK
  //-------------------------------------------------

  const stock = document.getElementById("filtroStockProducto");

  if (stock) {
    stock.addEventListener("change", function () {
      paginaActualProductos = 1;

      cargarEstadisticasProductos();
    });
  }

  //-------------------------------------------------
  // ORDEN
  //-------------------------------------------------

  const ordenar = document.getElementById("ordenarEstadisticasProducto");

  if (ordenar) {
    ordenar.addEventListener("change", function () {
      paginaActualProductos = 1;

      cargarEstadisticasProductos();
    });
  }
}

//=====================================================
// CONFIGURAR BOTONES
//=====================================================

function configurarBotones() {
  //-------------------------------------------------
  // APLICAR FILTROS
  //-------------------------------------------------

  const btnAplicar = document.getElementById("btnAplicarFiltrosProducto");

  if (btnAplicar) {
    btnAplicar.addEventListener("click", function () {
      paginaActualProductos = 1;

      cargarEstadisticasProductos();
    });
  }

  //-------------------------------------------------
  // LIMPIAR FILTROS
  //-------------------------------------------------

  const btnLimpiar = document.getElementById("btnLimpiarFiltrosProducto");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function () {
      limpiarFiltrosProductos();
    });
  }

  //-------------------------------------------------
  // ACTUALIZAR
  //-------------------------------------------------

  const btnActualizar = document.getElementById("btnActualizarEstadisticas");

  if (btnActualizar) {
    btnActualizar.addEventListener("click", function () {
      cargarEstadisticasProductos();
    });
  }

  //-------------------------------------------------
  // PAGINACIÓN
  //-------------------------------------------------

  document.addEventListener("click", function (e) {
    const boton = e.target.closest(".btn-pagina-estadistica-producto");

    if (!boton) {
      return;
    }

    e.preventDefault();

    const pagina = parseInt(boton.dataset.pagina, 10);

    if (!pagina || pagina < 1) {
      return;
    }

    paginaActualProductos = pagina;

    cargarEstadisticasProductos();
  });
}

//=====================================================
// CARGAR FILTROS
//=====================================================

function cargarFiltros() {
  fetch(URL_ESTADISTICAS_PRODUCTOS, {
    method: "POST",

    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
    },

    body: new URLSearchParams({
      accion: "cargar_filtros",
    }),
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (data) {
      if (!data.estado) {
        throw new Error(data.mensaje || "No se pudieron cargar los filtros.");
      }

      //-------------------------------------------------
      // CATEGORÍAS
      //-------------------------------------------------

      const selectCategoria = document.getElementById(
        "filtroCategoriaProducto",
      );

      if (selectCategoria && Array.isArray(data.categorias)) {
        selectCategoria.innerHTML =
          '<option value="0">' + "Todas las categorías" + "</option>";

        data.categorias.forEach(function (categoria) {
          const option = document.createElement("option");

          option.value = categoria.id_categorias;

          option.textContent = categoria.nombre;

          selectCategoria.appendChild(option);
        });
      }

      //-------------------------------------------------
      // MARCAS
      //-------------------------------------------------

      const selectMarca = document.getElementById("filtroMarcaProducto");

      if (selectMarca && Array.isArray(data.marcas)) {
        selectMarca.innerHTML =
          '<option value="0">' + "Todas las marcas" + "</option>";

        data.marcas.forEach(function (marca) {
          const option = document.createElement("option");

          option.value = marca.id_marca;

          option.textContent = marca.nombre;

          selectMarca.appendChild(option);
        });
      }

      //-------------------------------------------------
      // SUCURSALES
      //-------------------------------------------------

      const selectSucursal = document.getElementById("filtroSucursalProducto");

      if (selectSucursal && Array.isArray(data.sucursales)) {
        selectSucursal.innerHTML =
          '<option value="0">' + "Todas las sucursales" + "</option>";

        data.sucursales.forEach(function (sucursal) {
          const option = document.createElement("option");

          option.value = sucursal.id_sucursal;

          option.textContent = sucursal.nombre;

          selectSucursal.appendChild(option);
        });
      }
    })

    .catch(function (error) {
      console.error("Error cargando filtros:", error);
    });
}

//=====================================================
// CARGAR ESTADÍSTICAS
//=====================================================

function cargarEstadisticasProductos() {
  //-------------------------------------------------
  // CANCELAR SOLICITUD ANTERIOR
  //-------------------------------------------------

  if (solicitudEstadisticasActual) {
    solicitudEstadisticasActual.abort();
  }

  solicitudEstadisticasActual = new AbortController();

  //-------------------------------------------------
  // OBTENER FILTROS
  //-------------------------------------------------

  const buscar =
    document.getElementById("buscarProductoEstadistica")?.value.trim() || "";

  const categoria =
    document.getElementById("filtroCategoriaProducto")?.value || "0";

  const marca = document.getElementById("filtroMarcaProducto")?.value || "0";

  const sucursal =
    document.getElementById("filtroSucursalProducto")?.value || "0";

  const tipo = document.getElementById("filtroTipoProducto")?.value || "";

  const stock = document.getElementById("filtroStockProducto")?.value || "";

  const ordenar =
    document.getElementById("ordenarEstadisticasProducto")?.value ||
    "ventas_desc";

  const fechaInicio =
    document.getElementById("fechaInicioProducto")?.value || "";

  const fechaFin = document.getElementById("fechaFinProducto")?.value || "";

  //-------------------------------------------------
  // MOSTRAR CARGANDO
  //-------------------------------------------------

  mostrarCargandoEstadisticas();

  //-------------------------------------------------
  // PARÁMETROS
  //-------------------------------------------------

  const parametros = new URLSearchParams({
    accion: "obtener_estadisticas",

    buscar: buscar,

    categoria: categoria,

    marca: marca,

    sucursal: sucursal,

    tipo: tipo,

    stock: stock,

    ordenar: ordenar,

    fecha_inicio: fechaInicio,

    fecha_fin: fechaFin,

    pagina: paginaActualProductos,

    limite: registrosPorPaginaProductos,
  });

  //-------------------------------------------------
  // AJAX
  //-------------------------------------------------

  fetch(URL_ESTADISTICAS_PRODUCTOS, {
    method: "POST",

    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
    },

    body: parametros,

    signal: solicitudEstadisticasActual.signal,
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })

    .then(function (data) {
      if (!data.estado) {
        throw new Error(
          data.mensaje || "No se pudieron obtener las estadísticas.",
        );
      }

      //-------------------------------------------------
      // KPI
      //-------------------------------------------------

      actualizarKPI(data);

      //-------------------------------------------------
      // TABLA
      //-------------------------------------------------

      actualizarTablaProductos(data);

      //-------------------------------------------------
      // PAGINACIÓN
      //-------------------------------------------------

      actualizarPaginacionProductos(data);

      //-------------------------------------------------
      // GRÁFICOS
      //-------------------------------------------------

      actualizarGraficosProductos(data);
    })

    .catch(function (error) {
      if (error.name === "AbortError") {
        return;
      }

      console.error("Error cargando estadísticas:", error);

      mostrarErrorEstadisticas(error.message);
    })

    .finally(function () {
      solicitudEstadisticasActual = null;
    });
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKPI(data) {
  const kpiProductos = document.getElementById("kpiProductos");

  const kpiProductosVendidos = document.getElementById("kpiProductosVendidos");

  const kpiUnidadesVendidas = document.getElementById("kpiUnidadesVendidas");

  const kpiIngresos = document.getElementById("kpiIngresos");

  const kpiValorInventario = document.getElementById("kpiValorInventario");

  const kpiGanancia = document.getElementById("kpiGanancia");

  const kpiProductosSinVentas = document.getElementById(
    "kpiProductosSinVentas",
  );

  const kpi = data.kpi || {};

  //-------------------------------------------------
  // PRODUCTOS
  //-------------------------------------------------

  if (kpiProductos) {
    kpiProductos.textContent = formatearNumero(kpi.total_productos || 0);
  }

  //-------------------------------------------------
  // PRODUCTOS VENDIDOS
  //-------------------------------------------------

  if (kpiProductosVendidos) {
    kpiProductosVendidos.textContent = formatearNumero(
      kpi.productos_vendidos || 0,
    );
  }

  //-------------------------------------------------
  // UNIDADES VENDIDAS
  //-------------------------------------------------

  if (kpiUnidadesVendidas) {
    kpiUnidadesVendidas.textContent = formatearNumero(
      kpi.unidades_vendidas || 0,
    );
  }

  //-------------------------------------------------
  // INGRESOS
  //-------------------------------------------------

  if (kpiIngresos) {
    kpiIngresos.textContent = "S/ " + formatearMoneda(kpi.ingresos || 0);
  }

  //-------------------------------------------------
  // VALOR INVENTARIO
  //-------------------------------------------------

  if (kpiValorInventario) {
    kpiValorInventario.textContent =
      "S/ " + formatearMoneda(kpi.valor_inventario || 0);
  }

  //-------------------------------------------------
  // GANANCIA
  //-------------------------------------------------

  if (kpiGanancia) {
    kpiGanancia.textContent = "S/ " + formatearMoneda(kpi.ganancia || 0);
  }

  //-------------------------------------------------
  // SIN VENTAS
  //-------------------------------------------------

  if (kpiProductosSinVentas) {
    kpiProductosSinVentas.textContent = formatearNumero(
      kpi.productos_sin_ventas || 0,
    );
  }
}

//=====================================================
// ACTUALIZAR TABLA
//=====================================================

function actualizarTablaProductos(data) {
  const tbody = document.getElementById("tbodyEstadisticasProductos");

  if (!tbody) {
    return;
  }

  if (data.tabla && String(data.tabla).trim() !== "") {
    tbody.innerHTML = data.tabla;

    return;
  }

  tbody.innerHTML = `

        <tr>

            <td
                colspan="10"
                class="text-center py-5 text-muted">

                <i class="bi bi-box-seam fs-1 d-block mb-3"></i>

                <div class="fw-semibold">
                    No se encontraron productos.
                </div>

                <small>
                    Intenta cambiar los filtros.
                </small>

            </td>

        </tr>

    `;
}

//=====================================================
// ACTUALIZAR PAGINACIÓN
//=====================================================

function actualizarPaginacionProductos(data) {
  const contenedor = document.getElementById("paginacionEstadisticasProductos");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = data.paginacion || "";
}

//=====================================================
// ACTUALIZAR GRÁFICOS
//=====================================================

function actualizarGraficosProductos(data) {
  if (typeof Chart === "undefined") {
    console.error("Chart.js no está disponible.");

    return;
  }

  crearGraficoVentasProductos(data);

  crearGraficoProductosVendidos(data);

  crearGraficoIngresosProductos(data);

  crearGraficoVentasCategorias(data);
}

//=====================================================
// GRÁFICO VENTAS DE PRODUCTOS
//=====================================================

function crearGraficoVentasProductos(data) {
  const canvas = document.getElementById("graficoVentasProductos");

  if (!canvas) {
    return;
  }

  const contexto = canvas.getContext("2d");

  if (graficoVentasProductos) {
    graficoVentasProductos.destroy();
  }

  const datos = Array.isArray(data.grafico_ventas) ? data.grafico_ventas : [];

  graficoVentasProductos = new Chart(contexto, {
    type: "line",

    data: {
      labels: datos.map(function (item) {
        return item.fecha || item.periodo || item.nombre || "";
      }),

      datasets: [
        {
          label: "Ventas",

          data: datos.map(function (item) {
            return parseFloat(item.total || item.ventas || 0);
          }),

          tension: 0.3,

          fill: true,

          borderWidth: 2,
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
          display: true,
        },
      },

      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

//=====================================================
// GRÁFICO PRODUCTOS MÁS VENDIDOS
//=====================================================

function crearGraficoProductosVendidos(data) {
  const canvas = document.getElementById("graficoProductosVendidos");

  if (!canvas) {
    return;
  }

  const contexto = canvas.getContext("2d");

  if (graficoProductosVendidos) {
    graficoProductosVendidos.destroy();
  }

  const datos = Array.isArray(data.grafico_productos_vendidos)
    ? data.grafico_productos_vendidos
    : [];

  graficoProductosVendidos = new Chart(contexto, {
    type: "bar",

    data: {
      labels: datos.map(function (item) {
        return item.nombre || "";
      }),

      datasets: [
        {
          label: "Unidades vendidas",

          data: datos.map(function (item) {
            return parseInt(item.cantidad || item.total || 0, 10);
          }),

          borderWidth: 1,
        },
      ],
    },

    options: {
      indexAxis: "y",

      responsive: true,

      maintainAspectRatio: false,

      plugins: {
        legend: {
          display: false,
        },
      },

      scales: {
        x: {
          beginAtZero: true,
        },
      },
    },
  });
}

//=====================================================
// GRÁFICO INGRESOS POR PRODUCTO
//=====================================================

function crearGraficoIngresosProductos(data) {
  const canvas = document.getElementById("graficoIngresosProductos");

  if (!canvas) {
    return;
  }

  const contexto = canvas.getContext("2d");

  if (graficoIngresosProductos) {
    graficoIngresosProductos.destroy();
  }

  const datos = Array.isArray(data.grafico_ingresos_productos)
    ? data.grafico_ingresos_productos
    : [];

  graficoIngresosProductos = new Chart(contexto, {
    type: "bar",

    data: {
      labels: datos.map(function (item) {
        return item.nombre || "";
      }),

      datasets: [
        {
          label: "Ingresos",

          data: datos.map(function (item) {
            return parseFloat(item.ingresos || item.total || 0);
          }),

          borderWidth: 1,
        },
      ],
    },

    options: {
      indexAxis: "y",

      responsive: true,

      maintainAspectRatio: false,

      plugins: {
        legend: {
          display: false,
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              return "S/ " + formatearMoneda(context.raw);
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
}

//=====================================================
// GRÁFICO VENTAS POR CATEGORÍA
//=====================================================

function crearGraficoVentasCategorias(data) {
  const canvas = document.getElementById("graficoVentasCategorias");

  if (!canvas) {
    return;
  }

  const contexto = canvas.getContext("2d");

  if (graficoVentasCategorias) {
    graficoVentasCategorias.destroy();
  }

  const datos = Array.isArray(data.grafico_categorias)
    ? data.grafico_categorias
    : [];

  graficoVentasCategorias = new Chart(contexto, {
    type: "doughnut",

    data: {
      labels: datos.map(function (item) {
        return item.nombre || "";
      }),

      datasets: [
        {
          data: datos.map(function (item) {
            return parseFloat(item.total || item.cantidad || 0);
          }),

          borderWidth: 1,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      plugins: {
        legend: {
          position: "bottom",
        },
      },
    },
  });
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosProductos() {
  //-------------------------------------------------
  // BUSCAR
  //-------------------------------------------------

  const buscar = document.getElementById("buscarProductoEstadistica");

  if (buscar) {
    buscar.value = "";
  }

  //-------------------------------------------------
  // CATEGORÍA
  //-------------------------------------------------

  const categoria = document.getElementById("filtroCategoriaProducto");

  if (categoria) {
    categoria.value = "0";
  }

  //-------------------------------------------------
  // MARCA
  //-------------------------------------------------

  const marca = document.getElementById("filtroMarcaProducto");

  if (marca) {
    marca.value = "0";
  }

  //-------------------------------------------------
  // SUCURSAL
  //-------------------------------------------------

  const sucursal = document.getElementById("filtroSucursalProducto");

  if (sucursal) {
    sucursal.value = "0";
  }

  //-------------------------------------------------
  // TIPO
  //-------------------------------------------------

  const tipo = document.getElementById("filtroTipoProducto");

  if (tipo) {
    tipo.value = "";
  }

  //-------------------------------------------------
  // STOCK
  //-------------------------------------------------

  const stock = document.getElementById("filtroStockProducto");

  if (stock) {
    stock.value = "";
  }

  //-------------------------------------------------
  // ORDEN
  //-------------------------------------------------

  const ordenar = document.getElementById("ordenarEstadisticasProducto");

  if (ordenar) {
    ordenar.value = "ventas_desc";
  }

  //-------------------------------------------------
  // FECHA INICIO
  //-------------------------------------------------

  const fechaInicio = document.getElementById("fechaInicioProducto");

  if (fechaInicio) {
    fechaInicio.value = "";

    if (fechaInicio._flatpickr) {
      fechaInicio._flatpickr.clear();
    }
  }

  //-------------------------------------------------
  // FECHA FIN
  //-------------------------------------------------

  const fechaFin = document.getElementById("fechaFinProducto");

  if (fechaFin) {
    fechaFin.value = "";

    if (fechaFin._flatpickr) {
      fechaFin._flatpickr.clear();
    }
  }

  //-------------------------------------------------
  // PAGINA
  //-------------------------------------------------

  paginaActualProductos = 1;

  //-------------------------------------------------
  // RECARGAR
  //-------------------------------------------------

  cargarEstadisticasProductos();
}

//=====================================================
// MOSTRAR CARGANDO
//=====================================================

function mostrarCargandoEstadisticas() {
  const tbody = document.getElementById("tbodyEstadisticasProductos");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = `

        <tr>

            <td
                colspan="10"
                class="text-center py-5">

                <div
                    class="spinner-border text-primary"
                    role="status">
                </div>

                <div class="mt-3 text-muted">

                    Cargando estadísticas...

                </div>

            </td>

        </tr>

    `;
}

//=====================================================
// MOSTRAR ERROR
//=====================================================

function mostrarErrorEstadisticas(mensaje) {
  const tbody = document.getElementById("tbodyEstadisticasProductos");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = `

        <tr>

            <td
                colspan="10"
                class="py-4 px-4">

                <div
                    class="alert alert-danger mb-0">

                    <div class="d-flex align-items-center">

                        <i
                            class="bi bi-exclamation-triangle-fill me-2">
                        </i>

                        <div>

                            <strong>
                                No se pudieron cargar las estadísticas.
                            </strong>

                            <div class="small mt-1">

                                ${escaparHTML(mensaje)}

                            </div>

                        </div>

                    </div>

                </div>

            </td>

        </tr>

    `;
}

//=====================================================
// CONFIGURAR EXPORTACIÓN
//=====================================================

function configurarExportacion() {
  //-------------------------------------------------
  // BOTÓN ABRIR MODAL
  //-------------------------------------------------

  const btnExportar = document.getElementById("btnExportarEstadisticas");

  if (btnExportar) {
    btnExportar.addEventListener("click", function () {
      abrirModalExportacion();

      actualizarResumenFiltrosExportacion();
    });
  }

  //-------------------------------------------------
  // BOTÓN CONFIRMAR EXPORTACIÓN
  //-------------------------------------------------

  const btnConfirmar = document.getElementById(
    "btnConfirmarExportacionProductos",
  );

  if (btnConfirmar) {
    btnConfirmar.addEventListener("click", function () {
      exportarEstadisticasProductos();
    });
  }
}

//=====================================================
// ABRIR MODAL EXPORTACIÓN
//=====================================================

function abrirModalExportacion() {
  const modal = document.getElementById("modalExportarEstadisticasProductos");

  if (!modal || typeof bootstrap === "undefined") {
    console.warn("No se encontró el modal de exportación.");

    return;
  }

  const instancia = bootstrap.Modal.getOrCreateInstance(modal);

  instancia.show();
}
//=====================================================
// ACTUALIZAR RESUMEN DE FILTROS
//=====================================================

function actualizarResumenFiltrosExportacion() {
  const contenedor = document.getElementById(
    "resumenFiltrosExportacionProducto",
  );

  if (!contenedor) {
    return;
  }

  //-------------------------------------------------
  // OBTENER FILTROS
  //-------------------------------------------------

  const buscar =
    document.getElementById("buscarProductoEstadistica")?.value.trim() || "";

  const categoria =
    document.getElementById("filtroCategoriaProducto")?.selectedOptions[0]
      ?.text || "Todas las categorías";

  const marca =
    document.getElementById("filtroMarcaProducto")?.selectedOptions[0]?.text ||
    "Todas las marcas";

  const sucursal =
    document.getElementById("filtroSucursalProducto")?.selectedOptions[0]
      ?.text || "Todas las sucursales";

  const tipo =
    document.getElementById("filtroTipoProducto")?.selectedOptions[0]?.text ||
    "Todos los tipos";

  const stock =
    document.getElementById("filtroStockProducto")?.selectedOptions[0]?.text ||
    "Todos";

  const fechaInicio =
    document.getElementById("fechaInicioProducto")?.value || "";

  const fechaFin = document.getElementById("fechaFinProducto")?.value || "";

  //-------------------------------------------------
  // CREAR RESUMEN
  //-------------------------------------------------

  const filtros = [];

  if (buscar) {
    filtros.push(
      `<span class="badge text-bg-primary me-1 mb-1">
        Producto: ${escaparHTML(buscar)}
      </span>`,
    );
  }

  if (categoria !== "Todas las categorías") {
    filtros.push(
      `<span class="badge text-bg-light border me-1 mb-1">
        Categoría: ${escaparHTML(categoria)}
      </span>`,
    );
  }

  if (marca !== "Todas las marcas") {
    filtros.push(
      `<span class="badge text-bg-light border me-1 mb-1">
        Marca: ${escaparHTML(marca)}
      </span>`,
    );
  }

  if (sucursal !== "Todas las sucursales") {
    filtros.push(
      `<span class="badge text-bg-light border me-1 mb-1">
        Sucursal: ${escaparHTML(sucursal)}
      </span>`,
    );
  }

  if (tipo !== "Todos los tipos") {
    filtros.push(
      `<span class="badge text-bg-light border me-1 mb-1">
        Tipo: ${escaparHTML(tipo)}
      </span>`,
    );
  }

  if (stock !== "Todos") {
    filtros.push(
      `<span class="badge text-bg-light border me-1 mb-1">
        Stock: ${escaparHTML(stock)}
      </span>`,
    );
  }

  if (fechaInicio || fechaFin) {
    filtros.push(
      `<span class="badge text-bg-light border me-1 mb-1">
        Fecha: ${escaparHTML(fechaInicio || "...")}
        -
        ${escaparHTML(fechaFin || "...")}
      </span>`,
    );
  }

  //-------------------------------------------------
  // MOSTRAR
  //-------------------------------------------------

  if (filtros.length === 0) {
    contenedor.innerHTML =
      "Se exportarán todos los productos según los filtros actuales.";
  } else {
    contenedor.innerHTML = filtros.join("");
  }
}
//=====================================================
// EXPORTAR ESTADÍSTICAS DE PRODUCTOS
//=====================================================

function exportarEstadisticasProductos() {
  if (typeof XLSX === "undefined") {
    mostrarAlerta("Error", "No se pudo cargar la librería de Excel.", "error");

    return;
  }

  //-------------------------------------------------
  // OBTENER OPCIONES
  //-------------------------------------------------

  const alcance =
    document.querySelector('input[name="alcanceExportacionProducto"]:checked')
      ?.value || "todos";

  //-------------------------------------------------
  // COLUMNAS
  //-------------------------------------------------

  const columnas = [];

  if (document.getElementById("exportarCodigoProducto")?.checked) {
    columnas.push({
      clave: "codigo",
      nombre: "Código",
    });
  }

  if (document.getElementById("exportarNombreProducto")?.checked) {
    columnas.push({
      clave: "nombre",
      nombre: "Producto",
    });
  }

  if (document.getElementById("exportarCategoriaProducto")?.checked) {
    columnas.push({
      clave: "categoria",
      nombre: "Categoría",
    });
  }

  if (document.getElementById("exportarMarcaProducto")?.checked) {
    columnas.push({
      clave: "marca",
      nombre: "Marca",
    });
  }

  if (document.getElementById("exportarSucursalProducto")?.checked) {
    columnas.push({
      clave: "sucursal",
      nombre: "Sucursal",
    });
  }

  if (document.getElementById("exportarTipoProducto")?.checked) {
    columnas.push({
      clave: "tipo",
      nombre: "Tipo",
    });
  }

  if (document.getElementById("exportarStockProducto")?.checked) {
    columnas.push({
      clave: "stock",
      nombre: "Stock",
    });
  }

  if (document.getElementById("exportarCostoProducto")?.checked) {
    columnas.push({
      clave: "costo",
      nombre: "Costo de compra",
    });
  }

  if (document.getElementById("exportarPrecioProducto")?.checked) {
    columnas.push({
      clave: "precio",
      nombre: "Precio de venta",
    });
  }

  if (document.getElementById("exportarValorInventario")?.checked) {
    columnas.push({
      clave: "valor_inventario",
      nombre: "Valor inventario",
    });
  }

  if (document.getElementById("exportarUnidadesVendidas")?.checked) {
    columnas.push({
      clave: "vendidos",
      nombre: "Unidades vendidas",
    });
  }

  if (document.getElementById("exportarIngresosProducto")?.checked) {
    columnas.push({
      clave: "ingresos",
      nombre: "Ingresos",
    });
  }

  if (document.getElementById("exportarGananciaProducto")?.checked) {
    columnas.push({
      clave: "ganancia",
      nombre: "Ganancia",
    });
  }

  if (document.getElementById("exportarMargenProducto")?.checked) {
    columnas.push({
      clave: "margen",
      nombre: "Margen",
    });
  }

  //-------------------------------------------------
  // VALIDAR COLUMNAS
  //-------------------------------------------------

  if (columnas.length === 0) {
    mostrarAlerta(
      "Selecciona información",
      "Debes seleccionar al menos un dato para exportar.",
      "warning",
    );

    return;
  }

  //-------------------------------------------------
  // OBTENER DATOS
  //-------------------------------------------------

  let filas = obtenerFilasEstadisticasProductos();

  //-------------------------------------------------
  // PÁGINA ACTUAL
  //-------------------------------------------------

  if (alcance === "pagina") {
    filas = filas.slice(0, registrosPorPaginaProductos);
  }

  //-------------------------------------------------
  // VALIDAR DATOS
  //-------------------------------------------------

  if (filas.length === 0) {
    mostrarAlerta(
      "Sin datos",
      "No existen productos para exportar.",
      "warning",
    );

    return;
  }

  //-------------------------------------------------
  // CONSTRUIR EXCEL
  //-------------------------------------------------

  const datosExcel = [];

  //-------------------------------------------------
  // ENCABEZADOS
  //-------------------------------------------------

  datosExcel.push(
    columnas.map(function (columna) {
      return columna.nombre;
    }),
  );

  //-------------------------------------------------
  // FILAS
  //-------------------------------------------------

  filas.forEach(function (fila) {
    datosExcel.push(
      columnas.map(function (columna) {
        let valor = fila[columna.clave];

        if (valor === null || typeof valor === "undefined") {
          valor = "";
        }

        //-------------------------------------------------
        // FORMATO MONEDA
        //-------------------------------------------------

        if (
          [
            "costo",
            "precio",
            "valor_inventario",
            "ingresos",
            "ganancia",
          ].includes(columna.clave)
        ) {
          valor = Number(valor) || 0;
        }

        //-------------------------------------------------
        // FORMATO MARGEN
        //-------------------------------------------------

        if (columna.clave === "margen") {
          valor = Number(valor) || 0;
        }

        return valor;
      }),
    );
  });

  //-------------------------------------------------
  // CREAR HOJA
  //-------------------------------------------------

  const hoja = XLSX.utils.aoa_to_sheet(datosExcel);

  //-------------------------------------------------
  // ANCHOS
  //-------------------------------------------------

  hoja["!cols"] = columnas.map(function (columna) {
    let ancho = columna.nombre.length + 3;

    if (ancho < 15) {
      ancho = 15;
    }

    if (ancho > 35) {
      ancho = 35;
    }

    return {
      wch: ancho,
    };
  });

  //-------------------------------------------------
  // CREAR LIBRO
  //-------------------------------------------------

  const libro = XLSX.utils.book_new();

  //-------------------------------------------------
  // NOMBRE HOJA
  //-------------------------------------------------

  let nombreHoja =
    document.getElementById("nombreHojaExportacionProducto")?.value.trim() ||
    "Productos";

  nombreHoja = nombreHoja.replace(/[\\\/\?\*\[\]\:]/g, "").substring(0, 31);

  if (!nombreHoja) {
    nombreHoja = "Productos";
  }

  XLSX.utils.book_append_sheet(libro, hoja, nombreHoja);

  //-------------------------------------------------
  // NOMBRE ARCHIVO
  //-------------------------------------------------

  let nombreArchivo =
    document.getElementById("nombreArchivoExportacionProducto")?.value.trim() ||
    "estadisticas_productos";

  nombreArchivo = nombreArchivo
    .replace(/[\\\/\?\*\[\]\:]/g, "")
    .replace(/\.xlsx$/i, "");

  if (!nombreArchivo) {
    nombreArchivo = "estadisticas_productos";
  }

  //-------------------------------------------------
  // EXPORTAR
  //-------------------------------------------------

  XLSX.writeFile(libro, nombreArchivo + ".xlsx");

  //-------------------------------------------------
  // CERRAR MODAL
  //-------------------------------------------------

  const modal = document.getElementById("modalExportarEstadisticasProductos");

  if (modal && typeof bootstrap !== "undefined") {
    const instancia = bootstrap.Modal.getInstance(modal);

    if (instancia) {
      instancia.hide();
    }
  }

  //-------------------------------------------------
  // MENSAJE
  //-------------------------------------------------

  setTimeout(function () {
    mostrarAlerta(
      "Exportación completada",
      "El archivo Excel se generó correctamente.",
      "success",
    );
  }, 300);
}
//=====================================================
// OBTENER FILAS DE LA TABLA
//=====================================================

function obtenerFilasEstadisticasProductos() {
  const tbody = document.getElementById("tbodyEstadisticasProductos");

  if (!tbody) {
    return [];
  }

  const filas = [];

  tbody.querySelectorAll("tr").forEach(function (tr) {
    const celdas = tr.querySelectorAll("td");

    //-------------------------------------------------
    // IGNORAR FILAS VACÍAS / MENSAJES
    //-------------------------------------------------

    if (celdas.length < 10) {
      return;
    }

    //-------------------------------------------------
    // LEER CELDAS
    //-------------------------------------------------

    const producto = celdas[0]?.textContent.trim() || "";

    const categoria = celdas[1]?.textContent.trim() || "";

    const marca = celdas[2]?.textContent.trim() || "";

    const stock = limpiarNumero(celdas[3]?.textContent || "");

    const vendidos = limpiarNumero(celdas[4]?.textContent || "");

    const precio = limpiarNumeroDecimal(celdas[5]?.textContent || "");

    const costo = limpiarNumeroDecimal(celdas[6]?.textContent || "");

    const ingresos = limpiarNumeroDecimal(celdas[7]?.textContent || "");

    const ganancia = limpiarNumeroDecimal(celdas[8]?.textContent || "");

    const margen = limpiarNumeroDecimal(celdas[9]?.textContent || "");

    //-------------------------------------------------
    // CÓDIGO
    //-------------------------------------------------

    let codigo = "";

    const elementoCodigo = tr.querySelector("[data-codigo-producto]");

    if (elementoCodigo) {
      codigo = elementoCodigo.dataset.codigoProducto || "";
    }

    //-------------------------------------------------
    // AGREGAR FILA
    //-------------------------------------------------

    filas.push({
      codigo: codigo,

      nombre: producto,

      categoria: categoria,

      marca: marca,

      sucursal: "",

      tipo: "",

      stock: stock,

      vendidos: vendidos,

      precio: precio,

      costo: costo,

      valor_inventario: stock * costo,

      ingresos: ingresos,

      ganancia: ganancia,

      margen: margen,
    });
  });

  return filas;
}
//=====================================================
// LIMPIAR NÚMERO
//=====================================================

function limpiarNumero(valor) {
  valor = String(valor || "").replace(/[^\d-]/g, "");

  return parseInt(valor, 10) || 0;
}
//=====================================================
// SELECCIONAR / DESELECCIONAR CAMPOS
//=====================================================

function configurarSeleccionExportacion() {
  const btnTodo = document.getElementById(
    "btnSeleccionarTodoExportacionProductos",
  );

  const btnNinguno = document.getElementById(
    "btnDeseleccionarTodoExportacionProductos",
  );

  if (btnTodo) {
    btnTodo.addEventListener("click", function () {
      document
        .querySelectorAll(
          '#modalExportarEstadisticasProductos input[type="checkbox"]',
        )
        .forEach(function (checkbox) {
          checkbox.checked = true;
        });
    });
  }

  if (btnNinguno) {
    btnNinguno.addEventListener("click", function () {
      document
        .querySelectorAll(
          '#modalExportarEstadisticasProductos input[type="checkbox"]',
        )
        .forEach(function (checkbox) {
          checkbox.checked = false;
        });
    });
  }
}
//=====================================================
// LIMPIAR DECIMAL
//=====================================================

function limpiarNumeroDecimal(valor) {
  valor = String(valor || "")
    .replace(/S\//gi, "")
    .replace(/%/g, "")
    .replace(/\s/g, "")
    .replace(/,/g, "");

  return parseFloat(valor) || 0;
}
//=====================================================
// EXPORTAR EXCEL
//=====================================================

function exportarExcelProductos() {
  if (typeof XLSX === "undefined") {
    mostrarAlerta("Error", "No se pudo cargar la librería de Excel.", "error");

    return;
  }

  //-------------------------------------------------
  // OBTENER TABLA
  //-------------------------------------------------

  const tabla = document.getElementById("tablaEstadisticasProductos");

  if (!tabla) {
    mostrarAlerta(
      "Sin datos",
      "No existe la tabla de estadísticas.",
      "warning",
    );

    return;
  }

  //-------------------------------------------------
  // CREAR LIBRO
  //-------------------------------------------------

  const libro = XLSX.utils.table_to_book(tabla, {
    sheet: "Estadísticas",
  });

  //-------------------------------------------------
  // EXPORTAR
  //-------------------------------------------------

  XLSX.writeFile(libro, "estadisticas_productos.xlsx");
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(valor) {
  return new Intl.NumberFormat("es-PE").format(Number(valor) || 0);
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  return new Intl.NumberFormat("es-PE", {
    minimumFractionDigits: 2,

    maximumFractionDigits: 2,
  }).format(Number(valor) || 0);
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escaparHTML(texto) {
  const div = document.createElement("div");

  div.textContent = texto ?? "";

  return div.innerHTML;
}

//=====================================================
// ALERTA SWEETALERT
//=====================================================

function mostrarAlerta(titulo, mensaje, icono = "info") {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: icono,

      title: titulo,

      text: mensaje,

      confirmButtonText: "Aceptar",
    });

    return;
  }

  alert(titulo + "\n\n" + mensaje);
}

//=====================================================
// EXPORTAR FUNCIONES
//=====================================================

window.cargarEstadisticasProductos = cargarEstadisticasProductos;

window.limpiarFiltrosProductos = limpiarFiltrosProductos;

window.exportarExcelProductos = exportarExcelProductos;
