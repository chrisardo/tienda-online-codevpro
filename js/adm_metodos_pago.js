//==========================================================
// CoDevPro Technology
// js/adm_metodos_pago.js
//==========================================================

document.addEventListener("DOMContentLoaded", () => {
  inicializarEventos();
  inicializarFiltros();
  cargarKPI();
  cargarMetodosPago();
  cargarGraficosMetodos();
});

/*==========================================================
=            EVENTOS
==========================================================*/

function inicializarEventos() {
  /*======================================================
=            REGISTRAR MÉTODO
======================================================*/

  const formularioRegistrar = document.getElementById("formRegistrarMetodo");

  if (formularioRegistrar) {
    formularioRegistrar.addEventListener("submit", registrarMetodoPago);
  }

  /*======================================================
=            EDITAR MÉTODO
======================================================*/

  inicializarEventosEditarMetodo();

  /*======================================================
=            ELIMINAR MÉTODO
======================================================*/

  inicializarEventosEliminarMetodo();

  inicializarEventoConfirmarEliminarMetodo();

  /*======================================================
=            FORMULARIO EDITAR
======================================================*/

  const formularioEditar = document.getElementById("formEditarMetodo");

  if (formularioEditar) {
    formularioEditar.addEventListener("submit", actualizarMetodoPago);
  }
}

/*==========================================================
=            REGISTRAR MÉTODO DE PAGO
==========================================================*/

function registrarMetodoPago(e) {
  e.preventDefault();

  const nombre = document.getElementById("nombreMetodo").value.trim();

  if (nombre === "") {
    mostrarMensaje("Debe ingresar el nombre del método de pago.", "warning");

    document.getElementById("nombreMetodo").focus();

    return;
  }

  const btnGuardar = document.getElementById("btnGuardarMetodo");

  btnGuardar.disabled = true;

  btnGuardar.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Guardando...
    `;

  fetch("ajax/registrar_metodo_pago.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/json",
    },

    body: JSON.stringify({
      nombre: nombre,
    }),
  })
    .then((response) => response.json())

    .then((res) => {
      mostrarMensaje(res.mensaje, res.estado ? "success" : "danger");

      if (res.estado) {
        document.getElementById("formRegistrarMetodo").reset();

        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modalNuevoMetodo"),
        );

        if (modal) {
          modal.hide();
        }
        /*==========================
            ACTUALIZAR TABLA
            ==========================*/

        if (typeof cargarMetodosPago === "function") {
          cargarMetodosPago();
        }

        /*==========================
            ACTUALIZAR KPI
            ==========================*/

        if (typeof cargarKPI === "function") {
          cargarKPI();
        }
        if (typeof cargarGraficosMetodos === "function") {
          cargarGraficosMetodos();
        }
      }
    })

    .catch((error) => {
      console.error(error);

      mostrarMensaje("Ocurrió un error inesperado.", "danger");
    })

    .finally(() => {
      btnGuardar.disabled = false;

      btnGuardar.innerHTML = `
            <i class="bi bi-check-circle me-1"></i>
            Guardar Método
        `;
    });
}

/*==========================================================
=            MENSAJES
==========================================================*/

function mostrarMensaje(texto, tipo = "success") {
  let contenedor = document.getElementById("contenedorMensajes");

  if (!contenedor) {
    contenedor = document.createElement("div");

    contenedor.id = "contenedorMensajes";

    contenedor.style.position = "fixed";

    contenedor.style.top = "20px";

    contenedor.style.right = "20px";

    contenedor.style.zIndex = "99999";

    document.body.appendChild(contenedor);
  }

  const alerta = document.createElement("div");

  alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow`;

  alerta.style.minWidth = "330px";

  alerta.innerHTML = `
        ${texto}
        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>
    `;

  contenedor.appendChild(alerta);

  setTimeout(() => {
    alerta.classList.remove("show");

    alerta.classList.add("hide");

    setTimeout(() => {
      alerta.remove();
    }, 400);
  }, 3500);
}
/*==========================================================
=            CARGAR KPI
==========================================================*/

