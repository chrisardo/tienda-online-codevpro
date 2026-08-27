//======================================================
// CoDevPro Technology
// Archivo: js/mis_pedidos.js
// Módulo: Mis Pedidos
// Sistema: Inventa
//======================================================

"use strict";

/*======================================================
CONFIGURACIÓN
======================================================*/

const URL_PEDIDOS = {
  cargar: "ajax/cargar_mis_pedidos.php",

  dashboard: "ajax/obtener_estado_pedidos.php",

  confirmarEntrega: "ajax/confirmar_entrega_cliente.php",
};

/*======================================================
VARIABLES GLOBALES
======================================================*/

let paginaActual = 1;

/*======================================================
INICIAR
======================================================*/

document.addEventListener("DOMContentLoaded", function () {
  cargarMisPedidos(1);

  cargarDashboardPedidos();

  inicializarEventos();
});
/*======================================================
ABRIR MODAL CONFIRMAR ENTREGA
======================================================*/

function abrirModalConfirmarEntrega(idPedido, numeroPedido) {
  const idInput = document.getElementById("idPedidoConfirmarEntrega");

  const numeroInput = document.getElementById("numeroPedidoConfirmarEntrega");

  const modalElemento = document.getElementById("modalConfirmarEntregaCliente");

  if (!idInput || !numeroInput || !modalElemento) {
    console.error("No se encontró el modal de confirmar entrega.");

    return;
  }

  idInput.value = idPedido;

  numeroInput.textContent = numeroPedido;

  const modal = bootstrap.Modal.getOrCreateInstance(modalElemento);

  modal.show();
}
/*======================================================
EVENTO CONFIRMAR ENTREGA
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnAbrirConfirmarEntrega");

  if (!boton) {
    return;
  }

  const idPedido = parseInt(boton.dataset.idPedido, 10);

  const numeroPedido = boton.dataset.numeroPedido || "";

  if (isNaN(idPedido) || idPedido <= 0) {
    console.error("ID de pedido inválido.");

    return;
  }

  abrirModalConfirmarEntrega(idPedido, numeroPedido);
});
/*======================================================
CONFIRMAR ENTREGA DEL PEDIDO
======================================================*/

function confirmarEntregaCliente() {
  const idInput = document.getElementById("idPedidoConfirmarEntrega");

  const botonConfirmar = document.getElementById("btnConfirmarEntregaCliente");

  if (!idInput || !botonConfirmar) {
    console.error("No se encontró el formulario de confirmación.");

    return;
  }

  const idPedido = parseInt(idInput.value, 10);

  if (isNaN(idPedido) || idPedido <= 0) {
    Swal.fire({
      icon: "error",

      title: "Pedido inválido",

      text: "No fue posible identificar el pedido.",

      confirmButtonText: "Aceptar",
    });

    return;
  }

  /*====================================================
  DESHABILITAR BOTÓN
  ====================================================*/

  botonConfirmar.disabled = true;

  botonConfirmar.innerHTML = `

    <span
      class="spinner-border spinner-border-sm me-1"
      role="status"
      aria-hidden="true">
    </span>

    Confirmando...

  `;

  /*====================================================
  DATOS
  ====================================================*/

  const datos = new URLSearchParams();

  datos.append("id_pedido", idPedido);

  /*====================================================
  AJAX
  ====================================================*/

  fetch(URL_PEDIDOS.confirmarEntrega, {
    method: "POST",

    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
    },

    body: datos.toString(),

    cache: "no-store",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Error HTTP al confirmar entrega.");
      }

      return response.json();
    })

    .then((data) => {
      /*================================================
      ÉXITO
      ================================================*/

      if (data.success) {
        /*----------------------------------------------
        CERRAR MODAL
        ----------------------------------------------*/

        const modalElemento = document.getElementById(
          "modalConfirmarEntregaCliente",
        );

        if (modalElemento) {
          const modal = bootstrap.Modal.getInstance(modalElemento);

          if (modal) {
            modal.hide();
          }
        }

        /*----------------------------------------------
        MENSAJE DE ÉXITO
        ----------------------------------------------*/

        Swal.fire({
          icon: "success",

          title: "¡Entrega confirmada!",

          text: data.mensaje || "El pedido fue marcado como entregado.",

          confirmButtonText: "Aceptar",

          confirmButtonColor: "#198754",

          allowOutsideClick: false,

          allowEscapeKey: false,
        })

          .then(() => {
            /*==========================================
            ACTUALIZAR LISTA DE PEDIDOS
            ==========================================*/

            cargarMisPedidos(paginaActual);

            /*==========================================
            ACTUALIZAR KPI
            ==========================================*/

            cargarDashboardPedidos();

            /*==========================================
            ACTUALIZAR DETALLE DEL PEDIDO
            ==========================================*/

            /*
             * ver_detalle_pedido_cliente.php
             * utiliza PHP para construir el estado.
             *
             * Por eso debemos volver a cargar la página
             * para que consulte nuevamente el estado
             * actualizado en la base de datos.
             */

            if (
              window.location.pathname
                .toLowerCase()
                .includes("ver_detalle_pedido_cliente.php")
            ) {
              window.location.reload();
            }
          });
      } else {
        /*----------------------------------------------
        ERROR DEVUELTO POR PHP
        ----------------------------------------------*/

        Swal.fire({
          icon: "warning",

          title: "No se pudo confirmar",

          text: data.mensaje || "No fue posible confirmar la entrega.",

          confirmButtonText: "Aceptar",
        });
      }
    })

    .catch((error) => {
      console.error("Confirmar entrega:", error);

      Swal.fire({
        icon: "error",

        title: "Error",

        text: "Ocurrió un error al confirmar la entrega.",

        confirmButtonText: "Aceptar",
      });
    })

    .finally(() => {
      /*==============================================
      RESTAURAR BOTÓN
      ==============================================*/

      botonConfirmar.disabled = false;

      botonConfirmar.innerHTML = `

        <i class="bi bi-check-circle me-1"></i>

        Sí, confirmar entrega

      `;
    });
}
/*======================================================
EVENTO BOTÓN DEL MODAL
======================================================*/

