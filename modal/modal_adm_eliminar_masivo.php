<!-- =====================================
MODAL ELIMINAR MASIVO
===================================== -->

<div
    class="modal fade"
    id="modalEliminarMasivo"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">

                <h5 class="modal-title text-danger">

                    <i class="bi bi-trash-fill me-2"></i>

                    Eliminar productos

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body text-center">

                <i
                    class="bi bi-exclamation-triangle-fill text-warning"
                    style="font-size:60px;">
                </i>

                <h5 class="mt-3">

                    ¿Deseas eliminar los productos seleccionados?

                </h5>

                <p class="text-muted">

                    Se eliminarán

                    <strong id="cantidadProductosEliminar">
                        0
                    </strong>

                    productos seleccionados.

                </p>

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
                    id="btnConfirmarEliminarMasivo"
                    class="btn btn-danger">

                    <i class="bi bi-trash me-1"></i>

                    Eliminar

                </button>

            </div>

        </div>

    </div>

</div>