//=====================================================
// CoDevPro Technology
// Archivo: js/adm_contabilidad.js
// Módulo: Contabilidad
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let graficoIngresosGastos = null;
let graficoGastosCategoria = null;
let graficoEvolucionFinanciera = null;

let flatpickrFechaInicio = null;
let flatpickrFechaFin = null;

let periodoGraficoActual = 12;

let cargandoContabilidad = false;

//=====================================================
// ENDPOINTS
//=====================================================

const ENDPOINTS_CONTABILIDAD = {
  resumen: "ajax/obtener_resumen_contabilidad.php",
  cuentas: "ajax/obtener_cuentas_contabilidad.php",
  movimientos: "ajax/obtener_movimientos_contabilidad.php",
  exportarExcel: "ajax/exportar_contabilidad_excel.php",
  exportarPDF: "ajax/exportar_contabilidad_pdf.php",
};

//=====================================================
// CONFIGURACIÓN
//=====================================================

const CONFIG_CONTABILIDAD = {
  moneda: "S/",
  decimales: 2,

  meses: [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ],
};

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarContabilidad();
});

//=====================================================
// INICIALIZAR
//=====================================================

function inicializarContabilidad() {
  inicializarSelectAnio();

  inicializarFechas();

  inicializarEventos();

  inicializarGraficosVacios();

  actualizarBotonPeriodoGrafico();

  cargarContabilidad();
}

//=====================================================
// SELECT AÑO
//=====================================================

function inicializarSelectAnio() {
  const select = document.getElementById("filtroAnioContabilidad");

  if (!select) {
    return;
  }

  const anioActual = new Date().getFullYear();

  select.innerHTML = "";

  const opcionTodos = document.createElement("option");

  opcionTodos.value = "";

  opcionTodos.textContent = "Todos los años";

  select.appendChild(opcionTodos);

  for (let anio = anioActual; anio >= anioActual - 10; anio--) {
    const option = document.createElement("option");

    option.value = String(anio);

    option.textContent = String(anio);

    select.appendChild(option);
  }

  select.value = String(anioActual);
}

//=====================================================
// FLATPICKR
//=====================================================

function inicializarFechas() {
  const inputInicio = document.getElementById("fechaInicioContabilidad");

  const inputFin = document.getElementById("fechaFinContabilidad");

  if (typeof flatpickr === "undefined") {
    console.warn("Flatpickr no está disponible.");

    return;
  }

  if (inputInicio) {
    flatpickrFechaInicio = flatpickr(inputInicio, {
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "d/m/Y",
      locale: "es",
      allowInput: true,
    });
  }

  if (inputFin) {
    flatpickrFechaFin = flatpickr(inputFin, {
      dateFormat: "Y-m-d",
      altInput: true,
      altFormat: "d/m/Y",
      locale: "es",
      allowInput: true,
    });
  }
}

//=====================================================
// EVENTOS
//=====================================================

function inicializarEventos() {
  //=================================================
  // APLICAR FILTROS
  //=================================================

  const btnAplicar = document.getElementById("btnAplicarFiltrosContabilidad");

  if (btnAplicar) {
    btnAplicar.addEventListener("click", function () {
      cargarContabilidad();
    });
  }

  //=================================================
  // LIMPIAR FILTROS
  //=================================================

  const btnLimpiar = document.getElementById("btnLimpiarFiltrosContabilidad");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", limpiarFiltros);
  }

  //=================================================
  // ACTUALIZAR
  //=================================================

  const btnActualizar = document.getElementById("btnActualizarContabilidad");

  if (btnActualizar) {
    btnActualizar.addEventListener("click", function () {
      cargarContabilidad();
    });
  }

  //=================================================
  // EXPORTAR
  //=================================================

  const btnExportar = document.getElementById("btnExportarContabilidad");

  if (btnExportar) {
    btnExportar.addEventListener("click", abrirModalExportar);
  }

  //=================================================
  // EXCEL
  //=================================================

  const btnExcel = document.getElementById("btnExportarExcel");

  if (btnExcel) {
    btnExcel.addEventListener("click", exportarExcel);
  }

  //=================================================
  // PDF
  //=================================================

  const btnPDF = document.getElementById("btnExportarPDF");

  if (btnPDF) {
    btnPDF.addEventListener("click", exportarPDF);
  }

  //=================================================
  // AÑO
  //=================================================

  const selectAnio = document.getElementById("filtroAnioContabilidad");

  if (selectAnio) {
    selectAnio.addEventListener("change", function () {
      const periodo =
        document.getElementById("filtroPeriodoContabilidad")?.value || "todos";

      if (periodo !== "todos") {
        actualizarFechasDesdePeriodo();
      }
    });
  }

  //=================================================
  // PERÍODO
  //=================================================

  const selectPeriodo = document.getElementById("filtroPeriodoContabilidad");

  if (selectPeriodo) {
    selectPeriodo.addEventListener("change", function () {
      if (this.value === "todos") {
        limpiarFechas();
      } else {
        actualizarFechasDesdePeriodo();
      }
    });
  }

  //=================================================
  // PERÍODO GRÁFICO
  //=================================================

  const botonesPeriodo = document.querySelectorAll(".btn-periodo-grafico");

  botonesPeriodo.forEach(function (boton) {
    boton.addEventListener("click", function () {
      const periodo = parseInt(boton.dataset.periodo, 10);

      if (Number.isNaN(periodo) || periodo <= 0) {
        return;
      }

      cambiarPeriodoGrafico(periodo);
    });
  });
}

