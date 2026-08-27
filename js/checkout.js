//======================================================
// js/checkout.js
// CoDevPro Technology
//======================================================

"use strict";

/*======================================================
=            CONFIGURACIÓN
======================================================*/

const CHECKOUT = {
  finalizar: "ajax/finalizar_compra.php",

  confirmar: "pedido_confirmado.php",
};

/*======================================================
=            MENSAJES
======================================================*/

function mostrarMensaje(icono, titulo, texto, tiempo = 1800) {
  Swal.fire({
    icon: icono,

    title: titulo,

    text: texto,

    timer: tiempo,

    showConfirmButton: false,
  });
}

function mostrarError(texto) {
  Swal.fire({
    icon: "error",

    title: "Error",

    text: texto,
  });
}

function mostrarAdvertencia(texto) {
  Swal.fire({
    icon: "warning",

    title: "Atención",

    text: texto,
  });
}

function mostrarExito(texto) {
  Swal.fire({
    icon: "success",

    title: "Compra realizada",

    text: texto,

    confirmButtonText: "Aceptar",
  });
}

/*======================================================
=            LOADING
======================================================*/

function mostrarLoading() {
  Swal.fire({
    title: "Procesando pedido...",

    html: "Espere un momento.",

    allowOutsideClick: false,

    allowEscapeKey: false,

    didOpen: () => {
      Swal.showLoading();
    },
  });
}

function cerrarLoading() {
  Swal.close();
}

/*======================================================
=            OBTENER VALOR
======================================================*/

function valor(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return "";
  }

  return elemento.value.trim();
}

/*======================================================
=            OBTENER CHECKBOX
======================================================*/

function check(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return false;
  }

  return elemento.checked;
}

/*======================================================
=            MÉTODO DE PAGO
======================================================*/

function obtenerMetodoPago() {
  const radio = document.querySelector('input[name="id_metodo_pago"]:checked');

  if (!radio) {
    return "";
  }

  return radio.value;
}

/*======================================================
=            TÉRMINOS
======================================================*/

function aceptoTerminos() {
  return check("aceptoTerminos");
}

/*======================================================
=            CREAR FORMDATA
======================================================*/

function obtenerDatosFormulario() {
  let datos = new FormData();

  datos.append(
    "direccion",

    valor("direccion"),
  );

  datos.append(
    "comentarios",

    valor("comentarios"),
  );

  datos.append(
    "guardarDireccion",

    check("guardarDireccion") ? 1 : 0,
  );

  datos.append(
    "id_metodo_pago",

    obtenerMetodoPago(),
  );

  return datos;
}

/*======================================================
=            VALIDAR FORMULARIO
======================================================*/

function validarFormulario() {
  //=====================================
  // DIRECCIÓN
  //=====================================

  if (valor("direccion") === "") {
    mostrarAdvertencia("Ingrese la dirección de entrega.");

    document.getElementById("direccion").focus();

    return false;
  }

  //=====================================
  // MÉTODO DE PAGO
  //=====================================

  if (obtenerMetodoPago() === "") {
    mostrarAdvertencia("Seleccione un método de pago.");

    return false;
  }

  //=====================================
  // TÉRMINOS
  //=====================================

  if (!aceptoTerminos()) {
    mostrarAdvertencia("Debe aceptar los términos y condiciones.");

    return false;
  }

  return true;
}

/*======================================================
=            INICIALIZACIÓN
======================================================*/

document.addEventListener(
  "DOMContentLoaded",

  function () {
    console.log("Checkout inicializado correctamente.");
  },
);
/*======================================================
=            FINALIZAR COMPRA
======================================================*/

let procesandoPedido = false;

async function finalizarCompra() {
  //=========================================
  // EVITAR DOBLE CLICK
  //=========================================

  if (procesandoPedido) {
    return;
  }

  //=========================================
  // VALIDAR
  //=========================================

  if (!validarFormulario()) {
    return;
  }

  const boton = document.getElementById("btnFinalizarCompra");

  procesandoPedido = true;

  if (boton) {
    boton.disabled = true;

    boton.innerHTML = `

            <span class="spinner-border spinner-border-sm me-2"></span>

            Procesando compra...

        `;
  }

  mostrarLoading();

  try {
    const datos = obtenerDatosFormulario();

    const respuesta = await fetch(
      CHECKOUT.finalizar,

      {
        method: "POST",

        body: datos,
      },
    );

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const resultado = await respuesta.json();

    cerrarLoading();

    if (resultado.estado) {
      await Swal.fire({
        icon: "success",

        title: "Compra realizada",

        text: resultado.mensaje,

        confirmButtonColor: "#198754",

        confirmButtonText: "Aceptar",
      });

      if (resultado.id_ticket > 0) {
        window.location.replace(
          CHECKOUT.confirmar + "?id=" + resultado.id_ticket,
        );
      } else {
        mostrarError("No se pudo obtener el número del pedido.");
      }

      return;
    }

    mostrarAdvertencia(resultado.mensaje);
  } catch (error) {
    console.error(error);

    cerrarLoading();

    mostrarError("Ocurrió un error al procesar la compra.");
  } finally {
    procesandoPedido = false;

    if (boton) {
      boton.disabled = false;

      boton.innerHTML = `

                <i class="bi bi-check-circle-fill"></i>

                Finalizar Compra

            `;
    }
  }
}

/*======================================================
=            BOTÓN FINALIZAR
======================================================*/

document.addEventListener(
  "click",

  function (e) {
    const boton = e.target.closest("#btnFinalizarCompra");

    if (!boton) {
      return;
    }

    e.preventDefault();

    finalizarCompra();
  },
);

/*======================================================
=            ENTER EN EL FORMULARIO
======================================================*/

document.addEventListener(
  "keydown",

  function (e) {
    if (e.key === "Enter" && e.target.tagName !== "TEXTAREA") {
      e.preventDefault();
    }
  },
);

/*======================================================
=            AUTOFOCUS
======================================================*/

document.addEventListener(
  "DOMContentLoaded",

  function () {
    const direccion = document.getElementById("direccion");

    if (direccion) {
      direccion.focus();
    }
  },
);

/*======================================================
=            FIN
======================================================*/
