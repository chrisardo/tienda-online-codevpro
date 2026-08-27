//======================================================
// CoDevPro Technology
// js/categorias.js
//======================================================

document.addEventListener("DOMContentLoaded", () => {
  cargarCategorias();
  const filtroEstado = document.getElementById("filtroEstado");

  if (filtroEstado) {
    filtroEstado.addEventListener("change", () => {
      cargarCategorias(1);
    });
  }
  /*=========================================
    =            BUSCADOR
    =========================================*/

  const txtBuscar = document.getElementById("buscarCategoria");

  if (txtBuscar) {
    let timeout;

    txtBuscar.addEventListener("keyup", () => {
      clearTimeout(timeout);

      timeout = setTimeout(() => {
        cargarCategorias(1);
      }, 300);
    });
  }

  /*=========================================
    =            NUEVA CATEGORIA
    =========================================*/

  const formNuevaCategoria = document.getElementById("formNuevaCategoria");

  if (formNuevaCategoria) {
    formNuevaCategoria.addEventListener("submit", registrarCategoria);
  }
});

/*======================================================
=            REGISTRAR CATEGORIA
======================================================*/

function registrarCategoria(e) {
  e.preventDefault();

  const form = document.getElementById("formNuevaCategoria");

  if (!form.checkValidity()) {
    form.classList.add("was-validated");

    return;
  }

  const datos = new FormData(form);

  const btn = document.getElementById("btnGuardarCategoria");

  btn.disabled = true;

  btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Guardando...
    `;

  fetch("ajax/registrar_categoria.php", {
    method: "POST",

    body: datos,
  })
    .then((res) => res.json())
    .then((respuesta) => {
      btn.disabled = false;

      btn.innerHTML = `
                <i class="bi bi-save me-2"></i>
                Guardar Categoría
            `;

      if (!respuesta.estado) {
        Swal.fire({
          icon: "error",

          title: "Error",

          text: respuesta.mensaje,
        });

        return;
      }

      Swal.fire({
        icon: "success",

        title: "Correcto",

        text: respuesta.mensaje,

        timer: 1800,

        showConfirmButton: false,
      });

      const modal = bootstrap.Modal.getInstance(
        document.getElementById("modalNuevaCategoria"),
      );

      modal.hide();

      form.reset();

      form.classList.remove("was-validated");

      const preview = document.getElementById("previewCategoria");

      if (preview) {
        preview.src = "img/logo.png";
      }

      cargarCategorias();
    })
    .catch((error) => {
      btn.disabled = false;

      btn.innerHTML = `
                <i class="bi bi-save me-2"></i>
                Guardar Categoría
            `;

      Swal.fire({
        icon: "error",

        title: "Error",

        text: "No se pudo registrar.",
      });

      console.error(error);
    });
}

/*======================================================
=            CARGAR TABLA
======================================================*/

function cargarCategorias(pagina = 1) {
  const buscar = document.getElementById("buscarCategoria")?.value || "";

  const filtroEstado = document.getElementById("filtroEstado")?.value || "";

  const formData = new FormData();

  formData.append("buscar", buscar);
  formData.append("estado", filtroEstado);
  formData.append("pagina", pagina);

  fetch("ajax/listar_categorias.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.estado) return;

      const tabla = document.getElementById("contenedorTablaCategorias");

      if (tabla) {
        tabla.innerHTML = data.tabla;
      }

      const paginacion = document.getElementById(
        "contenedorPaginacionCategorias",
      );

      if (paginacion) {
        paginacion.innerHTML = data.paginacion;
      }

      const totalRegistros = document.getElementById("totalRegistros");

      if (totalRegistros) {
        totalRegistros.textContent = `${data.totalRegistros ?? 0} registros`;
      }

      actualizarKPIs(data);
    })
    .catch(console.error);
}

/*======================================================
=            KPI
======================================================*/
function actualizarKPIs(data) {
  const totalCategorias = document.getElementById("kpiTotalCategorias");

  const categoriasUso = document.getElementById("kpiCategoriasUso");

  const productos = document.getElementById("kpiProductos");

  const categoriaTop = document.getElementById("kpiCategoriaTop");

  if (totalCategorias) {
    totalCategorias.textContent = data.totalCategorias ?? 0;
  }

  if (categoriasUso) {
    categoriasUso.textContent = data.categoriasUso ?? 0;
  }

  if (productos) {
    productos.textContent = data.totalProductos ?? 0;
  }

  if (categoriaTop) {
    categoriaTop.textContent = data.categoriaTop ?? "-";
  }
}

/*======================================================
=            PAGINACION AJAX
======================================================*/

document.addEventListener("click", (e) => {
  const boton = e.target.closest(".btn-pagina");

  if (!boton) return;

  e.preventDefault();

  const pagina = boton.dataset.pagina;

  cargarCategorias(pagina);
});

/*======================================================
=            ELIMINAR CATEGORIA
======================================================*/

function eliminarCategoria(idCategoria) {
  Swal.fire({
    title: "¿Eliminar categoría?",

    text: "La categoría será enviada a papelera.",

    icon: "warning",

    showCancelButton: true,

    confirmButtonText: "Sí, eliminar",

    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (!result.isConfirmed) return;

    const datos = new FormData();

    datos.append("idCategoria", idCategoria);

    fetch("ajax/eliminar_categoria.php", {
      method: "POST",
      body: datos,
    })
      .then((res) => res.json())
      .then((respuesta) => {
        if (!respuesta.estado) {
          Swal.fire({
            icon: "error",

            title: "Error",

            text: respuesta.mensaje,
          });

          return;
        }

        Swal.fire({
          icon: "success",

          title: "Correcto",

          text: respuesta.mensaje,

          timer: 1500,

          showConfirmButton: false,
        });

        cargarCategorias();
      });
  });
}
/*=========================================
=            VER CATEGORIA
=========================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-ver-categoria");

  if (!boton) return;

  const idCategoria = boton.dataset.id;

  const modal = new bootstrap.Modal(
    document.getElementById("modalVerCategoria"),
  );

  modal.show();

  document.getElementById("contenidoDetalleCategoria").innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>
    `;

  const datos = new FormData();

  datos.append("idCategoria", idCategoria);

  fetch("ajax/obtener_categoria.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())
    .then((res) => {
      if (!res.estado) return;

      document.getElementById("contenidoDetalleCategoria").innerHTML = res.html;
    });
});
/*=========================================
=            ABRIR EDITAR
=========================================*/

