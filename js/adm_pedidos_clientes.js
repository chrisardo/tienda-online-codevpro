//=====================================================
// CoDevPro Technology
// Archivo: js/adm_pedidos_clientes.js
// Módulo: Gestión de Pedidos de Clientes
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let paginaActual = 1;

let cargandoRepartidores = false;

let solicitudRepartidores = null;

let solicitudDatosPedido = null;

//=====================================================
// DOM READY
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  cargarMetodosPago();

  cargarPedidos(1);

  inicializarEventosFiltros();

  inicializarModalVerPedido();

  inicializarModalEstadoPedido();
});

//=====================================================
// EVENTOS DE FILTROS
//=====================================================

function inicializarEventosFiltros() {
  const btnBuscarPedidos = document.getElementById("btnBuscarPedidos");

  if (btnBuscarPedidos) {
    btnBuscarPedidos.addEventListener("click", function () {
      cargarPedidos(1);
    });
  }

  const buscarPedido = document.getElementById("buscarPedido");

  if (buscarPedido) {
    buscarPedido.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();

        cargarPedidos(1);
      }
    });
  }

  const filtroEstado = document.getElementById("filtroEstado");

  if (filtroEstado) {
    filtroEstado.addEventListener("change", function () {
      cargarPedidos(1);
    });
  }

  const filtroMetodoPago = document.getElementById("filtroMetodoPago");

  if (filtroMetodoPago) {
    filtroMetodoPago.addEventListener("change", function () {
      cargarPedidos(1);
    });
  }

  const fechaInicio = document.getElementById("fechaInicio");

  if (fechaInicio) {
    fechaInicio.addEventListener("change", function () {
      cargarPedidos(1);
    });
  }

  const fechaFin = document.getElementById("fechaFin");

  if (fechaFin) {
    fechaFin.addEventListener("change", function () {
      cargarPedidos(1);
    });
  }

  const ordenarPor = document.getElementById("ordenarPor");

  if (ordenarPor) {
    ordenarPor.addEventListener("change", function () {
      cargarPedidos(1);
    });
  }

  const btnLimpiarFiltros = document.getElementById("btnLimpiarFiltros");

  if (btnLimpiarFiltros) {
    btnLimpiarFiltros.addEventListener("click", function () {
      const buscar = document.getElementById("buscarPedido");

      const estado = document.getElementById("filtroEstado");

      const metodoPago = document.getElementById("filtroMetodoPago");

      const inicio = document.getElementById("fechaInicio");

      const fin = document.getElementById("fechaFin");

      const ordenar = document.getElementById("ordenarPor");

      if (buscar) {
        buscar.value = "";
      }

      if (estado) {
        estado.value = "";
      }

      if (metodoPago) {
        metodoPago.value = "";
      }

      if (inicio) {
        inicio.value = "";
      }

      if (fin) {
        fin.value = "";
      }

      if (ordenar) {
        ordenar.value = "recientes";
      }

      cargarPedidos(1);
    });
  }
}

//=====================================================
// CARGAR MÉTODOS DE PAGO
//=====================================================

function cargarMetodosPago() {
  const select = document.getElementById("filtroMetodoPago");

  if (!select) {
    return;
  }

  fetch("ajax/adm_obtener_metodos_pago.php", {
    method: "GET",
    cache: "no-store",
    headers: {
      Accept: "application/json",
    },
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.json();
    })
    .then(function (data) {
      if (!data || data.estado !== "ok") {
        console.error("Error obteniendo métodos de pago:", data);

        return;
      }

      select.innerHTML = "";

      const opcionTodos = document.createElement("option");

      opcionTodos.value = "";

      opcionTodos.textContent = "Todos";

      select.appendChild(opcionTodos);

      if (!Array.isArray(data.metodos)) {
        return;
      }

      data.metodos.forEach(function (metodo) {
        const option = document.createElement("option");

        option.value = metodo.id;

        option.textContent = metodo.nombre;

        select.appendChild(option);
      });
    })
    .catch(function (error) {
      console.error("Error cargando métodos de pago:", error);
    });
}

//=====================================================
// CARGAR PEDIDOS
//=====================================================

function cargarPedidos(pagina) {
  if (typeof pagina === "undefined") {
    pagina = paginaActual;
  }

  pagina = parseInt(pagina, 10);

  if (isNaN(pagina) || pagina < 1) {
    pagina = 1;
  }

  paginaActual = pagina;

  const buscar = document.getElementById("buscarPedido");

  const estado = document.getElementById("filtroEstado");

  const metodoPago = document.getElementById("filtroMetodoPago");

  const fechaInicio = document.getElementById("fechaInicio");

  const fechaFin = document.getElementById("fechaFin");

  const ordenar = document.getElementById("ordenarPor");

  const tabla = document.getElementById("tablaPedidos");

  if (!tabla) {
    console.error("No existe el elemento #tablaPedidos.");

    return;
  }

  const valorBuscar = buscar ? buscar.value.trim() : "";

  const valorEstado = estado ? estado.value : "";

  const valorMetodoPago = metodoPago ? metodoPago.value : "";

  const valorFechaInicio = fechaInicio ? fechaInicio.value : "";

  const valorFechaFin = fechaFin ? fechaFin.value : "";

  const valorOrden = ordenar ? ordenar.value : "recientes";

  tabla.innerHTML = `
    <tr>
      <td colspan="7" class="text-center py-5">

        <div
          class="spinner-border text-primary"
          role="status">

          <span class="visually-hidden">
            Cargando...
          </span>

        </div>

        <p class="mt-3 mb-0">
          Cargando pedidos...
        </p>

      </td>
    </tr>
  `;

  const parametros = new URLSearchParams();

  parametros.set("pagina", pagina);

  parametros.set("buscar", valorBuscar);

  parametros.set("estado", valorEstado);

  parametros.set("metodo_pago", valorMetodoPago);

  parametros.set("fecha_inicio", valorFechaInicio);

  parametros.set("fecha_fin", valorFechaFin);

  parametros.set("orden", valorOrden);

  fetch("ajax/adm_listar_pedidos.php?" + parametros.toString(), {
    method: "GET",
    cache: "no-store",
    headers: {
      Accept: "text/html",
    },
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Error HTTP: " + response.status);
      }

      return response.text();
    })
    .then(function (html) {
      tabla.innerHTML = html;

      actualizarContadorPedidos();
    })
    .catch(function (error) {
      console.error("Error cargando pedidos:", error);

      tabla.innerHTML = `
        <tr>
          <td
            colspan="7"
            class="text-center text-danger py-4">

            <i
              class="bi bi-exclamation-triangle-fill me-2">
            </i>

            Error al cargar los pedidos.

          </td>
        </tr>
      `;

      actualizarContadorPedidos();
    });
}

