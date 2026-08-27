//=====================================================
// CoDevPro Technology
// Archivo: js/adm_mi_empresa.js
// Módulo: Mi Empresa
// Sistema: Inventa
//=====================================================

"use strict";

//=====================================================
// VARIABLES GLOBALES
//=====================================================

let idUserEmpresa = 0;

let datosEmpresaOriginales = null;

let logoEmpresaOriginal = null;

let archivoLogoSeleccionado = null;

let cargandoEmpresa = false;

let guardandoEmpresa = false;

let guardandoPassword = false;

let procesandoLogo = false;

//=====================================================
// CONSTANTES
//=====================================================

const MAX_TAMANO_LOGO = 2 * 1024 * 1024;

const TIPOS_LOGO_PERMITIDOS = ["image/jpeg", "image/png", "image/webp"];

const EXTENSIONES_LOGO_PERMITIDAS = ["jpg", "jpeg", "png", "webp"];

//=====================================================
// INICIALIZACIÓN
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarMiEmpresa();
});

//=====================================================
// INICIALIZAR MÓDULO
//=====================================================

function inicializarMiEmpresa() {
  const inputId = document.getElementById("idUserEmpresa");

  if (!inputId) {
    console.error("No se encontró el elemento #idUserEmpresa.");

    return;
  }

  idUserEmpresa = parseInt(inputId.value, 10);

  if (!idUserEmpresa || idUserEmpresa <= 0) {
    console.error("ID de usuario inválido.");

    return;
  }

  configurarEventosEmpresa();

  cargarDatosEmpresa();
}

//=====================================================
// CONFIGURAR EVENTOS
//=====================================================

function configurarEventosEmpresa() {
  //=================================================
  // FORMULARIO EMPRESA
  //=================================================

  const formularioEmpresa = document.getElementById("formMiEmpresa");

  if (formularioEmpresa) {
    formularioEmpresa.addEventListener("submit", guardarDatosEmpresa);
  }

  //=================================================
  // BOTÓN RESTABLECER
  //=================================================

  const btnCancelarCambios = document.getElementById("btnCancelarCambios");

  if (btnCancelarCambios) {
    btnCancelarCambios.addEventListener("click", function () {
      restablecerDatosEmpresa();
    });
  }

  //=================================================
  // BOTÓN SELECCIONAR LOGO
  //=================================================

  const btnSeleccionarLogo = document.getElementById("btnSeleccionarLogo");

  const inputLogo = document.getElementById("inputLogoEmpresa");

  if (btnSeleccionarLogo && inputLogo) {
    btnSeleccionarLogo.addEventListener("click", function () {
      inputLogo.click();
    });

    inputLogo.addEventListener("change", manejarSeleccionLogo);
  }

  //=================================================
  // BOTÓN ELIMINAR LOGO
  //=================================================

  const btnEliminarLogo = document.getElementById("btnEliminarLogo");

  if (btnEliminarLogo) {
    btnEliminarLogo.addEventListener("click", abrirModalEliminarLogo);
  }

  //=================================================
  // CONFIRMAR ELIMINACIÓN LOGO
  //=================================================

  const btnConfirmarEliminarLogo = document.getElementById(
    "btnConfirmarEliminarLogo",
  );

  if (btnConfirmarEliminarLogo) {
    btnConfirmarEliminarLogo.addEventListener("click", eliminarLogoEmpresa);
  }

  //=================================================
  // CAMBIAR CONTRASEÑA
  //=================================================

  const btnCambiarPassword = document.getElementById("btnCambiarPassword");

  if (btnCambiarPassword) {
    btnCambiarPassword.addEventListener("click", abrirModalCambiarPassword);
  }

  //=================================================
  // FORMULARIO PASSWORD
  //=================================================

  const formularioPassword = document.getElementById("formCambiarPassword");

  if (formularioPassword) {
    formularioPassword.addEventListener("submit", cambiarPasswordEmpresa);
  }

  //=================================================
  // MOSTRAR / OCULTAR PASSWORD
  //=================================================

  const botonesPassword = document.querySelectorAll(".btn-toggle-password");

  botonesPassword.forEach(function (boton) {
    boton.addEventListener("click", togglePassword);
  });

  //=================================================
  // LIMPIAR PASSWORD AL CERRAR MODAL
  //=================================================

  const modalPassword = document.getElementById("modalCambiarPassword");

  if (modalPassword) {
    modalPassword.addEventListener(
      "hidden.bs.modal",
      limpiarFormularioPassword,
    );
  }

  //=================================================
  // LIMPIAR INPUT LOGO
  //=================================================

  if (inputLogo) {
    inputLogo.addEventListener("click", function () {
      this.value = "";
    });
  }
}

