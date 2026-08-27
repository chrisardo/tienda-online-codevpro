//====================================================
// CoDevPro Technology
// js/login.js
//====================================================

"use strict";

document.addEventListener("DOMContentLoaded", function () {
  const formulario = document.getElementById("formLogin");

  if (!formulario) return;

  formulario.addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("contrasena").value.trim();

    if (email === "") {
      Swal.fire({
        icon: "warning",
        title: "Correo requerido",
        text: "Ingrese su correo electrónico.",
      });

      return;
    }

    if (password === "") {
      Swal.fire({
        icon: "warning",
        title: "Contraseña requerida",
        text: "Ingrese su contraseña.",
      });

      return;
    }

    let datos = new FormData(formulario);

    fetch("ajax/login.php", {
      method: "POST",

      body: datos,
    })
      .then((response) => response.json())

      .then((data) => {
        if (data.estado) {
          Swal.fire({
            icon: "success",

            title: "Bienvenido",

            text: data.mensaje,

            timer: 1500,

            showConfirmButton: false,
          });

          setTimeout(function () {
            if (data.redireccion) {
              window.location.href = data.redireccion;
            } else {
              window.location.href = "index.php";
            }
          }, 1500);
        } else {
          Swal.fire({
            icon: "error",

            title: "No se pudo iniciar sesión",

            text: data.mensaje,
          });
        }
      })

      .catch((error) => {
        console.error(error);

        Swal.fire({
          icon: "error",

          title: "Error",

          text: "Ocurrió un error al conectar con el servidor.",
        });
      });
  });
});
