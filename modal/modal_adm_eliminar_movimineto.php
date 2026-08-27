<!--=====================================================
    MODAL: ELIMINAR MOVIMIENTO
======================================================-->

<div
    class="modal fade"
    id="modalEliminarMovimiento"
    tabindex="-1"
    aria-labelledby="modalEliminarMovimientoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content eliminar-modal">

            <div class="modal-body text-center p-4">

                <div class="eliminar-icon">

                    <i class="bi bi-trash3"></i>

                </div>


                <h5
                    class="mt-3"
                    id="modalEliminarMovimientoLabel">

                    ¿Eliminar movimiento?

                </h5>


                <p class="text-muted mb-4">

                    Esta acción marcará el movimiento como eliminado.
                    Los datos no se eliminarán físicamente.

                </p>


                <input
                    type="hidden"
                    id="eliminarIdDeposito">


                <div class="d-flex justify-content-center gap-2">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="button"
                        class="btn btn-danger"
                        id="btnConfirmarEliminar">

                        <i class="bi bi-trash3 me-2"></i>

                        Eliminar

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>