//=====================================================
// CARGAR DATOS DE LA EMPRESA
//=====================================================

async function cargarDatosEmpresa() {
  if (cargandoEmpresa) {
    return;
  }

  cargandoEmpresa = true;

  mostrarEstadoCargaEmpresa(true);

  try {
    const parametros = new URLSearchParams();

    parametros.append("id_user", idUserEmpresa);

    const respuesta = await fetch("ajax/obtener_mi_empresa.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: parametros.toString(),
    });

    const datos = await obtenerRespuestaJSON(respuesta);

    if (!datos.success) {
      throw new Error(
        datos.message || "No se pudieron obtener los datos de la empresa.",
      );
    }

    datosEmpresaOriginales = {
      nombreEmpresa: datos.data?.nombreEmpresa || "",

      ruc: datos.data?.ruc || "",

      email: datos.data?.email || "",

      celular: datos.data?.celular || "",

      direccion: datos.data?.direccion || "",

      username: datos.data?.username || "",

      rol: datos.data?.rol || "",

      fechaRegistro: datos.data?.fechaRegistro || "",

      estado: datos.data?.estado || "ACTIVO",

      tieneLogo: Boolean(datos.data?.tieneLogo),

      logo: datos.data?.logo || null,
    };

    aplicarDatosEmpresa(datosEmpresaOriginales);
  } catch (error) {
    console.error("Error al cargar empresa:", error);

    mostrarAlerta(
      "error",
      "Error",
      error.message || "No se pudieron cargar los datos de la empresa.",
    );
  } finally {
    cargandoEmpresa = false;

    mostrarEstadoCargaEmpresa(false);
  }
}

//=====================================================
// OBTENER RESPUESTA JSON
//=====================================================

async function obtenerRespuestaJSON(respuesta) {
  const texto = await respuesta.text();

  if (!respuesta.ok) {
    throw new Error("Error HTTP " + respuesta.status + ". " + texto);
  }

  if (!texto || !texto.trim()) {
    throw new Error("El servidor no devolvió ninguna respuesta.");
  }

  try {
    return JSON.parse(texto);
  } catch (error) {
    console.error("Respuesta recibida del servidor:", texto);

    throw new Error(
      "El servidor devolvió una respuesta inválida. " +
        "Revisa ajax/eliminar_logo_empresa.php.",
    );
  }
}

//=====================================================
// APLICAR DATOS EMPRESA
//=====================================================

function aplicarDatosEmpresa(datos) {
  //=================================================
  // INFORMACIÓN GENERAL
  //=================================================

  establecerValor("nombreEmpresa", datos.nombreEmpresa);

  establecerValor("ruc", datos.ruc);

  establecerValor("emailEmpresa", datos.email);

  establecerValor("celularEmpresa", datos.celular);

  establecerValor("direccionEmpresa", datos.direccion);

  //=================================================
  // INFORMACIÓN ACCESO
  //=================================================

  establecerTexto("empresaUsername", datos.username || "--");

  establecerTexto("empresaRol", datos.rol || "--");

  establecerTexto("empresaFechaRegistro", formatearFecha(datos.fechaRegistro));

  //=================================================
  // ESTADO
  //=================================================

  actualizarEstadoEmpresa(datos.estado);

  //=================================================
  // LOGO
  //=================================================

  if (datos.tieneLogo && datos.logo) {
    mostrarLogoEmpresa(datos.logo);

    logoEmpresaOriginal = datos.logo;
  } else {
    mostrarLogoPlaceholder();

    logoEmpresaOriginal = null;
  }

  archivoLogoSeleccionado = null;
}

