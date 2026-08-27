//=====================================================
// CoDevPro Technology
// Archivo: js/adm_estadisticas_empleados.js
// Módulo: Estadísticas de Empleados
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let datosKPIEmpleados = null;
let datosGraficosEmpleados = null;

let solicitudKPIEmpleados = null;
let solicitudGraficosEmpleados = null;

let flatpickrFechaInicio = null;
let flatpickrFechaFin = null;

//=====================================================
// INSTANCIAS CHART.JS
//=====================================================

let graficoRendimientoEmpleados = null;
let graficoEstadoEmpleados = null;
let graficoEvolucionVentas = null;
let graficoEmpleadosRol = null;
let graficoPagosEmpleados = null;

//=====================================================
// TIPO DE GRÁFICO DE RENDIMIENTO
//=====================================================

let tipoRendimientoActual = "monto";

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarEstadisticasEmpleados();
});

//=====================================================
// FUNCIÓN PRINCIPAL
//=====================================================

async function inicializarEstadisticasEmpleados() {
  inicializarBotonesEstadisticas();

  inicializarFechasEstadisticas();

  inicializarOpcionesRendimiento();

  await cargarFiltrosEstadisticas();

  await cargarEstadisticasEmpleados();
}

//=====================================================
// CARGAR TODAS LAS ESTADÍSTICAS
//=====================================================

async function cargarEstadisticasEmpleados() {
  await Promise.all([cargarKPIEmpleados(), cargarGraficosEstadisticas()]);
}

//=====================================================
// INICIALIZAR BOTONES
//=====================================================

function inicializarBotonesEstadisticas() {
  //=================================================
  // ACTUALIZAR
  //=================================================

  const btnActualizar = document.getElementById("btnActualizarEstadisticas");

  if (btnActualizar) {
    btnActualizar.addEventListener("click", async function () {
      await cargarEstadisticasEmpleados();
    });
  }

  //=================================================
  // APLICAR FILTROS
  //=================================================

  const btnAplicar = document.getElementById("btnAplicarFiltrosEstadisticas");

  if (btnAplicar) {
    btnAplicar.addEventListener("click", async function () {
      if (!validarFechasEstadisticas()) {
        return;
      }

      await cargarEstadisticasEmpleados();
    });
  }

  //=================================================
  // LIMPIAR FILTROS
  //=================================================

  const btnLimpiar = document.getElementById("btnLimpiarFiltrosEstadisticas");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", async function () {
      limpiarFiltrosEstadisticas();

      await cargarEstadisticasEmpleados();
    });
  }
  //=================================================
  // EXPORTAR ESTADÍSTICAS
  //=================================================

  const btnExportar = document.getElementById("btnExportarEstadisticas");

  if (btnExportar) {
    btnExportar.addEventListener("click", function () {
      exportarEstadisticasEmpleados();
    });
  }

  //=================================================
  // ACTUALIZAR INFORMACIÓN DEL MODAL DE EXPORTACIÓN
  //=================================================

  const modalExportar = document.getElementById(
    "modalExportarEstadisticasEmpleados",
  );

  if (modalExportar) {
    modalExportar.addEventListener("show.bs.modal", function () {
      actualizarInformacionModalExportacion();
    });
  }
}
//=====================================================
// ACTUALIZAR INFORMACIÓN DEL MODAL DE EXPORTACIÓN
//=====================================================

function actualizarInformacionModalExportacion() {
  const selectEmpleado = document.getElementById("filtroEmpleadoEstadisticas");

  const selectRol = document.getElementById("filtroRolEstadisticas");

  const selectEstado = document.getElementById("filtroEstadoEstadisticas");

  const fechaInicio =
    document.getElementById("fechaInicioEstadisticas")?.value || "";

  const fechaFin = document.getElementById("fechaFinEstadisticas")?.value || "";

  //=================================================
  // EMPLEADO
  //=================================================

  const nombreEmpleado =
    selectEmpleado?.selectedOptions?.[0]?.textContent?.trim() ||
    "Todos los empleados";

  //=================================================
  // ROL
  //=================================================

  const nombreRol =
    selectRol?.selectedOptions?.[0]?.textContent?.trim() || "Todos los roles";

  //=================================================
  // ESTADO
  //=================================================

  const nombreEstado =
    selectEstado?.selectedOptions?.[0]?.textContent?.trim() || "Todos";

  //=================================================
  // PERÍODO
  //=================================================

  let periodo = "Sin filtro";

  if (fechaInicio !== "" && fechaFin !== "") {
    periodo = fechaInicio + " → " + fechaFin;
  }

  //=================================================
  // ACTUALIZAR MODAL
  //=================================================

  actualizarElemento("exportarFiltroEmpleado", nombreEmpleado);

  actualizarElemento("exportarFiltroRol", nombreRol);

  actualizarElemento("exportarFiltroEstado", nombreEstado);

  actualizarElemento("exportarFiltroFecha", periodo);
}
//=====================================================
// EXPORTAR ESTADÍSTICAS DE EMPLEADOS
//=====================================================