function cargarKPI() {
  fetch("ajax/adm_obtener_kpi_metodo.php")
    .then((response) => response.json())

    .then((res) => {
      if (!res.estado) {
        mostrarMensaje(
          res.mensaje || "No fue posible cargar los KPI.",
          "danger",
        );

        return;
      }

      const kpi = res.kpi;

      document.getElementById("kpiTotalMetodos").textContent = Number(
        kpi.total_metodos,
      ).toLocaleString();
      document.getElementById("kpiUtilizados").textContent = Number(
        kpi.utilizados,
      ).toLocaleString();

      document.getElementById("kpiVentas").textContent = Number(
        kpi.total_ventas,
      ).toLocaleString();

      document.getElementById("kpiMonto").textContent =
        "S/ " +
        Number(kpi.total_monto).toLocaleString("es-PE", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        });
    })

    .catch((error) => {
      console.error(error);

      mostrarMensaje("Error al cargar los KPI.", "danger");
    });
}

/*==========================================================
=            VARIABLES GLOBALES
==========================================================*/

let paginaActualMetodos = 1;

/*==========================================================
=            CARGAR MÉTODOS DE PAGO
==========================================================*/

function cargarMetodosPago(pagina = 1) {
  paginaActualMetodos = parseInt(pagina) || 1;

  /*======================================================
    =            OBTENER FILTROS
    ======================================================*/

  const buscar = document.getElementById("buscarMetodo")?.value.trim() || "";

  const fecha = document.getElementById("rangoFecha")?.value.trim() || "";

  const orden = document.getElementById("ordenarPor")?.value || "nombre_asc";

  const estado = document.getElementById("filtroEstado")?.value || "";

  /*
   * La tabla siempre mostrará 5 registros.
   */
  const registros = 5;

  /*======================================================
    =            FORM DATA
    ======================================================*/

  const formData = new FormData();

  formData.append("pagina", paginaActualMetodos);
  formData.append("buscar", buscar);
  formData.append("fecha", fecha);
  formData.append("orden", orden);
  formData.append("registros", registros);
  formData.append("estado", estado);

  /*======================================================
    =            TABLA
    ======================================================*/

  const tbody = document.getElementById("tbodyMetodosPago");

  if (!tbody) {
    console.error("No existe #tbodyMetodosPago");
    return;
  }

  tbody.innerHTML = `
        <tr>
            <td colspan="10" class="text-center py-5">

                <div
                    class="spinner-border text-primary"
                    role="status">
                </div>

                <div class="mt-2 text-muted">
                    Cargando métodos de pago...
                </div>

            </td>
        </tr>
    `;

  /*======================================================
    =            AJAX
    ======================================================*/

  fetch("ajax/adm_lista_metodos.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }

      return response.json();
    })

    .then((res) => {
      console.log("Respuesta adm_lista_metodos.php:", res);

      /*==================================================
        =            VALIDAR
        ==================================================*/

      if (!res.estado) {
        mostrarMensaje(
          res.mensaje || "No fue posible cargar los métodos de pago.",
          "danger",
        );

        return;
      }

      /*==================================================
        =            TABLA
        ==================================================*/

      tbody.innerHTML = res.tbody || "";

      /*==================================================
        =            PAGINACIÓN
        ==================================================*/

      const paginacion = document.getElementById("paginacionMetodosPago");

      if (paginacion) {
        paginacion.innerHTML = res.paginacion || "";
      }

      /*==================================================
        =            TEXTO
        ==================================================*/

      const texto = document.getElementById("textoRegistros");

      if (texto) {
        texto.textContent = res.texto || "Mostrando 0 a 0 de 0 registros";
      }

      /*==================================================
        =            ACTUALIZAR PÁGINA
        ==================================================*/

      paginaActualMetodos = parseInt(res.pagina) || paginaActualMetodos;

      /*==================================================
        =            REINICIALIZAR PAGINACIÓN
        ==================================================*/

      inicializarPaginacionMetodos();

      /*==================================================
        =            CHECKBOX
        ==================================================*/

      inicializarCheckTodos();

      /*==================================================
        =            CONTADOR
        ==================================================*/

      actualizarContadorSeleccionados();
    })

    .catch((error) => {
      console.error("Error en cargarMetodosPago():", error);

      tbody.innerHTML = `
            <tr>
                <td
                    colspan="10"
                    class="text-center py-5 text-danger">

                    <i
                        class="bi bi-exclamation-triangle-fill fs-1">
                    </i>

                    <h5 class="mt-3">
                        Error al cargar los registros
                    </h5>

                    <small>
                        Revisa la consola del navegador.
                    </small>

                </td>
            </tr>
        `;
    });
}

