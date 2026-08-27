<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_adm_registrar_sucursal.php
// Módulo: Sucursales
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL REGISTRAR SUCURSAL
======================================================-->

<div
    class="modal fade"
    id="modalRegistrarSucursal"
    tabindex="-1"
    aria-labelledby="tituloModalRegistrarSucursal"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header">

                <div class="d-flex align-items-center">

                    <div
                        class="bg-primary bg-opacity-10 text-primary rounded-3
                               d-flex align-items-center justify-content-center me-3"
                        style="width:45px;height:45px;">

                        <i class="bi bi-building-add fs-4"></i>

                    </div>

                    <div>

                        <h5
                            class="modal-title fw-bold mb-0"
                            id="tituloModalRegistrarSucursal">

                            Nueva Sucursal

                        </h5>

                        <small class="text-muted">

                            Registra una nueva sucursal

                        </small>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!--=================================================
                FORMULARIO
            ==================================================-->

            <form
                id="formRegistrarSucursal"
                autocomplete="off">

                <div class="modal-body">

                    <!-- ALERTA -->

                    <div
                        id="alertaModalRegistrarSucursal"
                        class="d-none">
                    </div>


                    <!--=================================================
                        NOMBRE
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="nombreSucursalRegistrar"
                            class="form-label fw-semibold">

                            Nombre de la sucursal

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-building"></i>

                            </span>

                            <input
                                type="text"
                                class="form-control"
                                id="nombreSucursalRegistrar"
                                name="nombre"
                                maxlength="150"
                                placeholder="Ej. Sucursal Principal"
                                autocomplete="off"
                                required>

                        </div>

                        <div class="form-text">

                            Ingresa el nombre con el que deseas
                            identificar la sucursal.

                        </div>

                    </div>

                </div>


                <!--=================================================
                    FOOTER
                ==================================================-->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarSucursal">

                        <i class="bi bi-check-circle me-1"></i>

                        Guardar sucursal

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>