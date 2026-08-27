//======================================================
// CoDevPro Technology
// js/adm_testimonios.js
//======================================================

let paginaActual = 1;
let timeoutBusqueda = null;

/*======================================================
=            INICIALIZAR
======================================================*/

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
  cargarTestimonios();
  cargarGraficos();
});
let graficoEstrellas = null;
let graficoMes = null;
let graficoProductos = null;

async function cargarGraficos() {
  try {
    const response = await fetch("ajax/obtener_graficos_testimonios.php", {
      cache: "no-cache",
    });

    const data = await response.json();

    if (!data.ok) {
      console.error(data);
      return;
    }

    dibujarGraficoEstrellas(data.estrellas);

    dibujarGraficoMeses(data.meses, data.testimonios_mes);

    dibujarGraficoProductos(data.productos, data.promedios);
  } catch (error) {
    console.error(error);
  }
}
function dibujarGraficoEstrellas(datos) {
  const ctx = document.getElementById("graficoEstrellas");

  if (!ctx) return;

  if (graficoEstrellas) {
    graficoEstrellas.destroy();
  }

  graficoEstrellas = new Chart(ctx, {
    type: "doughnut",

    data: {
      labels: ["1 ★", "2 ★", "3 ★", "4 ★", "5 ★"],

      datasets: [
        {
          data: datos,
        },
      ],
    },

    options: {
      responsive: true,
    },
  });
}
function dibujarGraficoMeses(labels, datos) {
  const ctx = document.getElementById("graficoTestimoniosMes");

  if (!ctx) return;

  if (graficoMes) {
    graficoMes.destroy();
  }

  graficoMes = new Chart(ctx, {
    type: "line",

    data: {
      labels,

      datasets: [
        {
          label: "Testimonios",

          data: datos,

          tension: 0.3,

          fill: true,
        },
      ],
    },

    options: {
      responsive: true,
    },
  });
}
function dibujarGraficoProductos(labels, datos) {
  const ctx = document.getElementById("graficoProductosValorados");

  if (!ctx) return;

  if (graficoProductos) {
    graficoProductos.destroy();
  }

  graficoProductos = new Chart(ctx, {
    type: "bar",

    data: {
      labels,

      datasets: [
        {
          label: "Promedio",

          data: datos,
        },
      ],
    },

    options: {
      responsive: true,

      scales: {
        y: {
          beginAtZero: true,

          max: 5,
        },
      },
    },
  });
}
/*======================================================
=            FILTROS AUTOMÁTICOS
======================================================*/

document.addEventListener("input", (e) => {
  if (
    e.target.id === "buscarTestimonio" ||
    e.target.id === "fechaInicio" ||
    e.target.id === "fechaFin"
  ) {
    clearTimeout(timeoutBusqueda);

    timeoutBusqueda = setTimeout(() => {
      paginaActual = 1;
      cargarTestimonios();
    }, 300);
  }
});

document.addEventListener("change", (e) => {
  if (e.target.id === "filtroEstado" || e.target.id === "filtroCalificacion") {
    paginaActual = 1;
    cargarTestimonios();
  }
});
document.addEventListener("change", (e) => {
  if (
    e.target.id === "filtroEstado" ||
    e.target.id === "filtroCalificacion" ||
    e.target.id === "fechaInicio" ||
    e.target.id === "fechaFin"
  ) {
    paginaActual = 1;

    cargarTestimonios();
  }
});
/*======================================================
=            RESTABLECER FILTROS
======================================================*/

document.addEventListener("click", function (e) {
  if (e.target.closest("#btnLimpiarFiltros")) {
    const txtBuscar = document.querySelector("#buscarTestimonio");
    const estado = document.querySelector("#filtroEstado");
    const calificacion = document.querySelector("#filtroCalificacion");

    if (txtBuscar) txtBuscar.value = "";

    if (estado) estado.value = "";

    if (calificacion) calificacion.value = "";

    // Limpiar Flatpickr correctamente
    const fechaInicio = document.querySelector("#fechaInicio");
    const fechaFin = document.querySelector("#fechaFin");

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

    cargarTestimonios();
  }
});
/*======================================================
=            CARGAR TESTIMONIOS
======================================================*/

