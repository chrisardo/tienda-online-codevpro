//======================================================
// CoDevPro Technology
// js/perfil.js
//======================================================

"use strict";
/*======================================================
=            FOTO ORIGINAL
======================================================*/

let fotoAnterior = "";

/*======================================================
=            VARIABLES GLOBALES
======================================================*/

const MENU_SELECTOR = ".menuPerfil";
const VISTA_SELECTOR = ".perfil-vista";

/*======================================================
=            INICIO
======================================================*/

document.addEventListener("DOMContentLoaded", () => {
  iniciarPerfil();
});

/*======================================================
=            INICIALIZAR PERFIL
======================================================*/

function iniciarPerfil() {
  iniciarMenuPerfil();

  const ultimaVista = obtenerVistaActual();

  if (ultimaVista) {
    const menu = document.querySelector(
      `.menuPerfil[data-vista="${ultimaVista}"]`,
    );

    if (menu) {
      activarMenu(menu);

      mostrarVista(ultimaVista);

      return;
    }
  }

  const menuInicial = document.querySelector(".menuPerfil");

  if (menuInicial) {
    activarMenu(menuInicial);

    mostrarVista(menuInicial.dataset.vista);
  }
}

/*======================================================
=            MENÚ LATERAL
======================================================*/

function iniciarMenuPerfil() {
  const menus = document.querySelectorAll(MENU_SELECTOR);

  menus.forEach((menu) => {
    menu.addEventListener("click", function (e) {
      e.preventDefault();

      const vista = this.dataset.vista;

      if (!vista) return;

      const actual = document.querySelector(".perfil-vista.activa");

      if (actual && actual.id === vista) {
        return;
      }

      mostrarVista(vista);

      activarMenu(this);
    });
  });
}

/*======================================================
=            MOSTRAR VISTA
======================================================*/

function mostrarVista(idVista) {
  ocultarTodasLasVistas();

  const vista = document.getElementById(idVista);

  if (!vista) return;

  requestAnimationFrame(() => {
    vista.classList.add("activa");
  });

  scrollSuperior();
}

/*======================================================
=            OCULTAR TODAS
======================================================*/

function ocultarTodasLasVistas() {
  document.querySelectorAll(VISTA_SELECTOR).forEach((vista) => {
    vista.classList.remove("activa");
  });
}

/*======================================================
=            MENÚ ACTIVO
======================================================*/

function activarMenu(menuActivo) {
  document.querySelectorAll(MENU_SELECTOR).forEach((menu) => {
    menu.classList.remove("active");

    menu.setAttribute("aria-current", "false");
  });

  menuActivo.classList.add("active");

  menuActivo.setAttribute("aria-current", "page");

  guardarVistaActual(menuActivo.dataset.vista);
}
/*======================================================
=            GUARDAR ÚLTIMA VISTA
======================================================*/

function guardarVistaActual(vista) {
  localStorage.setItem("perfilVista", vista);
}

/*======================================================
=            OBTENER ÚLTIMA VISTA
======================================================*/

function obtenerVistaActual() {
  return localStorage.getItem("perfilVista");
}
/*======================================================
=            SCROLL
======================================================*/

function scrollSuperior() {
  window.scrollTo({
    top: 0,

    behavior: "smooth",
  });
}

/*======================================================
=            API PÚBLICA
======================================================*/

window.PerfilCliente = {
  mostrarVista,

  activarMenu,
};
/*======================================================
=            EDITAR INFORMACIÓN PERSONAL
======================================================*/

document.addEventListener("DOMContentLoaded", () => {
  iniciarEditarPerfil();
});

/*======================================================
=            INICIAR
======================================================*/

