//======================================================
// CoDevPro Technology
// js/notificaciones.js
//======================================================

"use strict";

/*======================================================
=            INICIO DEL SISTEMA
======================================================*/

document.addEventListener("DOMContentLoaded", () => {
  if (!existeSistemaNotificaciones()) {
    return;
  }

  cargarContadorNotificaciones();

  cargarNotificaciones();

  inicializarBotonNotificaciones();

  iniciarActualizacionAutomatica();
  inicializarBotonMarcarTodas();
});

/*======================================================
=            CARGAR CONTADOR
======================================================*/

async function cargarContadorNotificaciones() {
  try {
    const respuesta = await fetch("ajax/obtener_contador_notificaciones.php");

    const texto = await respuesta.text();

    if (texto.trim() === "") {
      actualizarContadorNotificaciones(0);

      return;
    }

    let json;

    try {
      json = JSON.parse(texto);
    } catch (error) {
      console.error("Error al interpretar el contador:", texto);

      actualizarContadorNotificaciones(0);

      return;
    }

    if (json.estado === "error") {
      actualizarContadorNotificaciones(0);

      return;
    }

    actualizarContadorNotificaciones(parseInt(json.cantidad) || 0);
  } catch (error) {
    console.error("Error al cargar el contador:", error);
  }
}

/*======================================================
=            ACTUALIZAR CONTADOR
======================================================*/

function actualizarContadorNotificaciones(cantidad) {
  const contador = document.getElementById("contadorNotificaciones");

  if (!contador) {
    return;
  }

  cantidad = parseInt(cantidad) || 0;

  if (cantidad <= 0) {
    contador.textContent = "0";

    contador.classList.add("d-none");

    return;
  }

  contador.textContent = cantidad;

  contador.classList.remove("d-none");
}

/*======================================================
=            CARGAR NOTIFICACIONES
======================================================*/

async function cargarNotificaciones() {
  const contenedor = document.getElementById("contenedorNotificaciones");

  if (!contenedor) {
    return;
  }

  try {
    const respuesta = await fetch("ajax/obtener_notificaciones_cliente.php");

    const texto = await respuesta.text();

    // Para depuración
    console.log("Respuesta AJAX:");
    console.log(texto);

    if (texto.trim() === "") {
      mostrarSinNotificaciones();
      return;
    }

    let json;

    try {
      json = JSON.parse(texto);
      console.log(json);
      console.log(json.notificaciones);
      console.log(json.contador);
    } catch (error) {
      console.error("JSON inválido:", texto);
      mostrarErrorNotificaciones();
      return;
    }

    if (json.estado !== "ok") {
      console.error(json.mensaje);
      mostrarErrorNotificaciones();
      return;
    }

    actualizarContadorNotificaciones(json.contador);

    if (
      !Array.isArray(json.notificaciones) ||
      json.notificaciones.length === 0
    ) {
      mostrarSinNotificaciones();
      return;
    }

    let html = "";
    console.log("Cantidad:");

    console.log(json.notificaciones.length);
    json.notificaciones.forEach((notificacion) => {
      console.log(notificacion);
      html += construirNotificacion(notificacion);
    });
    console.log(html);
    contenedor.innerHTML = html;
  } catch (error) {
    console.error("Error al cargar las notificaciones:", error);

    mostrarErrorNotificaciones();
  }
}

/*======================================================
=            CONSTRUIR NOTIFICACIÓN
======================================================*/

function construirNotificacion(n) {
  const fondo = parseInt(n.leido) === 0 ? "bg-light" : "";
  const tipo = obtenerNombreTipo(n.tipo);
  const textoBoton = obtenerBotonNotificacion(n.tipo);
  const url = n.url && n.url !== "" ? n.url : "#";

  const puntoRojo =
    parseInt(n.leido) === 0
      ? `<span class="badge bg-danger rounded-pill">
                Nuevo
           </span>`
      : "";

  return `

        <a
            href="${url}"
            class="dropdown-item py-3 border-bottom ${fondo}"
            data-id="${n.id}">

            <div class="d-flex">

                <div class="me-3">

                    <i class="bi ${n.icono}
                    text-${n.color}
                    fs-3"></i>

                </div>

                <div class="flex-grow-1 overflow-hidden">

                    <div
                        class="d-flex
                        justify-content-between
                        align-items-center">

                        <span class="fw-bold">

                            ${n.titulo}

                        </span>
                        <button
                                class="btn btn-sm btn-light eliminarNotificacion"
                                data-id="${n.id}">

                                <i class="bi bi-x-lg text-danger"></i>

                        </button>
                        ${puntoRojo}

                    </div>
                    <small
                        class="text-muted mt-1"
                        style="
                          white-space: normal;
                          word-break: break-word;
                          overflow-wrap: break-word;
                          line-height: 1.4;
                          font-size: 0.875rem;
                      ">

                        ${n.mensaje}

                    </small>
                   <div class="mt-2">

                      <small
                      class="text-primary fw-semibold">

                          ${textoBoton}

                          <i class="bi bi-arrow-right-short"></i>

                      </small>

                  </div>
                    <br>

                    <small
                        class="text-secondary">

                        <i class="bi bi-clock"></i>

                        ${n.fecha}

                    </small>

                </div>

            </div>

        </a>

    `;
}

/*======================================================
=            SIN NOTIFICACIONES
======================================================*/

function mostrarSinNotificaciones() {
  const contenedor = document.getElementById("contenedorNotificaciones");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `

        <div class="text-center p-4">

            <i class="bi bi-bell-slash fs-1 text-muted"></i>

            <p class="mt-3 mb-0 text-muted">

                No tienes notificaciones.

            </p>

        </div>

    `;
}

