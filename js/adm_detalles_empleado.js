//=====================================================
// CoDevPro Technology
// Archivo: js/adm_detalles_empleado.js
// Módulo: Detalles del Empleado
// Sistema: Inventa
//=====================================================

document.addEventListener("DOMContentLoaded", () => {
  cargarDetallesEmpleado();
  inicializarEditarImagenEmpleado();
});

//=====================================================
// OBTENER ID DEL EMPLEADO
//=====================================================

function obtenerIdEmpleado() {
  const parametros = new URLSearchParams(window.location.search);

  const idEmpleado = parseInt(parametros.get("id_empleado"), 10);

  return Number.isInteger(idEmpleado) && idEmpleado > 0 ? idEmpleado : 0;
}

//=====================================================
// CARGAR DETALLES DEL EMPLEADO
//=====================================================

async function cargarDetallesEmpleado() {
  const contenedor = document.getElementById("contenedorDetalleEmpleado");

  if (!contenedor) {
    console.error("No existe el contenedorDetalleEmpleado.");

    return;
  }

  const idEmpleado = obtenerIdEmpleado();

  if (!idEmpleado) {
    mostrarErrorDetalle("No se recibió un ID de empleado válido.");

    return;
  }

  try {
    const datos = new FormData();

    datos.append("id_empleado", String(idEmpleado));

    const respuesta = await fetch("ajax/obtener_detalles_empleado.php", {
      method: "POST",
      body: datos,
      cache: "no-store",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const texto = await respuesta.text();

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (errorJSON) {
      console.error("Respuesta AJAX no válida:", texto);

      throw new Error("El servidor devolvió una respuesta no válida.");
    }

    if (!resultado.success) {
      mostrarErrorDetalle(
        resultado.message || "No se pudo obtener la información del empleado.",
      );

      return;
    }

    renderizarDetalleEmpleado(resultado);
  } catch (error) {
    console.error("Error al cargar detalles del empleado:", error);

    mostrarErrorDetalle(
      "Ocurrió un error al cargar la información del empleado.",
    );
  }
}

//=====================================================
// RENDERIZAR DETALLE DEL EMPLEADO
//=====================================================

function renderizarDetalleEmpleado(resultado) {
  const contenedor = document.getElementById("contenedorDetalleEmpleado");

  if (!contenedor) {
    return;
  }

  //---------------------------------------------------
  // DATOS
  //---------------------------------------------------

  const empleado = resultado.empleado || {};

  window.empleadoDetalleActual = empleado;

  const estadisticas = resultado.estadisticas || {};

  const estados = resultado.ventas_estados || {};

  const permisos = Array.isArray(resultado.permisos) ? resultado.permisos : [];

  //---------------------------------------------------
  // IDENTIFICAR ROL
  //---------------------------------------------------

  const rolOriginal = empleado.nombre_rol || "";

  const rolNormalizado = normalizarTexto(rolOriginal);

  const tipoCargo = identificarTipoCargo(rolNormalizado);

  //---------------------------------------------------
  // FOTO
  //---------------------------------------------------

  const fotoHTML = crearFotoEmpleado(empleado);

  //---------------------------------------------------
  // ESTADO
  //---------------------------------------------------

  const estadoHTML = crearBadgeEstado(empleado.estado);

  //---------------------------------------------------
  // INFORMACIÓN DEL CARGO
  //---------------------------------------------------

  const informacionCargo = crearInformacionCargo(empleado, tipoCargo);

  //---------------------------------------------------
  // ACTIVIDAD SEGÚN CARGO
  //---------------------------------------------------

  const actividadHTML = crearActividadPorCargo(
    empleado,
    tipoCargo,
    estadisticas,
    estados,
  );

  //---------------------------------------------------
  // RENDER PRINCIPAL
  //---------------------------------------------------

  contenedor.innerHTML = `

    <div class="row g-4">

      <!--=================================================
          PERFIL DEL EMPLEADO
      ==================================================-->

      <div class="col-12 col-xl-4">

        ${crearPerfilEmpleado(empleado, fotoHTML, estadoHTML, tipoCargo)}

      </div>


      <!--=================================================
          INFORMACIÓN PERSONAL
      ==================================================-->

      <div class="col-12 col-xl-8">

        ${crearInformacionPersonal(empleado, informacionCargo)}

      </div>


      <!--=================================================
          UBICACIÓN
      ==================================================-->

      <div class="col-12">

        ${crearInformacionUbicacion(empleado)}

      </div>


      <!--=================================================
          INFORMACIÓN DEL REGISTRO
      ==================================================-->

      <div class="col-12">

        ${crearInformacionRegistro(empleado)}

      </div>


      <!--=================================================
          ACTIVIDAD SEGÚN CARGO
      ==================================================-->

      <div class="col-12">

        ${actividadHTML}

      </div>


      <!--=================================================
          PERMISOS
      ==================================================-->

      <div class="col-12">

        ${crearSeccionPermisos(permisos, empleado)}

      </div>

    </div>

  `;
}

//=====================================================
// IDENTIFICAR TIPO DE CARGO
//=====================================================

function identificarTipoCargo(rol) {
  const texto = normalizarTexto(rol);

  //---------------------------------------------------
  // VENTAS
  //---------------------------------------------------

  const cargosVentas = [
    "vendedor",
    "ventas",
    "asesor de ventas",
    "ejecutivo de ventas",
    "asesor comercial",
    "ejecutivo comercial",
    "comercial",
    "cajero",
    "cajera",
  ];

  if (cargosVentas.some((cargo) => texto.includes(normalizarTexto(cargo)))) {
    return "VENTAS";
  }

  //---------------------------------------------------
  // ALMACÉN
  //---------------------------------------------------

  const cargosAlmacen = [
    "almacen",
    "almacén",
    "encargado de almacen",
    "encargado de almacén",
    "auxiliar de almacen",
    "auxiliar de almacén",
    "inventario",
    "logistica",
    "logística",
  ];

  if (cargosAlmacen.some((cargo) => texto.includes(normalizarTexto(cargo)))) {
    return "ALMACEN";
  }

  //---------------------------------------------------
  // ADMINISTRACIÓN
  //---------------------------------------------------

  const cargosAdministracion = [
    "administrador",
    "administradora",
    "gerente",
    "supervisor",
    "supervisora",
    "jefe",
    "jefa",
    "administracion",
    "administración",
  ];

  if (
    cargosAdministracion.some((cargo) => texto.includes(normalizarTexto(cargo)))
  ) {
    return "ADMINISTRACION";
  }

  //---------------------------------------------------
  // SOPORTE / TÉCNICO
  //---------------------------------------------------

  const cargosSoporte = [
    "soporte",
    "tecnico",
    "técnico",
    "tecnica",
    "técnica",
    "soporte tecnico",
    "soporte técnico",
    "informatico",
    "informático",
  ];

  if (cargosSoporte.some((cargo) => texto.includes(normalizarTexto(cargo)))) {
    return "SOPORTE";
  }

  //---------------------------------------------------
  // RECURSOS HUMANOS
  //---------------------------------------------------

  const cargosRRHH = ["recursos humanos", "rrhh", "recursos humanos"];

  if (cargosRRHH.some((cargo) => texto.includes(normalizarTexto(cargo)))) {
    return "RRHH";
  }

  //---------------------------------------------------
  // CONTABILIDAD
  //---------------------------------------------------

  const cargosContabilidad = [
    "contador",
    "contadora",
    "contabilidad",
    "finanzas",
    "financiero",
    "financiera",
  ];

  if (
    cargosContabilidad.some((cargo) => texto.includes(normalizarTexto(cargo)))
  ) {
    return "CONTABILIDAD";
  }

  //---------------------------------------------------
  // OTRO
  //---------------------------------------------------

  return "OTRO";
}

//=====================================================
// INFORMACIÓN DEL CARGO
//=====================================================

function crearInformacionCargo(empleado, tipoCargo) {
  const rol = empleado.nombre_rol || "Sin rol asignado";

  const configuracion = {
    VENTAS: {
      titulo: "Área comercial",
      descripcion: "Empleado relacionado con la atención y gestión de ventas.",
      icono: "bi-shop",
    },

    ALMACEN: {
      titulo: "Área de almacén",
      descripcion:
        "Empleado relacionado con inventario, productos y operaciones de almacén.",
      icono: "bi-box-seam",
    },

    ADMINISTRACION: {
      titulo: "Área administrativa",
      descripcion: "Empleado con funciones administrativas o de supervisión.",
      icono: "bi-building",
    },

    SOPORTE: {
      titulo: "Área técnica",
      descripcion: "Empleado relacionado con soporte y atención técnica.",
      icono: "bi-tools",
    },

    RRHH: {
      titulo: "Recursos humanos",
      descripcion: "Empleado relacionado con la gestión del personal.",
      icono: "bi-people",
    },

    CONTABILIDAD: {
      titulo: "Área contable",
      descripcion:
        "Empleado relacionado con operaciones contables y financieras.",
      icono: "bi-calculator",
    },

    OTRO: {
      titulo: "Área de trabajo",
      descripcion: "Información correspondiente al cargo asignado.",
      icono: "bi-person-workspace",
    },
  };

  const config = configuracion[tipoCargo] || configuracion.OTRO;

  return `

    <div class="cargo-empleado-box">

      <div class="cargo-empleado-icon">

        <i class="bi ${config.icono}"></i>

      </div>

      <div class="flex-grow-1">

        <div class="cargo-empleado-titulo">

          ${escapeHTML(config.titulo)}

        </div>

        <div class="cargo-empleado-descripcion">

          ${escapeHTML(config.descripcion)}

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// PERFIL DEL EMPLEADO
//=====================================================

function crearPerfilEmpleado(empleado, fotoHTML, estadoHTML, tipoCargo) {
  const nombre =
    empleado.nombre_completo ||
    [empleado.nombre, empleado.apellido].filter(Boolean).join(" ") ||
    "Empleado";

  const rol = empleado.nombre_rol || "Sin rol asignado";

  const etiquetaCargo = obtenerEtiquetaCargo(tipoCargo);

  return `

    <div class="detalle-card h-100">

      <div class="detalle-card-body">

        <div class="empleado-foto-wrapper">

          ${fotoHTML}

        </div>


        <div class="empleado-nombre">

          ${escapeHTML(nombre)}

        </div>


        <div class="empleado-rol">

          ${escapeHTML(rol)}

        </div>


        <div class="empleado-area">

          <i class="bi bi-briefcase me-1"></i>

          ${escapeHTML(etiquetaCargo)}

        </div>


        <div class="text-center mt-3">

          ${estadoHTML}

        </div>


        <hr>


        <div class="empleado-identificador">

          <div class="empleado-identificador-label">

            ID empleado

          </div>

          <div class="empleado-identificador-value">

            #${escapeHTML(String(empleado.id_empleado || ""))}

          </div>

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// ETIQUETA DEL TIPO DE CARGO
//=====================================================

function obtenerEtiquetaCargo(tipoCargo) {
  const etiquetas = {
    VENTAS: "Área comercial",
    ALMACEN: "Área de almacén",
    ADMINISTRACION: "Área administrativa",
    SOPORTE: "Área técnica",
    RRHH: "Recursos humanos",
    CONTABILIDAD: "Área contable",
    OTRO: "Área general",
  };

  return etiquetas[tipoCargo] || "Área general";
}

//=====================================================
// FOTO DEL EMPLEADO
//=====================================================

function crearFotoEmpleado(empleado) {
  if (empleado.imagen) {
    return `

      <img
        src="${escapeHTML(empleado.imagen)}"
        alt="Foto de ${escapeHTML(empleado.nombre_completo || "empleado")}"
        class="empleado-foto"
      >

    `;
  }

  return `

    <div class="empleado-foto-placeholder">

      <i class="bi bi-person-fill"></i>

    </div>

  `;
}

//=====================================================
// BADGE ESTADO
//=====================================================

function crearBadgeEstado(estado) {
  const estadoNormalizado = normalizarTexto(estado);

  if (estadoNormalizado === "activo") {
    return `

      <span class="badge-estado badge-activo">

        <i class="bi bi-check-circle-fill"></i>

        ACTIVO

      </span>

    `;
  }

  return `

    <span class="badge-estado badge-inactivo">

      <i class="bi bi-x-circle-fill"></i>

      INACTIVO

    </span>

  `;
}

//=====================================================
// INFORMACIÓN PERSONAL
//=====================================================

function crearInformacionPersonal(empleado, informacionCargo) {
  return `

    <div class="detalle-card h-100">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-person-vcard"></i>

          Información personal

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="row g-3">


          <div class="col-12 col-md-6">

            ${crearDato("Nombre", empleado.nombre)}

          </div>


          <div class="col-12 col-md-6">

            ${crearDato("Apellido", empleado.apellido)}

          </div>


          <div class="col-12 col-md-6">

            ${crearDato("DNI", empleado.dni)}

          </div>


          <div class="col-12 col-md-6">

            ${crearDato("Celular", empleado.celular)}

          </div>


          <div class="col-12 col-md-6">

            ${crearDato("Correo electrónico", empleado.email)}

          </div>


          <div class="col-12 col-md-6">

            ${crearDato("Cargo / Rol", empleado.nombre_rol)}

          </div>


          <div class="col-12">

            ${informacionCargo}

          </div>


        </div>

      </div>

    </div>

  `;
}

//=====================================================
// UBICACIÓN
//=====================================================

function crearInformacionUbicacion(empleado) {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-geo-alt"></i>

          Información de ubicación

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="row g-3">


          <div class="col-12 col-md-6 col-lg-3">

            ${crearDato("País", empleado.pais)}

          </div>


          <div class="col-12 col-md-6 col-lg-3">

            ${crearDato("Departamento", empleado.departamento)}

          </div>


          <div class="col-12 col-md-6 col-lg-3">

            ${crearDato("Provincia", empleado.provincia)}

          </div>


          <div class="col-12 col-md-6 col-lg-3">

            ${crearDato("Distrito", empleado.distrito)}

          </div>


          <div class="col-12">

            ${crearDato("Dirección", empleado.direccion)}

          </div>


        </div>

      </div>

    </div>

  `;
}

//=====================================================
// INFORMACIÓN DEL REGISTRO
//=====================================================

function crearInformacionRegistro(empleado) {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-calendar-event"></i>

          Información laboral y registro

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="row g-3">


          <div class="col-12 col-md-6">

            ${crearDato(
              "Fecha de registro",
              formatearFechaHoraCompleta(empleado.fecha_registro),
            )}

          </div>


          <div class="col-12 col-md-6">

            ${crearDato(
              "Última actualización",
              formatearFechaHoraCompleta(empleado.fecha_actualizado),
            )}

          </div>


          <div class="col-12 col-md-6">

            ${crearDato("Estado", empleado.estado)}

          </div>


          <div class="col-12 col-md-6">

            ${crearDato("ID del rol", empleado.id_rol)}

          </div>


        </div>

      </div>

    </div>

  `;
}

//=====================================================
// ACTIVIDAD SEGÚN CARGO
//=====================================================

function crearActividadPorCargo(empleado, tipoCargo, estadisticas, estados) {
  switch (tipoCargo) {
    //-------------------------------------------------
    // VENDEDOR
    //-------------------------------------------------

    case "VENTAS":
      return crearActividadVentas(estadisticas, estados);

    //-------------------------------------------------
    // ALMACÉN
    //-------------------------------------------------

    case "ALMACEN":
      return crearActividadAlmacen();

    //-------------------------------------------------
    // ADMINISTRACIÓN
    //-------------------------------------------------

    case "ADMINISTRACION":
      return crearActividadAdministrativa();

    //-------------------------------------------------
    // SOPORTE
    //-------------------------------------------------

    case "SOPORTE":
      return crearActividadSoporte();

    //-------------------------------------------------
    // RRHH
    //-------------------------------------------------

    case "RRHH":
      return crearActividadRRHH();

    //-------------------------------------------------
    // CONTABILIDAD
    //-------------------------------------------------

    case "CONTABILIDAD":
      return crearActividadContabilidad();

    //-------------------------------------------------
    // OTROS
    //-------------------------------------------------

    default:
      return crearActividadGeneral(empleado);
  }
}

//=====================================================
// ACTIVIDAD DE VENTAS
//=====================================================

function crearActividadVentas(estadisticas, estados) {
  const totalVentas = Number(estadisticas.total_ventas) || 0;

  const totalVendido = Number(estadisticas.total_vendido) || 0;

  const ultimaVenta = estadisticas.ultima_venta
    ? formatearFechaHora(estadisticas.ultima_venta)
    : "Sin ventas registradas";

  const entregados = Number(estados.ENTREGADO) || 0;

  const pendientes = Number(estados.PENDIENTE) || 0;

  const cancelados = Number(estados.CANCELADO) || 0;

  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <div class="d-flex
                    align-items-center
                    justify-content-between
                    gap-3">

          <h5 class="mb-0">

            <i class="bi bi-graph-up-arrow"></i>

            Actividad comercial

          </h5>


          <span class="badge bg-primary">

            Vendedor

          </span>

        </div>

      </div>


      <div class="detalle-card-body">


        <div class="row g-3">


          <!-- TOTAL VENTAS -->

          <div class="col-6 col-xl-3">

            ${crearKPIEmpleado(
              "bi-receipt",
              "Ventas atendidas",
              formatearNumero(totalVentas),
            )}

          </div>


          <!-- TOTAL VENDIDO -->

          <div class="col-6 col-xl-3">

            ${crearKPIEmpleado(
              "bi-cash-stack",
              "Total vendido",
              "S/ " + formatearNumero(totalVendido, 2),
            )}

          </div>


          <!-- ENTREGADOS -->

          <div class="col-6 col-xl-3">

            ${crearKPIEmpleado(
              "bi-check2-circle",
              "Pedidos entregados",
              formatearNumero(entregados),
            )}

          </div>


          <!-- PENDIENTES -->

          <div class="col-6 col-xl-3">

            ${crearKPIEmpleado(
              "bi-clock-history",
              "Pedidos pendientes",
              formatearNumero(pendientes),
            )}

          </div>


        </div>


        <!-- INFORMACIÓN ADICIONAL -->

        <div class="actividad-resumen mt-4">

          <div class="actividad-resumen-item">

            <div class="actividad-resumen-icon">

              <i class="bi bi-clock"></i>

            </div>

            <div>

              <div class="actividad-resumen-label">

                Última venta

              </div>

              <div class="actividad-resumen-value">

                ${escapeHTML(ultimaVenta)}

              </div>

            </div>

          </div>


          <div class="actividad-resumen-item">

            <div class="actividad-resumen-icon">

              <i class="bi bi-x-circle"></i>

            </div>

            <div>

              <div class="actividad-resumen-label">

                Pedidos cancelados

              </div>

              <div class="actividad-resumen-value">

                ${formatearNumero(cancelados)}

              </div>

            </div>

          </div>

        </div>


        <!-- ESTADOS -->

        <div class="mt-4">

          <div class="fw-semibold mb-3">

            <i class="bi bi-truck me-1"></i>

            Pedidos por estado

          </div>


          ${crearEstado("Pendientes", estados.PENDIENTE, "warning")}

          ${crearEstado("Confirmados", estados.CONFIRMADO, "info")}

          ${crearEstado("Preparando", estados.PREPARANDO, "primary")}

          ${crearEstado("Enviados", estados.ENVIADO, "secondary")}

          ${crearEstado("Entregados", estados.ENTREGADO, "success")}

          ${crearEstado("Cancelados", estados.CANCELADO, "danger")}

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// ACTIVIDAD DE ALMACÉN
//=====================================================

function crearActividadAlmacen() {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-box-seam"></i>

          Actividad de almacén

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="actividad-no-disponible">

          <div class="actividad-no-disponible-icon">

            <i class="bi bi-box-seam"></i>

          </div>


          <div>

            <div class="fw-semibold">

              Gestión de inventario

            </div>

            <div class="text-muted small">

              Los indicadores de productos,
              movimientos de inventario,
              entradas y salidas se mostrarán
              aquí cuando estén disponibles
              para este cargo.

            </div>

          </div>

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// ACTIVIDAD ADMINISTRATIVA
//=====================================================

function crearActividadAdministrativa() {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-building"></i>

          Actividad administrativa

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="actividad-no-disponible">

          <div class="actividad-no-disponible-icon">

            <i class="bi bi-clipboard-data"></i>

          </div>


          <div>

            <div class="fw-semibold">

              Gestión administrativa

            </div>

            <div class="text-muted small">

              Este empleado pertenece al área
              administrativa. Los indicadores
              correspondientes a sus operaciones
              se mostrarán aquí.

            </div>

          </div>

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// ACTIVIDAD SOPORTE
//=====================================================

function crearActividadSoporte() {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-tools"></i>

          Actividad técnica

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="actividad-no-disponible">

          <div class="actividad-no-disponible-icon">

            <i class="bi bi-headset"></i>

          </div>


          <div>

            <div class="fw-semibold">

              Soporte y atención técnica

            </div>

            <div class="text-muted small">

              Aquí se mostrarán las actividades
              técnicas realizadas por el empleado.

            </div>

          </div>

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// ACTIVIDAD RRHH
//=====================================================

function crearActividadRRHH() {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-people"></i>

          Actividad de recursos humanos

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="actividad-no-disponible">

          <div class="actividad-no-disponible-icon">

            <i class="bi bi-person-lines-fill"></i>

          </div>


          <div>

            <div class="fw-semibold">

              Gestión del personal

            </div>

            <div class="text-muted small">

              Aquí se mostrarán las operaciones
              relacionadas con la gestión del personal.

            </div>

          </div>

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// ACTIVIDAD CONTABILIDAD
//=====================================================

function crearActividadContabilidad() {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-calculator"></i>

          Actividad contable

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="actividad-no-disponible">

          <div class="actividad-no-disponible-icon">

            <i class="bi bi-cash-coin"></i>

          </div>


          <div>

            <div class="fw-semibold">

              Operaciones contables

            </div>

            <div class="text-muted small">

              Aquí se mostrarán las operaciones
              financieras y contables realizadas
              por el empleado.

            </div>

          </div>

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// ACTIVIDAD GENERAL
//=====================================================

function crearActividadGeneral(empleado) {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <h5>

          <i class="bi bi-activity"></i>

          Actividad del empleado

        </h5>

      </div>


      <div class="detalle-card-body">

        <div class="actividad-no-disponible">

          <div class="actividad-no-disponible-icon">

            <i class="bi bi-person-workspace"></i>

          </div>


          <div>

            <div class="fw-semibold">

              Actividad registrada

            </div>

            <div class="text-muted small">

              El cargo de

              <strong>
                ${escapeHTML(empleado.nombre_rol || "este empleado")}
              </strong>

              no tiene indicadores específicos
              configurados actualmente.

            </div>

          </div>

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// KPI
//=====================================================

function crearKPIEmpleado(icono, etiqueta, valor) {
  return `

    <div class="kpi-empleado">

      <div class="kpi-icon">

        <i class="bi ${icono}"></i>

      </div>


      <div class="kpi-label">

        ${escapeHTML(etiqueta)}

      </div>


      <div class="kpi-value">

        ${escapeHTML(String(valor))}

      </div>

    </div>

  `;
}

//=====================================================
// DATO
//=====================================================

function crearDato(etiqueta, valor) {
  if (valor === null || valor === undefined || valor === "") {
    valor = "No registrado";
  }

  return `

    <div class="dato-empleado">

      <div class="dato-label">

        ${escapeHTML(etiqueta)}

      </div>


      <div class="dato-valor">

        ${escapeHTML(String(valor))}

      </div>

    </div>

  `;
}

//=====================================================
// ESTADO DE PEDIDO
//=====================================================

function crearEstado(nombre, cantidad, clase) {
  const valor = Number(cantidad) || 0;

  return `

    <div class="d-flex
                align-items-center
                justify-content-between
                border-bottom
                py-2">

      <span>

        ${escapeHTML(nombre)}

      </span>


      <span class="badge text-bg-${clase}">

        ${formatearNumero(valor)}

      </span>

    </div>

  `;
}

//=====================================================
// PERMISOS
//=====================================================

function crearSeccionPermisos(permisos, empleado) {
  return `

    <div class="detalle-card">

      <div class="detalle-card-header">

        <div class="d-flex
                    align-items-center
                    justify-content-between
                    gap-3">

          <h5 class="mb-0">

            <i class="bi bi-shield-lock"></i>

            Permisos y accesos

          </h5>


          <span class="badge bg-secondary">

            ${formatearNumero(permisos.length)}

            módulos

          </span>

        </div>

      </div>


      <div class="detalle-card-body p-0">

        ${crearTablaPermisos(permisos)}

      </div>

    </div>

  `;
}

//=====================================================
// TABLA DE PERMISOS
//=====================================================

function crearTablaPermisos(permisos) {
  if (!permisos || permisos.length === 0) {
    return `

      <div class="p-4 text-center text-muted">

        <i class="bi bi-shield-x fs-2 d-block mb-2"></i>

        <div class="fw-semibold">

          Sin permisos configurados

        </div>

        <div class="small mt-1">

          Este rol no tiene módulos con permisos
          configurados actualmente.

        </div>

      </div>

    `;
  }

  let filas = "";

  permisos.forEach((permiso) => {
    filas += `

        <tr>

          <td>

            <div class="d-flex
                        align-items-center
                        gap-2">

              <div class="permiso-modulo-icon">

                <i class="bi ${permiso.icono || "bi-grid"}"></i>

              </div>


              <div>

                <div class="fw-semibold">

                  ${escapeHTML(permiso.nombre || "Sin nombre")}

                </div>


                ${
                  permiso.codigo
                    ? `
                      <div class="text-muted small">

                        ${escapeHTML(permiso.codigo)}

                      </div>
                    `
                    : ""
                }

              </div>

            </div>

          </td>


          <td class="text-center">

            ${iconoPermiso(permiso.ver)}

          </td>


          <td class="text-center">

            ${iconoPermiso(permiso.crear)}

          </td>


          <td class="text-center">

            ${iconoPermiso(permiso.editar)}

          </td>


          <td class="text-center">

            ${iconoPermiso(permiso.eliminar)}

          </td>

        </tr>

      `;
  });

  return `

    <div class="table-responsive">

      <table class="table table-hover
                    align-middle
                    tabla-permisos
                    mb-0">

        <thead>

          <tr>

            <th>
              Módulo
            </th>

            <th class="text-center">
              Ver
            </th>

            <th class="text-center">
              Crear
            </th>

            <th class="text-center">
              Editar
            </th>

            <th class="text-center">
              Eliminar
            </th>

          </tr>

        </thead>


        <tbody>

          ${filas}

        </tbody>

      </table>

    </div>

  `;
}

//=====================================================
// ICONO PERMISO
//=====================================================

function iconoPermiso(valor) {
  return Number(valor) === 1
    ? `

      <span class="permiso-icono permiso-activo"
            title="Permiso habilitado">

        <i class="bi bi-check-circle-fill"></i>

      </span>

    `
    : `

      <span class="permiso-icono permiso-inactivo"
            title="Permiso no habilitado">

        <i class="bi bi-dash-circle"></i>

      </span>

    `;
}

//=====================================================
// ERROR
//=====================================================

function mostrarErrorDetalle(mensaje) {
  const contenedor = document.getElementById("contenedorDetalleEmpleado");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `

    <div class="detalle-card">

      <div class="detalle-error p-4">

        <div class="text-center">


          <i class="bi bi-exclamation-triangle
                    text-danger
                    fs-1"></i>


          <h5 class="mt-3">

            No se pudo cargar el empleado

          </h5>


          <p class="text-muted">

            ${escapeHTML(mensaje)}

          </p>


          <div class="d-flex
                      justify-content-center
                      gap-2
                      flex-wrap">


            <button
              type="button"
              class="btn btn-primary"
              onclick="cargarDetallesEmpleado()"
            >

              <i class="bi bi-arrow-clockwise me-1"></i>

              Reintentar

            </button>


            <a
              href="adm_lista_empleados.php"
              class="btn btn-outline-secondary"
            >

              <i class="bi bi-arrow-left me-1"></i>

              Volver a empleados

            </a>


          </div>

        </div>

      </div>

    </div>

  `;
}

//=====================================================
// FORMATEAR NÚMERO
//=====================================================

function formatearNumero(numero, decimales = 0) {
  const valor = Number(numero) || 0;

  return valor.toLocaleString("es-PE", {
    minimumFractionDigits: decimales,

    maximumFractionDigits: decimales,
  });
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "No registrada";
  }

  const texto = String(fecha).trim();

  const partes = texto.split("-");

  if (partes.length !== 3) {
    return texto;
  }

  return `
    ${partes[2].substring(0, 2)}/
    ${partes[1]}/
    ${partes[0]}
  `.replace(/\s/g, "");
}

//=====================================================
// FORMATEAR FECHA Y HORA
//=====================================================

function formatearFechaHora(fechaHora) {
  if (!fechaHora) {
    return "Sin registro";
  }

  const texto = String(fechaHora).trim();

  const partes = texto.split(/\s+/);

  if (partes.length < 2) {
    return formatearFecha(texto);
  }

  return formatearFecha(partes[0]) + " " + partes[1];
}

//=====================================================
// FORMATEAR FECHA COMPLETA
//=====================================================

function formatearFechaHoraCompleta(fecha) {
  if (!fecha) {
    return "No registrada";
  }

  const texto = String(fecha).trim();

  if (texto.includes(" ")) {
    return formatearFechaHora(texto);
  }

  return formatearFecha(texto);
}

//=====================================================
// NORMALIZAR TEXTO
//=====================================================

function normalizarTexto(texto) {
  if (texto === null || texto === undefined) {
    return "";
  }

  return String(texto)
    .trim()
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "");
}

//=====================================================
// ESCAPAR HTML
//=====================================================

function escapeHTML(texto) {
  if (texto === null || texto === undefined) {
    return "";
  }

  return String(texto)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
//=====================================================
// INICIALIZAR EDICIÓN DE IMAGEN
//=====================================================

function inicializarEditarImagenEmpleado() {
  const modalElemento = document.getElementById(
    "modalEditarImagenEmpleado",
  );

  const formulario = document.getElementById(
    "formEditarImagenEmpleado",
  );

  const inputImagen = document.getElementById(
    "imagenEmpleadoEditar",
  );

  const idEmpleadoInput = document.getElementById(
    "editarImagenIdEmpleado",
  );

  const contenedorPreview = document.getElementById(
    "contenedorVistaPreviaImagenEmpleado",
  );

  if (!modalElemento) {
    console.warn(
      "No existe el modal #modalEditarImagenEmpleado.",
    );

    return;
  }

  if (!formulario) {
    console.warn(
      "No existe el formulario #formEditarImagenEmpleado.",
    );

    return;
  }

  if (!inputImagen) {
    console.warn(
      "No existe el input #imagenEmpleadoEditar.",
    );

    return;
  }

  if (!idEmpleadoInput) {
    console.warn(
      "No existe #editarImagenIdEmpleado.",
    );

    return;
  }

  if (!contenedorPreview) {
    console.warn(
      "No existe #contenedorVistaPreviaImagenEmpleado.",
    );

    return;
  }

  //---------------------------------------------------
  // INSTANCIA BOOTSTRAP
  //---------------------------------------------------

  const modal = bootstrap.Modal.getOrCreateInstance(
    modalElemento
  );

  //---------------------------------------------------
  // BOTÓN ACTUALIZAR IMAGEN
  //---------------------------------------------------

  document.addEventListener("click", (evento) => {
    const boton = evento.target.closest(
      ".btn-editar-imagen-empleado"
    );

    if (!boton) {
      return;
    }

    //-------------------------------------------------
    // OBTENER ID DEL EMPLEADO
    //-------------------------------------------------

    const idEmpleado = parseInt(
      boton.dataset.idEmpleado,
      10
    );

    if (
      !Number.isInteger(idEmpleado) ||
      idEmpleado <= 0
    ) {
      mostrarMensajeError(
        "No se pudo identificar al empleado."
      );

      return;
    }

    //-------------------------------------------------
    // LIMPIAR FORMULARIO
    //-------------------------------------------------

    formulario.reset();

    //-------------------------------------------------
    // COLOCAR ID
    //-------------------------------------------------

    idEmpleadoInput.value = String(idEmpleado);

    //-------------------------------------------------
    // LIMPIAR ERRORES
    //-------------------------------------------------

    limpiarErrorImagenEmpleado();

    //-------------------------------------------------
    // OBTENER EMPLEADO ACTUAL
    //-------------------------------------------------

    const empleado = window.empleadoDetalleActual || {};

    //-------------------------------------------------
    // VERIFICAR QUE SEA EL MISMO EMPLEADO
    //-------------------------------------------------

    const idEmpleadoActual = parseInt(
      empleado.id_empleado,
      10
    );

    //-------------------------------------------------
    // OBTENER IMAGEN
    //-------------------------------------------------

    let imagenEmpleado = "";

    if (
      idEmpleadoActual === idEmpleado &&
      empleado.imagen
    ) {
      imagenEmpleado = String(
        empleado.imagen
      ).trim();
    }

    //-------------------------------------------------
    // MOSTRAR IMAGEN
    //-------------------------------------------------

    if (imagenEmpleado !== "") {
      mostrarPreviewImagenEmpleado(
        imagenEmpleado
      );
    } else {
      mostrarPlaceholderImagenEmpleado();
    }

    //-------------------------------------------------
    // ABRIR MODAL
    //-------------------------------------------------

    modal.show();
  });

  //---------------------------------------------------
  // CAMBIO DE IMAGEN
  //---------------------------------------------------

  inputImagen.addEventListener("change", () => {
    limpiarErrorImagenEmpleado();

    const archivo = inputImagen.files[0];

    if (!archivo) {
      //-------------------------------------------------
      // SI QUITA LA NUEVA IMAGEN, MOSTRAR LA ACTUAL
      //-------------------------------------------------

      const empleado =
        window.empleadoDetalleActual || {};

      const imagenActual = empleado.imagen
        ? String(empleado.imagen).trim()
        : "";

      if (imagenActual !== "") {
        mostrarPreviewImagenEmpleado(
          imagenActual
        );
      } else {
        mostrarPlaceholderImagenEmpleado();
      }

      return;
    }

    //---------------------------------------------------
    // VALIDAR
    //---------------------------------------------------

    const validacion =
      validarImagenEmpleado(archivo);

    if (!validacion.valido) {
      mostrarErrorImagenEmpleado(
        validacion.mensaje
      );

      inputImagen.value = "";

      //-------------------------------------------------
      // RESTAURAR IMAGEN ACTUAL
      //-------------------------------------------------

      const empleado =
        window.empleadoDetalleActual || {};

      const imagenActual = empleado.imagen
        ? String(empleado.imagen).trim()
        : "";

      if (imagenActual !== "") {
        mostrarPreviewImagenEmpleado(
          imagenActual
        );
      } else {
        mostrarPlaceholderImagenEmpleado();
      }

      return;
    }

    //---------------------------------------------------
    // PREVIEW DE LA NUEVA IMAGEN
    //---------------------------------------------------

    const lector = new FileReader();

    lector.onload = function (evento) {
      mostrarPreviewImagenEmpleado(
        evento.target.result
      );
    };

    lector.readAsDataURL(archivo);
  });

  //---------------------------------------------------
  // GUARDAR
  //---------------------------------------------------

  formulario.addEventListener(
    "submit",
    async (evento) => {
      evento.preventDefault();

      const idEmpleado = parseInt(
        idEmpleadoInput.value,
        10
      );

      if (
        !Number.isInteger(idEmpleado) ||
        idEmpleado <= 0
      ) {
        mostrarMensajeError(
          "El ID del empleado no es válido."
        );

        return;
      }

      const archivo = inputImagen.files[0];

      if (!archivo) {
        mostrarErrorImagenEmpleado(
          "Seleccione una imagen para continuar."
        );

        inputImagen.focus();

        return;
      }

      const validacion =
        validarImagenEmpleado(archivo);

      if (!validacion.valido) {
        mostrarErrorImagenEmpleado(
          validacion.mensaje
        );

        return;
      }

      //-------------------------------------------------
      // CONFIRMACIÓN
      //-------------------------------------------------

      const confirmacion = await Swal.fire({
        title: "¿Actualizar imagen?",

        text:
          "La fotografía actual será reemplazada por la nueva imagen.",

        icon: "question",

        showCancelButton: true,

        confirmButtonText: "Sí, actualizar",

        cancelButtonText: "Cancelar",

        reverseButtons: true,

        focusCancel: true,
      });

      if (!confirmacion.isConfirmed) {
        return;
      }

      //-------------------------------------------------
      // ACTUALIZAR
      //-------------------------------------------------

      await actualizarImagenEmpleado(
        formulario,
        modal
      );
    }
  );
}

//=====================================================
// VALIDAR IMAGEN DEL EMPLEADO
//=====================================================

function validarImagenEmpleado(archivo) {
  if (!archivo) {
    return {
      valido: false,

      mensaje: "Seleccione una imagen.",
    };
  }

  //---------------------------------------------------
  // TIPOS PERMITIDOS
  //---------------------------------------------------

  const tiposPermitidos = ["image/jpeg", "image/png", "image/webp"];

  if (!tiposPermitidos.includes(archivo.type)) {
    return {
      valido: false,

      mensaje:
        "Formato no permitido. Seleccione una imagen JPG, JPEG, PNG o WEBP.",
    };
  }

  //---------------------------------------------------
  // TAMAÑO
  //---------------------------------------------------

  const maximo = 2.7 * 1024 * 1024;

  if (archivo.size > maximo) {
    return {
      valido: false,

      mensaje: "La imagen no puede superar los 2.7 MB.",
    };
  }

  return {
    valido: true,

    mensaje: "",
  };
}

//=====================================================
// ACTUALIZAR IMAGEN AJAX
//=====================================================

async function actualizarImagenEmpleado(formulario, modal) {
  const boton = document.getElementById("btnGuardarImagenEmpleado");

  if (!boton) {
    return;
  }

  //---------------------------------------------------
  // ESTADO BOTÓN
  //---------------------------------------------------

  const contenidoOriginal = boton.innerHTML;

  boton.disabled = true;

  boton.innerHTML = `

    <span
      class="spinner-border spinner-border-sm me-2"
      role="status"
      aria-hidden="true">
    </span>

    Guardando...

  `;

  //---------------------------------------------------
  // FORM DATA
  //---------------------------------------------------

  const datos = new FormData(formulario);

  try {
    const respuesta = await fetch("ajax/actualizar_imagen_empleado.php", {
      method: "POST",

      body: datos,

      cache: "no-store",

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    //-------------------------------------------------
    // LEER RESPUESTA
    //-------------------------------------------------

    const texto = await respuesta.text();

    let resultado;

    try {
      resultado = JSON.parse(texto);
    } catch (errorJSON) {
      console.error("Respuesta AJAX no válida:", texto);

      throw new Error("El servidor devolvió una respuesta no válida.");
    }

    //-------------------------------------------------
    // ERROR DEL SERVIDOR
    //-------------------------------------------------

    if (!resultado.success) {
      throw new Error(resultado.message || "No se pudo actualizar la imagen.");
    }

    //-------------------------------------------------
    // CERRAR MODAL
    //-------------------------------------------------

    modal.hide();

    //-------------------------------------------------
    // ACTUALIZAR FOTO EN LA VISTA
    //-------------------------------------------------

    if (resultado.imagen) {
      actualizarFotoEmpleadoEnDetalle(resultado.imagen);
    } else {
      // Si el servidor no devuelve la ruta,
      // recargamos los detalles.

      await cargarDetallesEmpleado();
    }

    //-------------------------------------------------
    // ÉXITO
    //-------------------------------------------------

    await Swal.fire({
      icon: "success",

      title: "Imagen actualizada",

      text:
        resultado.message ||
        "La imagen del empleado se actualizó correctamente.",

      confirmButtonText: "Aceptar",
    });
  } catch (error) {
    console.error("Error al actualizar imagen:", error);

    Swal.fire({
      icon: "error",

      title: "No se pudo actualizar",

      text: error.message || "Ocurrió un error al actualizar la imagen.",

      confirmButtonText: "Aceptar",
    });
  } finally {
    boton.disabled = false;

    boton.innerHTML = contenidoOriginal;
  }
}

//=====================================================
// ACTUALIZAR FOTO EN LA VISTA
//=====================================================

function actualizarFotoEmpleadoEnDetalle(rutaImagen) {
  const contenedor = document.querySelector(".empleado-foto-wrapper");

  if (!contenedor) {
    cargarDetallesEmpleado();

    return;
  }

  //---------------------------------------------------
  // GENERAR IMAGEN
  //---------------------------------------------------

  contenedor.innerHTML = `

    <img
      src="${escapeHTML(rutaImagen)}?t=${Date.now()}"
      alt="Foto del empleado"
      class="empleado-foto"
    >

  `;
}

//=====================================================
// MOSTRAR PREVIEW
//=====================================================

function mostrarPreviewImagenEmpleado(fuente) {
  const contenedor = document.getElementById(
    "contenedorVistaPreviaImagenEmpleado",
  );

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `

    <div class="empleado-imagen-modal-preview">

      <img
        src="${escapeHTML(fuente)}"
        alt="Vista previa de la imagen"
      >

    </div>

  `;
}

//=====================================================
// MOSTRAR PLACEHOLDER
//=====================================================

function mostrarPlaceholderImagenEmpleado() {
  const contenedor = document.getElementById(
    "contenedorVistaPreviaImagenEmpleado",
  );

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `

    <div class="empleado-imagen-modal-placeholder">

      <i class="bi bi-person-fill"></i>

    </div>

  `;
}

//=====================================================
// MOSTRAR ERROR DE IMAGEN
//=====================================================

function mostrarErrorImagenEmpleado(mensaje) {
  const input = document.getElementById("imagenEmpleadoEditar");

  const contenedor = document.getElementById("errorImagenEmpleadoEditar");

  if (input) {
    input.classList.add("is-invalid");
  }

  if (contenedor) {
    contenedor.textContent = mensaje;
  }
}

//=====================================================
// LIMPIAR ERROR DE IMAGEN
//=====================================================

function limpiarErrorImagenEmpleado() {
  const input = document.getElementById("imagenEmpleadoEditar");

  const contenedor = document.getElementById("errorImagenEmpleadoEditar");

  if (input) {
    input.classList.remove("is-invalid");
  }

  if (contenedor) {
    contenedor.textContent = "";
  }
}

//=====================================================
// MENSAJE DE ERROR
//=====================================================

function mostrarMensajeError(mensaje) {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: "error",

      title: "Error",

      text: mensaje,

      confirmButtonText: "Aceptar",
    });

    return;
  }

  alert(mensaje);
}
//=====================================================
// ACCIONES DE CONTACTO DEL EMPLEADO
//=====================================================

document.addEventListener("click", (evento) => {
  //---------------------------------------------------
  // WHATSAPP
  //---------------------------------------------------

  const botonWhatsApp = evento.target.closest(".btn-whatsapp-empleado");

  if (botonWhatsApp) {
    enviarWhatsAppEmpleado();

    return;
  }

  //---------------------------------------------------
  // CORREO
  //---------------------------------------------------

  const botonCorreo = evento.target.closest(".btn-correo-empleado");

  if (botonCorreo) {
    enviarCorreoEmpleado();

    return;
  }
});

//=====================================================
// ENVIAR WHATSAPP
//=====================================================

function enviarWhatsAppEmpleado() {
  const empleado = window.empleadoDetalleActual || {};

  const celular = empleado.celular
    ? String(empleado.celular).replace(/\D/g, "")
    : "";

  const nombre =
    empleado.nombre_completo ||
    [empleado.nombre, empleado.apellido].filter(Boolean).join(" ") ||
    "empleado";

  if (!celular) {
    Swal.fire({
      icon: "warning",
      title: "Sin número de celular",
      text: "Este empleado no tiene un número de celular registrado.",
      confirmButtonText: "Entendido",
    });

    return;
  }

  //---------------------------------------------------
  // PERÚ
  //---------------------------------------------------
  //
  // La tabla empleados almacena el celular como INT.
  //
  // Si viene como:
  //
  // 987654321
  //
  // lo convertimos a:
  //
  // 51987654321
  //
  //---------------------------------------------------

  let numeroWhatsApp = celular;

  if (numeroWhatsApp.length === 9) {
    numeroWhatsApp = "51" + numeroWhatsApp;
  }

  const mensaje = `Hola ${nombre}, me comunico contigo respecto a tu información como empleado.`;

  const url =
    "https://wa.me/" + numeroWhatsApp + "?text=" + encodeURIComponent(mensaje);

  window.open(url, "_blank", "noopener,noreferrer");
}

//=====================================================
// ENVIAR CORREO
//=====================================================

function enviarCorreoEmpleado() {
  const empleado = window.empleadoDetalleActual || {};

  const email = empleado.email ? String(empleado.email).trim() : "";

  const nombre =
    empleado.nombre_completo ||
    [empleado.nombre, empleado.apellido].filter(Boolean).join(" ") ||
    "empleado";

  if (!email) {
    Swal.fire({
      icon: "warning",
      title: "Sin correo electrónico",
      text: "Este empleado no tiene un correo electrónico registrado.",
      confirmButtonText: "Entendido",
    });

    return;
  }

  //---------------------------------------------------
  // ASUNTO
  //---------------------------------------------------

  const asunto = "Comunicación - " + nombre;

  //---------------------------------------------------
  // CUERPO
  //---------------------------------------------------

  const cuerpo =
    `Hola ${nombre},%0D%0A%0D%0A` +
    `Me comunico contigo respecto a tu información como empleado.%0D%0A%0D%0A` +
    `Saludos.`;

  //---------------------------------------------------
  // ABRIR CLIENTE DE CORREO
  //---------------------------------------------------

  const url =
    "mailto:" +
    encodeURIComponent(email) +
    "?subject=" +
    encodeURIComponent(asunto) +
    "&body=" +
    cuerpo;

  window.location.href = url;
}