function iniciarEditarPerfil() {
  const btnEditar = document.getElementById("btnEditarPerfil");

  const btnGuardar = document.getElementById("btnGuardarPerfil");

  const formulario = document.getElementById("formEditarPerfil");

  if (!btnEditar || !btnGuardar || !formulario) {
    return;
  }

  /*=========================================
    EDITAR
    =========================================*/

  btnEditar.addEventListener("click", function () {
    habilitarFormulario(formulario);

    btnGuardar.disabled = false;

    btnEditar.disabled = true;
  });

  /*=========================================
    GUARDAR
    =========================================*/

  formulario.addEventListener("submit", function (e) {
    e.preventDefault();

    guardarPerfil(formulario, btnGuardar, btnEditar);
  });
}

/*======================================================
=            HABILITAR FORMULARIO
======================================================*/

function habilitarFormulario(formulario) {
  formulario.querySelectorAll("input, textarea, select").forEach((campo) => {
    if (campo.name === "email") {
      return;
    }

    if (campo.name === "fecha_registro") {
      return;
    }

    campo.removeAttribute("readonly");

    campo.removeAttribute("disabled");
    ["pais", "departamento", "provincia", "distrito"].forEach((id) => {
      const select = document.getElementById(id);

      if (select) {
        select.disabled = false;
      }
    });
  });
}

/*======================================================
=            DESHABILITAR FORMULARIO
======================================================*/

function bloquearFormulario(formulario) {
  formulario.querySelectorAll("input, textarea, select").forEach((campo) => {
    if (campo.tagName === "SELECT") {
      campo.disabled = true;
    } else {
      campo.setAttribute("readonly", true);
    }
    ["pais", "departamento", "provincia", "distrito"].forEach((id) => {
      const select = document.getElementById(id);

      if (select) {
        select.disabled = true;
      }
    });
  });
}

/*======================================================
=            GUARDAR PERFIL
======================================================*/

async function guardarPerfil(formulario, btnGuardar, btnEditar) {
  btnGuardar.disabled = true;

  btnGuardar.innerHTML = `
        <span class="spinner-border spinner-border-sm"></span>
        Guardando...
    `;

  const datos = new FormData(formulario);

  try {
    const respuesta = await fetch("ajax/actualizar_perfil.php", {
      method: "POST",

      body: datos,
    });

    const json = await respuesta.json();

    if (json.estado === "ok") {
      bloquearFormulario(formulario);

      btnEditar.disabled = false;

      btnGuardar.innerHTML = `
                <i class="bi bi-check-circle-fill"></i>
                Guardar cambios
            `;

      btnGuardar.disabled = true;

      actualizarCabecera(json);

      mostrarMensaje(json.mensaje, "success");
    } else {
      btnGuardar.disabled = false;

      btnGuardar.innerHTML = `
                <i class="bi bi-check-circle-fill"></i>
                Guardar cambios
            `;

      mostrarMensaje(json.mensaje, "danger");
    }
  } catch (error) {
    console.error(error);

    btnGuardar.disabled = false;

    btnGuardar.innerHTML = `
            <i class="bi bi-check-circle-fill"></i>
            Guardar cambios
        `;

    mostrarMensaje("Error al conectar con el servidor.", "danger");
  }
}

/*======================================================
=            ACTUALIZAR CABECERA
======================================================*/

function actualizarCabecera(datos) {
  if (document.querySelector("#nombreCabecera")) {
    document.querySelector("#nombreCabecera").textContent = datos.nombre;
  }

  if (document.querySelector("#correoCabecera")) {
    document.querySelector("#correoCabecera").textContent = datos.email;
  }

  if (document.querySelector("#celularCabecera")) {
    document.querySelector("#celularCabecera").textContent = datos.celular;
  }

  if (document.querySelector("#direccionCabecera")) {
    document.querySelector("#direccionCabecera").textContent =
      datos.direccionCompleta;
  }
}

/*======================================================
=            MENSAJES
======================================================*/

