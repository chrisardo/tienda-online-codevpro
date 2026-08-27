"use strict";

/*======================================================
=            CONFIG
======================================================*/

const URL_TESTIMONIO = "ajax/guardar_testimonio.php";

/*======================================================
=            VARIABLES
======================================================*/

let estrellasSeleccionadas = 0;

/*======================================================
=            ABRIR MODAL
======================================================*/

document.addEventListener("click", function (e) {
  const btn = e.target.closest(".btnTestimonio");

  if (!btn) return;

  document.getElementById("idTicketTestimonio").value = btn.dataset.ticket;
  document.getElementById("idProductoTestimonio").value = btn.dataset.producto;

  document.getElementById("comentarioTestimonio").value = "";
  document.getElementById("calificacion").value = 0;

  resetEstrellas();

  new bootstrap.Modal(document.getElementById("modalTestimonio")).show();
});

/*======================================================
=            ESTRELLAS
======================================================*/

document.querySelectorAll("#estrellas i").forEach((estrella) => {
  estrella.addEventListener("mouseover", function () {
    pintarEstrellas(this.dataset.value);
  });

  estrella.addEventListener("click", function () {
    estrellasSeleccionadas = this.dataset.value;

    document.getElementById("calificacion").value = estrellasSeleccionadas;
  });
});

document
  .getElementById("estrellas")
  .addEventListener("mouseleave", function () {
    pintarEstrellas(estrellasSeleccionadas);
  });

function pintarEstrellas(valor) {
  document.querySelectorAll("#estrellas i").forEach((star) => {
    if (star.dataset.value <= valor) {
      star.classList.remove("bi-star");
      star.classList.add("bi-star-fill");
    } else {
      star.classList.remove("bi-star-fill");
      star.classList.add("bi-star");
    }
  });
}

function resetEstrellas() {
  estrellasSeleccionadas = 0;
  pintarEstrellas(0);
}

/*======================================================
=            ENVIAR TESTIMONIO
======================================================*/

document
  .getElementById("btnEnviarTestimonio")
  .addEventListener("click", function () {
    let idTicket = document.getElementById("idTicketTestimonio").value;
    let idProducto = document.getElementById("idProductoTestimonio").value;
    let calificacion = document.getElementById("calificacion").value;
    let comentario = document
      .getElementById("comentarioTestimonio")
      .value.trim();

    if (calificacion == 0) {
      Swal.fire("Atención", "Selecciona una calificación", "warning");
      return;
    }

    if (comentario === "") {
      Swal.fire("Atención", "Escribe un comentario", "warning");
      return;
    }

    let datos = new FormData();

    datos.append("id_ticket_ventas", idTicket);
    datos.append("idProducto", idProducto);
    datos.append("calificacion", calificacion);
    datos.append("comentario", comentario);

    fetch(URL_TESTIMONIO, {
      method: "POST",
      body: datos,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.estado) {
          Swal.fire("Correcto", data.mensaje, "success");

          bootstrap.Modal.getInstance(
            document.getElementById("modalTestimonio"),
          ).hide();
        } else {
          Swal.fire("Error", data.mensaje, "error");
        }
      })
      .catch(() => {
        Swal.fire("Error", "No se pudo enviar el testimonio", "error");
      });
  });
