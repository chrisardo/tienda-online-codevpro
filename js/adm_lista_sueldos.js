//=====================================================
// CoDevPro Technology
// Archivo: js/adm_lista_sueldos.js
// Módulo: Sueldos y Pagos
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActual = 1;

let temporizadorBusquedaSueldo = null;

let modalSueldo = null;

let empleadosCargados = false;

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarModalSueldo();

  inicializarEventosSueldos();

  cargarEmpleados();

  cargarSueldos();
});

//=====================================================
// INICIALIZAR MODAL
//=====================================================

function inicializarModalSueldo() {
  const elementoModal = document.getElementById("modalSueldo");

  if (!elementoModal) {
    console.warn("No se encontró el modal #modalSueldo.");

    return;
  }

  if (typeof bootstrap === "undefined" || !bootstrap.Modal) {
    console.error("Bootstrap Modal no está disponible.");

    return;
  }

  modalSueldo = bootstrap.Modal.getOrCreateInstance(elementoModal);
}

//=====================================================
// INICIALIZAR EVENTOS
//=====================================================

function inicializarEventosSueldos() {
  const buscar = document.getElementById("buscarSueldo");

  const estado = document.getElementById("filtroEstadoSueldo");

  const tipo = document.getElementById("filtroTipoSueldo");

  const btnLimpiar = document.getElementById("btnLimpiarFiltros");

  const btnNuevo = document.getElementById("btnNuevoSueldo");

  const formulario = document.getElementById("formSueldo");

  const empleado = document.getElementById("idEmpleadoSueldo");

  //=================================================
  // BUSCADOR
  //=================================================

  if (buscar) {
    buscar.addEventListener("input", function () {
      clearTimeout(temporizadorBusquedaSueldo);

      temporizadorBusquedaSueldo = setTimeout(function () {
        paginaActual = 1;

        cargarSueldos();
      }, 350);
    });
  }

  //=================================================
  // FILTRO ESTADO
  //=================================================

  if (estado) {
    estado.addEventListener("change", function () {
      paginaActual = 1;

      cargarSueldos();
    });
  }

  //=================================================
  // FILTRO PERIODICIDAD
  //=================================================

  if (tipo) {
    tipo.addEventListener("change", function () {
      paginaActual = 1;

      cargarSueldos();
    });
  }

  //=================================================
  // LIMPIAR FILTROS
  //=================================================

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", function () {
      limpiarFiltros();
    });
  }

  //=================================================
  // NUEVO SUELDO
  //=================================================

  if (btnNuevo) {
    btnNuevo.addEventListener("click", function () {
      abrirNuevoSueldo();
    });
  }

  //=================================================
  // FORMULARIO
  //=================================================

  if (formulario) {
    formulario.addEventListener("submit", function (event) {
      guardarSueldo(event);
    });
  }

  //=================================================
  // EMPLEADO
  //=================================================

  if (empleado) {
    empleado.addEventListener("change", function () {
      mostrarInformacionEmpleado();
    });
  }
}

//=====================================================
// CARGAR SUELDOS
//=====================================================

