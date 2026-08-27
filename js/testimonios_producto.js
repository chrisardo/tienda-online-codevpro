//Toda esta parte es de js/testimonios_producto.js
//======================================================
// CoDevPro Technology
// js/testimonios_producto.js
//======================================================

"use strict";

/*======================================================
CONFIGURACIÓN
======================================================*/

const URL_TESTIMONIOS = {
  cargar: "ajax/cargar_testimonios_pedido.php",

  guardar: "ajax/guardar_testimonio.php",

  obtener: "ajax/obtener_testimonio.php",
};

/*======================================================
INICIAR
======================================================*/

document.addEventListener("DOMContentLoaded", () => {
  if (typeof ID_TICKET === "undefined") {
    console.error("ID_TICKET no definido");

    return;
  }

  cargarProductosTestimonio();
});

/*======================================================
CARGAR PRODUCTOS DEL PEDIDO
======================================================*/

function cargarProductosTestimonio() {
  const contenedor = document.getElementById("contenedorTestimonios");

  if (!contenedor) return;

  contenedor.innerHTML = `

        <div class="text-center py-5">

            <div class="spinner-border text-primary"></div>

            <p class="mt-3 text-muted">

                Cargando productos...

            </p>

        </div>

    `;

  fetch(URL_TESTIMONIOS.cargar + "?id_ticket=" + ID_TICKET)
    .then((response) => response.text())

    .then((html) => {
      contenedor.innerHTML = html;
    })

    .catch((error) => {
      console.error(error);

      contenedor.innerHTML = `

                <div class="alert alert-danger">

                    Error al cargar los productos.

                </div>

            `;
    });
}

/*======================================================
ABRIR MODAL NUEVO TESTIMONIO
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".escribirTestimonio");

  if (!boton) return;

  abrirModalTestimonio(boton, false);
});

/*======================================================
EDITAR TESTIMONIO
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".editarTestimonio");

  if (!boton) return;

  abrirModalTestimonio(boton, true);
});

/*======================================================
ABRIR MODAL
======================================================*/

function abrirModalTestimonio(boton, editar = false) {
  restaurarAlertaTestimonio();
  const idProducto = boton.dataset.producto;

  document.getElementById("idProductoTestimonio").value = idProducto;

  document.getElementById("idTicketTestimonio").value = ID_TICKET;

  document.getElementById("comentarioTestimonio").value = "";

  limpiarEstrellas();

  /*==========================================
    DATOS DEL PRODUCTO
    ==========================================*/

  const nombre = boton.dataset.nombre || "";

  const imagen = boton.dataset.imagen || "";

  const precio = boton.dataset.precio || "0.00";

  const cantidad = boton.dataset.cantidad || "1";

  document.getElementById("nombreProductoModal").innerHTML = nombre;

  document.getElementById("precioProductoModal").innerHTML = "S/ " + precio;

  document.getElementById("cantidadProductoModal").innerHTML = cantidad;

  if (imagen !== "") {
    document.getElementById("imagenProductoModal").src =
      "data:image/jpeg;base64," + imagen;
  } else {
    document.getElementById("imagenProductoModal").src =
      "assets/img/no-image.png";
  }

  /*==========================================
    SI ES EDICIÓN
    ==========================================*/

  if (editar) {
    obtenerTestimonio(idProducto);
  }

  const modal = new bootstrap.Modal(document.getElementById("modalTestimonio"));

  modal.show();
}
/*======================================================
OBTENER TESTIMONIO EXISTENTE
======================================================*/

function obtenerTestimonio(idProducto) {
  fetch(
    URL_TESTIMONIOS.obtener +
      "?id_ticket=" +
      ID_TICKET +
      "&id_producto=" +
      idProducto,
  )
    .then((response) => response.json())

    .then((data) => {
      if (data.estado !== "ok") {
        return;
      }

      document.getElementById("comentarioTestimonio").value = data.comentario;

      document.getElementById("contadorComentario").innerHTML =
        data.comentario.length + " / 500 caracteres";

      seleccionarEstrellas(parseInt(data.calificacion));
    })

    .catch((error) => {
      console.error(error);
    });
}

/*======================================================
CLICK SOBRE LAS ESTRELLAS
======================================================*/

document.addEventListener("click", function (e) {
  const estrella = e.target.closest(".estrellaCalificacion");

  if (!estrella) {
    return;
  }

  const valor = parseInt(estrella.dataset.valor);

  seleccionarEstrellas(valor);
});

/*======================================================
PINTAR ESTRELLAS
======================================================*/

function seleccionarEstrellas(valor) {
  document.getElementById("calificacionTestimonio").value = valor;

  document.querySelectorAll(".estrellaCalificacion").forEach(function (item) {
    const numero = parseInt(item.dataset.valor);

    item.classList.remove(
      "bi-star",

      "bi-star-fill",

      "text-warning",
    );

    if (numero <= valor) {
      item.classList.add(
        "bi-star-fill",

        "text-warning",
      );
    } else {
      item.classList.add("bi-star");
    }
  });

  actualizarTextoCalificacion(valor);
}

/*======================================================
LIMPIAR ESTRELLAS
======================================================*/

function limpiarEstrellas() {
  seleccionarEstrellas(0);

  document.getElementById("comentarioTestimonio").value = "";

  document.getElementById("contadorComentario").innerHTML =
    "0 / 500 caracteres";
}

