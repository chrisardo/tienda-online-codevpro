// =====================================================
// CoDevPro Technology
// Archivo: js/adm_monedas_impuestos.js
// Módulo: Monedas e Impuestos
// =====================================================

"use strict";

// =====================================================
// CONFIGURACIÓN
// =====================================================

const URL_OBTENER = "ajax/obtener_monedas_impuestos.php";
const URL_GUARDAR = "ajax/guardar_monedas_impuestos.php";

// =====================================================
// ESTADO ORIGINAL
// =====================================================

let configuracionOriginal = null;

// =====================================================
// DOCUMENT READY
// =====================================================

document.addEventListener("DOMContentLoaded", function () {
  inicializarModulo();
});

// =====================================================
// INICIALIZAR MÓDULO
// =====================================================

function inicializarModulo() {
  configurarEventos();

  cargarConfiguracion();
}

// =====================================================
// CONFIGURAR EVENTOS
// =====================================================

function configurarEventos() {
  // ---------------------------------------------
  // CAMPOS DE MONEDA
  // ---------------------------------------------

  const camposMoneda = [
    "nombre_moneda",
    "codigo_moneda",
    "simbolo_moneda",
    "decimales",
    "posicion_simbolo",
    "separador_decimal",
    "separador_miles",
  ];

  camposMoneda.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (!elemento) {
      return;
    }

    elemento.addEventListener("input", actualizarVistaPrevia);
    elemento.addEventListener("change", actualizarVistaPrevia);
  });

  // ---------------------------------------------
  // CAMPOS DE IMPUESTO
  // ---------------------------------------------

  const camposImpuesto = [
    "impuesto_activo",
    "nombre_impuesto",
    "porcentaje_impuesto",
    "precios_incluyen_impuesto",
  ];

  camposImpuesto.forEach(function (id) {
    const elemento = document.getElementById(id);

    if (!elemento) {
      return;
    }

    elemento.addEventListener("input", function () {
      actualizarEstadoCamposImpuesto();
      actualizarVistaPrevia();
    });

    elemento.addEventListener("change", function () {
      actualizarEstadoCamposImpuesto();
      actualizarVistaPrevia();
    });
  });

  // ---------------------------------------------
  // CÓDIGO DE MONEDA
  // ---------------------------------------------

  const codigoMoneda = document.getElementById("codigo_moneda");

  if (codigoMoneda) {
    codigoMoneda.addEventListener("input", function () {
      this.value = this.value
        .toUpperCase()
        .replace(/\s+/g, "")
        .substring(0, 10);

      actualizarVistaPrevia();
    });
  }

  // ---------------------------------------------
  // PORCENTAJE
  // ---------------------------------------------

  const porcentaje = document.getElementById("porcentaje_impuesto");

  if (porcentaje) {
    porcentaje.addEventListener("input", function () {
      let valor = parseFloat(this.value);

      if (isNaN(valor)) {
        return;
      }

      if (valor < 0) {
        this.value = "0";
      }

      if (valor > 100) {
        this.value = "100";
      }

      actualizarVistaPrevia();
    });
  }

  // ---------------------------------------------
  // BOTÓN GUARDAR
  // ---------------------------------------------

  const btnGuardar = document.getElementById("btnGuardarConfiguracion");

  if (btnGuardar) {
    btnGuardar.addEventListener("click", guardarConfiguracion);
  }

  // ---------------------------------------------
  // BOTÓN RESTABLECER
  // ---------------------------------------------

  const btnRestablecer = document.getElementById("btnRestablecer");

  if (btnRestablecer) {
    btnRestablecer.addEventListener("click", restablecerConfiguracion);
  }
}

// =====================================================
// CARGAR CONFIGURACIÓN
// =====================================================