/*==========================================================
=            PAGINACIÓN
==========================================================*/

function inicializarPaginacionMetodos() {
  document.querySelectorAll(".paginaMetodo").forEach((boton) => {
    boton.addEventListener("click", function (e) {
      e.preventDefault();

      const pagina = parseInt(this.dataset.pagina);

      if (!pagina) {
        return;
      }

      cargarMetodosPago(pagina);
    });
  });
}

/*==========================================================
=            PAGINACIÓN
==========================================================*/

function inicializarPaginacionMetodos() {
  document.querySelectorAll(".paginaMetodo").forEach((boton) => {
    boton.onclick = function (e) {
      e.preventDefault();

      cargarMetodosPago(this.dataset.pagina);
    };
  });
}

/*==========================================================
=            CHECK TODOS
==========================================================*/

function inicializarCheckTodos() {
  const checkTodos = document.getElementById("checkTodos");

  if (checkTodos) {
    checkTodos.checked = false;

    checkTodos.onchange = function () {
      document.querySelectorAll(".checkMetodo").forEach((check) => {
        check.checked = this.checked;
      });

      actualizarContadorSeleccionados();
    };
  }

  document.querySelectorAll(".checkMetodo").forEach((check) => {
    check.onchange = actualizarContadorSeleccionados;
  });
}

/*==========================================================
=            CONTADOR SELECCIONADOS
==========================================================*/

function actualizarContadorSeleccionados() {
  const total = document.querySelectorAll(".checkMetodo:checked").length;

  const badge = document.getElementById("contadorSeleccionados");

  if (badge) {
    badge.textContent = `${total} seleccionado${total !== 1 ? "s" : ""}`;
  }
}

/*==========================================================
=            FILTROS
==========================================================*/