//=====================================================
// OBTENER FILTROS
//=====================================================

function obtenerFiltros() {
  const anio = document.getElementById("filtroAnioContabilidad")?.value || "";

  const periodo =
    document.getElementById("filtroPeriodoContabilidad")?.value || "todos";

  const fechaInicio =
    document.getElementById("fechaInicioContabilidad")?.value || "";

  const fechaFin = document.getElementById("fechaFinContabilidad")?.value || "";

  return {
    anio: anio,
    periodo: periodo,
    fecha_inicio: fechaInicio,
    fecha_fin: fechaFin,
  };
}

//=====================================================
// VALIDAR FECHA
//=====================================================

function fechaValida(fecha) {
  if (!fecha) {
    return false;
  }

  const regex = /^\d{4}-\d{2}-\d{2}$/;

  return regex.test(fecha);
}

//=====================================================
// CONSTRUIR PARÁMETROS
//=====================================================

function construirParametros(extra = {}) {
  const filtros = obtenerFiltros();

  const parametros = new URLSearchParams();

  parametros.set("anio", filtros.anio);

  parametros.set("periodo", filtros.periodo);

  parametros.set("fecha_inicio", filtros.fecha_inicio);

  parametros.set("fecha_fin", filtros.fecha_fin);

  Object.keys(extra).forEach(function (clave) {
    parametros.set(clave, extra[clave]);
  });

  return parametros;
}

//=====================================================
// ACTUALIZAR FECHAS POR PERÍODO
//=====================================================

function actualizarFechasDesdePeriodo() {
  const anio = document.getElementById("filtroAnioContabilidad")?.value || "";

  const periodo =
    document.getElementById("filtroPeriodoContabilidad")?.value || "todos";

  if (!anio || periodo === "todos") {
    return;
  }

  const mes = parseInt(periodo, 10);

  if (Number.isNaN(mes) || mes < 1 || mes > 12) {
    return;
  }

  const anioNumero = parseInt(anio, 10);

  const fechaInicio = new Date(anioNumero, mes - 1, 1);

  const fechaFin = new Date(anioNumero, mes, 0);

  const inicio = formatearFechaISO(fechaInicio);

  const fin = formatearFechaISO(fechaFin);

  if (flatpickrFechaInicio) {
    flatpickrFechaInicio.setDate(inicio, true);
  }

  if (flatpickrFechaFin) {
    flatpickrFechaFin.setDate(fin, true);
  }
}

//=====================================================
// LIMPIAR FECHAS
//=====================================================

function limpiarFechas() {
  if (flatpickrFechaInicio) {
    flatpickrFechaInicio.clear();
  }

  if (flatpickrFechaFin) {
    flatpickrFechaFin.clear();
  }
}

//=====================================================
// FORMATEAR FECHA ISO
//=====================================================