//=====================================================
// ESTABLECER VALOR INPUT
//=====================================================

function establecerValor(id, valor) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  elemento.value = valor !== null && valor !== undefined ? valor : "";
}

//=====================================================
// ESTABLECER TEXTO
//=====================================================

function establecerTexto(id, texto) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  elemento.textContent = texto !== null && texto !== undefined ? texto : "";
}

//=====================================================
// ACTUALIZAR ESTADO EMPRESA
//=====================================================

function actualizarEstadoEmpresa(estado) {
  const estadoNormalizado = String(estado || "ACTIVO").toUpperCase();

  const activo = estadoNormalizado === "ACTIVO";

  establecerTexto("empresaEstadoTexto", activo ? "Activo" : "Inactivo");

  establecerTexto("empresaEstado", activo ? "Activo" : "Inactivo");

  const puntoEstado = document.querySelector(".mi-empresa-status .status-dot");

  if (puntoEstado) {
    puntoEstado.classList.toggle("inactive", !activo);
  }

  const estadoContenedor = document.querySelector(".empresa-account-status");

  if (estadoContenedor) {
    estadoContenedor.classList.toggle("inactive", !activo);
  }

  const iconoEstado = document.querySelector(".account-status-icon i");

  if (iconoEstado) {
    iconoEstado.className = activo ? "bi bi-check-lg" : "bi bi-x-lg";
  }
}

//=====================================================
// FORMATEAR FECHA
//=====================================================