/*======================================================
=            ERROR AL CARGAR
======================================================*/

function mostrarErrorNotificaciones() {
  const contenedor = document.getElementById("contenedorNotificaciones");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `

        <div class="text-center p-4">

            <i class="bi bi-wifi-off text-danger fs-1"></i>

            <p class="mt-3 mb-0 text-muted">

                No se pudieron cargar las
                notificaciones.

            </p>

        </div>

    `;
}

/*======================================================
=            MARCAR COMO LEÍDA
======================================================*/

document.addEventListener("click", async (e) => {
  const notificacion = e.target.closest(".dropdown-item[data-id]");

  if (!notificacion) {
    return;
  }

  const id = notificacion.dataset.id;

  const datos = new FormData();

  datos.append("id_notificacion", id);

  try {
    await fetch("ajax/marcar_notificacion_leida.php", {
      method: "POST",
      body: datos,
    });
  } catch (error) {
    console.error("Error al marcar la notificación:", error);
  }
});

/*======================================================
=            BOTÓN DE NOTIFICACIONES
======================================================*/

function inicializarBotonNotificaciones() {
  const boton = document.getElementById("btnNotificaciones");

  if (!boton) return;

  boton.addEventListener("click", () => {
    cargarNotificaciones();
  });
}
/*======================================================
=            BOTÓN MARCAR TODAS
======================================================*/

function inicializarBotonMarcarTodas() {
  const boton = document.getElementById("btnMarcarTodasLeidas");

  if (!boton) {
    return;
  }

  boton.addEventListener("click", () => {
    marcarTodasComoLeidas();
  });
}
/*======================================================
=            EXISTE EL SISTEMA
======================================================*/

function existeSistemaNotificaciones() {
  return document.getElementById("contadorNotificaciones") !== null;
}
/*======================================================
=            MARCAR TODAS COMO LEÍDAS
======================================================*/

async function marcarTodasComoLeidas() {
  try {
    const respuesta = await fetch(
      "ajax/marcar_todas_notificaciones_leidas.php",
    );

    const json = await respuesta.json();

    if (json.estado !== "ok") {
      return;
    }

    // Actualizar contador

    cargarContadorNotificaciones();

    // Volver a cargar las notificaciones

    cargarNotificaciones();
  } catch (error) {
    console.error("Error al marcar las notificaciones:", error);
  }
}
/*======================================================
=            ACTUALIZACIÓN AUTOMÁTICA
======================================================*/

let intervaloNotificaciones = null;

function iniciarActualizacionAutomatica() {
  intervaloNotificaciones = setInterval(() => {
    if (document.hidden) {
      return;
    }

    cargarContadorNotificaciones();

    cargarNotificaciones();
  }, 10000);
}

/*======================================================
=            CUANDO EL USUARIO REGRESA A LA PESTAÑA
======================================================*/

document.addEventListener("visibilitychange", () => {
  if (!document.hidden) {
    cargarContadorNotificaciones();

    cargarNotificaciones();
  }
});
document.addEventListener("click", (e) => {
  const boton = e.target.closest(".eliminarNotificacion");

  if (!boton) {
    return;
  }

  e.preventDefault();

  e.stopPropagation();

  const id = boton.dataset.id;

  eliminarNotificacion(id);
});
async function eliminarNotificacion(id) {
  const datos = new FormData();

  datos.append("id_notificacion", id);

  try {
    const respuesta = await fetch(
      "ajax/eliminar_notificacion_cliente.php",

      {
        method: "POST",
        body: datos,
      },
    );

    const json = await respuesta.json();

    if (json.estado !== "ok") {
      return;
    }

    // Actualizar todo

    cargarContadorNotificaciones();

    cargarNotificaciones();
  } catch (error) {
    console.error(error);
  }
}
/*======================================================
=            OBTENER NOMBRE DEL TIPO
======================================================*/

function obtenerNombreTipo(tipo) {
  switch (tipo) {
    case "bienvenida":
      return "BIENVENIDA";

    case "pedido":
      return "PEDIDO";

    case "envio":
      return "ENVÍO";

    case "oferta":
      return "OFERTA FLASH";

    case "promocion":
      return "PROMOCIÓN";

    case "producto":
      return "NUEVO PRODUCTO";

    case "seguridad":
      return "SEGURIDAD";

    case "perfil":
      return "MI PERFIL";

    case "favorito":
      return "FAVORITOS";

    case "carrito":
      return "CARRITO";

    case "pago":
      return "PAGO";

    case "testimonio":
      return "TESTIMONIO";

    case "cuenta":
      return "CUENTA";

    case "sistema":
      return "SISTEMA";

    default:
      return "NOTIFICACIÓN";
  }
}
/*======================================================
=            OBTENER TEXTO DEL BOTÓN
======================================================*/

function obtenerBotonNotificacion(tipo) {
  switch (tipo) {
    case "pedido":
      return "Ver pedido >";

    case "envio":
      return "Ver envío >";

    case "oferta":
      return "Ver producto >";

    case "promocion":
      return "Ver promoción >";

    case "producto":
      return "Ver producto >";

    case "perfil":
      return "Completar perfil >";

    case "seguridad":
      return "Ir a seguridad >";

    case "favorito":
      return "Ver favorito >";

    case "carrito":
      return "Ver carrito >";

    case "pago":
      return "Ver pago >";

    case "testimonio":
      return "Ver testimonio >";

    case "cuenta":
      return "Ver mi cuenta >";

    case "bienvenida":
      return "Ir a mi perfil >";

    default:
      return "Ver más >";
  }
}
