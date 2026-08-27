//==========================================================
// CoDevPro Technology
// Archivo: js/registro_cliente.js
//==========================================================

"use strict";

/*==========================================================
=            PREVISUALIZAR IMAGEN
==========================================================*/

const inputImagen = document.getElementById("imagen");

if (inputImagen) {
  inputImagen.addEventListener("change", function () {
    const archivo = this.files[0];

    if (!archivo) return;

    // Validar tipo
    const tiposPermitidos = [
      "image/jpeg",
      "image/jpg",
      "image/png",
      "image/webp",
    ];

    if (!tiposPermitidos.includes(archivo.type)) {
      Swal.fire({
        icon: "warning",
        title: "Imagen no válida",
        text: "Solo se permiten imágenes JPG, JPEG, PNG o WEBP.",
      });

      this.value = "";

      return;
    }

    // Validar tamaño (2MB)
    if (archivo.size > 2 * 1024 * 1024) {
      Swal.fire({
        icon: "warning",
        title: "Imagen muy grande",
        text: "La imagen no debe superar los 2 MB.",
      });

      this.value = "";

      return;
    }

    document.getElementById("preview").src = URL.createObjectURL(archivo);
  });
}
/*************************************************
MOSTRAR / OCULTAR CONTRASEÑA
*************************************************/

const password = document.getElementById("password");
const password2 = document.getElementById("password2");

const verPassword = document.getElementById("verPassword");
const verPassword2 = document.getElementById("verPassword2");

if (verPassword) {
  verPassword.addEventListener("click", () => {
    if (password.type === "password") {
      password.type = "text";

      verPassword.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
      password.type = "password";

      verPassword.innerHTML = '<i class="bi bi-eye"></i>';
    }
  });
}

if (verPassword2) {
  verPassword2.addEventListener("click", () => {
    if (password2.type === "password") {
      password2.type = "text";

      verPassword2.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
      password2.type = "password";

      verPassword2.innerHTML = '<i class="bi bi-eye"></i>';
    }
  });
}
/*************************************************
FUERZA DE CONTRASEÑA
*************************************************/

const barraPassword = document.getElementById("seguridadPassword");

const textoPassword = document.getElementById("textoPassword");

if (password) {
  password.addEventListener("input", () => {
    let fuerza = 0;

    const valor = password.value;

    if (valor.length >= 8) fuerza++;

    if (/[A-Z]/.test(valor)) fuerza++;

    if (/[a-z]/.test(valor)) fuerza++;

    if (/[0-9]/.test(valor)) fuerza++;

    if (/[^A-Za-z0-9]/.test(valor)) fuerza++;

    barraPassword.className = "progress-bar";

    switch (fuerza) {
      case 0:
        barraPassword.style.width = "0%";

        textoPassword.innerHTML = "";

        break;

      case 1:
        barraPassword.style.width = "20%";

        barraPassword.classList.add("bg-danger");

        textoPassword.innerHTML = "Muy débil";

        break;

      case 2:
        barraPassword.style.width = "40%";

        barraPassword.classList.add("bg-warning");

        textoPassword.innerHTML = "Débil";

        break;

      case 3:
        barraPassword.style.width = "60%";

        barraPassword.classList.add("bg-info");

        textoPassword.innerHTML = "Regular";

        break;

      case 4:
        barraPassword.style.width = "80%";

        barraPassword.classList.add("bg-primary");

        textoPassword.innerHTML = "Buena";

        break;

      case 5:
        barraPassword.style.width = "100%";

        barraPassword.classList.add("bg-success");

        textoPassword.innerHTML = "Muy segura";

        break;
    }
  });
}
/*==========================================================
=            REGISTRAR CLIENTE
==========================================================*/

const formulario = document.getElementById("formRegistroCliente");