function formatearFecha(fecha) {
  if (!fecha) {
    return "--";
  }

  const fechaTexto = String(fecha).trim();

  //=================================================
  // YYYY-MM-DD
  //=================================================

  if (/^\d{4}-\d{2}-\d{2}$/.test(fechaTexto)) {
    const partes = fechaTexto.split("-");

    return partes[2] + "/" + partes[1] + "/" + partes[0];
  }

  //=================================================
  // DATETIME
  //=================================================

  const fechaObjeto = new Date(fechaTexto);

  if (isNaN(fechaObjeto.getTime())) {
    return fechaTexto;
  }

  return fechaObjeto.toLocaleDateString("es-PE", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

//=====================================================
// GUARDAR DATOS EMPRESA
//=====================================================

async function guardarDatosEmpresa(evento) {
  evento.preventDefault();

  if (guardandoEmpresa) {
    return;
  }

  const nombreEmpresa = obtenerValor("nombreEmpresa").trim();

  const ruc = obtenerValor("ruc").trim();

  const email = obtenerValor("emailEmpresa").trim();

  const celular = obtenerValor("celularEmpresa").trim();

  const direccion = obtenerValor("direccionEmpresa").trim();

  //=================================================
  // VALIDAR NOMBRE
  //=================================================

  if (!nombreEmpresa) {
    mostrarAlerta(
      "warning",
      "Campo obligatorio",
      "Debes ingresar el nombre de la empresa.",
    );

    enfocarElemento("nombreEmpresa");

    return;
  }

  //=================================================
  // VALIDAR EMAIL
  //=================================================

  if (!email) {
    mostrarAlerta(
      "warning",
      "Campo obligatorio",
      "Debes ingresar el correo electrónico.",
    );

    enfocarElemento("emailEmpresa");

    return;
  }

  if (!validarEmail(email)) {
    mostrarAlerta(
      "warning",
      "Correo inválido",
      "Ingresa un correo electrónico válido.",
    );

    enfocarElemento("emailEmpresa");

    return;
  }

  //=================================================
  // ACTIVAR CARGA
  //=================================================

  guardandoEmpresa = true;

  cambiarEstadoBoton("btnGuardarEmpresa", true, "Guardando...");

  try {
    const parametros = new URLSearchParams();

    parametros.append("id_user", idUserEmpresa);

    parametros.append("nombreEmpresa", nombreEmpresa);

    parametros.append("ruc", ruc);

    parametros.append("email", email);

    parametros.append("celular", celular);

    parametros.append("direccion", direccion);

    const respuesta = await fetch("ajax/actualizar_mi_empresa.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: parametros.toString(),
    });

    const datos = await obtenerRespuestaJSON(respuesta);

    if (!datos.success) {
      throw new Error(datos.message || "No se pudieron guardar los cambios.");
    }

    //=================================================
    // ACTUALIZAR DATOS ORIGINALES
    //=================================================

    if (!datosEmpresaOriginales) {
      datosEmpresaOriginales = {};
    }

    datosEmpresaOriginales.nombreEmpresa = nombreEmpresa;

    datosEmpresaOriginales.ruc = ruc;

    datosEmpresaOriginales.email = email;

    datosEmpresaOriginales.celular = celular;

    datosEmpresaOriginales.direccion = direccion;

    //=================================================
    // ACTUALIZAR NOMBRE DEL LOGO
    //=================================================

    establecerTexto("empresaNombreLogo", nombreEmpresa || "Mi Empresa");

    mostrarAlerta(
      "success",
      "Cambios guardados",
      datos.message ||
        "La información de la empresa se actualizó correctamente.",
    );
  } catch (error) {
    console.error("Error al guardar empresa:", error);

    mostrarAlerta(
      "error",
      "No se pudo guardar",
      error.message || "Ocurrió un error al guardar los cambios.",
    );
  } finally {
    guardandoEmpresa = false;

    cambiarEstadoBoton("btnGuardarEmpresa", false, "Guardar cambios");
  }
}

//=====================================================
// RESTABLECER DATOS
//=====================================================

function restablecerDatosEmpresa() {
  if (!datosEmpresaOriginales) {
    cargarDatosEmpresa();

    return;
  }

  establecerValor("nombreEmpresa", datosEmpresaOriginales.nombreEmpresa);

  establecerValor("ruc", datosEmpresaOriginales.ruc);

  establecerValor("emailEmpresa", datosEmpresaOriginales.email);

  establecerValor("celularEmpresa", datosEmpresaOriginales.celular);

  establecerValor("direccionEmpresa", datosEmpresaOriginales.direccion);

  archivoLogoSeleccionado = null;

  const inputLogo = document.getElementById("inputLogoEmpresa");

  if (inputLogo) {
    inputLogo.value = "";
  }

  if (datosEmpresaOriginales.tieneLogo && datosEmpresaOriginales.logo) {
    mostrarLogoEmpresa(datosEmpresaOriginales.logo);
  } else {
    mostrarLogoPlaceholder();
  }

  mostrarAlerta(
    "info",
    "Datos restablecidos",
    "Se restauraron los últimos datos guardados.",
  );
}

//=====================================================
// SELECCIONAR LOGO
//=====================================================

function manejarSeleccionLogo(evento) {
  const archivo = evento.target.files && evento.target.files[0];

  if (!archivo) {
    return;
  }

  //=================================================
  // VALIDAR MIME
  //=================================================

  if (!TIPOS_LOGO_PERMITIDOS.includes(archivo.type)) {
    mostrarAlerta(
      "warning",
      "Formato no permitido",
      "Solo puedes utilizar imágenes JPG, JPEG, PNG o WEBP.",
    );

    evento.target.value = "";

    return;
  }

  //=================================================
  // VALIDAR EXTENSIÓN
  //=================================================

  const nombreArchivo = archivo.name.toLowerCase();

  const extension = nombreArchivo.split(".").pop();

  if (!EXTENSIONES_LOGO_PERMITIDAS.includes(extension)) {
    mostrarAlerta(
      "warning",
      "Extensión no permitida",
      "El archivo debe ser JPG, JPEG, PNG o WEBP.",
    );

    evento.target.value = "";

    return;
  }

  //=================================================
  // VALIDAR TAMAÑO
  //=================================================

  if (archivo.size > MAX_TAMANO_LOGO) {
    mostrarAlerta(
      "warning",
      "Archivo demasiado grande",
      "El logo no puede superar los 2 MB.",
    );

    evento.target.value = "";

    return;
  }

  archivoLogoSeleccionado = archivo;

  //=================================================
  // PREVISUALIZAR
  //=================================================

  const lector = new FileReader();

  lector.onload = function (e) {
    mostrarLogoEmpresa(e.target.result);
  };

  lector.onerror = function () {
    mostrarAlerta("error", "Error", "No se pudo leer la imagen seleccionada.");
  };

  lector.readAsDataURL(archivo);

  //=================================================
  // SUBIR AUTOMÁTICAMENTE
  //=================================================

  subirLogoEmpresa(archivo);
}

//=====================================================
// MOSTRAR LOGO
//=====================================================

function mostrarLogoEmpresa(src) {
  const contenedor = document.getElementById("empresaLogoPreview");

  if (!contenedor) {
    return;
  }

  if (!src) {
    mostrarLogoPlaceholder();

    return;
  }

  contenedor.innerHTML = "";

  const imagen = document.createElement("img");

  imagen.src = src;

  imagen.alt = "Logo de la empresa";

  imagen.className = "empresa-logo-image";

  contenedor.appendChild(imagen);
}

//=====================================================
// MOSTRAR PLACEHOLDER
//=====================================================

function mostrarLogoPlaceholder() {
  const contenedor = document.getElementById("empresaLogoPreview");

  if (!contenedor) {
    return;
  }

  contenedor.innerHTML = `
        <div class="logo-placeholder">
            <i class="bi bi-buildings"></i>
            <span>
                Sin logo
            </span>
        </div>
    `;
}

//=====================================================
// SUBIR LOGO
//=====================================================

async function subirLogoEmpresa(archivo) {
  if (procesandoLogo) {
    return;
  }

  if (!archivo) {
    return;
  }

  procesandoLogo = true;

  try {
    const formulario = new FormData();

    formulario.append("id_user", idUserEmpresa);

    formulario.append("logo", archivo);

    const respuesta = await fetch("ajax/actualizar_logo_empresa.php", {
      method: "POST",
      body: formulario,
    });

    const datos = await obtenerRespuestaJSON(respuesta);

    if (!datos.success) {
      throw new Error(datos.message || "No se pudo actualizar el logo.");
    }

    //=================================================
    // OBTENER LOGO DEVUELTO POR PHP
    //=================================================

    let logoGuardado = datos.logo || null;

    //=================================================
    // SI PHP NO DEVUELVE LOGO, TOMAR EL ACTUAL
    //=================================================

    if (!logoGuardado) {
      const imagen = document.querySelector("#empresaLogoPreview img");

      if (imagen) {
        logoGuardado = imagen.src;
      }
    }

    logoEmpresaOriginal = logoGuardado;

    //=================================================
    // ACTUALIZAR DATOS ORIGINALES
    //=================================================

    if (!datosEmpresaOriginales) {
      datosEmpresaOriginales = {};
    }

    datosEmpresaOriginales.tieneLogo = true;

    datosEmpresaOriginales.logo = logoGuardado;

    archivoLogoSeleccionado = null;

    const inputLogo = document.getElementById("inputLogoEmpresa");

    if (inputLogo) {
      inputLogo.value = "";
    }

    mostrarAlerta(
      "success",
      "Logo actualizado",
      datos.message || "El logo de la empresa se actualizó correctamente.",
    );
  } catch (error) {
    console.error("Error al subir logo:", error);

    //=================================================
    // RESTAURAR LOGO ANTERIOR
    //=================================================

    if (logoEmpresaOriginal) {
      mostrarLogoEmpresa(logoEmpresaOriginal);
    } else {
      mostrarLogoPlaceholder();
    }

    mostrarAlerta(
      "error",
      "No se pudo actualizar",
      error.message || "Ocurrió un error al actualizar el logo.",
    );

    const inputLogo = document.getElementById("inputLogoEmpresa");

    if (inputLogo) {
      inputLogo.value = "";
    }

    archivoLogoSeleccionado = null;
  } finally {
    procesandoLogo = false;
  }
}

//=====================================================
// ABRIR MODAL ELIMINAR LOGO
//=====================================================

function abrirModalEliminarLogo() {
  const modalElemento = document.getElementById("modalEliminarLogo");

  if (!modalElemento) {
    console.error("No se encontró #modalEliminarLogo.");

    return;
  }

  //=================================================
  // VERIFICAR SI EXISTE LOGO
  //=================================================

  if (
    !datosEmpresaOriginales ||
    !datosEmpresaOriginales.tieneLogo ||
    !datosEmpresaOriginales.logo
  ) {
    mostrarAlerta(
      "info",
      "Sin logo",
      "La empresa no tiene un logo registrado.",
    );

    return;
  }

  //=================================================
  // ABRIR MODAL
  //=================================================

  if (typeof bootstrap === "undefined" || !bootstrap.Modal) {
    console.error("Bootstrap Modal no está disponible.");

    return;
  }

  const modal = bootstrap.Modal.getOrCreateInstance(modalElemento);

  modal.show();
}

//=====================================================
// ELIMINAR LOGO
//=====================================================

async function eliminarLogoEmpresa() {
  //=================================================
  // EVITAR DOBLE PROCESO
  //=================================================

  if (procesandoLogo) {
    return;
  }

  //=================================================
  // VALIDAR ID USUARIO
  //=================================================

  if (!idUserEmpresa || idUserEmpresa <= 0) {
    mostrarAlerta(
      "error",
      "Error",
      "No se pudo identificar al usuario de la empresa.",
    );

    return;
  }

  //=================================================
  // VALIDAR QUE EXISTA LOGO
  //=================================================

  if (!datosEmpresaOriginales || !datosEmpresaOriginales.tieneLogo) {
    mostrarAlerta(
      "info",
      "Sin logo",
      "La empresa no tiene un logo registrado.",
    );

    return;
  }

  procesandoLogo = true;

  cambiarEstadoBoton("btnConfirmarEliminarLogo", true, "Eliminando...");

  try {
    //=================================================
    // PARÁMETROS
    //=================================================

    const parametros = new URLSearchParams();

    // IMPORTANTE:
    // eliminar_logo_empresa.php recibe id_user
    parametros.append("id_user", String(idUserEmpresa));

    console.log("Eliminando logo. id_user:", idUserEmpresa);

    //=================================================
    // AJAX
    //=================================================

    const respuesta = await fetch("ajax/eliminar_logo_empresa.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: parametros.toString(),
    });

    //=================================================
    // OBTENER JSON
    //=================================================

    const datos = await obtenerRespuestaJSON(respuesta);

    console.log("Respuesta eliminar logo:", datos);

    //=================================================
    // VALIDAR RESPUESTA
    //=================================================

    if (!datos.success) {
      throw new Error(datos.message || "No se pudo eliminar el logo.");
    }

    //=================================================
    // ACTUALIZAR VISTA
    //=================================================

    mostrarLogoPlaceholder();

    //=================================================
    // ACTUALIZAR VARIABLES
    //=================================================

    logoEmpresaOriginal = null;

    archivoLogoSeleccionado = null;

    if (!datosEmpresaOriginales) {
      datosEmpresaOriginales = {};
    }

    datosEmpresaOriginales.tieneLogo = false;

    datosEmpresaOriginales.logo = null;

    //=================================================
    // LIMPIAR INPUT
    //=================================================

    const inputLogo = document.getElementById("inputLogoEmpresa");

    if (inputLogo) {
      inputLogo.value = "";
    }

    //=================================================
    // CERRAR MODAL
    //=================================================

    const modalElemento = document.getElementById("modalEliminarLogo");

    if (modalElemento) {
      const modal = bootstrap.Modal.getInstance(modalElemento);

      if (modal) {
        modal.hide();
      }
    }

    //=================================================
    // MOSTRAR MENSAJE
    //=================================================

    mostrarAlerta(
      "success",
      "Logo eliminado",
      datos.message || "El logo de la empresa fue eliminado correctamente.",
    );
  } catch (error) {
    console.error("Error al eliminar logo:", error);

    mostrarAlerta(
      "error",
      "No se pudo eliminar",
      error.message || "Ocurrió un error al eliminar el logo.",
    );
  } finally {
    procesandoLogo = false;

    cambiarEstadoBoton("btnConfirmarEliminarLogo", false, "Eliminar");
  }
}

