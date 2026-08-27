//======================================================
// CoDevPro Technology
// Dashboard del Administrador
// js/dashboard.js
//======================================================

//======================================================
// RESUMEN DE PEDIDOS
//======================================================

const graficoPedidos = document.getElementById("graficoPedidos");

if (graficoPedidos) {
  new Chart(graficoPedidos, {
    type: "doughnut",

    data: {
      labels: ["Entregados", "Pendientes", "Cancelados"],

      datasets: [
        {
          data: [pedidosEntregados, pedidosPendientes, pedidosCancelados],

          backgroundColor: ["#198754", "#FFC107", "#DC3545"],

          borderWidth: 0,
        },
      ],
    },

    options: {
      responsive: true,

      plugins: {
        legend: {
          position: "bottom",
        },
      },

      cutout: "70%",
    },
  });
}

//======================================================
// VENTAS DE LOS ÚLTIMOS 7 DÍAS
//======================================================

const graficoVentas = document.getElementById("graficoVentas");

if (graficoVentas) {
  new Chart(graficoVentas, {
    type: "line",

    data: {
      labels: labelsVentas,

      datasets: [
        {
          label: "Ventas",

          data: datosVentas,

          borderColor: "#0D6EFD",

          backgroundColor: "rgba(13,110,253,0.10)",

          fill: true,

          tension: 0.4,

          pointRadius: 5,

          pointHoverRadius: 7,
        },
      ],
    },

    options: {
      responsive: true,

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

//======================================================
// MÉTODOS DE PAGO
//======================================================

const graficoMetodoPago = document.getElementById("graficoMetodoPago");

if (graficoMetodoPago && typeof labelsMetodoPago !== "undefined") {
  new Chart(graficoMetodoPago, {
    type: "doughnut",

    data: {
      labels: labelsMetodoPago,

      datasets: [
        {
          data: datosMetodoPago,

          backgroundColor: [
            "#0D6EFD",
            "#198754",
            "#0DCAF0",
            "#FFC107",
            "#DC3545",
            "#6F42C1",
            "#FD7E14",
            "#20C997",
          ],

          borderWidth: 0,
        },
      ],
    },

    options: {
      responsive: true,

      plugins: {
        legend: {
          position: "bottom",
        },
      },

      cutout: "65%",
    },
  });
}
//======================================================
// CLIENTES OBTENIDOS POR MES
//======================================================

const graficoClientesMes = document.getElementById("graficoClientesMes");

if (graficoClientesMes && typeof labelsClientesMes !== "undefined") {
  new Chart(graficoClientesMes, {
    type: "bar",

    data: {
      labels: labelsClientesMes,

      datasets: [
        {
          label: "Clientes",

          data: datosClientesMes,

          borderRadius: 10,

          backgroundColor: [
            "#0D6EFD",
            "#198754",
            "#FFC107",
            "#DC3545",
            "#6F42C1",
            "#20C997",
            "#FD7E14",
            "#0DCAF0",
            "#6610F2",
            "#198754",
            "#FFC107",
            "#DC3545",
          ],
        },
      ],
    },

    options: {
      responsive: true,

      plugins: {
        legend: {
          display: false,
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
//======================================================
// CALENDARIO DEL DASHBOARD
//======================================================

const calendarioDashboard = document.getElementById("calendarioDashboard");

if (calendarioDashboard) {
  flatpickr("#calendarioDashboard", {
    inline: true,

    locale: "es",

    dateFormat: "Y-m-d",

    defaultDate: "today",
  });
}

//======================================================
// ACTUALIZAR ESTADÍSTICAS
//======================================================

function actualizarDashboard() {
  console.log("Actualizando Dashboard...");

  // Aquí podrás realizar llamadas AJAX.
}

//======================================================
// ACTUALIZACIÓN AUTOMÁTICA
//======================================================

// Cada 60 segundos.

setInterval(() => {
  actualizarDashboard();
}, 60000);

//======================================================
// MENSAJE DE INICIO
//======================================================

document.addEventListener("DOMContentLoaded", () => {
  console.log("Dashboard CoDevPro Technology cargado correctamente.");
});
/*=====================================================
=            CANTIDAD DE VENTAS POR MES
=====================================================*/

const canvasCantidadVentas = document.getElementById("graficoCantidadVentas");

if (canvasCantidadVentas && typeof labelsCantidadVentas !== "undefined") {
  new Chart(canvasCantidadVentas, {
    type: "bar",

    data: {
      labels: labelsCantidadVentas,

      datasets: [
        {
          label: "Ventas",

          data: datosCantidadVentas,

          borderWidth: 1,
          borderRadius: 8,
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

          ticks: {
            precision: 0,
          },
        },
      },
    },
  });
}
