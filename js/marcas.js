/* Toda esta pate es de js/marcas.js */
/*=========================================
=            VER MARCA
=========================================*/

/*=========================================
= ABRIR EDITAR MARCA
=========================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-editar-marca");

  if (!boton) return;

  const idMarca = boton.dataset.id;

  const modal = new bootstrap.Modal(
    document.getElementById("modalEditarMarca"),
  );

  modal.show();

  const datos = new FormData();

  datos.append("idMarca", idMarca);

  fetch("ajax/obtener_marca_editar.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())
    .then((res) => {
      if (!res.estado) return;

      document.getElementById("contenidoEditarMarca").innerHTML = res.html;
    });
});
/*=========================================
= PREVIEW EDITAR
=========================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "editarImagenMarca") return;

  const archivo = e.target.files[0];

  if (!archivo) return;

  const lector = new FileReader();

  lector.onload = function (evento) {
    document.getElementById("previewEditarMarca").src = evento.target.result;
  };

  lector.readAsDataURL(archivo);
});
/*=========================================
= ACTUALIZAR MARCA
=========================================*/

document.addEventListener("submit", function (e) {
  if (e.target.id !== "formEditarMarca") return;

  e.preventDefault();

  const form = e.target;

  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    return;
  }

  const datos = new FormData(form);

  fetch("ajax/actualizar_marca.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())
    .then((res) => {
      if (!res.estado) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: res.mensaje,
        });

        return;
      }

      Swal.fire({
        icon: "success",
        title: "Correcto",
        text: res.mensaje,
        timer: 1800,
        showConfirmButton: false,
      });

      bootstrap.Modal.getInstance(
        document.getElementById("modalEditarMarca"),
      ).hide();

      cargarMarcas();
      cargarKPIsMarcas();
    });
});
document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-ver-marca");

  if (!boton) return;

  const idMarca = boton.dataset.id;

  const modal = new bootstrap.Modal(document.getElementById("modalVerMarca"));

  modal.show();

  const datos = new FormData();

  datos.append("idMarca", idMarca);

  fetch("ajax/obtener_marca.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())
    .then((res) => {
      if (!res.estado) return;

      document.getElementById("contenidoDetalleMarca").innerHTML = res.html;
    });
});
document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-eliminar-marca");

  if (!boton) return;

  const idMarca = boton.dataset.id;

  Swal.fire({
    title: "¿Eliminar marca?",
    text: "La marca será enviada a papelera.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Eliminar",
  }).then((result) => {
    if (!result.isConfirmed) return;

    const datos = new FormData();

    datos.append("idMarca", idMarca);

    fetch("ajax/eliminar_marca.php", {
      method: "POST",
      body: datos,
    })
      .then((res) => res.json())
      .then((res) => {
        if (!res.estado) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: res.mensaje,
          });

          return;
        }

        Swal.fire({
          icon: "success",
          title: "Correcto",
          text: res.mensaje,
          timer: 1500,
          showConfirmButton: false,
        });

        cargarMarcas();
        cargarKPIsMarcas();
      });
  });
});
function cargarKPIsMarcas() {
  fetch("ajax/kpi_marcas.php")
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("kpiTotalMarcas").textContent =
        data.totalMarcas ?? 0;

      document.getElementById("kpiProductosMarca").textContent =
        data.productosMarca ?? 0;
    });
}
function cargarMarcas(pagina = 1) {
  const buscar = document.getElementById("buscarMarca")?.value || "";

  const filtro = document.getElementById("filtroEstado")?.value || "";

  const ordenar =
    document.getElementById("ordenarMarca")?.value || "nombre_asc";

  const datos = new FormData();

  datos.append("buscar", buscar);
  datos.append("filtro", filtro);
  datos.append("ordenar", ordenar);
  datos.append("pagina", pagina);

  fetch("ajax/listar_marcas.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.estado) return;

      document.getElementById("contenedorTablaMarcas").innerHTML = data.tabla;

      document.getElementById("contenedorPaginacionMarcas").innerHTML =
        data.paginacion;

      document.getElementById("totalRegistrosMarcas").textContent =
        data.totalRegistros + " registros";

      document.getElementById("kpiTotalMarcas").textContent = data.totalMarcas;

      document.getElementById("kpiMarcasUso").textContent = data.marcasUso;

      document.getElementById("kpiProductosMarca").textContent =
        data.totalProductos;

      document.getElementById("kpiMarcaTop").textContent = data.marcaTop;
    });
}
document.getElementById("buscarMarca")?.addEventListener("input", function () {
  clearTimeout(window.timerMarca);

  window.timerMarca = setTimeout(() => {
    cargarMarcas(1);
  }, 300);
});

document
  .getElementById("filtroEstado")
  ?.addEventListener("change", function () {
    cargarMarcas(1);
  });

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-pagina");

  if (!boton) return;

  e.preventDefault();

  cargarMarcas(boton.dataset.pagina);
});
function exportarExcelMarcas() {
  Swal.fire({
    title: "Generando Excel...",
    text: "Espere un momento",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  setTimeout(() => {
    window.open("ajax/exportar_marcas_excel.php", "_blank");

    Swal.close();
  }, 500);
}
document.addEventListener("change", function (e) {
  if (e.target.id !== "imagenMarca") return;

  const archivo = e.target.files[0];

  if (!archivo) return;

  const lector = new FileReader();

  lector.onload = function (evento) {
    document.getElementById("previewMarca").src = evento.target.result;
  };

  lector.readAsDataURL(archivo);
});
document.addEventListener("submit", function (e) {
  if (e.target.id !== "formNuevaMarca") return;

  e.preventDefault();

  const form = e.target;

  if (!form.checkValidity()) {
    form.classList.add("was-validated");

    return;
  }

  const datos = new FormData(form);

  fetch("ajax/registrar_marca.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())
    .then((res) => {
      if (!res.estado) {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: res.mensaje,
        });

        return;
      }

      Swal.fire({
        icon: "success",
        title: "Correcto",
        text: res.mensaje,
        timer: 1800,
        showConfirmButton: false,
      });

      form.reset();

      document.getElementById("previewMarca").src = "assets/img/sin_imagen.png";

      bootstrap.Modal.getInstance(
        document.getElementById("modalNuevaMarca"),
      ).hide();

      cargarMarcas();
    });
});
document
  .getElementById("ordenarMarca")
  ?.addEventListener("change", function () {
    cargarMarcas(1);
  });
document.addEventListener("DOMContentLoaded", function () {
  cargarMarcas(1);
});