//=====================================================
// ABRIR MODAL CAMBIAR PASSWORD
//=====================================================

function abrirModalCambiarPassword() {
  const modalElemento = document.getElementById("modalCambiarPassword");

  if (!modalElemento) {
    return;
  }

  limpiarFormularioPassword();

  const modal = bootstrap.Modal.getOrCreateInstance(modalElemento);

  modal.show();
}

//=====================================================
// CAMBIAR PASSWORD
//=====================================================

async function cambiarPasswordEmpresa(evento) {
  evento.preventDefault();

  if (guardandoPassword) {
    return;
  }

  const passwordActual = obtenerValor("passwordActual");

  const passwordNueva = obtenerValor("passwordNueva");

  const passwordConfirmar = obtenerValor("passwordConfirmar");

  //=================================================
  // VALIDAR PASSWORD ACTUAL
  //=================================================

  if (!passwordActual) {
    mostrarAlerta(
      "warning",
      "Campo obligatorio",
      "Ingresa tu contraseña actual.",
    );

    enfocarElemento("passwordActual");

    return;
  }

  //=================================================
  // VALIDAR PASSWORD NUEVA
  //=================================================

  if (!passwordNueva) {
    mostrarAlerta(
      "warning",
      "Campo obligatorio",
      "Ingresa la nueva contraseña.",
    );

    enfocarElemento("passwordNueva");

    return;
  }

  if (passwordNueva.length < 8) {
    mostrarAlerta(
      "warning",
      "Contraseña inválida",
      "La nueva contraseña debe tener al menos 8 caracteres.",
    );

    enfocarElemento("passwordNueva");

    return;
  }

  //=================================================
  // VALIDAR CONFIRMACIÓN
  //=================================================

  if (!passwordConfirmar) {
    mostrarAlerta(
      "warning",
      "Campo obligatorio",
      "Confirma la nueva contraseña.",
    );

    enfocarElemento("passwordConfirmar");

    return;
  }

  if (passwordNueva !== passwordConfirmar) {
    mostrarAlerta(
      "warning",
      "Las contraseñas no coinciden",
      "La nueva contraseña y su confirmación deben ser iguales.",
    );

    enfocarElemento("passwordConfirmar");

    return;
  }

  if (passwordActual === passwordNueva) {
    mostrarAlerta(
      "warning",
      "Contraseña inválida",
      "La nueva contraseña debe ser diferente a la contraseña actual.",
    );

    enfocarElemento("passwordNueva");

    return;
  }

  //=================================================
  // GUARDANDO
  //=================================================

  guardandoPassword = true;

  cambiarEstadoBoton("btnGuardarPassword", true, "Actualizando...");

  try {
    const parametros = new URLSearchParams();

    parametros.append("id_user", idUserEmpresa);

    parametros.append("passwordActual", passwordActual);

    parametros.append("passwordNueva", passwordNueva);

    parametros.append("passwordConfirmar", passwordConfirmar);

    const respuesta = await fetch("ajax/cambiar_password_empresa.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      body: parametros.toString(),
    });

    const datos = await obtenerRespuestaJSON(respuesta);

    if (!datos.success) {
      throw new Error(datos.message || "No se pudo actualizar la contraseña.");
    }

    mostrarAlerta(
      "success",
      "Contraseña actualizada",
      datos.message || "La contraseña se actualizó correctamente.",
    );

    limpiarFormularioPassword();

    const modalElemento = document.getElementById("modalCambiarPassword");

    if (modalElemento) {
      const modal = bootstrap.Modal.getInstance(modalElemento);

      if (modal) {
        modal.hide();
      }
    }
  } catch (error) {
    console.error("Error al cambiar contraseña:", error);

    mostrarAlerta(
      "error",
      "No se pudo actualizar",
      error.message || "Ocurrió un error al cambiar la contraseña.",
    );
  } finally {
    guardandoPassword = false;

    cambiarEstadoBoton("btnGuardarPassword", false, "Actualizar contraseña");
  }
}

