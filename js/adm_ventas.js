//=========================================================
// CoDevPro Technology
// js/adm_ventas.js
// Módulo: Gestión de Ventas
// Sistema: Inventa
//=========================================================

"use strict";

//=========================================================
// VARIABLES GLOBALES
//=========================================================

let paginaActualVentas = 1;
let timeoutBusquedaVentas = null;

let graficoVentasMes = null;
let graficoMetodoPago = null;
let graficoEstadoEnvio = null;

//=========================================================
// INIT
//=========================================================

document.addEventListener("DOMContentLoaded", async () => {
  inicializarFechasVentas();

  await cargarFiltrosVentas();

  await cargarKPIsVentas();

  await cargarGraficosVentas();

  await listarVentas(1);

  inicializarEventosFiltrosVentas();

  inicializarBotonResetVentas();

  inicializarExportacionesVentas();
});

//=========================================================
// INICIALIZAR FECHAS
//=========================================================

function inicializarFechasVentas() {
  const fechaInicio = document.getElementById("fechaInicioVenta");
  const fechaFin = document.getElementById("fechaFinVenta");

  if (typeof flatpickr === "undefined") {
    console.warn("Flatpickr no está cargado.");

    return;
  }

  if (fechaInicio) {
    flatpickr(fechaInicio, {
      dateFormat: "Y-m-d",

      altInput: true,

      altFormat: "d/m/Y",

      allowInput: true,

      disableMobile: true,

      onChange: function () {
        aplicarFiltrosVentas();
      },
    });
  }

  if (fechaFin) {
    flatpickr(fechaFin, {
      dateFormat: "Y-m-d",

      altInput: true,

      altFormat: "d/m/Y",

      allowInput: true,

      disableMobile: true,

      onChange: function () {
        aplicarFiltrosVentas();
      },
    });
  }
}

//=========================================================
// FILTROS
//=========================================================

const filtrosVentas = [
  "buscarVenta",
  "filtroEstadoVenta",
  "filtroEstadoEnvio",
  "filtroMetodoPago",
  "filtroEmpleado",
  "fechaInicioVenta",
  "fechaFinVenta",
];

function inicializarEventosFiltrosVentas() {
  filtrosVentas.forEach((id) => {
    const elemento = document.getElementById(id);

    if (!elemento) return;

    if (elemento.tagName === "SELECT") {
      elemento.addEventListener("change", () => {
        aplicarFiltrosVentas();
      });
    } else if (id !== "fechaInicioVenta" && id !== "fechaFinVenta") {
      elemento.addEventListener("input", () => {
        clearTimeout(timeoutBusquedaVentas);

        timeoutBusquedaVentas = setTimeout(() => {
          aplicarFiltrosVentas();
        }, 500);
      });
    }
  });

  const limite = document.getElementById("limiteVentas");

  if (limite) {
    limite.addEventListener("change", () => {
      paginaActualVentas = 1;

      listarVentas(1);
    });
  }
}

//=========================================================
// APLICAR FILTROS
//=========================================================

async function aplicarFiltrosVentas() {
  paginaActualVentas = 1;

  try {
    await Promise.all([
      cargarKPIsVentas(),

      cargarGraficosVentas(),

      listarVentas(1),
    ]);
  } catch (error) {
    console.error("Error aplicando filtros:", error);
  }
}

//=========================================================
// OBTENER FILTROS
//=========================================================

function obtenerFiltrosVentas() {
  return {
    buscar: obtenerValor("buscarVenta"),

    estadoVenta: obtenerValor("filtroEstadoVenta"),

    estadoEnvio: obtenerValor("filtroEstadoEnvio"),

    metodoPago: obtenerValor("filtroMetodoPago"),

    empleado: obtenerValor("filtroEmpleado"),

    fechaInicio: obtenerValor("fechaInicioVenta"),

    fechaFin: obtenerValor("fechaFinVenta"),

    limite: obtenerValor("limiteVentas") || "10",
  };
}