function exportarEstadisticasEmpleados() {
  try {
    //=================================================
    // VALIDAR LIBRERÍA XLSX
    //=================================================

    if (typeof XLSX === "undefined") {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "La librería XLSX no está disponible.",
      });

      return;
    }

    //=================================================
    // VALIDAR DATOS
    //=================================================

    if (!datosKPIEmpleados || !datosGraficosEmpleados) {
      Swal.fire({
        icon: "warning",
        title: "Sin datos",
        text: "Primero debes cargar las estadísticas.",
      });

      return;
    }

    //=================================================
    // OBTENER FILTROS
    //=================================================

    const selectEmpleado = document.getElementById(
      "filtroEmpleadoEstadisticas",
    );

    const selectRol = document.getElementById("filtroRolEstadisticas");

    const selectEstado = document.getElementById("filtroEstadoEstadisticas");

    const fechaInicio =
      document.getElementById("fechaInicioEstadisticas")?.value || "";

    const fechaFin =
      document.getElementById("fechaFinEstadisticas")?.value || "";

    const nombreEmpleado =
      selectEmpleado?.selectedOptions?.[0]?.textContent?.trim() ||
      "Todos los empleados";

    const nombreRol =
      selectRol?.selectedOptions?.[0]?.textContent?.trim() || "Todos los roles";

    const estado =
      selectEstado?.selectedOptions?.[0]?.textContent?.trim() || "Todos";

    //=================================================
    // CREAR LIBRO
    //=================================================

    const libro = XLSX.utils.book_new();

    //=================================================
    // HOJA 1: RESUMEN
    //=================================================

    const resumen = [
      ["ESTADÍSTICAS DE EMPLEADOS"],
      [],
      ["Filtros aplicados"],
      ["Empleado", nombreEmpleado],
      ["Rol", nombreRol],
      ["Estado", estado],
      ["Fecha inicio", fechaInicio !== "" ? fechaInicio : "Sin filtro"],
      ["Fecha fin", fechaFin !== "" ? fechaFin : "Sin filtro"],
      [],
      ["INDICADORES"],
      ["Total empleados", Number(datosKPIEmpleados.totalEmpleados || 0)],
      ["Empleados activos", Number(datosKPIEmpleados.empleadosActivos || 0)],
      [
        "Empleados inactivos",
        Number(datosKPIEmpleados.empleadosInactivos || 0),
      ],
      ["Ventas realizadas", Number(datosKPIEmpleados.ventasEmpleados || 0)],
      ["Monto de ventas", Number(datosKPIEmpleados.montoVentas || 0)],
      ["Ticket promedio", Number(datosKPIEmpleados.ticketPromedio || 0)],
      ["Productos vendidos", Number(datosKPIEmpleados.productosVendidos || 0)],
      ["Pagos realizados", Number(datosKPIEmpleados.pagosRealizados || 0)],
      ["Nómina activa", Number(datosKPIEmpleados.nominaActiva || 0)],
      ["Pagos pendientes", Number(datosKPIEmpleados.pagosPendientes || 0)],
      ["Bonificaciones", Number(datosKPIEmpleados.bonificaciones || 0)],
      ["Descuentos", Number(datosKPIEmpleados.descuentos || 0)],
    ];

    const hojaResumen = XLSX.utils.aoa_to_sheet(resumen);

    hojaResumen["!cols"] = [{ wch: 30 }, { wch: 28 }];

    XLSX.utils.book_append_sheet(libro, hojaResumen, "Resumen");

    //=================================================
    // HOJA 2: RENDIMIENTO
    //=================================================

    const rendimiento = [
      ["Empleado", "Rol", "Ventas", "Productos vendidos", "Monto vendido"],
    ];

    (datosGraficosEmpleados.rendimiento || []).forEach(function (fila) {
      rendimiento.push([
        fila.empleado || "Sin nombre",
        fila.rol || "Sin rol",
        Number(fila.ventas || 0),
        Number(fila.productos || 0),
        Number(fila.monto || 0),
      ]);
    });

    const hojaRendimiento = XLSX.utils.aoa_to_sheet(rendimiento);

    hojaRendimiento["!cols"] = [
      { wch: 30 },
      { wch: 22 },
      { wch: 12 },
      { wch: 20 },
      { wch: 18 },
    ];

    XLSX.utils.book_append_sheet(libro, hojaRendimiento, "Rendimiento");

    //=================================================
    // HOJA 3: PAGOS A EMPLEADOS
    //=================================================

    const pagos = [["Periodo", "Monto pagado"]];

    (datosGraficosEmpleados.pagos || []).forEach(function (fila) {
      pagos.push([fila.periodo || "", Number(fila.monto || 0)]);
    });

    const hojaPagos = XLSX.utils.aoa_to_sheet(pagos);

    hojaPagos["!cols"] = [{ wch: 18 }, { wch: 20 }];

    XLSX.utils.book_append_sheet(libro, hojaPagos, "Pagos");

    //=================================================
    // HOJA 4: RANKING
    //=================================================

    const ranking = [
      [
        "Posición",
        "Empleado",
        "Rol",
        "Ventas",
        "Productos",
        "Monto vendido",
        "Participación %",
      ],
    ];

    (datosGraficosEmpleados.ranking || []).forEach(function (fila, indice) {
      ranking.push([
        indice + 1,
        fila.empleado || "Sin nombre",
        fila.rol || "Sin rol",
        Number(fila.ventas || 0),
        Number(fila.productos || 0),
        Number(fila.monto || 0),
        Number(fila.participacion || 0),
      ]);
    });

    const hojaRanking = XLSX.utils.aoa_to_sheet(ranking);

    hojaRanking["!cols"] = [
      { wch: 12 },
      { wch: 30 },
      { wch: 22 },
      { wch: 12 },
      { wch: 15 },
      { wch: 18 },
      { wch: 18 },
    ];

    XLSX.utils.book_append_sheet(libro, hojaRanking, "Ranking");

    //=================================================
    // HOJA 5: EVOLUCIÓN DE VENTAS
    //=================================================

    const evolucion = [["Fecha", "Monto de ventas"]];

    (datosGraficosEmpleados.evolucionVentas || []).forEach(function (fila) {
      evolucion.push([fila.fecha || "", Number(fila.monto || 0)]);
    });

    const hojaEvolucion = XLSX.utils.aoa_to_sheet(evolucion);

    hojaEvolucion["!cols"] = [{ wch: 18 }, { wch: 20 }];

    XLSX.utils.book_append_sheet(libro, hojaEvolucion, "Evolución ventas");

    //=================================================
    // HOJA 6: ROLES
    //=================================================

    const roles = [["Rol", "Cantidad de empleados"]];

    (datosGraficosEmpleados.roles || []).forEach(function (fila) {
      roles.push([fila.rol || "Sin rol", Number(fila.cantidad || 0)]);
    });

    const hojaRoles = XLSX.utils.aoa_to_sheet(roles);

    hojaRoles["!cols"] = [{ wch: 25 }, { wch: 25 }];

    XLSX.utils.book_append_sheet(libro, hojaRoles, "Roles");

    //=================================================
    // FORMATO MONEDA
    //=================================================

    function aplicarFormatoMoneda(hoja, columnas) {
      const rango = XLSX.utils.decode_range(hoja["!ref"]);

      columnas.forEach(function (columna) {
        for (let fila = rango.s.r + 1; fila <= rango.e.r; fila++) {
          const celda =
            hoja[
              XLSX.utils.encode_cell({
                r: fila,
                c: columna,
              })
            ];

          if (celda && typeof celda.v === "number") {
            celda.z = '"S/" #,##0.00';
          }
        }
      });
    }

    aplicarFormatoMoneda(hojaResumen, [1]);

    aplicarFormatoMoneda(hojaRendimiento, [4]);

    aplicarFormatoMoneda(hojaPagos, [1]);

    aplicarFormatoMoneda(hojaRanking, [5]);

    aplicarFormatoMoneda(hojaEvolucion, [1]);

    //=================================================
    // FORMATO PORCENTAJE
    //=================================================

    const rangoRanking = XLSX.utils.decode_range(hojaRanking["!ref"]);

    for (let fila = rangoRanking.s.r + 1; fila <= rangoRanking.e.r; fila++) {
      const celda =
        hojaRanking[
          XLSX.utils.encode_cell({
            r: fila,
            c: 6,
          })
        ];

      if (celda && typeof celda.v === "number") {
        celda.z = '0.00"%"';
      }
    }

    //=================================================
    // NOMBRE DEL ARCHIVO
    //=================================================

    const fechaArchivo = new Date().toISOString().slice(0, 10);

    const nombreArchivo = "estadisticas_empleados_" + fechaArchivo + ".xlsx";

    //=================================================
    // GENERAR EXCEL
    //=================================================

    XLSX.writeFile(libro, nombreArchivo);

    //=================================================
    // CONFIRMACIÓN
    //=================================================

    Swal.fire({
      icon: "success",
      title: "Exportación completada",
      text: "Las estadísticas de empleados fueron exportadas correctamente.",
      timer: 1800,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error("Error al exportar estadísticas:", error);

    Swal.fire({
      icon: "error",
      title: "Error al exportar",
      text: error?.message || "No se pudieron exportar las estadísticas.",
    });
  }
}
//=====================================================
// INICIALIZAR OPCIONES DEL GRÁFICO DE RENDIMIENTO
//=====================================================

