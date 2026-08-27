<!-- ==========================================================
 MODAL EDITAR IMÁGENES
=========================================================== -->
<div class="modal fade" id="modalEditarImagenes" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-info text-white">

                <h5 class="modal-title">
                    <i class="fas fa-images me-2"></i>
                    Administrar imágenes del inventario
                </h5>

                <button class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <input type="hidden"
                    id="img_idProducto"
                    value="">

                <div class="row g-4" id="contenedorImagenes">

                    <!--
                        Aquí JS cargará automáticamente
                        los 4 espacios
                    -->

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
                    id="btnGuardarImagenes"
                    type="button"
                    class="btn btn-success">

                    <i class="fas fa-save me-2"></i>
                    Guardar cambios

                </button>

            </div>

        </div>

    </div>

</div>