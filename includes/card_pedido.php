<!--=========================================
CARD PEDIDO: includes/card_pedido.php
==========================================-->

<div class="card shadow-sm border-0 mb-4 pedido-card">

    <!-- Encabezado -->

    <div class="card-header bg-white">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <div class="d-flex flex-wrap align-items-center gap-3">

                    <span class="fw-bold">

                        Pedido #000125

                    </span>

                    <span class="badge bg-success">

                        ENTREGADO

                    </span>

                    <small class="text-muted">

                        04 Julio 2026

                    </small>

                </div>

            </div>

            <div class="col-lg-4 text-lg-end mt-2 mt-lg-0">

                <strong>

                    Total: S/ 2,899.00

                </strong>

            </div>

        </div>

    </div>

    <!-- Productos -->

    <div class="card-body">

        <div class="row">

            <!-- Imagen -->

            <div class="col-md-2 text-center">

                <img
                    src="assets/img/sin_imagen.png"
                    class="img-fluid rounded border"
                    style="max-height:120px;">
            </div>

            <!-- Información -->

            <div class="col-md-6">

                <h5 class="fw-bold mb-2">

                    Laptop Lenovo IdeaPad Gaming 3

                </h5>

                <div class="text-muted mb-2">

                    Código:
                    LEN-2026

                </div>

                <div class="text-muted">

                    Cantidad:
                    <strong>1</strong>

                </div>

                <div class="text-muted">

                    Precio:
                    <strong>S/ 2,899.00</strong>

                </div>

            </div>

            <!-- Estado -->

            <div class="col-md-4">

                <div class="border rounded p-3 bg-light">

                    <div class="mb-2">

                        <i class="bi bi-truck text-primary"></i>

                        Estado del envío

                    </div>

                    <div class="fw-bold text-success">

                        Entregado correctamente

                    </div>

                    <small class="text-muted">

                        Recibido por el cliente.

                    </small>

                </div>

            </div>

        </div>

    </div>

    <!-- Acciones -->

    <div class="card-footer bg-white">

        <div class="d-flex flex-wrap gap-2 justify-content-end">

            <button class="btn btn-outline-primary">

                <i class="bi bi-eye"></i>

                Ver detalle

            </button>

            <button class="btn btn-outline-success">

                <i class="bi bi-receipt"></i>

                Descargar boleta

            </button>

            <button class="btn btn-primary">

                <i class="bi bi-arrow-repeat"></i>

                Comprar nuevamente

            </button>

        </div>

    </div>

</div>
<!--=====================================
PAGINACIÓN
======================================-->

<div class="row mt-5">

    <div class="col-12">

        <nav aria-label="Paginación de pedidos">

            <ul class="pagination justify-content-center" id="paginacionPedidos">

                <!-- Anterior -->

                <li class="page-item disabled">

                    <a class="page-link" href="#">

                        <i class="bi bi-chevron-left"></i>

                    </a>

                </li>

                <!-- Página -->

                <li class="page-item active">

                    <a class="page-link" href="#">

                        1

                    </a>

                </li>

                <li class="page-item">

                    <a class="page-link" href="#">

                        2

                    </a>

                </li>

                <li class="page-item">

                    <a class="page-link" href="#">

                        3

                    </a>

                </li>

                <li class="page-item">

                    <span class="page-link">

                        ...

                    </span>

                </li>

                <li class="page-item">

                    <a class="page-link" href="#">

                        10

                    </a>

                </li>

                <!-- Siguiente -->

                <li class="page-item">

                    <a class="page-link" href="#">

                        <i class="bi bi-chevron-right"></i>

                    </a>

                </li>

            </ul>

        </nav>

    </div>

</div>