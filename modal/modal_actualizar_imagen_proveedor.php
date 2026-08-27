<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_actualizar_imagen_proveedor.php
// Módulo: Lista de Proveedores
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL ACTUALIZAR IMAGEN DEL PROVEEDOR
======================================================-->

<div
    class="modal fade"
    id="modalActualizarImagenProveedor"
    tabindex="-1"
    aria-labelledby="modalActualizarImagenProveedorLabel"
    aria-hidden="true">

    <div
        class="modal-dialog modal-lg modal-dialog-centered">

        <div
            class="modal-content border-0 shadow-lg">


            <!--=================================================
                HEADER
            ==================================================-->

            <div
                class="modal-header">

                <div
                    class="d-flex align-items-center gap-3">

                    <div
                        class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary"
                        style="width: 44px; height: 44px;">

                        <i
                            class="bi bi-image fs-4">
                        </i>

                    </div>

                    <div>

                        <h5
                            class="modal-title mb-1"
                            id="modalActualizarImagenProveedorLabel">

                            Actualizar imagen

                        </h5>

                        <small
                            class="text-muted"
                            id="textoModalImagenProveedor">

                            Actualiza la imagen del proveedor seleccionado.

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
                id="formActualizarImagenProveedor"
                enctype="multipart/form-data">

                <!--=================================================
                    BODY
                ==================================================-->

                <div
                    class="modal-body">


                    <!--=================================================
                        ID PROVEEDOR
                    ==================================================-->

                    <input
                        type="hidden"
                        id="actualizarImagenProveedorId"
                        name="id_provedor"
                        value="">


                    <!--=================================================
                        NOMBRE DEL PROVEEDOR
                    ==================================================-->

                    <div
                        class="text-center mb-4">

                        <div
                            class="fw-semibold text-dark"
                            id="nombreProveedorImagen">

                            Proveedor

                        </div>

                        <small
                            class="text-muted">

                            Imagen del proveedor

                        </small>

                    </div>


                    <!--=================================================
                        CONTENEDOR DE IMAGEN
                    ==================================================-->

                    <div
                        class="d-flex justify-content-center mb-4">

                        <div
                            id="contenedorVistaPreviaImagenProveedor"
                            class="position-relative d-flex align-items-center justify-content-center rounded-circle overflow-hidden border bg-light"
                            style="
                                width: 180px;
                                height: 180px;
                            ">


                            <!--=========================================
                                PLACEHOLDER
                            ==========================================-->

                            <div
                                id="placeholderImagenProveedor"
                                class="text-center text-muted px-3">

                                <i
                                    class="bi bi-person-badge fs-1 d-block mb-2">
                                </i>

                                <small>

                                    Sin imagen

                                </small>

                            </div>


                            <!--=========================================
                                IMAGEN
                            ==========================================-->

                            <img
                                id="vistaPreviaImagenProveedor"
                                src=""
                                alt="Imagen del proveedor"
                                class="w-100 h-100 object-fit-cover d-none">


                        </div>

                    </div>


                    <!--=================================================
                        INFORMACIÓN
                    ==================================================-->

                    <div
                        class="alert alert-light border d-flex align-items-start gap-2 mb-4">

                        <i
                            class="bi bi-info-circle text-primary mt-1">
                        </i>

                        <div
                            class="small text-muted">

                            <div
                                class="fw-semibold text-dark mb-1">

                                Requisitos de imagen

                            </div>

                            <div>

                                Formatos permitidos: JPG, JPEG, PNG y WEBP.

                            </div>

                            <div>

                                Tamaño máximo: 2.7 MB.

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                        INPUT IMAGEN
                    ==================================================-->

                    <input
                        type="file"
                        id="imagenProveedor"
                        name="imagen"
                        class="d-none"
                        accept="image/jpeg,image/jpg,image/png,image/webp">


                    <!--=================================================
                        BOTONES DE IMAGEN
                    ==================================================-->

                    <div
                        class="d-flex justify-content-center gap-2 flex-wrap">

                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            id="btnSeleccionarImagenProveedor">

                            <i
                                class="bi bi-upload me-1">
                            </i>

                            Seleccionar imagen

                        </button>


                        <button
                            type="button"
                            class="btn btn-outline-danger"
                            id="btnEliminarImagenProveedor">

                            <i
                                class="bi bi-trash3 me-1">
                            </i>

                            Eliminar imagen

                        </button>

                    </div>


                    <!--=================================================
                        NOMBRE DEL ARCHIVO
                    ==================================================-->

                    <div
                        class="text-center mt-3">

                        <small
                            class="text-muted"
                            id="nombreArchivoImagenProveedor">

                            No se ha seleccionado una nueva imagen.

                        </small>

                    </div>


                    <!--=================================================
                        MENSAJE DE VALIDACIÓN
                    ==================================================-->

                    <div
                        id="mensajeImagenProveedor"
                        class="alert alert-danger d-none mt-3 mb-0"
                        role="alert">

                        <i
                            class="bi bi-exclamation-triangle-fill me-1">
                        </i>

                        <span
                            id="textoMensajeImagenProveedor">
                        </span>

                    </div>


                </div>


                <!--=================================================
                    FOOTER
                ==================================================-->

                <div
                    class="modal-footer">


                    <!--=============================================
                        CANCELAR
                    ==============================================-->

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="btnCancelarImagenProveedor"
                        data-bs-dismiss="modal">

                        <i
                            class="bi bi-x-lg me-1">
                        </i>

                        Cancelar

                    </button>


                    <!--=============================================
                        GUARDAR
                    ==============================================-->

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarImagenProveedor">

                        <i
                            class="bi bi-check-lg me-1">
                        </i>

                        Guardar imagen

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>