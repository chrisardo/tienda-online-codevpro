<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_editar_empleados.php
// Módulo: Editar Empleado
//=====================================================

// Este archivo es únicamente la estructura del modal.
// Los datos del empleado serán cargados mediante AJAX.
?>

<!-- =====================================================
     MODAL EDITAR EMPLEADO
====================================================== -->

<div class="modal fade"
    id="modalEditarEmpleado"
    tabindex="-1"
    aria-labelledby="modalEditarEmpleadoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-editar-empleado">

        <div class="modal-content border-0 shadow">

            <!-- =================================================
                 HEADER
            ================================================== -->

            <div class="modal-header bg-white border-bottom">

                <div class="d-flex align-items-center">

                    <div class="rounded-circle
                                bg-primary
                                bg-opacity-10
                                text-primary
                                d-flex
                                align-items-center
                                justify-content-center
                                me-3"
                        style="width:42px;height:42px;">

                        <i class="bi bi-pencil-square fs-5"></i>

                    </div>

                    <div>

                        <h5 class="modal-title fw-bold mb-0"
                            id="modalEditarEmpleadoLabel">

                            Editar empleado

                        </h5>

                        <small class="text-muted">

                            Actualiza los datos personales, laborales y permisos del empleado.

                        </small>

                    </div>

                </div>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!-- =================================================
                 FORMULARIO
            ================================================== -->

            <form id="formEditarEmpleado"
                autocomplete="off">

                <div class="modal-body p-3">

                    <!-- =================================================
                         MENSAJE GENERAL
                    ================================================== -->

                    <div id="mensajeEditarEmpleado"
                        class="mb-3"
                        style="display:none;">
                    </div>


                    <!-- =================================================
                         DATOS PERSONALES
                    ================================================== -->

                    <div class="card border-0 shadow-sm mb-3">

                        <div class="card-body">

                            <div class="row g-3">


                                <!-- =========================================
                                     DNI
                                ========================================== -->

                                <div class="col-12 col-md-6">

                                    <label for="editar_dni"
                                        class="form-label fw-semibold">

                                        DNI

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-person-vcard"></i>

                                        </span>

                                        <input type="text"
                                            class="form-control"
                                            id="editar_dni"
                                            name="dni"
                                            maxlength="20"
                                            placeholder="Ingrese el DNI"
                                            required>

                                    </div>

                                    <div class="invalid-feedback">

                                        Ingrese un DNI válido.

                                    </div>

                                </div>


                                <!-- =========================================
                                     CELULAR
                                ========================================== -->

                                <div class="col-12 col-md-6">

                                    <label for="editar_celular"
                                        class="form-label fw-semibold">

                                        Celular

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-phone"></i>

                                        </span>

                                        <input type="text"
                                            class="form-control"
                                            id="editar_celular"
                                            name="celular"
                                            maxlength="15"
                                            placeholder="Ingrese el celular"
                                            required>

                                    </div>

                                    <div class="invalid-feedback">

                                        Ingrese un número de celular válido.

                                    </div>

                                </div>


                                <!-- =========================================
                                     NOMBRES
                                ========================================== -->

                                <div class="col-12 col-md-6">

                                    <label for="editar_nombre"
                                        class="form-label fw-semibold">

                                        Nombres

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-person"></i>

                                        </span>

                                        <input type="text"
                                            class="form-control"
                                            id="editar_nombre"
                                            name="nombre"
                                            maxlength="150"
                                            placeholder="Ingrese los nombres"
                                            required>

                                    </div>

                                    <div class="invalid-feedback">

                                        Ingrese los nombres del empleado.

                                    </div>

                                </div>


                                <!-- =========================================
                                     APELLIDOS
                                ========================================== -->

                                <div class="col-12 col-md-6">

                                    <label for="editar_apellido"
                                        class="form-label fw-semibold">

                                        Apellidos

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-person-lines-fill"></i>

                                        </span>

                                        <input type="text"
                                            class="form-control"
                                            id="editar_apellido"
                                            name="apellido"
                                            maxlength="150"
                                            placeholder="Ingrese los apellidos"
                                            required>

                                    </div>

                                    <div class="invalid-feedback">

                                        Ingrese los apellidos del empleado.

                                    </div>

                                </div>


                                <!-- =========================================
                                     EMAIL
                                ========================================== -->

                                <div class="col-12">

                                    <label for="editar_email"
                                        class="form-label fw-semibold">

                                        Correo electrónico

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-envelope"></i>

                                        </span>

                                        <input type="email"
                                            class="form-control"
                                            id="editar_email"
                                            name="email"
                                            maxlength="150"
                                            placeholder="ejemplo@correo.com"
                                            required>

                                    </div>

                                    <div class="invalid-feedback">

                                        Ingrese un correo electrónico válido.

                                    </div>

                                </div>


                                <!-- =========================================
                                     DIRECCIÓN
                                ========================================== -->

                                <div class="col-12 col-md-6">

                                    <label for="editar_direccion"
                                        class="form-label fw-semibold">

                                        Dirección

                                        <span class="text-danger">*</span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-geo-alt"></i>

                                        </span>

                                        <input type="text"
                                            class="form-control"
                                            id="editar_direccion"
                                            name="direccion"
                                            maxlength="255"
                                            placeholder="Ingrese la dirección"
                                            required>

                                    </div>

                                    <div class="invalid-feedback">

                                        Ingrese la dirección del empleado.

                                    </div>

                                </div>


                                <!-- =========================================
                                     CONTRASEÑA
                                ========================================== -->

                                <div class="col-12 col-md-6">

                                    <label for="editar_contrasena"
                                        class="form-label fw-semibold">

                                        Contraseña

                                        <span class="text-muted fw-normal">

                                            (opcional)

                                        </span>

                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">

                                            <i class="bi bi-lock"></i>

                                        </span>

                                        <input type="password"
                                            class="form-control"
                                            id="editar_contrasena"
                                            name="contrasena"
                                            maxlength="255"
                                            placeholder="Nueva contraseña"
                                            autocomplete="new-password">

                                        <button type="button"
                                            class="btn btn-outline-secondary"
                                            id="btnMostrarContrasenaEditar"
                                            title="Mostrar contraseña"
                                            tabindex="-1">

                                            <i class="bi bi-eye"
                                                id="iconoMostrarContrasenaEditar">
                                            </i>

                                        </button>

                                    </div>

                                    <div class="form-text">

                                        Déjela vacía para conservar la contraseña actual.

                                    </div>

                                    <div class="invalid-feedback">

                                        La contraseña ingresada no es válida.

                                    </div>

                                </div>
                                <!-- =========================================
                                     PAÍS
                                ========================================== -->

                                <div class="col-12 col-md-6 col-lg-3">

                                    <label for="editar_id_pais"
                                        class="form-label fw-semibold">

                                        País

                                        <span class="text-danger">*</span>

                                    </label>

                                    <select class="form-select"
                                        id="editar_id_pais"
                                        name="id_pais"
                                        required>

                                        <option value="">

                                            Seleccionar país

                                        </option>

                                    </select>

                                    <div class="invalid-feedback">

                                        Seleccione un país.

                                    </div>

                                </div>


                                <!-- =========================================
                                     DEPARTAMENTO
                                ========================================== -->

                                <div class="col-12 col-md-6 col-lg-3">

                                    <label for="editar_id_departamento"
                                        class="form-label fw-semibold">

                                        Departamento

                                        <span class="text-danger">*</span>

                                    </label>

                                    <select class="form-select"
                                        id="editar_id_departamento"
                                        name="id_departamento"
                                        required
                                        disabled>

                                        <option value="">

                                            Seleccionar departamento

                                        </option>

                                    </select>

                                    <div class="invalid-feedback">

                                        Seleccione un departamento.

                                    </div>

                                </div>


                                <!-- =========================================
                                     PROVINCIA
                                ========================================== -->

                                <div class="col-12 col-md-6 col-lg-3">

                                    <label for="editar_id_provincia"
                                        class="form-label fw-semibold">

                                        Provincia

                                        <span class="text-danger">*</span>

                                    </label>

                                    <select class="form-select"
                                        id="editar_id_provincia"
                                        name="id_provincia"
                                        required
                                        disabled>

                                        <option value="">

                                            Seleccionar provincia

                                        </option>

                                    </select>

                                    <div class="invalid-feedback">

                                        Seleccione una provincia.

                                    </div>

                                </div>


                                <!-- =========================================
                                     DISTRITO
                                ========================================== -->

                                <div class="col-12 col-md-6 col-lg-3">

                                    <label for="editar_id_distrito"
                                        class="form-label fw-semibold">

                                        Distrito

                                        <span class="text-danger">*</span>

                                    </label>

                                    <select class="form-select"
                                        id="editar_id_distrito"
                                        name="id_distrito"
                                        required
                                        disabled>

                                        <option value="">

                                            Seleccionar distrito

                                        </option>

                                    </select>

                                    <div class="invalid-feedback">

                                        Seleccione un distrito.

                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- =================================================
                         INFORMACIÓN LABORAL
                    ================================================== -->

                    <div class="card border-0 shadow-sm mb-3">

                        <div class="card-header bg-white border-bottom py-2">

                            <div class="d-flex align-items-center">

                                <div class="rounded-circle
                                            bg-warning
                                            bg-opacity-10
                                            text-warning
                                            d-flex
                                            align-items-center
                                            justify-content-center
                                            me-3"
                                    style="width:42px;height:42px;">

                                    <i class="bi bi-briefcase-fill fs-5"></i>

                                </div>

                                <div>

                                    <h5 class="fw-bold mb-0">

                                        Información laboral

                                    </h5>

                                    <small class="text-muted">

                                        Configura el cargo y estado del empleado.

                                    </small>

                                </div>

                            </div>

                        </div>


                        <div class="card-body">

                            <div class="row g-3">


                                <!-- =========================================
                                     ROL
                                ========================================== -->

                                <div class="col-12 col-md-6">

                                    <label for="editar_id_rol"
                                        class="form-label fw-semibold">

                                        Cargo / Rol

                                        <span class="text-danger">*</span>

                                    </label>

                                    <select class="form-select"
                                        id="editar_id_rol"
                                        name="id_rol"
                                        required>

                                        <option value="">

                                            Seleccionar cargo / rol

                                        </option>

                                    </select>

                                    <div class="form-text">

                                        Los permisos se cargarán según el rol seleccionado.

                                    </div>

                                    <div class="invalid-feedback">

                                        Seleccione un cargo o rol.

                                    </div>

                                </div>


                                <!-- =========================================
                                     ESTADO
                                ========================================== -->

                                <div class="col-12 col-md-6">

                                    <label for="editar_estado"
                                        class="form-label fw-semibold">

                                        Estado

                                        <span class="text-danger">*</span>

                                    </label>

                                    <select class="form-select"
                                        id="editar_estado"
                                        name="estado"
                                        required>

                                        <option value="ACTIVO">

                                            ACTIVO

                                        </option>

                                        <option value="INACTIVO">

                                            INACTIVO

                                        </option>

                                    </select>

                                    <div class="form-text">

                                        Un empleado inactivo no debería tener acceso a las operaciones del sistema.

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         PERMISOS
                    ================================================== -->

                    <div class="card border-0 shadow-sm mb-3">

                        <div class="card-header bg-white border-bottom py-2">

                            <div class="d-flex flex-wrap
                                        justify-content-between
                                        align-items-center
                                        gap-3">

                                <div class="d-flex align-items-center">

                                    <div class="rounded-circle
                                                bg-danger
                                                bg-opacity-10
                                                text-danger
                                                d-flex
                                                align-items-center
                                                justify-content-center
                                                me-3"
                                        style="width:42px;height:42px;">

                                        <i class="bi bi-shield-lock-fill fs-5"></i>

                                    </div>

                                    <div>

                                        <h5 class="fw-bold mb-0">

                                            Permisos del empleado

                                        </h5>

                                        <small class="text-muted">

                                            Los permisos corresponden al rol seleccionado.

                                        </small>

                                    </div>

                                </div>


                                <!-- =====================================
                                     ESTADO CARGA
                                ====================================== -->

                                <div id="editar_estadoCargaPermisos"
                                    class="text-muted small">

                                    <i class="bi bi-info-circle me-1"></i>

                                    Seleccione un rol para consultar sus permisos.

                                </div>

                            </div>

                        </div>


                        <div class="card-body p-0">


                            <!-- =========================================
                                 MENSAJE SIN ROL
                            ========================================== -->

                            <div id="editar_mensajeSinRol"
                                class="p-4 text-center">

                                <div class="mb-3">

                                    <i class="bi bi-shield-exclamation
                                              text-muted"
                                        style="font-size:3rem;">
                                    </i>

                                </div>

                                <h6 class="fw-semibold">

                                    Seleccione un cargo o rol

                                </h6>

                                <p class="text-muted mb-0">

                                    Los permisos asociados al rol aparecerán aquí.

                                </p>

                            </div>


                            <!-- =========================================
                                 TABLA DE PERMISOS
                            ========================================== -->

                            <div id="editar_contenedorPermisos"
                                class="table-responsive"
                                style="display:none;">

                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-light">

                                        <tr>

                                            <th class="ps-4">

                                                Módulo

                                            </th>

                                            <th class="text-center">

                                                <div>

                                                    <i class="bi bi-eye me-1"></i>

                                                    Ver

                                                </div>

                                            </th>

                                            <th class="text-center">

                                                <div>

                                                    <i class="bi bi-plus-circle me-1"></i>

                                                    Crear

                                                </div>

                                            </th>

                                            <th class="text-center">

                                                <div>

                                                    <i class="bi bi-pencil me-1"></i>

                                                    Editar

                                                </div>

                                            </th>

                                            <th class="text-center">

                                                <div>

                                                    <i class="bi bi-trash me-1"></i>

                                                    Eliminar

                                                </div>

                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody id="editar_tablaPermisos">

                                        <!--
                                            Los permisos serán cargados
                                            mediante AJAX.
                                        -->

                                    </tbody>

                                </table>

                            </div>


                            <!-- =========================================
                                 SIN PERMISOS
                            ========================================== -->

                            <div id="editar_mensajeSinPermisos"
                                class="p-4 text-center"
                                style="display:none;">

                                <div class="mb-3">

                                    <i class="bi bi-shield-x
                                              text-warning"
                                        style="font-size:3rem;">
                                    </i>

                                </div>

                                <h6 class="fw-semibold">

                                    Este rol no tiene permisos configurados

                                </h6>

                                <p class="text-muted mb-0">

                                    Configure los permisos desde la sección
                                    <a href="roles.php">

                                        Cargos y Roles

                                    </a>.

                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     CAMPOS OCULTOS
                ================================================== -->

                <input type="hidden"
                    id="editar_id_empleado"
                    name="id_empleado"
                    value="">


                <!-- =================================================
                     FOOTER
                ================================================== -->

                <div class="modal-footer bg-light border-top">

                    <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-2"></i>

                        Cancelar

                    </button>


                    <button type="submit"
                        class="btn btn-primary px-4"
                        id="btnGuardarCambiosEmpleado">

                        <i class="bi bi-check-circle me-2"></i>

                        Guardar cambios

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>