//=========================================================
// CoDevPro Technology
// js/adm_favoritos.js
//=========================================================

document.addEventListener("DOMContentLoaded", () => {
  let paginaActual = 1;

  let graficoProductos = null;
  let graficoCategorias = null;
  /*=============================================
    =            FLATPICKR
    =============================================*/

  if (document.querySelector("#fechaInicio")) {
    flatpickr("#fechaInicio", {
      locale: "es",

      dateFormat: "Y-m-d",

      allowInput: true,

      onChange: function () {
        actualizarTodo();
      },
    });
  }

  if (document.querySelector("#fechaFin")) {
    flatpickr("#fechaFin", {
      locale: "es",

      dateFormat: "Y-m-d",

      allowInput: true,

      onChange: function () {
        actualizarTodo();
      },
    });
  }

  /*=============================================
    =            INICIALIZAR
    =============================================*/

  cargarKPIs();

  cargarFavoritos();

  cargarCategorias();
  cargarMapaCalor();
  /*=============================================
    =            BUSCADOR
    =============================================*/

  let timeoutBusqueda;

  document.addEventListener("keyup", function (e) {
    if (e.target.id === "buscarFavorito") {
      clearTimeout(timeoutBusqueda);

      timeoutBusqueda = setTimeout(() => {
        paginaActual = 1;

        actualizarTodo();
      }, 400);
    }
  });
  /*=============================================
    =            FILTRAR
    =============================================*/

  /*=============================================
=            FILTROS AUTOMATICOS
=============================================*/

  function actualizarTodo() {
    paginaActual = 1;

    cargarFavoritos();

    cargarKPIs();

    cargarMapaCalor();
  }

  /* Categoria */

  document.addEventListener("change", function (e) {
    if (e.target.id === "filtroCategoria") {
      actualizarTodo();
    }
  });

  /* Fecha Inicio */

  document.addEventListener("change", function (e) {
    if (e.target.id === "fechaInicio") {
      actualizarTodo();
    }
  });

  /* Fecha Fin */

  document.addEventListener("change", function (e) {
    if (e.target.id === "fechaFin") {
      actualizarTodo();
    }
  });

  /*=============================================
    =            PAGINACION
    =============================================*/

  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btnPaginaFavoritos");

    if (!btn) return;

    paginaActual = parseInt(btn.dataset.pagina);

    cargarFavoritos();

    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });

  /*=============================================
    =            EXPORTAR EXCEL
    =============================================*/

  document.addEventListener("click", function (e) {
    if (e.target.closest("#btnExportarFavoritosExcel")) {
      const buscar = document.querySelector("#buscarFavorito")?.value || "";

      const categoria = document.querySelector("#filtroCategoria")?.value || "";

      const fechaInicio = document.querySelector("#fechaInicio")?.value || "";

      const fechaFin = document.querySelector("#fechaFin")?.value || "";

      const url =
        "exportar_favoritos_excel.php?" +
        "buscar=" +
        encodeURIComponent(buscar) +
        "&categoria=" +
        encodeURIComponent(categoria) +
        "&fechaInicio=" +
        encodeURIComponent(fechaInicio) +
        "&fechaFin=" +
        encodeURIComponent(fechaFin);

      window.open(url, "_blank");
    }
  });

  /*=============================================
    =            CARGAR KPIs
    =============================================*/

  async function cargarKPIs() {
    try {
      const response = await fetch("ajax/obtener_kpis_favoritos.php");

      const data = await response.json();

      if (!data.ok) return;

      document.querySelector("#kpiTotalFavoritos").textContent =
        data.kpis.totalFavoritos;

      document.querySelector("#kpiClientes").textContent = data.kpis.clientes;

      document.querySelector("#kpiProductos").textContent = data.kpis.productos;
      document.querySelector("#kpiTopCliente").textContent =
        data.kpis.topCliente;

      document.querySelector("#kpiTopMes").textContent = data.kpis.topMes;

      document.querySelector("#kpiConversion").textContent =
        data.kpis.conversion + "%";

      document.querySelector("#kpiValorPotencial").textContent =
        "S/ " + data.kpis.valorPotencial;
      document.querySelector("#kpiTopProducto").textContent =
        data.kpis.topProducto;

      crearGraficoProductos(
        data.graficoProductos.labels,
        data.graficoProductos.data,
      );

      crearGraficoCategorias(
        data.graficoCategorias.labels,
        data.graficoCategorias.data,
      );
    } catch (error) {
      console.error(error);
    }
  }

  /*=============================================
    =            CARGAR FAVORITOS
    =============================================*/

  async function cargarFavoritos() {
    try {
      const formData = new FormData();

      formData.append("pagina", paginaActual);

      formData.append(
        "buscar",
        document.querySelector("#buscarFavorito")?.value || "",
      );

      formData.append(
        "categoria",
        document.querySelector("#filtroCategoria")?.value || "",
      );

      formData.append(
        "fechaInicio",
        document.querySelector("#fechaInicio")?.value || "",
      );

      formData.append(
        "fechaFin",
        document.querySelector("#fechaFin")?.value || "",
      );

      const response = await fetch("ajax/obtener_favoritos.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (!data.ok) return;

      document.querySelector("#tablaFavoritos").innerHTML = data.tabla;

      document.querySelector("#paginacionFavoritos").innerHTML =
        data.paginacion;
    } catch (error) {
      console.error(error);
    }
  }

  /*=============================================
    =            CARGAR CATEGORIAS
    =============================================*/

  async function cargarCategorias() {
    try {
      const response = await fetch("ajax/obtener_categorias_favoritos.php");

      if (!response.ok) return;

      const categorias = await response.json();

      const select = document.querySelector("#filtroCategoria");

      if (!select) return;

      categorias.forEach((cat) => {
        select.innerHTML += `
                    <option value="${cat.id}">
                        ${cat.nombre}
                    </option>
                `;
      });
    } catch (error) {
      console.error(error);
    }
  }

  /*=============================================
    =            GRAFICO PRODUCTOS
    =============================================*/

  function crearGraficoProductos(labels, data) {
    const canvas = document.getElementById("graficoFavoritosProductos");

    if (!canvas) return;

    if (graficoProductos) {
      graficoProductos.destroy();
    }

    graficoProductos = new Chart(canvas, {
      type: "bar",

      data: {
        labels: labels,

        datasets: [
          {
            label: "Favoritos",

            data: data,

            borderWidth: 1,
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

  /*=============================================
    =            GRAFICO CATEGORIAS
    =============================================*/

  function crearGraficoCategorias(labels, data) {
    const canvas = document.getElementById("graficoCategoriasFavoritas");

    if (!canvas) return;

    if (graficoCategorias) {
      graficoCategorias.destroy();
    }

    graficoCategorias = new Chart(canvas, {
      type: "doughnut",

      data: {
        labels: labels,

        datasets: [
          {
            data: data,
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
=            RESTABLECER FILTROS
=============================================*/

  document.addEventListener("click", function (e) {
    if (!e.target.closest("#btnLimpiarFiltrosFavoritos")) return;

    const buscar = document.querySelector("#buscarFavorito");
    const categoria = document.querySelector("#filtroCategoria");
    const fechaInicio = document.querySelector("#fechaInicio");
    const fechaFin = document.querySelector("#fechaFin");

    if (buscar) buscar.value = "";

    if (categoria) categoria.value = "";

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

    paginaActual = 1;

    cargarFavoritos();

    cargarKPIs();

    cargarMapaCalor();
  });
});
/*=============================================
=            MAPA DE CALOR
=============================================*/

async function cargarMapaCalor() {
  try {
    const response = await fetch("ajax/obtener_mapa_calor_favoritos.php");

    const data = await response.json();

    if (!data.ok) return;

    document.querySelector("#tablaMapaCalor").innerHTML = data.tabla;
  } catch (error) {
    console.error(error);
  }
}