//=====================================================
// MOSTRAR / OCULTAR PASSWORD
//=====================================================

function togglePassword(evento) {
  const boton = evento.currentTarget;

  const targetId = boton.getAttribute("data-target");

  if (!targetId) {
    return;
  }

  const input = document.getElementById(targetId);

  if (!input) {
    return;
  }

  const icono = boton.querySelector("i");

  if (input.type === "password") {
    input.type = "text";

    if (icono) {
      icono.className = "bi bi-eye-slash";
    }
  } else {
    input.type = "password";

    if (icono) {
      icono.className = "bi bi-eye";
    }
  }
}

//=====================================================
// LIMPIAR FORMULARIO PASSWORD
//=====================================================

function limpiarFormularioPassword() {
  const formulario = document.getElementById("formCambiarPassword");

  if (formulario) {
    formulario.reset();
  }

  const inputs = formulario
    ? formulario.querySelectorAll('input[type="password"]')
    : [];

  inputs.forEach(function (input) {
    input.type = "password";
  });

  const iconos = formulario
    ? formulario.querySelectorAll(".btn-toggle-password i")
    : [];

  iconos.forEach(function (icono) {
    icono.className = "bi bi-eye";
  });
}

//=====================================================
// VALIDAR EMAIL
//=====================================================

function validarEmail(email) {
  const expresion = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  return expresion.test(email);
}