document.addEventListener("DOMContentLoaded", function () {
  const botonConfirmar = document.getElementById("btnConfirmarEntregaCliente");

  if (botonConfirmar) {
    botonConfirmar.addEventListener("click", confirmarEntregaCliente);
  }
});
/*======================================================
CARGAR PEDIDOS
======================================================*/

function cargarMisPedidos(pagina = 1) {
  paginaActual = pagina;

  const contenedor = document.getElementById("contenedorPedidos");

  if (!contenedor) {
    return;
  }

  const buscar = document.getElementById("buscarPedido")?.value.trim() || "";

  const estado = document.getElementById("estadoPedido")?.value || "";

  const fecha = document.getElementById("fechaPedido")?.value || "";

  const metodo = document.getElementById("metodoPago")?.value || "";

  const orden = document.getElementById("ordenPedido")?.value || "recientes";

  const parametros = new URLSearchParams({
    buscar: buscar,

    estado: estado,

    fecha: fecha,

    metodo: metodo,

    orden: orden,

    pagina: paginaActual,
  });

  /*=====================================
  CARGANDO
  =====================================*/

  contenedor.innerHTML = `

    <div class="card-body">

      <div class="text-center py-5">

        <div class="spinner-border text-primary"></div>

        <p class="text-muted mt-3 mb-0">

          Cargando pedidos...

        </p>

      </div>

    </div>

  `;

  fetch(`${URL_PEDIDOS.cargar}?${parametros.toString()}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error("Error al cargar pedidos");
      }

      return response.text();
    })

    .then((html) => {
      contenedor.innerHTML = html;

      actualizarContadorPedidos();

      window.scrollTo({
        top: 0,

        behavior: "smooth",
      });
    })

    .catch((error) => {
      console.error("Pedidos:", error);

      contenedor.innerHTML = `

        <div class="card-body">

          <div class="alert alert-danger text-center">

            <i class="bi bi-exclamation-triangle-fill fs-1"></i>

            <h5 class="mt-3">

              Error al cargar los pedidos

            </h5>

            <p class="mb-4">

              No fue posible obtener la información.

            </p>

            <button
              type="button"
              class="btn btn-danger"
              onclick="cargarMisPedidos(${paginaActual})">

              <i class="bi bi-arrow-clockwise"></i>

              Reintentar

            </button>

          </div>

        </div>

      `;
    });
}

/*======================================================
CARGAR DASHBOARD
======================================================*/

function cargarDashboardPedidos() {
  fetch(URL_PEDIDOS.dashboard, {
    method: "GET",

    cache: "no-store",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Error al obtener dashboard");
      }

      return response.json();
    })

    .then((data) => {
      /*=====================================
      PENDIENTES
      =====================================*/

      const pendientes = document.getElementById("totalPendientes");

      if (pendientes) {
        pendientes.textContent = Number(data.pendientes ?? 0);
      }

      /*=====================================
      CONFIRMADOS
      =====================================*/

      const confirmados = document.getElementById("totalConfirmados");

      if (confirmados) {
        confirmados.textContent = Number(data.confirmados ?? 0);
      }

      /*=====================================
      PREPARANDO
      =====================================*/

      const preparando = document.getElementById("totalPreparando");

      if (preparando) {
        preparando.textContent = Number(data.preparando ?? 0);
      }

      /*=====================================
      EN CAMINO
      ASIGNADO + OBTENIDO + ENVIADO
      =====================================*/

      const enCamino = document.getElementById("totalEnCamino");

      if (enCamino) {
        enCamino.textContent = Number(data.en_camino ?? 0);
      }

      /*=====================================
      ENTREGADOS
      =====================================*/

      const entregados = document.getElementById("totalEntregados");

      if (entregados) {
        entregados.textContent = Number(data.entregados ?? 0);
      }

      /*=====================================
      NO ENTREGADOS
      =====================================*/

      const noEntregados = document.getElementById("totalNoEntregados");

      if (noEntregados) {
        noEntregados.textContent = Number(data.no_entregados ?? 0);
      }

      /*=====================================
      CANCELADOS
      =====================================*/

      const cancelados = document.getElementById("totalCancelados");

      if (cancelados) {
        cancelados.textContent = Number(data.cancelados ?? 0);
      }
    })

    .catch((error) => {
      console.error("Dashboard pedidos:", error);
    });
}

/*======================================================
ACTUALIZAR CONTADOR DE PEDIDOS
======================================================*/

function actualizarContadorPedidos() {
  const contador = document.getElementById("contadorPedidos");

  const total = document.getElementById("totalPedidosGeneral");

  if (!contador || !total) {
    return;
  }

  const cantidad = parseInt(total.dataset.total, 10) || 0;

  contador.textContent = cantidad === 1 ? "1 pedido" : `${cantidad} pedidos`;
}

/*======================================================
APLICAR FILTROS
======================================================*/

function aplicarFiltrosPedidos() {
  paginaActual = 1;

  cargarMisPedidos(1);
}

/*======================================================
INICIALIZAR EVENTOS
======================================================*/

function inicializarEventos() {
  /*=====================================
  BUSCADOR
  =====================================*/

  const buscar = document.getElementById("buscarPedido");

  if (buscar) {
    let temporizador;

    buscar.addEventListener("input", function () {
      clearTimeout(temporizador);

      temporizador = setTimeout(function () {
        aplicarFiltrosPedidos();
      }, 300);
    });
  }

  /*=====================================
  BOTÓN LIMPIAR
  =====================================*/

  const btnLimpiar = document.getElementById("btnLimpiarFiltros");

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", limpiarFiltrosPedidos);
  }

  /*=====================================
  ESTADO
  =====================================*/

  const estado = document.getElementById("estadoPedido");

  if (estado) {
    estado.addEventListener("change", aplicarFiltrosPedidos);
  }

  /*=====================================
  FECHA
  =====================================*/

  const fecha = document.getElementById("fechaPedido");

  if (fecha) {
    fecha.addEventListener("change", aplicarFiltrosPedidos);
  }

  /*=====================================
  MÉTODO DE PAGO
  =====================================*/

  const metodo = document.getElementById("metodoPago");

  if (metodo) {
    metodo.addEventListener("change", aplicarFiltrosPedidos);
  }

  /*=====================================
  ORDEN
  =====================================*/

  const orden = document.getElementById("ordenPedido");

  if (orden) {
    orden.addEventListener("change", aplicarFiltrosPedidos);
  }
}

/*======================================================
PAGINACIÓN AJAX
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".paginaPedido");

  if (!boton) {
    return;
  }

  e.preventDefault();

  const item = boton.closest(".page-item");

  if (
    item &&
    (item.classList.contains("disabled") || item.classList.contains("active"))
  ) {
    return;
  }

  const pagina = parseInt(boton.dataset.pagina, 10);

  if (isNaN(pagina) || pagina < 1) {
    return;
  }

  cargarMisPedidos(pagina);
});

/*======================================================
REFRESCAR PEDIDOS
======================================================*/

function refrescarPedidos() {
  cargarMisPedidos(paginaActual);
}

/*======================================================
LIMPIAR FILTROS
======================================================*/

function limpiarFiltrosPedidos() {
  const buscar = document.getElementById("buscarPedido");

  const estado = document.getElementById("estadoPedido");

  const fecha = document.getElementById("fechaPedido");

  const metodo = document.getElementById("metodoPago");

  const orden = document.getElementById("ordenPedido");

  if (buscar) {
    buscar.value = "";
  }

  if (estado) {
    estado.value = "";
  }

  if (fecha) {
    fecha.value = "";
  }

  if (metodo) {
    metodo.value = "";
  }

  if (orden) {
    orden.value = "recientes";
  }

  paginaActual = 1;

  cargarMisPedidos(1);
}

/*======================================================
REFRESCAR DASHBOARD
======================================================*/

function refrescarDashboardPedidos() {
  cargarDashboardPedidos();
}

/*======================================================
OBTENER PÁGINA ACTUAL
======================================================*/

function obtenerPaginaActual() {
  return paginaActual;
}

/*======================================================
IR A UNA PÁGINA
======================================================*/

function irPaginaPedido(numeroPagina) {
  numeroPagina = parseInt(numeroPagina, 10);

  if (isNaN(numeroPagina) || numeroPagina < 1) {
    return;
  }

  cargarMisPedidos(numeroPagina);
}

/*======================================================
RECARGAR TODO
======================================================*/

function recargarMisPedidos() {
  cargarDashboardPedidos();

  cargarMisPedidos(paginaActual);
}

/*======================================================
EXPORTAR FUNCIONES
======================================================*/

window.cargarMisPedidos = cargarMisPedidos;

window.refrescarPedidos = refrescarPedidos;

window.limpiarFiltrosPedidos = limpiarFiltrosPedidos;

window.recargarMisPedidos = recargarMisPedidos;

window.obtenerPaginaActual = obtenerPaginaActual;

window.irPaginaPedido = irPaginaPedido;

window.refrescarDashboardPedidos = refrescarDashboardPedidos;