async function cargarTestimonios() {
  try {
    const formData = new FormData();

    formData.append("pagina", paginaActual);

    formData.append(
      "buscar",
      document.querySelector("#buscarTestimonio")?.value || "",
    );

    formData.append(
      "estado",
      document.querySelector("#filtroEstado")?.value || "",
    );

    formData.append(
      "calificacion",
      document.querySelector("#filtroCalificacion")?.value || "",
    );

    formData.append(
      "fechaInicio",
      document.querySelector("#fechaInicio")?.value || "",
    );

    formData.append(
      "fechaFin",
      document.querySelector("#fechaFin")?.value || "",
    );

    const response = await fetch("ajax/obtener_testimonios.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (!data.ok) {
      console.error(data);
      return;
    }

    const tabla = document.querySelector("#tablaTestimonios");

    if (tabla) {
      tabla.innerHTML = data.tabla;
    }

    const contador = document.querySelector("#contadorTestimonios");

    if (contador) {
      contador.textContent = data.total + " registros";
    }

    if (document.querySelector("#paginacionTestimonios")) {
      document.querySelector("#paginacionTestimonios").innerHTML =
        data.paginacion || "";
    }
  } catch (error) {
    console.error("Error cargar testimonios:", error);
  }
}

/*======================================================
=            CARGAR KPIs
======================================================*/

async function cargarKPIs() {
  try {
    const response = await fetch("ajax/obtener_kpis_testimonios.php", {
      cache: "no-cache",
    });

    const texto = await response.text();

    let data;

    try {
      data = JSON.parse(texto);
    } catch (e) {
      console.error("Respuesta inválida:", texto);

      return;
    }

    console.log("KPIs:", data);

    if (!data.ok) {
      console.error("Error KPI:", data.error || data.mensaje);

      return;
    }

    actualizarTexto("#kpiTotalTestimonios", data.total);

    actualizarTexto("#kpiPromedio", data.promedio);

    actualizarTexto("#kpiPendientes", data.pendientes);

    actualizarTexto("#kpiRespondidos", data.respondidos);

    actualizarTexto("#kpiMejorProducto", data.mejor_producto);

    actualizarTexto("#kpiTopCliente", data.top_cliente);

    actualizarTexto("#kpiCincoEstrellas", data.cinco_estrellas);

    actualizarTexto("#kpiTasaRespuesta", data.tasa_respuesta + "%");

    actualizarTexto("#indicadorSentimiento", data.sentimiento + "%");

    if (data.ultimo && document.querySelector("#ultimoTestimonio")) {
      document.querySelector("#ultimoTestimonio").innerHTML = `
      
        <h6 class="fw-bold mb-1">
          ${data.ultimo.cliente ?? ""}
        </h6>

        <small class="text-muted">
          ${data.ultimo.producto ?? ""}
        </small>

        <p class="mt-2 mb-2">
          ${data.ultimo.comentario ?? ""}
        </p>

        <div class="text-warning">
          ${"⭐".repeat(parseInt(data.ultimo.calificacion || 0))}
        </div>

      `;
    }
  } catch (error) {
    console.error("Error cargar KPIs:", error);
  }
}

/*======================================================
=            VER TESTIMONIO
======================================================*/

