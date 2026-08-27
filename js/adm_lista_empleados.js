//=====================================================
// CoDevPro Technology
// Archivo: js/adm_lista_empleados.js
// Módulo: Lista de Empleados
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let temporizadorBusquedaEmpleado = null;

let solicitudEmpleadosActual = null;

let solicitudEditarEmpleadoActual = null;

let eventosModalEditarInicializados = false;

//=====================================================
// PAGINACIÓN
//=====================================================

let paginaActualEmpleados = 1;

const registrosPorPaginaEmpleados = 5;

let totalRegistrosEmpleados = 0;

let totalPaginasEmpleados = 1;

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", () => {
  cargarKPIEmpleados();

  cargarRolesEmpleados();

  inicializarFiltrosEmpleados();

  inicializarEventosEditarEmpleado();

  inicializarEventosVerEmpleado();

  inicializarEventosEditarImagenEmpleado();

  inicializarEventosModalEditar();

  cargarEmpleados(1);
});

//=====================================================
// CARGAR KPI
//=====================================================

async function cargarKPIEmpleados() {
  const totalEmpleados = document.getElementById("totalEmpleados");
  const empleadosActivos = document.getElementById("empleadosActivos");
  const empleadosInactivos = document.getElementById("empleadosInactivos");
  const totalRoles = document.getElementById("totalRoles");

  if (
    !totalEmpleados ||
    !empleadosActivos ||
    !empleadosInactivos ||
    !totalRoles
  ) {
    console.error("No se encontraron los elementos de los KPI.");
    return;
  }

  totalEmpleados.textContent = "...";
  empleadosActivos.textContent = "...";
  empleadosInactivos.textContent = "...";
  totalRoles.textContent = "...";

  try {
    const respuesta = await fetch("ajax/obtener_kpis_empleados.php", {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      cache: "no-store",
    });

    const textoRespuesta = await respuesta.text();

    if (!respuesta.ok) {
      console.error(textoRespuesta);

      throw new Error("Error HTTP " + respuesta.status);
    }

    let resultado;

    try {
      resultado = JSON.parse(textoRespuesta);
    } catch (errorJSON) {
      console.error("Respuesta KPI no válida:", textoRespuesta);

      throw new Error("El servidor no devolvió JSON válido.");
    }

    if (!resultado || resultado.success !== true) {
      throw new Error(resultado?.mensaje || "No se pudieron obtener los KPI.");
    }

    const datos = resultado.data || {};

    totalEmpleados.textContent = formatearNumero(datos.totalEmpleados);

    empleadosActivos.textContent = formatearNumero(datos.empleadosActivos);

    empleadosInactivos.textContent = formatearNumero(datos.empleadosInactivos);

    totalRoles.textContent = formatearNumero(datos.totalRoles);
  } catch (error) {
    console.error("Error al cargar KPI de empleados:", error);

    totalEmpleados.textContent = "0";
    empleadosActivos.textContent = "0";
    empleadosInactivos.textContent = "0";
    totalRoles.textContent = "0";
  }
}

//=====================================================
// CARGAR ROLES - FILTRO DE EMPLEADOS
//=====================================================

async function cargarRolesEmpleados() {
  const selectRol = document.getElementById("filtroRolEmpleado");

  if (!selectRol) {
    return;
  }

  try {
    const respuesta = await fetch("ajax/obtener_roles_empleados.php", {
      method: "GET",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },

      cache: "no-store",
    });

    const textoRespuesta = await respuesta.text();

    if (!respuesta.ok) {
      console.error("Respuesta roles filtro:", textoRespuesta);

      throw new Error("Error HTTP " + respuesta.status);
    }

    let resultado;

    try {
      resultado = JSON.parse(textoRespuesta);
    } catch (errorJSON) {
      console.error("JSON roles filtro:", textoRespuesta);

      throw new Error("El servidor no devolvió JSON válido.");
    }

    if (!resultado || resultado.success !== true) {
      throw new Error(resultado?.mensaje || "No se pudieron cargar los roles.");
    }

    selectRol.innerHTML = `
      <option value="">Todos los roles</option>
    `;

    if (Array.isArray(resultado.data)) {
      resultado.data.forEach((rol) => {
        const option = document.createElement("option");

        option.value = rol.id_rol;

        option.textContent = rol.nombre;

        selectRol.appendChild(option);
      });
    }
  } catch (error) {
    console.error("Error al cargar roles del filtro:", error);
  }
}

//=====================================================
// CARGAR ROLES - MODAL EDITAR EMPLEADO
//=====================================================

async function cargarRolesEditarEmpleado(idRolSeleccionado = "") {
  const selectRol = document.getElementById("editar_id_rol");

  if (!selectRol) {
    return;
  }

  selectRol.innerHTML = `
        <option value="">
            Cargando cargos / roles...
        </option>
    `;

  selectRol.disabled = true;

  try {
    const respuesta = await fetch("ajax/obtener_roles_empleados.php", {
      method: "GET",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },

      cache: "no-store",
    });

    const texto = await respuesta.text();

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const resultado = JSON.parse(texto);

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje || "No se pudieron cargar los cargos / roles.",
      );
    }

    selectRol.innerHTML = `
            <option value="">
                Seleccionar cargo / rol
            </option>
        `;

    const roles = Array.isArray(resultado.data) ? resultado.data : [];

    roles.forEach(function (rol) {
      const option = document.createElement("option");

      option.value = rol.id_rol;

      option.textContent = rol.nombre;

      selectRol.appendChild(option);
    });

    //=================================================
    // SELECCIONAR ROL DEL EMPLEADO
    //=================================================

    if (idRolSeleccionado) {
      selectRol.value = String(idRolSeleccionado);
    }

    selectRol.disabled = false;

    //=================================================
    // CARGAR PERMISOS
    //=================================================

    if (idRolSeleccionado) {
      await cargarPermisosRolEditar(idRolSeleccionado);
    }
  } catch (error) {
    console.error("Error cargando roles:", error);

    selectRol.innerHTML = `
            <option value="">
                Error al cargar cargos / roles
            </option>
        `;

    selectRol.disabled = false;
  }
}

//=====================================================
// INICIALIZAR FILTROS
//=====================================================

function inicializarFiltrosEmpleados() {
  const buscar = document.getElementById("buscarEmpleado");

  const estado = document.getElementById("filtroEstadoEmpleado");

  const rol = document.getElementById("filtroRolEmpleado");

  const fechaDesde = document.getElementById("fechaDesdeEmpleado");

  const fechaHasta = document.getElementById("fechaHastaEmpleado");

  const btnLimpiar = document.getElementById("btnLimpiarFiltros");

  if (buscar) {
    buscar.addEventListener("input", () => {
      clearTimeout(temporizadorBusquedaEmpleado);

      temporizadorBusquedaEmpleado = setTimeout(() => {
        paginaActualEmpleados = 1;

        cargarEmpleados(1);
      }, 350);
    });
  }

  if (estado) {
    estado.addEventListener("change", () => {
      paginaActualEmpleados = 1;

      cargarEmpleados(1);
    });
  }

  if (rol) {
    rol.addEventListener("change", () => {
      paginaActualEmpleados = 1;

      cargarEmpleados(1);
    });
  }

  if (fechaDesde && typeof flatpickr !== "undefined") {
    flatpickr(fechaDesde, {
      locale: "es",
      dateFormat: "Y-m-d",
      allowInput: true,
      maxDate: "today",

      onChange: function () {
        if (!validarRangoFechas()) {
          return;
        }

        paginaActualEmpleados = 1;

        cargarEmpleados(1);
      },
    });
  }

  if (fechaHasta && typeof flatpickr !== "undefined") {
    flatpickr(fechaHasta, {
      locale: "es",
      dateFormat: "Y-m-d",
      allowInput: true,
      maxDate: "today",

      onChange: function () {
        if (!validarRangoFechas()) {
          return;
        }

        paginaActualEmpleados = 1;

        cargarEmpleados(1);
      },
    });
  }

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", limpiarFiltrosEmpleados);
  }
}

