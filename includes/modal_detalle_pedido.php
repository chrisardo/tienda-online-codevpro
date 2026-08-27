<!--======================================================
CoDevPro Technology
includes/modal_detalle_pedido.php
=======================================================-->

<div
    class="modal fade"
    id="modalDetallePedido"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-scrollable">

        <div class="modal-content border-0 shadow-lg">

            <!--=========================================
            HEADER
            ==========================================-->

            <div class="modal-header bg-primary text-white">

                <div>

                    <h4 class="fw-bold mb-1">

                        <i class="bi bi-bag-check-fill"></i>

                        Detalle del Pedido

                    </h4>

                    <small class="opacity-75">

                        Consulta toda la información de tu compra.

                    </small>

                </div>

                <button
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"></button>

            </div>

            <!--=========================================
            BODY
            ==========================================-->

            <div
                class="modal-body"
                id="contenidoDetallePedido">

                <div class="text-center py-5">

                    <div class="spinner-border text-primary"></div>

                </div>

            </div>

            <!--=========================================
            FOOTER
            ==========================================-->

            <div class="modal-footer bg-white">

                <button
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-circle"></i>

                    Cerrar

                </button>

                <button
                    class="btn btn-outline-success"
                    id="btnDescargarPedido">

                    <i class="bi bi-file-earmark-pdf"></i>

                    Descargar PDF

                </button>

                <button
                    class="btn btn-primary"
                    id="btnComprarNuevamente">

                    <i class="bi bi-arrow-repeat"></i>

                    Comprar nuevamente

                </button>

            </div>

        </div>

    </div>

</div>