//=====================================================
// CONTADOR DE PEDIDOS
//=====================================================

function actualizarContadorPedidos() {
  const tabla = document.getElementById("tablaPedidos");

  const contador = document.getElementById("totalPedidos");

  if (!tabla || !contador) {
    return;
  }

  const filas = tabla.querySelectorAll("tr");

  let total = 0;

  filas.forEach(function (fila) {
    const texto = fila.innerText.trim();

    if (!texto) {
      return;
    }

    if (
      texto.includes("No se encontraron") ||
      texto.includes("Error al cargar") ||
      texto.includes("Cargando pedidos")
    ) {
      return;
    }

    const celdas = fila.querySelectorAll("td");

    if (celdas.length > 0) {
      total++;
    }
  });

  contador.textContent = total + " Pedidos";
}

//=====================================================
// MODAL VER PEDIDO
//=====================================================

function inicializarModalVerPedido() {
  const modalPedido = document.getElementById("modalVerPedido");

  if (!modalPedido) {
    return;
  }

  modalPedido.addEventListener("show.bs.modal", function (event) {
    const boton = event.relatedTarget;

    if (!boton) {
      return;
    }

    const idPedido = boton.dataset.id || boton.getAttribute("data-id") || "";

    const contenedor = document.getElementById("contenidoDetallePedido");

    if (!contenedor) {
      console.error("No existe #contenidoDetallePedido.");

      return;
    }

    if (!idPedido) {
      contenedor.innerHTML = `
        <div class="alert alert-danger">
          No se pudo identificar el pedido.
        </div>
      `;

      return;
    }

    contenedor.innerHTML = `
      <div class="text-center py-5">

        <div
          class="spinner-border text-primary"
          role="status">

          <span class="visually-hidden">
            Cargando...
          </span>

        </div>

        <p class="mt-3 mb-0">
          Cargando información del pedido...
        </p>

      </div>
    `;

    fetch(
      "ajax/adm_obtener_detalle_pedido.php?id=" + encodeURIComponent(idPedido),
      {
        method: "GET",
        cache: "no-store",
        headers: {
          Accept: "text/html",
        },
      },
    )
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Error HTTP: " + response.status);
        }

        return response.text();
      })
      .then(function (html) {
        contenedor.innerHTML = html;
      })
      .catch(function (error) {
        console.error("Error obteniendo detalle:", error);

        contenedor.innerHTML = `
          <div class="alert alert-danger">

            <i
              class="bi bi-exclamation-triangle-fill me-2">
            </i>

            Error al cargar el pedido.

          </div>
        `;
      });
  });
}

//=====================================================
// MODAL ESTADO PEDIDO
//=====================================================