//=====================================================
// VALIDAR RANGO DE FECHAS
//=====================================================

function validarRangoFechas() {
  const fechaDesde = document.getElementById("fechaDesdeEmpleado")?.value || "";

  const fechaHasta = document.getElementById("fechaHastaEmpleado")?.value || "";

  if (fechaDesde !== "" && fechaHasta !== "" && fechaDesde > fechaHasta) {
    Swal.fire({
      icon: "warning",
      title: "Rango de fechas inválido",
      text: "La fecha Desde no puede ser posterior a la fecha Hasta.",
      confirmButtonText: "Entendido",
    });

    return false;
  }

  return true;
}

//=====================================================
// CARGAR EMPLEADOS
//=====================================================

async function cargarEmpleados(pagina = 1) {
  const tabla = document.getElementById("tablaEmpleados");

  if (!tabla) {
    return;
  }

  pagina = Number(pagina);

  if (!Number.isInteger(pagina) || pagina < 1) {
    pagina = 1;
  }

  paginaActualEmpleados = pagina;

  if (solicitudEmpleadosActual) {
    solicitudEmpleadosActual.abort();
  }

  solicitudEmpleadosActual = new AbortController();

  const buscar = document.getElementById("buscarEmpleado")?.value.trim() || "";

  const estado = document.getElementById("filtroEstadoEmpleado")?.value || "";

  const idRol = document.getElementById("filtroRolEmpleado")?.value || "";

  const fechaDesde = document.getElementById("fechaDesdeEmpleado")?.value || "";

  const fechaHasta = document.getElementById("fechaHastaEmpleado")?.value || "";

  if (fechaDesde !== "" && fechaHasta !== "" && fechaDesde > fechaHasta) {
    return;
  }

  tabla.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-5">

                <div
                    class="spinner-border text-primary mb-3"
                    role="status"
                >
                    <span class="visually-hidden">
                        Cargando...
                    </span>
                </div>

                <div class="text-muted">
                    Cargando empleados...
                </div>

            </td>
        </tr>
    `;

  const parametros = new URLSearchParams({
    buscar: buscar,
    estado: estado,
    idRol: idRol,
    fechaDesde: fechaDesde,
    fechaHasta: fechaHasta,
    pagina: pagina,
    limite: registrosPorPaginaEmpleados,
  });

  try {
    const respuesta = await fetch(
      "ajax/obtener_lista_empleados.php?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        cache: "no-store",

        signal: solicitudEmpleadosActual.signal,
      },
    );

    const textoRespuesta = await respuesta.text();

    if (!respuesta.ok) {
      console.error("Respuesta servidor:", textoRespuesta);

      throw new Error("Error HTTP " + respuesta.status);
    }

    let resultado;

    try {
      resultado = JSON.parse(textoRespuesta);
    } catch (errorJSON) {
      console.error("Respuesta lista empleados:", textoRespuesta);

      throw new Error("El servidor no devolvió JSON válido.");
    }

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje || "No se pudieron cargar los empleados.",
      );
    }

    const empleados = resultado.data?.empleados || [];

    totalRegistrosEmpleados = Number(resultado.data?.total || 0);

    paginaActualEmpleados = Number(resultado.data?.pagina || pagina);

    totalPaginasEmpleados = Number(resultado.data?.totalPaginas || 1);

    renderizarEmpleados(empleados);

    actualizarResumenEmpleados();

    renderizarPaginacionEmpleados();
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al cargar empleados:", error);

    tabla.innerHTML = `
            <tr>
                <td
                    colspan="8"
                    class="text-center py-5"
                >

                    <div class="text-danger">

                        <i
                            class="bi bi-exclamation-triangle fs-1 d-block mb-2"
                        ></i>

                        <h6 class="fw-semibold">
                            No se pudieron cargar los empleados
                        </h6>

                        <p class="small mb-0">
                            ${escapeHTML(error.message)}
                        </p>

                    </div>

                </td>
            </tr>
        `;

    totalRegistrosEmpleados = 0;

    totalPaginasEmpleados = 1;

    actualizarResumenEmpleados();

    renderizarPaginacionEmpleados();
  }
}

//=====================================================
// RENDERIZAR EMPLEADOS
//=====================================================

function renderizarEmpleados(empleados) {
  const tabla = document.getElementById("tablaEmpleados");

  if (!tabla) {
    return;
  }

  if (!empleados || empleados.length === 0) {
    tabla.innerHTML = `
            <tr>
                <td
                    colspan="8"
                    class="text-center py-5"
                >

                    <div class="text-muted">

                        <i
                            class="bi bi-search fs-1 d-block mb-2"
                        ></i>

                        <h6 class="fw-semibold">
                            No se encontraron empleados
                        </h6>

                        <p class="small mb-0">
                            No existen empleados que coincidan
                            con los filtros seleccionados.
                        </p>

                    </div>

                </td>
            </tr>
        `;

    return;
  }

  const offset = (paginaActualEmpleados - 1) * registrosPorPaginaEmpleados;

  let html = "";

  empleados.forEach((empleado, indice) => {
    //=================================================
    // NOMBRE COMPLETO
    //=================================================

    const nombreCompleto = (
      (empleado.nombre || "") +
      " " +
      (empleado.apellido || "")
    ).trim();

    //=================================================
    // ESTADO
    //=================================================

    const estado = empleado.estado || "";

    const badgeEstado =
      estado === "ACTIVO"
        ? `
            <span
                class="badge bg-success-subtle text-success"
            >
                <i
                    class="bi bi-check-circle me-1"
                ></i>
                Activo
            </span>
        `
        : `
            <span
                class="badge bg-danger-subtle text-danger"
            >
                <i
                    class="bi bi-x-circle me-1"
                ></i>
                Inactivo
            </span>
        `;

    //=================================================
    // ROL
    //=================================================

    const rol = empleado.nombre_rol
      ? escapeHTML(empleado.nombre_rol)
      : "Sin rol";

    //=================================================
    // FECHA
    //=================================================

    const fechaRegistro = formatearFecha(empleado.fecha_registro);

    //=================================================
    // CELULAR
    //=================================================

    const celularRaw = empleado.celular ? String(empleado.celular).trim() : "";

    const celularLimpio = celularRaw.replace(/\D/g, "");

    //=================================================
    // WHATSAPP
    //=================================================

    let numeroWhatsApp = "";

    if (celularLimpio !== "") {
      numeroWhatsApp = celularLimpio.startsWith("51")
        ? celularLimpio
        : "51" + celularLimpio;
    }

    const celular = celularRaw ? escapeHTML(celularRaw) : "—";

    //=================================================
    // EMAIL
    //=================================================

    const email = empleado.email ? escapeHTML(empleado.email) : "—";

    //=================================================
    // NÚMERO DE FILA
    //=================================================

    const numeroFila = offset + indice + 1;

    //=================================================
    // IMAGEN DEL EMPLEADO
    //=================================================

    /*
    |--------------------------------------------------------------------------
    | IMPORTANTE
    |--------------------------------------------------------------------------
    | Ajusta estas rutas únicamente si tu estructura de
    | carpetas es diferente.
    |
    | Ejemplo esperado:
    |
    | /vistas/plantilla/imagenes/empleados/
    | /vistas/plantilla/imagenes/empleados/default.png
    |
    */

    const imagenEmpleado = empleado.imagen
      ? String(empleado.imagen).trim()
      : "";

    const imagenPorDefecto = "vistas/plantilla/imagenes/empleados/default.png";

    let urlImagenEmpleado = imagenPorDefecto;

    if (imagenEmpleado !== "") {
      /*
      |--------------------------------------------------------------------------
      | Si en la BD guardas solamente:
      | foto.jpg
      |
      | se construye:
      | vistas/plantilla/imagenes/empleados/foto.jpg
      |--------------------------------------------------------------------------
      */

      urlImagenEmpleado =
        "vistas/plantilla/imagenes/empleados/" +
        encodeURIComponent(imagenEmpleado);
    }

    //=================================================
    // HTML
    //=================================================

    html += `
        <tr>

            <!--=================================================
                NÚMERO
            =================================================-->

            <td class="px-4 fw-semibold">
                ${numeroFila}
            </td>


            <!--=================================================
                EMPLEADO
            =================================================-->

            <td>

                <div
                    class="
                        d-flex
                        align-items-center
                        gap-3
                    "
                >

                    <!--=========================================
                        IMAGEN
                    =========================================-->

                    <div
                        class="
                            rounded-circle
                            overflow-hidden
                            flex-shrink-0
                            border
                            bg-light
                            d-flex
                            align-items-center
                            justify-content-center
                        "
                        style="
                            width:48px;
                            height:48px;
                            min-width:48px;
                            min-height:48px;
                        "
                    >

                        <img
    src="ajax/mostrar_imagen_empleado.php?id_empleado=${Number(
      empleado.id_empleado
    )}"
    alt="${escapeHTML(nombreCompleto)}"
    class="rounded-circle"
    width="42"
    height="42"
    style="
        width:42px;
        height:42px;
        min-width:42px;
        object-fit:cover;
        border:2px solid #e9ecef;
    "
    loading="lazy"
    onerror="this.onerror=null; this.src='assets/img/sin_imagen.png';"
