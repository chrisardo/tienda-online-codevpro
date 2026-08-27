<!-- =====================================================
CoDevPro Technology
Modal Ver Venta Premium Shopify Style
===================================================== -->

<div class="modal fade"
    id="modalVerVenta"
    tabindex="-1"
    aria-labelledby="modalVerVentaLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content border-0 shadow-lg rounded-4">

            <!-- HEADER -->

            <div class="modal-header bg-white border-bottom">

                <div>

                    <h4 class="modal-title fw-bold mb-1"
                        id="modalVerVentaLabel">

                        <i class="bi bi-receipt-cutoff text-primary"></i>

                        Detalle de Venta

                    </h4>

                    <small class="text-muted">

                        Información completa del pedido

                    </small>

                </div>

                <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"></button>

            </div>

            <!-- BODY -->

            <div class="modal-body bg-light">

                <div id="contenidoDetalleVenta">

                    <div class="text-center py-5">

                        <div class="spinner-border text-primary"></div>

                        <p class="mt-3 text-muted">

                            Cargando información de la venta...

                        </p>

                    </div>

                </div>

            </div>

            <!-- FOOTER -->

            <div class="modal-footer bg-white">

                <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-circle"></i>

                    Cerrar

                </button>

            </div>

        </div>

    </div>

</div>

<!-- =====================================================
ESTILOS SHOPIFY ADMIN
===================================================== -->

<style>
    .card-shopify {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
        overflow: hidden;
    }

    .card-shopify .card-header {
        background: #fff;
        border-bottom: 1px solid #eef1f4;
        font-weight: 600;
    }

    .avatar-shopify {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
    }

    .producto-resumen {
        padding: 16px;
        border: 1px solid #edf0f3;
        border-radius: 12px;
        margin-bottom: 12px;
        background: #fff;
        transition: .2s;
    }

    .producto-resumen:hover {
        background: #fafbfc;
    }

    .timeline-pedido {
        position: relative;
        margin-left: 10px;
    }

    .timeline-pedido:before {
        content: "";
        position: absolute;
        left: 9px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        padding-left: 35px;
        margin-bottom: 25px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-item:before {
        content: "";
        position: absolute;
        left: 0;
        top: 4px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #6c757d;
    }

    .timeline-item.success:before {
        background: #198754;
    }

    .timeline-item.warning:before {
        background: #ffc107;
    }

    .timeline-item.danger:before {
        background: #dc3545;
    }

    .timeline-item.info:before {
        background: #0dcaf0;
    }

    .estado-card {
        border-left: 4px solid #0d6efd;
    }

    .resumen-financiero .fila {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .progress-pedido {
        height: 10px;
        border-radius: 30px;
    }

    .progress-pedido .progress-bar {
        border-radius: 30px;
    }

    .badge-shopify {
        font-size: .85rem;
        padding: .55rem .8rem;
    }

    .btn-shopify {
        border-radius: 10px;
    }

    .kpi-total {
        font-size: 2rem;
        font-weight: 700;
    }

    .modal-body {
        min-height: 500px;
    }
</style>