function inicializarOpcionesRendimiento() {
  const opciones = document.querySelectorAll(".dropdown-menu .dropdown-item");

  opciones.forEach(function (opcion) {
    opcion.addEventListener("click", function () {
      const texto = opcion.textContent.trim().toLowerCase();

      opciones.forEach(function (item) {
        item.classList.remove("active");
      });

      opcion.classList.add("active");

      if (texto.includes("monto")) {
        tipoRendimientoActual = "monto";
      } else if (texto.includes("número")) {
        tipoRendimientoActual = "ventas";
      } else if (texto.includes("productos")) {
        tipoRendimientoActual = "productos";
      }

      if (datosGraficosEmpleados) {
        crearGraficoRendimiento(datosGraficosEmpleados.rendimiento || []);
      }
    });
  });
}

//=====================================================
// CARGAR FILTROS
//=====================================================

async function cargarFiltrosEstadisticas() {
  try {
    const respuesta = await fetch(
      "ajax/obtener_filtros_estadisticas_empleados.php",
      {
        method: "GET",
        cache: "no-store",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const resultado = await respuesta.json();

    console.log("Filtros estadísticas empleados:", resultado);

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje || "No se pudieron cargar los filtros.",
      );
    }

    const datos = resultado.data || {};

    cargarSelectEmpleados(datos.empleados || []);
    cargarSelectRoles(datos.roles || []);
  } catch (error) {
    console.error("Error al cargar filtros de estadísticas:", error);

    mostrarErrorFiltros();
  }
}