function inicializarFiltros() {
  /*======================================================
    =            BUSCADOR
    ======================================================*/

  const txtBuscar = document.getElementById("buscarMetodo");

  let tiempoBusqueda = null;

  if (txtBuscar) {
    txtBuscar.addEventListener("input", function () {
      clearTimeout(tiempoBusqueda);

      tiempoBusqueda = setTimeout(() => {
        cargarMetodosPago(1);
      }, 300);
    });
  }

  /*======================================================
    =            FECHA
    ======================================================*/

  const campoFecha = document.getElementById("rangoFecha");

  if (campoFecha && typeof flatpickr !== "undefined") {
    flatpickr(campoFecha, {
      mode: "range",

      dateFormat: "Y-m-d",

      locale: "es",

      allowInput: true,

      onClose: function (selectedDates, dateStr, instance) {
        /*
         * Si hay dos fechas seleccionadas,
         * cargar los resultados.
         */

        if (selectedDates.length === 2) {
          cargarMetodosPago(1);
        }
      },
    });
  }

  /*======================================================
    =            ORDENAR
    ======================================================*/

  const ordenar = document.getElementById("ordenarPor");

  if (ordenar) {
    ordenar.addEventListener("change", function () {
      cargarMetodosPago(1);
    });
  }

  /*======================================================
    =            BOTÓN ACTUALIZAR
    ======================================================*/

  const btnActualizar = document.getElementById("btnActualizar");

  if (btnActualizar) {
    btnActualizar.addEventListener("click", function (e) {
      e.preventDefault();

      /*
       * Volver a la primera página
       * y cargar nuevamente.
       */

      cargarMetodosPago(1);
      cargarKPI();
      cargarGraficosMetodos();
    });
  }

  /*======================================================
    =            BOTÓN LIMPIAR
    ======================================================*/

  const btnLimpiar = document.getElementById("btnLimpiarFiltros");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function (e) {
      e.preventDefault();

      /*------------------------------------------
                BUSCADOR
                ------------------------------------------*/

      const buscar = document.getElementById("buscarMetodo");

      if (buscar) {
        buscar.value = "";
      }

      /*------------------------------------------
                FECHA
                ------------------------------------------*/

      const fecha = document.getElementById("rangoFecha");

      if (fecha) {
        /*
         * Limpiar Flatpickr correctamente.
         */

        if (fecha._flatpickr) {
          fecha._flatpickr.clear();
        } else {
          fecha.value = "";
        }
      }

      /*------------------------------------------
                ORDEN
                ------------------------------------------*/

      const ordenar = document.getElementById("ordenarPor");

      if (ordenar) {
        ordenar.value = "nombre_asc";
      }

      /*------------------------------------------
                ESTADO
                ------------------------------------------*/

      const estado = document.getElementById("filtroEstado");

      if (estado) {
        estado.value = "";
      }

      /*------------------------------------------
                VOLVER A PRIMERA PÁGINA
                ------------------------------------------*/

      paginaActualMetodos = 1;

      /*------------------------------------------
                CARGAR TABLA
                ------------------------------------------*/

      cargarMetodosPago(1);

      /*------------------------------------------
                ACTUALIZAR KPI
                ------------------------------------------*/

      cargarKPI();
      cargarGraficosMetodos();
    });
  }
}
/*==========================================================
=            EVENTO EDITAR MÉTODO
==========================================================*/

function inicializarEventosEditarMetodo() {
  /*
   * Delegación de eventos.
   *
   * tbodyMetodosPago existe desde el inicio,
   * pero los botones .btnEditarMetodo son creados
   * posteriormente mediante AJAX.
   */

  const tbody = document.getElementById("tbodyMetodosPago");

  if (!tbody) {
    console.error("No existe #tbodyMetodosPago");
    return;
  }

  /*
   * Evitamos registrar el evento más de una vez.
   */

  if (tbody.dataset.editarInicializado === "1") {
    return;
  }

  tbody.dataset.editarInicializado = "1";

  tbody.addEventListener("click", function (e) {
    const boton = e.target.closest(".btnEditarMetodo");

    if (!boton) {
      return;
    }

    e.preventDefault();

    const idMetodo = boton.dataset.id;

    if (!idMetodo) {
      mostrarMensaje("No se pudo identificar el método de pago.", "danger");

      return;
    }

    abrirModalEditarMetodo(idMetodo);
  });
}

/*==========================================================
=            ABRIR MODAL EDITAR
==========================================================*/