function formatearFechaISO(fecha) {
  const anio = fecha.getFullYear();

  const mes = String(fecha.getMonth() + 1).padStart(2, "0");

  const dia = String(fecha.getDate()).padStart(2, "0");

  return `${anio}-${mes}-${dia}`;
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltros() {
  const selectAnio = document.getElementById("filtroAnioContabilidad");

  const selectPeriodo = document.getElementById("filtroPeriodoContabilidad");

  if (selectAnio) {
    selectAnio.value = String(new Date().getFullYear());
  }

  if (selectPeriodo) {
    selectPeriodo.value = "todos";
  }

  limpiarFechas();

  cargarContabilidad();
}

//=====================================================
// CARGAR CONTABILIDAD
//=====================================================

async function cargarContabilidad() {
  if (cargandoContabilidad) {
    return;
  }

  cargandoContabilidad = true;

  mostrarLoader();

  ocultarError();

  const btnActualizar = document.getElementById("btnActualizarContabilidad");

  if (btnActualizar) {
    btnActualizar.disabled = true;
  }

  try {
    const parametros = construirParametros({
      meses: periodoGraficoActual,
    });

    //=================================================
    // VALIDAR RANGO DE FECHAS
    //=================================================

    const filtros = obtenerFiltros();

    if (filtros.fecha_inicio && !fechaValida(filtros.fecha_inicio)) {
      throw new Error("La fecha de inicio no es válida.");
    }

    if (filtros.fecha_fin && !fechaValida(filtros.fecha_fin)) {
      throw new Error("La fecha final no es válida.");
    }

    if (
      filtros.fecha_inicio &&
      filtros.fecha_fin &&
      filtros.fecha_inicio > filtros.fecha_fin
    ) {
      throw new Error(
        "La fecha de inicio no puede ser mayor que la fecha final.",
      );
    }

    //=================================================
    // RESUMEN
    //=================================================

    const respuestaResumen = await fetch(
      ENDPOINTS_CONTABILIDAD.resumen + "?" + parametros.toString(),
      {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
        cache: "no-store",
      },
    );

    const textoResumen = await respuestaResumen.text();

    if (!respuestaResumen.ok) {
      throw new Error("El servidor no pudo obtener el resumen contable.");
    }

    let datosResumen;

    try {
      datosResumen = JSON.parse(textoResumen);
    } catch (error) {
      console.error("Respuesta resumen no válida:", textoResumen);

      throw new Error("El servidor devolvió una respuesta que no es JSON.");
    }

    if (datosResumen.estado === false) {
      throw new Error(
        datosResumen.mensaje || "Error al obtener el resumen contable.",
      );
    }

    procesarResumen(datosResumen);

    //=================================================
    // CUENTAS
    //=================================================

    await cargarCuentasBancarias(parametros);

    //=================================================
    // MOVIMIENTOS
    //=================================================

    await cargarUltimosMovimientos(parametros);

    //=================================================
    // GRÁFICOS
    //=================================================

    procesarGraficos(datosResumen);
  } catch (error) {
    console.error("Error contabilidad:", error);

    mostrarError(
      error.message || "Ocurrió un error al cargar la información contable.",
    );
  } finally {
    ocultarLoader();

    if (btnActualizar) {
      btnActualizar.disabled = false;
    }

    cargandoContabilidad = false;
  }
}

//=====================================================
// PROCESAR RESUMEN
//=====================================================

function procesarResumen(datos) {
  const resumen = datos.resumen || datos.data || datos;

  const ingresos = convertirNumero(
    resumen.ingresos ?? resumen.total_ingresos ?? 0,
  );

  const gastos = convertirNumero(resumen.gastos ?? resumen.total_gastos ?? 0);

  const utilidad = convertirNumero(
    resumen.utilidad ?? resumen.utilidad_neta ?? ingresos - gastos,
  );

  const balance = convertirNumero(
    resumen.balance ?? resumen.balance_bancario ?? 0,
  );

  const cuentas =
    parseInt(resumen.cuentas ?? resumen.cantidad_cuentas ?? 0, 10) || 0;

  actualizarTexto("kpiIngresos", formatearMoneda(ingresos));

  actualizarTexto("kpiGastos", formatearMoneda(gastos));

  actualizarTexto("kpiUtilidad", formatearMoneda(utilidad));

  actualizarTexto("kpiBalance", formatearMoneda(balance));

  actualizarTexto(
    "cantidadCuentas",
    cuentas === 1 ? "1 cuenta" : `${cuentas} cuentas`,
  );

  actualizarTexto("resumenIngresos", formatearMoneda(ingresos));

  actualizarTexto("resumenGastos", formatearMoneda(gastos));

  actualizarTexto("resumenUtilidad", formatearMoneda(utilidad));

  let margen = 0;

  if (ingresos !== 0) {
    margen = (utilidad / ingresos) * 100;
  }

  actualizarTexto("resumenMargen", `${formatearNumero(margen)}%`);

  actualizarVariacion("variacionIngresos", resumen.variacion_ingresos);

  actualizarVariacion("variacionGastos", resumen.variacion_gastos);

  actualizarVariacion("variacionUtilidad", resumen.variacion_utilidad);
}

//=====================================================
// VARIACIONES
//=====================================================

function actualizarVariacion(id, valor) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  const variacion = convertirNumero(valor);

  const absoluto = Math.abs(variacion);

  elemento.classList.remove("text-success", "text-danger", "text-muted");

  let icono = elemento.querySelector("i");

  if (!icono) {
    icono = document.createElement("i");
  }

  if (variacion > 0) {
    elemento.classList.add("text-success");

    icono.className = "bi bi-arrow-up";
  } else if (variacion < 0) {
    elemento.classList.add("text-danger");

    icono.className = "bi bi-arrow-down";
  } else {
    elemento.classList.add("text-muted");

    icono.className = "bi bi-dash";
  }

  elemento.innerHTML = "";

  elemento.appendChild(icono);

  elemento.append(` ${formatearNumero(absoluto)}%`);
}

