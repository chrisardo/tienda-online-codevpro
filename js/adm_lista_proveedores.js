//=====================================================
// CoDevPro Technology
// Archivo: js/adm_lista_proveedores.js
// Módulo: Lista de Proveedores
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActualProveedores = 1;

// Mostrar 5 proveedores por página
const registrosPorPaginaProveedores = 5;

let temporizadorBusquedaProveedor = null;

let solicitudProveedoresActual = null;

let solicitudKPIActual = null;

let solicitudDepartamentosActual = null;

let solicitudProvinciasActual = null;

let solicitudDistritosActual = null;

let modalEditarProveedor = null;

//=====================================================
// CONFIGURACIÓN AJAX
//=====================================================

const AJAX_PROVEEDORES = "ajax/obtener_lista_proveedores.php";

const AJAX_KPI_PROVEEDORES = "ajax/obtener_kpi_proveedores.php";

const AJAX_ACTUALIZAR_PROVEEDOR = "ajax/actualizar_proveedor.php";

const AJAX_UBICACIONES = "ajax/ubicaciones_proveedor.php";

//=====================================================
// DOM READY
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarModuloProveedores();
  //=================================================
  // MODAL ACTUALIZAR IMAGEN
  //=================================================

  inicializarModalActualizarImagenProveedor();

  const btnGuardarImagenProveedor = document.getElementById(
    "btnGuardarImagenProveedor",
  );

  if (btnGuardarImagenProveedor) {
    btnGuardarImagenProveedor.addEventListener("click", guardarImagenProveedor);
  }
});

//=====================================================
// INICIALIZAR MÓDULO
//=====================================================

function inicializarModuloProveedores() {
  inicializarModalProveedor();

  inicializarFlatpickrProveedor();

  cargarDepartamentosProveedor();

  cargarKPIProveedores();

  cargarProveedores();

  configurarEventosProveedores();
}

//=====================================================
// CONFIGURAR EVENTOS
//=====================================================

function configurarEventosProveedores() {
  //=================================================
  // BUSCADOR
  //=================================================

  const buscarProveedor = document.getElementById("buscarProveedor");

  if (buscarProveedor) {
    buscarProveedor.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaProveedor);

      temporizadorBusquedaProveedor = setTimeout(function () {
        paginaActualProveedores = 1;

        cargarProveedores();
      }, 300);
    });
  }

  //=================================================
  // FILTRO ESTADO
  //=================================================

  const filtroEstado = document.getElementById("filtroEstadoProveedor");

  if (filtroEstado) {
    filtroEstado.addEventListener("change", function () {
      paginaActualProveedores = 1;

      cargarProveedores();
    });
  }

  //=================================================
  // FILTRO DEPARTAMENTO
  //=================================================

  const filtroDepartamento = document.getElementById(
    "filtroDepartamentoProveedor",
  );

  if (filtroDepartamento) {
    filtroDepartamento.addEventListener("change", function () {
      paginaActualProveedores = 1;

      cargarProveedores();
    });
  }

  //=================================================
  // FILTRO FECHA
  //=================================================

  const filtroFecha = document.getElementById("filtroFechaProveedor");

  if (filtroFecha) {
    filtroFecha.addEventListener("change", function () {
      paginaActualProveedores = 1;

      cargarProveedores();
    });
  }

  //=================================================
  // LIMPIAR FILTROS
  //=================================================

  const btnLimpiar = document.getElementById("btnLimpiarFiltrosProveedor");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", limpiarFiltrosProveedores);
  }

  //=================================================
  // FORMULARIO EDITAR
  //=================================================

  const formulario = document.getElementById("formEditarProveedor");

  if (formulario) {
    formulario.addEventListener("submit", actualizarProveedor);
  }

  //=================================================
  // DEPARTAMENTO DEL MODAL
  //=================================================

  const departamentoModal = document.getElementById(
    "editarProveedorDepartamento",
  );

  if (departamentoModal) {
    departamentoModal.addEventListener("change", function () {
      const idDepartamento = this.value;

      cargarProvinciasProveedor(idDepartamento, "editarProveedorProvincia");

      limpiarSelect("editarProveedorDistrito", "Seleccione un distrito");
    });
  }

  //=================================================
  // PROVINCIA DEL MODAL
  //=================================================

  const provinciaModal = document.getElementById("editarProveedorProvincia");

  if (provinciaModal) {
    provinciaModal.addEventListener("change", function () {
      const idProvincia = this.value;

      cargarDistritosProveedor(idProvincia, "editarProveedorDistrito");
    });
  }
}

//=====================================================
// FLATPICKR
//=====================================================

function inicializarFlatpickrProveedor() {
  const campoFecha = document.getElementById("filtroFechaProveedor");

  if (!campoFecha) {
    return;
  }

  flatpickr(campoFecha, {
    locale: "es",

    dateFormat: "Y-m-d",

    altInput: true,

    altFormat: "d/m/Y",

    allowInput: true,

    onChange: function () {
      paginaActualProveedores = 1;

      cargarProveedores();
    },
  });
}

//=====================================================
// INICIALIZAR MODAL
//=====================================================

function inicializarModalProveedor() {
  const elementoModal = document.getElementById("modalEditarProveedor");

  if (!elementoModal) {
    return;
  }

  modalEditarProveedor = new bootstrap.Modal(elementoModal);

  elementoModal.addEventListener("hidden.bs.modal", function () {
    limpiarFormularioProveedor();
  });
}

//=====================================================
// CARGAR KPI
//=====================================================