function abrirModalEditarMetodo(idMetodo) {
  const modalElement = document.getElementById("modalEditarMetodo");

  if (!modalElement) {
    console.error("No existe #modalEditarMetodo");

    mostrarMensaje("No se encontró el modal de edición.", "danger");

    return;
  }

  const inputId = document.getElementById("editarIdMetodo");

  const inputNombre = document.getElementById("editarNombreMetodo");

  const cargando = document.getElementById("cargandoEditarMetodo");

  const mensaje = document.getElementById("mensajeEditarMetodo");

  /*
   * Limpiar información anterior.
   */

  if (inputId) {
    inputId.value = idMetodo;
  }

  if (inputNombre) {
    inputNombre.value = "";
  }

  if (mensaje) {
    mensaje.className = "alert d-none mb-0";

    mensaje.innerHTML = "";
  }

  /*
   * Mostrar cargando.
   */

  if (cargando) {
    cargando.classList.remove("d-none");
  }

  /*
   * Deshabilitar formulario mientras carga.
   */

  if (inputNombre) {
    inputNombre.disabled = true;
  }

  /*
   * Crear / obtener instancia Bootstrap.
   */

  const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

  /*
   * ABRIR MODAL INMEDIATAMENTE.
   */

  modal.show();

  /*
   * Obtener datos del método.
   */

  const formData = new FormData();

  formData.append("id_metodo_pago", idMetodo);

  fetch("ajax/obtener_metodo_pago.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }

      return response.json();
    })

    .then((res) => {
      if (!res.estado) {
        mostrarMensaje(
          res.mensaje || "No fue posible obtener el método de pago.",
          "danger",
        );

        modal.hide();

        return;
      }

      /*
       * Cargar información.
       */

      if (inputId) {
        inputId.value = res.metodo.id_metodo_pago;
      }

      if (inputNombre) {
        inputNombre.value = res.metodo.nombre;
      }
    })

    .catch((error) => {
      console.error("Error al obtener método:", error);

      mostrarMensaje("Error al cargar los datos del método de pago.", "danger");

      modal.hide();
    })

    .finally(() => {
      if (cargando) {
        cargando.classList.add("d-none");
      }

      if (inputNombre) {
        inputNombre.disabled = false;
      }
    });
}

/*==========================================================
=            GUARDAR EDICIÓN
==========================================================*/