//=====================================================
// CARGAR SELECT EMPLEADOS
//=====================================================

function cargarSelectEmpleados(empleados) {
  const select = document.getElementById("filtroEmpleadoEstadisticas");

  if (!select) {
    return;
  }

  select.innerHTML = "";

  const opcionTodos = document.createElement("option");

  opcionTodos.value = "";
  opcionTodos.textContent = "Todos los empleados";

  select.appendChild(opcionTodos);

  empleados.forEach(function (empleado) {
    const opcion = document.createElement("option");

    opcion.value = empleado.id_empleado;

    opcion.textContent =
      `${empleado.nombre || ""} ${empleado.apellido || ""}`.trim();

    select.appendChild(opcion);
  });
}

//=====================================================
// CARGAR SELECT ROLES
//=====================================================

function cargarSelectRoles(roles) {
  const select = document.getElementById("filtroRolEstadisticas");

  if (!select) {
    return;
  }

  select.innerHTML = "";

  const opcionTodos = document.createElement("option");

  opcionTodos.value = "";
  opcionTodos.textContent = "Todos los roles";

  select.appendChild(opcionTodos);

  roles.forEach(function (rol) {
    const opcion = document.createElement("option");

    opcion.value = rol.id_rol;
    opcion.textContent = rol.nombre;

    select.appendChild(opcion);
  });
}

//=====================================================
// ERROR FILTROS
//=====================================================

function mostrarErrorFiltros() {
  const selectEmpleado = document.getElementById("filtroEmpleadoEstadisticas");

  const selectRol = document.getElementById("filtroRolEstadisticas");

  if (selectEmpleado) {
    selectEmpleado.innerHTML =
      '<option value="">No se pudieron cargar los empleados</option>';
  }

  if (selectRol) {
    selectRol.innerHTML =
      '<option value="">No se pudieron cargar los roles</option>';
  }
}

//=====================================================
// INICIALIZAR FECHAS
//=====================================================

function inicializarFechasEstadisticas() {
  if (typeof flatpickr === "undefined") {
    console.warn("Flatpickr no está disponible.");

    return;
  }

  //=================================================
  // FECHA INICIO
  //=================================================

  const inputInicio = document.getElementById("fechaInicioEstadisticas");

  if (inputInicio) {
    flatpickrFechaInicio = flatpickr(inputInicio, {
      locale: "es",
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "d/m/Y",
      allowInput: true,
      maxDate: "today",
    });
  }

  //=================================================
  // FECHA FIN
  //=================================================

  const inputFin = document.getElementById("fechaFinEstadisticas");

  if (inputFin) {
    flatpickrFechaFin = flatpickr(inputFin, {
      locale: "es",
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "d/m/Y",
      allowInput: true,
      maxDate: "today",
    });
  }
}