async function cargarSueldos() {
  const tabla = document.getElementById("tablaSueldos");

  if (!tabla) {
    return;
  }

  //=================================================
  // ESTADO DE CARGA
  //=================================================

  tabla.innerHTML = `
        <tr>
            <td
                colspan="7"
                class="text-center py-5"
            >

                <div
                    class="spinner-border text-primary"
                    role="status"
                >

                    <span class="visually-hidden">
                        Cargando...
                    </span>

                </div>

                <div class="text-muted mt-2">
                    Cargando sueldos...
                </div>

            </td>
        </tr>
    `;

  //=================================================
  // OBTENER FILTROS
  //=================================================

  const buscar = document.getElementById("buscarSueldo")?.value.trim() || "";

  const estado = document.getElementById("filtroEstadoSueldo")?.value || "";

  const tipoBase = document.getElementById("filtroTipoSueldo")?.value || "";

  //=================================================
  // PARÁMETROS
  //=================================================

  const parametros = new URLSearchParams();

  parametros.append("buscar", buscar);

  parametros.append("estado", estado);

  parametros.append("tipo_base", tipoBase);

  parametros.append("pagina", paginaActual);

  //=================================================
  // SOLICITUD AJAX
  //=================================================

  try {
    const respuesta = await fetch(
      "ajax/listar_sueldos.php?" + parametros.toString(),
      {
        method: "GET",

        credentials: "same-origin",

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    //=================================================
    // VALIDAR HTTP
    //=================================================

    const textoRespuesta = await respuesta.text();

    let data = null;

    try {
      data = JSON.parse(textoRespuesta);
    } catch (error) {
      console.error("Respuesta no JSON de listar_empleados_sueldo.php:");
      console.error(textoRespuesta);

      throw new Error(
        "El servidor devolvió una respuesta inválida. Revise el error PHP.",
      );
    }

    if (!respuesta.ok) {
      throw new Error(
        data?.mensaje || "El servidor respondió con HTTP " + respuesta.status,
      );
    }

    console.log("Respuesta listar_sueldos:", data);

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!data || data.success !== true) {
      throw new Error(
        data?.mensaje || "No se pudo cargar la lista de sueldos.",
      );
    }

    //=================================================
    // INSERTAR TABLA
    //=================================================

    if (data.tabla && data.tabla.trim() !== "") {
      tabla.innerHTML = data.tabla;
    } else {
      tabla.innerHTML = `
                <tr>
                    <td
                        colspan="7"
                        class="text-center text-muted py-5"
                    >

                        <i
                            class="bi bi-inbox fs-2 d-block mb-2"
                        ></i>

                        No se encontraron registros.

                    </td>
                </tr>
            `;
    }

    //=================================================
    // ACTUALIZAR KPI
    //=================================================

    if (data.kpi) {
      actualizarKPI(data.kpi);
    } else {
      console.warn("listar_sueldos.php no devolvió información KPI.");
    }

    //=================================================
    // PAGINACIÓN
    //=================================================

    actualizarPaginacion(data.paginacion);

    //=================================================
    // CONTADOR
    //=================================================

    actualizarContador(data.paginacion);
  } catch (error) {
    console.error("Error cargarSueldos:", error);

    tabla.innerHTML = `
            <tr>
                <td
                    colspan="7"
                    class="text-center text-danger py-5"
                >

                    <i
                        class="bi bi-exclamation-triangle fs-2 d-block mb-2"
                    ></i>

                    <div>
                        No se pudieron cargar los sueldos.
                    </div>

                    <small class="text-muted">
                        ${escapeHtml(error.message)}
                    </small>

                </td>
            </tr>
        `;
  }
}

//=====================================================
// ACTUALIZAR KPI
//=====================================================

function actualizarKPI(kpi) {
  console.log("KPI recibidos:", kpi);

  //=================================================
  // VALIDAR
  //=================================================

  if (!kpi || typeof kpi !== "object") {
    console.warn("El servidor no devolvió un objeto KPI válido.");

    return;
  }

  //=================================================
  // ELEMENTOS
  //=================================================

  const empleados = document.getElementById("kpiEmpleados");

  const activos = document.getElementById("kpiSueldosActivos");

  const sinSueldo = document.getElementById("kpiSinSueldo");

  const nomina = document.getElementById("kpiNomina");

  //=================================================
  // EMPLEADOS
  //=================================================

  if (empleados) {
    empleados.textContent = formatearNumero(Number(kpi.empleados ?? 0));
  }

  //=================================================
  // SUELDOS ACTIVOS
  //=================================================

  if (activos) {
    activos.textContent = formatearNumero(Number(kpi.sueldos_activos ?? 0));
  }

  //=================================================
  // SIN SUELDO
  //=================================================

  if (sinSueldo) {
    sinSueldo.textContent = formatearNumero(Number(kpi.sin_sueldo ?? 0));
  }

  //=================================================
  // NÓMINA MENSUAL
  //=================================================

  if (nomina) {
    nomina.textContent =
      "S/ " + formatearMoneda(Number(kpi.nomina_mensual ?? 0));
  }
}

//=====================================================
// ACTUALIZAR CONTADOR
//=====================================================

function actualizarContador(paginacion) {
  const contador = document.getElementById("contadorSueldos");

  if (!contador || !paginacion) {
    return;
  }

  contador.textContent =
    formatearNumero(Number(paginacion.total || 0)) + " registros";
}

//=====================================================
// ACTUALIZAR PAGINACIÓN
//=====================================================

function actualizarPaginacion(data) {
  const contenedor = document.getElementById("paginacionSueldos");

  const info = document.getElementById("infoPaginacionSueldos");

  if (!contenedor || !data) {
    return;
  }

  contenedor.innerHTML = "";

  const total = Number(data.total || 0);

  const porPagina = Number(data.por_pagina || 10);

  let pagina = Number(data.pagina || 1);

  const paginas = total > 0 ? Math.ceil(total / porPagina) : 0;

  //=================================================
  // SIN REGISTROS
  //=================================================

  if (total === 0) {
    if (info) {
      info.textContent = "Mostrando 0 registros";
    }

    return;
  }

  //=================================================
  // CORREGIR PÁGINA
  //=================================================

  if (pagina < 1) {
    pagina = 1;
  }

  if (paginas > 0 && pagina > paginas) {
    pagina = paginas;

    paginaActual = pagina;
  }

  //=================================================
  // INFORMACIÓN
  //=================================================

  const inicio = (pagina - 1) * porPagina + 1;

  const fin = Math.min(pagina * porPagina, total);

  if (info) {
    info.textContent = `Mostrando ${inicio} - ${fin} de ${total} registros`;
  }

  //=================================================
  // ANTERIOR
  //=================================================

  agregarBotonPagina(contenedor, "Anterior", pagina > 1, function () {
    if (paginaActual > 1) {
      paginaActual--;

      cargarSueldos();
    }
  });

  //=================================================
  // NÚMEROS
  //=================================================

  if (paginas <= 7) {
    for (let i = 1; i <= paginas; i++) {
      agregarBotonNumero(contenedor, i, i === pagina, function () {
        paginaActual = i;

        cargarSueldos();
      });
    }
  } else {
    //=============================================
    // PRIMERA
    //=============================================

    agregarBotonNumero(contenedor, 1, pagina === 1, function () {
      paginaActual = 1;

      cargarSueldos();
    });

    //=============================================
    // ELLIPSIS IZQUIERDA
    //=============================================

    if (pagina > 4) {
      agregarTextoPaginacion(contenedor);
    }

    //=============================================
    // PÁGINAS CENTRALES
    //=============================================

    const inicioPaginas = Math.max(2, pagina - 2);

    const finPaginas = Math.min(paginas - 1, pagina + 2);

    for (let i = inicioPaginas; i <= finPaginas; i++) {
      agregarBotonNumero(contenedor, i, i === pagina, function () {
        paginaActual = i;

        cargarSueldos();
      });
    }

    //=============================================
    // ELLIPSIS DERECHA
    //=============================================

    if (pagina < paginas - 3) {
      agregarTextoPaginacion(contenedor);
    }

    //=============================================
    // ÚLTIMA
    //=============================================

    agregarBotonNumero(contenedor, paginas, pagina === paginas, function () {
      paginaActual = paginas;

      cargarSueldos();
    });
  }

  //=================================================
  // SIGUIENTE
  //=================================================

  agregarBotonPagina(contenedor, "Siguiente", pagina < paginas, function () {
    if (paginaActual < paginas) {
      paginaActual++;

      cargarSueldos();
    }
  });
}

//=====================================================
// BOTÓN NUMÉRICO
//=====================================================

function agregarBotonNumero(contenedor, numero, activo, callback) {
  const li = document.createElement("li");

  li.className = "page-item" + (activo ? " active" : "");

  const button = document.createElement("button");

  button.type = "button";

  button.className = "page-link";

  button.textContent = numero;

  if (!activo) {
    button.addEventListener("click", callback);
  }

  li.appendChild(button);

  contenedor.appendChild(li);
}

//=====================================================
// BOTÓN ANTERIOR / SIGUIENTE
//=====================================================

function agregarBotonPagina(contenedor, texto, habilitado, callback) {
  const li = document.createElement("li");

  li.className = "page-item" + (!habilitado ? " disabled" : "");

  const button = document.createElement("button");

  button.type = "button";

  button.className = "page-link";

  if (texto === "Anterior") {
    button.innerHTML = '<i class="bi bi-chevron-left"></i>';

    button.setAttribute("aria-label", "Página anterior");
  } else {
    button.innerHTML = '<i class="bi bi-chevron-right"></i>';

    button.setAttribute("aria-label", "Página siguiente");
  }

  if (habilitado) {
    button.addEventListener("click", callback);
  } else {
    button.disabled = true;
  }

  li.appendChild(button);

  contenedor.appendChild(li);
}

//=====================================================
// TEXTO DE PAGINACIÓN
//=====================================================

function agregarTextoPaginacion(contenedor) {
  const li = document.createElement("li");

  li.className = "page-item disabled";

  const span = document.createElement("span");

  span.className = "page-link";

  span.textContent = "...";

  li.appendChild(span);

  contenedor.appendChild(li);
}

//=====================================================
// LIMPIAR FILTROS
//=====================================================

function limpiarFiltros() {
  const buscar = document.getElementById("buscarSueldo");

  const estado = document.getElementById("filtroEstadoSueldo");

  const tipo = document.getElementById("filtroTipoSueldo");

  if (buscar) {
    buscar.value = "";
  }

  if (estado) {
    estado.value = "";
  }

  if (tipo) {
    tipo.value = "";
  }

  paginaActual = 1;

  cargarSueldos();
}

//=====================================================
// ABRIR NUEVO SUELDO
//=====================================================

function abrirNuevoSueldo() {
  const formulario = document.getElementById("formSueldo");

  if (!formulario) {
    console.error("No existe #formSueldo.");

    return;
  }

  formulario.reset();

  //=================================================
  // ID SUELDO
  //=================================================

  const idSueldo = document.getElementById("idSueldo");

  if (idSueldo) {
    idSueldo.value = "0";
  }

  //=================================================
  // TIPO BASE
  //=================================================

  const tipoBase = document.getElementById("tipoBase");

  if (tipoBase) {
    tipoBase.value = "MENSUAL";
  }

  //=================================================
  // ESTADO
  //=================================================

  const estado = document.getElementById("estadoSueldo");

  if (estado) {
    estado.value = "ACTIVO";
  }

  //=================================================
  // FECHA INICIO
  //=================================================

  const fechaInicio = document.getElementById("fechaInicioSueldo");

  if (fechaInicio) {
    fechaInicio.value = obtenerFechaActual();
  }

  //=================================================
  // FECHA FIN
  //=================================================

  const fechaFin = document.getElementById("fechaFinSueldo");

  if (fechaFin) {
    fechaFin.value = "";
  }

  //=================================================
  // TÍTULO
  //=================================================

  const titulo = document.getElementById("tituloModalSueldo");

  if (titulo) {
    titulo.textContent = "Asignar Sueldo";
  }

  //=================================================
  // BOTÓN
  //=================================================

  const boton = document.getElementById("btnGuardarSueldo");

  if (boton) {
    boton.innerHTML = '<i class="bi bi-check-circle me-1"></i> Guardar Sueldo';
  }

  //=================================================
  // OCULTAR INFORMACIÓN
  //=================================================

  ocultarInformacionEmpleado();

  //=================================================
  // MOSTRAR MODAL
  //=================================================

  if (modalSueldo) {
    modalSueldo.show();
  } else {
    inicializarModalSueldo();

    if (modalSueldo) {
      modalSueldo.show();
    }
  }
}

//=====================================================
// EDITAR SUELDO
//=====================================================

async function editarSueldo(idSueldo) {
  idSueldo = Number(idSueldo);

  if (!Number.isInteger(idSueldo) || idSueldo <= 0) {
    mostrarAlerta(
      "error",
      "ID inválido",
      "El sueldo seleccionado no es válido.",
    );

    return;
  }

  try {
    mostrarCargandoModal(true);

    //=================================================
    // ESPERAR EMPLEADOS
    //=================================================

    if (!empleadosCargados) {
      await cargarEmpleados();
    }

    //=================================================
    // CONSULTAR SUELDO
    //=================================================

    const respuesta = await fetch(
      "ajax/obtener_sueldo.php?id_sueldo=" + encodeURIComponent(idSueldo),
      {
        method: "GET",

        credentials: "same-origin",

        cache: "no-store",

        headers: {
          Accept: "application/json",
        },
      },
    );

    if (!respuesta.ok) {
      throw new Error("El servidor respondió con HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data || data.success !== true) {
      throw new Error(data?.mensaje || "No se pudo obtener el sueldo.");
    }

    const sueldo = data.sueldo;

    if (!sueldo) {
      throw new Error("El servidor no devolvió información del sueldo.");
    }

    //=================================================
    // ID
    //=================================================

    establecerValor("idSueldo", sueldo.id_sueldo);

    //=================================================
    // EMPLEADO
    //=================================================

    establecerValor("idEmpleadoSueldo", sueldo.id_empleado);

    //=================================================
    // SUELDO BASE
    //=================================================

    establecerValor("sueldoBase", sueldo.sueldo_base);

    //=================================================
    // TIPO PAGO
    //=================================================

    establecerValor("tipoBase", sueldo.tipo_pago);

    //=================================================
    // FECHA INICIO
    //=================================================

    establecerValor("fechaInicioSueldo", sueldo.fecha_inicio || "");

    //=================================================
    // FECHA FIN
    //=================================================

    establecerValor("fechaFinSueldo", sueldo.fecha_fin || "");

    //=================================================
    // ESTADO
    //=================================================

    establecerValor("estadoSueldo", sueldo.estado);

    //=================================================
    // OBSERVACIÓN
    //=================================================

    establecerValor("observacionSueldo", sueldo.observacion || "");

    //=================================================
    // INFORMACIÓN EMPLEADO
    //=================================================

    mostrarInformacionEmpleado();

    //=================================================
    // TÍTULO
    //=================================================

    const titulo = document.getElementById("tituloModalSueldo");

    if (titulo) {
      titulo.textContent = "Editar Sueldo";
    }

    //=================================================
    // BOTÓN
    //=================================================

    const boton = document.getElementById("btnGuardarSueldo");

    if (boton) {
      boton.innerHTML =
        '<i class="bi bi-check-circle me-1"></i> Guardar Cambios';
    }

    //=================================================
    // MOSTRAR MODAL
    //=================================================

    if (!modalSueldo) {
      inicializarModalSueldo();
    }

    if (modalSueldo) {
      modalSueldo.show();
    } else {
      throw new Error("No se pudo inicializar el modal.");
    }
  } catch (error) {
    console.error("Error editarSueldo:", error);

    mostrarAlerta("error", "Error", error.message);
  } finally {
    mostrarCargandoModal(false);
  }
}

//=====================================================
// GUARDAR SUELDO
//=====================================================

async function guardarSueldo(event) {
  event.preventDefault();

  const formulario = document.getElementById("formSueldo");

  const boton = document.getElementById("btnGuardarSueldo");

  if (!formulario) {
    console.error("No existe #formSueldo.");

    return;
  }

  //=================================================
  // DATOS
  //=================================================

  const idSueldo = Number(document.getElementById("idSueldo")?.value || 0);

  const idEmpleado = Number(
    document.getElementById("idEmpleadoSueldo")?.value || 0,
  );

  const sueldoBaseTexto =
    document.getElementById("sueldoBase")?.value.trim() || "";

  const sueldoBase = Number(sueldoBaseTexto);

  const tipoBase = document.getElementById("tipoBase")?.value || "";

  const fechaInicio = document.getElementById("fechaInicioSueldo")?.value || "";

  const fechaFin = document.getElementById("fechaFinSueldo")?.value || "";

  const estado = document.getElementById("estadoSueldo")?.value || "";

  //=================================================
  // VALIDAR EMPLEADO
  //=================================================

  if (!Number.isInteger(idEmpleado) || idEmpleado <= 0) {
    mostrarAlerta("warning", "Empleado requerido", "Seleccione un empleado.");

    return;
  }

  //=================================================
  // VALIDAR SUELDO
  //=================================================

  if (
    sueldoBaseTexto === "" ||
    !Number.isFinite(sueldoBase) ||
    sueldoBase < 0
  ) {
    mostrarAlerta(
      "warning",
      "Sueldo inválido",
      "Ingrese un sueldo base válido.",
    );

    return;
  }

  //=================================================
  // VALIDAR TIPO
  //=================================================

  const tiposPermitidos = ["MENSUAL", "QUINCENAL", "SEMANAL", "DIARIO"];

  if (!tiposPermitidos.includes(tipoBase)) {
    mostrarAlerta(
      "warning",
      "Periodicidad inválida",
      "Seleccione una periodicidad válida.",
    );

    return;
  }

  //=================================================
  // VALIDAR ESTADO
  //=================================================

  const estadosPermitidos = ["ACTIVO", "INACTIVO"];

  if (!estadosPermitidos.includes(estado)) {
    mostrarAlerta("warning", "Estado inválido", "Seleccione un estado válido.");

    return;
  }

  //=================================================
  // VALIDAR FECHAS
  //=================================================

  if (fechaFin && fechaInicio && fechaFin < fechaInicio) {
    mostrarAlerta(
      "warning",
      "Fechas inválidas",
      "La fecha de fin no puede ser anterior a la fecha de inicio.",
    );

    return;
  }

  //=================================================
  // CONFIRMACIÓN
  //=================================================

  const tituloConfirmacion =
    idSueldo > 0 ? "¿Guardar cambios?" : "¿Asignar sueldo?";

  const textoConfirmacion =
    idSueldo > 0
      ? "Se actualizará la información salarial."
      : "Se registrará el sueldo del empleado.";

  const confirmacion = await Swal.fire({
    icon: "question",

    title: tituloConfirmacion,

    text: textoConfirmacion,

    showCancelButton: true,

    confirmButtonText: "Sí, continuar",

    cancelButtonText: "Cancelar",

    reverseButtons: true,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  //=================================================
  // FORM DATA
  //=================================================

  const datos = new FormData(formulario);

  datos.set("id_sueldo", String(idSueldo));

  datos.set("id_empleado", String(idEmpleado));

  //=================================================
  // BOTÓN CARGANDO
  //=================================================

  if (boton) {
    boton.disabled = true;

    boton.dataset.textoOriginal = boton.innerHTML;

    boton.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-1"
                role="status"
            ></span>

            Guardando...
        `;
  }

  try {
    //=================================================
    // DETERMINAR OPERACIÓN
    //=================================================

    const url =
      idSueldo > 0 ? "ajax/editar_sueldo.php" : "ajax/guardar_sueldo.php";

    console.log("[Sueldos] Operación:", idSueldo > 0 ? "EDITAR" : "GUARDAR");

    console.log("[Sueldos] Endpoint:", url);

    //=================================================
    // AJAX
    //=================================================

    const respuesta = await fetch(url, {
      method: "POST",

      body: datos,

      credentials: "same-origin",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    //=================================================
    // OBTENER RESPUESTA COMO TEXTO
    //=================================================

    const textoRespuesta = await respuesta.text();

    console.log("[Sueldos] Respuesta AJAX:", textoRespuesta);

    //=================================================
    // CONVERTIR JSON
    //=================================================

    let data;

    try {
      data = JSON.parse(textoRespuesta);
    } catch (error) {
      console.error("[Sueldos] Respuesta no JSON:");

      console.error(textoRespuesta);

      throw new Error("El servidor devolvió una respuesta inválida.");
    }

    //=================================================
    // HTTP
    //=================================================

    if (!respuesta.ok) {
      throw new Error(
        data?.mensaje || "El servidor respondió con HTTP " + respuesta.status,
      );
    }

    //=================================================
    // SUCCESS
    //=================================================

    if (!data || data.success !== true) {
      throw new Error(data?.mensaje || "No se pudo guardar el sueldo.");
    }

    //=================================================
    // CERRAR MODAL
    //=================================================

    if (modalSueldo) {
      modalSueldo.hide();
    }

    //=================================================
    // MENSAJE
    //=================================================

    await Swal.fire({
      icon: "success",

      title: idSueldo > 0 ? "Sueldo actualizado" : "Sueldo asignado",

      text: data.mensaje || "La operación se realizó correctamente.",

      timer: 1800,

      showConfirmButton: false,
    });

    //=================================================
    // RECARGAR TABLA
    //=================================================

    paginaActual = 1;

    await cargarSueldos();
  } catch (error) {
    console.error("Error guardarSueldo:", error);

    mostrarAlerta("error", "Error", error.message);
  } finally {
    if (boton) {
      boton.disabled = false;

      boton.innerHTML =
        boton.dataset.textoOriginal ||
        '<i class="bi bi-check-circle me-1"></i> Guardar Sueldo';
    }
  }
}

//=====================================================
// CAMBIAR ESTADO
//=====================================================

async function cambiarEstadoSueldo(idSueldo, estadoActual) {
  idSueldo = Number(idSueldo);

  if (!Number.isInteger(idSueldo) || idSueldo <= 0) {
    mostrarAlerta(
      "error",
      "ID inválido",
      "El sueldo seleccionado no es válido.",
    );

    return;
  }

  const nuevoEstado = estadoActual === "ACTIVO" ? "INACTIVO" : "ACTIVO";

  //=================================================
  // CONFIRMACIÓN
  //=================================================

  const confirmacion = await Swal.fire({
    icon: "question",

    title:
      nuevoEstado === "ACTIVO" ? "¿Activar sueldo?" : "¿Desactivar sueldo?",

    text:
      nuevoEstado === "ACTIVO"
        ? "El sueldo volverá a estar vigente."
        : "El sueldo dejará de estar vigente.",

    showCancelButton: true,

    confirmButtonText: "Sí, continuar",

    cancelButtonText: "Cancelar",

    reverseButtons: true,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  try {
    const datos = new FormData();

    datos.append("id_sueldo", String(idSueldo));

    datos.append("estado", nuevoEstado);

    const respuesta = await fetch("ajax/cambiar_estado_sueldo.php", {
      method: "POST",

      body: datos,

      credentials: "same-origin",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    if (!respuesta.ok) {
      throw new Error("El servidor respondió con HTTP " + respuesta.status);
    }

    const data = await respuesta.json();

    if (!data || data.success !== true) {
      throw new Error(data?.mensaje || "No se pudo cambiar el estado.");
    }

    await Swal.fire({
      icon: "success",

      title: "Estado actualizado",

      text: data.mensaje || "El estado fue actualizado correctamente.",

      timer: 1500,

      showConfirmButton: false,
    });

    //=================================================
    // RECARGAR TABLA + KPI
    //=================================================

    await cargarSueldos();
  } catch (error) {
    console.error("Error cambiarEstadoSueldo:", error);

    mostrarAlerta("error", "Error", error.message);
  }
}

//=====================================================
// CARGAR EMPLEADOS
//=====================================================

async function cargarEmpleados() {
  const select = document.getElementById("idEmpleadoSueldo");

  console.log("[Sueldos] Iniciando cargarEmpleados()...");

  //=================================================
  // VALIDAR SELECT
  //=================================================

  if (!select) {
    console.error("[Sueldos] No existe #idEmpleadoSueldo en el DOM.");

    return [];
  }

  console.log("[Sueldos] Select encontrado:", select);

  //=================================================
  // ESTADO DE CARGA
  //=================================================

  empleadosCargados = false;

  select.innerHTML = `
    <option value="">
      Cargando empleados...
    </option>
  `;

  select.disabled = true;

  //=================================================
  // SOLICITUD
  //=================================================

  try {
    console.log("[Sueldos] Consultando ajax/listar_empleados_sueldo.php...");

    const respuesta = await fetch("ajax/listar_empleados_sueldo.php", {
      method: "GET",

      credentials: "same-origin",

      cache: "no-store",

      headers: {
        Accept: "application/json",
      },
    });

    console.log("[Sueldos] HTTP:", respuesta.status, respuesta.statusText);

    //=================================================
    // OBTENER TEXTO
    //=================================================

    const texto = await respuesta.text();

    console.log("[Sueldos] Respuesta AJAX:", texto);

    //=================================================
    // CONVERTIR JSON
    //=================================================

    let data;

    try {
      data = JSON.parse(texto);
    } catch (error) {
      console.error("[Sueldos] La respuesta NO es JSON válido.");

      console.error(texto);

      throw new Error(
        "El servidor devolvió una respuesta que no es JSON válido.",
      );
    }

    console.log("[Sueldos] JSON recibido:", data);

    //=================================================
    // HTTP
    //=================================================

    if (!respuesta.ok) {
      throw new Error(
        data?.mensaje || "El servidor respondió con HTTP " + respuesta.status,
      );
    }

    //=================================================
    // SUCCESS
    //=================================================

    if (!data || data.success !== true) {
      throw new Error(data?.mensaje || "No se pudieron cargar los empleados.");
    }

    //=================================================
    // EMPLEADOS
    //=================================================

    const empleados = Array.isArray(data.empleados) ? data.empleados : [];

    console.log("[Sueldos] Empleados recibidos:", empleados);

    //=================================================
    // LIMPIAR SELECT
    //=================================================

    select.innerHTML = "";

    //=================================================
    // OPCIÓN DEFAULT
    //=================================================

    const opcionDefault = document.createElement("option");

    opcionDefault.value = "";

    opcionDefault.textContent = "Seleccione un empleado...";

    select.appendChild(opcionDefault);

    //=================================================
    // SIN EMPLEADOS
    //=================================================

    if (empleados.length === 0) {
      const opcionSinEmpleados = document.createElement("option");

      opcionSinEmpleados.value = "";

      opcionSinEmpleados.textContent = "No hay empleados activos disponibles";

      select.appendChild(opcionSinEmpleados);

      empleadosCargados = true;

      select.disabled = false;

      console.warn(
        "[Sueldos] El servidor respondió correctamente, pero no hay empleados activos.",
      );

      return [];
    }

    //=================================================
    // CREAR OPTIONS
    //=================================================

    empleados.forEach(function (empleado) {
      console.log("[Sueldos] Procesando empleado:", empleado);

      const option = document.createElement("option");

      option.value = String(empleado.id_empleado);

      const nombre = [empleado.nombre || "", empleado.apellido || ""]
        .join(" ")
        .trim();

      const dni = String(empleado.dni || "").trim();

      option.textContent = dni ? `${nombre} — ${dni}` : nombre;

      //=================================================
      // DATASET
      //=================================================

      option.dataset.nombre = nombre;

      option.dataset.cargo = empleado.cargo || "Sin cargo";

      option.dataset.imagen = empleado.imagen || "";

      select.appendChild(option);
    });

    //=================================================
    // FINALIZAR
    //=================================================

    empleadosCargados = true;

    select.disabled = false;

    console.log(
      "[Sueldos] Empleados cargados correctamente:",
      empleados.length,
    );

    return empleados;
  } catch (error) {
    console.error("[Sueldos] Error cargarEmpleados:", error);

    select.innerHTML = `
      <option value="">
        Error al cargar empleados
      </option>
    `;

    select.disabled = true;

    empleadosCargados = false;

    throw error;
  }
}

//=====================================================
// MOSTRAR INFORMACIÓN DEL EMPLEADO
//=====================================================

function mostrarInformacionEmpleado() {
  const select = document.getElementById("idEmpleadoSueldo");

  const info = document.getElementById("infoEmpleadoSueldo");

  const avatar = document.getElementById("avatarEmpleadoSueldo");

  const nombre = document.getElementById("nombreEmpleadoSueldo");

  const cargo = document.getElementById("cargoEmpleadoSueldo");

  if (!select || !info || !avatar || !nombre || !cargo) {
    return;
  }

  const valor = select.value;

  if (!valor) {
    ocultarInformacionEmpleado();

    return;
  }

  const option = select.options[select.selectedIndex];

  if (!option) {
    ocultarInformacionEmpleado();

    return;
  }

  //=================================================
  // NOMBRE
  //=================================================

  nombre.textContent = option.dataset.nombre || option.textContent || "";

  //=================================================
  // CARGO
  //=================================================

  cargo.textContent = option.dataset.cargo || "Sin cargo";

  //=================================================
  // IMAGEN
  //=================================================

  const imagen = option.dataset.imagen || "";

  if (imagen) {
    avatar.innerHTML = "";

    const img = document.createElement("img");

    img.src = imagen;

    img.alt = "Foto del empleado";

    img.className = "sueldo-info-avatar";

    img.onerror = function () {
      mostrarAvatarPlaceholder(avatar);
    };

    avatar.appendChild(img);
  } else {
    mostrarAvatarPlaceholder(avatar);
  }

  //=================================================
  // MOSTRAR
  //=================================================

  info.classList.remove("d-none");
}

//=====================================================
// AVATAR PLACEHOLDER
//=====================================================

function mostrarAvatarPlaceholder(contenedor) {
  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `
        <div
            class="sueldo-info-avatar-placeholder"
        >
            <i class="bi bi-person"></i>
        </div>
    `;
}

//=====================================================
// OCULTAR INFORMACIÓN
//=====================================================

function ocultarInformacionEmpleado() {
  const info = document.getElementById("infoEmpleadoSueldo");

  if (info) {
    info.classList.add("d-none");
  }
}

//=====================================================
// ESTABLECER VALOR
//=====================================================

function establecerValor(id, valor) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    console.warn(`No existe el elemento #${id}`);

    return;
  }

  elemento.value = valor ?? "";
}

//=====================================================
// FECHA ACTUAL
//=====================================================

function obtenerFechaActual() {
  const fecha = new Date();

  const year = fecha.getFullYear();

  const month = String(fecha.getMonth() + 1).padStart(2, "0");

  const day = String(fecha.getDate()).padStart(2, "0");

  return year + "-" + month + "-" + day;
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(numero) {
  const valor = Number(numero || 0);

  return valor.toLocaleString("es-PE");
}

//=====================================================
// FORMATEAR MONEDA
//=====================================================

function formatearMoneda(numero) {
  const valor = Number(numero || 0);

  return valor.toLocaleString("es-PE", {
    minimumFractionDigits: 2,

    maximumFractionDigits: 2,
  });
}

//=====================================================
// MOSTRAR ALERTA
//=====================================================

function mostrarAlerta(icono, titulo, texto) {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: icono,

      title: titulo,

      text: texto,
    });

    return;
  }

  alert(titulo + "\n\n" + texto);
}

//=====================================================
// ESTADO DE CARGA DEL MODAL
//=====================================================

function mostrarCargandoModal(cargando) {
  const formulario = document.getElementById("formSueldo");

  if (!formulario) {
    return;
  }

  const elementos = formulario.querySelectorAll(
    "input, select, textarea, button",
  );

  elementos.forEach(function (elemento) {
    //=================================================
    // NO BLOQUEAR BOTONES DE CIERRE
    //=================================================

    if (
      elemento.id === "btnCerrarModalSueldo" ||
      elemento.classList.contains("btn-close")
    ) {
      return;
    }

    //=================================================
    // BLOQUEAR
    //=================================================

    if (cargando) {
      // Guardamos el estado ORIGINAL solamente
      // cuando estamos entrando en estado de carga.

      elemento.dataset.estadoAnterior = elemento.disabled ? "1" : "0";

      elemento.disabled = true;
    }

    //=================================================
    // RESTAURAR
    //=================================================
    else {
      const estadoAnterior = elemento.dataset.estadoAnterior;

      if (estadoAnterior !== undefined) {
        elemento.disabled = estadoAnterior === "1";

        delete elemento.dataset.estadoAnterior;
      } else {
        // Si no existía un estado anterior,
        // dejamos el campo habilitado.

        elemento.disabled = false;
      }
    }
  });
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escapeHtml(texto) {
  const div = document.createElement("div");

  div.textContent = texto ?? "";

  return div.innerHTML;
}

//=====================================================
// EXPONER FUNCIONES
//=====================================================

window.editarSueldo = editarSueldo;

window.cambiarEstadoSueldo = cambiarEstadoSueldo;

window.abrirNuevoSueldo = abrirNuevoSueldo;
