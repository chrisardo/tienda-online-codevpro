//======================================================
// Toda esta parte es de js/carrito.js
// CoDevPro Technology
//======================================================

"use strict";

/*======================================================
=            CONFIGURACIÓN GENERAL
======================================================*/

const URLS = {
  agregar: "ajax/agregar_carrito.php",

  obtener: "ajax/obtener_carrito.php",

  actualizar: "ajax/actualizar_carrito.php",

  contador: "ajax/obtener_contador_carrito.php",

  eliminar: "ajax/eliminar_carrito.php",

  vaciar: "ajax/vaciar_carrito.php",
  pagina: "./includes/obtener_carrito_pagina.php",
  resumen: "./includes/resumen_compra.php",
};

/*======================================================
=            MENSAJES SWEETALERT
======================================================*/

function mensaje(tipo, titulo, texto, tiempo = 1800) {
  Swal.fire({
    icon: tipo,

    title: titulo,

    text: texto,

    timer: tiempo,

    showConfirmButton: false,
  });
}

function mensajeError(texto) {
  Swal.fire({
    icon: "error",

    title: "Error",

    text: texto,
  });
}

function mensajeAdvertencia(texto) {
  Swal.fire({
    icon: "warning",

    title: "Atención",

    text: texto,
  });
}

function confirmar(titulo, texto) {
  return Swal.fire({
    title: titulo,

    text: texto,

    icon: "question",

    showCancelButton: true,

    confirmButtonText: "Aceptar",

    cancelButtonText: "Cancelar",
  });
}

/*======================================================
=            CONTADOR DEL CARRITO
======================================================*/

function actualizarContador(total) {
  const badge = document.getElementById("contadorCarrito");

  if (!badge) return;

  badge.innerText = total;

  if (parseInt(total) <= 0) {
    badge.classList.add("d-none");
  } else {
    badge.classList.remove("d-none");
  }
}

/*======================================================
=            CONTADOR FAVORITOS
======================================================*/

function actualizarContadorFavoritos(total) {
  const badge = document.getElementById("contadorFavoritos");

  if (!badge) return;

  badge.innerText = total;
}
function obtenerContadorFavoritos() {
  fetch("./ajax/obtener_contador_favoritos.php")
    .then((r) => r.json())

    .then((data) => {
      if (data.estado) {
        actualizarContadorFavoritos(data.contador);
      }
    })

    .catch((error) => {
      console.error(error);
    });
}
/*======================================================
=            OBTENER CONTADOR
======================================================*/

function obtenerContadorCarrito() {
  fetch(URLS.contador)
    .then((res) => res.json())

    .then((data) => {
      if (data.estado) {
        actualizarContador(data.contador);
      }
    })

    .catch(() => {
      console.error("No se pudo obtener el contador.");
    });
}

/*======================================================
=            CARGAR OFFCANVAS
======================================================*/

function cargarCarrito() {
  const contenedor = document.getElementById("contenidoCarrito");

  if (!contenedor) return;

  return fetch(URLS.obtener, {
    method: "GET",
    cache: "no-store",
  })
    .then((res) => res.text())
    .then((html) => {
      contenedor.innerHTML = html;
    })
    .catch(() => {
      contenedor.innerHTML = `
        <div class="text-center p-5">
          <i class="bi bi-exclamation-circle text-danger display-5"></i>
          <p class="mt-3 mb-0">
            No se pudo cargar el carrito.
          </p>
        </div>`;
    });
}
function cargarCarritoPagina() {
  const contenedor = document.getElementById("contenedorCarrito");

  if (!contenedor) return Promise.resolve();

  return fetch(URLS.pagina + "?t=" + Date.now(), {
    method: "GET",
    cache: "no-store",
  })
    .then((r) => {
      if (!r.ok) {
        throw new Error("No se pudo cargar el carrito.");
      }

      return r.text();
    })
    .then((html) => {
      contenedor.innerHTML = html;
    })
    .catch((error) => {
      console.error("Error al cargar carrito:", error);
    });
}
function cargarResumenCompra() {
  const resumen = document.getElementById("resumenCompra");

  if (!resumen) {
    console.warn("No existe el contenedor #resumenCompra");
    return Promise.resolve();
  }

  return fetch(URLS.resumen + "?t=" + Date.now(), {
    method: "GET",
    cache: "no-store",
    credentials: "same-origin",
  })
    .then((r) => {
      if (!r.ok) {
        throw new Error("No se pudo cargar el resumen.");
      }

      return r.text();
    })
    .then((html) => {
      resumen.innerHTML = html;
    })
    .catch((error) => {
      console.error("Error al cargar resumen:", error);
    });
}

