<!-- =====================================
MODAL CONFIRMAR ELIMINAR
===================================== -->

<div
    class="modal fade"
    id="modalEliminarProducto"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">

                <h5 class="modal-title text-danger">

                    <i class="bi bi-trash-fill me-2"></i>

                    Eliminar producto

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="text-center">

                    <i
                        class="bi bi-exclamation-triangle-fill text-warning"
                        style="font-size:60px;">
                    </i>

                    <h5 class="mt-3">

                        ¿Deseas eliminar este producto?

                    </h5>

                    <p class="text-muted mb-0">

                        Esta acción eliminará el producto del sistema.

                    </p>

                    <strong
                        id="nombreProductoEliminar"
                        class="d-block mt-2">
                    </strong>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">

                    Cancelar

                </button>

                <button
                    type="button"
                    id="btnConfirmarEliminarProducto"
                    class="btn btn-danger">

                    <i class="bi bi-trash me-1"></i>

                    Eliminar

                </button>

            </div>

        </div>

    </div>

</div>