document.addEventListener("click", function (e) {
  const btn = e.target.closest(".btn-editar-categoria");

  if (!btn) return;

  const idCategoria = btn.dataset.id;

  const modal = new bootstrap.Modal(
    document.getElementById("modalEditarCategoria"),
  );

  modal.show();

  const datos = new FormData();

  datos.append("idCategoria", idCategoria);

  fetch("ajax/obtener_categoria_editar.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())
    .then((res) => {
      if (!res.estado) return;

      document.getElementById("contenidoEditarCategoria").innerHTML = res.html;
    });
});
/*=========================================
=            PREVIEW EDITAR
=========================================*/

document.addEventListener("change", function (e) {
  if (e.target.id !== "editarImagenCategoria") return;

  const archivo = e.target.files[0];

  if (!archivo) return;

  const lector = new FileReader();

  lector.onload = function (event) {
    document.getElementById("previewEditarCategoria").src = event.target.result;
  };

  lector.readAsDataURL(archivo);
});
/*=========================================
=            ACTUALIZAR
=========================================*/

document.addEventListener("submit", function (e) {
  if (e.target.id !== "formEditarCategoria") return;

  e.preventDefault();

  const form = document.getElementById("formEditarCategoria");

  const datos = new FormData(form);

  fetch("ajax/actualizar_categoria.php", {
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
        document.getElementById("modalEditarCategoria"),
      ).hide();

      cargarCategorias();
    });
});
/*=========================================
= ELIMINAR CATEGORIA
=========================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btn-eliminar-categoria");

  if (!boton) return;

  const idCategoria = boton.dataset.id;

  Swal.fire({
    title: "¿Eliminar categoría?",

    text: "Esta acción no podrá deshacerse.",

    icon: "warning",

    showCancelButton: true,

    confirmButtonColor: "#dc3545",

    cancelButtonColor: "#6c757d",

    confirmButtonText: "Sí, eliminar",

    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (!result.isConfirmed) return;

    const datos = new FormData();

    datos.append("idCategoria", idCategoria);

    fetch("ajax/eliminar_categoria.php", {
      method: "POST",
      body: datos,
    })
      .then((res) => res.json())
      .then((res) => {
        if (!res.estado) {
          Swal.fire({
            icon: "error",

            title: "No se pudo eliminar",

            text: res.mensaje,
          });

          return;
        }

        Swal.fire({
          icon: "success",

          title: "Categoría eliminada",

          text: res.mensaje,

          timer: 1800,

          showConfirmButton: false,
        });

        cargarCategorias();

        cargarKPIsCategorias();
      });
  });
});
/*=========================================
= EXPORTAR EXCEL
=========================================*/

document.addEventListener("click", function (e) {
  if (e.target.closest("#btnExportarCategorias")) {
    window.open("ajax/exportar_categorias_excel.php", "_blank");
  }
});
