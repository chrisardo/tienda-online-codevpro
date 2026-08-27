<!--======================================================
=            MODAL EDITAR CATEGORÍA
=======================================================-->

<div
    class="modal fade"
    id="modalEditarCategoria"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content border-0 shadow">

            <form id="formEditarCategoria">

                <div class="modal-header bg-warning">

                    <h5 class="modal-title fw-bold">

                        <i class="bi bi-pencil-square me-2"></i>

                        Editar Categoría

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div
                    class="modal-body"
                    id="contenidoEditarCategoria">

                    <div class="text-center py-5">

                        <div class="spinner-border text-warning"></div>

                        <p class="mt-3">

                            Cargando información...

                        </p>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning">

                        <i class="bi bi-save me-2"></i>

                        Guardar Cambios

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>