function mostrarMensaje(texto, tipo) {
  const alerta = document.createElement("div");

  alerta.className = `alert alert-${tipo} alert-dismissible fade show mt-3`;

  alerta.innerHTML = `
        ${texto}
        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>
    `;

  const formulario = document.getElementById("formEditarPerfil");

  formulario.prepend(alerta);

  setTimeout(() => {
    alerta.remove();
  }, 4000);
}
/*======================================================
=            CARGA EN CASCADA
======================================================*/

document.addEventListener("DOMContentLoaded", iniciarUbigeo);

/*======================================================
=            INICIAR
======================================================*/

function iniciarUbigeo() {
  const pais = document.getElementById("pais");
  const departamento = document.getElementById("departamento");
  const provincia = document.getElementById("provincia");

  if (!pais || !departamento || !provincia) {
    return;
  }

  pais.addEventListener("change", function () {
    cargarDepartamentos(this.value);
  });

  departamento.addEventListener("change", function () {
    cargarProvincias(this.value);
  });

  provincia.addEventListener("change", function () {
    cargarDistritos(this.value);
  });
}

/*======================================================
=            DEPARTAMENTOS
======================================================*/

function cargarDepartamentos(idPais) {
  fetch("ajax/obtener_departamentos.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },

    body: "id_pais=" + idPais,
  })
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("departamento").innerHTML = html;

      document.getElementById("provincia").innerHTML =
        "<option value=''>Seleccione...</option>";

      document.getElementById("distrito").innerHTML =
        "<option value=''>Seleccione...</option>";
    });
}

/*======================================================
=            PROVINCIAS
======================================================*/

function cargarProvincias(idDepartamento) {
  fetch("ajax/obtener_provincias.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },

    body: "id_departamento=" + idDepartamento,
  })
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("provincia").innerHTML = html;

      document.getElementById("distrito").innerHTML =
        "<option value=''>Seleccione...</option>";
    });
}

/*======================================================
=            DISTRITOS
======================================================*/

function cargarDistritos(idProvincia) {
  fetch("ajax/obtener_distritos.php", {
    method: "POST",

    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },

    body: "id_provincia=" + idProvincia,
  })
    .then((res) => res.text())

    .then((html) => {
      document.getElementById("distrito").innerHTML = html;
    });
}
/*======================================================
=            FOTOGRAFÍA DEL PERFIL
======================================================*/

document.addEventListener("DOMContentLoaded", iniciarFotografiaPerfil);

/*======================================================
=            INICIAR
======================================================*/

function iniciarFotografiaPerfil() {
  const btnCambiar = document.getElementById("btnCambiarFoto");

  const inputFoto = document.getElementById("fotoPerfil");

  const preview = document.getElementById("previewFotoPerfil");

  if (!btnCambiar || !inputFoto || !preview) {
    return;
  }

  /*=========================================
    BOTÓN
    =========================================*/

  btnCambiar.addEventListener("click", function () {
    inputFoto.click();
  });

  /*=========================================
    CLICK SOBRE LA FOTO
    =========================================*/

  preview.addEventListener("click", function () {
    inputFoto.click();
  });

  /*=========================================
    CAMBIO DE IMAGEN
    =========================================*/

  inputFoto.addEventListener("change", function () {
    if (!this.files.length) {
      return;
    }

    validarImagen(this.files[0]);
  });
}

/*======================================================
=            VALIDAR IMAGEN
======================================================*/

function validarImagen(archivo) {
  const formatosPermitidos = [
    "image/jpeg",

    "image/jpg",

    "image/png",

    "image/webp",
  ];

  if (!formatosPermitidos.includes(archivo.type)) {
    mostrarMensaje(
      "Solo se permiten imágenes JPG, PNG o WEBP.",

      "danger",
    );

    limpiarInputFoto();

    return;
  }

  const pesoMaximo = 3 * 1024 * 1024;

  if (archivo.size > pesoMaximo) {
    mostrarMensaje(
      "La fotografía no puede superar los 3 MB.",

      "danger",
    );

    limpiarInputFoto();

    return;
  }

  mostrarVistaPrevia(archivo);

  subirFotografia(archivo);
}

