<?php
//Toda esta parte es de ajax/vista_producto.php
require_once "../controladores/obtener_producto2.php";

?>

<div class="row">

    <div class="col-lg-6">

        <div id="carouselProducto"

            class="carousel slide">

            <div class="carousel-inner">

                <?php
                $activo = true;

                while ($imagen = mysqli_fetch_assoc($imagenes)) {
                ?>

                    <div class="carousel-item <?= $activo ? 'active' : '' ?>">
                        <?php if (!empty($imagen['id_imagen'])) { ?>
                            <img
                                src="./mostrar_imagen.php?id=<?= $producto['idProducto'] ?>&img=<?= $imagen['id_imagen'] ?>"
                                class="d-block w-100 rounded">
                        <?php } else { ?>

                            <img
                                src="assets/img/sin-imagen.png"
                                class="img-fluid rounded"
                                alt="Sin imagen">

                        <?php } ?>
                    </div>

                <?php
                    $activo = false;
                }
                ?>

            </div>

            <button

                class="carousel-control-prev"

                type="button"

                data-bs-target="#carouselProducto"

                data-bs-slide="prev">

                <span class="carousel-control-prev-icon bg-dark rounded-circle"></span>

            </button>

            <button

                class="carousel-control-next"

                type="button"

                data-bs-target="#carouselProducto"

                data-bs-slide="next">

                <span class="carousel-control-next-icon bg-dark rounded-circle"></span>

            </button>

        </div>

    </div>

    <div class="col-lg-6">

        <h3><?= htmlspecialchars($producto['nombre']) ?></h3>

        <p class="text-muted">

            <?= htmlspecialchars($producto['marca']) ?>

        </p>

        <h2 class="text-primary">

            S/ <?= number_format($producto['precio'], 2) ?>

        </h2>

        <p>

            <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>

        </p>

        <button
            class="btn btn-primary btnAgregar"
            data-id="<?= $producto['idProducto']; ?>">

            Agregar al carrito

        </button>
        <button

            class="btn btn-outline-danger btnFavorito"

            data-id="<?= $producto["idProducto"]; ?>">

            <i class="bi <?= $producto["favorito"] ? "bi-heart-fill text-danger" : "bi-heart"; ?>"></i>

        </button>
    </div>

</div>