function actualizarMetodoPago(e) {
  e.preventDefault();

  const idMetodo = document.getElementById("editarIdMetodo")?.value.trim();

  const nombre = document.getElementById("editarNombreMetodo")?.value.trim();

  if (!idMetodo) {
    mostrarMensaje("No se identificó el método de pago.", "danger");

    return;
  }

  if (!nombre) {
    mostrarMensaje("Debe ingresar el nombre del método de pago.", "warning");

    document.getElementById("editarNombreMetodo")?.focus();

    return;
  }

  const boton = document.getElementById("btnActualizarMetodo");

  if (boton) {
    boton.disabled = true;

    boton.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Guardando...
    `;
  }

  const formData = new FormData();

  formData.append("id_metodo_pago", idMetodo);

  formData.append("nombre", nombre);

  fetch("ajax/editar_metodo_pago.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }

      return response.json();
    })

    .then((res) => {
      if (!res.estado) {
        mostrarMensaje(
          res.mensaje || "No fue posible actualizar el método.",
          "danger",
        );

        return;
      }

      /*
       * Cerrar modal.
       */

      const modalElement = document.getElementById("modalEditarMetodo");

      const modal = bootstrap.Modal.getInstance(modalElement);

      if (modal) {
        modal.hide();
      }

      /*
       * Mensaje de éxito.
       */

      mostrarMensaje(
        res.mensaje || "Método de pago actualizado correctamente.",
        "success",
      );

      /*
       * Recargar tabla.
       */

      cargarMetodosPago(paginaActualMetodos);

      /*
       * Actualizar KPI.
       */

      cargarKPI();
      cargarGraficosMetodos();
    })

    .catch((error) => {
      console.error("Error al actualizar método:", error);

      mostrarMensaje(
        "Ocurrió un error al actualizar el método de pago.",
        "danger",
      );
    })

    .finally(() => {
      if (boton) {
        boton.disabled = false;

        boton.innerHTML = `
            <i class="bi bi-check-circle me-1"></i>
            Guardar Cambios
        `;
      }
    });
}
/*==========================================================
=            EVENTO ELIMINAR MÉTODO
==========================================================*/

function inicializarEventosEliminarMetodo() {
  const tbody = document.getElementById("tbodyMetodosPago");

  if (!tbody) {
    console.error("No existe #tbodyMetodosPago");
    return;
  }

  /*
   * Evitar registrar el evento más de una vez.
   */

  if (tbody.dataset.eliminarInicializado === "1") {
    return;
  }

  tbody.dataset.eliminarInicializado = "1";

  /*
   * Delegación de eventos.
   *
   * Los botones son generados mediante AJAX.
   */

  tbody.addEventListener("click", function (e) {
    const boton = e.target.closest(".btnEliminarMetodo");

    if (!boton) {
      return;
    }

    e.preventDefault();

    const idMetodo = boton.dataset.id;

    if (!idMetodo) {
      mostrarMensaje("No se pudo identificar el método de pago.", "danger");

      return;
    }

    /*
     * Obtener nombre desde la fila.
     */

    const fila = boton.closest("tr");

    let nombreMetodo = "";

    if (fila) {
      const celdaNombre = fila.querySelector("td:nth-child(3)");

      if (celdaNombre) {
        nombreMetodo = celdaNombre.textContent.trim();
      }
    }

    /*
     * Abrir modal.
     */

    abrirModalEliminarMetodo(idMetodo, nombreMetodo);
  });
}
function abrirModalEliminarMetodo(idMetodo, nombreMetodo) {
  const modalElement = document.getElementById("modalEliminarMetodo");

  if (!modalElement) {
    console.error("No existe #modalEliminarMetodo");

    mostrarMensaje("No se encontró el modal de eliminación.", "danger");

    return;
  }

  /*
   * Guardar ID.
   */

  const inputId = document.getElementById("idMetodoEliminar");

  if (inputId) {
    inputId.value = idMetodo;
  }

  /*
   * Mostrar nombre.
   */

  const elementoNombre = document.getElementById("nombreMetodoEliminar");

  if (elementoNombre) {
    elementoNombre.textContent = nombreMetodo || "Método de pago";
  }

  /*
   * Obtener botón.
   */

  const botonConfirmar = document.getElementById("btnConfirmarEliminarMetodo");

  /*
   * Restaurar estado del botón.
   */

  if (botonConfirmar) {
    botonConfirmar.disabled = false;

    botonConfirmar.innerHTML = `
      <i class="bi bi-trash-fill me-1"></i>
      Sí, eliminar
    `;
  }

  /*
   * Mostrar modal Bootstrap.
   */

  const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

  modal.show();
}
function inicializarEventoConfirmarEliminarMetodo() {
  const boton = document.getElementById("btnConfirmarEliminarMetodo");

  if (!boton) {
    return;
  }

  /*
   * Evitar duplicar evento.
   */

  if (boton.dataset.inicializado === "1") {
    return;
  }

  boton.dataset.inicializado = "1";

  boton.addEventListener("click", function () {
    const inputId = document.getElementById("idMetodoEliminar");

    const idMetodo = inputId?.value.trim() || "";

    if (!idMetodo) {
      mostrarMensaje("No se identificó el método de pago.", "danger");

      return;
    }

    eliminarMetodoPago(idMetodo);
  });
}
/*==========================================================
=            ELIMINAR MÉTODO DE PAGO
==========================================================*/

function eliminarMetodoPago(idMetodo) {
  const boton = document.getElementById("btnConfirmarEliminarMetodo");

  /*======================================================
  =            VALIDAR ID
  ======================================================*/

  if (!idMetodo) {
    mostrarMensaje("No se identificó el método de pago.", "danger");

    return;
  }

  /*======================================================
  =            BLOQUEAR BOTÓN
  ======================================================*/

  if (boton) {
    boton.disabled = true;

    boton.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-2"
        role="status"
        aria-hidden="true">
      </span>

      Eliminando...
    `;
  }

  /*======================================================
  =            FORM DATA
  ======================================================*/

  const formData = new FormData();

  formData.append("id_metodo_pago", idMetodo);

  /*======================================================
  =            AJAX
  ======================================================*/

  fetch("ajax/eliminar_metodo_pago.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }

      return response.json();
    })

    .then((res) => {
      console.log("Respuesta eliminar_metodo_pago.php:", res);

      /*==================================================
    =            ERROR
    ==================================================*/

      if (!res.estado) {
        mostrarMensaje(
          res.mensaje || "No fue posible eliminar el método de pago.",
          "danger",
        );

        return;
      }

      /*==================================================
    =            CERRAR MODAL
    ==================================================*/

      const modalElement = document.getElementById("modalEliminarMetodo");

      const modal = bootstrap.Modal.getInstance(modalElement);

      if (modal) {
        modal.hide();
      }

      /*==================================================
    =            MENSAJE
    ==================================================*/

      mostrarMensaje(
        res.mensaje || "Método de pago eliminado correctamente.",
        "success",
      );

      /*==================================================
    =            RECARGAR TABLA
    ==================================================*/

      cargarMetodosPago(paginaActualMetodos);

      /*==================================================
    =            ACTUALIZAR KPI
    ==================================================*/

      cargarKPI();
      cargarGraficosMetodos();
    })

    .catch((error) => {
      console.error("Error al eliminar método:", error);

      mostrarMensaje(
        "Ocurrió un error al eliminar el método de pago.",
        "danger",
      );
    })

    .finally(() => {
      if (boton) {
        boton.disabled = false;

        boton.innerHTML = `
        <i class="bi bi-trash-fill me-1"></i>
        Sí, eliminar
      `;
      }
    });
}
/*==========================================================
=            GRÁFICOS
==========================================================*/