/*======================================================
=            VISTA PREVIA
======================================================*/

function mostrarVistaPrevia(archivo) {
  const preview = document.getElementById("previewFotoPerfil");

  fotoAnterior = preview.src;

  const lector = new FileReader();

  lector.onload = function (e) {
    const imagen = e.target.result;

    preview.src = imagen;

    const cabecera = document.getElementById("fotoCabecera");

    if (cabecera) {
      cabecera.src = imagen;
    }

    const navbar = document.getElementById("fotoNavbar");

    if (navbar) {
      navbar.src = imagen;
    }
  };

  lector.readAsDataURL(archivo);
}

/*======================================================
=            SUBIR FOTOGRAFÍA
======================================================*/

async function subirFotografia(archivo) {
  const datos = new FormData();

  datos.append("foto", archivo);

  const boton = document.getElementById("btnCambiarFoto");

  const textoOriginal = boton.innerHTML;

  boton.disabled = true;

  boton.innerHTML = `

        <span class="spinner-border spinner-border-sm me-2"></span>

        Subiendo...

    `;

  try {
    const respuesta = await fetch(
      "ajax/actualizar_foto_perfil.php",

      {
        method: "POST",

        body: datos,
      },
    );

    const json = await respuesta.json();

    if (json.estado === "ok") {
      mostrarMensaje(
        json.mensaje,

        "success",
      );
    } else {
      restaurarFotoAnterior();

      mostrarMensaje(
        json.mensaje,

        "danger",
      );
    }
  } catch (error) {
    console.error(error);

    restaurarFotoAnterior();

    mostrarMensaje(
      "No fue posible subir la fotografía.",

      "danger",
    );
  } finally {
    boton.disabled = false;

    boton.innerHTML = textoOriginal;

    limpiarInputFoto();
  }
}

/*======================================================
=            LIMPIAR INPUT
======================================================*/

function limpiarInputFoto() {
  const input = document.getElementById("fotoPerfil");

  if (input) {
    input.value = "";
  }
}
/*======================================================
=            RESTAURAR FOTO
======================================================*/

function restaurarFotoAnterior() {
  if (fotoAnterior === "") {
    return;
  }

  const preview = document.getElementById("previewFotoPerfil");

  preview.src = fotoAnterior;

  const cabecera = document.getElementById("fotoCabecera");

  if (cabecera) {
    cabecera.src = fotoAnterior;
  }

  const navbar = document.getElementById("fotoNavbar");

  if (navbar) {
    navbar.src = fotoAnterior;
  }
}
/*======================================================
=            SEGURIDAD DE LA CUENTA
======================================================*/

document.addEventListener("DOMContentLoaded", iniciarSeguridadPerfil);

/*======================================================
=            INICIAR SEGURIDAD
======================================================*/

function iniciarSeguridadPerfil() {
  iniciarMostrarPassword();

  iniciarFortalezaPassword();

  iniciarLimpiarPassword();
}

/*======================================================
=            MOSTRAR / OCULTAR PASSWORD
======================================================*/

function iniciarMostrarPassword() {
  const botones = document.querySelectorAll(".btnPassword");

  botones.forEach((boton) => {
    boton.addEventListener("click", function () {
      const inputID = this.dataset.target;

      mostrarOcultarPassword(inputID, this);
    });
  });
}

/*======================================================
=            MOSTRAR PASSWORD
======================================================*/

function mostrarOcultarPassword(idInput, boton) {
  const input = document.getElementById(idInput);

  if (!input) {
    return;
  }

  const icono = boton.querySelector("i");

  if (input.type === "password") {
    input.type = "text";

    icono.classList.remove("bi-eye");

    icono.classList.add("bi-eye-slash");
  } else {
    input.type = "password";

    icono.classList.remove("bi-eye-slash");

    icono.classList.add("bi-eye");
  }
}

/*======================================================
=            FORTALEZA DE PASSWORD
======================================================*/