/*======================================================
=            REFRESCAR TODO
======================================================*/

/*function refrescarCarrito() {
  cargarCarrito();

  obtenerContadorCarrito();
}*/
function refrescarCarrito() {
  cargarCarrito();
  cargarCarritoPagina(); // Productos

  cargarResumenCompra(); // Resumen
  obtenerContadorCarrito();

  // pequeño feedback visual
  const contenedor = document.getElementById("contenidoCarrito");

  if (contenedor) {
    contenedor.style.opacity = "0.5";

    setTimeout(() => {
      contenedor.style.opacity = "1";
    }, 200);
  }
}

/*======================================================
=            INICIALIZACIÓN
======================================================*/

document.addEventListener("DOMContentLoaded", function () {
  obtenerContadorCarrito();

  obtenerContadorFavoritos();

  // ==========================================
  // OFFCANVAS
  // ==========================================

  if (document.getElementById("contenidoCarrito")) {
    cargarCarrito();
  }

  // ==========================================
  // PÁGINA DEL CARRITO
  // ==========================================

  if (document.getElementById("contenedorCarrito")) {
    cargarCarritoPagina();
  }

  // ==========================================
  // RESUMEN DE COMPRA
  // ==========================================

  if (document.getElementById("resumenCompra")) {
    cargarResumenCompra();
  }
});

/*======================================================
=            FIN MÓDULO 1
======================================================*/
/*======================================================
=            AGREGAR PRODUCTO AL CARRITO
======================================================*/

let bloqueoCarrito = false;

function agregarProducto(idProducto, cantidad = 1) {
  if (bloqueoCarrito) return;

  bloqueoCarrito = true;

  let datos = new FormData();

  datos.append("idProducto", idProducto);
  datos.append("cantidad", cantidad);

  fetch(URLS.agregar, {
    method: "POST",
    body: datos,
    cache: "no-store",
  })
    .then((res) => {
      if (!res.ok) {
        throw new Error("Error HTTP");
      }

      return res.json();
    })

    .then((data) => {
      if (data.estado) {
        mensaje("success", "Correcto", data.mensaje);

        // ==========================================
        // ACTUALIZAR TODO EL CARRITO
        // ==========================================

        cargarCarrito();

        cargarCarritoPagina();

        cargarResumenCompra();

        obtenerContadorCarrito();
      } else {
        mensajeAdvertencia(data.mensaje);
      }
    })

    .catch((error) => {
      console.error(error);

      mensajeError("No fue posible agregar el producto al carrito.");
    })

    .finally(() => {
      setTimeout(() => {
        bloqueoCarrito = false;
      }, 400);
    });
}
/*======================================================
=            EVENTO BOTÓN AGREGAR
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnAgregar");
  if (!boton) return;

  e.preventDefault();

  // 🔒 bloque anti doble click
  if (boton.dataset.loading === "1") return;

  boton.dataset.loading = "1";

  let cantidad = 1;

  const inputCantidad = document.getElementById("cantidadProducto");

  if (inputCantidad) {
    cantidad = parseInt(inputCantidad.value);
    if (isNaN(cantidad) || cantidad <= 0) cantidad = 1;
  }

  agregarProducto(boton.dataset.id, cantidad);

  setTimeout(() => {
    boton.dataset.loading = "0";
  }, 600);
});

function actualizarCarrito(idCarrito, accion) {
  const btns = document.querySelectorAll(`[data-id="${idCarrito}"]`);

  btns.forEach((b) => {
    b.disabled = true;
  });

  const datos = new FormData();

  datos.append("idCarrito", idCarrito);
  datos.append("accion", accion);

  fetch(URLS.actualizar, {
    method: "POST",
    body: datos,
    cache: "no-store",
  })
    .then((res) => {
      if (!res.ok) {
        throw new Error("Error HTTP: " + res.status);
      }

      return res.json();
    })
    .then((data) => {
      if (!data.estado) {
        mensajeAdvertencia(data.mensaje || "No se pudo actualizar el carrito.");

        return Promise.reject(
          new Error(data.mensaje || "No se pudo actualizar el carrito."),
        );
      }

      // ============================================
      // ACTUALIZAR CONTADOR INMEDIATAMENTE
      // ============================================

      actualizarContador(data.contador);

      // ============================================
      // RECARGAR CARRITO Y RESUMEN
      // ============================================

      return Promise.all([
        cargarCarrito(),
        cargarCarritoPagina(),
        cargarResumenCompra(),
      ]);
    })
    .catch((error) => {
      console.error("Error al actualizar carrito:", error);

      if (error.message !== "No se pudo actualizar el carrito.") {
        mensajeError("No fue posible actualizar el carrito.");
      }
    })
    .finally(() => {
      btns.forEach((b) => {
        b.disabled = false;
      });
    });
}
/*======================================================
=            BOTÓN SUMAR
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnSumar");

  if (!boton) return;

  actualizarCarrito(
    boton.dataset.id,

    "sumar",
  );
});
/*======================================================
=            BOTÓN RESTAR
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnRestar");

  if (!boton) return;

  actualizarCarrito(
    boton.dataset.id,

    "restar",
  );
});
/*======================================================
=            ELIMINAR PRODUCTO
======================================================*/

