<?php
//======================================================
// CoDevPro Technology
// Archivo: modal/modal_confirmar_entrega_cliente.php
// Módulo: Mis Pedidos
// Sistema: Inventa
//======================================================
?>

<!--=====================================================
MODAL CONFIRMAR ENTREGA
======================================================-->

<div
    class="modal fade"
    id="modalConfirmarEntregaCliente"
    tabindex="-1"
    aria-labelledby="modalConfirmarEntregaClienteLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <!--=================================================
            CABECERA
            =================================================-->

            <div class="modal-header bg-primary text-white">

                <h5
                    class="modal-title"
                    id="modalConfirmarEntregaClienteLabel">

                    <i class="bi bi-box-seam me-2"></i>

                    Confirmar entrega

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!--=================================================
            CUERPO
            =================================================-->

            <div class="modal-body text-center">

                <div class="mb-3">

                    <div
                        class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center"
                        style="width:80px;height:80px;">

                        <i class="bi bi-check-circle-fill text-success fs-1"></i>

                    </div>

                </div>


                <h5 class="fw-bold mb-3">

                    ¿Confirmar que recibiste tu pedido?

                </h5>


                <p class="text-muted mb-3">

                    Al confirmar la entrega, el pedido cambiará
                    automáticamente a estado

                    <strong class="text-success">
                        ENTREGADO
                    </strong>.

                </p>


                <div
                    class="alert alert-warning text-start">

                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    <strong>Importante:</strong>

                    Solo confirma la entrega si ya recibiste
                    correctamente tu pedido.

                </div>


                <!--=============================================
                PEDIDO
                ==============================================-->

                <div
                    class="bg-light rounded p-3 mb-3">

                    <small class="text-muted d-block">

                        Pedido

                    </small>

                    <strong
                        id="numeroPedidoConfirmarEntrega"
                        class="fs-5">

                        -

                    </strong>

                </div>


                <!--=============================================
                ID OCULTO
                ==============================================-->

                <input
                    type="hidden"
                    id="idPedidoConfirmarEntrega"
                    value="">

            </div>


            <!--=================================================
            PIE
            =================================================-->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-success"
                    id="btnConfirmarEntregaCliente">

                    <i class="bi bi-check-circle me-1"></i>

                    Sí, confirmar entrega

                </button>

            </div>

        </div>

    </div>

</div>