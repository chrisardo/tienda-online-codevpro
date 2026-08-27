<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_editar_proveedor.php
// Módulo: Lista de Proveedores
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EDITAR PROVEEDOR
======================================================-->

<div
    class="modal fade"
    id="modalEditarProveedor"
    tabindex="-1"
    aria-labelledby="tituloModalEditarProveedor"
    aria-hidden="true">

    <div
        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-editar-proveedor">

        <div class="modal-content border-0 shadow-lg">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header bg-primary text-white">

                <div
                    class="d-flex align-items-center gap-3">

                    <div
                        class="d-flex align-items-center justify-content-center bg-white bg-opacity-25 rounded-circle"
                        style="width:46px;height:46px;">

                        <i
                            class="bi bi-pencil-square fs-4">
                        </i>

                    </div>


                    <div>

                        <h5
                            class="modal-title mb-1"
                            id="tituloModalEditarProveedor">

                            Editar proveedor

                        </h5>


                        <small
                            class="text-white-50"
                            id="textoModalProveedor">

                            Actualiza los datos del proveedor.

                        </small>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>



            <!--=================================================
                FORMULARIO
            ==================================================-->

            <form
                id="formEditarProveedor"
                autocomplete="off">


                <!-- ID -->

                <input
                    type="hidden"
                    name="id_provedor"
                    id="editarProveedorId">


                <!--=================================================
                    BODY
                ==================================================-->

                <div class="modal-body p-4">


                    <!--=================================================
                        DATOS GENERALES
                    ==================================================-->

                    <div class="mb-4">


                        <div
                            class="d-flex align-items-center gap-2 mb-3">

                            <div
                                class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded"
                                style="width:38px;height:38px;">

                                <i
                                    class="bi bi-building">
                                </i>

                            </div>


                            <div>

                                <h6
                                    class="fw-bold mb-0">

                                    Información del proveedor

                                </h6>

                                <small
                                    class="text-muted">

                                    Datos principales del proveedor.

                                </small>

                            </div>

                        </div>


                        <div class="row g-3">


                            <!--=================================================
                                NOMBRE
                            ==================================================-->

                            <div
                                class="col-12 col-lg-6">

                                <label
                                    for="editarProveedorNombre"
                                    class="form-label fw-semibold">

                                    Nombre del proveedor

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <div class="input-group">

                                    <span
                                        class="input-group-text">

                                        <i
                                            class="bi bi-building">
                                        </i>

                                    </span>


                                    <input
                                        type="text"
                                        class="form-control"
                                        id="editarProveedorNombre"
                                        name="nombre"
                                        maxlength="150"
                                        placeholder="Nombre o razón social"
                                        required>

                                </div>

                            </div>



                            <!--=================================================
                                RUC
                            ==================================================-->

                            <div
                                class="col-12 col-lg-6">

                                <label
                                    for="editarProveedorRuc"
                                    class="form-label fw-semibold">

                                    RUC

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <div class="input-group">

                                    <span
                                        class="input-group-text">

                                        <i
                                            class="bi bi-card-text">
                                        </i>

                                    </span>


                                    <input
                                        type="text"
                                        class="form-control"
                                        id="editarProveedorRuc"
                                        name="ruc"
                                        maxlength="11"
                                        inputmode="numeric"
                                        placeholder="00000000000"
                                        required>

                                </div>


                                <div
                                    class="form-text">

                                    El RUC debe contener 11 dígitos.

                                </div>

                            </div>



                            <!--=================================================
                                CELULAR
                            ==================================================-->

                            <div
                                class="col-12 col-md-6">

                                <label
                                    for="editarProveedorCelular"
                                    class="form-label fw-semibold">

                                    Celular

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <div class="input-group">

                                    <span
                                        class="input-group-text">

                                        <i
                                            class="bi bi-phone">
                                        </i>

                                    </span>


                                    <input
                                        type="text"
                                        class="form-control"
                                        id="editarProveedorCelular"
                                        name="celular"
                                        maxlength="15"
                                        inputmode="numeric"
                                        placeholder="999999999"
                                        required>

                                </div>

                            </div>



                            <!--=================================================
                                EMAIL
                            ==================================================-->

                            <div
                                class="col-12 col-md-6">

                                <label
                                    for="editarProveedorEmail"
                                    class="form-label fw-semibold">

                                    Correo electrónico

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <div class="input-group">

                                    <span
                                        class="input-group-text">

                                        <i
                                            class="bi bi-envelope">
                                        </i>

                                    </span>


                                    <input
                                        type="email"
                                        class="form-control"
                                        id="editarProveedorEmail"
                                        name="email"
                                        maxlength="150"
                                        placeholder="proveedor@empresa.com"
                                        required>

                                </div>

                            </div>



                            <!--=================================================
                                DIRECCIÓN
                            ==================================================-->

                            <div
                                class="col-12">

                                <label
                                    for="editarProveedorDireccion"
                                    class="form-label fw-semibold">

                                    Dirección

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <div class="input-group">

                                    <span
                                        class="input-group-text align-items-start pt-2">

                                        <i
                                            class="bi bi-geo-alt">
                                        </i>

                                    </span>


                                    <textarea
                                        class="form-control"
                                        id="editarProveedorDireccion"
                                        name="direccion"
                                        rows="2"
                                        maxlength="255"
                                        placeholder="Dirección completa del proveedor"
                                        required></textarea>

                                </div>

                            </div>

                        </div>

                    </div>



                    <!--=================================================
                        UBICACIÓN
                    ==================================================-->

                    <div class="mb-2">


                        <div
                            class="d-flex align-items-center gap-2 mb-3">

                            <div
                                class="d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded"
                                style="width:38px;height:38px;">

                                <i
                                    class="bi bi-geo-alt-fill">
                                </i>

                            </div>


                            <div>

                                <h6
                                    class="fw-bold mb-0">

                                    Ubicación

                                </h6>

                                <small
                                    class="text-muted">

                                    Selecciona la ubicación geográfica del proveedor.

                                </small>

                            </div>

                        </div>


                        <div class="row g-3">


                            <!--=================================================
                                PAÍS
                            ==================================================-->

                            <div
                                class="col-12 col-md-6">

                                <label
                                    for="editarProveedorPais"
                                    class="form-label fw-semibold">

                                    País

                                </label>


                                <select
                                    class="form-select"
                                    id="editarProveedorPais"
                                    name="id_pais">

                                    <option value="">

                                        Seleccione un país

                                    </option>

                                </select>

                            </div>



                            <!--=================================================
                                DEPARTAMENTO
                            ==================================================-->

                            <div
                                class="col-12 col-md-6">

                                <label
                                    for="editarProveedorDepartamento"
                                    class="form-label fw-semibold">

                                    Departamento

                                </label>


                                <select
                                    class="form-select"
                                    id="editarProveedorDepartamento"
                                    name="id_departamento">

                                    <option value="">

                                        Seleccione un departamento

                                    </option>

                                </select>

                            </div>



                            <!--=================================================
                                PROVINCIA
                            ==================================================-->

                            <div
                                class="col-12 col-md-6">

                                <label
                                    for="editarProveedorProvincia"
                                    class="form-label fw-semibold">

                                    Provincia

                                </label>


                                <select
                                    class="form-select"
                                    id="editarProveedorProvincia"
                                    name="id_provincia"
                                    disabled>

                                    <option value="">

                                        Seleccione una provincia

                                    </option>

                                </select>

                            </div>



                            <!--=================================================
                                DISTRITO
                            ==================================================-->

                            <div
                                class="col-12 col-md-6">

                                <label
                                    for="editarProveedorDistrito"
                                    class="form-label fw-semibold">

                                    Distrito

                                </label>


                                <select
                                    class="form-select"
                                    id="editarProveedorDistrito"
                                    name="id_distrito"
                                    disabled>

                                    <option value="">

                                        Seleccione un distrito

                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>



                    <!--=================================================
                        INFORMACIÓN
                    ==================================================-->

                    <div
                        class="alert alert-info d-flex align-items-start gap-2 mt-4 mb-0">

                        <i
                            class="bi bi-info-circle-fill mt-1">
                        </i>

                        <div>

                            <strong>
                                Información
                            </strong>

                            <div class="small">

                                La fotografía del proveedor no se modifica
                                desde este formulario. Los cambios realizados
                                aquí corresponden únicamente a sus datos
                                generales y ubicación.

                            </div>

                        </div>

                    </div>

                </div>



                <!--=================================================
                    FOOTER
                ==================================================-->

                <div
                    class="modal-footer bg-light">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">

                        <i
                            class="bi bi-x-lg me-1">
                        </i>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarCambiosProveedor">

                        <i
                            class="bi bi-check-lg me-1">
                        </i>

                        Guardar cambios

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>