document.addEventListener("click", async function (e) {
  const btn = e.target.closest(".btnVerTestimonio");

  if (!btn) return;

  try {
    const formData = new FormData();

    formData.append("id", btn.dataset.id);

    const response = await fetch("ajax/obtener_detalle_testimonio.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (!data.ok) return;

    const t = data.data;

    document.querySelector("#idTestimonio").value = t.id_testimonio;

    document.querySelector("#detalleCliente").innerHTML = t.cliente;

    document.querySelector("#detalleProducto").innerHTML = t.producto;

    document.querySelector("#detalleComentario").innerHTML = t.comentario;
    /*=========================================
=            ESTADO
=========================================*/

    let badgeEstado = "secondary";

    if (t.estado === "APROBADO") {
      badgeEstado = "success";
    }

    if (t.estado === "PENDIENTE") {
      badgeEstado = "warning";
    }

    if (t.estado === "RECHAZADO") {
      badgeEstado = "danger";
    }

    document.querySelector("#detalleEstado").innerHTML = `
    <span class="badge bg-${badgeEstado}">
        ${t.estado}
    </span>
`;

    /*=========================================
=            FECHA
=========================================*/

    document.querySelector("#detalleFecha").innerHTML = t.fecha || "--";

    /*=========================================
=            TICKET
=========================================*/

    document.querySelector("#detalleTicket").innerHTML = `
    <span class="badge bg-primary">
        #${t.id_ticket_ventas}
    </span>
`;

    document.querySelector("#respuestaTestimonio").value = t.respuesta || "";

    document.querySelector("#detalleCalificacion").innerHTML = "⭐".repeat(
      parseInt(t.calificacion),
    );
    const btnAprobar = document.querySelector("#btnAprobarTestimonio");

    const btnRechazar = document.querySelector("#btnRechazarTestimonio");

    if (t.estado === "APROBADO") {
      btnAprobar.disabled = true;
      btnRechazar.disabled = false;
    } else if (t.estado === "RECHAZADO") {
      btnAprobar.disabled = false;
      btnRechazar.disabled = true;
    } else {
      btnAprobar.disabled = false;
      btnRechazar.disabled = false;
    }
    document.querySelector("#detalleFechaRespuesta").innerHTML =
      t.fecha_respuesta
        ? `Respondido el ${t.fecha_respuesta}`
        : "Sin respuesta aún";
    const modal = new bootstrap.Modal(
      document.getElementById("modalTestimonio"),
    );

    modal.show();
  } catch (error) {
    console.error(error);
  }
});

/*======================================================
=            GUARDAR RESPUESTA
======================================================*/

document.addEventListener("click", async function (e) {
  if (e.target.id !== "btnGuardarRespuesta") return;

  try {
    const respuesta = document
      .querySelector("#respuestaTestimonio")
      .value.trim();

    if (respuesta === "") {
      alert("Ingrese una respuesta");
      return;
    }

    const formData = new FormData();

    formData.append("id", document.querySelector("#idTestimonio").value);

    formData.append("respuesta", respuesta);

    const response = await fetch("ajax/responder_testimonio.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (!data.ok) {
      alert(data.mensaje || "Error");
      return;
    }

    const modal = bootstrap.Modal.getInstance(
      document.getElementById("modalTestimonio"),
    );

    if (modal) {
      modal.hide();
    }

    cargarKPIs();
    cargarGraficos();
    cargarTestimonios();

    alert("Respuesta guardada correctamente");
  } catch (error) {
    console.error(error);
  }
});

/*======================================================
=            APROBAR
======================================================*/

document.addEventListener("click", function (e) {
  if (e.target.id === "btnAprobarTestimonio") {
    actualizarEstadoTestimonio(
      document.querySelector("#idTestimonio").value,
      "APROBADO",
    );
  }
});

/*======================================================
=            RECHAZAR
======================================================*/

document.addEventListener("click", function (e) {
  if (e.target.id === "btnRechazarTestimonio") {
    actualizarEstadoTestimonio(
      document.querySelector("#idTestimonio").value,
      "RECHAZADO",
    );
  }
});

/*======================================================
=            CAMBIAR ESTADO
======================================================*/

async function actualizarEstadoTestimonio(id, estado) {
  try {
    const mensaje =
      estado === "APROBADO"
        ? "¿Desea aprobar este testimonio?"
        : "¿Desea rechazar este testimonio?";

    if (!confirm(mensaje)) {
      return;
    }

    const formData = new FormData();

    formData.append("id", id);

    formData.append("estado", estado);

    const response = await fetch("ajax/cambiar_estado_testimonio.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (!data.ok) {
      alert("No se pudo actualizar");
      return;
    }

    const modal = bootstrap.Modal.getInstance(
      document.getElementById("modalTestimonio"),
    );

    if (modal) {
      modal.hide();
    }

    cargarKPIs();
    cargarGraficos();
    cargarTestimonios();

    alert(
      estado === "APROBADO" ? "Testimonio aprobado" : "Testimonio rechazado",
    );
  } catch (error) {
    console.error(error);
  }
}

/*======================================================
=            UTILIDADES
======================================================*/

function actualizarTexto(selector, valor) {
  const elemento = document.querySelector(selector);

  if (elemento) {
    elemento.textContent = valor ?? "";
  }
}
/*======================================================
=            PAGINACION
======================================================*/

document.addEventListener("click", function (e) {
  const btn = e.target.closest(".btnPaginaTestimonio");

  if (!btn) return;

  paginaActual = parseInt(btn.dataset.pagina);

  cargarTestimonios();
});