//=====================================================
// OBTENER VALOR
//=====================================================

function obtenerValor(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return "";
  }

  return elemento.value || "";
}

//=====================================================
// ENFOCAR ELEMENTO
//=====================================================

function enfocarElemento(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  setTimeout(function () {
    elemento.focus();
  }, 100);
}

//=====================================================
// CAMBIAR ESTADO BOTÓN
//=====================================================

function cambiarEstadoBoton(id, cargando, texto) {
  const boton = document.getElementById(id);

  if (!boton) {
    return;
  }

  if (cargando) {
    if (!boton.dataset.textoOriginal) {
      boton.dataset.textoOriginal = boton.innerHTML;
    }

    boton.disabled = true;

    boton.innerHTML = `
            <span
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true">
            </span>
            ${texto}
        `;
  } else {
    boton.disabled = false;

    if (boton.dataset.textoOriginal) {
      boton.innerHTML = boton.dataset.textoOriginal;

      delete boton.dataset.textoOriginal;
    }
  }
}

//=====================================================
// MOSTRAR ESTADO DE CARGA
//=====================================================

function mostrarEstadoCargaEmpresa(cargando) {
  const formulario = document.getElementById("formMiEmpresa");

  if (!formulario) {
    return;
  }

  const campos = formulario.querySelectorAll("input, textarea, select");

  campos.forEach(function (campo) {
    campo.disabled = cargando;
  });

  const botonGuardar = document.getElementById("btnGuardarEmpresa");

  if (botonGuardar) {
    botonGuardar.disabled = cargando;
  }
}

//=====================================================
// ALERTA SWEETALERT
//=====================================================

function mostrarAlerta(icono, titulo, texto) {
  if (typeof Swal === "undefined") {
    alert(titulo + "\n\n" + texto);

    return;
  }

  Swal.fire({
    icon: icono,

    title: titulo,

    text: texto,

    confirmButtonText: "Aceptar",

    confirmButtonColor: "#0d6efd",

    customClass: {
      popup: "shadow-lg",
    },
  });
}
