//======================================================
// CoDevPro Technology
// js/filtro_dashboard.js
//======================================================

document.addEventListener("DOMContentLoaded", () => {
  const btnFiltrar = document.getElementById("btnFiltrarDashboard");

  if (btnFiltrar) {
    btnFiltrar.addEventListener("click", guardarFiltro);
  }

  document.querySelectorAll(".btnFiltroRapido").forEach((btn) => {
    btn.addEventListener("click", function () {
      document.getElementById("fecha_inicio").value = this.dataset.inicio;

      document.getElementById("fecha_fin").value = this.dataset.fin;

      guardarFiltro();
    });
  });
});

//======================================================
// GUARDAR FILTRO
//======================================================

function guardarFiltro() {
  const fechaInicio = document.getElementById("fecha_inicio").value;

  const fechaFin = document.getElementById("fecha_fin").value;

  const datos = new FormData();

  datos.append("fecha_inicio", fechaInicio);
  datos.append("fecha_fin", fechaFin);

  fetch("ajax/guardar_filtro_dashboard.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())

    .then((respuesta) => {
      if (respuesta.estado) {
        location.reload();
      }
    })

    .catch((error) => {
      console.error(error);
    });
}