//=====================================================
// CUENTAS BANCARIAS
//=====================================================

async function cargarCuentasBancarias(parametros) {
  const tbody = document.getElementById("tablaCuentasBancarias");

  if (!tbody) {
    return [];
  }

  tbody.innerHTML = `
        <tr>
            <td colspan="3"
                class="text-center py-4">

                <div class="spinner-border spinner-border-sm text-primary"
                     role="status">
                </div>

                <span class="text-muted ms-2">
                    Cargando cuentas...
                </span>

            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch(
      ENDPOINTS_CONTABILIDAD.cuentas + "?" + parametros.toString(),
      {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
        cache: "no-store",
      },
    );

    const texto = await respuesta.text();

    if (!respuesta.ok) {
      throw new Error("No se pudieron cargar las cuentas bancarias.");
    }

    let datos;

    try {
      datos = JSON.parse(texto);
    } catch (error) {
      console.error("Respuesta cuentas no válida:", texto);

      throw new Error(
        "El servidor devolvió una respuesta inválida al consultar las cuentas bancarias.",
      );
    }

    if (datos.estado === false) {
      throw new Error(
        datos.mensaje || "Error al cargar las cuentas bancarias.",
      );
    }

    const cuentas = Array.isArray(datos.cuentas)
      ? datos.cuentas
      : Array.isArray(datos.data)
        ? datos.data
        : [];

    renderizarCuentas(cuentas);

    actualizarTexto(
      "cantidadCuentas",
      cuentas.length === 1 ? "1 cuenta" : `${cuentas.length} cuentas`,
    );

    return cuentas;
  } catch (error) {
    console.error("Error cuentas:", error);

    tbody.innerHTML = `
            <tr>
                <td colspan="3"
                    class="text-center text-danger py-4">

                    <i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>

                    <div class="fw-semibold">
                        No se pudieron cargar las cuentas.
                    </div>

                    <div class="small text-muted mt-1">
                        ${escaparHTML(error.message)}
                    </div>

                </td>
            </tr>
        `;

    actualizarTexto("cantidadCuentas", "0 cuentas");

    return [];
  }
}

//=====================================================
// RENDERIZAR CUENTAS
//=====================================================

function renderizarCuentas(cuentas) {
  const tbody = document.getElementById("tablaCuentasBancarias");

  if (!tbody) {
    return;
  }

  if (!Array.isArray(cuentas) || cuentas.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td colspan="3"
                    class="text-center text-muted py-4">

                    <i class="bi bi-bank fs-3 d-block mb-2"></i>

                    No hay cuentas bancarias registradas.

                </td>
            </tr>
        `;

    return;
  }

  tbody.innerHTML = cuentas
    .map(function (cuenta) {
      const nombre = escaparHTML(
        cuenta.nombre || cuenta.nombre_cuenta || "Cuenta bancaria",
      );

      const balance = convertirNumero(cuenta.balance);

      let estado = "";

      if (balance > 0) {
        estado = `
                        <span class="badge bg-success-subtle text-success">
                            Disponible
                        </span>
                    `;
      } else if (balance < 0) {
        estado = `
                        <span class="badge bg-danger-subtle text-danger">
                            Negativo
                        </span>
                    `;
      } else {
        estado = `
                        <span class="badge bg-secondary-subtle text-secondary">
                            Sin saldo
                        </span>
                    `;
      }

      return `
                    <tr>

                        <td>
                            <div class="d-flex align-items-center">

                                <div class="rounded-circle bg-primary-subtle p-2 me-2">
                                    <i class="bi bi-bank text-primary"></i>
                                </div>

                                <span class="fw-semibold">
                                    ${nombre}
                                </span>

                            </div>
                        </td>

                        <td class="text-end fw-semibold">
                            ${formatearMoneda(balance)}
                        </td>

                        <td class="text-end">
                            ${estado}
                        </td>

                    </tr>
                `;
    })
    .join("");
}

//=====================================================
// ÚLTIMOS MOVIMIENTOS
//=====================================================

