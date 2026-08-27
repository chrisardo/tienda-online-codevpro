//=========================================================
// CoDevPro Technology
// js/adm_estadistica_cliente.js
//=========================================================

let graficoClientesMes = null;
let graficoSegmentacion = null;

document.addEventListener("DOMContentLoaded", () => {
  cargarKPIs();
});

/*=============================================
CARGAR DASHBOARD CLIENTES
=============================================*/
async function cargarKPIs() {
  try {
    const response = await fetch("ajax/obtener_kpis_estadisticas_clientes.php");

    const data = await response.json();

    if (!data.ok) {
      console.error(data.mensaje);
      return;
    }

    /*=============================================
    KPIs
    =============================================*/

    document.querySelector("#kpiTotalClientes").textContent =
      data.kpis.totalClientes;

    document.querySelector("#kpiClientesActivos").textContent =
      data.kpis.clientesActivos;

    document.querySelector("#kpiClientesInactivos").textContent =
      data.kpis.clientesInactivos;

    document.querySelector("#kpiNuevosMes").textContent = data.kpis.nuevosMes;

    document.querySelector("#kpiTicketPromedio").textContent =
      "S/ " + data.kpis.ticketPromedio;

    document.querySelector("#kpiVip").textContent = data.kpis.clientesVip;

    document.querySelector("#kpiValorCliente").textContent =
      "S/ " + data.kpis.valorCliente;

    document.querySelector("#kpiConversionClientes").textContent =
      data.kpis.conversion + "%";

    /*=============================================
    GRAFICOS
    =============================================*/

    crearGraficoClientes(
      data.graficoClientes.labels,
      data.graficoClientes.data,
    );

    crearGraficoSegmentacion(data.segmentacion.labels, data.segmentacion.data);

    /*=============================================
    TOP CLIENTES
    =============================================*/

    renderTopClientes(data.topClientes || []);

    /*=============================================
    CLIENTES RECIENTES
    =============================================*/

    renderClientesRecientes(data.clientesRecientes || []);
  } catch (error) {
    console.error("Error cargando estadísticas:", error);
  }
}