>

                    </div>


                    <!--=========================================
                        DATOS DEL EMPLEADO
                    =========================================-->

                    <div>

                        <div class="fw-semibold">
                            ${escapeHTML(nombreCompleto)}
                        </div>
                    </div>

                </div>

            </td>


            <!--=================================================
                DNI
            =================================================-->

            <td>
                ${escapeHTML(empleado.dni || "—")}
            </td>


            <!--=================================================
                CONTACTO
            =================================================-->

            <td>

                <div
                    class="
                        d-flex
                        flex-column
                        gap-1
                    "
                >

                    <!--=========================================
                        WHATSAPP
                    =========================================-->

                    ${
                      numeroWhatsApp
                        ? `
                            <a
                                href="https://wa.me/${encodeURIComponent(
                                  numeroWhatsApp,
                                )}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="
                                    text-decoration-none
                                    fw-semibold
                                "
                                title="Enviar mensaje por WhatsApp"
                            >

                                <i
                                    class="
                                        bi
                                        bi-whatsapp
                                        text-success
                                        me-1
                                    "
                                ></i>

                                ${celular}

                            </a>
                          `
                        : `
                            <span class="text-muted">

                                <i
                                    class="
                                        bi
                                        bi-phone
                                        me-1
                                    "
                                ></i>

                                —

                            </span>
                          `
                    }


                    <!--=========================================
                        EMAIL
                    =========================================-->

                    ${
                      empleado.email
                        ? `
                            <a
                                href="mailto:${encodeURIComponent(
                                  String(empleado.email).trim(),
                                )}"
                                class="
                                    text-decoration-none
                                    small
                                    text-muted
                                "
                                title="Enviar correo electrónico"
                            >

                                <i
                                    class="
                                        bi
                                        bi-envelope-fill
                                        text-primary
                                        me-1
                                    "
                                ></i>

                                ${email}

                            </a>
                          `
                        : `
                            <span
                                class="
                                    text-muted
                                    small
                                "
                            >

                                <i
                                    class="
                                        bi
                                        bi-envelope
                                        me-1
                                    "
                                ></i>

                                —

                            </span>
                          `
                    }

                </div>

            </td>


            <!--=================================================
                ROL
            =================================================-->

            <td>
                ${rol}
            </td>


            <!--=================================================
                FECHA
            =================================================-->

            <td>
                ${fechaRegistro}
            </td>


            <!--=================================================
                ESTADO
            =================================================-->

            <td>
                ${badgeEstado}
            </td>


            <!--=================================================
                ACCIONES
            =================================================-->

            <td class="text-center">

                <!-- BOTÓN VER -->

                <button
                    type="button"
                    class="
                        btn
                        btn-sm
                        btn-outline-secondary
                        btn-ver-empleado
                    "
                    data-id-empleado="${Number(empleado.id_empleado)}"
                    title="Ver empleado"
                >

                    <i class="bi bi-eye"></i>

                </button>


                <!-- BOTÓN EDITAR -->

                <button
                    type="button"
                    class="
                        btn
                        btn-sm
                        btn-outline-primary
                        btn-editar-empleado
                    "
                    data-id-empleado="${Number(empleado.id_empleado)}"
                    title="Editar empleado"
                >

                    <i
                        class="
                            bi
                            bi-pencil-square
                        "
                    ></i>

                </button>

            </td>

        </tr>
    `;
  });

  tabla.innerHTML = html;
}

//=====================================================
// ACTUALIZAR RESUMEN
//=====================================================

function actualizarResumenEmpleados() {
  const inicio = document.getElementById("rangoInicioEmpleados");

  const fin = document.getElementById("rangoFinEmpleados");

  const total = document.getElementById("totalRegistrosEmpleados");

  if (!inicio || !fin || !total) {
    return;
  }

  if (totalRegistrosEmpleados <= 0) {
    inicio.textContent = "0";

    fin.textContent = "0";

    total.textContent = "0";

    return;
  }

  const inicioNumero =
    (paginaActualEmpleados - 1) * registrosPorPaginaEmpleados + 1;

  const finNumero = Math.min(
    paginaActualEmpleados * registrosPorPaginaEmpleados,
    totalRegistrosEmpleados,
  );

  inicio.textContent = inicioNumero.toLocaleString("es-PE");

  fin.textContent = finNumero.toLocaleString("es-PE");

  total.textContent = totalRegistrosEmpleados.toLocaleString("es-PE");
}

//=====================================================
// RENDERIZAR PAGINACIÓN
//=====================================================

function renderizarPaginacionEmpleados() {
  const paginacion = document.getElementById("paginacionEmpleados");

  if (!paginacion) {
    return;
  }

  paginacion.innerHTML = "";

  if (totalRegistrosEmpleados <= 0) {
    paginacion.innerHTML = `
            <li class="page-item disabled">

                <span class="page-link">

                    <i
                        class="bi bi-chevron-left"
                    ></i>

                </span>

            </li>
        `;

    return;
  }

  const liAnterior = document.createElement("li");

  liAnterior.className =
    "page-item" + (paginaActualEmpleados <= 1 ? " disabled" : "");

  liAnterior.innerHTML = `
        <a
            class="page-link"
            href="#"
            aria-label="Anterior"
        >
            <i
                class="bi bi-chevron-left"
            ></i>
        </a>
    `;

  if (paginaActualEmpleados > 1) {
    liAnterior.querySelector("a").addEventListener("click", function (evento) {
      evento.preventDefault();

      cargarEmpleados(paginaActualEmpleados - 1);
    });
  }

  paginacion.appendChild(liAnterior);

  const maxPaginasVisibles = 5;

  let inicioPagina = Math.max(
    1,
    paginaActualEmpleados - Math.floor(maxPaginasVisibles / 2),
  );

  let finPagina = Math.min(
    totalPaginasEmpleados,
    inicioPagina + maxPaginasVisibles - 1,
  );

  if (finPagina - inicioPagina + 1 < maxPaginasVisibles) {
    inicioPagina = Math.max(1, finPagina - maxPaginasVisibles + 1);
  }

  for (let pagina = inicioPagina; pagina <= finPagina; pagina++) {
    const li = document.createElement("li");

    li.className =
      "page-item" + (pagina === paginaActualEmpleados ? " active" : "");

    li.innerHTML = `
            <a
                class="page-link"
                href="#"
            >
                ${pagina}
            </a>
        `;

    li.querySelector("a").addEventListener("click", function (evento) {
      evento.preventDefault();

      if (pagina !== paginaActualEmpleados) {
        cargarEmpleados(pagina);
      }
    });

    paginacion.appendChild(li);
  }

  const liSiguiente = document.createElement("li");

  liSiguiente.className =
    "page-item" +
    (paginaActualEmpleados >= totalPaginasEmpleados ? " disabled" : "");

  liSiguiente.innerHTML = `
        <a
            class="page-link"
            href="#"
            aria-label="Siguiente"
        >
            <i
                class="bi bi-chevron-right"
            ></i>
        </a>
    `;

  if (paginaActualEmpleados < totalPaginasEmpleados) {
    liSiguiente.querySelector("a").addEventListener("click", function (evento) {
      evento.preventDefault();

      cargarEmpleados(paginaActualEmpleados + 1);
    });
  }

  paginacion.appendChild(liSiguiente);
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltrosEmpleados() {
  const buscar = document.getElementById("buscarEmpleado");

  const estado = document.getElementById("filtroEstadoEmpleado");

  const rol = document.getElementById("filtroRolEmpleado");

  const fechaDesde = document.getElementById("fechaDesdeEmpleado");

  const fechaHasta = document.getElementById("fechaHastaEmpleado");

  if (buscar) {
    buscar.value = "";
  }

  if (estado) {
    estado.value = "";
  }

  if (rol) {
    rol.value = "";
  }

  if (fechaDesde) {
    fechaDesde.value = "";

    if (fechaDesde._flatpickr) {
      fechaDesde._flatpickr.clear();
    }
  }

  if (fechaHasta) {
    fechaHasta.value = "";

    if (fechaHasta._flatpickr) {
      fechaHasta._flatpickr.clear();
    }
  }

  paginaActualEmpleados = 1;

  cargarEmpleados(1);
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "—";
  }

  const partes = String(fecha).split(" ")[0].split("-");

  if (partes.length !== 3) {
    return escapeHTML(String(fecha));
  }

  return partes[2] + "/" + partes[1] + "/" + partes[0];
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escapeHTML(valor) {
  if (valor === null || valor === undefined) {
    return "";
  }

  const div = document.createElement("div");

  div.textContent = String(valor);

  return div.innerHTML;
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(numero) {
  const valor = Number(numero);

  if (isNaN(valor)) {
    return "0";
  }

  return valor.toLocaleString("es-PE");
}
//=====================================================
// INICIALIZAR EVENTO EDITAR
//=====================================================

function inicializarEventosEditarEmpleado() {
  document.addEventListener("click", function (evento) {
    const boton = evento.target.closest(".btn-editar-empleado");

    if (!boton) {
      return;
    }

    const idEmpleado = boton.getAttribute("data-id-empleado");

    if (!idEmpleado || Number(idEmpleado) <= 0) {
      Swal.fire({
        icon: "error",
        title: "Empleado inválido",
        text: "No se pudo identificar al empleado seleccionado.",
        confirmButtonText: "Entendido",
      });

      return;
    }

    abrirModalEditarEmpleado(Number(idEmpleado));
  });
}
//=====================================================
// INICIALIZAR EVENTO VER EMPLEADO
//=====================================================

function inicializarEventosVerEmpleado() {
  document.addEventListener("click", function (evento) {
    const boton = evento.target.closest(".btn-ver-empleado");

    if (!boton) {
      return;
    }

    const idEmpleado = boton.getAttribute("data-id-empleado");

    if (!idEmpleado || Number(idEmpleado) <= 0) {
      Swal.fire({
        icon: "error",
        title: "Empleado inválido",
        text: "No se pudo identificar al empleado seleccionado.",
        confirmButtonText: "Entendido",
      });

      return;
    }

    //=================================================
    // REDIRIGIR AL DETALLE DEL EMPLEADO
    //=================================================

    window.location.href =
      "adm_detalles_empleado.php?id_empleado=" + encodeURIComponent(idEmpleado);
  });
}
//=====================================================
// INICIALIZAR EVENTO EDITAR IMAGEN EMPLEADO
//=====================================================

function inicializarEventosEditarImagenEmpleado() {
  document.addEventListener("click", function (evento) {
    const boton = evento.target.closest(".btn-editar-imagen-empleado");

    if (!boton) {
      return;
    }

    //=================================================
    // OBTENER ID EMPLEADO
    //=================================================

    const idEmpleado = boton.getAttribute("data-id-empleado");

    //=================================================
    // VALIDAR ID
    //=================================================

    if (!idEmpleado || Number(idEmpleado) <= 0) {
      Swal.fire({
        icon: "error",
        title: "Empleado inválido",
        text: "No se pudo identificar al empleado seleccionado.",
        confirmButtonText: "Entendido",
      });

      return;
    }

    //=================================================
    // BUSCAR MODAL
    //=================================================

    const modal = document.getElementById("modalEditarImagenEmpleado");

    if (!modal) {
      console.error("No se encontró #modalEditarImagenEmpleado.");

      Swal.fire({
        icon: "error",
        title: "Modal no encontrado",
        text: "No se encontró el modal para actualizar la imagen del empleado.",
        confirmButtonText: "Entendido",
      });

      return;
    }

    //=================================================
    // ASIGNAR ID EMPLEADO
    //=================================================

    const inputIdEmpleado = document.getElementById("editarImagenIdEmpleado");

    if (inputIdEmpleado) {
      inputIdEmpleado.value = idEmpleado;
    }

    //=================================================
    // REINICIAR INPUT IMAGEN
    //=================================================

    const inputImagen = document.getElementById("imagenEmpleadoEditar");

    if (inputImagen) {
      inputImagen.value = "";
    }

    //=================================================
    // LIMPIAR ERROR
    //=================================================

    const errorImagen = document.getElementById("errorImagenEmpleadoEditar");

    if (errorImagen) {
      errorImagen.textContent = "";

      inputImagen?.classList.remove("is-invalid");
    }

    //=================================================
    // ABRIR MODAL
    //=================================================

    const instanciaModal = bootstrap.Modal.getOrCreateInstance(modal);

    instanciaModal.show();
  });
}
//=====================================================
// ABRIR MODAL EDITAR EMPLEADO
//=====================================================

async function abrirModalEditarEmpleado(idEmpleado) {
  try {
    idEmpleado = Number(idEmpleado);

    if (!Number.isInteger(idEmpleado) || idEmpleado <= 0) {
      throw new Error("El identificador del empleado no es válido.");
    }

    let modal = document.getElementById("modalEditarEmpleado");

    if (!modal) {
      modal = cargarModalEditarEmpleado();
    }

    if (!modal) {
      throw new Error("No se encontró el modal de edición del empleado.");
    }

    //=================================================
    // ASEGURAR EVENTOS
    //=================================================

    inicializarEventosModalEditar();

    mostrarEstadoCargandoModalEditar();

    const instanciaModal = bootstrap.Modal.getOrCreateInstance(modal);

    instanciaModal.show();

    await obtenerDatosEmpleadoEditar(idEmpleado);
  } catch (error) {
    console.error("Error al abrir modal de edición:", error);

    if (typeof Swal !== "undefined") {
      Swal.fire({
        icon: "error",
        title: "No se pudo abrir la edición",
        text: error.message || "Ocurrió un error al cargar el empleado.",
        confirmButtonText: "Entendido",
      });
    } else {
      alert(error.message || "Ocurrió un error al cargar el empleado.");
    }
  }
}

//=====================================================
// CARGAR / VERIFICAR MODAL EDITAR EMPLEADO
//=====================================================

function cargarModalEditarEmpleado() {
  const modal = document.getElementById("modalEditarEmpleado");

  if (!modal) {
    throw new Error("No se encontró #modalEditarEmpleado en la página.");
  }

  inicializarEventosModalEditar();

  return modal;
}

//=====================================================
// MOSTRAR ESTADO CARGANDO
//=====================================================

function mostrarEstadoCargandoModalEditar() {
  const formulario = document.getElementById("formEditarEmpleado");

  if (!formulario) {
    return;
  }

  formulario
    .querySelectorAll("input, select, textarea, button")
    .forEach((elemento) => {
      if (elemento.getAttribute("data-bs-dismiss") === "modal") {
        return;
      }

      elemento.disabled = true;
    });

  const mensaje = document.getElementById("mensajeEditarEmpleado");

  if (mensaje) {
    mensaje.style.display = "block";

    mensaje.className = "alert alert-info mb-3";

    mensaje.innerHTML = `
            <div
                class="d-flex align-items-center"
            >

                <div
                    class="
                        spinner-border
                        spinner-border-sm
                        me-2
                    "
                    role="status"
                ></div>

                <span>
                    Cargando información del empleado...
                </span>

            </div>
        `;
  }
}

//=====================================================
// OBTENER DATOS DEL EMPLEADO
//=====================================================

async function obtenerDatosEmpleadoEditar(idEmpleado) {
  if (solicitudEditarEmpleadoActual) {
    solicitudEditarEmpleadoActual.abort();
  }

  solicitudEditarEmpleadoActual = new AbortController();

  const parametros = new URLSearchParams({
    id_empleado: idEmpleado,
  });

  try {
    const respuesta = await fetch(
      "ajax/obtener_empleado.php?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        cache: "no-store",

        signal: solicitudEditarEmpleadoActual.signal,
      },
    );

    const textoRespuesta = await respuesta.text();

    if (!respuesta.ok) {
      console.error("Respuesta obtener empleado:", textoRespuesta);

      throw new Error("Error HTTP " + respuesta.status);
    }

    let resultado;

    try {
      resultado = JSON.parse(textoRespuesta);
    } catch (errorJSON) {
      console.error("JSON obtener empleado:", textoRespuesta);

      throw new Error("El servidor no devolvió JSON válido.");
    }

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje || "No se pudo obtener la información del empleado.",
      );
    }

    const empleado = resultado.data?.empleado || resultado.data;

    if (!empleado) {
      throw new Error("No se encontraron datos del empleado.");
    }

    //=================================================
    // RELLENAR DATOS BÁSICOS
    //=================================================

    rellenarFormularioEditarEmpleado(empleado);

    //=================================================
    // CARGAR UBICACIÓN
    //=================================================

    await cargarUbicacionEmpleadoEditar(empleado);

    //=================================================
    // CARGAR ROL DEL EMPLEADO
    //=================================================

    await cargarRolesEditarEmpleado(empleado.id_rol || "");

    //=================================================
    // ESTADO
    //=================================================

    const selectEstado = document.getElementById("editar_estado");

    if (selectEstado && empleado.estado) {
      selectEstado.value = empleado.estado;
    }

    //=================================================
    // OCULTAR MENSAJE
    //=================================================

    const mensaje = document.getElementById("mensajeEditarEmpleado");

    if (mensaje) {
      mensaje.style.display = "none";

      mensaje.innerHTML = "";
    }

    //=================================================
    // HABILITAR FORMULARIO
    //=================================================

    habilitarFormularioEditar();
  } catch (error) {
    if (error.name === "AbortError") {
      return;
    }

    console.error("Error al obtener empleado:", error);

    const mensaje = document.getElementById("mensajeEditarEmpleado");

    if (mensaje) {
      mensaje.style.display = "block";

      mensaje.className = "alert alert-danger mb-3";

      mensaje.innerHTML = `
                <i
                    class="
                        bi
                        bi-exclamation-triangle-fill
                        me-2
                    "
                ></i>

                ${escapeHTML(error.message)}
            `;
    }

    habilitarFormularioEditar();
  }
}

//=====================================================
// RELLENAR FORMULARIO
//=====================================================

function rellenarFormularioEditarEmpleado(empleado) {
  establecerValorCampo("editar_id_empleado", empleado.id_empleado);

  establecerValorCampo("editar_dni", empleado.dni);

  establecerValorCampo("editar_celular", empleado.celular);

  establecerValorCampo("editar_nombre", empleado.nombre);

  establecerValorCampo("editar_apellido", empleado.apellido);

  establecerValorCampo("editar_email", empleado.email);

  establecerValorCampo("editar_direccion", empleado.direccion);

  const contrasena = document.getElementById("editar_contrasena");

  if (contrasena) {
    contrasena.value = "";
  }

  establecerValorCampo("editar_id_rol", empleado.id_rol);

  establecerValorCampo("editar_estado", empleado.estado);

  //=================================================
  // NO INTENTAMOS SELECCIONAR PAÍS AQUÍ.
  //
  // Primero se cargan las opciones de países
  // mediante cargarPaisesEditar().
  //=================================================

  establecerValorCampo("editar_id_pais", "");

  establecerValorCampo("editar_id_departamento", "");

  establecerValorCampo("editar_id_provincia", "");

  establecerValorCampo("editar_id_distrito", "");
}

//=====================================================
// ESTABLECER VALOR CAMPO
//=====================================================

function establecerValorCampo(id, valor) {
  const campo = document.getElementById(id);

  if (!campo) {
    return;
  }

  campo.value = valor !== null && valor !== undefined ? valor : "";
}

//=====================================================
// CARGAR UBICACIÓN DEL EMPLEADO
//=====================================================

async function cargarUbicacionEmpleadoEditar(empleado) {
  const selectPais = document.getElementById("editar_id_pais");

  const selectDepartamento = document.getElementById("editar_id_departamento");

  const selectProvincia = document.getElementById("editar_id_provincia");

  const selectDistrito = document.getElementById("editar_id_distrito");

  if (!selectPais) {
    console.error("No se encontró #editar_id_pais.");

    return;
  }

  //=================================================
  // LIMPIAR UBICACIÓN
  //=================================================

  selectPais.innerHTML = `
        <option value="">
            Cargando países...
        </option>
    `;

  selectPais.disabled = true;

  if (selectDepartamento) {
    selectDepartamento.innerHTML = `
            <option value="">
                Seleccionar departamento
            </option>
        `;

    selectDepartamento.disabled = true;
  }

  if (selectProvincia) {
    selectProvincia.innerHTML = `
            <option value="">
                Seleccionar provincia
            </option>
        `;

    selectProvincia.disabled = true;
  }

  if (selectDistrito) {
    selectDistrito.innerHTML = `
            <option value="">
                Seleccionar distrito
            </option>
        `;

    selectDistrito.disabled = true;
  }

  //=================================================
  // CARGAR PAÍSES
  //=================================================

  await cargarPaisesEditar(empleado.id_pais || "");

  //=================================================
  // SI NO HAY PAÍS
  //=================================================

  if (!empleado.id_pais) {
    return;
  }

  //=================================================
  // CARGAR DEPARTAMENTOS
  //=================================================

  await cargarDepartamentosEditar(
    empleado.id_pais,
    empleado.id_departamento || "",
  );

  //=================================================
  // CARGAR PROVINCIAS
  //=================================================

  if (empleado.id_departamento) {
    await cargarProvinciasEditar(
      empleado.id_departamento,
      empleado.id_provincia || "",
    );
  }

  //=================================================
  // CARGAR DISTRITOS
  //=================================================

  if (empleado.id_provincia) {
    await cargarDistritosEditar(
      empleado.id_provincia,
      empleado.id_distrito || "",
    );
  }
}

//=====================================================
// CARGAR PAÍSES
//=====================================================

async function cargarPaisesEditar(idPaisSeleccionado = "") {
  const selectPais = document.getElementById("editar_id_pais");

  if (!selectPais) {
    console.error("No se encontró #editar_id_pais.");

    return;
  }

  selectPais.disabled = true;

  selectPais.innerHTML = `
        <option value="">
            Cargando países...
        </option>
    `;

  try {
    //=================================================
    // ENDPOINT
    //=================================================

    const respuesta = await fetch("ajax/adm_obtener_pais.php", {
      method: "GET",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },

      cache: "no-store",
    });

    const texto = await respuesta.text();

    if (!respuesta.ok) {
      console.error("Respuesta países:", texto);

      throw new Error("No se pudieron cargar los países.");
    }

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (errorJSON) {
      console.error("JSON países:", texto);

      throw new Error("El servidor no devolvió JSON válido para los países.");
    }

    //=================================================
    // SOPORTAR:
    // estado: true
    // estado: 1
    // success: true
    //=================================================

    if (
      !resultado ||
      (resultado.estado !== true &&
        resultado.estado !== 1 &&
        resultado.success !== true)
    ) {
      throw new Error(
        resultado?.mensaje || "No se pudieron cargar los países.",
      );
    }

    const datos = Array.isArray(resultado.data) ? resultado.data : [];

    //=================================================
    // RECONSTRUIR SELECT
    //=================================================

    selectPais.innerHTML = `
            <option value="">
                Seleccionar país
            </option>
        `;

    //=================================================
    // AGREGAR PAÍSES
    //=================================================

    datos.forEach((pais) => {
      const option = document.createElement("option");

      option.value = pais.id_pais;

      option.textContent = pais.nombre;

      //=========================================
      // SELECCIONAR PAÍS DEL EMPLEADO
      //=========================================

      if (String(pais.id_pais) === String(idPaisSeleccionado)) {
        option.selected = true;
      }

      selectPais.appendChild(option);
    });

    //=================================================
    // HABILITAR SELECT
    //=================================================

    selectPais.disabled = false;

    //=================================================
    // VALIDAR QUE REALMENTE SE SELECCIONÓ
    //=================================================

    if (
      idPaisSeleccionado &&
      String(selectPais.value) !== String(idPaisSeleccionado)
    ) {
      console.warn(
        "El país del empleado no existe entre los países disponibles.",
        {
          idPaisEmpleado: idPaisSeleccionado,
          valorSelect: selectPais.value,
          paises: datos,
        },
      );
    }
  } catch (error) {
    console.error("Error al cargar países:", error);

    selectPais.innerHTML = `
            <option value="">
                Error al cargar países
            </option>
        `;

    selectPais.disabled = true;

    throw error;
  }
}

//=====================================================
// CARGAR DEPARTAMENTOS
//=====================================================

async function cargarDepartamentosEditar(
  idPais,
  idDepartamentoSeleccionado = "",
) {
  const select = document.getElementById("editar_id_departamento");

  if (!select) {
    return;
  }

  select.disabled = true;

  select.innerHTML = `
        <option value="">
            Cargando departamentos...
        </option>
    `;

  if (!idPais) {
    select.innerHTML = `
            <option value="">
                Seleccionar departamento
            </option>
        `;

    return;
  }

  try {
    const parametros = new URLSearchParams({
      id_pais: idPais,
    });

    const respuesta = await fetch(
      "ajax/adm_obtener_departamentos.php?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        cache: "no-store",
      },
    );

    const texto = await respuesta.text();

    if (!respuesta.ok) {
      console.error("Respuesta departamentos:", texto);

      throw new Error("No se pudieron cargar los departamentos.");
    }

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (errorJSON) {
      console.error("JSON departamentos:", texto);

      throw new Error(
        "El servidor no devolvió JSON válido para los departamentos.",
      );
    }

    if (
      !resultado ||
      (resultado.estado !== true &&
        resultado.estado !== 1 &&
        resultado.success !== true)
    ) {
      throw new Error(
        resultado?.mensaje || "No se pudieron cargar los departamentos.",
      );
    }

    const datos = Array.isArray(resultado.data) ? resultado.data : [];

    select.innerHTML = `
            <option value="">
                Seleccionar departamento
            </option>
        `;

    datos.forEach((departamento) => {
      const option = document.createElement("option");

      option.value = departamento.id_departamento;

      option.textContent = departamento.nombre;

      if (
        String(departamento.id_departamento) ===
        String(idDepartamentoSeleccionado)
      ) {
        option.selected = true;
      }

      select.appendChild(option);
    });

    select.disabled = false;
  } catch (error) {
    console.error("Error departamentos:", error);

    select.innerHTML = `
            <option value="">
                Error al cargar departamentos
            </option>
        `;

    select.disabled = true;

    throw error;
  }
}

//=====================================================
// CARGAR PROVINCIAS
//=====================================================

async function cargarProvinciasEditar(
  idDepartamento,
  idProvinciaSeleccionada = "",
) {
  const select = document.getElementById("editar_id_provincia");

  if (!select) {
    return;
  }

  select.disabled = true;

  select.innerHTML = `
        <option value="">
            Cargando provincias...
        </option>
    `;

  if (!idDepartamento) {
    select.innerHTML = `
            <option value="">
                Seleccionar provincia
            </option>
        `;

    return;
  }

  try {
    const parametros = new URLSearchParams({
      id_departamento: idDepartamento,
    });

    const respuesta = await fetch(
      "ajax/adm_obtener_provincias.php?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        cache: "no-store",
      },
    );

    const texto = await respuesta.text();

    if (!respuesta.ok) {
      console.error("Respuesta provincias:", texto);

      throw new Error("No se pudieron cargar las provincias.");
    }

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (errorJSON) {
      console.error("JSON provincias:", texto);

      throw new Error(
        "El servidor no devolvió JSON válido para las provincias.",
      );
    }

    if (
      !resultado ||
      (resultado.estado !== true &&
        resultado.estado !== 1 &&
        resultado.success !== true)
    ) {
      throw new Error(
        resultado?.mensaje || "No se pudieron cargar las provincias.",
      );
    }

    const datos = Array.isArray(resultado.data) ? resultado.data : [];

    select.innerHTML = `
            <option value="">
                Seleccionar provincia
            </option>
        `;

    datos.forEach((provincia) => {
      const option = document.createElement("option");

      option.value = provincia.id_provincia;

      option.textContent = provincia.nombre;

      if (String(provincia.id_provincia) === String(idProvinciaSeleccionada)) {
        option.selected = true;
      }

      select.appendChild(option);
    });

    select.disabled = false;
  } catch (error) {
    console.error("Error provincias:", error);

    select.innerHTML = `
            <option value="">
                Error al cargar provincias
            </option>
        `;

    select.disabled = true;

    throw error;
  }
}

//=====================================================
// CARGAR DISTRITOS
//=====================================================

async function cargarDistritosEditar(idProvincia, idDistritoSeleccionado = "") {
  const select = document.getElementById("editar_id_distrito");

  if (!select) {
    return;
  }

  select.disabled = true;

  select.innerHTML = `
        <option value="">
            Cargando distritos...
        </option>
    `;

  if (!idProvincia) {
    select.innerHTML = `
            <option value="">
                Seleccionar distrito
            </option>
        `;

    return;
  }

  try {
    const parametros = new URLSearchParams({
      id_provincia: idProvincia,
    });

    const respuesta = await fetch(
      "ajax/adm_obtener_distritos.php?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        cache: "no-store",
      },
    );

    const texto = await respuesta.text();

    if (!respuesta.ok) {
      console.error("Respuesta distritos:", texto);

      throw new Error("No se pudieron cargar los distritos.");
    }

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (errorJSON) {
      console.error("JSON distritos:", texto);

      throw new Error(
        "El servidor no devolvió JSON válido para los distritos.",
      );
    }

    if (
      !resultado ||
      (resultado.estado !== true &&
        resultado.estado !== 1 &&
        resultado.success !== true)
    ) {
      throw new Error(
        resultado?.mensaje || "No se pudieron cargar los distritos.",
      );
    }

    const datos = Array.isArray(resultado.data) ? resultado.data : [];

    select.innerHTML = `
            <option value="">
                Seleccionar distrito
            </option>
        `;

    datos.forEach((distrito) => {
      const option = document.createElement("option");

      option.value = distrito.id_distrito;

      option.textContent = distrito.nombre;

      if (String(distrito.id_distrito) === String(idDistritoSeleccionado)) {
        option.selected = true;
      }

      select.appendChild(option);
    });

    select.disabled = false;
  } catch (error) {
    console.error("Error distritos:", error);

    select.innerHTML = `
            <option value="">
                Error al cargar distritos
            </option>
        `;

    select.disabled = true;

    throw error;
  }
}

//=====================================================
// INICIALIZAR EVENTOS DEL MODAL
//=====================================================

function inicializarEventosModalEditar() {
  //=================================================
  // EVITAR REGISTRAR LOS EVENTOS DOS VECES
  //=================================================

  if (eventosModalEditarInicializados) {
    return;
  }

  const selectPais = document.getElementById("editar_id_pais");

  const selectDepartamento = document.getElementById("editar_id_departamento");

  const selectProvincia = document.getElementById("editar_id_provincia");

  const selectRol = document.getElementById("editar_id_rol");

  const formulario = document.getElementById("formEditarEmpleado");

  const btnMostrarContrasena = document.getElementById(
    "btnMostrarContrasenaEditar",
  );

  //=================================================
  // SI EL MODAL AÚN NO EXISTE
  //=================================================

  if (
    !selectPais &&
    !selectDepartamento &&
    !selectProvincia &&
    !selectRol &&
    !formulario
  ) {
    return;
  }

  //=================================================
  // PAÍS
  //=================================================

  if (selectPais) {
    selectPais.addEventListener("change", async function () {
      const valorPais = this.value;

      const provincia = document.getElementById("editar_id_provincia");

      const distrito = document.getElementById("editar_id_distrito");

      //=========================================
      // LIMPIAR PROVINCIA
      //=========================================

      if (provincia) {
        provincia.innerHTML = `
                        <option value="">
                            Seleccionar provincia
                        </option>
                    `;

        provincia.disabled = true;
      }

      //=========================================
      // LIMPIAR DISTRITO
      //=========================================

      if (distrito) {
        distrito.innerHTML = `
                        <option value="">
                            Seleccionar distrito
                        </option>
                    `;

        distrito.disabled = true;
      }

      //=========================================
      // CARGAR DEPARTAMENTOS
      //=========================================

      try {
        await cargarDepartamentosEditar(valorPais);
      } catch (error) {
        console.error("Error al cambiar país:", error);
      }
    });
  }

  //=================================================
  // DEPARTAMENTO
  //=================================================

  if (selectDepartamento) {
    selectDepartamento.addEventListener("change", async function () {
      const valorDepartamento = this.value;

      const distrito = document.getElementById("editar_id_distrito");

      if (distrito) {
        distrito.innerHTML = `
                        <option value="">
                            Seleccionar distrito
                        </option>
                    `;

        distrito.disabled = true;
      }

      try {
        await cargarProvinciasEditar(valorDepartamento);
      } catch (error) {
        console.error("Error al cambiar departamento:", error);
      }
    });
  }

  //=================================================
  // PROVINCIA
  //=================================================

  if (selectProvincia) {
    selectProvincia.addEventListener("change", async function () {
      try {
        await cargarDistritosEditar(this.value);
      } catch (error) {
        console.error("Error al cambiar provincia:", error);
      }
    });
  }

  //=================================================
  // ROL
  //=================================================

  if (selectRol) {
    selectRol.addEventListener("change", function () {
      cargarPermisosRolEditar(this.value);
    });
  }

  //=================================================
  // MOSTRAR CONTRASEÑA
  //=================================================

  if (btnMostrarContrasena) {
    btnMostrarContrasena.addEventListener("click", function () {
      const input = document.getElementById("editar_contrasena");

      const icono = document.getElementById("iconoMostrarContrasenaEditar");

      if (!input) {
        return;
      }

      if (input.type === "password") {
        input.type = "text";

        if (icono) {
          icono.className = "bi bi-eye-slash";
        }

        this.title = "Ocultar contraseña";
      } else {
        input.type = "password";

        if (icono) {
          icono.className = "bi bi-eye";
        }

        this.title = "Mostrar contraseña";
      }
    });
  }

  //=================================================
  // FORMULARIO
  //=================================================

  if (formulario) {
    formulario.addEventListener("submit", guardarCambiosEmpleado);
  }

  eventosModalEditarInicializados = true;
}
//=====================================================
// CARGAR PERMISOS DEL ROL
//=====================================================

async function cargarPermisosRolEditar(idRol) {
  const contenedor = document.getElementById("editar_contenedorPermisos");

  const tabla = document.getElementById("editar_tablaPermisos");

  const mensajeSinRol = document.getElementById("editar_mensajeSinRol");

  const mensajeSinPermisos = document.getElementById(
    "editar_mensajeSinPermisos",
  );

  const estadoCarga = document.getElementById("editar_estadoCargaPermisos");

  //=================================================
  // VALIDAR ELEMENTOS
  //=================================================

  if (!contenedor || !tabla) {
    console.error("No se encontraron los elementos del módulo de permisos.");

    return;
  }

  //=================================================
  // SIN ROL
  //=================================================

  if (!idRol || idRol === "") {
    contenedor.style.display = "none";

    if (mensajeSinRol) {
      mensajeSinRol.style.display = "block";
    }

    if (mensajeSinPermisos) {
      mensajeSinPermisos.style.display = "none";
    }

    if (estadoCarga) {
      estadoCarga.innerHTML = `
                <i class="bi bi-info-circle me-1"></i>
                Seleccione un rol para consultar sus permisos.
            `;
    }

    tabla.innerHTML = "";

    return;
  }

  //=================================================
  // ESTADO CARGANDO
  //=================================================

  if (mensajeSinRol) {
    mensajeSinRol.style.display = "none";
  }

  if (mensajeSinPermisos) {
    mensajeSinPermisos.style.display = "none";
  }

  contenedor.style.display = "none";

  tabla.innerHTML = "";

  if (estadoCarga) {
    estadoCarga.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-1"
                role="status">
            </span>

            Cargando permisos...
        `;
  }

  //=================================================
  // AJAX
  //=================================================

  try {
    const parametros = new URLSearchParams({
      id_rol: idRol,
    });

    console.log("Cargando permisos del rol:", idRol);

    const respuesta = await fetch(
      "ajax/obtener_permisos_rol_empleado.php?" + parametros.toString(),
      {
        method: "GET",

        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },

        cache: "no-store",
      },
    );

    const texto = await respuesta.text();

    console.log("Respuesta permisos:", texto);

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (errorJSON) {
      console.error("Respuesta no válida:", texto);

      throw new Error("El servidor no devolvió JSON válido.");
    }

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje || "No se pudieron cargar los permisos.",
      );
    }

    const permisos = Array.isArray(resultado.data) ? resultado.data : [];

    //=================================================
    // SIN PERMISOS
    //=================================================

    if (permisos.length === 0) {
      contenedor.style.display = "none";

      if (mensajeSinPermisos) {
        mensajeSinPermisos.style.display = "block";
      }

      if (estadoCarga) {
        estadoCarga.innerHTML = `
                    <i class="bi bi-shield-x me-1"></i>
                    Este rol no tiene permisos configurados.
                `;
      }

      return;
    }

    //=================================================
    // CONSTRUIR TABLA
    //=================================================

    tabla.innerHTML = "";

    permisos.forEach(function (permiso) {
      const fila = document.createElement("tr");

      fila.innerHTML = `

                <td class="ps-4">

                    <div class="fw-semibold">

                        ${escapeHTML(permiso.nombre || "")}

                    </div>

                    ${
                      permiso.codigo
                        ? `
                                <small class="text-muted">
                                    ${escapeHTML(permiso.codigo)}
                                </small>
                              `
                        : ""
                    }

                </td>


                <td class="text-center">

                    <div
                        class="form-check d-flex justify-content-center">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="permisos[${permiso.id_modulo}][ver]"
                            value="1"
                            ${Number(permiso.ver) === 1 ? "checked" : ""}
                        >

                    </div>

                </td>


                <td class="text-center">

                    <div
                        class="form-check d-flex justify-content-center">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="permisos[${permiso.id_modulo}][crear]"
                            value="1"
                            ${Number(permiso.crear) === 1 ? "checked" : ""}
                        >

                    </div>

                </td>


                <td class="text-center">

                    <div
                        class="form-check d-flex justify-content-center">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="permisos[${permiso.id_modulo}][editar]"
                            value="1"
                            ${Number(permiso.editar) === 1 ? "checked" : ""}
                        >

                    </div>

                </td>


                <td class="text-center">

                    <div
                        class="form-check d-flex justify-content-center">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="permisos[${permiso.id_modulo}][eliminar]"
                            value="1"
                            ${Number(permiso.eliminar) === 1 ? "checked" : ""}
                        >

                    </div>

                </td>

            `;

      tabla.appendChild(fila);
    });

    //=================================================
    // MOSTRAR TABLA
    //=================================================

    contenedor.style.display = "block";

    if (mensajeSinRol) {
      mensajeSinRol.style.display = "none";
    }

    if (mensajeSinPermisos) {
      mensajeSinPermisos.style.display = "none";
    }

    if (estadoCarga) {
      estadoCarga.innerHTML = `

                <i
                    class="bi bi-check-circle-fill text-success me-1">
                </i>

                Permisos cargados correctamente.

            `;
    }

    console.log("Permisos cargados:", permisos);
  } catch (error) {
    console.error("Error cargando permisos:", error);

    contenedor.style.display = "none";

    if (mensajeSinPermisos) {
      mensajeSinPermisos.style.display = "block";
    }

    if (estadoCarga) {
      estadoCarga.innerHTML = `

                <i
                    class="bi bi-exclamation-triangle-fill text-danger me-1">
                </i>

                ${escapeHTML(error.message || "Error al cargar los permisos.")}

            `;
    }
  }
}

