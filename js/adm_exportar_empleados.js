//=====================================================
// Archivo: js/adm_lista_empleados.js
// EXPORTACIÓN DE DATOS DE EMPLEADOS
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(".opcion-exportacion");

  const categorias = document.querySelectorAll(".categoria-exportacion");

  const contador = document.getElementById("contadorExportacion");

  const btnSeleccionarTodo = document.getElementById(
    "btnSeleccionarTodoExportacion",
  );

  const btnDeseleccionarTodo = document.getElementById(
    "btnDeseleccionarTodoExportacion",
  );

  const btnExportar = document.getElementById("btnEjecutarExportacionEmpleado");

  //=================================================
  // ACTUALIZAR CONTADOR
  //=================================================

  function actualizarContador() {
    const seleccionados = document.querySelectorAll(
      ".opcion-exportacion:checked",
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
    checkbox.addEventListener("change", actualizarContador);
  });

  categorias.forEach(function (categoria) {
    categoria.addEventListener("change", function () {
      const card = categoria.closest(".card");

      if (!card) {
        return;
      }

      const opciones = card.querySelectorAll(".opcion-exportacion");

      opciones.forEach(function (opcion) {
        opcion.checked = categoria.checked;
      });

      actualizarContador();
    });
  });

  //=================================================
  // EXPORTAR
  //=================================================

  if (btnExportar) {
    btnExportar.addEventListener("click", function () {
      const seleccionados = [];

      document
        .querySelectorAll(".opcion-exportacion:checked")
        .forEach(function (checkbox) {
          seleccionados.push(checkbox.value);
        });

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
        text: "Estamos preparando la información.",
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      //=================================================
      // CREAR FORMULARIO
      //=================================================

      const form = document.createElement("form");

      form.method = "POST";

      form.action = "ajax/exportar_empleados_excel.php";

      form.target = "_blank";

      seleccionados.forEach(function (opcion) {
        const input = document.createElement("input");

        input.type = "hidden";

        input.name = "exportar[]";

        input.value = opcion;

        form.appendChild(input);
      });

      document.body.appendChild(form);

      form.submit();

      form.remove();

      //=================================================
      // CERRAR SWEETALERT
      //=================================================

      setTimeout(function () {
        Swal.close();
      }, 1500);
    });
  }

  actualizarContador();
});

