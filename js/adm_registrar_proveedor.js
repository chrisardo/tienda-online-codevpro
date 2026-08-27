//======================================================
// CoDevPro Technology
// Archivo: js/adm_registrar_proveedor.js
// Módulo: Registrar Proveedor
//======================================================

document.addEventListener("DOMContentLoaded", function () {
  "use strict";

  //==================================================
  // ELEMENTOS PRINCIPALES
  //==================================================

  const formulario = document.getElementById("formRegistrarProveedor");

  if (!formulario) {
    console.error(
      "adm_registrar_proveedor.js: No se encontró #formRegistrarProveedor.",
    );

    return;
  }

  const alertaProveedor = document.getElementById("alertaProveedor");

  //==================================================
  // CAMPOS DEL PROVEEDOR
  //==================================================

  const campoNombre = document.getElementById("nombre");

  const campoRuc = document.getElementById("ruc");

  const campoCelular = document.getElementById("celular");

  const campoEmail = document.getElementById("email");

  //const campoDireccion = document.getElementById("direccion");

  //==================================================
  // IMAGEN
  //==================================================

  const inputImagen = document.getElementById("imagen");

  const btnEliminarImagen = document.getElementById("btnEliminarImagen");

  const contenedorImagen = document.getElementById("contenedorImagen");

  //==================================================
  // UBICACIÓN
  //==================================================

  const idPais = document.getElementById("id_pais");

  const idDepartamento = document.getElementById("id_departamento");

  /*
   * IMPORTANTE
   *
   * En tu HTML el select tiene:
   *
   * id="provincia"
   *
   * NO:
   *
   * id="id_provincia"
   *
   * Por eso aquí debemos utilizar "provincia".
   */

  const idProvincia = document.getElementById("provincia");

  const idDistrito = document.getElementById("id_distrito");

  //==================================================
  // BOTONES
  //==================================================

  const btnRegistrarProveedor = document.getElementById(
    "btnRegistrarProveedor",
  );

  const spinnerProveedor = document.getElementById("spinnerProveedor");

  const iconRegistrarProveedor = document.getElementById(
    "iconRegistrarProveedor",
  );

  const textoRegistrarProveedor = document.getElementById(
    "textoRegistrarProveedor",
  );

  //==================================================
  // CONFIGURACIÓN AJAX
  //==================================================

  const URL_REGISTRAR = "ajax/registrar_proveedor.php";

  /*
   * TU ARCHIVO REAL ES:
   *
   * ajax/adm_obtener_pais.php
   *
   * No adm_obtener_paises.php
   */

  const URL_PAISES = "ajax/adm_obtener_pais.php";

  const URL_DEPARTAMENTOS = "ajax/adm_obtener_departamentos.php";

  const URL_PROVINCIAS = "ajax/adm_obtener_provincias.php";

  const URL_DISTRITOS = "ajax/adm_obtener_distritos.php";

  //==================================================
  // CONFIGURACIÓN IMAGEN
  //==================================================

  /*
   * El HTML indica 2 MB.
   *
   * Utilizamos exactamente el mismo límite
   * en JavaScript.
   */

  const MAX_TAMANO_IMAGEN = 2.8 * 1024 * 1024;

  const TIPOS_IMAGEN_PERMITIDOS = [
    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/webp",
  ];

  //==================================================
  // CONTROL DE SOLICITUDES
  //==================================================

  let solicitudPaises = 0;

  let solicitudDepartamentos = 0;

  let solicitudProvincias = 0;

  let solicitudDistritos = 0;

  //==================================================
  // MOSTRAR ALERTA
  //==================================================

  function mostrarAlerta(tipo, mensaje, icono) {
    if (!alertaProveedor) {
      return;
    }

    let iconoHTML = "";

    if (icono) {
      iconoHTML = '<i class="bi ' + escaparAtributo(icono) + ' me-2"></i>';
    }

    alertaProveedor.className = "alert proveedor-alert alert-" + tipo;

    alertaProveedor.innerHTML = iconoHTML + escaparHTML(mensaje);

    alertaProveedor.classList.remove("d-none");

    alertaProveedor.scrollIntoView({
      behavior: "smooth",
      block: "center",
    });
  }

  //==================================================
  // OCULTAR ALERTA
  //==================================================

  function ocultarAlerta() {
    if (!alertaProveedor) {
      return;
    }

    alertaProveedor.classList.add("d-none");

    alertaProveedor.innerHTML = "";
  }

  //==================================================
  // ESCAPAR HTML
  //==================================================

  function escaparHTML(texto) {
    if (texto === null || texto === undefined) {
      return "";
    }

    const div = document.createElement("div");

    div.textContent = String(texto);

    return div.innerHTML;
  }

  //==================================================
  // ESCAPAR ATRIBUTO
  //==================================================

  function escaparAtributo(texto) {
    return escaparHTML(texto).replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  //==================================================
  // OBTENER MENSAJE DE RESPUESTA
  //==================================================

  function obtenerMensajeRespuesta(datos, mensajePredeterminado) {
    if (!datos) {
      return mensajePredeterminado;
    }

    return (
      datos.mensaje || datos.message || datos.error || mensajePredeterminado
    );
  }

  //==================================================
  // DETERMINAR RESPUESTA EXITOSA
  //==================================================

  function respuestaExitosa(datos) {
    if (!datos) {
      return false;
    }

    return (
      datos.estado === true ||
      datos.estado === 1 ||
      datos.estado === "1" ||
      datos.success === true ||
      datos.success === 1 ||
      datos.success === "1"
    );
  }

  //==================================================
  // OBTENER DATA
  //==================================================

  function obtenerData(datos) {
    if (!datos) {
      return [];
    }

    if (Array.isArray(datos.data)) {
      return datos.data;
    }

    if (Array.isArray(datos.datos)) {
      return datos.datos;
    }

    if (Array.isArray(datos.resultado)) {
      return datos.resultado;
    }

    return [];
  }

  //==================================================
  // AJAX JSON
  //==================================================

  async function solicitarJSON(url, opciones) {
    opciones = opciones || {};

    const configuracion = {
      method: opciones.method || "GET",

      credentials: "same-origin",

      headers: opciones.headers || {},
    };

    if (opciones.body !== undefined && opciones.body !== null) {
      configuracion.body = opciones.body;
    }

    let respuesta;

    try {
      respuesta = await fetch(url, configuracion);
    } catch (error) {
      console.error("Error de conexión AJAX:", error);

      throw new Error("No se pudo establecer comunicación con el servidor.");
    }

    const texto = await respuesta.text();

    let datos;

    try {
      datos = JSON.parse(texto);
    } catch (error) {
      console.error("Respuesta AJAX no válida:", texto);

      throw new Error(
        "El servidor devolvió una respuesta no válida. Revise el archivo PHP del servidor.",
      );
    }

    if (!respuesta.ok) {
      throw new Error(
        obtenerMensajeRespuesta(
          datos,
          "Ocurrió un error en la comunicación con el servidor.",
        ),
      );
    }

    return datos;
  }

  //==================================================
  // SELECT - CARGANDO
  //==================================================

  function mostrarCargandoSelect(elemento, texto) {
    if (!elemento) {
      return;
    }

    elemento.innerHTML =
      '<option value="">' + escaparHTML(texto || "Cargando...") + "</option>";

    elemento.value = "";

    elemento.disabled = true;

    elemento.classList.remove("is-valid", "is-invalid");
  }

  //==================================================
  // SELECT - RESTAURAR
  //==================================================

  function restaurarSelect(elemento, texto, deshabilitado) {
    if (!elemento) {
      return;
    }

    if (deshabilitado === undefined) {
      deshabilitado = true;
    }

    elemento.innerHTML =
      '<option value="">' + escaparHTML(texto || "Seleccionar") + "</option>";

    elemento.value = "";

    elemento.disabled = deshabilitado;

    elemento.classList.remove("is-valid", "is-invalid");
  }

  //==================================================
  // CARGAR OPCIONES EN SELECT
  //==================================================

  function cargarOpcionesSelect(
    elemento,
    registros,
    idCampo,
    nombreCampo,
    textoInicial,
  ) {
    if (!elemento) {
      return false;
    }

    elemento.innerHTML =
      '<option value="">' + escaparHTML(textoInicial) + "</option>";

    if (!Array.isArray(registros) || registros.length === 0) {
      elemento.disabled = true;

      return false;
    }

    registros.forEach(function (registro) {
      if (!registro) {
        return;
      }

      const valor = registro[idCampo];

      const nombre = registro[nombreCampo];

      if (valor === undefined || valor === null) {
        return;
      }

      const option = document.createElement("option");

      option.value = String(valor);

      option.textContent =
        nombre !== undefined && nombre !== null ? String(nombre) : "";

      elemento.appendChild(option);
    });

    elemento.disabled = false;

    return elemento.options.length > 1;
  }

  //==================================================
  // CARGAR PAÍSES
  //==================================================

  async function cargarPaises() {
    if (!idPais) {
      console.error("No se encontró #id_pais.");

      return;
    }

    solicitudPaises++;

    const solicitudActual = solicitudPaises;

    mostrarCargandoSelect(idPais, "Cargando países...");

    try {
      const datos = await solicitarJSON(URL_PAISES);

      if (solicitudActual !== solicitudPaises) {
        return;
      }

      const registros = obtenerData(datos);

      if (!respuestaExitosa(datos) || registros.length === 0) {
        restaurarSelect(idPais, "Sin países disponibles", true);

        mostrarAlerta(
          "warning",
          obtenerMensajeRespuesta(
            datos,
            "No se encontraron países disponibles.",
          ),
          "bi-info-circle",
        );

        return;
      }

      cargarOpcionesSelect(
        idPais,
        registros,
        "id_pais",
        "nombre",
        "Seleccionar país",
      );
    } catch (error) {
      if (solicitudActual !== solicitudPaises) {
        return;
      }

      console.error("Error cargando países:", error);

      restaurarSelect(idPais, "Seleccionar país", false);

      mostrarAlerta(
        "danger",
        error.message || "No se pudieron cargar los países.",
        "bi-exclamation-triangle-fill",
      );
    }
  }

  //==================================================
  // CAMBIO DE PAÍS
  //==================================================

  if (idPais) {
    idPais.addEventListener("change", async function () {
      const pais = idPais.value;

      solicitudDepartamentos++;

      const solicitudActual = solicitudDepartamentos;

      /*
       * Al cambiar de país debemos
       * reiniciar todos los niveles
       * inferiores.
       */

      restaurarSelect(idDepartamento, "Seleccionar departamento", true);

      restaurarSelect(idProvincia, "Seleccionar provincia", true);

      restaurarSelect(idDistrito, "Seleccionar distrito", true);

      if (!pais) {
        return;
      }

      ocultarAlerta();

      mostrarCargandoSelect(idDepartamento, "Cargando departamentos...");

      try {
        const datos = await solicitarJSON(
          URL_DEPARTAMENTOS + "?id_pais=" + encodeURIComponent(pais),
        );

        if (solicitudActual !== solicitudDepartamentos) {
          return;
        }

        const registros = obtenerData(datos);

        if (!respuestaExitosa(datos) || registros.length === 0) {
          restaurarSelect(
            idDepartamento,
            "Sin departamentos disponibles",
            true,
          );

          mostrarAlerta(
            "warning",
            obtenerMensajeRespuesta(
              datos,
              "No se encontraron departamentos para el país seleccionado.",
            ),
            "bi-info-circle",
          );

          return;
        }

        cargarOpcionesSelect(
          idDepartamento,
          registros,
          "id_departamento",
          "nombre",
          "Seleccionar departamento",
        );
      } catch (error) {
        if (solicitudActual !== solicitudDepartamentos) {
          return;
        }

        console.error("Error cargando departamentos:", error);

        restaurarSelect(idDepartamento, "Seleccionar departamento", true);

        mostrarAlerta(
          "danger",
          error.message || "No se pudieron cargar los departamentos.",
          "bi-exclamation-triangle-fill",
        );
      }
    });
  }

  //==================================================
  // CAMBIO DE DEPARTAMENTO
  //==================================================

  if (idDepartamento) {
    idDepartamento.addEventListener("change", async function () {
      const departamento = idDepartamento.value;

      solicitudProvincias++;

      const solicitudActual = solicitudProvincias;

      /*
       * Reiniciar provincia y distrito.
       */

      restaurarSelect(idProvincia, "Seleccionar provincia", true);

      restaurarSelect(idDistrito, "Seleccionar distrito", true);

      if (!departamento) {
        return;
      }

      ocultarAlerta();

      mostrarCargandoSelect(idProvincia, "Cargando provincias...");

      try {
        const datos = await solicitarJSON(
          URL_PROVINCIAS +
            "?id_departamento=" +
            encodeURIComponent(departamento),
        );

        if (solicitudActual !== solicitudProvincias) {
          return;
        }

        const registros = obtenerData(datos);

        if (!respuestaExitosa(datos) || registros.length === 0) {
          restaurarSelect(idProvincia, "Sin provincias disponibles", true);

          mostrarAlerta(
            "warning",
            obtenerMensajeRespuesta(
              datos,
              "No se encontraron provincias para el departamento seleccionado.",
            ),
            "bi-info-circle",
          );

          return;
        }

        cargarOpcionesSelect(
          idProvincia,
          registros,
          "id_provincia",
          "nombre",
          "Seleccionar provincia",
        );
      } catch (error) {
        if (solicitudActual !== solicitudProvincias) {
          return;
        }

        console.error("Error cargando provincias:", error);

        restaurarSelect(idProvincia, "Seleccionar provincia", true);

        mostrarAlerta(
          "danger",
          error.message || "No se pudieron cargar las provincias.",
          "bi-exclamation-triangle-fill",
        );
      }
    });
  }

  //==================================================
  // CAMBIO DE PROVINCIA
  //==================================================

  if (idProvincia) {
    idProvincia.addEventListener("change", async function () {
      const provincia = idProvincia.value;

      solicitudDistritos++;

      const solicitudActual = solicitudDistritos;

      restaurarSelect(idDistrito, "Seleccionar distrito", true);

      if (!provincia) {
        return;
      }

      ocultarAlerta();

      mostrarCargandoSelect(idDistrito, "Cargando distritos...");

      try {
        const datos = await solicitarJSON(
          URL_DISTRITOS + "?id_provincia=" + encodeURIComponent(provincia),
        );

        if (solicitudActual !== solicitudDistritos) {
          return;
        }

        const registros = obtenerData(datos);

        if (!respuestaExitosa(datos) || registros.length === 0) {
          restaurarSelect(idDistrito, "Sin distritos disponibles", true);

          mostrarAlerta(
            "warning",
            obtenerMensajeRespuesta(
              datos,
              "No se encontraron distritos para la provincia seleccionada.",
            ),
            "bi-info-circle",
          );

          return;
        }

        cargarOpcionesSelect(
          idDistrito,
          registros,
          "id_distrito",
          "nombre",
          "Seleccionar distrito",
        );
      } catch (error) {
        if (solicitudActual !== solicitudDistritos) {
          return;
        }

        console.error("Error cargando distritos:", error);

        restaurarSelect(idDistrito, "Seleccionar distrito", true);

        mostrarAlerta(
          "danger",
          error.message || "No se pudieron cargar los distritos.",
          "bi-exclamation-triangle-fill",
        );
      }
    });
  }

  //==================================================
  // RESTAURAR IMAGEN
  //==================================================

  function restaurarImagen() {
    if (contenedorImagen) {
      contenedorImagen.classList.remove("has-image");

      contenedorImagen.innerHTML = `
                <div class="proveedor-image-placeholder">

                    <i class="bi bi-building"></i>

                    <span>
                        Sin imagen
                    </span>

                </div>
            `;
    }

    if (btnEliminarImagen) {
      btnEliminarImagen.classList.add("d-none");
    }
  }

  //==================================================
  // VALIDAR IMAGEN
  //==================================================

  function validarImagen() {
    /*
     * La imagen es opcional.
     */

    if (!inputImagen || !inputImagen.files || inputImagen.files.length === 0) {
      if (inputImagen) {
        inputImagen.classList.remove("is-valid", "is-invalid");
      }

      return true;
    }

    const archivo = inputImagen.files[0];

    //==============================================
    // TIPO
    //==============================================

    if (!TIPOS_IMAGEN_PERMITIDOS.includes(archivo.type)) {
      inputImagen.classList.add("is-invalid");

      mostrarAlerta(
        "danger",
        "El formato de imagen no es válido. Solo se permiten JPG, JPEG, PNG y WEBP.",
        "bi-image",
      );

      return false;
    }

    //==============================================
    // TAMAÑO
    //==============================================

    if (archivo.size > MAX_TAMANO_IMAGEN) {
      inputImagen.classList.add("is-invalid");

      mostrarAlerta(
        "danger",
        "La imagen supera el tamaño máximo permitido de 2 MB.",
        "bi-exclamation-triangle-fill",
      );

      return false;
    }

    inputImagen.classList.remove("is-invalid");

    inputImagen.classList.add("is-valid");

    return true;
  }

  //==================================================
  // CAMBIO DE IMAGEN
  //==================================================

  if (inputImagen) {
    inputImagen.addEventListener("change", function () {
      ocultarAlerta();

      inputImagen.classList.remove("is-invalid", "is-valid");

      const archivo =
        inputImagen.files && inputImagen.files.length > 0
          ? inputImagen.files[0]
          : null;

      if (!archivo) {
        restaurarImagen();

        return;
      }

      //======================================
      // VALIDAR TIPO
      //======================================

      if (!TIPOS_IMAGEN_PERMITIDOS.includes(archivo.type)) {
        inputImagen.value = "";

        restaurarImagen();

        inputImagen.classList.add("is-invalid");

        mostrarAlerta(
          "danger",
          "El formato de imagen no es válido. Solo se permiten JPG, JPEG, PNG y WEBP.",
          "bi-image",
        );

        return;
      }

      //======================================
      // VALIDAR TAMAÑO
      //======================================

      if (archivo.size > MAX_TAMANO_IMAGEN) {
        inputImagen.value = "";

        restaurarImagen();

        inputImagen.classList.add("is-invalid");

        mostrarAlerta(
          "danger",
          "La imagen supera el tamaño máximo permitido de 2 MB.",
          "bi-exclamation-triangle-fill",
        );

        return;
      }

      //======================================
      // VISTA PREVIA
      //======================================

      const lector = new FileReader();

      lector.onload = function (evento) {
        if (!contenedorImagen) {
          return;
        }

        contenedorImagen.innerHTML = `
                            <img
                                src="${evento.target.result}"
                                alt="Vista previa del proveedor"
                                class="img-fluid proveedor-image-preview-img"
                            >
                        `;

        contenedorImagen.classList.add("has-image");

        if (btnEliminarImagen) {
          btnEliminarImagen.classList.remove("d-none");
        }

        inputImagen.classList.remove("is-invalid");

        inputImagen.classList.add("is-valid");
      };

      lector.onerror = function () {
        inputImagen.value = "";

        restaurarImagen();

        inputImagen.classList.remove("is-valid");

        inputImagen.classList.add("is-invalid");

        mostrarAlerta(
          "danger",
          "No se pudo leer la imagen seleccionada.",
          "bi-image",
        );
      };

      lector.readAsDataURL(archivo);
    });
  }

  //==================================================
  // ELIMINAR IMAGEN
  //==================================================

  if (btnEliminarImagen) {
    btnEliminarImagen.addEventListener("click", function () {
      if (inputImagen) {
        inputImagen.value = "";

        inputImagen.classList.remove("is-valid", "is-invalid");
      }

      restaurarImagen();

      ocultarAlerta();
    });
  }

  //==================================================
  // VALIDAR NOMBRE
  //==================================================

  function validarNombre() {
    if (!campoNombre) {
      return true;
    }

    const valor = campoNombre.value.trim();

    const valido = valor.length >= 2 && valor.length <= 150;

    campoNombre.classList.toggle("is-invalid", !valido);

    campoNombre.classList.toggle("is-valid", valido);

    return valido;
  }

  //==================================================
  // VALIDAR RUC
  //==================================================

  function validarRuc() {
    if (!campoRuc) {
      return true;
    }

    const valor = campoRuc.value.trim();

    const valido = /^[0-9]{11}$/.test(valor);

    campoRuc.classList.toggle("is-invalid", !valido);

    campoRuc.classList.toggle("is-valid", valido);

    return valido;
  }

  //==================================================
  // VALIDAR CELULAR
  //==================================================

  function validarCelular() {
    if (!campoCelular) {
      return true;
    }

    const valor = campoCelular.value.trim();

    /*
     * El input actualmente permite
     * solamente números.
     */

    const valido = /^[0-9]{9,15}$/.test(valor);

    campoCelular.classList.toggle("is-invalid", !valido);

    campoCelular.classList.toggle("is-valid", valido);

    return valido;
  }

  //==================================================
  // VALIDAR EMAIL
  //==================================================

  function validarEmail() {
    if (!campoEmail) {
      return true;
    }

    const valor = campoEmail.value.trim();

    const patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const valido = patron.test(valor);

    campoEmail.classList.toggle("is-invalid", !valido);

    campoEmail.classList.toggle("is-valid", valido);

    return valido;
  }

  //==================================================
  // VALIDAR DIRECCIÓN
  //==================================================

  /*function validarDireccion() {
    if (!campoDireccion) {
      return true;
    }

    const valor = campoDireccion.value.trim();

    const valido = valor.length >= 3 && valor.length <= 250;

    campoDireccion.classList.toggle("is-invalid", !valido);

    campoDireccion.classList.toggle("is-valid", valido);

    return valido;
  }*/

  //==================================================
  // VALIDAR SELECT
  //==================================================

  function validarSelect(elemento, obligatorio) {
    if (!elemento) {
      /*
       * Si el elemento no existe,
       * se considera inválido cuando
       * es obligatorio.
       */

      return obligatorio ? false : true;
    }

    const valor = String(elemento.value).trim();

    const valido = obligatorio ? valor !== "" : true;

    elemento.classList.toggle("is-invalid", !valido);

    elemento.classList.toggle("is-valid", valido);

    return valido;
  }

  //==================================================
  // VALIDACIÓN GENERAL
  //==================================================

  function validarFormulario() {
    let valido = true;

    ocultarAlerta();

    //==============================================
    // DATOS PRINCIPALES
    //==============================================

    if (!validarNombre()) {
      valido = false;
    }

    if (!validarRuc()) {
      valido = false;
    }

    if (!validarCelular()) {
      valido = false;
    }

    if (!validarEmail()) {
      valido = false;
    }

    /*if (!validarDireccion()) {
      valido = false;
    }*/

    //==============================================
    // UBICACIÓN
    //==============================================

    /*
     * El país no se almacena en provedores,
     * pero sí es obligatorio para determinar
     * el departamento/provincia/distrito.
     */

    if (!validarSelect(idPais, true)) {
      valido = false;
    }

    if (!validarSelect(idDepartamento, true)) {
      valido = false;
    }

    if (!validarSelect(idProvincia, true)) {
      valido = false;
    }

    if (!validarSelect(idDistrito, true)) {
      valido = false;
    }

    //==============================================
    // IMAGEN
    //==============================================

    if (!validarImagen()) {
      valido = false;
    }

    //==============================================
    // MENSAJE
    //==============================================

    if (!valido) {
      mostrarAlerta(
        "danger",
        "Revise los campos marcados antes de registrar al proveedor.",
        "bi-exclamation-triangle-fill",
      );
    }

    return valido;
  }

  //==================================================
  // VALIDACIONES EN TIEMPO REAL
  //==================================================

  if (campoNombre) {
    campoNombre.addEventListener("blur", validarNombre);
  }

  if (campoRuc) {
    campoRuc.addEventListener("blur", validarRuc);

    campoRuc.addEventListener("input", function () {
      campoRuc.value = campoRuc.value.replace(/\D/g, "").slice(0, 11);
    });
  }

  if (campoCelular) {
    campoCelular.addEventListener("blur", validarCelular);

    campoCelular.addEventListener("input", function () {
      campoCelular.value = campoCelular.value.replace(/\D/g, "").slice(0, 15);
    });
  }

  if (campoEmail) {
    campoEmail.addEventListener("blur", validarEmail);
  }

  /*if (campoDireccion) {
    campoDireccion.addEventListener("blur", validarDireccion);
  }*/

  //==================================================
  // QUITAR ESPACIOS AL SALIR
  //==================================================

  [campoNombre, campoRuc, campoCelular, campoEmail].forEach(function (campo) {
    if (!campo) {
      return;
    }

    campo.addEventListener("blur", function () {
      campo.value = campo.value.trim();
    });
  });

  //==================================================
  // QUITAR INVALIDACIÓN AL MODIFICAR
  //==================================================

  formulario
    .querySelectorAll("input, textarea, select")
    .forEach(function (campo) {
      campo.addEventListener("input", function () {
        this.classList.remove("is-invalid");
      });

      campo.addEventListener("change", function () {
        this.classList.remove("is-invalid");
      });
    });

  //==================================================
  // MOSTRAR ESTADO DEL BOTÓN
  //==================================================

  function activarEstadoRegistro() {
    if (!btnRegistrarProveedor) {
      return;
    }

    btnRegistrarProveedor.disabled = true;

    if (spinnerProveedor) {
      spinnerProveedor.classList.remove("d-none");
    }

    if (iconRegistrarProveedor) {
      iconRegistrarProveedor.classList.add("d-none");
    }

    if (textoRegistrarProveedor) {
      textoRegistrarProveedor.textContent = "Registrando proveedor...";
    }
  }

  //==================================================
  // RESTAURAR ESTADO DEL BOTÓN
  //==================================================

  function restaurarEstadoRegistro() {
    if (!btnRegistrarProveedor) {
      return;
    }

    btnRegistrarProveedor.disabled = false;

    if (spinnerProveedor) {
      spinnerProveedor.classList.add("d-none");
    }

    if (iconRegistrarProveedor) {
      iconRegistrarProveedor.classList.remove("d-none");
    }

    if (textoRegistrarProveedor) {
      textoRegistrarProveedor.textContent = "Registrar proveedor";
    }
  }

  //==================================================
  // ENVIAR FORMULARIO
  //==================================================

  formulario.addEventListener("submit", async function (evento) {
    evento.preventDefault();

    //==========================================
    // EVITAR DOBLE ENVÍO
    //==========================================

    if (btnRegistrarProveedor && btnRegistrarProveedor.disabled) {
      return;
    }

    ocultarAlerta();

    //==========================================
    // VALIDAR
    //==========================================

    if (!validarFormulario()) {
      return;
    }

    //==========================================
    // CREAR FORMDATA
    //==========================================

    const formData = new FormData(formulario);

    //==========================================
    // ACTIVAR BOTÓN
    //==========================================

    activarEstadoRegistro();

    try {
      //======================================
      // REGISTRAR MEDIANTE AJAX
      //======================================

      const datos = await solicitarJSON(URL_REGISTRAR, {
        method: "POST",
        body: formData,
      });

      //======================================
      // RESPUESTA EXITOSA
      //======================================

      if (respuestaExitosa(datos)) {
        mostrarAlerta(
          "success",
          obtenerMensajeRespuesta(datos, "Proveedor registrado correctamente."),
          "bi-check-circle-fill",
        );

        //====================================
        // LIMPIAR FORMULARIO
        //====================================

        limpiarFormulario();

        //====================================
        // VOLVER A CARGAR PAÍSES
        //====================================

        cargarPaises();
      } else {
        //====================================
        // ERROR DEL SERVIDOR
        //====================================

        mostrarAlerta(
          "danger",
          obtenerMensajeRespuesta(datos, "No se pudo registrar el proveedor."),
          "bi-x-circle-fill",
        );
      }
    } catch (error) {
      console.error("Error al registrar proveedor:", error);

      mostrarAlerta(
        "danger",
        error.message || "Ocurrió un error al registrar el proveedor.",
        "bi-exclamation-triangle-fill",
      );
    } finally {
      //======================================
      // RESTAURAR BOTÓN
      //======================================

      restaurarEstadoRegistro();
    }
  });

  //==================================================
  // LIMPIAR FORMULARIO
  //==================================================

  function limpiarFormulario() {
    formulario.reset();

    //==============================================
    // IMAGEN
    //==============================================

    if (inputImagen) {
      inputImagen.value = "";

      inputImagen.classList.remove("is-valid", "is-invalid");
    }

    restaurarImagen();

    //==============================================
    // PAÍS
    //==============================================

    if (idPais) {
      restaurarSelect(idPais, "Seleccionar país", false);
    }

    //==============================================
    // DEPARTAMENTO
    //==============================================

    if (idDepartamento) {
      restaurarSelect(idDepartamento, "Seleccionar departamento", true);
    }

    //==============================================
    // PROVINCIA
    //==============================================

    if (idProvincia) {
      restaurarSelect(idProvincia, "Seleccionar provincia", true);
    }

    //==============================================
    // DISTRITO
    //==============================================

    if (idDistrito) {
      restaurarSelect(idDistrito, "Seleccionar distrito", true);
    }

    //==============================================
    // VALIDACIONES
    //==============================================

    formulario
      .querySelectorAll(".is-valid, .is-invalid")
      .forEach(function (elemento) {
        elemento.classList.remove("is-valid", "is-invalid");
      });
  }

  //==================================================
  // INICIALIZACIÓN
  //==================================================

  restaurarImagen();

  //==================================================
  // INICIALIZAR UBICACIÓN
  //==================================================

  if (idPais) {
    restaurarSelect(idPais, "Cargando países...", true);
  }

  if (idDepartamento) {
    restaurarSelect(idDepartamento, "Seleccionar departamento", true);
  }

  if (idProvincia) {
    restaurarSelect(idProvincia, "Seleccionar provincia", true);
  }

  if (idDistrito) {
    restaurarSelect(idDistrito, "Seleccionar distrito", true);
  }

  //==================================================
  // CARGAR PAÍSES
  //==================================================

  cargarPaises();
});