/*======================================================
ACTUALIZAR TEXTO SEGÚN CALIFICACIÓN
======================================================*/

function actualizarTextoCalificacion(valor) {
  const texto = document.getElementById("textoCalificacion");

  switch (valor) {
    case 1:
      texto.innerHTML = "⭐ Muy malo";

      break;

    case 2:
      texto.innerHTML = "⭐⭐ Malo";

      break;

    case 3:
      texto.innerHTML = "⭐⭐⭐ Regular";

      break;

    case 4:
      texto.innerHTML = "⭐⭐⭐⭐ Bueno";

      break;

    case 5:
      texto.innerHTML = "⭐⭐⭐⭐⭐ Excelente";

      break;

    default:
      texto.innerHTML = "Selecciona una calificación";
  }
}

/*======================================================
CONTADOR DE CARACTERES
======================================================*/

document.addEventListener("input", function (e) {
  if (e.target.id !== "comentarioTestimonio") {
    return;
  }

  document.getElementById("contadorComentario").innerHTML =
    e.target.value.length + " / 500 caracteres";
});
/*======================================================
GUARDAR TESTIMONIO
======================================================*/

document.addEventListener("click", function (e) {
  if (e.target.id !== "guardarTestimonio") {
    return;
  }

  guardarTestimonio();
});

/*======================================================
VALIDAR Y ENVIAR
======================================================*/

function guardarTestimonio() {
  const idTicket = document.getElementById("idTicketTestimonio").value;

  const idProducto = document.getElementById("idProductoTestimonio").value;

  const calificacion = document.getElementById("calificacionTestimonio").value;

  const comentario = document
    .getElementById("comentarioTestimonio")
    .value.trim();

  /*==========================================
    VALIDACIONES
    ==========================================*/

  if (parseInt(calificacion) <= 0) {
    mostrarAlertaTestimonio("warning", "Debes seleccionar una calificación.");

    return;
  }

  if (comentario.length < 5) {
    mostrarAlertaTestimonio(
      "warning",
      "Escribe un comentario de al menos 5 caracteres.",
    );

    return;
  }

  if (comentario.length > 500) {
    mostrarAlertaTestimonio(
      "warning",
      "El comentario no puede superar los 500 caracteres.",
    );

    return;
  }

  const boton = document.getElementById("guardarTestimonio");

  boton.disabled = true;

  boton.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Guardando...
    `;

  /*==========================================
    ENVIAR AJAX
    ==========================================*/

  const datos = new FormData();

  datos.append("id_ticket", idTicket);

  datos.append("id_producto", idProducto);

  datos.append("calificacion", calificacion);

  datos.append("comentario", comentario);

  fetch(URL_TESTIMONIOS.guardar, {
    method: "POST",

    body: datos,
  })
    .then((response) => response.json())

    .then((data) => {
      boton.disabled = false;

      boton.innerHTML = `

            <i class="bi bi-send-fill"></i>

            Guardar opinión

        `;

      if (data.estado === "ok") {
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modalTestimonio"),
        );

        if (modal) {
          modal.hide();
        }

        /*======================================
            LIMPIAR FORMULARIO
            ======================================*/

        document.getElementById("comentarioTestimonio").value = "";

        document.getElementById("contadorComentario").innerHTML =
          "0 / 500 caracteres";

        limpiarEstrellas();

        /*======================================
            RECARGAR PRODUCTOS
            ======================================*/

        cargarProductosTestimonio();

        /*======================================
            MENSAJE
            ======================================*/

        mostrarAlertaTestimonio("success", data.mensaje);
      } else {
        mostrarAlertaTestimonio("danger", data.mensaje);
      }
    })

    .catch((error) => {
      console.error(error);

      boton.disabled = false;

      boton.innerHTML = `

            <i class="bi bi-send-fill"></i>

            Guardar opinión

        `;

      mostrarAlertaTestimonio(
        "danger",
        "Ocurrió un error al guardar el testimonio.",
      );
    });
}
/*======================================================
MOSTRAR ALERTA EN EL MODAL
======================================================*/

function mostrarAlertaTestimonio(tipo, mensaje) {
  const alerta = document.getElementById("alertaTestimonio");
  const icono = document.getElementById("iconoAlertaTestimonio");
  const texto = document.getElementById("textoAlertaTestimonio");

  alerta.className = "alert d-flex align-items-center mb-4";

  icono.className = "bi me-2";

  switch (tipo) {
    case "success":
      alerta.classList.add("alert-success");
      icono.classList.add("bi-check-circle-fill");
      break;

    case "danger":
      alerta.classList.add("alert-danger");
      icono.classList.add("bi-x-circle-fill");
      break;

    case "warning":
      alerta.classList.add("alert-warning");
      icono.classList.add("bi-exclamation-triangle-fill");
      break;

    default:
      alerta.classList.add("alert-info");
      icono.classList.add("bi-info-circle-fill");
  }

  texto.innerHTML = mensaje;
}

/*======================================================
RESTAURAR ALERTA
======================================================*/

function restaurarAlertaTestimonio() {
  mostrarAlertaTestimonio(
    "info",
    "Tu opinión ayudará a otros clientes a conocer mejor este producto.",
  );
}
