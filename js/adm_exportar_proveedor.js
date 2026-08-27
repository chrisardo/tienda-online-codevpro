//=====================================================
// CoDevPro Technology
// Archivo: js/adm_exportar_proveedor.js
// Módulo: Lista de Proveedores
// Sistema: Inventa
//=====================================================

"use strict";

document.addEventListener("DOMContentLoaded", function () {
  //=================================================
  // ELEMENTOS
  //=================================================

  const checkboxes = document.querySelectorAll(".opcion-exportacion-proveedor");

  const categorias = document.querySelectorAll(
    ".categoria-exportacion-proveedor",
  );

  const contador = document.getElementById("contadorExportacionProveedor");

  const btnSeleccionarTodo = document.getElementById(
    "btnSeleccionarTodoExportacionProveedor",
  );

  const btnDeseleccionarTodo = document.getElementById(
    "btnDeseleccionarTodoExportacionProveedor",
  );

  const btnExportar = document.getElementById(
    "btnEjecutarExportacionProveedor",
  );

  //=================================================
  // ACTUALIZAR CONTADOR
  //=================================================

  function actualizarContador() {
    const seleccionados = document.querySelectorAll(
      ".opcion-exportacion-proveedor:checked",
    ).length;

    if (contador) {
      contador.textContent = seleccionados;
    }
  }

  //=================================================
  // SELECCIONAR TODO
  //=================================================

  if (btnSeleccionarTodo) {
    btnSeleccionarTodo.addEventListener("click", function () {
      checkboxes.forEach(function (checkbox) {
        checkbox.checked = true;
      });

      categorias.forEach(function (categoria) {
        categoria.checked = true;
      });

      actualizarContador();
    });
  }

  //=================================================
  // DESELECCIONAR TODO
  //=================================================

  if (btnDeseleccionarTodo) {
    btnDeseleccionarTodo.addEventListener("click", function () {
      checkboxes.forEach(function (checkbox) {
        checkbox.checked = false;
      });

      categorias.forEach(function (categoria) {
        categoria.checked = false;
      });

      actualizarContador();
    });
  }

  //=================================================
  // CAMBIO DE OPCIONES
  //=================================================

  checkboxes.forEach(function (checkbox) {
    checkbox.addEventListener("change", function () {
      actualizarContador();

      actualizarEstadoCategorias();
    });
  });

  //=================================================
  // CAMBIO DE CATEGORÍA
  //=================================================

  categorias.forEach(function (categoria) {
    categoria.addEventListener("change", function () {
      const card = categoria.closest(".card");

      if (!card) {
        return;
      }

      const opciones = card.querySelectorAll(".opcion-exportacion-proveedor");

      opciones.forEach(function (opcion) {
        opcion.checked = categoria.checked;
      });

      actualizarContador();

      actualizarEstadoCategorias();
    });
  });

  //=================================================
  // ACTUALIZAR ESTADO DE CATEGORÍAS
  //=================================================

  function actualizarEstadoCategorias() {
    categorias.forEach(function (categoria) {
      const card = categoria.closest(".card");

      if (!card) {
        return;
      }

      const opciones = card.querySelectorAll(".opcion-exportacion-proveedor");

      if (opciones.length === 0) {
        return;
      }

      const seleccionadas = Array.from(opciones).filter(function (opcion) {
        return opcion.checked;
      }).length;

      const todasSeleccionadas = seleccionadas === opciones.length;

      const ningunaSeleccionada = seleccionadas === 0;

      categoria.checked = todasSeleccionadas;

      categoria.indeterminate = !todasSeleccionadas && !ningunaSeleccionada;
    });
  }

  //=================================================
  // EXPORTAR
  //=================================================

  if (btnExportar) {
    btnExportar.addEventListener("click", function () {
      const seleccionados = [];

      document
        .querySelectorAll(".opcion-exportacion-proveedor:checked")
        .forEach(function (checkbox) {
          seleccionados.push(checkbox.value);
        });

      //=================================================
      // VALIDAR
      //=================================================

      if (seleccionados.length === 0) {
        Swal.fire({
          icon: "warning",

          title: "Sin datos seleccionados",

          text: "Selecciona al menos una opción para exportar.",
        });

        return;
      }

      //=================================================
      // MOSTRAR CARGANDO
      //=================================================

      Swal.fire({
        title: "Generando Excel...",

        text: "Estamos preparando la información de los proveedores.",

        allowOutsideClick: false,

        allowEscapeKey: false,

        didOpen: function () {
          Swal.showLoading();
        },
      });

      //=================================================
      // CREAR FORMULARIO
      //=================================================

      const form = document.createElement("form");

      form.method = "POST";

      form.action = "ajax/exportar_proveedores_excel.php";

      form.target = "_blank";

      //=================================================
      // AGREGAR OPCIONES
      //=================================================

      seleccionados.forEach(function (opcion) {
        const input = document.createElement("input");

        input.type = "hidden";

        input.name = "exportar[]";

        input.value = opcion;

        form.appendChild(input);
      });

      //=================================================
      // AGREGAR FORMULARIO AL DOM
      //=================================================

      document.body.appendChild(form);

      //=================================================
      // ENVIAR
      //=================================================

      form.submit();

      //=================================================
      // ELIMINAR FORMULARIO
      //=================================================

      form.remove();

      //=================================================
      // CERRAR SWEETALERT
      //=================================================

      setTimeout(function () {
        Swal.close();
      }, 1500);
    });
  }

  //=================================================
  // ESTADO INICIAL
  //=================================================

  actualizarContador();

  actualizarEstadoCategorias();
});
