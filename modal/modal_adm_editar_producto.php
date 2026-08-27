<!-- =====================================
MODAL EDITAR PRODUCTO
===================================== -->

<div
    class="modal fade"
    id="modalEditarProducto"
    tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">


            <!-- HEADER -->
            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-pencil-square me-2"></i>

                    Editar Producto

                </h5>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>


            </div>



            <!-- BODY -->

            <div
                class="modal-body"
                id="contenidoEditarProducto">


                <div class="text-center py-5">


                    <div class="spinner-border text-primary">
                    </div>


                    <div class="mt-3">

                        Cargando información...

                    </div>


                </div>


            </div>




            <!-- FOOTER -->

            <div class="modal-footer">


                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">


                    <i class="bi bi-x-circle me-1"></i>

                    Cancelar


                </button>



                <button
                    type="button"
                    id="btnGuardarCambiosProducto"
                    class="btn btn-primary">


                    <i class="bi bi-save me-1"></i>

                    Guardar Cambios


                </button>



            </div>



        </div>

    </div>

</div>