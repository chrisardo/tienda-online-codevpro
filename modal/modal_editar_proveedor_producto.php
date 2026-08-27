<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_editar_proveedor_producto.php
// Módulo: Productos del Proveedor
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EDITAR PROVEEDOR DEL PRODUCTO
======================================================-->

<div
    class="modal fade"
    id="modalEditarProveedorProducto"
    tabindex="-1"
    aria-labelledby="modalEditarProveedorProductoLabel"
    aria-hidden="true">

    <div
        class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header">


                <div
                    class="d-flex align-items-center gap-3">


                    <!-- ICONO -->

                    <div
                        class="d-flex align-items-center justify-content-center
                               rounded-3 bg-warning-subtle text-warning"
                        style="
                            width: 44px;
                            height: 44px;
                        ">

                        <i class="bi bi-building-fill fs-5"></i>

                    </div>


                    <!-- TITULO -->

                    <div>

                        <h5
                            class="modal-title mb-0"
                            id="modalEditarProveedorProductoLabel">

                            Editar proveedor

                        </h5>


                        <small class="text-muted">

                            Cambia el proveedor asociado al producto.

                        </small>

                    </div>


                </div>


                <!-- CERRAR -->

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>


            </div>



            <!--=================================================
                BODY
            ==================================================-->

            <div class="modal-body">


                <!--=================================================
                    FORMULARIO
                ==================================================-->

                <form
                    id="formEditarProveedorProducto"
                    autocomplete="off">


                    <!--=============================================
                        ID PRODUCTO
                    ==============================================-->

                    <input
                        type="hidden"
                        id="editarProveedorIdProducto"
                        value="">


                    <!--=============================================
                        INFORMACIÓN
                    ==============================================-->

                    <div
                        class="alert alert-light border d-flex
                               align-items-start gap-3 mb-4">


                        <i
                            class="bi bi-info-circle-fill
                                   text-primary fs-5 mt-1"></i>


                        <div>

                            <div class="fw-semibold">

                                Proveedor del producto

                            </div>


                            <div
                                class="text-muted small"
                                id="editarProveedorNombreProducto">

                                Cargando información del producto...

                            </div>

                        </div>


                    </div>



                    <!--=============================================
                        PROVEEDOR ACTUAL
                    ==============================================-->

                    <div class="mb-3">


                        <label
                            class="form-label fw-semibold">

                            Proveedor actual

                        </label>


                        <div
                            class="form-control bg-light"
                            id="editarProveedorActual">

                            <span class="text-muted">

                                Cargando...

                            </span>

                        </div>


                    </div>



                    <!--=============================================
                        NUEVO PROVEEDOR
                    ==============================================-->

                    <div class="mb-3">


                        <label
                            for="editarProveedorNuevo"
                            class="form-label fw-semibold">

                            Nuevo proveedor

                            <span class="text-danger">

                                *

                            </span>

                        </label>


                        <select
                            class="form-select"
                            id="editarProveedorIdProveedor">

                            <option value="">
                                Cargando proveedores...
                            </option>

                        </select>


                        <div
                            class="form-text">

                            Selecciona el proveedor que deseas asociar
                            a este producto.

                        </div>


                        <div
                            class="invalid-feedback">

                            Debes seleccionar un proveedor.

                        </div>


                    </div>



                    <!--=============================================
                        ESTADO DE CARGA
                    ==============================================-->

                    <div
                        id="estadoEditarProveedorProducto"
                        class="d-none">


                        <div
                            class="d-flex align-items-center gap-2
                                   text-muted small">

                            <div
                                class="spinner-border spinner-border-sm
                                       text-primary"
                                role="status">

                                <span class="visually-hidden">

                                    Cargando...

                                </span>

                            </div>


                            <span id="textoEstadoEditarProveedorProducto">

                                Cargando información...

                            </span>

                        </div>


                    </div>



                    <!--=============================================
                        MENSAJE
                    ==============================================-->

                    <div
                        id="mensajeEditarProveedorProducto"
                        class="d-none mt-3"></div>


                </form>


            </div>



            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer">


                <!-- CANCELAR -->

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    id="btnCancelarEditarProveedorProducto">

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>



                <!-- GUARDAR -->

                <button
                    type="button"
                    class="btn btn-warning"
                    id="btnGuardarProveedorProducto">

                    <i class="bi bi-check-lg me-1"></i>

                    Guardar cambios

                </button>


            </div>


        </div>

    </div>

</div>