function iniciarFortalezaPassword() {
  const input = document.getElementById("passwordNueva");

  if (!input) {
    return;
  }

  input.addEventListener("input", function () {
    actualizarFortalezaPassword(this.value);
  });
}

/*======================================================
=            ACTUALIZAR FORTALEZA
======================================================*/

function actualizarFortalezaPassword(password) {
  const barra = document.getElementById("barraPassword");

  const texto = document.getElementById("textoFortaleza");

  if (!barra || !texto) {
    return;
  }

  let puntuacion = 0;

  /*=========================================
    MÍNIMO 8 CARACTERES
    =========================================*/

  if (password.length >= 8) {
    puntuacion += 20;
  }

  /*=========================================
    MAYÚSCULA
    =========================================*/

  if (/[A-Z]/.test(password)) {
    puntuacion += 20;
  }

  /*=========================================
    MINÚSCULA
    =========================================*/

  if (/[a-z]/.test(password)) {
    puntuacion += 20;
  }

  /*=========================================
    NÚMERO
    =========================================*/

  if (/[0-9]/.test(password)) {
    puntuacion += 20;
  }

  /*=========================================
    CARÁCTER ESPECIAL
    =========================================*/

  if (/[^A-Za-z0-9]/.test(password)) {
    puntuacion += 20;
  }

  barra.style.width = puntuacion + "%";

  actualizarColorPassword(puntuacion, barra, texto);
}

/*======================================================
=            COLOR DE LA BARRA
======================================================*/

function actualizarColorPassword(puntuacion, barra, texto) {
  barra.className = "progress-bar";

  if (puntuacion <= 20) {
    barra.classList.add("bg-danger");

    texto.textContent = "Contraseña muy débil.";

    return;
  }

  if (puntuacion <= 40) {
    barra.classList.add("bg-warning");

    texto.textContent = "Contraseña débil.";

    return;
  }

  if (puntuacion <= 60) {
    barra.classList.add("bg-info");

    texto.textContent = "Contraseña aceptable.";

    return;
  }

  if (puntuacion <= 80) {
    barra.classList.add("bg-primary");

    texto.textContent = "Contraseña segura.";

    return;
  }

  barra.classList.add("bg-success");

  texto.textContent = "Contraseña muy segura.";
}

/*======================================================
=            LIMPIAR FORMULARIO
======================================================*/

function iniciarLimpiarPassword() {
  const boton = document.getElementById("btnCancelarPassword");

  if (!boton) {
    return;
  }

  boton.addEventListener("click", limpiarPassword);
}

/*======================================================
=            LIMPIAR PASSWORD
======================================================*/

function limpiarPassword() {
  const passwordActual = document.getElementById("passwordActual");

  const passwordNueva = document.getElementById("passwordNueva");

  const passwordConfirmar = document.getElementById("passwordConfirmar");

  if (passwordActual) {
    passwordActual.value = "";
  }

  if (passwordNueva) {
    passwordNueva.value = "";
  }

  if (passwordConfirmar) {
    passwordConfirmar.value = "";
  }

  const barra = document.getElementById("barraPassword");

  const texto = document.getElementById("textoFortaleza");

  if (barra) {
    barra.style.width = "0%";

    barra.className = "progress-bar";
  }

  if (texto) {
    texto.textContent = "Ingrese una contraseña segura.";
  }
}
/*======================================================
=            VALIDAR CAMBIO DE CONTRASEÑA
======================================================*/

document.addEventListener("DOMContentLoaded", iniciarActualizarPassword);

/*======================================================
=            INICIAR
======================================================*/

function iniciarActualizarPassword() {
  const boton = document.getElementById("btnActualizarPassword");

  if (!boton) {
    return;
  }

  boton.addEventListener("click", function () {
    validarFormularioPassword();
  });
}

/*======================================================
=            VALIDAR FORMULARIO
======================================================*/