//=====================================================
// VALIDAR FECHAS
//=====================================================

function validarFechasEstadisticas() {
  const fechaInicio =
    document.getElementById("fechaInicioEstadisticas")?.value || "";

  const fechaFin = document.getElementById("fechaFinEstadisticas")?.value || "";

  if (
    (fechaInicio !== "" && fechaFin === "") ||
    (fechaInicio === "" && fechaFin !== "")
  ) {
    Swal.fire({
      icon: "warning",
      title: "Rango de fechas incompleto",
      text: "Selecciona la fecha inicial y la fecha final.",
    });

    return false;
  }

  if (fechaInicio !== "" && fechaFin !== "") {
    const inicio = new Date(fechaInicio + "T00:00:00");

    const fin = new Date(fechaFin + "T00:00:00");

    if (inicio > fin) {
      Swal.fire({
        icon: "warning",
        title: "Rango de fechas inválido",
        text: "La fecha inicial no puede ser posterior a la fecha final.",
      });

      return false;
    }
  }

  return true;
}

//=====================================================
// OBTENER PARÁMETROS DE FILTRO
//=====================================================

function obtenerParametrosEstadisticas() {
  const empleado =
    document.getElementById("filtroEmpleadoEstadisticas")?.value || "";

  const rol = document.getElementById("filtroRolEstadisticas")?.value || "";

  const estado =
    document.getElementById("filtroEstadoEstadisticas")?.value || "";

  const fechaInicio =
    document.getElementById("fechaInicioEstadisticas")?.value || "";

  const fechaFin = document.getElementById("fechaFinEstadisticas")?.value || "";

  const parametros = new URLSearchParams();

  if (empleado !== "") {
    parametros.append("id_empleado", empleado);
  }

  if (rol !== "") {
    parametros.append("id_rol", rol);
  }

  if (estado !== "") {
    parametros.append("estado", estado);
  }

  if (fechaInicio !== "") {
    parametros.append("fecha_inicio", fechaInicio);
  }

  if (fechaFin !== "") {
    parametros.append("fecha_fin", fechaFin);
  }

  return parametros;
}

//=====================================================
// CARGAR KPI
//=====================================================

async function cargarKPIEmpleados() {
  try {
    if (solicitudKPIEmpleados) {
      solicitudKPIEmpleados.abort();
    }

    const controlador = new AbortController();

    solicitudKPIEmpleados = controlador;

    mostrarCargaKPI();

    const parametros = obtenerParametrosEstadisticas();

    let url = "ajax/obtener_kpi_estadisticas_empleados.php";

    const query = parametros.toString();

    if (query !== "") {
      url += "?" + query;
    }

    console.log("Consultando KPI empleados:", url);

    const respuesta = await fetch(url, {
      method: "GET",
      cache: "no-store",
      signal: controlador.signal,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const resultado = await respuesta.json();

    console.log("Respuesta KPI empleados:", resultado);

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje || "No se pudieron obtener las estadísticas.",
      );
    }

    datosKPIEmpleados = resultado.data || {};

    actualizarKPIEmpleados(datosKPIEmpleados);

    actualizarEstadoEmpleados(datosKPIEmpleados);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar KPI:", error);

    mostrarErrorKPI();
  } finally {
    solicitudKPIEmpleados = null;
  }
}

//=====================================================
// CARGAR GRÁFICOS
//=====================================================

async function cargarGraficosEstadisticas() {
  try {
    if (typeof Chart === "undefined") {
      throw new Error("Chart.js no está disponible.");
    }

    if (solicitudGraficosEmpleados) {
      solicitudGraficosEmpleados.abort();
    }

    const controlador = new AbortController();

    solicitudGraficosEmpleados = controlador;

    const parametros = obtenerParametrosEstadisticas();

    let url = "ajax/obtener_graficos_estadisticas_empleados.php";

    const query = parametros.toString();

    if (query !== "") {
      url += "?" + query;
    }

    console.log("Consultando gráficos:", url);

    const respuesta = await fetch(url, {
      method: "GET",
      cache: "no-store",
      signal: controlador.signal,
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    const resultado = await respuesta.json();

    console.log("Respuesta gráficos:", resultado);

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje ||
          "No se pudieron obtener los datos de los gráficos.",
      );
    }

    datosGraficosEmpleados = resultado.data || {};

    //=================================================
    // CREAR GRÁFICOS
    //=================================================

    crearGraficoRendimiento(datosGraficosEmpleados.rendimiento || []);

    crearGraficoEstado(datosGraficosEmpleados.estado || []);

    crearGraficoEvolucionVentas(datosGraficosEmpleados.evolucionVentas || []);

    crearGraficoRoles(datosGraficosEmpleados.roles || []);

    crearGraficoPagos(datosGraficosEmpleados.pagos || []);

    //=================================================
    // RANKING
    //=================================================

    actualizarRankingEmpleados(datosGraficosEmpleados.ranking || []);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar gráficos:", error);

    destruirGraficos();
    mostrarRankingVacio();
  } finally {
    solicitudGraficosEmpleados = null;
  }
}