async function cargarKPIProveedores() {
  try {
    //=================================================
    // CANCELAR SOLICITUD ANTERIOR
    //=================================================

    if (solicitudKPIActual) {
      solicitudKPIActual.abort();
    }

    solicitudKPIActual = new AbortController();

    //=================================================
    // SOLICITUD
    //=================================================

    const respuesta = await fetch(AJAX_KPI_PROVEEDORES, {
      method: "GET",

      signal: solicitudKPIActual.signal,

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    //=================================================
    // VALIDAR HTTP
    //=================================================

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    //=================================================
    // OBTENER JSON
    //=================================================

    const datos = await respuesta.json();

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!datos.success) {
      throw new Error(datos.message || "No se pudieron obtener los KPI.");
    }

    //=================================================
    // ACTUALIZAR KPI
    //=================================================

    actualizarKPIProveedores(datos);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar KPI de proveedores:", error);
  }
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKPIProveedores(datos) {
  const total = document.getElementById("kpiTotalProveedores");

  const activos = document.getElementById("kpiProveedoresActivos");

  const inactivos = document.getElementById("kpiProveedoresInactivos");

  const productos = document.getElementById("kpiProveedoresProductos");

  //=================================================
  // TOTAL
  //=================================================

  if (total) {
    total.textContent = formatearNumero(datos.total || 0);
  }

  //=================================================
  // ACTIVOS
  //=================================================

  if (activos) {
    activos.textContent = formatearNumero(datos.activos || 0);
  }

  //=================================================
  // INACTIVOS
  //=================================================

  if (inactivos) {
    inactivos.textContent = formatearNumero(datos.inactivos || 0);
  }

  //=================================================
  // CON PRODUCTOS
  //=================================================

  if (productos) {
    productos.textContent = formatearNumero(datos.con_productos || 0);
  }
}

//=====================================================
// CARGAR PROVEEDORES
//=====================================================

async function cargarProveedores() {
  const tabla = document.getElementById("tablaProveedores");

  if (!tabla) {
    return;
  }

  //=================================================
  // MOSTRAR CARGA
  //=================================================

  mostrarCargaProveedores();

  try {
    //=================================================
    // CANCELAR SOLICITUD ANTERIOR
    //=================================================

    if (solicitudProveedoresActual) {
      solicitudProveedoresActual.abort();
    }

    solicitudProveedoresActual = new AbortController();

    //=================================================
    // PARÁMETROS
    //=================================================

    const parametros = new URLSearchParams();

    parametros.append("accion", "listar");

    parametros.append("pagina", paginaActualProveedores);

    parametros.append("limite", registrosPorPaginaProveedores);

    parametros.append("buscar", obtenerValor("buscarProveedor"));

    parametros.append("estado", obtenerValor("filtroEstadoProveedor"));

    parametros.append(
      "departamento",
      obtenerValor("filtroDepartamentoProveedor"),
    );

    parametros.append("fecha", obtenerValor("filtroFechaProveedor"));

    //=================================================
    // SOLICITUD
    //=================================================

    const respuesta = await fetch(
      AJAX_PROVEEDORES + "?" + parametros.toString(),
      {
        method: "GET",

        signal: solicitudProveedoresActual.signal,

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    //=================================================
    // VALIDAR HTTP
    //=================================================

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    //=================================================
    // JSON
    //=================================================

    const datos = await respuesta.json();

    //=================================================
    // VALIDAR
    //=================================================

    if (!datos.success) {
      throw new Error(
        datos.message || "No se pudieron cargar los proveedores.",
      );
    }

    //=================================================
    // RENDERIZAR
    //=================================================

    renderizarProveedores(datos.proveedores || []);

    renderizarPaginacionProveedores(datos.total || 0, datos.paginas || 1);

    actualizarInformacionPaginacion(
      datos.total || 0,
      datos.desde || 0,
      datos.hasta || 0,
    );

    actualizarTextoResultados(datos.total || 0);
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar proveedores:", error);

    mostrarErrorProveedores("No se pudieron cargar los proveedores.");
  }
}

//=====================================================
// RENDERIZAR PROVEEDORES
//=====================================================

function renderizarProveedores(proveedores) {
  const tabla = document.getElementById("tablaProveedores");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = "";

  //=================================================
  // SIN RESULTADOS
  //=================================================

  if (!Array.isArray(proveedores) || proveedores.length === 0) {
    tabla.innerHTML = `

      <tr>

        <td
          colspan="7"
          class="text-center py-5">

          <div class="mb-3">

            <i
              class="bi bi-person-badge fs-1 text-muted">
            </i>

          </div>

          <h6 class="fw-semibold mb-1">

            No se encontraron proveedores

          </h6>

          <p class="text-muted mb-0">

            No existen proveedores que coincidan
            con los filtros seleccionados.

          </p>

        </td>

      </tr>

    `;

    return;
  }

  //=================================================
  // CREAR FILAS
  //=================================================

  proveedores.forEach(function (proveedor) {
    tabla.appendChild(crearFilaProveedor(proveedor));
  });
}

//=====================================================
// CREAR FILA
//=====================================================

function crearFilaProveedor(proveedor) {
  const fila = document.createElement("tr");

  const activo = Number(proveedor.Eliminado) === 0;

  const iniciales = obtenerIniciales(proveedor.nombre);

  //=================================================
  // ESTADO
  //=================================================

  const estadoHTML = activo
    ? `

        <span
          class="badge rounded-pill text-bg-success">

          <i
            class="bi bi-check-circle me-1">
          </i>

          Activo

        </span>

      `
    : `

        <span
          class="badge rounded-pill text-bg-secondary">

          <i
            class="bi bi-pause-circle me-1">
          </i>

          Inactivo

        </span>

      `;
  //=================================================
  // CONTACTO
  //=================================================

  const telefono = proveedor.celular
    ? String(proveedor.celular).trim()
    : "Sin teléfono";

  const email = proveedor.email ? String(proveedor.email).trim() : "Sin correo";

  const telefonoWhatsApp = obtenerNumeroWhatsApp(telefono);

  const emailHref = email ? `mailto:${encodeURIComponent(email)}` : "";

  //=================================================
  // UBICACIÓN
  //=================================================

  const ubicacion = construirUbicacion(proveedor);

  //=================================================
  // HTML
  //=================================================

  fila.innerHTML = `

    <td class="ps-4">

      <div
        class="d-flex align-items-center gap-3">

        <div
          class="adm-proveedor-avatar">

          ${escapeHTML(iniciales)}

        </div>

        <div>

          <div
            class="fw-semibold text-dark">

            ${escapeHTML(proveedor.nombre || "Sin nombre")}

          </div>

          <small
            class="text-muted">

            ${escapeHTML(proveedor.email || "Sin correo")}

          </small>

        </div>

      </div>

    </td>


    <td>

      <span
        class="fw-semibold">

        ${escapeHTML(proveedor.ruc || "Sin RUC")}

      </span>

    </td>


    <td>

      <div>

        <!--=================================================
            WHATSAPP
        =================================================-->

        ${
          telefono
            ? `
              <a
                href="https://wa.me/${telefonoWhatsApp}"
                target="_blank"
                rel="noopener noreferrer"
                class="text-decoration-none fw-semibold d-flex align-items-center gap-1"
                title="Enviar mensaje por WhatsApp">

                <i
                  class="bi bi-whatsapp text-success">
                </i>

                <span>
                  ${escapeHTML(telefono)}
                </span>

              </a>
            `
            : `
              <span
                class="text-muted d-block">

                <i
                  class="bi bi-telephone me-1">
                </i>

                Sin teléfono

              </span>
            `
        }


        <!--=================================================
            CORREO
        =================================================-->

        ${
          email
            ? `
              <a
                href="${emailHref}"
                class="text-decoration-none small d-flex align-items-center gap-1 mt-1"
                title="Enviar correo electrónico">

                <i
                  class="bi bi-envelope text-primary">
                </i>

                <span>
                  ${escapeHTML(email)}
                </span>

              </a>
            `
            : `
              <small
                class="text-muted d-block mt-1">

                <i
                  class="bi bi-envelope me-1">
                </i>

                Sin correo

              </small>
            `
        }

      </div>

    </td>


    <td>

      <span
        class="small">

        <i
          class="bi bi-geo-alt me-1 text-muted">
        </i>

        ${escapeHTML(ubicacion)}

      </span>

    </td>


    <td>

      <span
        class="small">

        ${formatearFecha(proveedor.fecha_registro)}

      </span>

    </td>


    <td>

      ${estadoHTML}

    </td>


    <td
      class="text-end pe-4">

      <div
        class="d-inline-flex gap-1">

        <button
          type="button"
          class="btn btn-sm btn-outline-primary"
          title="Editar proveedor"
          onclick="abrirEditarProveedor(${Number(proveedor.id_provedor)})">

          <i
            class="bi bi-pencil-square">
          </i>

        </button>
        <button
    type="button"
    class="btn btn-sm btn-outline-secondary"
    title="Actualizar imagen del proveedor"
    onclick="abrirActualizarImagenProveedor(${Number(proveedor.id_provedor)})">

    <i
      class="bi bi-image">
    </i>

  </button>

        <button
          type="button"
          class="btn btn-sm ${
            activo ? "btn-outline-warning" : "btn-outline-success"
          }"
          title="${activo ? "Desactivar proveedor" : "Activar proveedor"}"
          onclick="cambiarEstadoProveedor(
            ${Number(proveedor.id_provedor)},
            ${activo ? 1 : 0},
            '${escapeAtributo(proveedor.nombre || "")}'
          )">

          <i
            class="bi ${activo ? "bi-person-dash" : "bi-person-check"}">
          </i>

        </button>

      </div>

    </td>

  `;

  return fila;
}

//=====================================================
// ABRIR MODAL EDITAR
//=====================================================

async function abrirEditarProveedor(idProveedor) {
  if (!idProveedor) {
    return;
  }

  try {
    mostrarCargandoModal();

    const respuesta = await fetch(
      AJAX_PROVEEDORES +
        "?accion=obtener&id_provedor=" +
        encodeURIComponent(idProveedor),
      {
        method: "GET",

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success || !datos.proveedor) {
      throw new Error(datos.message || "No se pudo obtener el proveedor.");
    }

    await llenarFormularioProveedor(datos.proveedor);

    if (modalEditarProveedor) {
      modalEditarProveedor.show();
    }
  } catch (error) {
    console.error("Error al obtener proveedor:", error);

    Swal.fire({
      icon: "error",

      title: "Error",

      text: error.message || "No se pudo cargar la información del proveedor.",

      confirmButtonText: "Aceptar",
    });
  }
}

//=====================================================
// LLENAR FORMULARIO
//=====================================================

async function llenarFormularioProveedor(proveedor) {
  //=================================================
  // DATOS GENERALES
  //=================================================

  establecerValor("editarProveedorId", proveedor.id_provedor);

  establecerValor("editarProveedorNombre", proveedor.nombre);

  establecerValor("editarProveedorRuc", proveedor.ruc);

  establecerValor("editarProveedorCelular", proveedor.celular);

  establecerValor("editarProveedorEmail", proveedor.email);

  establecerValor("editarProveedorDireccion", proveedor.direccion);

  //=================================================
  // PAÍS
  //=================================================

  await cargarPaisesEditar();

  establecerValor("editarProveedorPais", proveedor.id_pais);

  //=================================================
  // DEPARTAMENTO
  //=================================================

  await cargarDepartamentosProveedor(
    proveedor.id_pais,
    "editarProveedorDepartamento",
  );

  establecerValor("editarProveedorDepartamento", proveedor.id_departamento);

  //=================================================
  // PROVINCIA
  //=================================================

  await cargarProvinciasProveedor(
    proveedor.id_departamento,
    "editarProveedorProvincia",
  );

  establecerValor("editarProveedorProvincia", proveedor.id_provincia);

  //=================================================
  // DISTRITO
  //=================================================

  await cargarDistritosProveedor(
    proveedor.id_provincia,
    "editarProveedorDistrito",
  );

  establecerValor("editarProveedorDistrito", proveedor.id_distrito);

  //=================================================
  // TEXTO MODAL
  //=================================================

  establecerTexto(
    "textoModalProveedor",
    "Edita los datos del proveedor seleccionado.",
  );

  //=================================================
  // BOTÓN
  //=================================================

  const botonGuardar = document.getElementById("btnGuardarCambiosProveedor");

  if (botonGuardar) {
    botonGuardar.disabled = false;

    botonGuardar.innerHTML = `
            <i class="bi bi-check-lg me-1"></i>
            Guardar cambios
        `;
  }
}

//=====================================================
// CARGAR PAÍSES
//=====================================================

async function cargarPaisesEditar() {
  const select = document.getElementById("editarProveedorPais");

  if (!select) {
    return;
  }

  try {
    select.disabled = true;

    const respuesta = await fetch("ajax/adm_obtener_pais.php", {
      method: "GET",
      cache: "no-store",
      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.estado) {
      throw new Error(datos.mensaje || "No se pudieron cargar los países.");
    }

    //=================================================
    // LIMPIAR SELECT
    //=================================================

    select.innerHTML = "";

    //=================================================
    // OPCIÓN INICIAL
    //=================================================

    const opcionInicial = document.createElement("option");

    opcionInicial.value = "";

    opcionInicial.textContent = "Seleccione un país";

    select.appendChild(opcionInicial);

    //=================================================
    // CARGAR PAÍSES
    //=================================================

    datos.data.forEach(function (pais) {
      const option = document.createElement("option");

      option.value = pais.id_pais;

      option.textContent = pais.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    console.error("Error al cargar países:", error);

    select.innerHTML = `
            <option value="">
                No se pudieron cargar los países
            </option>
        `;
  } finally {
    select.disabled = false;
  }
}
//=====================================================
// CAMBIO DE PAÍS - EDITAR PROVEEDOR
//=====================================================

const selectPaisProveedor = document.getElementById("editarProveedorPais");

if (selectPaisProveedor) {
  selectPaisProveedor.addEventListener("change", async function () {
    const idPais = this.value;

    const selectDepartamento = document.getElementById(
      "editarProveedorDepartamento",
    );

    const selectProvincia = document.getElementById("editarProveedorProvincia");

    const selectDistrito = document.getElementById("editarProveedorDistrito");

    //=================================================
    // LIMPIAR PROVINCIA
    //=================================================

    if (selectProvincia) {
      selectProvincia.innerHTML = `
                    <option value="">
                        Seleccione una provincia
                    </option>
                `;

      selectProvincia.disabled = true;
    }

    //=================================================
    // LIMPIAR DISTRITO
    //=================================================

    if (selectDistrito) {
      selectDistrito.innerHTML = `
                    <option value="">
                        Seleccione un distrito
                    </option>
                `;

      selectDistrito.disabled = true;
    }

    //=================================================
    // SIN PAÍS
    //=================================================

    if (!idPais) {
      if (selectDepartamento) {
        selectDepartamento.innerHTML = `
                        <option value="">
                            Seleccione un departamento
                        </option>
                    `;

        selectDepartamento.disabled = true;
      }

      return;
    }

    //=================================================
    // CARGAR DEPARTAMENTOS
    //=================================================

    await cargarDepartamentosProveedor(idPais, "editarProveedorDepartamento");
  });
}
//=====================================================
// CARGAR DEPARTAMENTOS
//=====================================================

async function cargarDepartamentosProveedor(
  idPais = "",
  idSelect = "filtroDepartamentoProveedor",
) {
  const select = document.getElementById(idSelect);

  if (!select) {
    return;
  }

  try {
    //=================================================
    // CANCELAR PETICIÓN ANTERIOR
    //=================================================

    if (solicitudDepartamentosActual) {
      solicitudDepartamentosActual.abort();
    }

    solicitudDepartamentosActual = new AbortController();

    select.disabled = true;

    //=================================================
    // PARÁMETROS
    //=================================================

    const parametros = new URLSearchParams();

    parametros.append("accion", "departamentos");

    if (idPais) {
      parametros.append("id_pais", idPais);
    }

    //=================================================
    // SOLICITUD
    //=================================================

    const respuesta = await fetch(
      AJAX_UBICACIONES + "?" + parametros.toString(),
      {
        method: "GET",

        signal: solicitudDepartamentosActual.signal,

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(
        datos.message || "No se pudieron cargar los departamentos.",
      );
    }

    //=================================================
    // TEXTO INICIAL
    //=================================================

    const textoInicial =
      idSelect === "filtroDepartamentoProveedor"
        ? "Todos los departamentos"
        : "Seleccione un departamento";

    select.innerHTML = `

      <option value="">

        ${textoInicial}

      </option>

    `;

    //=================================================
    // OPCIONES
    //=================================================

    (datos.departamentos || []).forEach(function (departamento) {
      const option = document.createElement("option");

      option.value = departamento.id_departamento;

      option.textContent = departamento.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar departamentos:", error);
  } finally {
    select.disabled = false;
  }
}

//=====================================================
// CARGAR PROVINCIAS
//=====================================================

async function cargarProvinciasProveedor(
  idDepartamento,
  idSelect = "editarProveedorProvincia",
) {
  const select = document.getElementById(idSelect);

  if (!select) {
    return;
  }

  limpiarSelect(idSelect, "Seleccione una provincia");

  if (!idDepartamento) {
    select.disabled = true;

    return;
  }

  try {
    if (solicitudProvinciasActual) {
      solicitudProvinciasActual.abort();
    }

    solicitudProvinciasActual = new AbortController();

    select.disabled = true;

    const respuesta = await fetch(
      AJAX_UBICACIONES +
        "?accion=provincias&id_departamento=" +
        encodeURIComponent(idDepartamento),
      {
        method: "GET",

        signal: solicitudProvinciasActual.signal,

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.message || "No se pudieron cargar las provincias.");
    }

    (datos.provincias || []).forEach(function (provincia) {
      const option = document.createElement("option");

      option.value = provincia.id_provincia;

      option.textContent = provincia.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar provincias:", error);
  } finally {
    select.disabled = false;
  }
}

//=====================================================
// CARGAR DISTRITOS
//=====================================================

async function cargarDistritosProveedor(
  idProvincia,
  idSelect = "editarProveedorDistrito",
) {
  const select = document.getElementById(idSelect);

  if (!select) {
    return;
  }

  limpiarSelect(idSelect, "Seleccione un distrito");

  if (!idProvincia) {
    select.disabled = true;

    return;
  }

  try {
    if (solicitudDistritosActual) {
      solicitudDistritosActual.abort();
    }

    solicitudDistritosActual = new AbortController();

    select.disabled = true;

    const respuesta = await fetch(
      AJAX_UBICACIONES +
        "?accion=distritos&id_provincia=" +
        encodeURIComponent(idProvincia),
      {
        method: "GET",

        signal: solicitudDistritosActual.signal,

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.message || "No se pudieron cargar los distritos.");
    }

    (datos.distritos || []).forEach(function (distrito) {
      const option = document.createElement("option");

      option.value = distrito.id_distrito;

      option.textContent = distrito.nombre;

      select.appendChild(option);
    });
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar distritos:", error);
  } finally {
    select.disabled = false;
  }
}

//=====================================================
// ACTUALIZAR PROVEEDOR
//=====================================================

async function actualizarProveedor(evento) {
  evento.preventDefault();

  const formulario = evento.target;

  const idProveedor = obtenerValor("editarProveedorId");

  //=================================================
  // VALIDAR ID
  //=================================================

  if (!idProveedor) {
    Swal.fire({
      icon: "error",

      title: "Proveedor inválido",

      text: "No se pudo identificar el proveedor.",

      confirmButtonText: "Aceptar",
    });

    return;
  }

  //=================================================
  // VALIDAR FORMULARIO
  //=================================================

  if (!validarFormularioProveedor()) {
    return;
  }

  //=================================================
  // BOTÓN
  //=================================================

  const boton = document.getElementById("btnGuardarCambiosProveedor");

  const textoOriginal = boton ? boton.innerHTML : "";

  try {
    if (boton) {
      boton.disabled = true;

      boton.innerHTML = `

        <span
          class="spinner-border spinner-border-sm me-2">
        </span>

        Guardando...

      `;
    }

    //=================================================
    // FORM DATA
    //=================================================

    const formData = new FormData(formulario);

    //=================================================
    // SOLICITUD
    //=================================================

    const respuesta = await fetch(AJAX_ACTUALIZAR_PROVEEDOR, {
      method: "POST",

      body: formData,

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.message || "No se pudo actualizar el proveedor.");
    }

    //=================================================
    // CERRAR MODAL
    //=================================================

    if (modalEditarProveedor) {
      modalEditarProveedor.hide();
    }

    //=================================================
    // MENSAJE
    //=================================================

    await Swal.fire({
      icon: "success",

      title: "Proveedor actualizado",

      text:
        datos.message ||
        "Los datos del proveedor fueron actualizados correctamente.",

      timer: 1800,

      showConfirmButton: false,
    });

    //=================================================
    // ACTUALIZAR TABLA
    //=================================================

    await cargarProveedores();

    //=================================================
    // ACTUALIZAR KPI
    //=================================================

    await cargarKPIProveedores();
  } catch (error) {
    console.error("Error al actualizar proveedor:", error);

    Swal.fire({
      icon: "error",

      title: "No se pudo actualizar",

      text: error.message || "Ocurrió un error al actualizar el proveedor.",

      confirmButtonText: "Aceptar",
    });
  } finally {
    if (boton) {
      boton.disabled = false;

      boton.innerHTML = textoOriginal;
    }
  }
}

//=====================================================
// VALIDAR FORMULARIO
//=====================================================

function validarFormularioProveedor() {
  const nombre = obtenerValor("editarProveedorNombre").trim();

  const ruc = obtenerValor("editarProveedorRuc").trim();

  const celular = obtenerValor("editarProveedorCelular").trim();

  const email = obtenerValor("editarProveedorEmail").trim();

  const direccion = obtenerValor("editarProveedorDireccion").trim();

  //=================================================
  // NOMBRE
  //=================================================

  if (!nombre) {
    mostrarValidacion(
      "editarProveedorNombre",
      "Ingresa el nombre del proveedor.",
    );

    return false;
  }

  //=================================================
  // RUC
  //=================================================

  if (!ruc) {
    mostrarValidacion("editarProveedorRuc", "Ingresa el RUC del proveedor.");

    return false;
  }

  if (ruc.length !== 11) {
    mostrarValidacion("editarProveedorRuc", "El RUC debe contener 11 dígitos.");

    return false;
  }

  if (!/^\d{11}$/.test(ruc)) {
    mostrarValidacion(
      "editarProveedorRuc",
      "El RUC solo debe contener números.",
    );

    return false;
  }

  //=================================================
  // CELULAR
  //=================================================

  if (!celular) {
    mostrarValidacion(
      "editarProveedorCelular",
      "Ingresa el celular del proveedor.",
    );

    return false;
  }

  if (!/^\d+$/.test(celular)) {
    mostrarValidacion(
      "editarProveedorCelular",
      "El celular solo debe contener números.",
    );

    return false;
  }

  //=================================================
  // EMAIL
  //=================================================

  if (!email) {
    mostrarValidacion(
      "editarProveedorEmail",
      "Ingresa el correo del proveedor.",
    );

    return false;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    mostrarValidacion(
      "editarProveedorEmail",
      "Ingresa un correo electrónico válido.",
    );

    return false;
  }

  //=================================================
  // DIRECCIÓN
  //=================================================

  if (!direccion) {
    mostrarValidacion(
      "editarProveedorDireccion",
      "Ingresa la dirección del proveedor.",
    );

    return false;
  }

  return true;
}

//=====================================================
// CAMBIAR ESTADO
//=====================================================

async function cambiarEstadoProveedor(
  idProveedor,
  nuevoEliminado,
  nombreProveedor,
) {
  const activar = Number(nuevoEliminado) === 0;

  const titulo = activar ? "¿Activar proveedor?" : "¿Desactivar proveedor?";

  const texto = activar
    ? `El proveedor "${nombreProveedor}" volverá a estar activo.`
    : `El proveedor "${nombreProveedor}" quedará inactivo y no aparecerá como proveedor disponible.`;

  //=================================================
  // CONFIRMACIÓN
  //=================================================

  const confirmacion = await Swal.fire({
    icon: activar ? "question" : "warning",

    title: titulo,

    text: texto,

    showCancelButton: true,

    confirmButtonText: activar ? "Sí, activar" : "Sí, desactivar",

    cancelButtonText: "Cancelar",

    reverseButtons: true,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  try {
    //=================================================
    // FORM DATA
    //=================================================

    const formData = new FormData();

    formData.append("id_provedor", idProveedor);

    formData.append("Eliminado", nuevoEliminado);

    //=================================================
    // SOLICITUD
    //=================================================

    const respuesta = await fetch(
      AJAX_ACTUALIZAR_PROVEEDOR + "?accion=estado",
      {
        method: "POST",

        body: formData,

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success) {
      throw new Error(datos.message || "No se pudo cambiar el estado.");
    }

    //=================================================
    // MENSAJE
    //=================================================

    await Swal.fire({
      icon: "success",

      title: activar ? "Proveedor activado" : "Proveedor desactivado",

      text: datos.message || "El estado fue actualizado correctamente.",

      timer: 1600,

      showConfirmButton: false,
    });

    //=================================================
    // ACTUALIZAR TABLA
    //=================================================

    await cargarProveedores();

    //=================================================
    // ACTUALIZAR KPI
    //=================================================

    await cargarKPIProveedores();
  } catch (error) {
    console.error("Error al cambiar estado:", error);

    Swal.fire({
      icon: "error",

      title: "Error",

      text: error.message || "No se pudo cambiar el estado del proveedor.",

      confirmButtonText: "Aceptar",
    });
  }
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosProveedores() {
  const buscar = document.getElementById("buscarProveedor");

  const estado = document.getElementById("filtroEstadoProveedor");

  const departamento = document.getElementById("filtroDepartamentoProveedor");

  const fecha = document.getElementById("filtroFechaProveedor");

  if (buscar) {
    buscar.value = "";
  }

  if (estado) {
    estado.value = "todos";
  }

  if (departamento) {
    departamento.value = "";
  }

  if (fecha) {
    if (fecha._flatpickr) {
      fecha._flatpickr.clear();
    } else {
      fecha.value = "";
    }
  }

  paginaActualProveedores = 1;

  cargarProveedores();
}

//=====================================================
// PAGINACIÓN
//=====================================================

function renderizarPaginacionProveedores(total, totalPaginas) {
  const contenedor = document.getElementById("paginacionProveedores");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = "";

  if (totalPaginas <= 1) {
    return;
  }

  const pagina = paginaActualProveedores;

  //=================================================
  // ANTERIOR
  //=================================================

  const liAnterior = document.createElement("li");

  liAnterior.className = "page-item" + (pagina <= 1 ? " disabled" : "");

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

  liAnterior.querySelector("a").addEventListener("click", function (evento) {
    evento.preventDefault();

    if (pagina > 1) {
      paginaActualProveedores = pagina - 1;

      cargarProveedores();
    }
  });

  contenedor.appendChild(liAnterior);

  //=================================================
  // PÁGINAS
  //=================================================

  const rango = obtenerRangoPaginas(pagina, totalPaginas);

  rango.forEach(function (numeroPagina) {
    const li = document.createElement("li");

    li.className = "page-item" + (numeroPagina === pagina ? " active" : "");

    li.innerHTML = `

        <a
          class="page-link"
          href="#">

          ${numeroPagina}

        </a>

      `;

    li.querySelector("a").addEventListener("click", function (evento) {
      evento.preventDefault();

      paginaActualProveedores = numeroPagina;

      cargarProveedores();
    });

    contenedor.appendChild(li);
  });

  //=================================================
  // SIGUIENTE
  //=================================================

  const liSiguiente = document.createElement("li");

  liSiguiente.className =
    "page-item" + (pagina >= totalPaginas ? " disabled" : "");

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

  liSiguiente.querySelector("a").addEventListener("click", function (evento) {
    evento.preventDefault();

    if (pagina < totalPaginas) {
      paginaActualProveedores = pagina + 1;

      cargarProveedores();
    }
  });

  contenedor.appendChild(liSiguiente);
}

//=====================================================
// RANGO DE PÁGINAS
//=====================================================

function obtenerRangoPaginas(paginaActual, totalPaginas) {
  const paginas = [];

  let inicio = Math.max(1, paginaActual - 2);

  let fin = Math.min(totalPaginas, paginaActual + 2);

  if (paginaActual <= 2) {
    fin = Math.min(totalPaginas, 5);
  }

  if (paginaActual >= totalPaginas - 1) {
    inicio = Math.max(1, totalPaginas - 4);
  }

  for (let i = inicio; i <= fin; i++) {
    paginas.push(i);
  }

  return paginas;
}

//=====================================================
// INFORMACIÓN PAGINACIÓN
//=====================================================

function actualizarInformacionPaginacion(total, desde, hasta) {
  const elemento = document.getElementById("infoPaginacionProveedores");

  if (!elemento) {
    return;
  }

  if (!total) {
    elemento.textContent = "Mostrando 0 de 0 proveedores";

    return;
  }

  elemento.textContent = `Mostrando ${formatearNumero(desde)} - ${formatearNumero(hasta)} de ${formatearNumero(total)} proveedores`;
}

//=====================================================
// TEXTO RESULTADOS
//=====================================================

function actualizarTextoResultados(total) {
  const elemento = document.getElementById("textoResultadosProveedores");

  if (!elemento) {
    return;
  }

  if (total === 0) {
    elemento.textContent = "No se encontraron proveedores";

    return;
  }

  elemento.textContent = `${formatearNumero(total)} proveedor${
    total === 1 ? "" : "es"
  } encontrado${total === 1 ? "" : "s"}`;
}

//=====================================================
// MOSTRAR CARGA
//=====================================================

function mostrarCargaProveedores() {
  const tabla = document.getElementById("tablaProveedores");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `

    <tr>

      <td
        colspan="7"
        class="text-center py-5">

        <div
          class="spinner-border text-primary mb-3"
          role="status">

          <span
            class="visually-hidden">

            Cargando...

          </span>

        </div>

        <div class="text-muted">

          Cargando proveedores...

        </div>

      </td>

    </tr>

  `;
}

//=====================================================
// ERROR TABLA
//=====================================================

function mostrarErrorProveedores(mensaje) {
  const tabla = document.getElementById("tablaProveedores");

  if (!tabla) {
    return;
  }

  tabla.innerHTML = `

    <tr>

      <td
        colspan="7"
        class="text-center py-5">

        <div class="mb-3">

          <i
            class="bi bi-exclamation-triangle-fill fs-1 text-danger">
          </i>

        </div>

        <h6
          class="fw-semibold">

          No se pudo cargar la información

        </h6>

        <p
          class="text-muted mb-3">

          ${escapeHTML(mensaje)}

        </p>

        <button
          type="button"
          class="btn btn-sm btn-primary"
          onclick="cargarProveedores()">

          <i
            class="bi bi-arrow-clockwise me-1">
          </i>

          Reintentar

        </button>

      </td>

    </tr>

  `;
}

//=====================================================
// CARGANDO MODAL
//=====================================================

function mostrarCargandoModal() {
  const boton = document.getElementById("btnGuardarCambiosProveedor");

  if (boton) {
    boton.disabled = true;

    boton.innerHTML = `

      <span
        class="spinner-border spinner-border-sm me-2">
      </span>

      Cargando...

    `;
  }
}

//=====================================================
// LIMPIAR FORMULARIO
//=====================================================

function limpiarFormularioProveedor() {
  const formulario = document.getElementById("formEditarProveedor");

  if (!formulario) {
    return;
  }

  formulario.reset();

  limpiarSelect("editarProveedorDepartamento", "Seleccione un departamento");

  limpiarSelect("editarProveedorProvincia", "Seleccione una provincia");

  limpiarSelect("editarProveedorDistrito", "Seleccione un distrito");

  const boton = document.getElementById("btnGuardarCambiosProveedor");

  if (boton) {
    boton.disabled = false;

    boton.innerHTML = `

      <i class="bi bi-check-lg me-1"></i>

      Guardar cambios

    `;
  }
}

//=====================================================
// HELPERS
//=====================================================

function obtenerValor(id) {
  const elemento = document.getElementById(id);

  return elemento ? elemento.value : "";
}

//=====================================================
// ESTABLECER VALOR
//=====================================================

function establecerValor(id, valor) {
  const elemento = document.getElementById(id);

  if (elemento) {
    elemento.value = valor ?? "";
  }
}

//=====================================================
// ESTABLECER TEXTO
//=====================================================

function establecerTexto(id, texto) {
  const elemento = document.getElementById(id);

  if (elemento) {
    elemento.textContent = texto;
  }
}

//=====================================================
// LIMPIAR SELECT
//=====================================================

function limpiarSelect(id, texto) {
  const select = document.getElementById(id);

  if (!select) {
    return;
  }

  select.innerHTML = `

    <option value="">

      ${texto}

    </option>

  `;

  select.disabled = true;
}

//=====================================================
// MOSTRAR VALIDACIÓN
//=====================================================

function mostrarValidacion(id, mensaje) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  elemento.focus();

  Swal.fire({
    icon: "warning",

    title: "Dato incompleto",

    text: mensaje,

    confirmButtonText: "Aceptar",
  });
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(numero) {
  return Number(numero || 0).toLocaleString("es-PE");
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "Sin fecha";
  }

  const partes = String(fecha).split("-");

  if (partes.length !== 3) {
    return fecha;
  }

  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

//=====================================================
// CONSTRUIR UBICACIÓN
//=====================================================

function construirUbicacion(proveedor) {
  const partes = [];

  if (proveedor.distrito_nombre) {
    partes.push(proveedor.distrito_nombre);
  }

  if (proveedor.provincia_nombre) {
    partes.push(proveedor.provincia_nombre);
  }

  if (proveedor.departamento_nombre) {
    partes.push(proveedor.departamento_nombre);
  }

  if (partes.length === 0 && proveedor.pais_nombre) {
    partes.push(proveedor.pais_nombre);
  }

  return partes.length ? partes.join(", ") : "Sin ubicación";
}
//=====================================================
// OBTENER NÚMERO PARA WHATSAPP
//=====================================================

function obtenerNumeroWhatsApp(telefono) {
  if (!telefono) {
    return "";
  }

  // Eliminar espacios, guiones, paréntesis y cualquier
  // carácter que no sea numérico.
  let numero = String(telefono).replace(/\D/g, "");

  //=================================================
  // PERÚ
  //=================================================
  // Si el número tiene 9 dígitos:
  // 987654321
  //
  // Se convierte en:
  // 51987654321

  if (numero.length === 9) {
    numero = "51" + numero;
  }

  //=================================================
  // SI YA TIENE CÓDIGO DE PAÍS
  //=================================================

  if (numero.length === 11 && numero.startsWith("51")) {
    return numero;
  }

  //=================================================
  // DEVOLVER NÚMERO LIMPIO
  //=================================================

  return numero;
}
//=====================================================
// OBTENER INICIALES
//=====================================================

function obtenerIniciales(nombre) {
  if (!nombre) {
    return "P";
  }

  const palabras = String(nombre).trim().split(/\s+/).filter(Boolean);

  if (palabras.length === 1) {
    return palabras[0].substring(0, 2).toUpperCase();
  }

  return (palabras[0].charAt(0) + palabras[1].charAt(0)).toUpperCase();
}

//=====================================================
// ESCAPE HTML
//=====================================================

function escapeHTML(texto) {
  return String(texto ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

//=====================================================
// ESCAPE ATRIBUTO
//=====================================================

function escapeAtributo(texto) {
  return String(texto ?? "")
    .replace(/\\/g, "\\\\")
    .replace(/'/g, "\\'");
}

//=====================================================
// EXPONER FUNCIONES NECESARIAS AL HTML
//=====================================================

window.abrirEditarProveedor = abrirEditarProveedor;

window.cambiarEstadoProveedor = cambiarEstadoProveedor;

window.cargarProveedores = cargarProveedores;

window.cargarKPIProveedores = cargarKPIProveedores;
//=====================================================
// MODAL ACTUALIZAR IMAGEN PROVEEDOR
//=====================================================

let modalActualizarImagenProveedor = null;

const MAX_TAMANO_IMAGEN_PROVEEDOR = 2.7 * 1024 * 1024;

const TIPOS_IMAGEN_PROVEEDOR_PERMITIDOS = [
  "image/jpeg",
  "image/jpg",
  "image/png",
  "image/webp",
];
//=====================================================
// IMAGEN POR DEFECTO DEL PROVEEDOR
//=====================================================

const IMAGEN_PROVEEDOR_DEFAULT = "img/proveedor_default.png";
//=====================================================
// INICIALIZAR MODAL ACTUALIZAR IMAGEN
//=====================================================

function inicializarModalActualizarImagenProveedor() {
  const elementoModal = document.getElementById(
    "modalActualizarImagenProveedor",
  );

  if (!elementoModal) {
    console.warn("No se encontró el modal #modalActualizarImagenProveedor");

    return;
  }

  //===================================================
  // CREAR INSTANCIA BOOTSTRAP
  //===================================================

  modalActualizarImagenProveedor =
    bootstrap.Modal.getOrCreateInstance(elementoModal);

  //===================================================
  // BOTÓN SELECCIONAR IMAGEN
  //===================================================

  const btnSeleccionar = document.getElementById(
    "btnSeleccionarImagenProveedor",
  );

  const inputImagen = document.getElementById("imagenProveedor");

  if (btnSeleccionar && inputImagen) {
    btnSeleccionar.addEventListener("click", function () {
      inputImagen.click();
    });
  }

  //===================================================
  // CAMBIO DE IMAGEN
  //===================================================

  if (inputImagen) {
    inputImagen.addEventListener("change", manejarSeleccionImagenProveedor);
  }

  //===================================================
  // BOTÓN ELIMINAR
  //===================================================

  const btnEliminar = document.getElementById("btnEliminarImagenProveedor");

  if (btnEliminar) {
    btnEliminar.addEventListener("click", eliminarVistaPreviaImagenProveedor);
  }

  //===================================================
  // FORMULARIO
  //===================================================

  const formulario = document.getElementById("formActualizarImagenProveedor");

  if (formulario) {
    formulario.addEventListener("submit", guardarImagenProveedor);
  }

  //===================================================
  // LIMPIAR AL CERRAR
  //===================================================

  elementoModal.addEventListener("hidden.bs.modal", function () {
    limpiarModalActualizarImagenProveedor();
  });
}

//=====================================================
// ABRIR MODAL ACTUALIZAR IMAGEN
//=====================================================

async function abrirActualizarImagenProveedor(idProveedor) {
  if (!idProveedor || Number(idProveedor) <= 0) {
    mostrarAlertaProveedor(
      "Proveedor inválido",
      "No se pudo identificar el proveedor.",
      "error",
    );

    return;
  }

  //===================================================
  // ASEGURAR MODAL INICIALIZADO
  //===================================================

  if (!modalActualizarImagenProveedor) {
    inicializarModalActualizarImagenProveedor();
  }

  //===================================================
  // ELEMENTOS
  //===================================================

  const inputId = document.getElementById("actualizarImagenProveedorId");

  const nombreProveedor = document.getElementById("nombreProveedorImagen");

  const textoModal = document.getElementById("textoModalImagenProveedor");

  if (!inputId) {
    console.error("No existe #actualizarImagenProveedorId");

    return;
  }

  //===================================================
  // LIMPIAR ESTADO ANTERIOR
  //===================================================

  limpiarModalActualizarImagenProveedor();

  //===================================================
  // ASIGNAR ID
  //===================================================

  inputId.value = idProveedor;

  //===================================================
  // OBTENER INFORMACIÓN DEL PROVEEDOR
  //===================================================

  try {
    const respuesta = await fetch(
      AJAX_PROVEEDORES +
        "?accion=obtener&id_provedor=" +
        encodeURIComponent(idProveedor),
      {
        method: "GET",
        cache: "no-store",
        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const datos = await respuesta.json();

    if (!datos.success || !datos.proveedor) {
      throw new Error(
        datos.message || "No se pudo obtener la información del proveedor.",
      );
    }

    const proveedor = datos.proveedor;

    //=================================================
    // NOMBRE
    //=================================================

    if (nombreProveedor) {
      nombreProveedor.textContent = proveedor.nombre || "Proveedor";
    }

    //=================================================
    // TEXTO
    //=================================================

    if (textoModal) {
      textoModal.textContent = "Selecciona una nueva imagen para el proveedor.";
    }

    //=================================================
    // MOSTRAR IMAGEN ACTUAL O IMAGEN POR DEFECTO
    //=================================================

    const preview = document.getElementById("vistaPreviaImagenProveedor");

    if (preview) {
      //=================================================
      // IMAGEN DEL PROVEEDOR
      //=================================================

      preview.src =
        "mostrar_imagen_proveedor.php?id=" + encodeURIComponent(idProveedor);

      preview.classList.remove("d-none");

      //=================================================
      // SI EL PROVEEDOR NO TIENE IMAGEN
      // O LA IMAGEN NO EXISTE
      //=================================================

      preview.onerror = function () {
        preview.onerror = null;

        preview.src = IMAGEN_PROVEEDOR_DEFAULT;
      };

      //=================================================
      // OCULTAR PLACEHOLDER
      //=================================================

      const placeholder = document.getElementById("placeholderImagenProveedor");

      if (placeholder) {
        placeholder.classList.add("d-none");
      }
    }

    //=================================================
    // MOSTRAR MODAL
    //=================================================

    modalActualizarImagenProveedor.show();
  } catch (error) {
    console.error("Error al obtener proveedor para actualizar imagen:", error);

    mostrarAlertaProveedor(
      "Error",
      error.message || "No se pudo cargar la información del proveedor.",
      "error",
    );
  }
}

//=====================================================
// SELECCIONAR IMAGEN
//=====================================================

function manejarSeleccionImagenProveedor(evento) {
  const archivo = evento.target.files[0];

  if (!archivo) {
    return;
  }

  //===================================================
  // VALIDAR TIPO
  //===================================================

  if (!TIPOS_IMAGEN_PROVEEDOR_PERMITIDOS.includes(archivo.type)) {
    mostrarAlertaProveedor(
      "Formato no permitido",
      "Solo se permiten imágenes JPG, JPEG, PNG o WEBP.",
      "warning",
    );

    evento.target.value = "";

    mostrarPlaceholderImagenProveedor();

    return;
  }

  //===================================================
  // VALIDAR TAMAÑO
  //===================================================

  if (archivo.size > MAX_TAMANO_IMAGEN_PROVEEDOR) {
    mostrarAlertaProveedor(
      "Imagen demasiado grande",
      "La imagen no puede superar los 2.7 MB.",
      "warning",
    );

    evento.target.value = "";

    mostrarPlaceholderImagenProveedor();

    return;
  }

  //===================================================
  // NOMBRE ARCHIVO
  //===================================================

  const nombreArchivo = document.getElementById("nombreArchivoImagenProveedor");

  if (nombreArchivo) {
    nombreArchivo.textContent = archivo.name;
  }

  //===================================================
  // VISTA PREVIA
  //===================================================

  const lector = new FileReader();

  lector.onload = function (e) {
    mostrarVistaPreviaImagenProveedor(e.target.result);
  };

  lector.onerror = function () {
    mostrarAlertaProveedor(
      "Error",
      "No se pudo leer la imagen seleccionada.",
      "error",
    );

    evento.target.value = "";
  };

  lector.readAsDataURL(archivo);
}

//=====================================================
// MOSTRAR VISTA PREVIA
//=====================================================

function mostrarVistaPreviaImagenProveedor(imagen) {
  const preview = document.getElementById("vistaPreviaImagenProveedor");

  const placeholder = document.getElementById("placeholderImagenProveedor");

  if (!preview) {
    return;
  }

  //===================================================
  // ASIGNAR IMAGEN
  //===================================================

  preview.src = imagen || IMAGEN_PROVEEDOR_DEFAULT;

  preview.classList.remove("d-none");

  if (placeholder) {
    placeholder.classList.add("d-none");
  }

  //===================================================
  // SI LA IMAGEN NO EXISTE, USAR DEFAULT
  //===================================================

  preview.onerror = function () {
    preview.onerror = null;

    preview.src = IMAGEN_PROVEEDOR_DEFAULT;
  };
}

//=====================================================
// MOSTRAR PLACEHOLDER
//=====================================================

function mostrarPlaceholderImagenProveedor() {
  const preview = document.getElementById("vistaPreviaImagenProveedor");

  const placeholder = document.getElementById("placeholderImagenProveedor");

  if (preview) {
    preview.src = "";

    preview.classList.add("d-none");
  }

  if (placeholder) {
    placeholder.classList.remove("d-none");
  }
}

//=====================================================
// ELIMINAR VISTA PREVIA
//=====================================================

function eliminarVistaPreviaImagenProveedor() {
  const inputImagen = document.getElementById("imagenProveedor");

  const nombreArchivo = document.getElementById("nombreArchivoImagenProveedor");

  if (inputImagen) {
    inputImagen.value = "";
  }

  if (nombreArchivo) {
    nombreArchivo.textContent = "No se ha seleccionado una nueva imagen.";
  }

  mostrarPlaceholderImagenProveedor();
}

//=====================================================
// GUARDAR IMAGEN
//=====================================================

async function guardarImagenProveedor(evento) {
  //===================================================
  // EVITAR RECARGA DEL FORMULARIO
  //===================================================

  if (evento) {
    evento.preventDefault();
  }

  //===================================================
  // ELEMENTOS
  //===================================================

  const formulario = document.getElementById("formActualizarImagenProveedor");

  const inputId = document.getElementById("actualizarImagenProveedorId");

  const inputImagen = document.getElementById("imagenProveedor");

  const btnGuardar = document.getElementById("btnGuardarImagenProveedor");

  if (!formulario || !inputId || !inputImagen) {
    console.error(
      "No se encontraron los elementos necesarios para actualizar la imagen.",
    );

    return;
  }

  //===================================================
  // VALIDAR ID
  //===================================================

  const idProveedor = inputId.value.trim();

  if (!idProveedor || Number(idProveedor) <= 0) {
    mostrarAlertaProveedor(
      "Proveedor inválido",
      "No se pudo identificar al proveedor.",
      "error",
    );

    return;
  }

  //===================================================
  // VALIDAR ARCHIVO
  //===================================================

  if (!inputImagen.files || inputImagen.files.length === 0) {
    mostrarAlertaProveedor(
      "Imagen requerida",
      "Selecciona una nueva imagen para actualizar.",
      "warning",
    );

    return;
  }

  const archivo = inputImagen.files[0];

  //===================================================
  // VALIDAR TIPO
  //===================================================

  if (!TIPOS_IMAGEN_PROVEEDOR_PERMITIDOS.includes(archivo.type)) {
    mostrarAlertaProveedor(
      "Formato no permitido",
      "Solo se permiten imágenes JPG, JPEG, PNG o WEBP.",
      "warning",
    );

    return;
  }

  //===================================================
  // VALIDAR TAMAÑO
  //===================================================

  if (archivo.size > MAX_TAMANO_IMAGEN_PROVEEDOR) {
    mostrarAlertaProveedor(
      "Imagen demasiado grande",
      "La imagen no puede superar los 2.7 MB.",
      "warning",
    );

    return;
  }

  //===================================================
  // FORM DATA
  //===================================================

  const formData = new FormData();

  formData.append("id_provedor", idProveedor);

  formData.append("imagen", archivo);

  //===================================================
  // ESTADO BOTÓN
  //===================================================

  const textoOriginal = btnGuardar ? btnGuardar.innerHTML : "";

  try {
    if (btnGuardar) {
      btnGuardar.disabled = true;

      btnGuardar.innerHTML = `

        <span
          class="spinner-border spinner-border-sm me-2"
          role="status"
          aria-hidden="true">
        </span>

        Actualizando...

      `;
    }

    //=================================================
    // AJAX
    //=================================================

    const respuesta = await fetch("ajax/actualizar_imagen_proveedor.php", {
      method: "POST",

      body: formData,

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    //=================================================
    // OBTENER TEXTO
    //=================================================

    const texto = await respuesta.text();

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (error) {
      console.error("Respuesta AJAX no válida:", texto);

      throw new Error("El servidor devolvió una respuesta no válida.");
    }

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!respuesta.ok) {
      throw new Error(resultado.message || "Error HTTP " + respuesta.status);
    }

    if (!resultado.success) {
      throw new Error(resultado.message || "No se pudo actualizar la imagen.");
    }

    //=================================================
    // CERRAR MODAL
    //=================================================

    if (modalActualizarImagenProveedor) {
      modalActualizarImagenProveedor.hide();
    }

    //=================================================
    // MENSAJE
    //=================================================

    await Swal.fire({
      icon: "success",

      title: "Imagen actualizada",

      text:
        resultado.message ||
        "La imagen del proveedor se actualizó correctamente.",

      timer: 1800,

      showConfirmButton: false,
    });

    //=================================================
    // RECARGAR TABLA
    //=================================================

    await cargarProveedores();
  } catch (error) {
    console.error("Error al actualizar imagen:", error);

    mostrarAlertaProveedor(
      "No se pudo actualizar",
      error.message ||
        "Ocurrió un error al actualizar la imagen del proveedor.",
      "error",
    );
  } finally {
    if (btnGuardar) {
      btnGuardar.disabled = false;

      btnGuardar.innerHTML = textoOriginal;
    }
  }
}

//=====================================================
// LIMPIAR MODAL
//=====================================================

function limpiarModalActualizarImagenProveedor() {
  const inputId = document.getElementById("actualizarImagenProveedorId");

  const inputImagen = document.getElementById("imagenProveedor");

  const nombreArchivo = document.getElementById("nombreArchivoImagenProveedor");

  const nombreProveedor = document.getElementById("nombreProveedorImagen");

  const textoModal = document.getElementById("textoModalImagenProveedor");

  //===================================================
  // ID
  //===================================================

  if (inputId) {
    inputId.value = "";
  }

  //===================================================
  // INPUT
  //===================================================

  if (inputImagen) {
    inputImagen.value = "";
  }

  //===================================================
  // NOMBRE ARCHIVO
  //===================================================

  if (nombreArchivo) {
    nombreArchivo.textContent = "No se ha seleccionado una nueva imagen.";
  }

  //===================================================
  // NOMBRE PROVEEDOR
  //===================================================

  if (nombreProveedor) {
    nombreProveedor.textContent = "Proveedor";
  }

  //===================================================
  // TEXTO
  //===================================================

  if (textoModal) {
    textoModal.textContent = "Actualiza la imagen del proveedor seleccionado.";
  }

  //===================================================
  // PLACEHOLDER
  //===================================================

  mostrarPlaceholderImagenProveedor();
}

//=====================================================
// ALERTAS
//=====================================================

function mostrarAlertaProveedor(titulo, mensaje, tipo = "info") {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      title: titulo,

      text: mensaje,

      icon: tipo,

      confirmButtonText: "Aceptar",
    });

    return;
  }

  alert(titulo + "\n\n" + mensaje);
}

//=====================================================
// EXPONER FUNCIÓN AL HTML
//=====================================================

window.abrirActualizarImagenProveedor = abrirActualizarImagenProveedor;