function inicializarModalEstadoPedido() {
  const modalEstado = document.getElementById("modalEstadoPedido");

  if (!modalEstado) {
    return;
  }

  const selectEstado = document.getElementById("estadoPedido");

  const contenedorRepartidor = document.getElementById("contenedorRepartidor");

  const contenedorSeleccionRepartidor = document.getElementById(
    "contenedorSeleccionRepartidor",
  );

  const selectRepartidor = document.getElementById("repartidorPedido");

  const infoRepartidor = document.getElementById("infoRepartidor");

  const nombreRepartidor = document.getElementById("nombreRepartidor");

  const celularRepartidor = document.getElementById("celularRepartidor");

  const emailRepartidor = document.getElementById("emailRepartidor");

  const rolRepartidor = document.getElementById("rolRepartidor");

  const alertaRepartidorAsignado = document.getElementById(
    "alertaRepartidorAsignado",
  );

  const formEstadoPedido = document.getElementById("formEstadoPedido");

  const btnGuardarEstadoPedido = document.getElementById(
    "btnGuardarEstadoPedido",
  );

  const idPedidoEstado = document.getElementById("idPedidoEstado");

  const observacionPedido = document.getElementById("observacionPedido");

  const alertaPedidoEntregado = document.getElementById(
    "alertaPedidoEntregado",
  );

  if (
    !selectEstado ||
    !contenedorRepartidor ||
    !selectRepartidor ||
    !infoRepartidor ||
    !nombreRepartidor ||
    !celularRepartidor ||
    !formEstadoPedido ||
    !btnGuardarEstadoPedido ||
    !idPedidoEstado ||
    !observacionPedido
  ) {
    console.error(
      "Faltan elementos necesarios del modal de estado del pedido.",
    );

    return;
  }

  //=================================================
  // ESTADO REAL CONFIRMADO POR BD
  //=================================================

  let estadoConfirmadoBD = "";

  //=================================================
  // NORMALIZAR ESTADO
  //=================================================

  function normalizarEstado(estado) {
    return String(estado || "")
      .trim()
      .toUpperCase();
  }

  //=================================================
  // GUARDAR ESTADO CONFIRMADO EN MODAL
  //=================================================

  function guardarEstadoConfirmadoBD(estado) {
    estadoConfirmadoBD = normalizarEstado(estado);

    modalEstado.dataset.estadoConfirmadoBD = estadoConfirmadoBD;
  }

  //=================================================
  // OBTENER ID REPARTIDOR ACTUAL
  //=================================================

  function obtenerIdRepartidorActual() {
    return String(
      selectRepartidor.dataset.empleadoAsignado ||
        selectRepartidor.dataset.empleadoSeleccionado ||
        selectRepartidor.value ||
        "",
    ).trim();
  }

  //=================================================
  // CONTROLAR ESTADOS SEGÚN REPARTIDOR
  //=================================================

  function controlarEstadosSegunRepartidor() {
    const idRepartidor = obtenerIdRepartidorActual();

    const tieneRepartidor = idRepartidor !== "" && idRepartidor !== "0";

    Array.from(selectEstado.options).forEach(function (option) {
      const estado = normalizarEstado(option.value);

      if (tieneRepartidor) {
        option.disabled = estado === "PENDIENTE" || estado === "CONFIRMADO";
      } else {
        option.disabled = false;
      }
    });
  }

  //=================================================
  // ESTADO PERMITE SELECCIONAR REPARTIDOR
  //=================================================

  function estadoPermiteSeleccionarRepartidor(estado) {
    return normalizarEstado(estado) === "PREPARANDO";
  }

  //=================================================
  // ESTADO MUESTRA REPARTIDOR
  //=================================================

  function estadoMuestraRepartidor(estado) {
    estado = normalizarEstado(estado);

    return (
      estado === "PREPARANDO" ||
      estado === "ASIGNADO" ||
      estado === "OBTENIDO" ||
      estado === "ENTREGADO" ||
      estado === "NO_ENTREGADO"
    );
  }

  //=================================================
  // ESTADO FINAL
  //=================================================

  function estadoEsFinal(estado) {
    estado = normalizarEstado(estado);

    return estado === "ENTREGADO" || estado === "CANCELADO";
  }

  //=================================================
  // MOSTRAR INFORMACIÓN REPARTIDOR
  //=================================================

  function mostrarInformacionRepartidorGlobal() {
    const idActual = obtenerIdRepartidorActual();

    if (!idActual) {
      ocultarInformacionRepartidor();

      return;
    }

    const opcion = Array.from(selectRepartidor.options).find(function (option) {
      return String(option.value) === String(idActual);
    });

    if (!opcion) {
      return;
    }

    mostrarInformacionDesdeOpcion(opcion);
  }

  //=================================================
  // MOSTRAR INFORMACIÓN DESDE OPTION
  //=================================================

  function mostrarInformacionDesdeOpcion(opcion) {
    if (!opcion) {
      ocultarInformacionRepartidor();

      return;
    }

    const nombre = opcion.dataset.nombre || opcion.textContent || "Repartidor";

    const celular = opcion.dataset.celular || "";

    const email = opcion.dataset.email || "";

    const rol = opcion.dataset.rol || "REPARTIDOR";

    nombreRepartidor.textContent = nombre;

    celularRepartidor.textContent = celular
      ? "Celular: " + celular
      : "Sin celular registrado";

    if (emailRepartidor) {
      emailRepartidor.textContent = email
        ? "Email: " + email
        : "Sin email registrado";
    }

    if (rolRepartidor) {
      rolRepartidor.textContent = "Rol: " + rol;
    }

    infoRepartidor.classList.remove("d-none");
  }

  //=================================================
  // MOSTRAR INFORMACIÓN DESDE OBJETO BD
  //
  // NUEVO:
  // Permite mostrar al repartidor de un pedido
  // ENTREGADO aunque no esté en la lista actual.
  //=================================================

  function mostrarInformacionDesdeDatos(repartidor) {
    if (!repartidor || typeof repartidor !== "object") {
      return false;
    }

    const nombre =
      repartidor.nombre_completo ||
      repartidor.nombre ||
      repartidor.nombreRepartidor ||
      "";

    const celular =
      repartidor.celular ||
      repartidor.telefono ||
      repartidor.celularRepartidor ||
      "";

    const email = repartidor.email || repartidor.correo || "";

    const rol = repartidor.rol || repartidor.nombre_rol || "REPARTIDOR";

    if (!nombre && !celular && !email) {
      return false;
    }

    nombreRepartidor.textContent = nombre || "Repartidor";

    celularRepartidor.textContent = celular
      ? "Celular: " + celular
      : "Sin celular registrado";

    if (emailRepartidor) {
      emailRepartidor.textContent = email
        ? "Email: " + email
        : "Sin email registrado";
    }

    if (rolRepartidor) {
      rolRepartidor.textContent = "Rol: " + rol;
    }

    infoRepartidor.classList.remove("d-none");

    return true;
  }

  //=================================================
  // AGREGAR REPARTIDOR ACTUAL A SELECT
  //
  // NUEVO:
  // Si el repartidor de la BD no aparece entre
  // los repartidores disponibles, se agrega como
  // opción para poder mostrar sus datos.
  //=================================================

  function agregarRepartidorActualAlSelect(repartidor, idEmpleado) {
    if (!repartidor || !idEmpleado) {
      return;
    }

    const id = String(idEmpleado);

    const existe = Array.from(selectRepartidor.options).some(function (option) {
      return String(option.value) === id;
    });

    if (existe) {
      return;
    }

    const option = document.createElement("option");

    const nombre =
      repartidor.nombre_completo || repartidor.nombre || "Repartidor asignado";

    const celular = repartidor.celular || repartidor.telefono || "";

    const email = repartidor.email || "";

    const rol = repartidor.rol || "REPARTIDOR";

    option.value = id;

    option.textContent = nombre + " (Asignado)";

    option.dataset.nombre = nombre;

    option.dataset.celular = celular;

    option.dataset.email = email;

    option.dataset.rol = rol;

    option.selected = true;

    selectRepartidor.appendChild(option);

    selectRepartidor.value = id;
  }

  //=================================================
  // OCULTAR INFORMACIÓN
  //=================================================

  function ocultarInformacionRepartidor() {
    infoRepartidor.classList.add("d-none");

    nombreRepartidor.textContent = "";

    celularRepartidor.textContent = "";

    if (emailRepartidor) {
      emailRepartidor.textContent = "";
    }

    if (rolRepartidor) {
      rolRepartidor.textContent = "";
    }
  }

  //=================================================
  // CONTROLAR REPARTIDOR
  //=================================================

  function controlarRepartidor() {
    const estado = normalizarEstado(selectEstado.value);

    const muestra = estadoMuestraRepartidor(estado);

    const puedeSeleccionar = estadoPermiteSeleccionarRepartidor(estado);

    //=============================================
    // NO MOSTRAR REPARTIDOR
    //=============================================

    if (!muestra) {
      contenedorRepartidor.classList.add("d-none");

      if (contenedorSeleccionRepartidor) {
        contenedorSeleccionRepartidor.classList.add("d-none");
      }

      ocultarInformacionRepartidor();

      if (alertaRepartidorAsignado) {
        alertaRepartidorAsignado.classList.add("d-none");
      }

      return;
    }

    //=============================================
    // MOSTRAR CONTENEDOR
    //=============================================

    contenedorRepartidor.classList.remove("d-none");

    //=============================================
    // PREPARANDO
    //=============================================

    if (puedeSeleccionar) {
      if (contenedorSeleccionRepartidor) {
        contenedorSeleccionRepartidor.classList.remove("d-none");
      }

      selectRepartidor.disabled = cargandoRepartidores;

      if (selectRepartidor.value) {
        mostrarInformacionRepartidorGlobal();
      }

      if (alertaRepartidorAsignado) {
        alertaRepartidorAsignado.classList.add("d-none");
      }

      return;
    }

    //=============================================
    // OTROS ESTADOS
    //=============================================

    if (contenedorSeleccionRepartidor) {
      contenedorSeleccionRepartidor.classList.add("d-none");
    }

    selectRepartidor.disabled = true;

    mostrarInformacionRepartidorGlobal();

    if (
      alertaRepartidorAsignado &&
      estado === "ASIGNADO" &&
      obtenerIdRepartidorActual()
    ) {
      alertaRepartidorAsignado.classList.remove("d-none");
    } else if (alertaRepartidorAsignado) {
      alertaRepartidorAsignado.classList.add("d-none");
    }
  }

  //=================================================
  // CONTROLAR PEDIDO FINAL
  //=================================================

  function controlarPedidoFinal() {
    const estadoBD = normalizarEstado(estadoConfirmadoBD);

    const esEntregadoBD = estadoBD === "ENTREGADO";

    //=============================================
    // ALERTA
    //=============================================

    if (alertaPedidoEntregado) {
      if (esEntregadoBD) {
        alertaPedidoEntregado.classList.remove("d-none");
      } else {
        alertaPedidoEntregado.classList.add("d-none");
      }
    }

    //=============================================
    // PEDIDO ENTREGADO
    //=============================================

    if (esEntregadoBD) {
      selectEstado.disabled = true;

      observacionPedido.disabled = true;

      selectRepartidor.disabled = true;

      btnGuardarEstadoPedido.disabled = true;

      btnGuardarEstadoPedido.innerHTML = `
        <i class="bi bi-lock-fill me-1"></i>
        Pedido entregado
      `;

      //===========================================
      // IMPORTANTE:
      // AUNQUE ESTÉ ENTREGADO, MOSTRAR REPARTIDOR.
      //===========================================

      controlarRepartidor();

      selectRepartidor.disabled = true;

      return;
    }

    //=============================================
    // PEDIDO NO ENTREGADO
    //=============================================

    selectEstado.disabled = false;

    observacionPedido.disabled = false;

    btnGuardarEstadoPedido.disabled = false;

    btnGuardarEstadoPedido.innerHTML = `
      <i class="bi bi-save-fill me-1"></i>
      Guardar cambios
    `;

    controlarRepartidor();

    controlarEstadosSegunRepartidor();
  }

  //=================================================
  // CARGAR DATOS ACTUALES DEL PEDIDO
  //=================================================

  function cargarDatosActualesPedido(idPedido) {
    if (!idPedido) {
      return Promise.reject(new Error("No se recibió el ID del pedido."));
    }

    if (solicitudDatosPedido) {
      try {
        solicitudDatosPedido.abort();
      } catch (error) {}

      solicitudDatosPedido = null;
    }

    solicitudDatosPedido = new AbortController();

    return fetch(
      "ajax/adm_obtener_datos_estado_pedido.php?id=" +
        encodeURIComponent(idPedido),
      {
        method: "GET",
        cache: "no-store",
        signal: solicitudDatosPedido.signal,
        headers: {
          Accept: "application/json",
        },
      },
    )
      .then(async function (response) {
        const texto = await response.text();

        console.log("Respuesta RAW datos pedido:", texto);

        if (!response.ok) {
          throw new Error("Error HTTP " + response.status + ": " + texto);
        }

        let data;

        try {
          data = JSON.parse(texto);
        } catch (error) {
          console.error("JSON inválido datos pedido:", texto);

          throw new Error("El servidor no devolvió JSON válido.");
        }

        return data;
      })
      .then(function (data) {
        console.log("Datos actuales del pedido:", data);

        if (!data || data.estado !== "ok") {
          throw new Error(
            data && data.mensaje
              ? data.mensaje
              : "No se pudieron obtener los datos del pedido.",
          );
        }

        return data;
      })
      .finally(function () {
        solicitudDatosPedido = null;
      });
  }

  //=================================================
  // CAMBIO DE ESTADO
  //=================================================

  selectEstado.addEventListener("change", function () {
    const estadoSeleccionado = normalizarEstado(this.value);

    console.log("Cambio de estado seleccionado:", estadoSeleccionado);

    console.log("Estado confirmado actualmente en BD:", estadoConfirmadoBD);

    //===========================================
    // PROTECCIÓN ENTREGADO
    //===========================================

    if (normalizarEstado(estadoConfirmadoBD) === "ENTREGADO") {
      controlarPedidoFinal();

      return;
    }

    controlarRepartidor();

    //===========================================
    // CARGAR REPARTIDORES
    //===========================================

    if (
      estadoSeleccionado === "PREPARANDO" &&
      selectRepartidor.options.length <= 1
    ) {
      cargarRepartidores(obtenerIdRepartidorActual());
    }

    //===========================================
    // ASEGURAR BOTÓN
    //===========================================

    btnGuardarEstadoPedido.disabled = false;

    btnGuardarEstadoPedido.innerHTML = `
        <i class="bi bi-save-fill me-1"></i>
        Guardar cambios
      `;

    if (alertaPedidoEntregado) {
      alertaPedidoEntregado.classList.add("d-none");
    }
  });

  //=================================================
  // CAMBIO DE REPARTIDOR
  //=================================================

  selectRepartidor.addEventListener("change", function () {
    const estado = normalizarEstado(selectEstado.value);

    if (estado !== "PREPARANDO") {
      const idAsignado = selectRepartidor.dataset.empleadoAsignado || "";

      this.value = idAsignado;

      mostrarInformacionRepartidorGlobal();

      controlarEstadosSegunRepartidor();

      return;
    }

    this.dataset.empleadoSeleccionado = this.value || "";

    mostrarInformacionRepartidorGlobal();

    controlarEstadosSegunRepartidor();
  });

  //=================================================
  // ABRIR MODAL
  //=================================================

  modalEstado.addEventListener("show.bs.modal", function (event) {
    const boton = event.relatedTarget;

    if (!boton) {
      return;
    }

    const idPedido = boton.dataset.id || boton.getAttribute("data-id") || "";

    console.log("====================================");

    console.log("ABRIENDO MODAL ESTADO PEDIDO");

    console.log("ID pedido:", idPedido);

    console.log("====================================");

    if (!idPedido) {
      alert("No se pudo identificar el pedido.");

      return;
    }

    //=========================================
    // REINICIAR ESTADO BD
    //=========================================

    guardarEstadoConfirmadoBD("");

    //=========================================
    // ID PEDIDO
    //=========================================

    idPedidoEstado.value = idPedido;

    //=========================================
    // ESTADO TEMPORAL
    //=========================================

    selectEstado.value = "PENDIENTE";

    selectEstado.disabled = true;

    //=========================================
    // OBSERVACIÓN
    //=========================================

    observacionPedido.value = "";

    observacionPedido.disabled = true;

    //=========================================
    // REPARTIDOR
    //=========================================

    selectRepartidor.value = "";

    selectRepartidor.disabled = true;

    selectRepartidor.dataset.empleadoAsignado = "";

    selectRepartidor.dataset.empleadoSeleccionado = "";

    selectRepartidor.innerHTML = `
        <option value="">
          Cargando repartidores...
        </option>
      `;

    ocultarInformacionRepartidor();

    if (alertaRepartidorAsignado) {
      alertaRepartidorAsignado.classList.add("d-none");
    }

    if (alertaPedidoEntregado) {
      alertaPedidoEntregado.classList.add("d-none");
    }

    contenedorRepartidor.classList.add("d-none");

    if (contenedorSeleccionRepartidor) {
      contenedorSeleccionRepartidor.classList.add("d-none");
    }

    //=========================================
    // BLOQUEAR DURANTE CONSULTA
    //=========================================

    btnGuardarEstadoPedido.disabled = true;

    btnGuardarEstadoPedido.innerHTML = `
        <span
          class="spinner-border spinner-border-sm me-1"
          role="status"
          aria-hidden="true">
        </span>

        Cargando...
      `;

    //=========================================
    // CONSULTAR BD
    //=========================================

    cargarDatosActualesPedido(idPedido)
      .then(function (data) {
        const pedido = data.pedido || {};

        const estadoActual = normalizarEstado(pedido.estado || "PENDIENTE");

        const idEmpleado = String(pedido.id_empleado || "").trim();

        const observacion = pedido.observacion || "";

        //=====================================
        // INFORMACIÓN DEL REPARTIDOR
        //=====================================

        const repartidorBD = data.repartidor || null;

        console.log("====================================");

        console.log("DATOS ACTUALES DESDE BD");

        console.log("ID pedido:", pedido.id);

        console.log("Estado BD:", estadoActual);

        console.log("ID repartidor BD:", idEmpleado);

        console.log("Observación BD:", observacion);

        console.log("Repartidor completo:", repartidorBD);

        console.log("====================================");

        //=====================================
        // GUARDAR ESTADO REAL
        //=====================================

        guardarEstadoConfirmadoBD(estadoActual);

        //=====================================
        // COLOCAR ESTADO ACTUAL
        //=====================================

        selectEstado.value = estadoActual;

        //=====================================
        // OBSERVACIÓN
        //=====================================

        observacionPedido.value = observacion;

        //=====================================
        // ID REPARTIDOR
        //=====================================

        selectRepartidor.dataset.empleadoAsignado = idEmpleado;

        selectRepartidor.dataset.empleadoSeleccionado = idEmpleado;

        //=====================================
        // CARGAR REPARTIDORES
        //=====================================

        return cargarRepartidores(idEmpleado, repartidorBD).then(function () {
          //=================================
          // ASEGURAR REPARTIDOR DE BD
          //=================================

          if (idEmpleado && repartidorBD) {
            agregarRepartidorActualAlSelect(repartidorBD, idEmpleado);
          }

          //=================================
          // MOSTRAR DATOS DEL REPARTIDOR
          //=================================

          if (idEmpleado && repartidorBD) {
            mostrarInformacionDesdeDatos(repartidorBD);
          } else {
            mostrarInformacionRepartidorGlobal();
          }

          //=================================
          // CONTROLAR REPARTIDOR
          //=================================

          controlarRepartidor();

          //=================================
          // CONTROLAR ESTADOS
          //=================================

          controlarEstadosSegunRepartidor();

          //=================================
          // CONTROLAR PEDIDO
          //=================================

          controlarPedidoFinal();

          //=================================
          // CASO ENTREGADO
          //=================================

          if (estadoActual === "ENTREGADO" && idEmpleado) {
            contenedorRepartidor.classList.remove("d-none");

            if (contenedorSeleccionRepartidor) {
              contenedorSeleccionRepartidor.classList.add("d-none");
            }

            selectRepartidor.disabled = true;

            // Si tenemos los datos del
            // repartidor, se muestran
            // directamente.
            if (repartidorBD) {
              mostrarInformacionDesdeDatos(repartidorBD);
            } else {
              mostrarInformacionRepartidorGlobal();
            }
          }
        });
      })
      .catch(function (error) {
        if (error && error.name === "AbortError") {
          console.log("Consulta anterior de pedido cancelada.");

          return;
        }

        console.error("Error obteniendo datos actuales del pedido:", error);

        guardarEstadoConfirmadoBD("");

        selectEstado.disabled = false;

        observacionPedido.disabled = false;

        btnGuardarEstadoPedido.disabled = false;

        btnGuardarEstadoPedido.innerHTML = `
            <i class="bi bi-save-fill me-1"></i>
            Guardar cambios
          `;

        contenedorRepartidor.classList.add("d-none");

        ocultarInformacionRepartidor();

        alert(
          error.message ||
            "No se pudieron cargar los datos actuales del pedido.",
        );
      });
  });

  //=================================================
  // CERRAR MODAL
  //=================================================

  modalEstado.addEventListener("hidden.bs.modal", function () {
    //=========================================
    // CANCELAR AJAX PEDIDO
    //=========================================

    if (solicitudDatosPedido) {
      try {
        solicitudDatosPedido.abort();
      } catch (error) {}

      solicitudDatosPedido = null;
    }

    //=========================================
    // CANCELAR AJAX REPARTIDORES
    //=========================================

    if (solicitudRepartidores) {
      try {
        solicitudRepartidores.abort();
      } catch (error) {}

      solicitudRepartidores = null;
    }

    //=========================================
    // LIMPIAR ESTADO
    //=========================================

    guardarEstadoConfirmadoBD("");

    idPedidoEstado.value = "";

    selectEstado.value = "PENDIENTE";

    selectEstado.disabled = false;

    selectRepartidor.value = "";

    selectRepartidor.disabled = false;

    selectRepartidor.dataset.empleadoAsignado = "";

    selectRepartidor.dataset.empleadoSeleccionado = "";

    selectRepartidor.innerHTML = `
        <option value="">
          Seleccione un repartidor
        </option>
      `;

    observacionPedido.value = "";

    observacionPedido.disabled = false;

    contenedorRepartidor.classList.add("d-none");

    if (contenedorSeleccionRepartidor) {
      contenedorSeleccionRepartidor.classList.add("d-none");
    }

    ocultarInformacionRepartidor();

    if (alertaRepartidorAsignado) {
      alertaRepartidorAsignado.classList.add("d-none");
    }

    if (alertaPedidoEntregado) {
      alertaPedidoEntregado.classList.add("d-none");
    }

    btnGuardarEstadoPedido.disabled = false;

    btnGuardarEstadoPedido.innerHTML = `
        <i class="bi bi-save-fill me-1"></i>
        Guardar cambios
      `;
  });

  //=================================================
  // SUBMIT
  //=================================================

  formEstadoPedido.addEventListener("submit", function (e) {
    e.preventDefault();

    const estado = normalizarEstado(selectEstado.value);

    const idPedido = String(idPedidoEstado.value || "").trim();

    const estadoBD = normalizarEstado(estadoConfirmadoBD);

    //=========================================
    // PEDIDO YA ENTREGADO
    //=========================================

    if (estadoBD === "ENTREGADO") {
      alert("Este pedido ya fue entregado y no puede modificarse.");

      return;
    }

    let idEmpleado = "";

    //=========================================
    // PREPARANDO
    //=========================================

    if (estado === "PREPARANDO") {
      idEmpleado = String(selectRepartidor.value || "").trim();
    } else {
      idEmpleado = String(
        selectRepartidor.dataset.empleadoAsignado ||
          selectRepartidor.value ||
          "",
      ).trim();
    }

    const observacion = String(observacionPedido.value || "").trim();

    //=========================================
    // VALIDAR PEDIDO
    //=========================================

    if (!idPedido) {
      alert("No se pudo identificar el pedido.");

      return;
    }

    //=========================================
    // VALIDAR ESTADO
    //=========================================

    if (!estado) {
      alert("Debe seleccionar un estado.");

      selectEstado.focus();

      return;
    }

    //=========================================
    // CANCELADO
    //=========================================

    if (estado === "CANCELADO") {
      const confirmar = confirm("¿Está seguro de cancelar este pedido?");

      if (!confirmar) {
        return;
      }
    }

    //=========================================
    // PREPARANDO
    //=========================================

    if (estado === "PREPARANDO" && !idEmpleado) {
      alert("Debe seleccionar un repartidor para preparar el pedido.");

      selectRepartidor.focus();

      return;
    }

    //=========================================
    // FORMDATA
    //=========================================

    const formData = new FormData(formEstadoPedido);

    formData.set("idPedido", idPedido);

    formData.set("estadoPedido", estado);

    formData.set("idEmpleado", idEmpleado);

    formData.set("observacionPedido", observacion);

    console.log("====================================");

    console.log("ACTUALIZANDO PEDIDO");

    console.log("ID pedido:", idPedido);

    console.log("Estado seleccionado:", estado);

    console.log("Estado confirmado BD antes de guardar:", estadoBD);

    console.log("ID repartidor enviado:", idEmpleado);

    console.log("Observación:", observacion);

    console.log("====================================");

    //=========================================
    // BLOQUEAR DURANTE PETICIÓN
    //=========================================

    btnGuardarEstadoPedido.disabled = true;

    selectEstado.disabled = true;

    selectRepartidor.disabled = true;

    observacionPedido.disabled = true;

    btnGuardarEstadoPedido.innerHTML = `
        <span
          class="spinner-border spinner-border-sm me-1"
          role="status"
          aria-hidden="true">
        </span>

        Guardando...
      `;

    //=========================================
    // AJAX ACTUALIZAR
    //=========================================

    fetch("ajax/adm_actualizar_estado_pedido.php", {
      method: "POST",
      body: formData,
      cache: "no-store",
      headers: {
        Accept: "application/json",
      },
    })
      .then(async function (response) {
        const texto = await response.text();

        console.log("Respuesta RAW actualizar pedido:", texto);

        if (!response.ok) {
          throw new Error("Error HTTP " + response.status + ": " + texto);
        }

        let data;

        try {
          data = JSON.parse(texto);
        } catch (error) {
          console.error("Respuesta no válida:", texto);

          throw new Error("El servidor no devolvió una respuesta JSON válida.");
        }

        return data;
      })
      .then(function (data) {
        console.log("Respuesta JSON actualizar pedido:", data);

        if (!data || data.estado !== "ok") {
          throw new Error(
            data && data.mensaje
              ? data.mensaje
              : "No se pudo actualizar el pedido.",
          );
        }

        //=====================================
        // BD CONFIRMÓ ACTUALIZACIÓN
        //=====================================

        guardarEstadoConfirmadoBD(estado);

        //=====================================
        // SI ES ENTREGADO
        //=====================================

        if (estado === "ENTREGADO") {
          selectEstado.disabled = true;

          observacionPedido.disabled = true;

          selectRepartidor.disabled = true;

          btnGuardarEstadoPedido.disabled = true;

          if (alertaPedidoEntregado) {
            alertaPedidoEntregado.classList.remove("d-none");
          }

          btnGuardarEstadoPedido.innerHTML = `
              <i class="bi bi-lock-fill me-1"></i>
              Pedido entregado
            `;
        }

        alert(data.mensaje || "Pedido actualizado correctamente.");

        //=====================================
        // CERRAR MODAL
        //=====================================

        const instanciaModal = bootstrap.Modal.getInstance(modalEstado);

        if (instanciaModal) {
          instanciaModal.hide();
        }

        //=====================================
        // RECARGAR TABLA
        //=====================================

        cargarPedidos(paginaActual);
      })
      .catch(function (error) {
        console.error("Error actualizando pedido:", error);

        alert(error.message || "Ocurrió un error al actualizar el pedido.");

        //=====================================
        // SI NO FUE ENTREGADO
        //=====================================

        if (normalizarEstado(estadoConfirmadoBD) !== "ENTREGADO") {
          selectEstado.disabled = false;

          observacionPedido.disabled = false;

          btnGuardarEstadoPedido.disabled = false;

          controlarRepartidor();

          controlarEstadosSegunRepartidor();

          btnGuardarEstadoPedido.innerHTML = `
              <i class="bi bi-save-fill me-1"></i>
              Guardar cambios
            `;
        }
      });
  });
}

