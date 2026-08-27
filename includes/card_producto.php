<!-- Toda esta parte es de includes/card_producto.php-->
<?php
/*Cards Premium de Productos*/
require_once "./controladores/obtener_productos_destacados.php";

?>
<section class="pb-5">

    <div class="container">
        <!-- TÍTULO GLOBAL -->
        <div class="text-center mb-4">
            <h2 class="fw-bold">
                Productos más comprados
            </h2>

            <p class="text-muted">
                Descubre los productos más populares de nuestra tienda
            </p>
        </div>
        <div class="row g-4">
            <?php while ($productos = mysqli_fetch_assoc($resultadoProductos)) { ?>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">

                    <div class="card product-card h-100 shadow-sm">

                        <?php if ($productos['oferta'] == 1 && $productos['descuento'] > 0) { ?>
                            <span class="badge bg-danger badge-sale">
                                -<?= $productos['descuento']; ?>%
                            </span>
                        <?php } ?>

                        <div class="product-actions">

                            <button
                                class="action-btn btnFavorito"
                                data-id="<?= $productos['idProducto']; ?>">

                                <i class="bi <?= $productos["favorito"] ? "bi-heart-fill text-danger" : "bi-heart"; ?>"></i>

                            </button>

                            <button

                                class="btn btn-outline-secondary btnVista"

                                data-id="<?= $productos['idProducto']; ?>">

                                <i class="bi bi-eye"></i>

                            </button>

                            <button
                                class="action-btn btnComparar"
                                data-id="<?= $productos['idProducto']; ?>">

                                <i class="bi bi-arrow-left-right"></i>

                            </button>

                        </div>

                        <?php if ($productos['imagen']) { ?>

                            <img
                                id="imagenPrincipal"
                                src="mostrar_imagen.php?id=<?= $productos['idProducto']; ?>&img=<?= $productos['id_imagen']; ?>"
                                class="img-fluid rounded"
                                style="max-height:450px; object-fit:contain;">

                        <?php } else { ?>

                            <img
                                src="./assets/img/sin_imagen.png"
                                class="img-fluid rounded"
                                style="max-height:450px; object-fit:contain;">

                        <?php } ?>

                        <div class="card-body">

                            <span class="badge bg-success mb-2">

                                Nuevo

                            </span>

                            <h5 class="fw-bold">

                                <?= $productos['nombre']; ?>

                            </h5>

                            <!-- =========================================
     CALIFICACIÓN DINÁMICA
========================================= -->

                            <div class="rating mb-2">

                                <?php

                                $promedio = (float) $productos['promedio_calificacion'];

                                $totalOpiniones = (int) $productos['total_opiniones'];

                                $estrellaCompleta = floor($promedio);

                                $mediaEstrella = ($promedio - $estrellaCompleta) >= 0.5 ? 1 : 0;

                                $estrellaVacia = 5 - ($estrellaCompleta + $mediaEstrella);

                                ?>

                                <!-- ESTRELLAS COMPLETAS -->

                                <?php for ($i = 0; $i < $estrellaCompleta; $i++) { ?>

                                    <i class="bi bi-star-fill text-warning"></i>

                                <?php } ?>


                                <!-- MEDIA ESTRELLA -->

                                <?php if ($mediaEstrella) { ?>

                                    <i class="bi bi-star-half text-warning"></i>

                                <?php } ?>


                                <!-- ESTRELLAS VACÍAS -->

                                <?php for ($i = 0; $i < $estrellaVacia; $i++) { ?>

                                    <i class="bi bi-star text-secondary"></i>

                                <?php } ?>


                                <!-- PROMEDIO -->

                                <span class="fw-semibold ms-1">

                                    <?= number_format($promedio, 1); ?>

                                </span>


                                <!-- CANTIDAD DE OPINIONES -->

                                <?php if ($totalOpiniones > 0) { ?>

                                    <small class="text-secondary">

                                        (<?= $totalOpiniones; ?>)

                                    </small>

                                <?php } else { ?>

                                    <small class="text-secondary">

                                        (Sin opiniones)

                                    </small>

                                <?php } ?>

                            </div>

                            <div class="mb-3">
                                <?php
                                if ($productos['precio_anterior'] > 0) {
                                ?>
                                    <div class="price-old">

                                        S/ <?= number_format($productos['precio_anterior'], 2); ?>

                                    </div>
                                <?php
                                } 
                                ?>
                                


                                <div class="price-new">

                                    S/

                                    <?= number_format($productos['precio'], 2); ?>

                                </div>

                            </div>

                            <div class="mb-3">

                                <div class="info-item mb-1">

                                    <i class="bi bi-truck text-success"></i>

                                    Envío Gratis

                                </div>

                                <div class="info-item mb-1">

                                    <i class="bi bi-shield-check text-primary"></i>

                                    Garantía Oficial

                                </div>

                                <div class="info-item">


                                    <?php if ($productos['stock'] > 0) { ?>


                                        <i class="bi bi-box-seam text-success"></i>


                                        Stock disponible:

                                        <strong>

                                            <?= $productos['stock']; ?>

                                            unidades

                                        </strong>



                                    <?php } else { ?>


                                        <i class="bi bi-x-circle text-danger"></i>


                                        <strong class="text-danger">

                                            Producto agotado

                                        </strong>



                                    <?php } ?>


                                </div>

                            </div>

                            <div class="d-grid">

                                <?php if ($productos['stock'] > 0) { ?>


                                    <button

                                        class="btn btn-primary btn-cart btnAgregar"

                                        data-id="<?= $productos['idProducto']; ?>">


                                        <i class="bi bi-cart-plus"></i>


                                        Agregar al carrito


                                    </button>



                                <?php } else { ?>


                                    <button

                                        class="btn btn-secondary btn-cart"

                                        disabled>


                                        <i class="bi bi-x-circle"></i>


                                        Agotado


                                    </button>


                                <?php } ?>

                            </div>

                        </div>

                    </div>
                </div>
            <?php } ?>
        </div>

    </div>

</section>