async function cargarUltimosMovimientos(parametros) {
  const tbody = document.getElementById("tablaUltimosMovimientos");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = `
        <tr>
            <td colspan="6"
                class="text-center py-4">

                <div class="spinner-border spinner-border-sm text-primary"
                     role="status">
                </div>

                <span class="text-muted ms-2">
                    Cargando movimientos...
                </span>

            </td>
        </tr>
    `;

  try {
    const respuesta = await fetch(
      ENDPOINTS_CONTABILIDAD.movimientos + "?" + parametros.toString(),
      {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
        cache: "no-store",
      },
    );

    const texto = await respuesta.text();

    if (!respuesta.ok) {
      throw new Error("No se pudieron cargar los movimientos.");
    }

    let datos;

    try {
      datos = JSON.parse(texto);
    } catch (error) {
      console.error("Respuesta movimientos no válida:", texto);

      throw new Error(
        "El servidor devolvió una respuesta inválida al consultar los movimientos.",
      );
    }

    if (datos.estado === false) {
      throw new Error(datos.mensaje || "Error al cargar los movimientos.");
    }

    const movimientos = Array.isArray(datos.movimientos)
      ? datos.movimientos
      : Array.isArray(datos.data)
        ? datos.data
        : [];

    renderizarMovimientos(movimientos);

    return movimientos;
  } catch (error) {
    console.error("Error movimientos:", error);

    tbody.innerHTML = `
            <tr>
                <td colspan="6"
                    class="text-center text-danger py-5">

                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>

                    <h6 class="fw-semibold">
                        No se pudieron cargar los movimientos.
                    </h6>

                    <p class="small mb-0">
                        ${escaparHTML(error.message)}
                    </p>

                </td>
            </tr>
        `;
  }
}

//=====================================================
// RENDERIZAR MOVIMIENTOS
//=====================================================