//=====================================================
// GRÁFICO: RENDIMIENTO
//=====================================================

function crearGraficoRendimiento(datos) {
  const canvas = document.getElementById("graficoRendimientoEmpleados");

  if (!canvas) {
    return;
  }

  if (graficoRendimientoEmpleados) {
    graficoRendimientoEmpleados.destroy();
  }

  const etiquetas = datos.map(function (fila) {
    return fila.empleado || "Sin nombre";
  });

  let valores = [];

  let etiqueta = "";

  let formatoMoneda = false;

  if (tipoRendimientoActual === "ventas") {
    valores = datos.map(function (fila) {
      return Number(fila.ventas || 0);
    });

    etiqueta = "Número de ventas";
  } else if (tipoRendimientoActual === "productos") {
    valores = datos.map(function (fila) {
      return Number(fila.productos || 0);
    });

    etiqueta = "Productos vendidos";
  } else {
    valores = datos.map(function (fila) {
      return Number(fila.monto || 0);
    });

    etiqueta = "Monto vendido";

    formatoMoneda = true;
  }

  graficoRendimientoEmpleados = new Chart(canvas, {
    type: "bar",

    data: {
      labels: etiquetas,

      datasets: [
        {
          label: etiqueta,

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
              const valor = context.raw || 0;

              if (formatoMoneda) {
                return " " + formatearMoneda(valor);
              }

              return " " + Number(valor).toLocaleString("es-PE");
            },
          },
        },
      },

      scales: {
        x: {
          beginAtZero: true,

          ticks: {
            callback: function (value) {
              if (formatoMoneda) {
                return "S/ " + Number(value).toLocaleString("es-PE");
              }

              return Number(value).toLocaleString("es-PE");
            },
          },
        },

        y: {
          ticks: {
            autoSkip: false,
          },
        },
      },
    },
  });
}

//=====================================================
// GRÁFICO: ESTADO
//=====================================================

function crearGraficoEstado(datos) {
  const canvas = document.getElementById("graficoEstadoEmpleados");

  if (!canvas) {
    return;
  }

  if (graficoEstadoEmpleados) {
    graficoEstadoEmpleados.destroy();
  }

  let activos = 0;
  let inactivos = 0;

  datos.forEach(function (fila) {
    const estado = String(fila.estado || "").toUpperCase();

    const cantidad = Number(fila.cantidad || 0);

    if (estado === "ACTIVO") {
      activos += cantidad;
    } else {
      inactivos += cantidad;
    }
  });

  actualizarElemento("estadoActivos", formatearNumero(activos));

  actualizarElemento("estadoInactivos", formatearNumero(inactivos));

  graficoEstadoEmpleados = new Chart(canvas, {
    type: "doughnut",

    data: {
      labels: ["Activos", "Inactivos"],

      datasets: [
        {
          data: [activos, inactivos],

          borderWidth: 2,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      cutout: "68%",

      plugins: {
        legend: {
          display: false,
        },
      },
    },
  });
}

//=====================================================
// GRÁFICO: EVOLUCIÓN DE VENTAS
//=====================================================

function crearGraficoEvolucionVentas(datos) {
  const canvas = document.getElementById("graficoEvolucionVentas");

  if (!canvas) {
    return;
  }

  if (graficoEvolucionVentas) {
    graficoEvolucionVentas.destroy();
  }

  const etiquetas = datos.map(function (fila) {
    return formatearFechaGrafico(fila.fecha);
  });

  const valores = datos.map(function (fila) {
    return Number(fila.monto || 0);
  });

  graficoEvolucionVentas = new Chart(canvas, {
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
              return "S/ " + Number(value).toLocaleString("es-PE");
            },
          },
        },
      },
    },
  });
}

//=====================================================
// GRÁFICO: ROLES
//=====================================================

function crearGraficoRoles(datos) {
  const canvas = document.getElementById("graficoEmpleadosRol");

  if (!canvas) {
    return;
  }

  if (graficoEmpleadosRol) {
    graficoEmpleadosRol.destroy();
  }

  const etiquetas = datos.map(function (fila) {
    return fila.rol || "Sin rol";
  });

  const valores = datos.map(function (fila) {
    return Number(fila.cantidad || 0);
  });

  graficoEmpleadosRol = new Chart(canvas, {
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

      cutout: "58%",

      plugins: {
        legend: {
          position: "bottom",
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              return (
                " " +
                context.label +
                ": " +
                Number(context.raw || 0).toLocaleString("es-PE")
              );
            },
          },
        },
      },
    },
  });
}