function eliminarProducto(idCarrito) {
  confirmar(
    "¿Eliminar producto?",

    "El producto será eliminado del carrito.",
  ).then((result) => {
    if (!result.isConfirmed) {
      return;
    }

    let datos = new FormData();

    datos.append("idCarrito", idCarrito);

    fetch(URLS.eliminar, {
      method: "POST",

      body: datos,
    })
      .then((res) => res.json())

      .then((data) => {
        if (data.estado) {
          mensaje(
            "success",

            "Correcto",

            data.mensaje,
          );

          refrescarCarrito();
        } else {
          mensajeAdvertencia(data.mensaje);
        }
      })

      .catch(() => {
        mensajeError("No fue posible eliminar el producto.");
      });
  });
}
/*======================================================
=            BOTÓN ELIMINAR
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnEliminar");

  if (!boton) return;

  eliminarProducto(boton.dataset.id);
});
/*======================================================
=            VACIAR CARRITO
======================================================*/

function vaciarCarrito() {
  confirmar(
    "¿Vaciar carrito?",

    "Se eliminarán todos los productos.",
  ).then((result) => {
    if (!result.isConfirmed) {
      return;
    }

    fetch(URLS.vaciar, {
      method: "POST",
    })
      .then((res) => res.json())

      .then((data) => {
        if (data.estado) {
          mensaje(
            "success",

            "Correcto",

            data.mensaje,
          );

          refrescarCarrito();
        } else {
          mensajeAdvertencia(data.mensaje);
        }
      })

      .catch(() => {
        mensajeError("No fue posible vaciar el carrito.");
      });
  });
}
/*======================================================
=            BOTÓN VACIAR
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest("#btnVaciarCarrito");

  if (!boton) return;

  vaciarCarrito();
});
/*======================================================
=            AGREGAR / QUITAR FAVORITOS
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest(".btnFavorito");

  if (!boton) return;

  e.preventDefault();

  if (boton.dataset.loading === "1") return;

  boton.dataset.loading = "1";

  let datos = new FormData();
  datos.append("idProducto", boton.dataset.id);

  fetch("ajax/agregar_favorito.php", {
    method: "POST",
    body: datos,
  })
    .then((res) => res.json())

    .then((data) => {
      if (!data.estado) {
        Swal.fire("Error", data.mensaje, "warning");
        return;
      }

      const icono = boton.querySelector("i");

      if (data.accion === "agregado") {
        icono.classList.remove("bi-heart");
        icono.classList.add("bi-heart-fill");
        icono.classList.add("text-danger");
      } else {
        icono.classList.remove("bi-heart-fill");
        icono.classList.remove("text-danger");
        icono.classList.add("bi-heart");
      }

      obtenerContadorFavoritos();

      Swal.fire({
        icon: "success",
        title: data.mensaje,
        timer: 1200,
        showConfirmButton: false,
      });
    })

    .catch(() => {
      Swal.fire("Error", "No fue posible actualizar favoritos.", "error");
    })

    .finally(() => {
      boton.dataset.loading = "0";
    });
});
/*======================================================
CLIENTE NO LOGUEADO
======================================================*/

document.addEventListener("click", function (e) {
  const boton = e.target.closest("#btnLoginCheckout");

  if (!boton) return;

  Swal.fire({
    icon: "info",

    title: "Debes iniciar sesión",

    html: `
            Para finalizar tu compra debes iniciar sesión.<br><br>

            ¿Aún no tienes una cuenta?<br>

            Puedes registrarte gratuitamente.
        `,

    showCancelButton: true,

    confirmButtonText: "Iniciar sesión",

    cancelButtonText: "Registrarme",
  }).then((result) => {
    if (result.isConfirmed) {
      window.location = "login.php";
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      window.location = "registro_cuenta.php";
    }
  });
});