let graficoVentasMetodo = null;
let graficoMontoMetodo = null;
let graficoHistoricoMetodo = null;

/*==========================================================
=            CARGAR GRÁFICOS
==========================================================*/

function cargarGraficosMetodos() {
  fetch("ajax/adm_graficos_metodos.php", {
    method: "POST",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("HTTP " + response.status);
      }

      return response.json();
    })

    .then((res) => {
      console.log("Respuesta gráficos:", res);

      if (!res.estado) {
        mostrarMensaje(
          res.mensaje || "No fue posible cargar los gráficos.",
          "danger",
        );

        return;
      }

      /*====================================================
      =            GRÁFICO 1
      =            VENTAS POR MÉTODO
      ====================================================*/

      crearGraficoVentasMetodo(res.ventasMetodo);

      /*====================================================
      =            GRÁFICO 2
      =            MONTO POR MÉTODO
      ====================================================*/

      crearGraficoMontoMetodo(res.montoMetodo);

      /*====================================================
      =            GRÁFICO 3
      =            HISTÓRICO
      ====================================================*/

      crearGraficoHistoricoMetodo(res.historico);
    })

    .catch((error) => {
      console.error("Error al cargar gráficos:", error);

      mostrarMensaje("Error al cargar los gráficos estadísticos.", "danger");
    });
}
/*==========================================================
=            GRÁFICO VENTAS POR MÉTODO
==========================================================*/

