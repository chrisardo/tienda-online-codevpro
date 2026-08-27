<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_editar_imagen_empleado.php
// Módulo: Editar Imagen del Empleado
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EDITAR IMAGEN DEL EMPLEADO
======================================================-->

<div class="modal fade"
    id="modalEditarImagenEmpleado"
    tabindex="-1"
    aria-labelledby="modalEditarImagenEmpleadoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header">

                <div>

                    <h5 class="modal-title"
                        id="modalEditarImagenEmpleadoLabel">

                        <i class="bi bi-camera me-2"></i>

                        Editar imagen del empleado

                    </h5>

                    <div class="text-muted small mt-1">

                        Actualiza la fotografía del empleado.

                    </div>

                </div>


                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!--=================================================
                FORMULARIO
            ==================================================-->

            <form id="formEditarImagenEmpleado"
                enctype="multipart/form-data"
                novalidate>

                <div class="modal-body">


                    <!--=========================================
                        ID EMPLEADO
                    ==========================================-->

                    <input type="hidden"
                        id="editarImagenIdEmpleado"
                        name="id_empleado"
                        value="">


                    <!--=========================================
                        VISTA PREVIA
                    ==========================================-->

                    <div class="text-center mb-4">

                        <div id="contenedorVistaPreviaImagenEmpleado"
                            class="d-flex
                                    justify-content-center
                                    align-items-center">

                            <div class="empleado-imagen-modal-placeholder">

                                <i class="bi bi-person-fill"></i>

                            </div>

                        </div>

                    </div>


                    <!--=========================================
                        SELECCIONAR IMAGEN
                    ==========================================-->

                    <div class="mb-3">

                        <label for="imagenEmpleadoEditar"
                            class="form-label fw-semibold">

                            Nueva imagen

                        </label>

                        <input type="file"
                            class="form-control"
                            id="imagenEmpleadoEditar"
                            name="imagen"
                            accept="image/jpeg,image/png,image/webp">

                        <div class="form-text">

                            Formatos permitidos:
                            JPG, JPEG, PNG y WEBP.

                            Tamaño máximo: 2.7 MB.

                        </div>

                        <div id="errorImagenEmpleadoEditar"
                            class="invalid-feedback">
                        </div>

                    </div>


                    <!--=========================================
                        INFORMACIÓN
                    ==========================================-->

                    <div class="alert alert-info d-flex
                                align-items-start
                                gap-2
                                mb-0">

                        <i class="bi bi-info-circle-fill mt-1"></i>

                        <div class="small">

                            La nueva imagen reemplazará la fotografía
                            actual del empleado.

                        </div>

                    </div>


                </div>


                <!--=================================================
                    FOOTER
                ==================================================-->

                <div class="modal-footer">

                    <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-lg me-1"></i>

                        Cancelar

                    </button>


                    <button type="submit"
                        class="btn btn-primary"
                        id="btnGuardarImagenEmpleado">

                        <i class="bi bi-check-lg me-1"></i>

                        Guardar imagen

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