async function cargarConfiguracion() {
  mostrarEstadoCarga(true);

  try {
    const respuesta = await fetch(URL_OBTENER, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      cache: "no-store",
    });

    if (!respuesta.ok) {
      throw new Error("No se pudo conectar con el servidor.");
    }

    const resultado = await respuesta.json();

    if (!resultado || typeof resultado !== "object") {
      throw new Error("La respuesta del servidor no es válida.");
    }

    if (resultado.success === false) {
      throw new Error(
        resultado.message || "No se pudo obtener la configuración.",
      );
    }

    // ---------------------------------------------
    // SI EXISTE CONFIGURACIÓN
    // ---------------------------------------------

    if (
      resultado.configuracion &&
      typeof resultado.configuracion === "object"
    ) {
      aplicarConfiguracion(resultado.configuracion);
    } else if (resultado.data && typeof resultado.data === "object") {
      aplicarConfiguracion(resultado.data);
    } else {
      // -----------------------------------------
      // CONFIGURACIÓN POR DEFECTO
      // -----------------------------------------

      aplicarConfiguracion(obtenerValoresPorDefecto());
    }

    // ---------------------------------------------
    // GUARDAR ESTADO ORIGINAL
    // ---------------------------------------------

    configuracionOriginal = obtenerConfiguracionFormulario();

    actualizarEstadoCamposImpuesto();

    actualizarVistaPrevia();
  } catch (error) {
    console.error("Error cargando configuración:", error);

    // ---------------------------------------------
    // CARGAR VALORES POR DEFECTO
    // ---------------------------------------------

    aplicarConfiguracion(obtenerValoresPorDefecto());

    configuracionOriginal = obtenerConfiguracionFormulario();

    actualizarEstadoCamposImpuesto();

    actualizarVistaPrevia();

    mostrarAlerta(
      "warning",
      "Configuración",
      "No se pudo cargar la configuración guardada. " +
        "Se utilizarán valores predeterminados.",
    );
  } finally {
    mostrarEstadoCarga(false);
  }
}

// =====================================================
// VALORES POR DEFECTO
// =====================================================

function obtenerValoresPorDefecto() {
  return {
    nombre_moneda: "Sol peruano",

    codigo_moneda: "PEN",

    simbolo_moneda: "S/",

    decimales: 2,

    separador_decimal: ".",

    separador_miles: ",",

    posicion_simbolo: "ANTES",

    impuesto_activo: 1,

    nombre_impuesto: "IGV",

    porcentaje_impuesto: 18,

    precios_incluyen_impuesto: 0,
  };
}

// =====================================================
// APLICAR CONFIGURACIÓN
// =====================================================

function aplicarConfiguracion(config) {
  establecerValor("nombre_moneda", config.nombre_moneda);

  establecerValor("codigo_moneda", config.codigo_moneda);

  establecerValor("simbolo_moneda", config.simbolo_moneda);

  establecerValor("decimales", config.decimales);

  establecerValor("separador_decimal", config.separador_decimal);

  establecerValor("separador_miles", config.separador_miles);

  establecerValor("posicion_simbolo", config.posicion_simbolo);

  establecerCheckbox(
    "impuesto_activo",
    convertirBooleano(config.impuesto_activo),
  );

  establecerValor("nombre_impuesto", config.nombre_impuesto);

  establecerValor("porcentaje_impuesto", config.porcentaje_impuesto);

  establecerCheckbox(
    "precios_incluyen_impuesto",
    convertirBooleano(config.precios_incluyen_impuesto),
  );
}

// =====================================================
// ESTABLECER VALOR
// =====================================================

function establecerValor(id, valor) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  if (valor === null || valor === undefined) {
    elemento.value = "";

    return;
  }

  elemento.value = valor;
}

// =====================================================
// ESTABLECER CHECKBOX
// =====================================================

function establecerCheckbox(id, valor) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return;
  }

  elemento.checked = Boolean(valor);
}

// =====================================================
// OBTENER CONFIGURACIÓN DEL FORMULARIO
// =====================================================

function obtenerConfiguracionFormulario() {
  return {
    nombre_moneda: obtenerValor("nombre_moneda"),

    codigo_moneda: obtenerValor("codigo_moneda").toUpperCase(),

    simbolo_moneda: obtenerValor("simbolo_moneda"),

    decimales: parseInt(obtenerValor("decimales"), 10) || 0,

    separador_decimal: obtenerValor("separador_decimal"),

    separador_miles: obtenerValor("separador_miles"),

    posicion_simbolo: obtenerValor("posicion_simbolo"),

    impuesto_activo: obtenerCheckbox("impuesto_activo") ? 1 : 0,

    nombre_impuesto: obtenerValor("nombre_impuesto"),

    porcentaje_impuesto: obtenerPorcentaje(),

    precios_incluyen_impuesto: obtenerCheckbox("precios_incluyen_impuesto")
      ? 1
      : 0,
  };
}