if (formulario) {
  formulario.addEventListener("submit", function (e) {
    e.preventDefault();

    const btn = formulario.querySelector("button[type='submit']");

    const textoOriginal = btn.innerHTML;

    btn.disabled = true;

    btn.innerHTML = `
            <span class="spinner-border spinner-border-sm"></span>
            Registrando...
        `;

    const datos = new FormData(formulario);

    fetch("ajax/registrar_cliente.php", {
      method: "POST",

      body: datos,
    })
      .then(function (respuesta) {
        if (!respuesta.ok) {
          throw new Error("Error del servidor");
        }

        return respuesta.json();
      })

      .then(function (data) {
        btn.disabled = false;

        btn.innerHTML = textoOriginal;

        if (data.estado) {
          Swal.fire({
            icon: "success",

            title: "Cuenta creada",

            text: data.mensaje,

            confirmButtonText: "Iniciar sesión",
          }).then(() => {
            window.location.href = "login.php";
          });
        } else {
          Swal.fire({
            icon: "warning",

            title: "Atención",

            text: data.mensaje,
          });
        }
      })

      .catch(function (error) {
        console.error(error);

        btn.disabled = false;

        btn.innerHTML = textoOriginal;

        Swal.fire({
          icon: "error",

          title: "Error",

          text: "Ocurrió un error al registrar la cuenta.",
        });
      });
  });
}
/*==========================================================
=            PAÍS -> DEPARTAMENTOS
==========================================================*/

const selectPais = document.getElementById("pais");
const selectDepartamento = document.getElementById("departamento");
const selectProvincia = document.getElementById("provincia");
const selectDistrito = document.getElementById("distrito");

if (selectPais) {
  selectPais.addEventListener("change", function () {
    const idPais = this.value;

    /*------------------------------------------
    LIMPIAR LOS SELECTS
    ------------------------------------------*/

    selectDepartamento.innerHTML = '<option value="">Cargando...</option>';

    selectProvincia.innerHTML =
      '<option value="">Seleccione un departamento</option>';

    selectDistrito.innerHTML =
      '<option value="">Seleccione una provincia</option>';

    if (idPais === "") {
      selectDepartamento.innerHTML =
        '<option value="">Seleccione un país</option>';

      return;
    }

    /*------------------------------------------
    OBTENER DEPARTAMENTOS
    ------------------------------------------*/

    const datos = new FormData();

    datos.append("id_pais", idPais);

    fetch("ajax/obtener_departamentos.php", {
      method: "POST",

      body: datos,
    })
      .then((respuesta) => respuesta.text())

      .then((html) => {
        selectDepartamento.innerHTML = html;
      })

      .catch(() => {
        selectDepartamento.innerHTML =
          '<option value="">Error al cargar</option>';
      });
  });
}
/*==========================================================
=            DEPARTAMENTO -> PROVINCIAS
==========================================================*/

if (selectDepartamento) {
  selectDepartamento.addEventListener("change", function () {
    const idDepartamento = this.value;

    /*------------------------------------------
        LIMPIAR LOS SELECTS
        ------------------------------------------*/

    selectProvincia.innerHTML = '<option value="">Cargando...</option>';

    selectDistrito.innerHTML =
      '<option value="">Seleccione una provincia</option>';

    if (idDepartamento === "") {
      selectProvincia.innerHTML =
        '<option value="">Seleccione un departamento</option>';

      return;
    }

    /*------------------------------------------
        OBTENER PROVINCIAS
        ------------------------------------------*/

    const datos = new FormData();

    datos.append("id_departamento", idDepartamento);

    fetch("ajax/obtener_provincias.php", {
      method: "POST",

      body: datos,
    })
      .then((respuesta) => respuesta.text())

      .then((html) => {
        selectProvincia.innerHTML = html;
      })

      .catch(() => {
        selectProvincia.innerHTML = '<option value="">Error al cargar</option>';
      });
  });
}
/*==========================================================
=            PROVINCIA -> DISTRITOS
==========================================================*/

if (selectProvincia) {
  selectProvincia.addEventListener("change", function () {
    const idProvincia = this.value;

    /*------------------------------------------
        LIMPIAR SELECT
        ------------------------------------------*/

    selectDistrito.innerHTML = '<option value="">Cargando...</option>';

    if (idProvincia === "") {
      selectDistrito.innerHTML =
        '<option value="">Seleccione una provincia</option>';

      return;
    }

    /*------------------------------------------
        OBTENER DISTRITOS
        ------------------------------------------*/

    const datos = new FormData();

    datos.append("id_provincia", idProvincia);

    fetch("ajax/obtener_distritos.php", {
      method: "POST",

      body: datos,
    })
      .then((respuesta) => respuesta.text())

      .then((html) => {
        selectDistrito.innerHTML = html;
      })

      .catch(() => {
        selectDistrito.innerHTML = '<option value="">Error al cargar</option>';
      });
  });
}
