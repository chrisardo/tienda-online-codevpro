<?php
//======================================================
// CoDevPro Technology
// includes/perfil/dashboard_ultimo_pedido.php
//======================================================
?>

<div class="card border-0 shadow-sm h-100">

    <!--=========================================
    CABECERA
    ==========================================-->

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="fw-bold mb-1">

                    <i class="bi bi-bag-check-fill text-primary"></i>

                    Último pedido

                </h5>

                <small class="text-muted">

                    Información de tu compra más reciente

                </small>

            </div>

            <span
                class="badge bg-success"
                id="ultimoPedidoEstado">

                ENTREGADO

            </span>

        </div>

    </div>

    <!--=========================================
    CUERPO
    ==========================================-->

    <div class="card-body">

        <div class="row g-4">

            <!--=====================================
            COLUMNA IZQUIERDA
            ======================================-->

            <div class="col-md-7">

                <div class="mb-3">

                    <small class="text-muted">

                        Número de pedido

                    </small>

                    <h4
                        class="fw-bold mb-0"
                        id="ultimoPedidoNumero">

                        F001-000001

                    </h4>

                </div>

                <div class="row">

                    <div class="col-6 mb-3">

                        <small class="text-muted">

                            Fecha

                        </small>

                        <div
                            class="fw-semibold"
                            id="ultimoPedidoFecha">

                            13/07/2026

                        </div>

                    </div>

                    <div class="col-6 mb-3">

                        <small class="text-muted">

                            Hora

                        </small>

                        <div
                            class="fw-semibold"
                            id="ultimoPedidoHora">

                            10:30 AM

                        </div>

                    </div>

                    <div class="col-6 mb-3">

                        <small class="text-muted">

                            Comprobante

                        </small>

                        <div
                            id="ultimoPedidoComprobante">

                            BOLETA

                        </div>

                    </div>

                    <div class="col-6 mb-3">

                        <small class="text-muted">

                            Método de pago

                        </small>

                        <div
                            id="ultimoPedidoMetodoPago">

                            Yape

                        </div>

                    </div>

                </div>

            </div>

            <!--=====================================
            COLUMNA DERECHA
            ======================================-->

            <div class="col-md-5">

                <div
                    class="border rounded p-4 bg-light h-100">

                    <small class="text-muted">

                        Total pagado

                    </small>

                    <h2
                        class="fw-bold text-success mb-3"
                        id="ultimoPedidoTotal">

                        S/ 0.00

                    </h2>

                    <small class="text-muted">

                        Dirección de envío

                    </small>

                    <div
                        class="fw-semibold"
                        id="ultimoPedidoDireccion">

                        Sin información

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!--=========================================
    FOOTER
    ==========================================-->

    <div class="card-footer bg-white">

        <div class="d-flex flex-wrap gap-2 justify-content-end">

            <button
                class="btn btn-outline-primary btnDetallePedido"
                id="btnVerUltimoPedido"
                data-id="0">

                <i class="bi bi-eye"></i>

                Ver detalle

            </button>

            <button
                class="btn btn-outline-success"
                id="btnDescargarUltimoPedido">

                <i class="bi bi-file-earmark-pdf"></i>

                Descargar PDF

            </button>

        </div>

    </div>

</div>