function validarFormularioPassword() {
  const passwordActual = document.getElementById("passwordActual").value.trim();

  const passwordNueva = document.getElementById("passwordNueva").value.trim();

  const passwordConfirmar = document
    .getElementById("passwordConfirmar")
    .value.trim();

  /*=========================================
    CAMPOS VACÍOS
    =========================================*/

  if (
    passwordActual === "" ||
    passwordNueva === "" ||
    passwordConfirmar === ""
  ) {
    mostrarMensajePassword(
      "Todos los campos son obligatorios.",

      "danger",
    );

    return;
  }

  /*=========================================
    LONGITUD MÍNIMA
    =========================================*/

  if (passwordNueva.length < 8) {
    mostrarMensajePassword(
      "La nueva contraseña debe tener como mínimo 8 caracteres.",

      "danger",
    );

    return;
  }

  /*=========================================
    MAYÚSCULA
    =========================================*/

  if (!/[A-Z]/.test(passwordNueva)) {
    mostrarMensajePassword(
      "La contraseña debe tener al menos una letra mayúscula.",

      "danger",
    );

    return;
  }

  /*=========================================
    MINÚSCULA
    =========================================*/

  if (!/[a-z]/.test(passwordNueva)) {
    mostrarMensajePassword(
      "La contraseña debe tener al menos una letra minúscula.",

      "danger",
    );

    return;
  }

  /*=========================================
    NÚMERO
    =========================================*/

  if (!/[0-9]/.test(passwordNueva)) {
    mostrarMensajePassword(
      "La contraseña debe tener al menos un número.",

      "danger",
    );

    return;
  }

  /*=========================================
    CARÁCTER ESPECIAL
    =========================================*/

  if (!/[^A-Za-z0-9]/.test(passwordNueva)) {
    mostrarMensajePassword(
      "La contraseña debe tener un carácter especial.",

      "danger",
    );

    return;
  }

  /*=========================================
    CONFIRMACIÓN
    =========================================*/

  if (passwordNueva !== passwordConfirmar) {
    mostrarMensajePassword(
      "Las contraseñas no coinciden.",

      "danger",
    );

    return;
  }

  /*=========================================
    MISMA CONTRASEÑA
    =========================================*/

  if (passwordActual === passwordNueva) {
    mostrarMensajePassword(
      "La nueva contraseña debe ser diferente a la actual.",

      "danger",
    );

    return;
  }

  /*=========================================
    TODO CORRECTO
    =========================================*/

  enviarPassword();
}

/*======================================================
=            MENSAJES
======================================================*/

function mostrarMensajePassword(mensaje, tipo = "success") {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: tipo,

      title: mensaje,

      confirmButtonText: "Aceptar",
    });

    return;
  }

  alert(mensaje);
}

/*======================================================
=            ENVIAR PASSWORD POR AJAX
======================================================*/

async function enviarPassword() {
  const passwordActual = document.getElementById("passwordActual").value.trim();

  const passwordNueva = document.getElementById("passwordNueva").value.trim();

  const boton = document.getElementById("btnActualizarPassword");

  const textoOriginal = boton.innerHTML;

  /*=========================================
    LOADING
    =========================================*/

  boton.disabled = true;

  boton.innerHTML = `
      <span class="spinner-border spinner-border-sm me-2"></span>
      Actualizando...
  `;

  /*=========================================
    DATOS
    =========================================*/

  const datos = new FormData();

  datos.append("password_actual", passwordActual);

  datos.append("password_nueva", passwordNueva);

  try {
    /*=========================================
      AJAX
      =========================================*/

    const respuesta = await fetch("ajax/actualizar_password.php", {
      method: "POST",

      body: datos,
    });

    const json = await respuesta.json();

    /*=========================================
      ÉXITO
      =========================================*/

    if (json.estado === "ok") {
      Swal.fire({
        icon: "success",

        title: json.mensaje,

        confirmButtonText: "Aceptar",
      });

      limpiarPassword();
    } else {
      /*=========================================
      ERROR
      =========================================*/
      Swal.fire({
        icon: "error",

        title: json.mensaje,

        confirmButtonText: "Aceptar",
      });
    }
  } catch (error) {
    console.error(error);

    Swal.fire({
      icon: "error",

      title: "Ocurrió un error al conectar con el servidor.",

      confirmButtonText: "Aceptar",
    });
  } finally {
    /*=========================================
    RESTAURAR BOTÓN
    =========================================*/

    boton.disabled = false;

    boton.innerHTML = textoOriginal;
  }
}
/*======================================================
=            PREFERENCIAS DEL CLIENTE
======================================================*/