//=========================================================
// OBTENER VALOR
//=========================================================

function obtenerValor(id) {
  const elemento = document.getElementById(id);

  if (!elemento) return "";

  return String(elemento.value || "").trim();
}

//=========================================================
// RESET
//=========================================================

function inicializarBotonResetVentas() {
  const btn = document.getElementById("btnResetFiltrosVentas");

  if (!btn) return;

  btn.addEventListener("click", async () => {
    const buscar = document.getElementById("buscarVenta");

    const estadoVenta = document.getElementById("filtroEstadoVenta");

    const estadoEnvio = document.getElementById("filtroEstadoEnvio");

    const metodoPago = document.getElementById("filtroMetodoPago");

    const empleado = document.getElementById("filtroEmpleado");

    const fechaInicio = document.getElementById("fechaInicioVenta");

    const fechaFin = document.getElementById("fechaFinVenta");

    const limite = document.getElementById("limiteVentas");

    if (buscar) buscar.value = "";

    if (estadoVenta) estadoVenta.value = "";

    if (estadoEnvio) estadoEnvio.value = "";

    if (metodoPago) metodoPago.value = "";

    if (empleado) empleado.value = "";

    if (limite) limite.value = "10";

    // Limpiar Flatpickr correctamente

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

    paginaActualVentas = 1;

    await aplicarFiltrosVentas();
  });
}

//=========================================================
// VALIDAR RANGO DE FECHAS
//=========================================================

function validarRangoFechas() {
  const fechaInicio = obtenerValor("fechaInicioVenta");

  const fechaFin = obtenerValor("fechaFinVenta");

  if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
    return false;
  }

  return true;
}

//=========================================================
// KPIs
//=========================================================

async function cargarKPIsVentas() {
  try {
    const filtros = obtenerFiltrosVentas();

    const formData = new FormData();

    Object.entries(filtros).forEach(([key, value]) => {
      formData.append(key, value);
    });

    const response = await fetch("ajax/obtener_kpis_ventas.php", {
      method: "POST",

      body: formData,
    });

    const data = await response.json();

    if (!data.estado) {
      console.error(data.mensaje || "Error KPI");

      return;
    }

    const kpiVentasHoy = document.getElementById("kpiVentasHoy");

    const kpiVentasMes = document.getElementById("kpiVentasMes");

    const kpiTicketPromedio = document.getElementById("kpiTicketPromedio");

    const kpiPedidosPendientes = document.getElementById(
      "kpiPedidosPendientes",
    );

    const kpiTotalVentas = document.getElementById("kpiTotalVentas");

    const kpiVentasOnline = document.getElementById("kpiVentasOnline");

    const kpiEntregados = document.getElementById("kpiEntregados");

    const kpiCancelados = document.getElementById("kpiCancelados");

    if (kpiVentasHoy) {
      kpiVentasHoy.textContent = "S/ " + data.ventasHoy;
    }

    if (kpiVentasMes) {
      kpiVentasMes.textContent = "S/ " + data.ventasMes;
    }

    if (kpiTicketPromedio) {
      kpiTicketPromedio.textContent = "S/ " + data.ticketPromedio;
    }

    if (kpiPedidosPendientes) {
      kpiPedidosPendientes.textContent = data.pendientes;
    }

    if (kpiTotalVentas) {
      kpiTotalVentas.textContent = data.totalVentas;
    }

    if (kpiVentasOnline) {
      kpiVentasOnline.textContent = data.ventasOnline;
    }

    if (kpiEntregados) {
      kpiEntregados.textContent = data.entregados;
    }

    if (kpiCancelados) {
      kpiCancelados.textContent = data.cancelados;
    }
  } catch (error) {
    console.error("Error KPI:", error);
  }
}

//=========================================================
// GRÁFICOS
//=========================================================

