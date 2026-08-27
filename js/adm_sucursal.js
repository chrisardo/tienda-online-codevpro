//=====================================================
// CoDevPro Technology
// Archivo: js/adm_sucursal.js
// Módulo: Sucursales
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActualSucursales = 1;

let registrosPorPaginaSucursales = 5;

let temporizadorBusquedaSucursal = null;

let modalRegistrarSucursal = null;

let modalEditarSucursal = null;

let sucursalEditando = null;

//=====================================================
// CONFIGURACIÓN AJAX
//=====================================================

const AJAX_SUCURSALES = {
  listar: "ajax/obtener_lista_sucursales.php",

  registrar: "ajax/registrar_sucursal.php",

  editar: "ajax/editar_sucursal.php",

  eliminar: "ajax/eliminar_sucursal.php",
};

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarSucursales();
});

//=====================================================
// FUNCIÓN PRINCIPAL
//=====================================================

function inicializarSucursales() {
  inicializarModalesSucursal();

  configurarEventosSucursales();

  cargarSucursales();
}

//=====================================================
// INICIALIZAR MODALES
//=====================================================

function inicializarModalesSucursal() {
  const elementoRegistrar = document.getElementById("modalRegistrarSucursal");

  const elementoEditar = document.getElementById("modalEditarSucursal");

  if (elementoRegistrar) {
    modalRegistrarSucursal =
      bootstrap.Modal.getOrCreateInstance(elementoRegistrar);

    elementoRegistrar.addEventListener("shown.bs.modal", function () {
      const input = document.getElementById("nombreSucursalRegistrar");

      if (input) {
        input.focus();
      }
    });

    elementoRegistrar.addEventListener("hidden.bs.modal", function () {
      limpiarFormularioRegistrar();
    });
  }

  if (elementoEditar) {
    modalEditarSucursal = bootstrap.Modal.getOrCreateInstance(elementoEditar);

    elementoEditar.addEventListener("shown.bs.modal", function () {
      const input = document.getElementById("nombreSucursalEditar");

      if (input) {
        input.focus();
      }
    });

    elementoEditar.addEventListener("hidden.bs.modal", function () {
      sucursalEditando = null;

      limpiarAlertasModal("modalEditarSucursal");
    });
  }
}

//=====================================================
// CONFIGURAR EVENTOS
//=====================================================

function configurarEventosSucursales() {
  //=================================================
  // BUSCADOR
  //=================================================

  const inputBusqueda = document.getElementById("buscarSucursal");

  if (inputBusqueda) {
    inputBusqueda.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaSucursal);

      temporizadorBusquedaSucursal = setTimeout(function () {
        paginaActualSucursales = 1;

        cargarSucursales();
      }, 300);
    });
  }

  //=================================================
  // LIMPIAR BÚSQUEDA
  //=================================================

  const btnLimpiar = document.getElementById("btnLimpiarBusquedaSucursal");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function () {
      const input = document.getElementById("buscarSucursal");

      if (input) {
        input.value = "";

        input.focus();
      }

      paginaActualSucursales = 1;

      cargarSucursales();
    });
  }

  //=================================================
  // ACTUALIZAR
  //=================================================

  const btnActualizar = document.getElementById("btnActualizarSucursales");

  if (btnActualizar) {
    btnActualizar.addEventListener("click", function () {
      cargarSucursales();
    });
  }

  //=================================================
  // NUEVA SUCURSAL
  //=================================================

  const btnNueva = document.getElementById("btnNuevaSucursal");

  if (btnNueva) {
    btnNueva.addEventListener("click", function () {
      limpiarFormularioRegistrar();
    });
  }

  //=================================================
  // FORMULARIO REGISTRAR
  //=================================================

  const formularioRegistrar = document.getElementById("formRegistrarSucursal");

  if (formularioRegistrar) {
    formularioRegistrar.addEventListener("submit", function (e) {
      e.preventDefault();

      registrarSucursal();
    });
  }

  //=================================================
  // FORMULARIO EDITAR
  //=================================================

  const formularioEditar = document.getElementById("formEditarSucursal");

  if (formularioEditar) {
    formularioEditar.addEventListener("submit", function (e) {
      e.preventDefault();

      actualizarSucursal();
    });
  }
}

//=====================================================
// CARGAR SUCURSALES
//=====================================================