// =====================================================
// OBTENER VALOR
// =====================================================

function obtenerValor(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return "";
  }

  return elemento.value.trim();
}

// =====================================================
// OBTENER CHECKBOX
// =====================================================

function obtenerCheckbox(id) {
  const elemento = document.getElementById(id);

  if (!elemento) {
    return false;
  }

  return elemento.checked;
}

// =====================================================
// OBTENER PORCENTAJE
// =====================================================

function obtenerPorcentaje() {
  const elemento = document.getElementById("porcentaje_impuesto");

  if (!elemento) {
    return 0;
  }

  const valor = parseFloat(elemento.value);

  if (isNaN(valor)) {
    return 0;
  }

  return Math.round(valor * 100) / 100;
}

// =====================================================
// VALIDAR CONFIGURACIÓN
// =====================================================

function validarConfiguracion(config) {
  const errores = [];

  // ---------------------------------------------
  // NOMBRE MONEDA
  // ---------------------------------------------

  if (!config.nombre_moneda) {
    errores.push("Debes ingresar el nombre de la moneda.");
  }

  if (config.nombre_moneda.length > 100) {
    errores.push(
      "El nombre de la moneda no puede superar " + "los 100 caracteres.",
    );
  }

  // ---------------------------------------------
  // CÓDIGO
  // ---------------------------------------------

  if (!config.codigo_moneda) {
    errores.push("Debes ingresar el código de la moneda.");
  } else if (!/^[A-Z0-9]{1,10}$/.test(config.codigo_moneda)) {
    errores.push(
      "El código de moneda solo puede contener " +
        "letras mayúsculas y números.",
    );
  }

  // ---------------------------------------------
  // SÍMBOLO
  // ---------------------------------------------

  if (!config.simbolo_moneda) {
    errores.push("Debes ingresar el símbolo de la moneda.");
  }

  if (config.simbolo_moneda.length > 10) {
    errores.push(
      "El símbolo de moneda no puede superar " + "los 10 caracteres.",
    );
  }

  // ---------------------------------------------
  // DECIMALES
  // ---------------------------------------------

  if (config.decimales < 0 || config.decimales > 4) {
    errores.push("La cantidad de decimales debe estar " + "entre 0 y 4.");
  }

  // ---------------------------------------------
  // SEPARADORES
  // ---------------------------------------------

  if (config.separador_decimal === config.separador_miles) {
    errores.push(
      "El separador decimal y el separador " +
        "de miles no pueden ser iguales.",
    );
  }

  // ---------------------------------------------
  // POSICIÓN
  // ---------------------------------------------

  if (
    config.posicion_simbolo !== "ANTES" &&
    config.posicion_simbolo !== "DESPUES"
  ) {
    errores.push("La posición del símbolo no es válida.");
  }

  // ---------------------------------------------
  // IMPUESTO
  // ---------------------------------------------

  if (config.impuesto_activo === 1) {
    if (!config.nombre_impuesto) {
      errores.push("Debes ingresar el nombre del impuesto.");
    }

    if (config.porcentaje_impuesto < 0 || config.porcentaje_impuesto > 100) {
      errores.push("El porcentaje del impuesto debe estar " + "entre 0 y 100.");
    }
  }

  // ---------------------------------------------
  // RESULTADO
  // ---------------------------------------------

  return errores;
}

// =====================================================
// GUARDAR CONFIGURACIÓN
// =====================================================