async function cargarGraficosVentas() {
  try {
    const filtros = obtenerFiltrosVentas();

    const formData = new FormData();

    Object.entries(filtros).forEach(([key, value]) => {
      formData.append(key, value);
    });

    const response = await fetch("ajax/obtener_graficos_ventas.php", {
      method: "POST",

      body: formData,
    });

    const data = await response.json();

    if (!data.estado) {
      console.error(data.mensaje || "Error gráficos");

      return;
    }

    crearGraficoVentas(data.ventas.labels, data.ventas.data);

    crearGraficoMetodoPago(data.metodosPago.labels, data.metodosPago.data);

    crearGraficoEstadoEnvio(data.estadoEnvio.labels, data.estadoEnvio.data);
  } catch (error) {
    console.error("Error gráficos:", error);
  }
}

//=========================================================
// GRÁFICO EVOLUCIÓN
//=========================================================

function crearGraficoVentas(labels, valores) {
  if (graficoVentasMes) {
    graficoVentasMes.destroy();
  }

  const canvas = document.getElementById("graficoVentasMes");

  if (!canvas) return;

  graficoVentasMes = new Chart(canvas, {
    type: "line",

    data: {
      labels: labels,

      datasets: [
        {
          label: "Ventas (S/)",

          data: valores,

          borderWidth: 3,

          tension: 0.4,

          fill: true,
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
      },

      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}

//=========================================================
// GRÁFICO MÉTODO PAGO
//=========================================================

function crearGraficoMetodoPago(labels, valores) {
  if (graficoMetodoPago) {
    graficoMetodoPago.destroy();
  }

  const canvas = document.getElementById("graficoMetodoPago");

  if (!canvas) return;

  graficoMetodoPago = new Chart(canvas, {
    type: "doughnut",

    data: {
      labels: labels,

      datasets: [
        {
          data: valores,
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

//=========================================================
// GRÁFICO ESTADO ENVÍO
//=========================================================

function crearGraficoEstadoEnvio(labels, valores) {
  if (graficoEstadoEnvio) {
    graficoEstadoEnvio.destroy();
  }

  const canvas = document.getElementById("graficoEstadoEnvio");

  if (!canvas) return;

  graficoEstadoEnvio = new Chart(canvas, {
    type: "bar",

    data: {
      labels: labels,

      datasets: [
        {
          label: "Pedidos",

          data: valores,

          borderWidth: 1,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      scales: {
        y: {
          beginAtZero: true,

          ticks: {
            precision: 0,
          },
        },
      },
    },
  });
}

//=========================================================
// CARGAR SELECTS
//=========================================================

async function cargarFiltrosVentas() {
  try {
    const response = await fetch("ajax/obtener_filtros_ventas.php");

    const data = await response.json();

    if (!data.estado) {
      console.error(data.mensaje);

      return;
    }

    const metodoPago = document.getElementById("filtroMetodoPago");

    const empleado = document.getElementById("filtroEmpleado");

    if (!metodoPago || !empleado) {
      return;
    }

    metodoPago.innerHTML = '<option value="">Todos</option>';

    empleado.innerHTML = '<option value="">Todos</option>';

    data.metodosPago.forEach((item) => {
      const option = document.createElement("option");

      option.value = item.id;

      option.textContent = item.nombre;

      metodoPago.appendChild(option);
    });

    data.empleados.forEach((item) => {
      const option = document.createElement("option");

      option.value = item.id;

      option.textContent = item.nombre;

      empleado.appendChild(option);
    });
  } catch (error) {
    console.error("Error filtros:", error);
  }
}

//=========================================================
// LISTAR VENTAS
//=========================================================

async function listarVentas(pagina = 1) {
  if (!validarRangoFechas()) {
    alert("La Fecha Inicio no puede ser mayor que la Fecha Fin.");

    return;
  }

  paginaActualVentas = pagina;

  try {
    const filtros = obtenerFiltrosVentas();

    const formData = new FormData();

    Object.entries(filtros).forEach(([key, value]) => {
      formData.append(key, value);
    });

    formData.append("pagina", pagina);

    const response = await fetch("ajax/listar_ventas.php", {
      method: "POST",

      body: formData,
    });

    const data = await response.json();

    if (!data.estado) {
      console.error(data.mensaje || "Error listado");

      return;
    }

    const tabla = document.getElementById("tablaVentas");

    const total = document.getElementById("totalVentasEncontradas");

    const info = document.getElementById("infoPaginacionVentas");

    const paginacion = document.getElementById("paginacionVentas");

    if (tabla) {
      tabla.innerHTML = data.tabla;
    }

    if (total) {
      total.textContent = data.totalRegistros + " registros";
    }

    if (info) {
      info.textContent = data.info;
    }

    if (paginacion) {
      paginacion.innerHTML = data.paginacion;
    }
  } catch (error) {
    console.error("Error listado:", error);
  }
}

//=========================================================
// PAGINACIÓN
//=========================================================

document.addEventListener("click", (e) => {
  const boton = e.target.closest(".pagina-ventas");

  if (!boton) return;

  e.preventDefault();

  if (boton.parentElement.classList.contains("disabled")) {
    return;
  }

  const pagina = parseInt(boton.dataset.pagina, 10);

  if (!pagina || pagina < 1) {
    return;
  }

  listarVentas(pagina);
});

//=========================================================
// VER VENTA
//=========================================================

document.addEventListener("click", async (e) => {
  const boton = e.target.closest(".btnVerVenta");

  if (!boton) return;

  const idVenta = boton.dataset.id;

  try {
    const formData = new FormData();

    formData.append("idVenta", idVenta);

    const response = await fetch("ajax/obtener_detalle_venta.php", {
      method: "POST",

      body: formData,
    });

    const data = await response.json();

    if (!data.estado) {
      alert(data.mensaje);

      return;
    }

    renderDetalleVenta(data);

    const modal = new bootstrap.Modal(document.getElementById("modalVerVenta"));

    modal.show();
  } catch (error) {
    console.error(error);
  }
});

//=========================================================
// RENDER DETALLE
//=========================================================

function renderDetalleVenta(data) {
  const venta = data.venta;

  let progreso = 0;

  switch (venta.estado_envio) {
    case "PENDIENTE":
      progreso = 10;
      break;

    case "CONFIRMADO":
      progreso = 30;
      break;

    case "PREPARANDO":
      progreso = 55;
      break;

    case "ENVIADO":
      progreso = 80;
      break;

    case "ENTREGADO":
      progreso = 100;
      break;

    case "CANCELADO":
      progreso = 100;
      break;
  }

  let productosHTML = "";

  data.productos.forEach((producto) => {
    productosHTML += `

                <div class="card border-0 shadow-sm mb-3">

                    <div class="card-body">

                        <div class="row align-items-center">

                            <div class="col-md-2">

                                <img
                                    src="${producto.imagen}"
                                    class="img-fluid rounded"
                                    style="
                                        height:70px;
                                        object-fit:cover;
                                        width:70px;
                                    "
                                >

                            </div>

                            <div class="col-md-5">

                                <h6 class="fw-bold mb-1">

                                    ${producto.nombre}

                                </h6>

                                <small class="text-muted">

                                    SKU: ${producto.codigo}

                                </small>

                            </div>

                            <div class="col-md-2 text-center">

                                x${producto.cantidad_pedido_producto}

                            </div>

                            <div class="col-md-3 text-end">

                                <div>

                                    S/
                                    ${parseFloat(producto.precio).toFixed(2)}

                                </div>

                                <strong class="text-success">

                                    S/
                                    ${parseFloat(producto.sub_total).toFixed(2)}

                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            `;
  });

  let timeline = `

        <div class="timeline-item">

            <strong>
                Pedido Registrado
            </strong>

            <div>
                ${venta.fecha_venta}
            </div>

        </div>

    `;

  if (venta.fecha_confirmado) {
    timeline += `

            <div class="timeline-item text-success">

                <strong>
                    Confirmado
                </strong>

                <div>
                    ${venta.fecha_confirmado}
                </div>

            </div>

        `;
  }

  if (venta.fecha_preparando) {
    timeline += `

            <div class="timeline-item text-warning">

                <strong>
                    Preparando
                </strong>

                <div>
                    ${venta.fecha_preparando}
                </div>

            </div>

        `;
  }

  if (venta.fecha_enviado) {
    timeline += `

            <div class="timeline-item text-primary">

                <strong>
                    Enviado
                </strong>

                <div>
                    ${venta.fecha_enviado}
                </div>

            </div>

        `;
  }

  if (venta.fecha_entregado) {
    timeline += `

            <div class="timeline-item text-success">

                <strong>
                    Entregado
                </strong>

                <div>
                    ${venta.fecha_entregado}
                </div>

            </div>

        `;
  }

  if (venta.fecha_cancelado) {
    timeline += `

            <div class="timeline-item text-danger">

                <strong>
                    Cancelado
                </strong>

                <div>
                    ${venta.fecha_cancelado}
                </div>

            </div>

        `;
  }
  //=========================================================
  // REPARTIDOR
  //=========================================================

  let repartidorHTML = "";

  if (venta.tiene_repartidor && venta.repartidor) {
    const repartidor = venta.repartidor;

    repartidorHTML = `

        <div class="card shadow-sm mb-4">

            <div class="card-header">

                <i class="bi bi-person-badge text-primary"></i>

                Repartidor asignado

            </div>

            <div class="card-body">

                <div class="d-flex align-items-center gap-3">

                    <div
                        class="avatar-shopify"
                        style="
                            width:60px;
                            height:60px;
                            min-width:60px;
                        "
                    >

                        <i class="bi bi-person-fill"></i>

                    </div>

                    <div>

                        <h6 class="fw-bold mb-1">

                            ${repartidor.nombre || "Sin nombre"}

                        </h6>

                        <small class="text-muted">

                            ID Empleado:
                            #${repartidor.id}

                        </small>

                    </div>

                </div>

                <hr>

                <div class="mb-2">

                    <i class="bi bi-card-text text-primary me-2"></i>

                    <strong>DNI:</strong>

                    <span class="text-muted">

                        ${repartidor.dni || "No registrado"}

                    </span>

                </div>

                <div class="mb-2">

                    <i class="bi bi-phone text-success me-2"></i>

                    <strong>Celular:</strong>

                    ${
                      repartidor.celular
                        ? `
                            <a
                                href="tel:${repartidor.celular}"
                                class="text-decoration-none"
                            >
                                ${repartidor.celular}
                            </a>
                          `
                        : `
                            <span class="text-muted">
                                No registrado
                            </span>
                          `
                    }

                </div>

                <div>

                    <i class="bi bi-envelope text-primary me-2"></i>

                    <strong>Email:</strong>

                    <span class="text-muted">

                        ${repartidor.email || "No registrado"}

                    </span>

                </div>

            </div>

        </div>

    `;
  } else {
    repartidorHTML = `

        <div class="card shadow-sm mb-4">

            <div class="card-header">

                <i class="bi bi-person-badge text-secondary"></i>

                Repartidor

            </div>

            <div class="card-body">

                <div class="text-center py-3">

                    <div
                        class="mb-2"
                        style="font-size:40px;"
                    >

                        <i class="bi bi-person-x text-muted"></i>

                    </div>

                    <h6 class="fw-bold text-muted">

                        Sin repartidor asignado

                    </h6>

                    <small class="text-muted">

                        Este pedido todavía no tiene un repartidor registrado.

                    </small>

                </div>

            </div>

        </div>

    `;
  }
  const bloqueado =
    venta.estado_envio === "ENTREGADO" || venta.estado_envio === "CANCELADO";

  document.getElementById("contenidoDetalleVenta").innerHTML = `

        <div class="row g-4">

            <div class="col-lg-8">

                <div class="card shadow-sm mb-4">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <h4 class="fw-bold">

                                    Pedido
                                    #${venta.id_ticket_ventas}

                                </h4>

                                <small class="text-muted">

                                    ${venta.tipo_comprobante}
                                    ${venta.serie}-
                                    ${String(venta.numero).padStart(8, "0")}

                                </small>

                            </div>

                            <div class="text-end">

                                <span class="badge bg-primary">

                                    ${venta.estado_envio}

                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="card shadow-sm mb-4">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <span>
                                Estado del envío
                            </span>

                            <strong>
                                ${venta.estado_envio}
                            </strong>

                        </div>

                        <div class="progress mt-3">

                            <div
                                class="progress-bar bg-success"
                                style="
                                    width:${progreso}%
                                "
                            ></div>

                        </div>

                    </div>

                </div>


                <div class="card shadow-sm mb-4">

                    <div class="card-header">

                        Productos

                    </div>

                    <div class="card-body">

                        ${productosHTML}

                    </div>

                </div>


                <div class="card shadow-sm">

                    <div class="card-header">

                        Seguimiento

                    </div>

                    <div class="card-body">

                        ${timeline}

                    </div>

                </div>

            </div>


            <div class="col-lg-4">

                <div class="card shadow-sm mb-4">

                    <div class="card-body text-center">

                        <small class="text-muted">

                            Total Pagado

                        </small>

                        <h2 class="fw-bold text-success">

                            S/
                            ${parseFloat(venta.total_venta).toFixed(2)}

                        </h2>

                    </div>

                </div>
                
                <!-- =========================================
                     REPARTIDOR
                ========================================== -->

                ${repartidorHTML}


                <!-- =========================================
                     CLIENTE
                ========================================== -->

                <div class="card shadow-sm mb-4">

                    <div class="card-header">

                        <i class="bi bi-person text-primary"></i>

                        Cliente

                    </div>

                    <div class="card-body">

                        <h6 class="fw-bold">

                            ${venta.cliente}

                        </h6>

                        <div class="mb-1">

                            <i class="bi bi-envelope me-2"></i>

                            ${venta.email || "Sin email"}

                        </div>

                        <div>

                            <i class="bi bi-phone me-2"></i>

                            ${venta.celular || "Sin celular"}

                        </div>

                    </div>

                </div>
                <div class="card shadow-sm">

                    <div class="card-header">

                        Resumen Financiero

                    </div>

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <span>
                                Total
                            </span>

                            <strong>

                                S/
                                ${parseFloat(venta.total_venta).toFixed(2)}

                            </strong>

                        </div>


                        <div class="d-flex justify-content-between">

                            <span>
                                Pago Cliente
                            </span>

                            <strong>

                                S/
                                ${parseFloat(venta.pago_cliente).toFixed(2)}

                            </strong>

                        </div>


                        <div class="d-flex justify-content-between">

                            <span>
                                Vuelto
                            </span>

                            <strong>

                                S/
                                ${parseFloat(venta.vuelto_venta).toFixed(2)}

                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    `;

  const selectEstado = document.getElementById("nuevoEstadoPedido");

  if (selectEstado) {
    selectEstado.value = venta.estado_envio;
  }
}

//=========================================================
// GUARDAR ESTADO PEDIDO
//=========================================================

document.addEventListener("click", async (e) => {
  const boton = e.target.closest("#btnGuardarEstadoPedido");

  if (!boton) return;

  const idVenta = boton.dataset.id;

  const select = document.getElementById("nuevoEstadoPedido");

  if (!select) return;

  const nuevoEstado = select.value;

  await actualizarEstadoPedido(idVenta, nuevoEstado);
});

//=========================================================
// ACTUALIZAR ESTADO
//=========================================================

async function actualizarEstadoPedido(idVenta, nuevoEstado) {
  try {
    const formData = new FormData();

    formData.append("idVenta", idVenta);

    formData.append("estado", nuevoEstado);

    const response = await fetch("ajax/actualizar_estado_pedido_adm.php", {
      method: "POST",

      body: formData,
    });

    const data = await response.json();

    if (!data.estado) {
      alert(data.mensaje);

      return;
    }

    alert(data.mensaje);

    await Promise.all([
      listarVentas(paginaActualVentas),

      cargarKPIsVentas(),

      cargarGraficosVentas(),
    ]);

    const modal = bootstrap.Modal.getInstance(
      document.getElementById("modalVerVenta"),
    );

    if (modal) {
      modal.hide();
    }
  } catch (error) {
    console.error(error);

    alert("Error al actualizar estado.");
  }
}

//=========================================================
// DESCARGAR COMPROBANTE
//=========================================================

document.addEventListener("click", (e) => {
  const boton = e.target.closest(".btnDescargarComprobante");

  if (!boton) return;

  const idVenta = boton.dataset.id;

  window.open(`ajax/generar_comprobante_pdf.php?idVenta=${idVenta}`, "_blank");
});

//=========================================================
// EXPORTACIONES
//=========================================================

function inicializarExportacionesVentas() {
  //=========================================
  // EXCEL
  //=========================================

  const btnExcel = document.getElementById("btnExportarVentasExcel");

  if (btnExcel) {
    btnExcel.addEventListener("click", () => {
      const modal = new bootstrap.Modal(
        document.getElementById("modalExportarVentasExcel"),
      );

      modal.show();
    });
  }

  const btnGenerarExcel = document.getElementById("btnGenerarExcelVentas");

  if (btnGenerarExcel) {
    btnGenerarExcel.addEventListener("click", () => {
      const campos = [];

      document.querySelectorAll(".campoExcel:checked").forEach((item) => {
        campos.push(item.value);
      });

      const checkbox = document.getElementById("exportarProductos");

      const incluirProductos = checkbox && checkbox.checked ? 1 : 0;

      const filtros = obtenerFiltrosVentas();

      const params = new URLSearchParams();

      params.append("campos", JSON.stringify(campos));

      params.append("productos", incluirProductos);

      Object.entries(filtros).forEach(([key, value]) => {
        params.append(key, value);
      });

      window.open(
        "ajax/exportar_ventas_excel.php?" + params.toString(),
        "_blank",
      );
    });
  }

  //=========================================
  // PDF
  //=========================================

  const btnPDF = document.getElementById("btnExportarVentasPDF");

  if (btnPDF) {
    btnPDF.addEventListener("click", () => {
      const modal = new bootstrap.Modal(
        document.getElementById("modalExportarVentasPDF"),
      );

      modal.show();
    });
  }
}

//=========================================================
// GENERAR PDF
//=========================================================

document.addEventListener("click", (e) => {
  if (e.target.id !== "btnGenerarPDFVentas") {
    return;
  }

  const campos = [];

  document.querySelectorAll(".campoPDF:checked").forEach((item) => {
    campos.push(item.value);
  });

  const checkbox = document.getElementById("pdfIncluirProductos");

  const incluirProductos = checkbox && checkbox.checked ? 1 : 0;

  const filtros = obtenerFiltrosVentas();

  const params = new URLSearchParams();

  params.append("campos", JSON.stringify(campos));

  params.append("productos", incluirProductos);

  Object.entries(filtros).forEach(([key, value]) => {
    params.append(key, value);
  });

  window.open("ajax/exportar_ventas_pdf.php?" + params.toString(), "_blank");
});