function cargarSucursales() {
  const tbody = document.getElementById("tbodySucursales");

  if (!tbody) {
    return;
  }

  //=================================================
  // LOADING
  //=================================================

  tbody.innerHTML = `

        <tr>

            <td
                colspan="4"
                class="text-center py-5">

                <div
                    class="spinner-border text-primary mb-3"
                    role="status">

                    <span class="visually-hidden">
                        Cargando...
                    </span>

                </div>

                <div class="text-muted">

                    Cargando sucursales...

                </div>

            </td>

        </tr>

    `;

  const busqueda =
    document.getElementById("buscarSucursal")?.value.trim() || "";

  const parametros = new URLSearchParams();

  parametros.append("pagina", paginaActualSucursales);

  parametros.append("registros", registrosPorPaginaSucursales);

  parametros.append("busqueda", busqueda);

  fetch(AJAX_SUCURSALES.listar + "?" + parametros.toString(), {
    method: "GET",

    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then(function (respuesta) {
      if (!respuesta.ok) {
        throw new Error("Error HTTP: " + respuesta.status);
      }

      return respuesta.json();
    })
    .then(function (data) {
      if (!data) {
        throw new Error("Respuesta vacía del servidor.");
      }

      if (data.error) {
        mostrarAlertaSucursal(data.mensaje || "Ocurrió un error.", "danger");

        mostrarTablaVacia();

        return;
      }

      renderizarSucursales(data);
    })
    .catch(function (error) {
      console.error("Error al cargar sucursales:", error);

      mostrarAlertaSucursal("No se pudieron cargar las sucursales.", "danger");

      mostrarTablaVacia();
    });
}

//=====================================================
// RENDERIZAR SUCURSALES
//=====================================================

function renderizarSucursales(data) {
  const tbody = document.getElementById("tbodySucursales");

  const sinSucursales = document.getElementById("sinSucursales");

  if (!tbody) {
    return;
  }

  //=================================================
  // OBTENER REGISTROS
  //=================================================

  let sucursales = [];

  if (Array.isArray(data.sucursales)) {
    sucursales = data.sucursales;
  } else if (Array.isArray(data.data)) {
    sucursales = data.data;
  }

  //=================================================
  // KPI
  //=================================================

  actualizarKPI(data);

  //=================================================
  // SIN REGISTROS
  //=================================================

  if (sucursales.length === 0) {
    tbody.innerHTML = "";

    if (sinSucursales) {
      sinSucursales.classList.remove("d-none");
    }

    actualizarInformacionPaginacion(0, 0, 0);

    renderizarPaginacion(0);

    return;
  }

  //=================================================
  // OCULTAR SIN REGISTROS
  //=================================================

  if (sinSucursales) {
    sinSucursales.classList.add("d-none");
  }

  //=================================================
  // GENERAR TABLA
  //=================================================

  let html = "";

  sucursales.forEach(function (sucursal, index) {
    const id = parseInt(sucursal.id_sucursal || 0);

    const nombreOriginal = String(sucursal.nombre || "Sin nombre");

    const nombre = escaparHTML(nombreOriginal);

    const eliminado = parseInt(sucursal.Eliminado || 0);

    const estadoActivo = eliminado === 0;

    const estadoHTML = estadoActivo
      ? `

                    <span
                        class="badge bg-success-subtle text-success px-3 py-2">

                        <i
                            class="bi bi-check-circle-fill me-1">
                        </i>

                        Activa

                    </span>

                `
      : `

                    <span
                        class="badge bg-danger-subtle text-danger px-3 py-2">

                        <i
                            class="bi bi-x-circle-fill me-1">
                        </i>

                        Inactiva

                    </span>

                `;

    const numero = obtenerNumeroRegistro(index);

    html += `

                <tr>

                    <td class="px-4 fw-semibold text-muted">

                        ${numero}

                    </td>


                    <td>

                        <div
                            class="d-flex align-items-center">

                            <div
                                class="rounded-3 bg-primary bg-opacity-10
                                       text-primary d-flex align-items-center
                                       justify-content-center me-3"
                                style="width:42px;height:42px;">

                                <i
                                    class="bi bi-building">
                                </i>

                            </div>


                            <div>

                                <div
                                    class="fw-semibold">

                                    ${nombre}

                                </div>

                                <small
                                    class="text-muted">

                                    ID: ${id}

                                </small>

                            </div>

                        </div>

                    </td>


                    <td class="text-center">

                        ${estadoHTML}

                    </td>


                    <td class="text-center">

                        <div
                            class="btn-group"
                            role="group">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                title="Editar sucursal"
                                onclick="abrirEditarSucursal(
                                    ${id},
                                    '${escaparAtributoJS(nombreOriginal)}',
                                    ${eliminado}
                                )">

                                <i
                                    class="bi bi-pencil-square">
                                </i>

                            </button>


                            ${
                              estadoActivo
                                ? `

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Desactivar sucursal"
                                        onclick="confirmarEliminarSucursal(
                                            ${id},
                                            '${escaparAtributoJS(nombreOriginal)}'
                                        )">

                                        <i
                                            class="bi bi-trash">
                                        </i>

                                    </button>

                                `
                                : `

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-success"
                                        title="Reactivar sucursal"
                                        onclick="reactivarSucursal(
                                            ${id},
                                            '${escaparAtributoJS(nombreOriginal)}'
                                        )">

                                        <i
                                            class="bi bi-arrow-counterclockwise">
                                        </i>

                                    </button>

                                `
                            }

                        </div>

                    </td>

                </tr>

            `;
  });

  tbody.innerHTML = html;

  //=================================================
  // PAGINACIÓN
  //=================================================

  const total = parseInt(
    data.total || data.totalRegistros || sucursales.length,
  );

  const paginas = parseInt(
    data.paginas ||
      data.totalPaginas ||
      Math.ceil(total / registrosPorPaginaSucursales),
  );

  const desde =
    total > 0
      ? (paginaActualSucursales - 1) * registrosPorPaginaSucursales + 1
      : 0;

  const hasta = Math.min(
    paginaActualSucursales * registrosPorPaginaSucursales,
    total,
  );

  actualizarInformacionPaginacion(desde, hasta, total);

  renderizarPaginacion(paginas);
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKPI(data) {
  let total = parseInt(data.total || data.totalRegistros || 0);

  let activas = parseInt(data.activas || data.totalActivas || 0);

  let inactivas = parseInt(data.inactivas || data.totalInactivas || 0);

  if (data.kpi) {
    total = parseInt(data.kpi.total || total);

    activas = parseInt(data.kpi.activas || activas);

    inactivas = parseInt(data.kpi.inactivas || inactivas);
  }

  const elementoTotal = document.getElementById("kpiTotalSucursales");

  const elementoActivas = document.getElementById("kpiSucursalesActivas");

  const elementoInactivas = document.getElementById("kpiSucursalesInactivas");

  const elementoEstado = document.getElementById("kpiEstadoSucursales");

  if (elementoTotal) {
    elementoTotal.textContent = total;
  }

  if (elementoActivas) {
    elementoActivas.textContent = activas;
  }

  if (elementoInactivas) {
    elementoInactivas.textContent = inactivas;
  }

  if (elementoEstado) {
    elementoEstado.textContent = activas > 0 ? "Activo" : "Sin sucursales";
  }
}

//=====================================================
// PAGINACIÓN
//=====================================================

function renderizarPaginacion(totalPaginas) {
  const contenedor = document.getElementById("paginacionSucursales");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = "";

  if (totalPaginas <= 1) {
    return;
  }

  //=================================================
  // ANTERIOR
  //=================================================

  const liAnterior = document.createElement("li");

  liAnterior.className =
    "page-item " + (paginaActualSucursales <= 1 ? "disabled" : "");

  liAnterior.innerHTML = `

        <a
            class="page-link"
            href="#"
            aria-label="Anterior">

            <i
                class="bi bi-chevron-left">
            </i>

        </a>

    `;

  liAnterior.addEventListener("click", function (e) {
    e.preventDefault();

    if (paginaActualSucursales > 1) {
      paginaActualSucursales--;

      cargarSucursales();
    }
  });

  contenedor.appendChild(liAnterior);

  //=================================================
  // NÚMEROS
  //=================================================

  const inicio = Math.max(1, paginaActualSucursales - 2);

  const fin = Math.min(totalPaginas, paginaActualSucursales + 2);

  for (let i = inicio; i <= fin; i++) {
    const li = document.createElement("li");

    li.className =
      "page-item " + (i === paginaActualSucursales ? "active" : "");

    li.innerHTML = `

            <a
                class="page-link"
                href="#">

                ${i}

            </a>

        `;

    li.addEventListener("click", function (e) {
      e.preventDefault();

      paginaActualSucursales = i;

      cargarSucursales();
    });

    contenedor.appendChild(li);
  }

  //=================================================
  // SIGUIENTE
  //=================================================

  const liSiguiente = document.createElement("li");

  liSiguiente.className =
    "page-item " + (paginaActualSucursales >= totalPaginas ? "disabled" : "");

  liSiguiente.innerHTML = `

        <a
            class="page-link"
            href="#"
            aria-label="Siguiente">

            <i
                class="bi bi-chevron-right">
            </i>

        </a>

    `;

  liSiguiente.addEventListener("click", function (e) {
    e.preventDefault();

    if (paginaActualSucursales < totalPaginas) {
      paginaActualSucursales++;

      cargarSucursales();
    }
  });

  contenedor.appendChild(liSiguiente);
}

//=====================================================
// INFORMACIÓN PAGINACIÓN
//=====================================================

function actualizarInformacionPaginacion(desde, hasta, total) {
  const elemento = document.getElementById("infoPaginacionSucursales");

  if (!elemento) {
    return;
  }

  if (total === 0) {
    elemento.textContent = "Mostrando 0 sucursales";

    return;
  }

  elemento.textContent = `Mostrando ${desde} - ${hasta} de ${total} sucursales`;
}

//=====================================================
// MOSTRAR TABLA VACÍA
//=====================================================

function mostrarTablaVacia() {
  const tbody = document.getElementById("tbodySucursales");

  if (!tbody) {
    return;
  }

  tbody.innerHTML = `

        <tr>

            <td
                colspan="4"
                class="text-center py-5">

                <div
                    class="text-muted">

                    No se pudieron cargar los registros.

                </div>

            </td>

        </tr>

    `;
}

//=====================================================
// REGISTRAR SUCURSAL
//=====================================================

function registrarSucursal() {
  const formulario = document.getElementById("formRegistrarSucursal");

  if (!formulario) {
    return;
  }

  const inputNombre = document.getElementById("nombreSucursalRegistrar");

  const nombre = inputNombre?.value.trim() || "";

  if (!nombre) {
    mostrarAlertaModalSucursal(
      "modalRegistrarSucursal",
      "Ingresa el nombre de la sucursal.",
      "warning",
    );

    inputNombre?.focus();

    return;
  }

  if (nombre.length < 2) {
    mostrarAlertaModalSucursal(
      "modalRegistrarSucursal",
      "El nombre de la sucursal debe tener al menos 2 caracteres.",
      "warning",
    );

    inputNombre?.focus();

    return;
  }

  const boton = document.getElementById("btnGuardarSucursal");

  cambiarEstadoBoton(boton, true, "Guardando...");

  const datos = new FormData();

  datos.append("nombre", nombre);

  fetch(AJAX_SUCURSALES.registrar, {
    method: "POST",

    body: datos,

    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then(function (respuesta) {
      if (!respuesta.ok) {
        throw new Error("Error HTTP: " + respuesta.status);
      }

      return respuesta.json();
    })
    .then(function (data) {
      if (data.error) {
        throw new Error(data.mensaje || "No se pudo registrar la sucursal.");
      }

      mostrarAlertaSucursal(
        data.mensaje || "Sucursal registrada correctamente.",
        "success",
      );

      cerrarModal(modalRegistrarSucursal);

      limpiarFormularioRegistrar();

      paginaActualSucursales = 1;

      cargarSucursales();
    })
    .catch(function (error) {
      console.error("Error al registrar sucursal:", error);

      mostrarAlertaModalSucursal(
        "modalRegistrarSucursal",
        error.message || "No se pudo registrar la sucursal.",
        "danger",
      );
    })
    .finally(function () {
      cambiarEstadoBoton(boton, false, "Guardar sucursal");
    });
}

//=====================================================
// ABRIR EDITAR SUCURSAL
//=====================================================

function abrirEditarSucursal(idSucursal, nombre, eliminado) {
  sucursalEditando = {
    id_sucursal: parseInt(idSucursal),

    nombre: String(nombre || ""),

    Eliminado: parseInt(eliminado || 0),
  };

  const inputId = document.getElementById("idSucursalEditar");

  const inputNombre = document.getElementById("nombreSucursalEditar");

  if (inputId) {
    inputId.value = sucursalEditando.id_sucursal;
  }

  if (inputNombre) {
    inputNombre.value = sucursalEditando.nombre;
  }

  limpiarAlertasModal("modalEditarSucursal");

  if (!modalEditarSucursal) {
    const elemento = document.getElementById("modalEditarSucursal");

    if (elemento) {
      modalEditarSucursal = bootstrap.Modal.getOrCreateInstance(elemento);
    }
  }

  if (modalEditarSucursal) {
    modalEditarSucursal.show();
  }
}

//=====================================================
// ACTUALIZAR SUCURSAL
//=====================================================

function actualizarSucursal() {
  const inputId = document.getElementById("idSucursalEditar");

  const inputNombre = document.getElementById("nombreSucursalEditar");

  const idSucursal = parseInt(inputId?.value || 0);

  const nombre = inputNombre?.value.trim() || "";

  if (!idSucursal) {
    mostrarAlertaModalSucursal(
      "modalEditarSucursal",
      "No se pudo identificar la sucursal.",
      "danger",
    );

    return;
  }

  if (!nombre) {
    mostrarAlertaModalSucursal(
      "modalEditarSucursal",
      "Ingresa el nombre de la sucursal.",
      "warning",
    );

    inputNombre?.focus();

    return;
  }

  if (nombre.length < 2) {
    mostrarAlertaModalSucursal(
      "modalEditarSucursal",
      "El nombre debe tener al menos 2 caracteres.",
      "warning",
    );

    inputNombre?.focus();

    return;
  }

  const boton = document.getElementById("btnActualizarSucursal");

  cambiarEstadoBoton(boton, true, "Actualizando...");

  const datos = new FormData();

  datos.append("id_sucursal", idSucursal);

  datos.append("nombre", nombre);

  fetch(AJAX_SUCURSALES.editar, {
    method: "POST",

    body: datos,

    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then(function (respuesta) {
      if (!respuesta.ok) {
        throw new Error("Error HTTP: " + respuesta.status);
      }

      return respuesta.json();
    })
    .then(function (data) {
      if (data.error) {
        throw new Error(data.mensaje || "No se pudo actualizar la sucursal.");
      }

      mostrarAlertaSucursal(
        data.mensaje || "Sucursal actualizada correctamente.",
        "success",
      );

      cerrarModal(modalEditarSucursal);

      sucursalEditando = null;

      cargarSucursales();
    })
    .catch(function (error) {
      console.error("Error al actualizar sucursal:", error);

      mostrarAlertaModalSucursal(
        "modalEditarSucursal",
        error.message || "No se pudo actualizar la sucursal.",
        "danger",
      );
    })
    .finally(function () {
      cambiarEstadoBoton(boton, false, "Guardar cambios");
    });
}

//=====================================================
// CONFIRMAR ELIMINACIÓN
//=====================================================

function confirmarEliminarSucursal(idSucursal, nombre) {
  const confirmar = window.confirm(
    `¿Deseas desactivar la sucursal "${nombre}"?\n\n` +
      "La sucursal no será eliminada físicamente. " +
      "Quedará como inactiva y podrá ser reactivada posteriormente.",
  );

  if (!confirmar) {
    return;
  }

  cambiarEstadoSucursal(idSucursal, 1);
}

//=====================================================
// REACTIVAR SUCURSAL
//=====================================================

function reactivarSucursal(idSucursal, nombre) {
  const confirmar = window.confirm(
    `¿Deseas reactivar la sucursal "${nombre}"?`,
  );

  if (!confirmar) {
    return;
  }

  cambiarEstadoSucursal(idSucursal, 0);
}

//=====================================================
// CAMBIAR ESTADO
//=====================================================

function cambiarEstadoSucursal(idSucursal, eliminado) {
  const datos = new FormData();

  datos.append("id_sucursal", idSucursal);

  datos.append("Eliminado", eliminado);

  fetch(AJAX_SUCURSALES.eliminar, {
    method: "POST",

    body: datos,

    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then(function (respuesta) {
      if (!respuesta.ok) {
        throw new Error("Error HTTP: " + respuesta.status);
      }

      return respuesta.json();
    })
    .then(function (data) {
      if (data.error) {
        throw new Error(data.mensaje || "No se pudo cambiar el estado.");
      }

      mostrarAlertaSucursal(
        data.mensaje ||
          (eliminado === 1
            ? "Sucursal desactivada correctamente."
            : "Sucursal reactivada correctamente."),
        "success",
      );

      cargarSucursales();
    })
    .catch(function (error) {
      console.error("Error al cambiar estado:", error);

      mostrarAlertaSucursal(
        error.message || "No se pudo cambiar el estado de la sucursal.",
        "danger",
      );
    });
}

//=====================================================
// LIMPIAR FORMULARIO REGISTRAR
//=====================================================

function limpiarFormularioRegistrar() {
  const formulario = document.getElementById("formRegistrarSucursal");

  if (formulario) {
    formulario.reset();
  }

  limpiarAlertasModal("modalRegistrarSucursal");
}

//=====================================================
// ALERTA GENERAL
//=====================================================

function mostrarAlertaSucursal(mensaje, tipo = "success") {
  const alerta = document.getElementById("alertaSucursales");

  if (!alerta) {
    return;
  }

  alerta.className = `px-4 pt-3 alert alert-${tipo}`;

  let icono = "bi-exclamation-circle-fill";

  if (tipo === "success") {
    icono = "bi-check-circle-fill";
  } else if (tipo === "warning") {
    icono = "bi-exclamation-triangle-fill";
  }

  alerta.innerHTML = `

        <div
            class="d-flex align-items-center">

            <i
                class="bi ${icono} me-2">
            </i>

            <span>
                ${escaparHTML(mensaje)}
            </span>

        </div>

    `;

  alerta.classList.remove("d-none");

  setTimeout(function () {
    alerta.classList.add("d-none");
  }, 4000);
}

//=====================================================
// ALERTA DEL MODAL
//=====================================================

function mostrarAlertaModalSucursal(modalId, mensaje, tipo = "danger") {
  let idAlerta = "";

  if (modalId === "modalRegistrarSucursal") {
    idAlerta = "alertaModalRegistrarSucursal";
  } else {
    idAlerta = "alertaModalEditarSucursal";
  }

  const alerta = document.getElementById(idAlerta);

  if (!alerta) {
    return;
  }

  let icono = "bi-exclamation-circle-fill";

  if (tipo === "warning") {
    icono = "bi-exclamation-triangle-fill";
  }

  alerta.className = `alert alert-${tipo} py-2 mb-3`;

  alerta.innerHTML = `

        <i
            class="bi ${icono} me-2">
        </i>

        ${escaparHTML(mensaje)}

    `;
}

//=====================================================
// LIMPIAR ALERTAS MODAL
//=====================================================

function limpiarAlertasModal(modalId) {
  let idAlerta = "";

  if (modalId === "modalRegistrarSucursal") {
    idAlerta = "alertaModalRegistrarSucursal";
  } else {
    idAlerta = "alertaModalEditarSucursal";
  }

  const alerta = document.getElementById(idAlerta);

  if (alerta) {
    alerta.className = "d-none";

    alerta.innerHTML = "";
  }
}

//=====================================================
// CERRAR MODAL
//=====================================================

function cerrarModal(modal) {
  if (modal && typeof modal.hide === "function") {
    modal.hide();
  }
}

//=====================================================
// CAMBIAR ESTADO BOTÓN
//=====================================================

function cambiarEstadoBoton(boton, cargando, texto) {
  if (!boton) {
    return;
  }

  if (cargando) {
    boton.disabled = true;

    if (!boton.dataset.textoOriginal) {
      boton.dataset.textoOriginal = boton.innerHTML;
    }

    boton.innerHTML = `

            <span
                class="spinner-border spinner-border-sm me-1"
                role="status"
                aria-hidden="true">
            </span>

            ${texto}

        `;
  } else {
    boton.disabled = false;

    boton.innerHTML = `

            <i
                class="bi bi-check-circle me-1">
            </i>

            ${texto}

        `;
  }
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escaparHTML(valor) {
  return String(valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

//=====================================================
// ESCAPAR PARA JAVASCRIPT
//=====================================================

function escaparAtributoJS(valor) {
  return String(valor)
    .replace(/\\/g, "\\\\")
    .replace(/'/g, "\\'")
    .replace(/"/g, '\\"')
    .replace(/\r?\n/g, " ");
}

//=====================================================
// OBTENER NÚMERO DE REGISTRO
//=====================================================

function obtenerNumeroRegistro(index) {
  return (
    (paginaActualSucursales - 1) * registrosPorPaginaSucursales + index + 1
  );
}

//=====================================================
// EXPONER FUNCIONES GLOBALES
//=====================================================

window.abrirEditarSucursal = abrirEditarSucursal;

window.confirmarEliminarSucursal = confirmarEliminarSucursal;

window.reactivarSucursal = reactivarSucursal;
