<?php
//=====================================================
// CoDevPro Technology
// includes/pedidos_clientes/modal_ver_pedido.php
//=====================================================
?>

<div class="modal fade"
    id="modalVerPedido"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content border-0 shadow">


            <!-- HEADER -->

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">

                    <i class="bi bi-bag-check-fill me-2"></i>

                    Detalle del Pedido

                </h5>

                <button type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>



            <!-- BODY -->

            <div class="modal-body">

                <div id="contenidoDetallePedido">

                    <div class="text-center py-0">

                        <div class="spinner-border text-primary">

                        </div>

                        <p class="mt-0 mb-0">

                            Cargando información...

                        </p>

                    </div>

                </div>

            </div>



            <!-- FOOTER -->

            <div class="modal-footer">

                <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Cerrar

                </button>

            </div>

        </div>

    </div>

</div>