//=====================================================
// CARGAR REPARTIDORES
//=====================================================

function cargarRepartidores(idEmpleadoSeleccionado, repartidorActualBD) {
  if (typeof idEmpleadoSeleccionado === "undefined") {
    idEmpleadoSeleccionado = "";
  }

  if (typeof repartidorActualBD === "undefined") {
    repartidorActualBD = null;
  }

  idEmpleadoSeleccionado = String(idEmpleadoSeleccionado || "").trim();

  const select = document.getElementById("repartidorPedido");

  if (!select) {
    return Promise.reject(new Error("No existe #repartidorPedido."));
  }

  //=================================================
  // CANCELAR PETICIÓN ANTERIOR
  //=================================================

  if (solicitudRepartidores) {
    try {
      solicitudRepartidores.abort();
    } catch (error) {}

    solicitudRepartidores = null;
  }

  solicitudRepartidores = new AbortController();

  cargandoRepartidores = true;

  select.disabled = true;

  select.innerHTML = `
    <option value="">
      Cargando repartidores...
    </option>
  `;

  return fetch("ajax/adm_obtener_repartidores.php", {
    method: "GET",
    cache: "no-store",
    signal: solicitudRepartidores.signal,
    headers: {
      Accept: "application/json",
    },
  })
    .then(async function (response) {
      const texto = await response.text();

      console.log("Respuesta RAW repartidores:", texto);

      if (!response.ok) {
        throw new Error("Error HTTP " + response.status + ": " + texto);
      }

      let data;

      try {
        data = JSON.parse(texto);
      } catch (error) {
        throw new Error(
          "adm_obtener_repartidores.php no devolvió JSON válido.",
        );
      }

      return data;
    })
    .then(function (data) {
      console.log("Respuesta JSON repartidores:", data);

      if (!data || data.estado !== "ok") {
        throw new Error(
          data && data.mensaje
            ? data.mensaje
            : "No se pudieron cargar los repartidores.",
        );
      }

      if (!Array.isArray(data.repartidores)) {
        throw new Error(
          "La respuesta de repartidores no contiene una lista válida.",
        );
      }

      select.innerHTML = `
        <option value="">
          Seleccione un repartidor
        </option>
      `;

      //=================================================
      // CREAR OPTIONS
      //=================================================

      data.repartidores.forEach(function (repartidor) {
        const option = document.createElement("option");

        const id = repartidor.id || repartidor.id_empleado || "";

        const nombre =
          repartidor.nombre_completo || repartidor.nombre || "Repartidor";

        const celular = repartidor.celular || repartidor.telefono || "";

        const email = repartidor.email || "";

        const rol = repartidor.rol || "REPARTIDOR";

        option.value = String(id);

        option.textContent = nombre;

        option.dataset.nombre = nombre;

        option.dataset.celular = celular;

        option.dataset.email = email;

        option.dataset.rol = rol;

        if (
          idEmpleadoSeleccionado &&
          String(id) === String(idEmpleadoSeleccionado)
        ) {
          option.selected = true;
        }

        select.appendChild(option);
      });

      //=================================================
      // CONSERVAR ID BD
      //=================================================

      select.dataset.empleadoAsignado = idEmpleadoSeleccionado;

      select.dataset.empleadoSeleccionado = idEmpleadoSeleccionado;

      //=================================================
      // SI EL REPARTIDOR ACTUAL NO ESTÁ EN LA LISTA
      //
      // IMPORTANTE PARA ENTREGADOS
      //=================================================

      if (idEmpleadoSeleccionado && repartidorActualBD) {
        const existe = Array.from(select.options).some(function (option) {
          return String(option.value) === String(idEmpleadoSeleccionado);
        });

        if (!existe) {
          const option = document.createElement("option");

          const nombre =
            repartidorActualBD.nombre_completo ||
            repartidorActualBD.nombre ||
            "Repartidor asignado";

          const celular =
            repartidorActualBD.celular || repartidorActualBD.telefono || "";

          const email = repartidorActualBD.email || "";

          const rol = repartidorActualBD.rol || "REPARTIDOR";

          option.value = String(idEmpleadoSeleccionado);

          option.textContent = nombre + " (Asignado)";

          option.dataset.nombre = nombre;

          option.dataset.celular = celular;

          option.dataset.email = email;

          option.dataset.rol = rol;

          option.selected = true;

          select.appendChild(option);
        }
      }

      //=================================================
      // ASEGURAR SELECCIÓN
      //=================================================

      if (idEmpleadoSeleccionado) {
        select.value = idEmpleadoSeleccionado;
      }

      //=================================================
      // MOSTRAR INFORMACIÓN
      //=================================================

      if (select.value) {
        const opcion = select.options[select.selectedIndex];

        if (opcion) {
          const info = document.getElementById("infoRepartidor");

          const nombre = document.getElementById("nombreRepartidor");

          const celular = document.getElementById("celularRepartidor");

          const email = document.getElementById("emailRepartidor");

          const rol = document.getElementById("rolRepartidor");

          if (nombre) {
            nombre.textContent = opcion.dataset.nombre || opcion.textContent;
          }

          if (celular) {
            celular.textContent = opcion.dataset.celular
              ? "Celular: " + opcion.dataset.celular
              : "Sin celular registrado";
          }

          if (email) {
            email.textContent = opcion.dataset.email
              ? "Email: " + opcion.dataset.email
              : "Sin email registrado";
          }

          if (rol) {
            rol.textContent = "Rol: " + (opcion.dataset.rol || "REPARTIDOR");
          }

          if (info) {
            info.classList.remove("d-none");
          }
        }
      }
    })
    .finally(function () {
      cargandoRepartidores = false;

      solicitudRepartidores = null;

      const selectEstado = document.getElementById("estadoPedido");

      const estadoActual = normalizarEstadoGlobal(
        selectEstado ? selectEstado.value : "",
      );

      //=================================================
      // PEDIDO ENTREGADO
      //=================================================

      if (normalizarEstadoGlobal(estadoConfirmadoBDGlobal()) === "ENTREGADO") {
        select.disabled = true;
      } else {
        select.disabled = estadoActual !== "PREPARANDO";
      }
    });
}

//=====================================================
// OBTENER ESTADO CONFIRMADO BD GLOBAL
//=====================================================

function estadoConfirmadoBDGlobal() {
  const modal = document.getElementById("modalEstadoPedido");

  if (!modal) {
    return "";
  }

  return String(modal.dataset.estadoConfirmadoBD || "").trim();
}

//=====================================================
// NORMALIZAR ESTADO GLOBAL
//=====================================================

function normalizarEstadoGlobal(estado) {
  return String(estado || "")
    .trim()
    .toUpperCase();
}

//=====================================================
// PAGINACIÓN AJAX
//=====================================================

document.addEventListener("click", function (e) {
  const enlace = e.target.closest(".pagina-pedido");

  if (!enlace) {
    return;
  }

  e.preventDefault();

  const pagina = parseInt(enlace.dataset.pagina, 10);

  if (isNaN(pagina) || pagina < 1) {
    return;
  }

  cargarPedidos(pagina);
});