async function guardarConfiguracion() {
  const btnGuardar = document.getElementById("btnGuardarConfiguracion");

  const config = obtenerConfiguracionFormulario();

  const errores = validarConfiguracion(config);

  // ---------------------------------------------
  // VALIDACIÓN
  // ---------------------------------------------

  if (errores.length > 0) {
    mostrarErroresValidacion(errores);

    return;
  }

  // ---------------------------------------------
  // CONFIRMAR
  // ---------------------------------------------

  const confirmacion = await Swal.fire({
    icon: "question",

    title: "¿Guardar configuración?",

    text:
      "Los valores de moneda e impuestos " + "serán utilizados por el sistema.",

    showCancelButton: true,

    confirmButtonText: "Sí, guardar",

    cancelButtonText: "Cancelar",

    reverseButtons: true,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  // ---------------------------------------------
  // BLOQUEAR BOTÓN
  // ---------------------------------------------

  bloquearBotonGuardar(btnGuardar, true);

  try {
    const formData = new FormData();

    // -----------------------------------------
    // MONEDA
    // -----------------------------------------

    formData.append("nombre_moneda", config.nombre_moneda);

    formData.append("codigo_moneda", config.codigo_moneda);

    formData.append("simbolo_moneda", config.simbolo_moneda);

    formData.append("decimales", config.decimales);

    formData.append("separador_decimal", config.separador_decimal);

    formData.append("separador_miles", config.separador_miles);

    formData.append("posicion_simbolo", config.posicion_simbolo);

    // -----------------------------------------
    // IMPUESTOS
    // -----------------------------------------

    formData.append("impuesto_activo", config.impuesto_activo);

    formData.append("nombre_impuesto", config.nombre_impuesto);

    formData.append("porcentaje_impuesto", config.porcentaje_impuesto);

    formData.append(
      "precios_incluyen_impuesto",
      config.precios_incluyen_impuesto,
    );

    // -----------------------------------------
    // PETICIÓN
    // -----------------------------------------

    const respuesta = await fetch(URL_GUARDAR, {
      method: "POST",

      body: formData,

      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP " + respuesta.status);
    }

    const resultado = await respuesta.json();

    // -----------------------------------------
    // ERROR DEL SERVIDOR
    // -----------------------------------------

    if (!resultado || resultado.success !== true) {
      throw new Error(
        resultado && resultado.message
          ? resultado.message
          : "No se pudo guardar la configuración.",
      );
    }

    // -----------------------------------------
    // ACTUALIZAR ORIGINAL
    // -----------------------------------------

    configuracionOriginal = obtenerConfiguracionFormulario();

    // -----------------------------------------
    // ÉXITO
    // -----------------------------------------

    await Swal.fire({
      icon: "success",

      title: "Configuración guardada",

      text: resultado.message || "La configuración se guardó correctamente.",

      confirmButtonText: "Aceptar",
    });
  } catch (error) {
    console.error("Error guardando configuración:", error);

    mostrarAlerta(
      "error",
      "Error",
      error.message || "No se pudo guardar la configuración.",
    );
  } finally {
    bloquearBotonGuardar(btnGuardar, false);
  }
}

// =====================================================
// RESTABLECER CONFIGURACIÓN
// =====================================================

async function restablecerConfiguracion() {
  if (!configuracionOriginal) {
    mostrarAlerta(
      "warning",
      "Configuración",
      "Todavía no existe una configuración " + "original para restablecer.",
    );

    return;
  }

  const confirmacion = await Swal.fire({
    icon: "warning",

    title: "¿Restablecer cambios?",

    text:
      "Se descartarán los cambios realizados " +
      "desde la última configuración guardada.",

    showCancelButton: true,

    confirmButtonText: "Sí, restablecer",

    cancelButtonText: "Cancelar",

    reverseButtons: true,
  });

  if (!confirmacion.isConfirmed) {
    return;
  }

  aplicarConfiguracion(configuracionOriginal);

  actualizarEstadoCamposImpuesto();

  actualizarVistaPrevia();

  mostrarAlerta(
    "success",
    "Restablecido",
    "Los valores han sido restablecidos.",
  );
}

// =====================================================
// ACTUALIZAR ESTADO DE CAMPOS DE IMPUESTO
// =====================================================

function actualizarEstadoCamposImpuesto() {
  const impuestoActivo = obtenerCheckbox("impuesto_activo");

  const nombreImpuesto = document.getElementById("nombre_impuesto");

  const porcentajeImpuesto = document.getElementById("porcentaje_impuesto");

  const preciosIncluyen = document.getElementById("precios_incluyen_impuesto");

  if (nombreImpuesto) {
    nombreImpuesto.disabled = !impuestoActivo;
  }

  if (porcentajeImpuesto) {
    porcentajeImpuesto.disabled = !impuestoActivo;
  }

  if (preciosIncluyen) {
    preciosIncluyen.disabled = !impuestoActivo;
  }

  // ---------------------------------------------
  // CONTENEDOR VISUAL
  // ---------------------------------------------

  const incluidoBox = document.querySelector(".impuesto-incluido-box");

  if (incluidoBox) {
    incluidoBox.classList.toggle("opacity-50", !impuestoActivo);
  }
}

// =====================================================
// ACTUALIZAR VISTA PREVIA
// =====================================================

function actualizarVistaPrevia() {
  const config = obtenerConfiguracionFormulario();

  // ---------------------------------------------
  // MONEDA
  // ---------------------------------------------

  const previewMoneda = document.getElementById("previewMoneda");

  if (previewMoneda) {
    previewMoneda.textContent = config.nombre_moneda || "Sol peruano";
  }

  // ---------------------------------------------
  // CÓDIGO
  // ---------------------------------------------

  const previewCodigo = document.getElementById("previewCodigo");

  if (previewCodigo) {
    previewCodigo.textContent = config.codigo_moneda || "PEN";
  }

  // ---------------------------------------------
  // IMPUESTO
  // ---------------------------------------------

  const previewImpuesto = document.getElementById("previewImpuesto");

  if (previewImpuesto) {
    if (config.impuesto_activo) {
      const nombre = config.nombre_impuesto || "Impuesto";

      const porcentaje = formatearPorcentaje(config.porcentaje_impuesto);

      previewImpuesto.textContent = nombre + " " + porcentaje + "%";
    } else {
      previewImpuesto.textContent = "No aplica";
    }
  }

  // ---------------------------------------------
  // INCLUYE IMPUESTO
  // ---------------------------------------------

  const previewIncluye = document.getElementById("previewIncluye");

  if (previewIncluye) {
    if (config.impuesto_activo && config.precios_incluyen_impuesto) {
      previewIncluye.textContent = "Sí";
    } else {
      previewIncluye.textContent = "No";
    }
  }

  // ---------------------------------------------
  // PRECIO
  // ---------------------------------------------

  const previewPrecio = document.getElementById("previewPrecio");

  if (previewPrecio) {
    previewPrecio.textContent = formatearPrecio(1250.5, config);
  }
}

// =====================================================
// FORMATEAR PRECIO
// =====================================================

function formatearPrecio(precio, config) {
  let decimales = parseInt(config.decimales, 10);

  if (isNaN(decimales) || decimales < 0) {
    decimales = 2;
  }

  if (decimales > 4) {
    decimales = 4;
  }

  // ---------------------------------------------
  // REDONDEAR
  // ---------------------------------------------

  const factor = Math.pow(10, decimales);

  const numeroRedondeado = Math.round(Number(precio) * factor) / factor;

  // ---------------------------------------------
  // CONVERTIR A STRING
  // ---------------------------------------------

  let partes = numeroRedondeado.toFixed(decimales).split(".");

  let parteEntera = partes[0];

  let parteDecimal = partes[1] || "";

  // ---------------------------------------------
  // SEPARADOR DE MILES
  // ---------------------------------------------

  const separadorMiles = config.separador_miles || ",";

  const regex = /\B(?=(\d{3})+(?!\d))/g;

  parteEntera = parteEntera.replace(regex, separadorMiles);

  // ---------------------------------------------
  // SEPARADOR DECIMAL
  // ---------------------------------------------

  const separadorDecimal = config.separador_decimal || ".";

  let numeroFormateado = parteEntera;

  if (decimales > 0) {
    numeroFormateado += separadorDecimal + parteDecimal;
  }

  // ---------------------------------------------
  // SÍMBOLO
  // ---------------------------------------------

  const simbolo = config.simbolo_moneda || "";

  if (!simbolo) {
    return numeroFormateado;
  }

  if (config.posicion_simbolo === "DESPUES") {
    return numeroFormateado + " " + simbolo;
  }

  return simbolo + " " + numeroFormateado;
}

// =====================================================
// FORMATEAR PORCENTAJE
// =====================================================

function formatearPorcentaje(valor) {
  const numero = parseFloat(valor);

  if (isNaN(numero)) {
    return "0";
  }

  if (Number.isInteger(numero)) {
    return String(numero);
  }

  return numero.toFixed(2).replace(/0+$/, "").replace(/\.$/, "");
}

// =====================================================
// CONVERTIR BOOLEANO
// =====================================================

function convertirBooleano(valor) {
  if (
    valor === true ||
    valor === 1 ||
    valor === "1" ||
    valor === "true" ||
    valor === "TRUE"
  ) {
    return true;
  }

  return false;
}

// =====================================================
// BLOQUEAR BOTÓN GUARDAR
// =====================================================

function bloquearBotonGuardar(boton, bloquear) {
  if (!boton) {
    return;
  }

  if (bloquear) {
    boton.disabled = true;

    boton.dataset.textoOriginal = boton.innerHTML;

    boton.innerHTML =
      '<span class="spinner-border ' +
      'spinner-border-sm me-1" ' +
      'role="status" ' +
      'aria-hidden="true"></span>' +
      "Guardando...";
  } else {
    boton.disabled = false;

    if (boton.dataset.textoOriginal) {
      boton.innerHTML = boton.dataset.textoOriginal;
    }
  }
}

// =====================================================
// MOSTRAR ESTADO DE CARGA
// =====================================================

function mostrarEstadoCarga(cargando) {
  const pagina = document.querySelector(".adm-monedas-impuestos-page");

  if (!pagina) {
    return;
  }

  if (cargando) {
    pagina.classList.add("config-loading");
  } else {
    pagina.classList.remove("config-loading");
  }
}

// =====================================================
// MOSTRAR ERRORES DE VALIDACIÓN
// =====================================================

function mostrarErroresValidacion(errores) {
  let html = "<ul class='text-start mb-0'>";

  errores.forEach(function (error) {
    html += "<li>" + escaparHtml(error) + "</li>";
  });

  html += "</ul>";

  Swal.fire({
    icon: "warning",

    title: "Revisa la configuración",

    html: html,

    confirmButtonText: "Aceptar",
  });
}

// =====================================================
// MOSTRAR ALERTA
// =====================================================

function mostrarAlerta(icono, titulo, mensaje) {
  if (typeof Swal !== "undefined") {
    Swal.fire({
      icon: icono,

      title: titulo,

      text: mensaje,

      confirmButtonText: "Aceptar",
    });

    return;
  }

  alert(titulo + "\n\n" + mensaje);
}

// =====================================================
// ESCAPAR HTML
// =====================================================

function escaparHtml(texto) {
  const div = document.createElement("div");

  div.textContent = texto == null ? "" : String(texto);

  return div.innerHTML;
}

// =====================================================
// DETECTAR CAMBIOS
// =====================================================

function configuracionTieneCambios() {
  if (!configuracionOriginal) {
    return false;
  }

  const actual = obtenerConfiguracionFormulario();

  return JSON.stringify(actual) !== JSON.stringify(configuracionOriginal);
}

// =====================================================
// ADVERTENCIA ANTES DE SALIR
// =====================================================

window.addEventListener("beforeunload", function (evento) {
  if (configuracionTieneCambios()) {
    evento.preventDefault();

    evento.returnValue = "";
  }
});

// =====================================================
// EXPORTAR FUNCIONES ÚTILES
// =====================================================

window.AdmMonedasImpuestos = {
  cargarConfiguracion: cargarConfiguracion,

  guardarConfiguracion: guardarConfiguracion,

  restablecerConfiguracion: restablecerConfiguracion,

  actualizarVistaPrevia: actualizarVistaPrevia,

  obtenerConfiguracion: obtenerConfiguracionFormulario,

  validarConfiguracion: validarConfiguracion,

  formatearPrecio: formatearPrecio,
};

// =====================================================
// FIN DEL ARCHIVO
// =====================================================