//=====================================================
// HABILITAR FORMULARIO
//=====================================================

function habilitarFormularioEditar() {
  const formulario = document.getElementById("formEditarEmpleado");

  if (!formulario) {
    return;
  }

  formulario
    .querySelectorAll("input, select, textarea, button")
    .forEach((elemento) => {
      if (elemento.getAttribute("data-bs-dismiss") === "modal") {
        return;
      }

      elemento.disabled = false;
    });
}

//=====================================================
// GUARDAR CAMBIOS DEL EMPLEADO
//=====================================================

async function guardarCambiosEmpleado(evento) {
  evento.preventDefault();

  const formulario = evento.currentTarget;

  const botonGuardar = document.getElementById("btnGuardarCambiosEmpleado");

  const mensaje = document.getElementById("mensajeEditarEmpleado");

  if (!formulario) {
    return;
  }

  //===================================================
  // VALIDACIÓN HTML
  //===================================================

  if (!formulario.checkValidity()) {
    formulario.classList.add("was-validated");

    return;
  }

  //===================================================
  // CONFIRMACIÓN
  //===================================================

  const confirmacion = await Swal.fire({
    icon: "question",

    title: "¿Guardar cambios?",

    text: "Se actualizará la información del empleado y los permisos del rol seleccionado.",

    showCancelButton: true,

    confirmButtonText: "Sí, guardar cambios",

    cancelButtonText: "Cancelar",

    reverseButtons: true,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  //===================================================
  // DESHABILITAR BOTÓN
  //===================================================

  if (botonGuardar) {
    botonGuardar.disabled = true;

    botonGuardar.innerHTML = `
      <span
        class="spinner-border spinner-border-sm me-2"
        role="status"
      ></span>

      Guardando...
    `;
  }

  if (mensaje) {
    mensaje.style.display = "none";
    mensaje.innerHTML = "";
  }

  try {
    //=================================================
    // FORM DATA
    //=================================================

    const datos = new FormData(formulario);

    //=================================================
    // ID EMPLEADO
    //=================================================

    const idEmpleado =
      document.getElementById("editar_id_empleado")?.value || "";

    //=================================================
    // ID ROL
    //=================================================

    const idRol = document.getElementById("editar_id_rol")?.value || "";

    //=================================================
    // VALIDAR EMPLEADO
    //=================================================

    if (!idEmpleado || Number(idEmpleado) <= 0) {
      throw new Error(
        "No se pudo identificar al empleado que se desea actualizar.",
      );
    }

    //=================================================
    // VALIDAR ROL
    //=================================================

    if (!idRol || Number(idRol) <= 0) {
      throw new Error("Debe seleccionar un rol para el empleado.");
    }

    //=================================================
    // ASEGURAR ID EMPLEADO
    //=================================================

    datos.set("id_empleado", idEmpleado);

    //=================================================
    // ASEGURAR ID ROL
    //=================================================

    datos.set("id_rol", idRol);

    //=================================================
    // PROCESAR PERMISOS
    //
    // IMPORTANTE:
    //
    // FormData NO envía checkbox desmarcados.
    //
    // Por eso recorremos todos los checkbox de permisos
    // y enviamos explícitamente 1 o 0.
    //=================================================

    const checkboxesPermisos = formulario.querySelectorAll(
      '#editar_tablaPermisos input[type="checkbox"][name^="permisos["]',
    );

    console.log("Cantidad de checkbox de permisos:", checkboxesPermisos.length);

    checkboxesPermisos.forEach((checkbox) => {
      //===============================================
      // NOMBRE
      //===============================================

      const nombre = checkbox.name;

      if (!nombre) {
        return;
      }

      //===============================================
      // VALOR EXPLÍCITO
      //
      // checked     = 1
      // no checked  = 0
      //===============================================

      datos.set(nombre, checkbox.checked ? "1" : "0");
    });

    //=================================================
    // DEBUG
    //=================================================

    console.group("DATOS QUE SE ENVIARÁN AL SERVIDOR");

    for (const [clave, valor] of datos.entries()) {
      console.log(clave, "=", valor);
    }

    console.groupEnd();

    //=================================================
    // AJAX
    //=================================================

    const respuesta = await fetch("ajax/actualizar_empleado.php", {
      method: "POST",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },

      body: datos,

      cache: "no-store",
    });

    //=================================================
    // RESPUESTA
    //=================================================

    const texto = await respuesta.text();

    console.log("Respuesta actualizar empleado:", texto);

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    //=================================================
    // JSON
    //=================================================

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (errorJSON) {
      console.error("JSON actualizar empleado:", texto);

      throw new Error("El servidor no devolvió JSON válido.");
    }

    //=================================================
    // VALIDAR RESULTADO
    //=================================================

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado?.mensaje || "No se pudieron guardar los cambios.",
      );
    }

    //=================================================
    // CERRAR MODAL
    //=================================================

    const modal = document.getElementById("modalEditarEmpleado");

    if (modal) {
      const instanciaModal = bootstrap.Modal.getInstance(modal);

      if (instanciaModal) {
        instanciaModal.hide();
      }
    }

    //=================================================
    // MENSAJE ÉXITO
    //=================================================

    await Swal.fire({
      icon: "success",

      title: "Empleado actualizado",

      text:
        resultado.mensaje ||
        "Los datos del empleado y los permisos del rol fueron actualizados correctamente.",

      confirmButtonText: "Entendido",
    });

    //=================================================
    // ACTUALIZAR VISTA SEGÚN LA PÁGINA ACTUAL
    //=================================================
    //
    // Si estamos en adm_detalles_empleado.php,
    // la página debe volver a consultar:
    // ajax/obtener_detalles_empleado.php
    //
    // Si estamos en adm_lista_empleados.php,
    // solamente actualizamos KPI y listado.
    //

    const contenedorDetalleEmpleado = document.getElementById(
      "contenedorDetalleEmpleado",
    );

    //=================================================
    // ESTAMOS EN DETALLES DEL EMPLEADO
    //=================================================

    if (contenedorDetalleEmpleado) {
      window.location.reload();

      return;
    }

    //=================================================
    // ESTAMOS EN LA LISTA DE EMPLEADOS
    //=================================================

    await cargarKPIEmpleados();

    await cargarEmpleados(paginaActualEmpleados);
  } catch (error) {
    console.error("Error al actualizar empleado:", error);

    //=================================================
    // MOSTRAR ERROR
    //=================================================

    if (mensaje) {
      mensaje.style.display = "block";

      mensaje.className = "alert alert-danger mb-3";

      mensaje.innerHTML = `
        <i
          class="
            bi
            bi-exclamation-triangle-fill
            me-2
          "
        ></i>

        ${escapeHTML(
          error.message || "Ocurrió un error al guardar los cambios.",
        )}
      `;

      mensaje.scrollIntoView({
        behavior: "smooth",
        block: "nearest",
      });
    }
  } finally {
    //=================================================
    // RESTAURAR BOTÓN
    //=================================================

    if (botonGuardar) {
      botonGuardar.disabled = false;

      botonGuardar.innerHTML = `
        <i
          class="bi bi-check-circle me-2"
        ></i>

        Guardar cambios
      `;
    }
  }
}
