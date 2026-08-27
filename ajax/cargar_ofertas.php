<?php
//=========================================================
// CoDevPro Technology
// ajax/cargar_ofertas.php
//=========================================================

require_once "../controladores/obtener_ofertas2.php";

/*=============================================
SI NO HAY OFERTAS
=============================================*/

if (empty($productosOferta)) {

?>

    <div class="w-100">

        <div class="alert alert-warning text-center">

            <i class="bi bi-exclamation-circle fs-2"></i>

            <h5 class="mt-3">

                No existen ofertas disponibles.

            </h5>

        </div>

    </div>

<?php

    exit();
}

/*=============================================
RECORRER PRODUCTOS
=============================================*/

foreach ($productosOferta as $producto) {

?>

    <div class="card card-oferta">

        <!--==============================
        DESCUENTO
        ==============================-->

        <?php if ($producto["descuento"] > 0) { ?>

            <span class="badge-descuento">

                -<?= intval($producto["descuento"]); ?>%

            </span>

        <?php } ?>

        <!--==============================
        STOCK
        ==============================-->

        <?php if (!empty($producto["etiqueta"])) { ?>

            <span class="badge-stock">

                <?= htmlspecialchars($producto["etiqueta"]); ?>

            </span>

        <?php } ?>

        <!--==============================
        IMAGEN
        ==============================-->
        <?php if (!empty($producto["imagen"])) { ?>
            <img
                src="<?= htmlspecialchars($producto["imagen"]); ?>"
                class="imagen-oferta"
                alt="<?= htmlspecialchars($producto["nombre"]); ?>"
                loading="lazy">
        <?php } else { ?>
            <img
                src="./assets/img/sin_imagen.png"
                class="imagen-oferta"
                alt="Sin imagen disponible"
                loading="lazy"
                style="max-height:450px; object-fit:contain;">

        <?php } ?>
        <!--==============================
        INFORMACIÓN
        ==============================-->

        <div class="card-body d-flex flex-column">

            <small class="marca">

                <?= htmlspecialchars($producto["marca"]); ?>

            </small>

            <h5 class="nombre-producto">

                <a href="producto.php?id=<?= $producto["idProducto"]; ?>">

                    <?= htmlspecialchars($producto["nombre"]); ?>

                </a>

            </h5>

            <!--==============================
CALIFICACIÓN
==============================-->

            <div class="estrellas mb-2">

                <?php

                $promedio = floatval($producto["promedio"]);

                $totalOpiniones = intval($producto["totalOpiniones"]);

                $estrellasCompletas = floor($promedio);

                $tieneMedia = ($promedio - $estrellasCompletas) >= 0.5;

                $estrellasVacias = 5 - $estrellasCompletas - ($tieneMedia ? 1 : 0);

                ?>

                <?php if ($totalOpiniones > 0) { ?>

                    <!-- ESTRELLAS COMPLETAS -->

                    <?php for ($i = 0; $i < $estrellasCompletas; $i++) { ?>

                        <i class="bi bi-star-fill text-warning"></i>

                    <?php } ?>


                    <!-- MEDIA ESTRELLA -->

                    <?php if ($tieneMedia) { ?>

                        <i class="bi bi-star-half text-warning"></i>

                    <?php } ?>


                    <!-- ESTRELLAS VACÍAS -->

                    <?php for ($i = 0; $i < $estrellasVacias; $i++) { ?>

                        <i class="bi bi-star text-secondary"></i>

                    <?php } ?>


                    <!-- PROMEDIO -->

                    <span class="fw-bold ms-1">

                        <?= number_format($promedio, 1); ?>

                    </span>


                    <!-- CANTIDAD -->

                    <small class="text-secondary">

                        (<?= $totalOpiniones; ?>)

                    </small>

                <?php } else { ?>

                    <i class="bi bi-star text-secondary"></i>

                    <span class="text-muted small ms-1">

                        Sin opiniones

                    </span>

                <?php } ?>

            </div>

            <!--==============================
            PRECIOS
            ==============================-->

            <?php if ($producto["precioAnterior"] > $producto["precio"]) { ?>

                <div class="precio-anterior">

                    S/ <?= number_format($producto["precioAnterior"], 2); ?>

                </div>

            <?php } ?>

            <div class="precio-actual">

                S/ <?= number_format($producto["precio"], 2); ?>

            </div>

            <!--==============================
            STOCK
            ==============================-->

            <div class="stock-text mt-3">

                Stock disponible

                <strong>

                    <?= intval($producto["stock"]); ?>

                </strong>

            </div>

            <div class="stock-bar mt-2 mb-3">

                <div style="width:<?= intval($producto["stockBarra"]); ?>%"></div>

            </div>

            <!--==============================
            BOTONES
            ==============================-->

            <div class="mt-auto d-grid gap-2">

                <button
                    class="btn  <?= $producto["favorito"] ? "btn-outline-danger" : "btn-danger"; ?> btn-oferta agregar-carrito btnFavorito"
                    data-id="<?= $producto["idProducto"]; ?>">

                    <i class="bi <?= $producto["favorito"] ? "bi-heart-fill text-danger" : "bi-heart"; ?>"></i>

                </button>
                <?php if ($producto["stock"] > 0) { ?>


                    <button

                        class="btn btn-primary btn-cart btnAgregar"

                        data-id="<?= $producto['idProducto']; ?>">


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
<?php

}
?>