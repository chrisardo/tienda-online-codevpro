//======================================================
// CoDevPro Technology
// Archivo: js/adm_registrar_empleado.js
// Módulo: Registrar Empleado
//======================================================

document.addEventListener("DOMContentLoaded", function () {
  "use strict";

  //==================================================
  // ELEMENTOS PRINCIPALES
  //==================================================

  const formulario = document.getElementById("formRegistrarEmpleado");

  if (!formulario) {
    console.error(
      "adm_registrar_empleado.js: No se encontró #formRegistrarEmpleado.",
    );
    return;
  }

  const mensajeEmpleado = document.getElementById("mensajeEmpleado");

  // Imagen
  const imagenEmpleado = document.getElementById("imagenEmpleado");
  const btnSeleccionarImagen = document.getElementById("btnSeleccionarImagen");
  const btnEliminarImagen = document.getElementById("btnEliminarImagen");
  const contenedorVistaPreviaEmpleado = document.getElementById(
    "contenedorVistaPreviaEmpleado",
  );

  // Ubicación
  const idPais = document.getElementById("id_pais");
  const idDepartamento = document.getElementById("id_departamento");
  const idProvincia = document.getElementById("id_provincia");
  const idDistrito = document.getElementById("id_distrito");

  // Rol
  const idRol = document.getElementById("id_rol");

  // Permisos
  const mensajeSinRol = document.getElementById("mensajeSinRol");
  const contenedorPermisos = document.getElementById("contenedorPermisos");
  const tablaPermisos = document.getElementById("tablaPermisos");
  const mensajeSinPermisos = document.getElementById("mensajeSinPermisos");
  const estadoCargaPermisos = document.getElementById("estadoCargaPermisos");

  // Botones
  const btnRegistrarEmpleado = document.getElementById("btnRegistrarEmpleado");

  const btnLimpiarEmpleado = document.getElementById("btnLimpiarEmpleado");
  // Contraseña

  const btnMostrarContrasena = document.getElementById("btnMostrarContrasena");

  const iconoMostrarContrasena = document.getElementById(
    "iconoMostrarContrasena",
  );

  //==================================================
  // CAMPOS PERSONALES
  //==================================================

  const campoDNI = document.getElementById("dni");
  const campoCelular = document.getElementById("celular");
  const campoNombre = document.getElementById("nombre");
  const campoApellido = document.getElementById("apellido");
  const campoEmail = document.getElementById("email");
  const campoDireccion = document.getElementById("direccion");
  const campoContrasena = document.getElementById("contrasena");
  const campoEstado = document.getElementById("estado");
  //==================================================
  // MOSTRAR / OCULTAR CONTRASEÑA
  //==================================================

  if (btnMostrarContrasena && campoContrasena) {
    btnMostrarContrasena.addEventListener("click", function () {
      const mostrando = campoContrasena.type === "text";

      if (mostrando) {
        campoContrasena.type = "password";

        if (iconoMostrarContrasena) {
          iconoMostrarContrasena.className = "bi bi-eye";
        }

        btnMostrarContrasena.title = "Mostrar contraseña";
      } else {
        campoContrasena.type = "text";

        if (iconoMostrarContrasena) {
          iconoMostrarContrasena.className = "bi bi-eye-slash";
        }

        btnMostrarContrasena.title = "Ocultar contraseña";
      }
    });
  }
  //==================================================
  // CONFIGURACIÓN
  //==================================================

  const MAX_TAMANO_IMAGEN = 2.7 * 1024 * 1024;

  const TIPOS_IMAGEN_PERMITIDOS = [
    "image/jpeg",
    "image/jpg",
    "image/png",
    "image/webp",
  ];

  let solicitudDepartamentos = 0;
  let solicitudProvincias = 0;
  let solicitudDistritos = 0;
  let solicitudPermisos = 0;

  //==================================================
  // MOSTRAR MENSAJE
  //==================================================

  function mostrarMensaje(tipo, mensaje, icono) {
    if (!mensajeEmpleado) {
      return;
    }

    let iconoHTML = "";

    if (icono) {
      iconoHTML = '<i class="bi ' + escaparHTML(icono) + ' me-2"></i>';
    }

    mensajeEmpleado.className = "alert alert-" + tipo + " shadow-sm mb-3";

    mensajeEmpleado.innerHTML = iconoHTML + mensaje;

    mensajeEmpleado.style.display = "block";

    mensajeEmpleado.scrollIntoView({
      behavior: "smooth",
      block: "center",
    });
  }

  //==================================================
  // OCULTAR MENSAJE
  //==================================================

  function ocultarMensaje() {
    if (!mensajeEmpleado) {
      return;
    }

    mensajeEmpleado.style.display = "none";
    mensajeEmpleado.innerHTML = "";
    mensajeEmpleado.className = "mb-3";
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
  // ESCAPAR ATRIBUTO HTML
  //==================================================

  function escaparAtributo(texto) {
    return escaparHTML(texto).replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  //==================================================
  // PETICIÓN AJAX JSON
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

    let datos = null;

    try {
      datos = JSON.parse(texto);
    } catch (error) {
      console.error("Respuesta AJAX no válida:", texto);

      throw new Error("El servidor devolvió una respuesta no válida.");
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
  // DETERMINAR ESTADO EXITOSO
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
  // OBTENER DATA DE RESPUESTA
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
  // MOSTRAR CARGANDO EN SELECT
  //==================================================

  function mostrarCargandoSelect(elemento, texto) {
    if (!elemento) {
      return;
    }

    elemento.innerHTML =
      '<option value="">' + escaparHTML(texto || "Cargando...") + "</option>";

    elemento.value = "";
    elemento.disabled = true;
  }

  //==================================================
  // RESTAURAR SELECT
  //==================================================

  function restaurarSelect(elemento, texto) {
    if (!elemento) {
      return;
    }

    elemento.innerHTML =
      '<option value="">' + escaparHTML(texto || "Seleccionar") + "</option>";

    elemento.value = "";
    elemento.disabled = true;

    elemento.classList.remove("is-valid", "is-invalid");
  }

  //==================================================
  // CARGAR OPCIONES
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
  // FOTO - RESTAURAR VISTA PREVIA
  //==================================================

  function restaurarVistaPreviaImagen() {
    if (!contenedorVistaPreviaEmpleado) {
      return;
    }

    contenedorVistaPreviaEmpleado.innerHTML = `
            <div class="empleado-imagen-placeholder">

                <i class="bi bi-person fs-1"></i>

                <span>
                    Sin imagen
                </span>

            </div>
        `;

    if (btnEliminarImagen) {
      btnEliminarImagen.style.display = "none";
    }
  }

  //==================================================
  // FOTO - SELECCIONAR
  //==================================================

  if (btnSeleccionarImagen && imagenEmpleado) {
    btnSeleccionarImagen.addEventListener("click", function () {
      imagenEmpleado.click();
    });
  }

  //==================================================
  // FOTO - CAMBIO
  //==================================================

  if (imagenEmpleado) {
    imagenEmpleado.addEventListener("change", function () {
      ocultarMensaje();
      imagenEmpleado.classList.remove("is-invalid");
      const archivo =
        imagenEmpleado.files && imagenEmpleado.files.length > 0
          ? imagenEmpleado.files[0]
          : null;

      if (!archivo) {
        restaurarVistaPreviaImagen();

        return;
      }

      //========================================
      // VALIDAR TIPO
      //========================================

      if (!TIPOS_IMAGEN_PERMITIDOS.includes(archivo.type)) {
        imagenEmpleado.value = "";

        restaurarVistaPreviaImagen();

        mostrarMensaje(
          "danger",
          "El formato de imagen no es válido. " +
            "Solo se permiten JPG, JPEG, PNG y WEBP.",
          "bi-image",
        );

        return;
      }

      //========================================
      // VALIDAR TAMAÑO
      //========================================

      if (archivo.size > MAX_TAMANO_IMAGEN) {
        imagenEmpleado.value = "";

        restaurarVistaPreviaImagen();

        mostrarMensaje(
          "danger",
          "La imagen supera el tamaño máximo permitido de 2.7 MB.",
          "bi-exclamation-triangle-fill",
        );

        return;
      }

      //========================================
      // VISTA PREVIA
      //========================================

      const lector = new FileReader();

      lector.onload = function (evento) {
        if (!contenedorVistaPreviaEmpleado) {
          return;
        }

        contenedorVistaPreviaEmpleado.innerHTML = `
        <img
            src="${evento.target.result}"
            alt="Vista previa del empleado"
            class="img-fluid empleado-imagen-preview-img"
        >
    `;

        if (btnEliminarImagen) {
          btnEliminarImagen.style.display = "block";
        }

        if (imagenEmpleado) {
          imagenEmpleado.classList.remove("is-invalid");
          imagenEmpleado.classList.add("is-valid");
        }
      };

      lector.onerror = function () {
        imagenEmpleado.value = "";

        restaurarVistaPreviaImagen();

        imagenEmpleado.classList.remove("is-valid");
        imagenEmpleado.classList.add("is-invalid");

        mostrarMensaje(
          "danger",
          "No se pudo leer la imagen seleccionada.",
          "bi-image",
        );
      };

      lector.readAsDataURL(archivo);
    });
  }

  //==================================================
  // FOTO - ELIMINAR
  //==================================================

  if (btnEliminarImagen) {
    btnEliminarImagen.addEventListener("click", function () {
      if (imagenEmpleado) {
        imagenEmpleado.value = "";

        imagenEmpleado.classList.remove("is-valid");
        imagenEmpleado.classList.add("is-invalid");
      }

      restaurarVistaPreviaImagen();

      ocultarMensaje();
    });
  }
  //==================================================
  // CARGAR DEPARTAMENTOS
  //==================================================

  if (idPais) {
    idPais.addEventListener("change", async function () {
      const pais = idPais.value;

      solicitudDepartamentos++;

      const solicitudActual = solicitudDepartamentos;

      restaurarSelect(idDepartamento, "Seleccionar departamento");

      restaurarSelect(idProvincia, "Seleccionar provincia");

      restaurarSelect(idDistrito, "Seleccionar distrito");

      if (!pais) {
        return;
      }

      mostrarCargandoSelect(idDepartamento, "Cargando departamentos...");

      try {
        const datos = await solicitarJSON(
          "ajax/adm_obtener_departamentos.php?id_pais=" +
            encodeURIComponent(pais),
        );

        if (solicitudActual !== solicitudDepartamentos) {
          return;
        }

        const registros = obtenerData(datos);

        if (!respuestaExitosa(datos) || registros.length === 0) {
          restaurarSelect(idDepartamento, "Sin departamentos");

          mostrarMensaje(
            "warning",
            obtenerMensajeRespuesta(datos, "No se encontraron departamentos."),
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

        console.error("Error departamentos:", error);

        restaurarSelect(idDepartamento, "Seleccionar departamento");

        mostrarMensaje(
          "danger",
          error.message || "No se pudieron cargar los departamentos.",
          "bi-exclamation-triangle-fill",
        );
      }
    });
  }

  //==================================================
  // CARGAR PROVINCIAS
  //==================================================

  if (idDepartamento) {
    idDepartamento.addEventListener("change", async function () {
      const departamento = idDepartamento.value;

      solicitudProvincias++;

      const solicitudActual = solicitudProvincias;

      restaurarSelect(idProvincia, "Seleccionar provincia");

      restaurarSelect(idDistrito, "Seleccionar distrito");

      if (!departamento) {
        return;
      }

      mostrarCargandoSelect(idProvincia, "Cargando provincias...");

      try {
        const datos = await solicitarJSON(
          "ajax/adm_obtener_provincias.php?id_departamento=" +
            encodeURIComponent(departamento),
        );

        if (solicitudActual !== solicitudProvincias) {
          return;
        }

        const registros = obtenerData(datos);

        if (!respuestaExitosa(datos) || registros.length === 0) {
          restaurarSelect(idProvincia, "Sin provincias");

          mostrarMensaje(
            "warning",
            obtenerMensajeRespuesta(datos, "No se encontraron provincias."),
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

        console.error("Error provincias:", error);

        restaurarSelect(idProvincia, "Seleccionar provincia");

        mostrarMensaje(
          "danger",
          error.message || "No se pudieron cargar las provincias.",
          "bi-exclamation-triangle-fill",
        );
      }
    });
  }

  //==================================================
  // CARGAR DISTRITOS
  //==================================================

  if (idProvincia) {
    idProvincia.addEventListener("change", async function () {
      const provincia = idProvincia.value;

      solicitudDistritos++;

      const solicitudActual = solicitudDistritos;

      restaurarSelect(idDistrito, "Seleccionar distrito");

      if (!provincia) {
        return;
      }

      mostrarCargandoSelect(idDistrito, "Cargando distritos...");

      try {
        const datos = await solicitarJSON(
          "ajax/adm_obtener_distritos.php?id_provincia=" +
            encodeURIComponent(provincia),
        );

        if (solicitudActual !== solicitudDistritos) {
          return;
        }

        const registros = obtenerData(datos);

        if (!respuestaExitosa(datos) || registros.length === 0) {
          restaurarSelect(idDistrito, "Sin distritos");

          mostrarMensaje(
            "warning",
            obtenerMensajeRespuesta(datos, "No se encontraron distritos."),
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

        console.error("Error distritos:", error);

        restaurarSelect(idDistrito, "Seleccionar distrito");

        mostrarMensaje(
          "danger",
          error.message || "No se pudieron cargar los distritos.",
          "bi-exclamation-triangle-fill",
        );
      }
    });
  }

  //==================================================
  // CARGAR PERMISOS DEL ROL
  //==================================================

  if (idRol) {
    idRol.addEventListener("change", async function () {
      const rol = idRol.value;

      solicitudPermisos++;

      const solicitudActual = solicitudPermisos;

      limpiarPermisos();

      if (!rol) {
        mostrarEstadoSinRol();

        return;
      }

      if (mensajeSinRol) {
        mensajeSinRol.style.display = "none";
      }

      if (mensajeSinPermisos) {
        mensajeSinPermisos.style.display = "none";
      }

      if (contenedorPermisos) {
        contenedorPermisos.style.display = "none";
      }

      if (estadoCargaPermisos) {
        estadoCargaPermisos.innerHTML = `
                        <span
                            class="spinner-border spinner-border-sm me-1"
                            role="status"
                            aria-hidden="true">
                        </span>
                        Cargando permisos...
                        `;
      }

      try {
        const datos = await solicitarJSON(
          "ajax/obtener_permisos_rol.php?id_rol=" + encodeURIComponent(rol),
        );

        if (solicitudActual !== solicitudPermisos) {
          return;
        }

        const permisos = obtenerData(datos);

        if (!respuestaExitosa(datos) || permisos.length === 0) {
          if (contenedorPermisos) {
            contenedorPermisos.style.display = "none";
          }

          if (mensajeSinPermisos) {
            mensajeSinPermisos.style.display = "block";
          }

          if (estadoCargaPermisos) {
            estadoCargaPermisos.innerHTML = `
                                <i class="bi bi-shield-x me-1"></i>
                                Sin permisos configurados
                                `;
          }

          return;
        }

        construirTablaPermisos(permisos);

        if (contenedorPermisos) {
          contenedorPermisos.style.display = "block";
        }

        if (mensajeSinPermisos) {
          mensajeSinPermisos.style.display = "none";
        }

        if (estadoCargaPermisos) {
          estadoCargaPermisos.innerHTML = `
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Permisos cargados correctamente
                            `;
        }
      } catch (error) {
        if (solicitudActual !== solicitudPermisos) {
          return;
        }

        console.error("Error permisos:", error);

        if (contenedorPermisos) {
          contenedorPermisos.style.display = "none";
        }

        if (mensajeSinPermisos) {
          mensajeSinPermisos.style.display = "none";
        }

        if (estadoCargaPermisos) {
          estadoCargaPermisos.innerHTML = `
                            <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                            Error al cargar permisos
                            `;
        }

        mostrarMensaje(
          "danger",
          error.message || "No se pudieron cargar los permisos del rol.",
          "bi-shield-exclamation",
        );
      }
    });
  }

  //==================================================
  // MOSTRAR ESTADO SIN ROL
  //==================================================

  function mostrarEstadoSinRol() {
    if (mensajeSinRol) {
      mensajeSinRol.style.display = "block";
    }

    if (mensajeSinPermisos) {
      mensajeSinPermisos.style.display = "none";
    }

    if (contenedorPermisos) {
      contenedorPermisos.style.display = "none";
    }

    if (estadoCargaPermisos) {
      estadoCargaPermisos.innerHTML = `
                <i class="bi bi-info-circle me-1"></i>
                Seleccione un rol para consultar sus permisos.
                `;
    }
  }

  //==================================================
  // CONSTRUIR TABLA DE PERMISOS
  //==================================================

  function construirTablaPermisos(permisos) {
    if (!tablaPermisos) {
      return;
    }

    tablaPermisos.innerHTML = "";

    if (!Array.isArray(permisos) || permisos.length === 0) {
      return;
    }

    permisos.forEach(function (permiso, indice) {
      if (!permiso) {
        return;
      }

      const idModulo = parseInt(permiso.id_modulo || permiso.idModulo || 0, 10);

      if (!idModulo) {
        return;
      }

      const nombreModulo =
        permiso.nombre_modulo ||
        permiso.nombreModulo ||
        permiso.modulo ||
        permiso.nombre ||
        "Módulo";

      const codigoModulo =
        permiso.codigo_modulo || permiso.codigoModulo || permiso.codigo || "";

      const iconoModulo = permiso.icono || "bi-grid";

      const ver = Number(permiso.ver || permiso.visualizar || 0) === 1;

      const crear = Number(permiso.crear || permiso.agregar || 0) === 1;

      const editar = Number(permiso.editar || permiso.actualizar || 0) === 1;

      const eliminar = Number(permiso.eliminar || permiso.borrar || 0) === 1;

      const fila = document.createElement("tr");

      fila.innerHTML = `
                    <td class="ps-4">

                        <div
                            class="d-flex align-items-center gap-3">

                            <div
                                class="permiso-modulo-icono">

                                <i
                                    class="bi ${escaparAtributo(iconoModulo)}">
                                </i>

                            </div>

                            <div>

                                <div class="fw-semibold">
                                    ${escaparHTML(nombreModulo)}
                                </div>

                                ${
                                  codigoModulo
                                    ? `
                                            <small class="text-muted">
                                                ${escaparHTML(codigoModulo)}
                                            </small>
                                          `
                                    : ""
                                }

                            </div>

                        </div>

                        <input
                            type="hidden"
                            name="permisos[${indice}][id_modulo]"
                            value="${idModulo}"
                            data-id-modulo="${idModulo}"
                        >

                    </td>


                    <td class="text-center">

                        <div
                            class="form-check d-flex justify-content-center">

                            <input
                                class="form-check-input permiso-check"
                                type="checkbox"
                                name="permisos[${indice}][ver]"
                                value="1"
                                data-id-modulo="${idModulo}"
                                data-permiso="ver"
                                ${ver ? "checked" : ""}
                            >

                        </div>

                    </td>


                    <td class="text-center">

                        <div
                            class="form-check d-flex justify-content-center">

                            <input
                                class="form-check-input permiso-check"
                                type="checkbox"
                                name="permisos[${indice}][crear]"
                                value="1"
                                data-id-modulo="${idModulo}"
                                data-permiso="crear"
                                ${crear ? "checked" : ""}
                            >

                        </div>

                    </td>


                    <td class="text-center">

                        <div
                            class="form-check d-flex justify-content-center">

                            <input
                                class="form-check-input permiso-check"
                                type="checkbox"
                                name="permisos[${indice}][editar]"
                                value="1"
                                data-id-modulo="${idModulo}"
                                data-permiso="editar"
                                ${editar ? "checked" : ""}
                            >

                        </div>

                    </td>


                    <td class="text-center">

                        <div
                            class="form-check d-flex justify-content-center">

                            <input
                                class="form-check-input permiso-check"
                                type="checkbox"
                                name="permisos[${indice}][eliminar]"
                                value="1"
                                data-id-modulo="${idModulo}"
                                data-permiso="eliminar"
                                ${eliminar ? "checked" : ""}
                            >

                        </div>

                    </td>
                    `;

      tablaPermisos.appendChild(fila);
    });

    aplicarReglasPermisos();
  }

  //==================================================
  // REGLAS DE PERMISOS
  //==================================================

  function aplicarReglasPermisos() {
    if (!tablaPermisos) {
      return;
    }

    const filas = tablaPermisos.querySelectorAll("tr");

    filas.forEach(function (fila) {
      const checkboxVer = fila.querySelector('[data-permiso="ver"]');

      const checkboxCrear = fila.querySelector('[data-permiso="crear"]');

      const checkboxEditar = fila.querySelector('[data-permiso="editar"]');

      const checkboxEliminar = fila.querySelector('[data-permiso="eliminar"]');

      if (!checkboxVer) {
        return;
      }

      function actualizarDependientes() {
        const tieneVer = checkboxVer.checked;

        if (checkboxCrear) {
          checkboxCrear.disabled = !tieneVer;

          if (!tieneVer) {
            checkboxCrear.checked = false;
          }
        }

        if (checkboxEditar) {
          checkboxEditar.disabled = !tieneVer;

          if (!tieneVer) {
            checkboxEditar.checked = false;
          }
        }

        if (checkboxEliminar) {
          checkboxEliminar.disabled = !tieneVer;

          if (!tieneVer) {
            checkboxEliminar.checked = false;
          }
        }
      }

      checkboxVer.addEventListener("change", actualizarDependientes);

      actualizarDependientes();
    });
  }

  //==================================================
  // LIMPIAR PERMISOS
  //==================================================

  function limpiarPermisos() {
    if (tablaPermisos) {
      tablaPermisos.innerHTML = "";
    }

    if (contenedorPermisos) {
      contenedorPermisos.style.display = "none";
    }

    if (mensajeSinPermisos) {
      mensajeSinPermisos.style.display = "none";
    }
  }

  //==================================================
  // VALIDAR DNI
  //==================================================

  function validarDNI() {
    if (!campoDNI) {
      return true;
    }

    const valor = campoDNI.value.trim();

    const valido =
      valor.length >= 8 && valor.length <= 20 && /^[0-9A-Za-z-]+$/.test(valor);

    campoDNI.classList.toggle("is-invalid", !valido);

    campoDNI.classList.toggle("is-valid", valido);

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

    const valido =
      valor.length >= 9 && valor.length <= 15 && /^[0-9+\-\s]+$/.test(valor);

    campoCelular.classList.toggle("is-invalid", !valido);

    campoCelular.classList.toggle("is-valid", valido);

    return valido;
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
  // VALIDAR APELLIDO
  //==================================================

  function validarApellido() {
    if (!campoApellido) {
      return true;
    }

    const valor = campoApellido.value.trim();

    const valido = valor.length >= 2 && valor.length <= 150;

    campoApellido.classList.toggle("is-invalid", !valido);

    campoApellido.classList.toggle("is-valid", valido);

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

  function validarDireccion() {
    if (!campoDireccion) {
      return true;
    }

    const valor = campoDireccion.value.trim();

    const valido = valor.length >= 3 && valor.length <= 255;

    campoDireccion.classList.toggle("is-invalid", !valido);

    campoDireccion.classList.toggle("is-valid", valido);

    return valido;
  }
  //==================================================
  // VALIDAR CONTRASEÑA
  // OPCIONAL
  //==================================================

  function validarContrasena() {
    if (!campoContrasena) {
      return true;
    }

    const valor = campoContrasena.value.trim();

    //========================================
    // VACÍA = VÁLIDA
    //========================================

    if (valor === "") {
      campoContrasena.classList.remove("is-valid", "is-invalid");

      return true;
    }

    //========================================
    // SI SE INGRESA, MÍNIMO 6 CARACTERES
    //========================================

    const valido = valor.length >= 6 && valor.length <= 255;

    campoContrasena.classList.toggle("is-invalid", !valido);

    campoContrasena.classList.toggle("is-valid", valido);

    return valido;
  }
  //==================================================
  // VALIDAR SELECT
  //==================================================

  function validarSelect(elemento) {
    if (!elemento) {
      return false;
    }

    const valido = String(elemento.value).trim() !== "";

    elemento.classList.toggle("is-invalid", !valido);

    elemento.classList.toggle("is-valid", valido);

    return valido;
  }

  //==================================================
  // VALIDACIÓN GENERAL
  //==================================================

  function validarFormulario() {
    let valido = true;

    ocultarMensaje();

    if (!validarDNI()) {
      valido = false;
    }

    if (!validarCelular()) {
      valido = false;
    }

    if (!validarNombre()) {
      valido = false;
    }

    if (!validarApellido()) {
      valido = false;
    }

    if (!validarEmail()) {
      valido = false;
    }

    if (!validarDireccion()) {
      valido = false;
    }

    if (!validarContrasena()) {
      valido = false;
    }

    if (!validarSelect(idPais)) {
      valido = false;
    }

    if (!validarSelect(idDepartamento)) {
      valido = false;
    }

    if (!validarSelect(idProvincia)) {
      valido = false;
    }

    if (!validarSelect(idDistrito)) {
      valido = false;
    }

    if (!validarSelect(idRol)) {
      valido = false;
    }

    if (!validarSelect(campoEstado)) {
      valido = false;
    }

    if (!valido) {
      mostrarMensaje(
        "danger",
        "Revise los campos marcados antes de registrar al empleado.",
        "bi-exclamation-triangle-fill",
      );
    }

    return valido;
  }

  //==================================================
  // VALIDACIONES EN TIEMPO REAL
  //==================================================

  if (campoDNI) {
    campoDNI.addEventListener("blur", validarDNI);
  }

  if (campoCelular) {
    campoCelular.addEventListener("blur", validarCelular);
  }

  if (campoNombre) {
    campoNombre.addEventListener("blur", validarNombre);
  }

  if (campoApellido) {
    campoApellido.addEventListener("blur", validarApellido);
  }

  if (campoEmail) {
    campoEmail.addEventListener("blur", validarEmail);
  }

  if (campoDireccion) {
    campoDireccion.addEventListener("blur", validarDireccion);
  }
  if (campoContrasena) {
    campoContrasena.addEventListener("blur", validarContrasena);
  }
  //==================================================
  // QUITAR ESPACIOS INNECESARIOS
  //==================================================

  [
    campoDNI,
    campoCelular,
    campoNombre,
    campoApellido,
    campoEmail,
    campoDireccion,
    campoContrasena,
  ].forEach(function (campo) {
    if (!campo) {
      return;
    }

    campo.addEventListener("blur", function () {
      campo.value = campo.value.trim();
    });
  });
  //==================================================
  // VALIDAR IMAGEN
  // LA FOTO DEL EMPLEADO ES OBLIGATORIA
  //==================================================

  function validarImagen() {
    if (
      !imagenEmpleado ||
      !imagenEmpleado.files ||
      imagenEmpleado.files.length === 0
    ) {
      mostrarMensaje(
        "danger",
        "La foto del empleado es obligatoria. Debe seleccionar una imagen antes de registrar al empleado.",
        "bi-person-bounding-box",
      );

      if (imagenEmpleado) {
        imagenEmpleado.classList.add("is-invalid");
        imagenEmpleado.classList.remove("is-valid");
      }

      return false;
    }

    const archivo = imagenEmpleado.files[0];

    //========================================
    // VALIDAR TIPO
    //========================================

    if (!TIPOS_IMAGEN_PERMITIDOS.includes(archivo.type)) {
      mostrarMensaje(
        "danger",
        "La imagen seleccionada no tiene un formato permitido. Solo se permiten JPG, JPEG, PNG y WEBP.",
        "bi-image",
      );

      imagenEmpleado.classList.add("is-invalid");
      imagenEmpleado.classList.remove("is-valid");

      return false;
    }

    //========================================
    // VALIDAR TAMAÑO
    //========================================

    if (archivo.size > MAX_TAMANO_IMAGEN) {
      mostrarMensaje(
        "danger",
        "La imagen supera el tamaño máximo permitido de 2.7 MB.",
        "bi-exclamation-triangle-fill",
      );

      imagenEmpleado.classList.add("is-invalid");
      imagenEmpleado.classList.remove("is-valid");

      return false;
    }

    //========================================
    // IMAGEN VÁLIDA
    //========================================

    imagenEmpleado.classList.remove("is-invalid");
    imagenEmpleado.classList.add("is-valid");

    return true;
  }

  //==================================================
  // OBTENER PERMISOS DE LA TABLA
  //==================================================

  function obtenerPermisosFormulario() {
    const permisos = [];

    if (!tablaPermisos) {
      return permisos;
    }

    const filas = tablaPermisos.querySelectorAll("tr");

    filas.forEach(function (fila) {
      const inputModulo = fila.querySelector("input[data-id-modulo]");

      if (!inputModulo) {
        return;
      }

      const idModulo = parseInt(
        inputModulo.dataset.idModulo || inputModulo.value || "0",
        10,
      );

      if (!idModulo) {
        return;
      }

      const checkboxVer = fila.querySelector('[data-permiso="ver"]');

      const checkboxCrear = fila.querySelector('[data-permiso="crear"]');

      const checkboxEditar = fila.querySelector('[data-permiso="editar"]');

      const checkboxEliminar = fila.querySelector('[data-permiso="eliminar"]');

      permisos.push({
        id_modulo: idModulo,

        ver: checkboxVer && checkboxVer.checked ? 1 : 0,

        crear: checkboxCrear && checkboxCrear.checked ? 1 : 0,

        editar: checkboxEditar && checkboxEditar.checked ? 1 : 0,

        eliminar: checkboxEliminar && checkboxEliminar.checked ? 1 : 0,
      });
    });

    return permisos;
  }

  //==================================================
  // ENVIAR FORMULARIO
  //==================================================

  if (formulario) {
    formulario.addEventListener("submit", async function (evento) {
      evento.preventDefault();

      //========================================
      // EVITAR DOBLE ENVÍO
      //========================================

      if (btnRegistrarEmpleado && btnRegistrarEmpleado.disabled) {
        return;
      }

      ocultarMensaje();

      //========================================
      // VALIDAR FORMULARIO
      //========================================

      if (!validarFormulario()) {
        return;
      }

      //========================================
      // VALIDAR IMAGEN
      //========================================

      if (!validarImagen()) {
        return;
      }

      //========================================
      // VALIDAR ROL
      //========================================

      if (!idRol || !idRol.value) {
        mostrarMensaje(
          "warning",
          "Debe seleccionar un cargo o rol para el empleado.",
          "bi-shield-exclamation",
        );

        return;
      }

      //========================================
      // CREAR FORM DATA
      //========================================

      const formData = new FormData(formulario);

      //========================================
      // OBTENER PERMISOS
      //========================================

      const permisos = obtenerPermisosFormulario();

      //========================================
      // ENVIAR PERMISOS JSON
      //========================================

      formData.set("permisos", JSON.stringify(permisos));

      //========================================
      // ASEGURAR ID USER
      //========================================

      const idUser = document.getElementById("id_user");

      if (idUser && idUser.value) {
        formData.set("id_user", idUser.value);
      }

      //========================================
      // BOTÓN
      //========================================

      const textoOriginal = btnRegistrarEmpleado
        ? btnRegistrarEmpleado.innerHTML
        : "";

      if (btnRegistrarEmpleado) {
        btnRegistrarEmpleado.disabled = true;

        btnRegistrarEmpleado.innerHTML = `
                        <span
                            class="spinner-border spinner-border-sm me-2"
                            role="status"
                            aria-hidden="true">
                        </span>
                        Registrando empleado...
                        `;
      }

      try {
        //====================================
        // AJAX REGISTRAR EMPLEADO
        //====================================

        const datos = await solicitarJSON("ajax/registrar_empleado.php", {
          method: "POST",
          body: formData,
        });

        //====================================
        // RESPUESTA
        //====================================

        if (respuestaExitosa(datos)) {
          mostrarMensaje(
            "success",
            obtenerMensajeRespuesta(
              datos,
              "Empleado registrado correctamente.",
            ),
            "bi-check-circle-fill",
          );

          //================================
          // LIMPIAR FORMULARIO
          //================================

          limpiarFormulario();
        } else {
          mostrarMensaje(
            "danger",
            obtenerMensajeRespuesta(datos, "No se pudo registrar el empleado."),
            "bi-x-circle-fill",
          );
        }
      } catch (error) {
        console.error("Error al registrar empleado:", error);

        mostrarMensaje(
          "danger",
          error.message || "Ocurrió un error al registrar el empleado.",
          "bi-exclamation-triangle-fill",
        );
      } finally {
        //====================================
        // RESTAURAR BOTÓN
        //====================================

        if (btnRegistrarEmpleado) {
          btnRegistrarEmpleado.disabled = false;

          btnRegistrarEmpleado.innerHTML = textoOriginal;
        }
      }
    });
  }

  //==================================================
  // LIMPIAR FORMULARIO COMPLETO
  //==================================================

  function limpiarFormulario() {
    formulario.reset();

    //==============================================
    // IMAGEN
    //==============================================

    if (imagenEmpleado) {
      imagenEmpleado.value = "";
    }

    restaurarVistaPreviaImagen();

    //==============================================
    // UBICACIÓN
    //==============================================

    if (idDepartamento) {
      restaurarSelect(idDepartamento, "Seleccionar departamento");
    }

    if (idProvincia) {
      restaurarSelect(idProvincia, "Seleccionar provincia");
    }

    if (idDistrito) {
      restaurarSelect(idDistrito, "Seleccionar distrito");
    }

    //==============================================
    // ROL
    //==============================================

    if (idRol) {
      idRol.value = "";

      idRol.classList.remove("is-valid", "is-invalid");
    }

    //==============================================
    // ESTADO
    //==============================================

    if (campoEstado) {
      campoEstado.value = "ACTIVO";

      campoEstado.classList.remove("is-valid", "is-invalid");
    }

    //==============================================
    // PERMISOS
    //==============================================

    limpiarPermisos();

    mostrarEstadoSinRol();

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
  // BOTÓN LIMPIAR
  //==================================================

  if (btnLimpiarEmpleado) {
    btnLimpiarEmpleado.addEventListener("click", function () {
      // El reset nativo se ejecutará primero.
      setTimeout(function () {
        ocultarMensaje();

        limpiarFormulario();
      }, 0);
    });
  }

  //==================================================
  // INICIALIZACIÓN
  //==================================================

  restaurarVistaPreviaImagen();

  limpiarPermisos();

  mostrarEstadoSinRol();
});