function renderizarMovimientos(movimientos) {
  const tbody = document.getElementById("tablaUltimosMovimientos");

  if (!tbody) {
    return;
  }

  if (!Array.isArray(movimientos) || movimientos.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td colspan="6"
                    class="text-center text-muted py-5">

                    <div class="py-3">

                        <i class="bi bi-receipt fs-1 d-block mb-3"></i>

                        <h6 class="fw-semibold">
                            No hay movimientos
                        </h6>

                        <p class="small mb-0">
                            Los movimientos encontrados aparecerán aquí.
                        </p>

                    </div>

                </td>
            </tr>
        `;

    return;
  }

  tbody.innerHTML = movimientos
    .map(function (movimiento) {
      const fecha = formatearFecha(movimiento.fecha);

      const concepto = escaparHTML(movimiento.concepto || "Sin concepto");

      const categoria = escaparHTML(
        movimiento.categoria || movimiento.nombre_categoria || "Sin categoría",
      );

      const metodo = escaparHTML(
        movimiento.metodo_pago || movimiento.nombre_metodo_pago || "Sin método",
      );

      const tipo = String(movimiento.tipo || "")
        .trim()
        .toLowerCase();

      const monto = convertirNumero(
        movimiento.monto_pago ?? movimiento.monto ?? movimiento.montoPago ?? 0,
      );

      const esIngreso =
        tipo === "entrada" ||
        tipo === "ingreso" ||
        tipo === "venta" ||
        tipo === "deposito" ||
        tipo === "depósito";

      const badgeTipo = esIngreso
        ? `
                            <span class="badge bg-success-subtle text-success">
                                <i class="bi bi-arrow-down-left me-1"></i>
                                Ingreso
                            </span>
                        `
        : `
                            <span class="badge bg-danger-subtle text-danger">
                                <i class="bi bi-arrow-up-right me-1"></i>
                                Gasto
                            </span>
                        `;

      const claseMonto = esIngreso ? "text-success" : "text-danger";

      const signo = esIngreso ? "+" : "-";

      return `
                    <tr>

                        <td>
                            ${fecha}
                        </td>

                        <td>
                            <span class="fw-semibold">
                                ${concepto}
                            </span>
                        </td>

                        <td>
                            ${categoria}
                        </td>

                        <td>
                            ${metodo}
                        </td>

                        <td>
                            ${badgeTipo}
                        </td>

                        <td class="text-end fw-semibold ${claseMonto}">
                            ${signo}
                            ${formatearMoneda(monto)}
                        </td>

                    </tr>
                `;
    })
    .join("");
}

//=====================================================
// GRÁFICOS
//=====================================================

function procesarGraficos(datos) {
  const graficos = datos.graficos || datos;

  procesarGraficoIngresosGastos(graficos);

  procesarGraficoGastosCategoria(graficos);

  procesarGraficoEvolucion(graficos);
}

//=====================================================
// GRÁFICOS VACÍOS
//=====================================================

function inicializarGraficosVacios() {
  if (typeof Chart === "undefined") {
    console.warn("Chart.js no está disponible.");

    return;
  }

  crearGraficoIngresosGastos([], [], []);

  crearGraficoGastosCategoria([], []);

  crearGraficoEvolucion([], [], [], []);
}

//=====================================================
// INGRESOS VS GASTOS
//=====================================================

function procesarGraficoIngresosGastos(datos) {
  const grafico = datos.ingresos_gastos || datos.grafico_ingresos_gastos || {};

  const etiquetas = Array.isArray(grafico.etiquetas)
    ? grafico.etiquetas
    : Array.isArray(grafico.labels)
      ? grafico.labels
      : [];

  const ingresos = Array.isArray(grafico.ingresos)
    ? grafico.ingresos.map(convertirNumero)
    : [];

  const gastos = Array.isArray(grafico.gastos)
    ? grafico.gastos.map(convertirNumero)
    : [];

  crearGraficoIngresosGastos(etiquetas, ingresos, gastos);
}

//=====================================================
// CREAR INGRESOS VS GASTOS
//=====================================================

function crearGraficoIngresosGastos(etiquetas, ingresos, gastos) {
  const canvas = document.getElementById("graficoIngresosGastos");

  if (!canvas || typeof Chart === "undefined") {
    return;
  }

  if (graficoIngresosGastos) {
    graficoIngresosGastos.destroy();

    graficoIngresosGastos = null;
  }

  graficoIngresosGastos = new Chart(canvas, {
    type: "bar",

    data: {
      labels: etiquetas,

      datasets: [
        {
          label: "Ingresos",

          data: ingresos,

          backgroundColor: "rgba(25, 135, 84, 0.75)",

          borderColor: "rgb(25, 135, 84)",

          borderWidth: 1,

          borderRadius: 5,
        },

        {
          label: "Gastos",

          data: gastos,

          backgroundColor: "rgba(220, 53, 69, 0.75)",

          borderColor: "rgb(220, 53, 69)",

          borderWidth: 1,

          borderRadius: 5,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      interaction: {
        mode: "index",
        intersect: false,
      },

      plugins: {
        legend: {
          position: "top",
        },

        tooltip: {
          callbacks: {
            label: function (contexto) {
              return (
                " " +
                contexto.dataset.label +
                ": " +
                formatearMoneda(contexto.raw)
              );
            },
          },
        },
      },

      scales: {
        y: {
          beginAtZero: true,

          ticks: {
            callback: function (valor) {
              return "S/ " + formatearNumero(valor);
            },
          },
        },
      },
    },
  });
}

//=====================================================
// GASTOS POR CATEGORÍA
//=====================================================

function procesarGraficoGastosCategoria(datos) {
  const grafico =
    datos.gastos_categoria ||
    datos.gastos_por_categoria ||
    datos.grafico_gastos_categoria ||
    {};

  const etiquetas = Array.isArray(grafico.etiquetas)
    ? grafico.etiquetas
    : Array.isArray(grafico.labels)
      ? grafico.labels
      : [];

  const valores = Array.isArray(grafico.valores)
    ? grafico.valores.map(convertirNumero)
    : Array.isArray(grafico.data)
      ? grafico.data.map(convertirNumero)
      : [];

  crearGraficoGastosCategoria(etiquetas, valores);

  generarLeyendaGastos(etiquetas, valores);
}

//=====================================================
// CREAR GASTOS POR CATEGORÍA
//=====================================================

function crearGraficoGastosCategoria(etiquetas, valores) {
  const canvas = document.getElementById("graficoGastosCategoria");

  if (!canvas || typeof Chart === "undefined") {
    return;
  }

  if (graficoGastosCategoria) {
    graficoGastosCategoria.destroy();

    graficoGastosCategoria = null;
  }

  const colores = [
    "#0d6efd",
    "#198754",
    "#dc3545",
    "#ffc107",
    "#6f42c1",
    "#0dcaf0",
    "#fd7e14",
    "#20c997",
    "#6c757d",
    "#212529",
  ];

  graficoGastosCategoria = new Chart(canvas, {
    type: "doughnut",

    data: {
      labels: etiquetas,

      datasets: [
        {
          data: valores,

          backgroundColor: colores,

          borderWidth: 2,

          borderColor: "#ffffff",
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      cutout: "65%",

      plugins: {
        legend: {
          display: false,
        },

        tooltip: {
          callbacks: {
            label: function (contexto) {
              return (
                " " + contexto.label + ": " + formatearMoneda(contexto.raw)
              );
            },
          },
        },
      },
    },
  });
}

//=====================================================
// LEYENDA GASTOS
//=====================================================

function generarLeyendaGastos(etiquetas, valores) {
  const contenedor = document.getElementById("leyendaGastosCategoria");

  if (!contenedor) {
    return;
  }

  if (!etiquetas.length) {
    contenedor.innerHTML = `
            <div class="text-center text-muted small">
                No hay gastos por categoría.
            </div>
        `;

    return;
  }

  const colores = [
    "#0d6efd",
    "#198754",
    "#dc3545",
    "#ffc107",
    "#6f42c1",
    "#0dcaf0",
    "#fd7e14",
    "#20c997",
    "#6c757d",
    "#212529",
  ];

  const total = valores.reduce(function (acumulado, valor) {
    return acumulado + convertirNumero(valor);
  }, 0);

  contenedor.innerHTML = etiquetas
    .map(function (etiqueta, indice) {
      const valor = convertirNumero(valores[indice]);

      const porcentaje = total > 0 ? (valor / total) * 100 : 0;

      return `
                    <div class="d-flex justify-content-between align-items-center mb-2">

                        <div class="d-flex align-items-center">

                            <span
                                class="rounded-circle me-2"
                                style="
                                    width:10px;
                                    height:10px;
                                    background-color:${
                                      colores[indice % colores.length]
                                    };
                                ">
                            </span>

                            <span class="small">
                                ${escaparHTML(etiqueta)}
                            </span>

                        </div>

                        <div>

                            <span class="small fw-semibold">
                                ${formatearMoneda(valor)}
                            </span>

                            <span class="text-muted small ms-1">
                                (${formatearNumero(porcentaje)}%)
                            </span>

                        </div>

                    </div>
                `;
    })
    .join("");
}

//=====================================================
// EVOLUCIÓN
//=====================================================

function procesarGraficoEvolucion(datos) {
  const grafico =
    datos.evolucion ||
    datos.evolucion_financiera ||
    datos.grafico_evolucion ||
    {};

  const etiquetas = Array.isArray(grafico.etiquetas)
    ? grafico.etiquetas
    : Array.isArray(grafico.labels)
      ? grafico.labels
      : [];

  const ingresos = Array.isArray(grafico.ingresos)
    ? grafico.ingresos.map(convertirNumero)
    : [];

  const gastos = Array.isArray(grafico.gastos)
    ? grafico.gastos.map(convertirNumero)
    : [];

  const utilidad = Array.isArray(grafico.utilidad)
    ? grafico.utilidad.map(convertirNumero)
    : [];

  crearGraficoEvolucion(etiquetas, ingresos, gastos, utilidad);
}

//=====================================================
// CREAR EVOLUCIÓN
//=====================================================

function crearGraficoEvolucion(etiquetas, ingresos, gastos, utilidad) {
  const canvas = document.getElementById("graficoEvolucionFinanciera");

  if (!canvas || typeof Chart === "undefined") {
    return;
  }

  if (graficoEvolucionFinanciera) {
    graficoEvolucionFinanciera.destroy();

    graficoEvolucionFinanciera = null;
  }

  graficoEvolucionFinanciera = new Chart(canvas, {
    type: "line",

    data: {
      labels: etiquetas,

      datasets: [
        {
          label: "Ingresos",

          data: ingresos,

          borderColor: "rgb(25, 135, 84)",

          backgroundColor: "rgba(25, 135, 84, 0.10)",

          tension: 0.35,

          fill: true,

          pointRadius: 3,
        },

        {
          label: "Gastos",

          data: gastos,

          borderColor: "rgb(220, 53, 69)",

          backgroundColor: "rgba(220, 53, 69, 0.08)",

          tension: 0.35,

          fill: true,

          pointRadius: 3,
        },

        {
          label: "Utilidad",

          data: utilidad,

          borderColor: "rgb(13, 110, 253)",

          backgroundColor: "rgba(13, 110, 253, 0.08)",

          tension: 0.35,

          fill: true,

          pointRadius: 3,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      interaction: {
        mode: "index",
        intersect: false,
      },

      plugins: {
        legend: {
          position: "top",
        },

        tooltip: {
          callbacks: {
            label: function (contexto) {
              return (
                " " +
                contexto.dataset.label +
                ": " +
                formatearMoneda(contexto.raw)
              );
            },
          },
        },
      },

      scales: {
        y: {
          beginAtZero: true,

          ticks: {
            callback: function (valor) {
              return "S/ " + formatearNumero(valor);
            },
          },
        },
      },
    },
  });
}

//=====================================================
// CAMBIAR PERÍODO GRÁFICO
//=====================================================

function cambiarPeriodoGrafico(periodo) {
  periodoGraficoActual = periodo;

  actualizarBotonPeriodoGrafico();

  cargarEvolucionPorPeriodo(periodo);
}

//=====================================================
// ACTUALIZAR BOTÓN PERÍODO
//=====================================================

function actualizarBotonPeriodoGrafico() {
  const botones = document.querySelectorAll(".btn-periodo-grafico");

  botones.forEach(function (boton) {
    const activo = parseInt(boton.dataset.periodo, 10) === periodoGraficoActual;

    boton.classList.toggle("active", activo);

    boton.classList.toggle("btn-primary", activo);

    boton.classList.toggle("btn-outline-primary", !activo);
  });
}

//=====================================================
// CARGAR EVOLUCIÓN
//=====================================================

async function cargarEvolucionPorPeriodo(periodo) {
  try {
    const parametros = construirParametros({
      meses: periodo,
    });

    const respuesta = await fetch(
      ENDPOINTS_CONTABILIDAD.resumen + "?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          Accept: "application/json",
        },

        cache: "no-store",
      },
    );

    const texto = await respuesta.text();

    if (!respuesta.ok) {
      throw new Error("No se pudo actualizar la evolución financiera.");
    }

    let datos;

    try {
      datos = JSON.parse(texto);
    } catch (error) {
      console.error("Respuesta evolución no válida:", texto);

      throw new Error(
        "El servidor devolvió una respuesta inválida para la evolución financiera.",
      );
    }

    if (datos.estado === false) {
      throw new Error(datos.mensaje || "Error en la evolución financiera.");
    }

    procesarGraficoEvolucion(datos);
  } catch (error) {
    console.error("Error evolución:", error);
  }
}

//=====================================================
// MODAL EXPORTAR
//=====================================================

function abrirModalExportar() {
  const modalElemento = document.getElementById("modalExportarContabilidad");

  if (!modalElemento || typeof bootstrap === "undefined") {
    return;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(modalElemento);

  modal.show();
}

//=====================================================
// EXPORTAR EXCEL
//=====================================================

function exportarExcel() {
  exportarArchivo(ENDPOINTS_CONTABILIDAD.exportarExcel, "excel");
}

//=====================================================
// EXPORTAR PDF
//=====================================================

function exportarPDF() {
  exportarArchivo(ENDPOINTS_CONTABILIDAD.exportarPDF, "pdf");
}

//=====================================================
// EXPORTAR ARCHIVO
//=====================================================

function exportarArchivo(endpoint, tipo) {
  const parametros = construirParametros();

  const url = endpoint + "?" + parametros.toString();

  const ventana = window.open(url, "_blank");

  if (!ventana) {
    mostrarError(
      `El navegador bloqueó la ventana de exportación ${tipo.toUpperCase()}.`,
    );
  }
}

//=====================================================
// LOADER
//=====================================================

function mostrarLoader() {
  const loader = document.getElementById("loaderContabilidad");

  if (loader) {
    loader.classList.remove("d-none");
  }
}

function ocultarLoader() {
  const loader = document.getElementById("loaderContabilidad");

  if (loader) {
    loader.classList.add("d-none");
  }
}

//=====================================================
// ERROR
//=====================================================

function mostrarError(mensaje) {
  const contenedor = document.getElementById("errorContabilidad");

  const texto = document.getElementById("mensajeErrorContabilidad");

  if (texto) {
    texto.textContent = mensaje;
  }

  if (contenedor) {
    contenedor.classList.remove("d-none");
  }
}

function ocultarError() {
  const contenedor = document.getElementById("errorContabilidad");

  if (contenedor) {
    contenedor.classList.add("d-none");
  }
}

//=====================================================
// ACTUALIZAR TEXTO
//=====================================================

function actualizarTexto(id, texto) {
  const elemento = document.getElementById(id);

  if (elemento) {
    elemento.textContent = texto;
  }
}

//=====================================================
// CONVERTIR NÚMERO
//=====================================================

function convertirNumero(valor) {
  if (valor === null || valor === undefined || valor === "") {
    return 0;
  }

  if (typeof valor === "number") {
    return Number.isFinite(valor) ? valor : 0;
  }

  let texto = String(valor).trim();

  if (!texto) {
    return 0;
  }

  /*
   * Maneja:
   * 1500.50
   * 1500,50
   * 1,500.50
   */

  if (texto.includes(",") && !texto.includes(".")) {
    texto = texto.replace(",", ".");
  } else {
    texto = texto.replace(/,/g, "");
  }

  const numero = parseFloat(texto);

  return Number.isFinite(numero) ? numero : 0;
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(valor) {
  const numero = convertirNumero(valor);

  return (
    CONFIG_CONTABILIDAD.moneda +
    " " +
    numero.toLocaleString("es-PE", {
      minimumFractionDigits: CONFIG_CONTABILIDAD.decimales,

      maximumFractionDigits: CONFIG_CONTABILIDAD.decimales,
    })
  );
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(valor) {
  const numero = convertirNumero(valor);

  return numero.toLocaleString("es-PE", {
    minimumFractionDigits: 0,

    maximumFractionDigits: 2,
  });
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "-";
  }

  const valor = String(fecha).trim();

  /*
   * YYYY-MM-DD
   */

  const matchFecha = valor.match(/^(\d{4})-(\d{2})-(\d{2})/);

  if (matchFecha) {
    return matchFecha[3] + "/" + matchFecha[2] + "/" + matchFecha[1];
  }

  const fechaObj = new Date(valor);

  if (Number.isNaN(fechaObj.getTime())) {
    return escaparHTML(valor);
  }

  return fechaObj.toLocaleDateString("es-PE");
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escaparHTML(texto) {
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
// FIN
//=====================================================