document.addEventListener("DOMContentLoaded", iniciarPreferenciasCliente);
document.addEventListener("DOMContentLoaded", async () => {
  await cargarIdiomas();

  await cargarMonedas();

  await obtenerMetodosPago();

  await cargarPreferencias();

  inicializarBotonesPreferencias();
});

/*======================================================
=            INICIAR
======================================================*/

function iniciarPreferenciasCliente() {
  const formulario = document.getElementById("formPreferencias");

  const btnGuardar = document.getElementById("btnGuardarPreferencias");

  const btnRestablecer = document.getElementById("btnRestablecerPreferencias");

  const btnCerrarSesiones = document.getElementById("btnCerrarSesiones");

  const btnEliminarCuenta = document.getElementById("btnEliminarCuenta");

  if (!formulario || !btnGuardar || !btnRestablecer) {
    return;
  }

  btnGuardar.addEventListener("click", guardarPreferencias);

  if (btnRestablecer) {
    btnRestablecer.addEventListener("click", restablecerPreferencias);
  }

  if (btnCerrarSesiones) {
    btnCerrarSesiones.addEventListener("click", cerrarTodasLasSesiones);
  }

  if (btnEliminarCuenta) {
    btnEliminarCuenta.addEventListener("click", eliminarCuentaCliente);
  }
}
/*======================================================
=            CARGAR PREFERENCIAS
======================================================*/

async function cargarPreferencias() {
  try {
    const respuesta = await fetch("ajax/obtener_preferencias.php");

    const json = await respuesta.json();

    if (json.estado !== "ok") {
      return;
    }

    const p = json.preferencias;

    document.getElementById("correoPromociones").checked =
      p.correo_promociones == 1;

    document.getElementById("estadoPedido").checked = p.estado_pedido == 1;

    document.getElementById("nuevosProductos").checked =
      p.nuevos_productos == 1;

    document.getElementById("ofertasFlash").checked = p.ofertas_flash == 1;

    if (p.id_idiomas) {
      document.getElementById("idioma").value = p.id_idiomas;
    } else {
      document.getElementById("idioma").value = "";
    }

    if (p.id_moneda) {
      document.getElementById("moneda").value = p.id_moneda;
    } else {
      document.getElementById("moneda").value = "";
    }
    if (p.id_metodo_pago) {
      document.getElementById("metodoPago").value = p.id_metodo_pago;
    } else {
      document.getElementById("metodoPago").value = "";
    }
  } catch (error) {
    console.error(error);
  }
}
/*======================================================
=            GUARDAR PREFERENCIAS
======================================================*/