//=====================================================
// GRÁFICO: PAGOS
//=====================================================

function crearGraficoPagos(datos) {
  const canvas = document.getElementById("graficoPagosEmpleados");

  if (!canvas) {
    return;
  }

  if (graficoPagosEmpleados) {
    graficoPagosEmpleados.destroy();
  }

  const etiquetas = datos.map(function (fila) {
    return formatearPeriodo(fila.periodo);
  });

  const valores = datos.map(function (fila) {
    return Number(fila.monto || 0);
  });

  graficoPagosEmpleados = new Chart(canvas, {
    type: "bar",

    data: {
      labels: etiquetas,

      datasets: [
        {
          label: "Pagos",

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
              return " Pagos: " + formatearMoneda(context.raw || 0);
            },
          },
        },
      },

      scales: {
        y: {
          beginAtZero: true,

          ticks: {
            callback: function (value) {
              return "S/ " + Number(value).toLocaleString("es-PE");
            },
          },
        },
      },
    },
  });
}

//=====================================================
// ACTUALIZAR RANKING
//=====================================================

function actualizarRankingEmpleados(datos) {
  const tabla = document.getElementById("tablaRankingEmpleados");

  if (!tabla) {
    return;
  }

  const tbody = tabla.querySelector("tbody");

  if (!tbody) {
    return;
  }

  if (!Array.isArray(datos) || datos.length === 0) {
    mostrarRankingVacio();

    return;
  }

  tbody.innerHTML = "";

  datos.forEach(function (fila, indice) {
    const tr = document.createElement("tr");

    const posicion = indice + 1;

    const participacion = Number(fila.participacion || 0);

    tr.innerHTML = `
        <td>
          <span class="fw-bold">
            ${posicion}
          </span>
        </td>

        <td>
          <div class="fw-semibold">
            ${escaparHTML(fila.empleado || "Sin nombre")}
          </div>
        </td>

        <td>
          <span class="badge bg-light text-dark border">
            ${escaparHTML(fila.rol || "Sin rol")}
          </span>
        </td>

        <td class="text-center">
          ${Number(fila.ventas || 0).toLocaleString("es-PE")}
        </td>

        <td class="text-center">
          ${Number(fila.productos || 0).toLocaleString("es-PE")}
        </td>

        <td class="text-end fw-semibold">
          ${formatearMoneda(fila.monto || 0)}
        </td>

        <td class="text-center">
          ${participacion.toLocaleString("es-PE", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          })}%
        </td>
      `;

    tbody.appendChild(tr);
  });
}

//=====================================================
// RANKING VACÍO
//=====================================================

function mostrarRankingVacio() {
  const tabla = document.getElementById("tablaRankingEmpleados");

  if (!tabla) {
    return;
  }

  const tbody = tabla.querySelector("tbody");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = `
    <tr>
      <td colspan="7">
        <div class="estadistica-tabla-vacia">
          <i class="bi bi-bar-chart-line"></i>
          <span>
            No hay datos disponibles.
          </span>
        </div>
      </td>
    </tr>
  `;
}

//=====================================================
// DESTRUIR GRÁFICOS
//=====================================================

