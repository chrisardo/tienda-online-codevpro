//=====================================================
// CoDevPro Technology
// Archivo: js/adm_lista_roles.js
// Módulo: Cargos y Roles
//=====================================================

document.addEventListener("DOMContentLoaded", function () {
  "use strict";

  //=================================================
  // ELEMENTOS
  //=================================================

  const tablaRoles = document.getElementById("tablaRoles");

  const buscarRol = document.getElementById("buscarRol");

  const btnNuevoRol = document.getElementById("btnNuevoRol");

  const btnLimpiarBusqueda = document.getElementById("btnLimpiarBusqueda");

  const contadorRoles = document.getElementById("contadorRoles");

  const kpiTotalRoles = document.getElementById("kpiTotalRoles");

  const kpiRolesUtilizados = document.getElementById("kpiRolesUtilizados");

  const kpiRolesSinEmpleados = document.getElementById("kpiRolesSinEmpleados");

  const modalElement = document.getElementById("modalRol");

  const modalRol = new bootstrap.Modal(modalElement);

  const formRol = document.getElementById("formRol");

  const tituloModalRol = document.getElementById("tituloModalRol");

  const idRol = document.getElementById("idRol");

  const nombreRol = document.getElementById("nombreRol");

  const tablaPermisos = document.getElementById("tablaPermisos");

  const btnGuardarRol = document.getElementById("btnGuardarRol");

  const btnSeleccionarTodos = document.getElementById("btnSeleccionarTodos");

  const btnDeseleccionarTodos = document.getElementById(
    "btnDeseleccionarTodos",
  );

  let roles = [];

  //=================================================
  // CARGAR ROLES
  //=================================================

  cargarRoles();

  async function cargarRoles() {
    tablaRoles.innerHTML = `

            <tr>

                <td
                    colspan="5"
                    class="text-center py-5">

                    <div class="spinner-border text-primary"></div>

                    <div class="mt-2 text-muted">
                        Cargando roles...
                    </div>

                </td>

            </tr>
        `;

    try {
      const respuesta = await fetch("ajax/listar_roles.php", {
        method: "POST",
      });

      const data = await respuesta.json();

      if (!data.success) {
        throw new Error(data.mensaje || "No se pudieron cargar los roles.");
      }

      roles = data.data.roles || [];

      actualizarKPI(data.data);

      renderizarRoles();
    } catch (error) {
      console.error(error);

      tablaRoles.innerHTML = `

                <tr>

                    <td
                        colspan="5"
                        class="text-center text-danger py-5">

                        <i class="bi bi-exclamation-triangle fs-3"></i>

                        <div class="mt-2">
                            ${escapeHtml(error.message)}
                        </div>

                    </td>

                </tr>
            `;
    }
  }

  //=================================================
  // KPI
  //=================================================

  function actualizarKPI(data) {
    kpiTotalRoles.textContent = data.total_roles || 0;

    kpiRolesUtilizados.textContent = data.roles_utilizados || 0;

    kpiRolesSinEmpleados.textContent = data.roles_sin_empleados || 0;
  }

  //=================================================
  // RENDERIZAR ROLES
  //=================================================

  function renderizarRoles() {
    const texto = buscarRol.value.trim().toLowerCase();

    const filtrados = roles.filter(function (rol) {
      return rol.nombre.toLowerCase().includes(texto);
    });

    contadorRoles.textContent =
      filtrados.length + (filtrados.length === 1 ? " rol" : " roles");

    if (filtrados.length === 0) {
      tablaRoles.innerHTML = `

                <tr>

                    <td
                        colspan="5"
                        class="text-center py-5">

                        <div class="text-muted">

                            <i class="bi bi-person-badge fs-1"></i>

                            <div class="mt-2 fw-semibold">
                                No se encontraron roles
                            </div>

                            <small>
                                Intenta realizar otra búsqueda.
                            </small>

                        </div>

                    </td>

                </tr>
            `;

      return;
    }

    let html = "";

    filtrados.forEach(function (rol, index) {
      const cantidadEmpleados = Number(rol.cantidad_empleados);

      const cantidadPermisos = Number(rol.cantidad_permisos);

      let badgeEmpleados = "";

      if (cantidadEmpleados > 0) {
        badgeEmpleados = `

                    <span class="badge bg-primary-subtle text-primary">

                        <i class="bi bi-people-fill me-1"></i>

                        ${cantidadEmpleados}

                    </span>
                `;
      } else {
        badgeEmpleados = `

                    <span class="badge bg-secondary-subtle text-secondary">

                        <i class="bi bi-person-x me-1"></i>

                        0

                    </span>
                `;
      }

      html += `

                <tr>

                    <td class="text-muted fw-semibold">
                        ${index + 1}
                    </td>


                    <td>

                        <div class="d-flex align-items-center gap-3">

                            <div class="rol-icon">

                                <i class="bi bi-person-badge-fill"></i>

                            </div>

                            <div>

                                <div class="fw-semibold">
                                    ${escapeHtml(rol.nombre)}
                                </div>

                                <small class="text-muted">
                                    ID: ${rol.id_rol}
                                </small>

                            </div>

                        </div>

                    </td>


                    <td class="text-center">

                        ${badgeEmpleados}

                    </td>


                    <td class="text-center">

                        <span class="badge bg-success-subtle text-success">

                            <i class="bi bi-shield-check me-1"></i>

                            ${cantidadPermisos}

                        </span>

                    </td>


                    <td class="text-center">

                        <div class="btn-group">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary btn-editar-rol"
                                data-id="${rol.id_rol}"
                                title="Editar rol">

                                <i class="bi bi-pencil-square"></i>

                            </button>


                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger btn-eliminar-rol"
                                data-id="${rol.id_rol}"
                                data-nombre="${escapeHtml(rol.nombre)}"
                                title="Eliminar rol">

                                <i class="bi bi-trash"></i>

                            </button>

                        </div>

                    </td>

                </tr>
            `;
    });

    tablaRoles.innerHTML = html;
  }

  //=================================================
  // NUEVO ROL
  //=================================================

  btnNuevoRol.addEventListener("click", function () {
    prepararNuevoRol();

    modalRol.show();
  });

  function prepararNuevoRol() {
    formRol.reset();

    idRol.value = "0";

    tituloModalRol.innerHTML = `

            <i class="bi bi-person-badge-fill me-2 text-primary"></i>

            Nuevo Rol

        `;

    btnGuardarRol.innerHTML = `

            <i class="bi bi-check-circle me-1"></i>

            Guardar Rol

        `;

    cargarPermisos([]);
  }

  //=================================================
  // EDITAR
  //=================================================

  tablaRoles.addEventListener("click", function (e) {
    const boton = e.target.closest(".btn-editar-rol");

    if (!boton) {
      return;
    }

    const id = Number(boton.dataset.id);

    editarRol(id);
  });

  async function editarRol(id) {
    Swal.fire({
      title: "Cargando rol...",

      allowOutsideClick: false,

      didOpen: () => {
        Swal.showLoading();
      },
    });

    try {
      const formData = new FormData();

      formData.append("id_rol", id);

      const respuesta = await fetch("ajax/obtener_rol.php", {
        method: "POST",
        body: formData,
      });

      const data = await respuesta.json();

      Swal.close();

      if (!data.success) {
        throw new Error(data.mensaje);
      }

      idRol.value = data.data.id_rol;

      nombreRol.value = data.data.nombre;

      tituloModalRol.innerHTML = `

                <i class="bi bi-pencil-square me-2 text-primary"></i>

                Editar Rol

            `;

      btnGuardarRol.innerHTML = `

                <i class="bi bi-check-circle me-1"></i>

                Actualizar Rol

            `;

      cargarPermisos(data.data.permisos);

      modalRol.show();
    } catch (error) {
      Swal.close();

      Swal.fire({
        icon: "error",

        title: "Error",

        text: error.message,
      });
    }
  }

  //=================================================
  // CARGAR PERMISOS
  //=================================================

  async function cargarPermisos(permisosGuardados) {
    tablaPermisos.innerHTML = `

        <tr>

            <td
                colspan="5"
                class="text-center py-4">

                <div class="spinner-border spinner-border-sm text-primary"></div>

                <span class="ms-2">
                    Cargando módulos...
                </span>

            </td>

        </tr>
    `;

    try {
      let modulos = [];

      //=================================================
      // EDITAR
      //=================================================

      if (Number(idRol.value) > 0) {
        const formData = new FormData();

        formData.append("id_rol", idRol.value);

        const respuesta = await fetch("ajax/obtener_rol.php", {
          method: "POST",
          body: formData,
        });

        const data = await respuesta.json();

        if (!data.success) {
          throw new Error(data.mensaje);
        }

        modulos = data.data.permisos;
      } else {
        //=================================================
        // NUEVO
        //=================================================

        const respuesta = await fetch("ajax/listar_modulos.php", {
          method: "POST",
        });

        const data = await respuesta.json();

        if (!data.success) {
          throw new Error(data.mensaje);
        }

        modulos = data.data;
      }

      renderizarPermisos(modulos);
    } catch (error) {
      console.error(error);

      tablaPermisos.innerHTML = `

            <tr>

                <td
                    colspan="5"
                    class="text-center text-danger py-4">

                    <i class="bi bi-exclamation-triangle me-2"></i>

                    ${escapeHtml(error.message)}

                </td>

            </tr>
        `;
    }
  }

  //=================================================
  // RENDERIZAR PERMISOS
  //=================================================

  function renderizarPermisos(permisos) {
    if (!permisos || permisos.length === 0) {
      tablaPermisos.innerHTML = `

                <tr>

                    <td
                        colspan="5"
                        class="text-center text-muted py-4">

                        No existen módulos configurados.

                    </td>

                </tr>
            `;

      return;
    }

    let html = "";

    permisos.forEach(function (modulo) {
      const icono =
        modulo.icono && modulo.icono.trim() !== ""
          ? modulo.icono
          : "bi bi-grid";

      html += `

                <tr>

                    <td>

                        <div class="d-flex align-items-center gap-2">

                            <i class="${escapeHtml(icono)} text-primary"></i>

                            <div>

                                <div class="fw-semibold">

                                    ${escapeHtml(modulo.nombre)}

                                </div>

                                <small class="text-muted">

                                    ${escapeHtml(modulo.codigo || "")}

                                </small>

                            </div>

                        </div>

                    </td>


                    ${crearCheckbox(modulo, "ver", "Ver")}


                    ${crearCheckbox(modulo, "crear", "Crear")}


                    ${crearCheckbox(modulo, "editar", "Editar")}


                    ${crearCheckbox(modulo, "eliminar", "Eliminar")}

                </tr>
            `;
    });

    tablaPermisos.innerHTML = html;
  }

  function crearCheckbox(modulo, permiso, titulo) {
    const marcado = Number(modulo[permiso]) === 1 ? "checked" : "";

    return `

            <td class="text-center">

                <div class="form-check permiso-check">

                    <input
                        class="form-check-input permiso-input"
                        type="checkbox"
                        data-modulo="${modulo.id_modulo}"
                        data-permiso="${permiso}"
                        ${marcado}>

                </div>

            </td>
        `;
  }

  //=================================================
  // TODOS
  //=================================================

  btnSeleccionarTodos.addEventListener("click", function () {
    document
      .querySelectorAll("#tablaPermisos .permiso-input")
      .forEach(function (checkbox) {
        checkbox.checked = true;
      });
  });

  btnDeseleccionarTodos.addEventListener("click", function () {
    document
      .querySelectorAll("#tablaPermisos .permiso-input")
      .forEach(function (checkbox) {
        checkbox.checked = false;
      });
  });

  //=================================================
  // GUARDAR
  //=================================================

  formRol.addEventListener("submit", async function (e) {
    e.preventDefault();

    const nombre = nombreRol.value.trim();

    if (nombre === "") {
      Swal.fire({
        icon: "warning",

        title: "Nombre requerido",

        text: "Ingrese el nombre del cargo o rol.",
      });

      nombreRol.focus();

      return;
    }

    const formData = new FormData();

    formData.append("id_rol", idRol.value);

    formData.append("nombre", nombre);

    document
      .querySelectorAll("#tablaPermisos .permiso-input")
      .forEach(function (checkbox) {
        if (!checkbox.checked) {
          return;
        }

        const idModulo = checkbox.dataset.modulo;

        const permiso = checkbox.dataset.permiso;

        formData.append(`permisos[${idModulo}][${permiso}]`, "1");
      });

    const editando = Number(idRol.value) > 0;

    const url = editando ? "ajax/actualizar_rol.php" : "ajax/registrar_rol.php";

    btnGuardarRol.disabled = true;

    btnGuardarRol.innerHTML = `

                <span
                    class="spinner-border spinner-border-sm me-1">
                </span>

                Guardando...

            `;

    try {
      const respuesta = await fetch(url, {
        method: "POST",
        body: formData,
      });

      const data = await respuesta.json();

      if (!data.success) {
        throw new Error(data.mensaje);
      }

      modalRol.hide();

      await Swal.fire({
        icon: "success",

        title: "Operación exitosa",

        text: data.mensaje,

        timer: 1800,

        showConfirmButton: false,
      });

      cargarRoles();
    } catch (error) {
      Swal.fire({
        icon: "error",

        title: "No se pudo guardar",

        text: error.message,
      });
    } finally {
      btnGuardarRol.disabled = false;

      btnGuardarRol.innerHTML = editando
        ? `
                            <i class="bi bi-check-circle me-1"></i>
                            Actualizar Rol
                          `
        : `
                            <i class="bi bi-check-circle me-1"></i>
                            Guardar Rol
                          `;
    }
  });

  //=================================================
  // ELIMINAR
  //=================================================

  tablaRoles.addEventListener("click", function (e) {
    const boton = e.target.closest(".btn-eliminar-rol");

    if (!boton) {
      return;
    }

    const id = Number(boton.dataset.id);

    const nombre = boton.dataset.nombre;

    eliminarRol(id, nombre);
  });

  async function eliminarRol(id, nombre) {
    const confirmacion = await Swal.fire({
      icon: "warning",

      title: "¿Eliminar este rol?",

      html: `

                    Se eliminará el rol:

                    <br>

                    <strong>
                        ${escapeHtml(nombre)}
                    </strong>

                    <br><br>

                    Esta acción no se puede deshacer.

                `,

      showCancelButton: true,

      confirmButtonText: "Sí, eliminar",

      cancelButtonText: "Cancelar",

      confirmButtonColor: "#dc3545",
    });

    if (!confirmacion.isConfirmed) {
      return;
    }

    const formData = new FormData();

    formData.append("id_rol", id);

    try {
      Swal.fire({
        title: "Eliminando rol...",

        allowOutsideClick: false,

        didOpen: () => {
          Swal.showLoading();
        },
      });

      const respuesta = await fetch("ajax/eliminar_rol.php", {
        method: "POST",
        body: formData,
      });

      const data = await respuesta.json();

      Swal.close();

      if (!data.success) {
        throw new Error(data.mensaje);
      }

      await Swal.fire({
        icon: "success",

        title: "Rol eliminado",

        text: data.mensaje,

        timer: 1800,

        showConfirmButton: false,
      });

      cargarRoles();
    } catch (error) {
      Swal.fire({
        icon: "error",

        title: "No se puede eliminar",

        text: error.message,
      });
    }
  }

  //=================================================
  // BÚSQUEDA
  //=================================================

  buscarRol.addEventListener("input", renderizarRoles);

  btnLimpiarBusqueda.addEventListener("click", function () {
    buscarRol.value = "";

    renderizarRoles();

    buscarRol.focus();
  });

  //=================================================
  // ESCAPE HTML
  //=================================================

  function escapeHtml(text) {
    const div = document.createElement("div");

    div.textContent = text ?? "";

    return div.innerHTML;
  }
});