async function guardarPreferencias() {
  const datos = new FormData();

  datos.append(
    "correo_promociones",
    document.getElementById("correoPromociones").checked ? 1 : 0,
  );

  datos.append(
    "estado_pedido",
    document.getElementById("estadoPedido").checked ? 1 : 0,
  );

  datos.append(
    "nuevos_productos",
    document.getElementById("nuevosProductos").checked ? 1 : 0,
  );

  datos.append(
    "ofertas_flash",
    document.getElementById("ofertasFlash").checked ? 1 : 0,
  );

  datos.append("id_idiomas", document.getElementById("idioma").value);

  datos.append("id_moneda", document.getElementById("moneda").value);
  const idMetodoPago = document.getElementById("metodoPago").value;
  datos.append("id_metodo_pago", idMetodoPago);

  try {
    const respuesta = await fetch(
      "ajax/guardar_preferencias.php",

      {
        method: "POST",
        body: datos,
      },
    );

    const json = await respuesta.json();

    if (json.estado === "ok") {
      Swal.fire({
        icon: "success",

        title: "Éxito",

        text: json.mensaje,

        timer: 1800,

        showConfirmButton: false,
      });
    } else {
      Swal.fire({
        icon: "error",

        title: "Error",

        text: json.mensaje,
      });
    }
  } catch (error) {
    console.error(error);
  }
}
/*======================================================
=            CARGAR IDIOMAS
======================================================*/

async function cargarIdiomas() {
  try {
    const respuesta = await fetch("ajax/obtener_idiomas.php");

    const json = await respuesta.json();

    if (json.estado !== "ok") {
      return;
    }

    const select = document.getElementById("idioma");

    select.innerHTML = "";

    json.idiomas.forEach((idioma) => {
      select.innerHTML += `

            <option value="${idioma.id}">

                ${idioma.nombre}

            </option>

            `;
    });
  } catch (error) {
    console.error(error);
  }
}
/*======================================================
=            OBTENER MÉTODOS DE PAGO
======================================================*/

async function obtenerMetodosPago() {
  try {
    const respuesta = await fetch("ajax/obtener_metodos_pago.php");

    const json = await respuesta.json();

    if (json.estado !== "ok") {
      return;
    }

    const select = document.getElementById("metodoPago");

    if (!select) {
      return;
    }

    let html = `

            <option value="">

                Seleccionar...

            </option>

        `;

    json.metodos.forEach((metodo) => {
      html += `

                <option value="${metodo.id}">

                    ${metodo.nombre}

                </option>

            `;
    });

    select.innerHTML = html;
  } catch (error) {
    console.error(error);
  }
}
/*======================================================
=            CARGAR MONEDAS
======================================================*/

async function cargarMonedas() {
  try {
    const respuesta = await fetch("ajax/obtener_monedas.php");

    const json = await respuesta.json();

    if (json.estado !== "ok") {
      return;
    }

    const select = document.getElementById("moneda");

    select.innerHTML = "";

    json.monedas.forEach((moneda) => {
      select.innerHTML += `

            <option value="${moneda.id}">

                ${moneda.nombre}

            </option>

            `;
    });
  } catch (error) {
    console.error(error);
  }
}
/*======================================================
=            BOTONES
======================================================*/

function inicializarBotonesPreferencias() {
  const btnGuardar = document.getElementById("btnGuardarPreferencias");

  const btnRestablecer = document.getElementById("btnRestablecerPreferencias");

  if (btnGuardar) {
    btnGuardar.addEventListener("click", guardarPreferencias);
  }

  if (btnRestablecer) {
    btnRestablecer.addEventListener("click", restablecerPreferencias);
  }
}
/*======================================================
=            RESTABLECER PREFERENCIAS
======================================================*/

async function restablecerPreferencias() {
  const confirmar = await Swal.fire({
    title: "¿Restablecer preferencias?",

    text: "Se restaurarán los valores predeterminados.",

    icon: "question",

    showCancelButton: true,

    confirmButtonText: "Sí",

    cancelButtonText: "Cancelar",
  });

  if (!confirmar.isConfirmed) {
    return;
  }

  try {
    const respuesta = await fetch(
      "ajax/restablecer_preferencias.php",

      {
        method: "POST",
      },
    );

    const json = await respuesta.json();

    if (json.estado === "ok") {
      Swal.fire({
        icon: "success",

        title: "Correcto",

        text: json.mensaje,

        timer: 1800,

        showConfirmButton: false,
      });

      cargarPreferencias();
    }
  } catch (error) {
    console.error(error);
  }
}
