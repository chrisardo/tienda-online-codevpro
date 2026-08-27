<?php
require_once "./controladores/estadisticas_inicio.php";
?>
<section class="py-5 bg-light">

    <div class="container">

        <div class="row justify-content-center mb-5">

            <div class="col-lg-8 text-center">

                <span class="badge bg-primary fs-6">

                    ¿POR QUÉ ELEGIRNOS?

                </span>

                <h2 class="display-5 fw-bold mt-3">

                    CoDevPro Technology

                </h2>

                <p class="text-secondary">

                    Somos especialistas en venta de productos tecnológicos y
                    soluciones informáticas para hogares, empresas e instituciones.

                </p>

            </div>

        </div>

        <div class="row g-4">

            <div class="col-lg-6">

                <div class="card border-0 shadow h-100">

                    <div class="card-body p-5">

                        <h3 class="fw-bold mb-4">

                            ¿Por qué comprar con nosotros?

                        </h3>

                        <div class="mb-4 d-flex">

                            <div class="me-3">

                                <span class="badge bg-primary rounded-circle p-3">

                                    <i class="bi bi-patch-check-fill fs-4"></i>

                                </span>

                            </div>

                            <div>

                                <h5 class="fw-bold">

                                    Productos Originales

                                </h5>

                                <p class="text-secondary">

                                    Trabajamos únicamente con marcas reconocidas y productos
                                    con garantía.

                                </p>

                            </div>

                        </div>

                        <div class="mb-4 d-flex">

                            <div class="me-3">

                                <span class="badge bg-success rounded-circle p-3">

                                    <i class="bi bi-headset fs-4"></i>

                                </span>

                            </div>

                            <div>

                                <h5 class="fw-bold">

                                    Soporte Especializado

                                </h5>

                                <p class="text-secondary">

                                    Nuestro equipo técnico brinda asesoría antes y después
                                    de la compra.

                                </p>

                            </div>

                        </div>

                        <div class="mb-4 d-flex">

                            <div class="me-3">

                                <span class="badge bg-warning rounded-circle p-3">

                                    <i class="bi bi-truck fs-4"></i>

                                </span>

                            </div>

                            <div>

                                <h5 class="fw-bold">

                                    Envíos Seguros

                                </h5>

                                <p class="text-secondary">

                                    Realizamos envíos a todo el Perú con total seguridad.

                                </p>

                            </div>

                        </div>

                        <div class="d-flex">

                            <div class="me-3">

                                <span class="badge bg-danger rounded-circle p-3">

                                    <i class="bi bi-shield-lock-fill fs-4"></i>

                                </span>

                            </div>

                            <div>

                                <h5 class="fw-bold">

                                    Compra Segura

                                </h5>

                                <p class="text-secondary">

                                    Protegemos toda la información de nuestros clientes.

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="row g-4">

                    <div class="col-6">

                        <div class="card shadow border-0 text-center">

                            <div class="card-body py-5">

                                <i class="bi bi-people-fill text-primary display-4"></i>

                                <div class="counter">

                                    <?= number_format($totalClientes) ?>+

                                </div>

                                <p>

                                    Clientes registrados

                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="col-6">

                        <div class="card shadow border-0 text-center">

                            <div class="card-body py-5">

                                <i class="bi bi-box-seam text-success display-4"></i>

                                <div class="counter">

                                    <?= number_format($totalProductos) ?>+

                                </div>

                                <p>

                                    Productos disponibles

                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="col-6">

                        <div class="card shadow border-0 text-center">

                            <div class="card-body py-5">

                                <i class="bi bi-cart-check text-warning display-4"></i>

                                <div class="counter">

                                    <?= number_format($totalVendidos) ?>+

                                </div>

                                <p>

                                    Productos vendidos

                                </p>

                            </div>

                        </div>

                    </div>

                    <div class="col-6">

                        <div class="card shadow border-0 text-center">

                            <div class="card-body py-5">

                                <i class="bi bi-award-fill text-danger display-4"></i>

                                <h2 class="fw-bold mt-3">

                                    1+

                                </h2>

                                <p>

                                    Años de experiencia

                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>