function crearGraficoVentasMetodo(datos) {
  const canvas = document.getElementById("graficoVentasMetodo");

  if (!canvas) {
    return;
  }

  /*======================================================
  =            DESTRUIR GRÁFICO ANTERIOR
  ======================================================*/

  if (graficoVentasMetodo) {
    graficoVentasMetodo.destroy();

    graficoVentasMetodo = null;
  }

  /*======================================================
  =            SIN DATOS
  ======================================================*/

  if (!datos || !datos.labels || datos.labels.length === 0) {
    const ctx = canvas.getContext("2d");

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    return;
  }

  /*======================================================
  =            CREAR GRÁFICO
  ======================================================*/

  graficoVentasMetodo = new Chart(canvas, {
    type: "doughnut",

    data: {
      labels: datos.labels,

      datasets: [
        {
          data: datos.data,

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

        tooltip: {
          callbacks: {
            label: function (context) {
              const valor = context.parsed || 0;

              return (
                context.label + ": " + valor.toLocaleString("es-PE") + " ventas"
              );
            },
          },
        },
      },
    },
  });
}
/*==========================================================
=            GRÁFICO MONTO POR MÉTODO
==========================================================*/

function crearGraficoMontoMetodo(datos) {
  const canvas = document.getElementById("graficoMontoMetodo");

  if (!canvas) {
    return;
  }

  /*======================================================
  =            DESTRUIR ANTERIOR
  ======================================================*/

  if (graficoMontoMetodo) {
    graficoMontoMetodo.destroy();

    graficoMontoMetodo = null;
  }

  /*======================================================
  =            SIN DATOS
  ======================================================*/

  if (!datos || !datos.labels || datos.labels.length === 0) {
    const ctx = canvas.getContext("2d");

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    return;
  }

  /*======================================================
  =            CREAR
  ======================================================*/

  graficoMontoMetodo = new Chart(canvas, {
    type: "bar",

    data: {
      labels: datos.labels,

      datasets: [
        {
          label: "Monto vendido",

          data: datos.data,

          borderWidth: 1,

          borderRadius: 6,
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
            callback: function (value) {
              return "S/ " + Number(value).toLocaleString("es-PE");
            },
          },
        },
      },

      plugins: {
        legend: {
          display: false,
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              const valor = Number(context.raw || 0);

              return (
                "S/ " +
                valor.toLocaleString("es-PE", {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2,
                })
              );
            },
          },
        },
      },
    },
  });
}
/*==========================================================
=            GRÁFICO HISTÓRICO POR MÉTODO
==========================================================*/

function crearGraficoHistoricoMetodo(datos) {
  const canvas = document.getElementById("graficoHistoricoMetodo");

  if (!canvas) {
    return;
  }

  /*======================================================
  =            DESTRUIR ANTERIOR
  ======================================================*/

  if (graficoHistoricoMetodo) {
    graficoHistoricoMetodo.destroy();

    graficoHistoricoMetodo = null;
  }

  /*======================================================
  =            VALIDAR
  ======================================================*/

  if (
    !datos ||
    !datos.labels ||
    !datos.datasets ||
    datos.datasets.length === 0
  ) {
    return;
  }

  /*======================================================
  =            PREPARAR DATASETS
  ======================================================*/

  const datasets = datos.datasets.map(function (dataset) {
    return {
      label: dataset.label,

      data: dataset.data,

      fill: false,

      tension: 0.35,

      pointRadius: 3,

      pointHoverRadius: 6,

      borderWidth: 2,
    };
  });

  /*======================================================
  =            CREAR GRÁFICO
  ======================================================*/

  graficoHistoricoMetodo = new Chart(canvas, {
    type: "line",

    data: {
      labels: datos.labels,

      datasets: datasets,
    },

    options: {
      responsive: true,

      maintainAspectRatio: false,

      interaction: {
        mode: "index",

        intersect: false,
      },

      scales: {
        y: {
          beginAtZero: true,

          ticks: {
            precision: 0,
          },

          title: {
            display: true,

            text: "Cantidad de ventas",
          },
        },

        x: {
          title: {
            display: true,

            text: "Fecha",
          },
        },
      },

      plugins: {
        legend: {
          position: "bottom",
        },

        tooltip: {
          callbacks: {
            label: function (context) {
              return (
                context.dataset.label +
                ": " +
                Number(context.raw || 0).toLocaleString("es-PE") +
                " ventas"
              );
            },
          },
        },
      },
    },
  });
}
