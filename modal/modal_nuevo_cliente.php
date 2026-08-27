<?php
//=====================================================
// CoDevPro Technology
// modal/modal_nuevo_cliente.php
//=====================================================
?>

<div class="modal fade"
    id="modalNuevoCliente"
    tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content border-0 shadow-lg">

            <!--=====================================
            HEADER
            ======================================-->

            <div class="modal-header bg-primary text-white">

                <h4 class="modal-title fw-bold">

                    <i class="bi bi-person-plus-fill me-2"></i>

                    Registrar Nuevo Cliente

                </h4>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">

                </button>

            </div>

            <!--=====================================
            FORMULARIO
            ======================================-->

            <form
                id="formNuevoCliente"
                enctype="multipart/form-data"
                novalidate>

                <div class="modal-body">

                    <div class="row g-4">

                        <!--=====================================
                        FOTO PERFIL
                        ======================================-->

                        <div class="col-lg-3">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-body text-center">

                                    <h6 class="fw-bold mb-3">

                                        Foto del Cliente

                                    </h6>

                                    <img
                                        src="assets/img/sin_imagen.png"
                                        id="previewCliente"
                                        class="rounded-circle border shadow-sm mb-3"
                                        width="180"
                                        height="180"
                                        style="object-fit:cover;">

                                    <input
                                        type="file"
                                        class="form-control"
                                        id="imagenCliente"
                                        name="imagenCliente"
                                        accept="image/*">

                                    <small class="text-muted d-block mt-2">

                                        JPG, PNG o WEBP

                                    </small>

                                </div>

                            </div>

                        </div>

                        <!--=====================================
                        DATOS GENERALES
                        ======================================-->

                        <div class="col-lg-9">

                            <div class="card border-0 shadow-sm">

                                <div class="card-header bg-light">

                                    <h6 class="mb-0 fw-bold">

                                        <i class="bi bi-person-vcard-fill text-primary me-2"></i>

                                        Información Personal

                                    </h6>

                                </div>

                                <div class="card-body">

                                    <div class="row g-3">

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Nombre Completo

                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                name="nombre"
                                                required>

                                            <div class="invalid-feedback">

                                                Ingrese el nombre.

                                            </div>

                                        </div>

                                        <div class="col-md-3">

                                            <label class="form-label">

                                                DNI / RUC

                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                name="dni_ruc"
                                                id="dniCliente"
                                                maxlength="11"
                                                required>

                                        </div>

                                        <div class="col-md-3">

                                            <label class="form-label">

                                                Celular

                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                name="celular"
                                                id="celularCliente"
                                                maxlength="9"
                                                required>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Correo Electrónico

                                            </label>

                                            <input
                                                type="email"
                                                class="form-control"
                                                name="email"
                                                required>

                                            <div class="invalid-feedback">

                                                Ingrese un correo válido.

                                            </div>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Rubro

                                            </label>

                                            <select
                                                class="form-select"
                                                name="id_rubro">

                                                <option value="">

                                                    Seleccionar

                                                </option>

                                            </select>

                                        </div>

                                        <div class="col-12">

                                            <label class="form-label">

                                                Dirección

                                            </label>

                                            <input
                                                type="text"
                                                class="form-control"
                                                name="direccion">

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!--=====================================
                        UBICACIÓN
                        ======================================-->

                        <div class="col-lg-7">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-header bg-light">

                                    <h6 class="mb-0 fw-bold">

                                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>

                                        Ubicación

                                    </h6>

                                </div>

                                <div class="card-body">

                                    <div class="row g-3">

                                        <div class="col-md-4">

                                            <label class="form-label">

                                                País

                                            </label>

                                            <select
                                                class="form-select"
                                                name="id_pais"
                                                id="paisCliente"
                                                required>

                                                <option value="">

                                                    Seleccionar
                                                </option>

                                            </select>

                                        </div>

                                        <div class="col-md-4">

                                            <label class="form-label">

                                                Departamento

                                            </label>

                                            <select
                                                class="form-select"
                                                name="id_departamento"
                                                id="departamentoCliente"
                                                required>

                                                <option value="">

                                                    Seleccionar
                                                </option>

                                            </select>

                                        </div>

                                        <div class="col-md-4">

                                            <label class="form-label">

                                                Provincia

                                            </label>

                                            <select
                                                class="form-select"
                                                name="id_provincia"
                                                id="provinciaCliente"
                                                required>

                                                <option value="">

                                                    Seleccionar
                                                </option>

                                            </select>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Distrito

                                            </label>

                                            <select
                                                class="form-select"
                                                name="id_distrito"
                                                id="distritoCliente"
                                                required>

                                                <option value="">

                                                    Seleccionar
                                                </option>

                                            </select>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Estado

                                            </label>

                                            <select
                                                class="form-select"
                                                name="estado">

                                                <option value="ACTIVO" selected>
                                                    ACTIVO
                                                </option>

                                                <option value="INACTIVO">
                                                    INACTIVO
                                                </option>

                                            </select>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!--=====================================
                        SEGURIDAD
                        ======================================-->

                        <div class="col-lg-5">

                            <div class="card border-0 shadow-sm h-100">

                                <div class="card-header bg-light">

                                    <h6 class="mb-0 fw-bold">

                                        <i class="bi bi-shield-lock-fill text-success me-2"></i>

                                        Seguridad

                                    </h6>

                                </div>

                                <div class="card-body">

                                    <div class="mb-3">

                                        <label class="form-label">

                                            Contraseña

                                        </label>

                                        <div class="input-group">

                                            <input
                                                type="password"
                                                class="form-control"
                                                id="passwordCliente"
                                                name="password"
                                                minlength="6"
                                                required>

                                            <button
                                                class="btn btn-outline-secondary"
                                                type="button"
                                                id="btnVerPasswordCliente">

                                                <i class="bi bi-eye" id="iconoPasswordCliente"></i>

                                            </button>

                                        </div>

                                    </div>

                                    <div class="mb-3">

                                        <label class="form-label">

                                            Confirmar Contraseña

                                        </label>

                                        <div class="input-group">

                                            <input
                                                type="password"
                                                class="form-control"
                                                id="confirmarPasswordCliente"
                                                name="confirmar_password"
                                                minlength="6"
                                                required>

                                            <button
                                                class="btn btn-outline-secondary"
                                                type="button"
                                                id="btnVerConfirmarPasswordCliente">

                                                <i class="bi bi-eye" id="iconoConfirmarPasswordCliente"></i>

                                            </button>


                                        </div>

                                    </div>

                                    <div class="alert alert-info mb-0">

                                        <i class="bi bi-info-circle-fill me-2"></i>

                                        La contraseña debe contener mínimo 8 caracteres.

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!--=====================================
                FOOTER
                ======================================-->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="reset"
                        class="btn btn-warning">

                        <i class="bi bi-arrow-clockwise me-2"></i>

                        Limpiar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-check-circle-fill me-2"></i>

                        Registrar Cliente

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>