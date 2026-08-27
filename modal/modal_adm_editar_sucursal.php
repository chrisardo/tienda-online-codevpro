<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_adm_editar_sucursal.php
// Módulo: Sucursales
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EDITAR SUCURSAL
======================================================-->

<div
    class="modal fade"
    id="modalEditarSucursal"
    tabindex="-1"
    aria-labelledby="tituloModalEditarSucursal"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header">

                <div class="d-flex align-items-center">

                    <div
                        class="bg-warning bg-opacity-10 text-warning rounded-3
                               d-flex align-items-center justify-content-center me-3"
                        style="width:45px;height:45px;">

                        <i class="bi bi-pencil-square fs-4"></i>

                    </div>

                    <div>

                        <h5
                            class="modal-title fw-bold mb-0"
                            id="tituloModalEditarSucursal">

                            Editar Sucursal

                        </h5>

                        <small class="text-muted">

                            Actualiza los datos de la sucursal

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
                id="formEditarSucursal"
                autocomplete="off">

                <div class="modal-body">

                    <!-- ALERTA -->

                    <div
                        id="alertaModalEditarSucursal"
                        class="d-none">
                    </div>


                    <!-- ID OCULTO -->

                    <input
                        type="hidden"
                        id="idSucursalEditar"
                        name="id_sucursal">


                    <!--=================================================
                        NOMBRE
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="nombreSucursalEditar"
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
                                id="nombreSucursalEditar"
                                name="nombre"
                                maxlength="150"
                                placeholder="Ej. Sucursal Principal"
                                autocomplete="off"
                                required>

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
                        id="btnActualizarSucursal">

                        <i class="bi bi-check-circle me-1"></i>

                        Guardar cambios

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>