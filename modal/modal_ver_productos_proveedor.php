<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_ver_productos_proveedor.php
// Módulo: Productos del Proveedor
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL VER PRODUCTO
======================================================-->

<div
    class="modal fade"
    id="modalVerProductoProveedor"
    tabindex="-1"
    aria-labelledby="modalVerProductoProveedorLabel"
    aria-hidden="true">


    <div
        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">


        <div class="modal-content">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header">


                <div class="d-flex align-items-center gap-3">


                    <div
                        class="d-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary"
                        style="
                            width: 45px;
                            height: 45px;
                        ">

                        <i class="bi bi-box-seam-fill fs-4"></i>

                    </div>


                    <div>

                        <h5
                            class="modal-title mb-0"
                            id="modalVerProductoProveedorLabel">

                            Detalle del producto

                        </h5>


                        <small class="text-muted">

                            Información del producto asociado al proveedor

                        </small>

                    </div>


                </div>


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


                <div id="contenidoDetalleProductoProveedor">


                    <!--=========================================
                        LOADING INICIAL
                    ==========================================-->

                    <div class="text-center py-5">


                        <div
                            class="spinner-border text-primary mb-3"
                            role="status">

                            <span class="visually-hidden">

                                Cargando...

                            </span>

                        </div>


                        <div class="text-muted">

                            Cargando información del producto...

                        </div>


                    </div>


                </div>


            </div>


            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer">


                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-lg me-1"></i>

                    Cerrar

                </button>


            </div>


        </div>


    </div>


</div>