function destruirGraficos() {
  if (graficoRendimientoEmpleados) {
    graficoRendimientoEmpleados.destroy();
    graficoRendimientoEmpleados = null;
  }

  if (graficoEstadoEmpleados) {
    graficoEstadoEmpleados.destroy();
    graficoEstadoEmpleados = null;
  }

  if (graficoEvolucionVentas) {
    graficoEvolucionVentas.destroy();
    graficoEvolucionVentas = null;
  }

  if (graficoEmpleadosRol) {
    graficoEmpleadosRol.destroy();
    graficoEmpleadosRol = null;
  }

  if (graficoPagosEmpleados) {
    graficoPagosEmpleados.destroy();
    graficoPagosEmpleados = null;
  }
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKPIEmpleados(datos) {
  if (!datos) {
    return;
  }

  actualizarElemento(
    "kpiTotalEmpleados",
    formatearNumero(datos.totalEmpleados),
  );

  actualizarElemento(
    "kpiEmpleadosActivos",
    formatearNumero(datos.empleadosActivos),
  );

  actualizarElemento(
    "kpiVentasEmpleados",
    formatearNumero(datos.ventasEmpleados),
  );

  actualizarElemento("kpiMontoVentas", formatearMoneda(datos.montoVentas));

  actualizarElemento(
    "kpiTicketPromedio",
    formatearMoneda(datos.ticketPromedio),
  );

  actualizarElemento(
    "kpiProductosVendidos",
    formatearNumero(datos.productosVendidos),
  );

  actualizarElemento(
    "kpiPagosRealizados",
    formatearMoneda(datos.pagosRealizados),
  );

  actualizarElemento("kpiNominaActiva", formatearMoneda(datos.nominaActiva));

  actualizarElemento(
    "resumenPagosPendientes",
    formatearMoneda(datos.pagosPendientes),
  );

  actualizarElemento(
    "resumenBonificaciones",
    formatearMoneda(datos.bonificaciones),
  );

  actualizarElemento("resumenDescuentos", formatearMoneda(datos.descuentos));
}

//=====================================================
// ACTUALIZAR ESTADO EMPLEADOS
//=====================================================

function actualizarEstadoEmpleados(datos) {
  actualizarElemento("estadoActivos", formatearNumero(datos.empleadosActivos));

  actualizarElemento(
    "estadoInactivos",
    formatearNumero(datos.empleadosInactivos),
  );
}

//=====================================================
// ACTUALIZAR ELEMENTO
//=====================================================

function actualizarElemento(id, valor) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    console.warn("No existe el elemento #" + id);

    return;
  }

  elemento.textContent = valor;
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(valor) {
  const numero = Number(valor) || 0;

  return numero.toLocaleString("es-PE");
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  const numero = Number(valor) || 0;

  return numero.toLocaleString("es-PE", {
    style: "currency",
    currency: "PEN",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFechaGrafico(fecha) {
  if (!fecha) {
    return "";
  }

  const partes = String(fecha).split("-");

  if (partes.length !== 3) {
    return fecha;
  }

  return partes[2] + "/" + partes[1];
}

//=====================================================
// FORMATEAR PERIODO
//=====================================================

function formatearPeriodo(periodo) {
  if (!periodo) {
    return "";
  }

  const partes = String(periodo).split("-");

  if (partes.length !== 2) {
    return periodo;
  }

  const meses = [
    "Ene",
    "Feb",
    "Mar",
    "Abr",
    "May",
    "Jun",
    "Jul",
    "Ago",
    "Sep",
    "Oct",
    "Nov",
    "Dic",
  ];

  const indice = Number(partes[1]) - 1;

  return (meses[indice] || partes[1]) + " " + partes[0];
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
// MOSTRAR CARGA KPI
//=====================================================

function mostrarCargaKPI() {
  const elementos = [
    "kpiTotalEmpleados",
    "kpiEmpleadosActivos",
    "kpiVentasEmpleados",
    "kpiMontoVentas",
    "kpiTicketPromedio",
    "kpiProductosVendidos",
    "kpiPagosRealizados",
    "kpiNominaActiva",
    "resumenPagosPendientes",
    "resumenBonificaciones",
    "resumenDescuentos",
    "estadoActivos",
    "estadoInactivos",
  ];

  elementos.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (!elemento) {
      return;
    }

    elemento.innerHTML =
      '<span class="spinner-border spinner-border-sm" ' +
      'role="status" aria-hidden="true"></span>';
  });
}

//=====================================================
// MOSTRAR ERROR KPI
//=====================================================

function mostrarErrorKPI() {
  const elementos = [
    "kpiTotalEmpleados",
    "kpiEmpleadosActivos",
    "kpiVentasEmpleados",
    "kpiMontoVentas",
    "kpiTicketPromedio",
    "kpiProductosVendidos",
    "kpiPagosRealizados",
    "kpiNominaActiva",
    "resumenPagosPendientes",
    "resumenBonificaciones",
    "resumenDescuentos",
    "estadoActivos",
    "estadoInactivos",
  ];

  elementos.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (!elemento) {
      return;
    }

    elemento.textContent = "—";
  });
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosEstadisticas() {
  const filtroEmpleado = document.getElementById("filtroEmpleadoEstadisticas");

  const filtroRol = document.getElementById("filtroRolEstadisticas");

  const filtroEstado = document.getElementById("filtroEstadoEstadisticas");

  if (filtroEmpleado) {
    filtroEmpleado.value = "";
  }

  if (filtroRol) {
    filtroRol.value = "";
  }

  if (filtroEstado) {
    filtroEstado.value = "";
  }

  if (flatpickrFechaInicio) {
    flatpickrFechaInicio.clear();
  }

  if (flatpickrFechaFin) {
    flatpickrFechaFin.clear();
  }
}

//=====================================================
// RECARGAR KPI
//=====================================================

function recargarKPIEmpleados() {
  cargarEstadisticasEmpleados();
}
