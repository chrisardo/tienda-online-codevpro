<?php
//======================================================
// CoDevPro Technology
// includes/perfil/dashboard_estadisticas.php
//======================================================
?>

<div class="row g-4">

    <!--=========================================
    TOTAL PEDIDOS
    ==========================================-->

    <div class="col-xl-3 col-lg-6 col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex align-items-center">

                    <div class="flex-shrink-0">

                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">

                            <i class="bi bi-bag-check-fill fs-2 text-primary"></i>

                        </div>

                    </div>

                    <div class="ms-3 flex-grow-1">

                        <small class="text-muted text-uppercase">

                            Pedidos

                        </small>

                        <h2
                            class="fw-bold mb-0"
                            id="estadisticaPedidos">

                            0

                        </h2>

                    </div>

                </div>

            </div>

            <div class="card-footer bg-transparent border-0">

                <small class="text-muted">

                    Total de compras realizadas

                </small>

            </div>

        </div>

    </div>

    <!--=========================================
    TOTAL GASTADO
    ==========================================-->

    <div class="col-xl-3 col-lg-6 col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex align-items-center">

                    <div class="flex-shrink-0">

                        <div class="rounded-circle bg-success bg-opacity-10 p-3">

                            <i class="bi bi-cash-stack fs-2 text-success"></i>

                        </div>

                    </div>

                    <div class="ms-3 flex-grow-1">

                        <small class="text-muted text-uppercase">

                            Total Gastado

                        </small>

                        <h2
                            class="fw-bold text-success mb-0"
                            id="estadisticaGastado">

                            S/ 0.00

                        </h2>

                    </div>

                </div>

            </div>

            <div class="card-footer bg-transparent border-0">

                <small class="text-muted">

                    Monto invertido en la tienda

                </small>

            </div>

        </div>

    </div>

    <!--=========================================
    FAVORITOS
    ==========================================-->

    <div class="col-xl-3 col-lg-6 col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex align-items-center">

                    <div class="flex-shrink-0">

                        <div class="rounded-circle bg-danger bg-opacity-10 p-3">

                            <i class="bi bi-heart-fill fs-2 text-danger"></i>

                        </div>

                    </div>

                    <div class="ms-3 flex-grow-1">

                        <small class="text-muted text-uppercase">

                            Favoritos

                        </small>

                        <h2
                            class="fw-bold text-danger mb-0"
                            id="estadisticaFavoritos">

                            0

                        </h2>

                    </div>

                </div>

            </div>

            <div class="card-footer bg-transparent border-0">

                <small class="text-muted">

                    Productos guardados

                </small>

            </div>

        </div>

    </div>

    <!--=========================================
    CARRITO
    ==========================================-->

    <div class="col-xl-3 col-lg-6 col-md-6">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex align-items-center">

                    <div class="flex-shrink-0">

                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">

                            <i class="bi bi-cart-fill fs-2 text-warning"></i>

                        </div>

                    </div>

                    <div class="ms-3 flex-grow-1">

                        <small class="text-muted text-uppercase">

                            Carrito

                        </small>

                        <h2
                            class="fw-bold text-warning mb-0"
                            id="estadisticaCarrito">

                            0

                        </h2>

                    </div>

                </div>

            </div>

            <div class="card-footer bg-transparent border-0">

                <small class="text-muted">

                    Productos pendientes de compra

                </small>

            </div>

        </div>

    </div>

</div>