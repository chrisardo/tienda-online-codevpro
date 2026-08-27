<?php
//=========================================================
// CoDevPro Technology
// includes/lista_favoritos.php
//=========================================================

require_once "../controladores/obtener_favoritos.php";

if (!$resultadoFavoritos || mysqli_num_rows($resultadoFavoritos) == 0) {
?>

    <div class="text-center py-5">

        <img src="assets/img/favoritos-vacio.png"
            style="max-width:180px"
            class="mb-4">

        <h3 class="fw-bold">

            No tienes productos favoritos

        </h3>

        <p class="text-muted">

            Agrega productos a favoritos para encontrarlos rápidamente.

        </p>

        <a href="tienda.php"
            class="btn btn-primary btn-lg mt-3">

            <i class="bi bi-shop"></i>

            Ir a la tienda

        </a>

    </div>

<?php
    return;
}
?>

<div class="row g-4">

    <?php while ($producto = mysqli_fetch_assoc($resultadoFavoritos)) { ?>

        <div class="col-xl-3 col-lg-4 col-md-6">

            <div
                class="card product-card h-100 shadow-sm" data-precio="<?php echo $producto["precio"]; ?>">

                <?php if ($producto["descuento"] > 0) { ?>

                    <span class="badge bg-danger badge-sale">

                        -<?= intval($producto["descuento"]) ?>%

                    </span>

                <?php } ?>

                <!--============================
BOTONES
=============================-->

                <div class="product-actions">

                    <button

                        class="action-btn btnFavorito"

                        data-id="<?= $producto["idProducto"] ?>">

                        <i class="bi bi-heart-fill text-danger"></i>

                    </button>

                    <button

                        class="btn btn-outline-secondary btnVista"

                        data-id="<?= $producto["idProducto"] ?>">

                        <i class="bi bi-eye"></i>

                    </button>

                    <button

                        class="action-btn btnComparar"

                        data-id="<?= $producto["idProducto"] ?>">

                        <i class="bi bi-arrow-left-right"></i>

                    </button>

                </div>

                <!--============================
IMAGEN
=============================-->

                <?php if (!empty($producto["id_imagen"])) { ?>

                    <img

                        src="mostrar_imagen.php?id=<?= $producto["idProducto"] ?>&img=<?= $producto["id_imagen"] ?>"

                        class="img-fluid rounded"

                        style="height:260px;object-fit:contain;">

                <?php } else { ?>

                    <img

                        src="assets/img/sin_imagen.png"

                        class="img-fluid rounded"

                        style="height:260px;object-fit:contain;">

                <?php } ?>

                <div class="card-body d-flex flex-column">

                    <span class="badge bg-success mb-2">

                        Favorito

                    </span>

                    <h5 class="fw-bold">

                        <?= htmlspecialchars($producto["nombre"]) ?>

                    </h5>

                    <div class="rating mb-2">

                        <i class="bi bi-star-fill"></i>

                        <i class="bi bi-star-fill"></i>

                        <i class="bi bi-star-fill"></i>

                        <i class="bi bi-star-fill"></i>

                        <i class="bi bi-star-half"></i>

                        <small class="text-secondary">

                            (235)

                        </small>

                    </div>

                    <?php if ($producto["precio_anterior"] > 0) { ?>

                        <div class="price-old">

                            S/ <?= number_format($producto["precio_anterior"], 2) ?>

                        </div>

                    <?php } ?>

                    <div class="price-new">

                        S/ <?= number_format($producto["precio"], 2) ?>

                    </div>
                    <!--============================
                INFORMACIÓN
                =============================-->

                    <div class="mb-3 mt-3">

                        <div class="info-item mb-2">

                            <i class="bi bi-truck text-success"></i>

                            Envío Gratis

                        </div>

                        <div class="info-item mb-2">

                            <i class="bi bi-shield-check text-primary"></i>

                            Garantía Oficial

                        </div>

                        <div class="info-item">

                            <?php if ($producto["stock"] > 0) { ?>

                                <i class="bi bi-box-seam text-success"></i>

                                Stock Disponible

                                <strong>

                                    (<?= intval($producto["stock"]) ?>)

                                </strong>

                            <?php } else { ?>

                                <i class="bi bi-x-circle text-danger"></i>

                                Sin Stock

                            <?php } ?>

                        </div>

                    </div>

                    <!--============================
                BOTONES
                =============================-->

                    <div class="mt-auto d-grid gap-2">

                        <?php if ($producto["stock"] > 0) { ?>

                            <button

                                class="btn btn-primary btnAgregar"

                                data-id="<?= $producto["idProducto"] ?>">

                                <i class="bi bi-cart-plus"></i>

                                Agregar al carrito

                            </button>

                        <?php } else { ?>

                            <button

                                class="btn btn-secondary"

                                disabled>

                                Sin Stock

                            </button>

                        <?php } ?>

                        <button

                            class="btn btn-outline-primary btnVista"

                            data-id="<?= $producto["idProducto"] ?>">

                            <i class="bi bi-eye"></i>

                            Vista rápida

                        </button>

                    </div>

                </div>

            </div>

        </div>

    <?php } ?>

</div>