/*=============================================
GRAFICO EVOLUCION CLIENTES
=============================================*/
function crearGraficoClientes(labels, datos) {
  const canvas = document.getElementById("graficoClientesMes");

  if (!canvas) return;

  if (graficoClientesMes) {
    graficoClientesMes.destroy();
  }

  graficoClientesMes = new Chart(canvas, {
    type: "line",

    data: {
      labels: labels,

      datasets: [
        {
          label: "Clientes Registrados",

          data: datos,

          borderWidth: 3,

          tension: 0.4,

          fill: true,

          pointRadius: 4,

          pointHoverRadius: 6,
        },
      ],
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      plugins: {
        legend: {
          display: true,
        },
      },

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

/*=============================================
GRAFICO SEGMENTACION
=============================================*/
function crearGraficoSegmentacion(labels, datos) {
  const canvas = document.getElementById("graficoSegmentacion");

  if (!canvas) return;

  if (graficoSegmentacion) {
    graficoSegmentacion.destroy();
  }

  graficoSegmentacion = new Chart(canvas, {
    type: "doughnut",

    data: {
      labels: labels,

      datasets: [
        {
          data: datos,

          borderWidth: 2,
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

/*=============================================
TOP CLIENTES COMPRADORES
=============================================*/
function renderTopClientes(clientes) {
  const contenedor = document.getElementById("topClientesCompradores");

  if (!contenedor) return;

  if (!clientes.length) {
    contenedor.innerHTML = `
      <div class="text-center py-5 text-muted">
        <i class="bi bi-people fs-1"></i>
        <p class="mb-0 mt-2">
          No hay clientes con compras registradas
        </p>
      </div>
    `;
    return;
  }

  let html = "";

  clientes.forEach((cliente, index) => {
    html += `
      <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

        <div class="d-flex align-items-center">

          <div class="me-3">

            <span class="badge bg-warning text-dark fs-6">

              #${index + 1}

            </span>

          </div>

          <div>

            <div class="fw-semibold">

              ${cliente.nombre || ""}
              ${cliente.apellido || ""}

            </div>

            <small class="text-muted">

              ${cliente.pedidos} pedido(s)

            </small>

          </div>

        </div>

        <div class="text-end">

          <span class="badge bg-success">

            S/ ${parseFloat(cliente.totalComprado || 0).toFixed(2)}

          </span>

        </div>

      </div>
    `;
  });

  contenedor.innerHTML = html;
}

/*=============================================
CLIENTES RECIENTES
=============================================*/
function renderClientesRecientes(clientes) {
  const contenedor = document.getElementById("clientesRecientes");

  if (!contenedor) return;

  if (!clientes.length) {
    contenedor.innerHTML = `
      <div class="text-center py-5 text-muted">
        <i class="bi bi-person-plus fs-1"></i>
        <p class="mb-0 mt-2">
          No hay clientes recientes
        </p>
      </div>
    `;
    return;
  }

  let html = "";

  clientes.forEach((cliente) => {
    let fecha = "";

    if (cliente.fecha_registro) {
      fecha = new Date(cliente.fecha_registro).toLocaleDateString("es-PE");
    }

    html += `
      <div class="d-flex justify-content-between align-items-center py-3 border-bottom">

        <div>

          <div class="fw-semibold">

            ${cliente.nombre || ""}
            ${cliente.apellido || ""}

          </div>

          <small class="text-muted">

            ${cliente.email || "-"}

          </small>

        </div>

        <div>

          <span class="badge bg-primary">

            ${fecha}

          </span>

        </div>

      </div>
    `;
  });

  contenedor.innerHTML = html;
}
/*=============================================
LISTAR CLIENTES
=============================================*/

let paginaActualClientes = 1;

document.addEventListener("DOMContentLoaded", () => {
  flatpickr("#fechaInicio", {
    dateFormat: "Y-m-d",

    allowInput: true,
  });

  flatpickr("#fechaFin", {
    dateFormat: "Y-m-d",

    allowInput: true,
  });
  cargarKPIs();
  cargarDepartamentos(); // <-- Call the function to load departments
  inicializarFiltrosClientes();

  listarClientes();
});
/*=============================================
CARGAR DEPARTAMENTOS
=============================================*/

async function cargarDepartamentos() {
  try {
    const response = await fetch("ajax/obtener_departamentos_clientes.php");

    const data = await response.json();

    if (!data.ok) return;

    const select = document.querySelector("#filtroDepartamento");

    if (!select) return;

    select.innerHTML = `
            <option value="">
                Todos
            </option>
        `;

    data.departamentos.forEach((dep) => {
      select.innerHTML += `
                <option value="${dep.id_departamento}">
                    ${dep.nombre}
                </option>
            `;
    });
  } catch (error) {
    console.error("Error cargando departamentos:", error);
  }
}
function inicializarFiltrosClientes() {
  const buscar = document.querySelector("#buscarClienteStats");
  const estado = document.querySelector("#filtroEstado");
  const departamento = document.querySelector("#filtroDepartamento");
  const fechaInicio = document.querySelector("#fechaInicio");
  const fechaFin = document.querySelector("#fechaFin");

  let timeout;

  buscar.addEventListener("keyup", () => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
      paginaActualClientes = 1;

      listarClientes();
    }, 400);
  });

  [estado, departamento].forEach((control) => {
    control.addEventListener("change", () => {
      paginaActualClientes = 1;

      listarClientes();
    });
  });

  [fechaInicio, fechaFin].forEach((control) => {
    control.addEventListener("change", () => {
      paginaActualClientes = 1;

      listarClientes();
    });
  });

  document.querySelector("#btnResetFiltros").addEventListener("click", () => {
    buscar.value = "";
    estado.value = "";
    departamento.value = "";
    fechaInicio.value = "";
    fechaFin.value = "";

    paginaActualClientes = 1;

    listarClientes();
  });
}
async function listarClientes(pagina = 1) {
  paginaActualClientes = pagina;

  const formData = new FormData();

  formData.append(
    "buscar",
    document.querySelector("#buscarClienteStats").value,
  );

  formData.append("estado", document.querySelector("#filtroEstado").value);

  formData.append(
    "departamento",
    document.querySelector("#filtroDepartamento").value,
  );

  formData.append("fechaInicio", document.querySelector("#fechaInicio").value);

  formData.append("fechaFin", document.querySelector("#fechaFin").value);

  formData.append("pagina", pagina);

  try {
    const response = await fetch("ajax/listar_estadisticas_clientes.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (!data.ok) return;

    document.querySelector("#tablaEstadisticasClientes").innerHTML = data.tabla;

    document.querySelector("#paginacionClientes").innerHTML = data.paginacion;
  } catch (error) {
    console.error(error);
  }
}
document.addEventListener("click", function (e) {
  const pagina = e.target.dataset.pagina;

  if (!pagina) return;

  e.preventDefault();

  listarClientes(pagina);
});
/*=============================================
VER CLIENTE
=============================================*/

document.addEventListener("click", async (e) => {
  const boton = e.target.closest(".btnVerCliente");

  if (!boton) return;

  const idCliente = boton.dataset.id;

  const modal = new bootstrap.Modal(document.getElementById("modalVerCliente"));

  modal.show();

  document.querySelector("#contenidoModalCliente").innerHTML = `
      <div class="text-center py-5">
          <div class="spinner-border text-primary"></div>
      </div>
  `;

  try {
    const formData = new FormData();

    formData.append("idCliente", idCliente);

    const response = await fetch(
      "ajax/obtener_detalle_estadistica_cliente.php",
      {
        method: "POST",
        body: formData,
      },
    );

    const data = await response.json();

    if (!data.ok) {
      document.querySelector("#contenidoModalCliente").innerHTML =
        `<div class="alert alert-danger">${data.mensaje}</div>`;
      return;
    }

    document.querySelector("#contenidoModalCliente").innerHTML = data.html;
  } catch (error) {
    console.error(error);

    document.querySelector("#contenidoModalCliente").innerHTML =
      `<div class="alert alert-danger">
          Error cargando cliente
       </div>`;
  }
});
/*=============================================
EXPORTAR EXCEL
=============================================*/
document
  .getElementById("btnExportarClientesExcel")
  .addEventListener("click", () => {
    const params = new URLSearchParams({
      buscar: document.getElementById("buscarClienteStats").value,

      estado: document.getElementById("filtroEstado").value,

      departamento: document.getElementById("filtroDepartamento").value,

      fechaInicio: document.getElementById("fechaInicio").value,

      fechaFin: document.getElementById("fechaFin").value,
    });

    window.open(
      "ajax/exportar_clientes_estadistico_excel.php?" + params.toString(),
      "_blank",
    );
  });
/*=============================================
EXPORTAR PDF
=============================================*/
document
  .getElementById("btnExportarClientesPDF")
  .addEventListener("click", () => {
    const params = new URLSearchParams({
      buscar: document.getElementById("buscarClienteStats").value,

      estado: document.getElementById("filtroEstado").value,

      departamento: document.getElementById("filtroDepartamento").value,

      fechaInicio: document.getElementById("fechaInicio").value,

      fechaFin: document.getElementById("fechaFin").value,
    });

    window.open(
      "ajax/exportar_clientes_estadistico_pdf.php?" + params.toString(),
      "_blank",
    );
  });
