<?php
require_once "./controladores/conexion.php";
$idCliente = $_SESSION["idCliente"] ?? 0;
$sql = "SELECT
            p.*,
            c.nombre AS categoria,
            m.nombre AS marca,
            i.id_imagen,

            CASE
                WHEN f.id_favorito IS NULL THEN 0
                ELSE 1
            END AS favorito,


            ROUND(AVG(t.calificacion),1) AS promedio,

            COUNT(t.id_testimonio) AS total_opiniones


        FROM producto p


        LEFT JOIN categorias c
            ON c.id_categorias = p.id_categorias


        LEFT JOIN marcas m
            ON m.id_marca = p.id_marca


        LEFT JOIN imagenes i
            ON i.idProducto = p.idProducto
            AND i.orden = 1


        LEFT JOIN favoritos f
            ON f.idProducto=p.idProducto
            AND f.idCliente=$idCliente


        LEFT JOIN testimonios t
            ON t.idProducto=p.idProducto
            AND t.estado='APROBADO'


        WHERE p.Eliminado = 0

        AND p.tipo='producto'


        GROUP BY p.idProducto


        ORDER BY p.idProducto DESC";

$resultado = mysqli_query($conexion, $sql);
?>


<section class="pb-5">

    <div class="container">

        <div class="row g-4" id="contenedorProductos">

            <?php while ($producto = mysqli_fetch_assoc($resultado)) { ?>

                <div class="col-xl-3 col-lg-4 col-md-6">

                    <div class="card h-100 shadow-sm border-0 rounded-4">

                        <div class="position-relative">

                            <?php if ($producto['oferta'] == 1) { ?>

                                <span class="badge bg-danger position-absolute m-3">

                                    -<?= $producto['descuento']; ?>%

                                </span>

                            <?php } ?>

                            <img

                                src="mostrar_imagen.php?id=<?= $producto['idProducto']; ?>"

                                class="card-img-top"

                                style="height:220px;object-fit:contain;">

                        </div>

                        <div class="card-body d-flex flex-column">

                            <span class="text-primary small">

                                <?= $producto['categoria']; ?>

                            </span>
                            <!--================================
CALIFICACIÓN PRODUCTO
================================-->

                            <div class="mb-2">


                                <?php


                                $promedio = $producto['promedio'] ?? 0;

                                $total = $producto['total_opiniones'] ?? 0;


                                $completas = floor($promedio);

                                $media = ($promedio - $completas) >= 0.5 ? 1 : 0;

                                $vacias = 5 - ($completas + $media);


                                ?>


                                <?php for ($i = 0; $i < $completas; $i++) { ?>

                                    <i class="bi bi-star-fill text-warning"></i>

                                <?php } ?>



                                <?php if ($media) { ?>

                                    <i class="bi bi-star-half text-warning"></i>

                                <?php } ?>



                                <?php for ($i = 0; $i < $vacias; $i++) { ?>

                                    <i class="bi bi-star text-secondary"></i>

                                <?php } ?>



                                <?php if ($total > 0) { ?>


                                    <small class="text-muted ms-1">

                                        <?= number_format($promedio, 1); ?>

                                        (<?= $total; ?>)

                                    </small>


                                <?php } else { ?>


                                    <small class="text-muted ms-1">

                                        Sin opiniones

                                    </small>


                                <?php } ?>


                            </div>
                            <h5 class="fw-bold">

                                <?= $producto['nombre']; ?>

                            </h5>

                            <p class="text-secondary small">

                                <?= substr($producto['descripcion'], 0, 80); ?>

                                ...

                            </p>

                            <div class="mb-3">

                                <?php if ($producto['oferta'] == 1) { ?>

                                    <div class="text-decoration-line-through text-secondary">

                                        S/

                                        <?= number_format($producto['precio_anterior'], 2); ?>

                                    </div>

                                <?php } ?>

                                <div class="fs-3 fw-bold text-primary">

                                    S/

                                    <?= number_format($producto['precio'], 2); ?>

                                </div>

                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">

                                <?php

                                if ($producto['stock'] > 0) {

                                ?>

                                    <span class="badge bg-success">

                                        Disponible

                                    </span>

                                <?php

                                } else {

                                ?>

                                    <span class="badge bg-danger">

                                        Agotado

                                    </span>

                                <?php

                                }

                                ?>

                                <small class="text-muted">

                                    <?= $producto['marca']; ?>

                                </small>

                            </div>

                            <div class="d-grid gap-2 mt-auto">

                                <?php if ($producto['stock'] > 0) { ?>


                                    <button

                                        class="btn btn-primary btnAgregar"

                                        data-id="<?= $producto['idProducto']; ?>">


                                        <i class="bi bi-cart-plus"></i>

                                        Agregar


                                    </button>


                                <?php } else { ?>


                                    <button

                                        class="btn btn-secondary"

                                        disabled>


                                        <i class="bi bi-x-circle"></i>

                                        Agotado


                                    </button>


                                <?php } ?>

                                <div class="btn-group">

                                    <button

                                        class="btn btn-outline-danger btnFavorito"

                                        data-id="<?= $producto['idProducto']; ?>">

                                        <i class="bi <?= $producto["favorito"] ? "bi-heart-fill text-danger" : "bi-heart"; ?>"></i>

                                    </button>

                                    <button

                                        class="btn btn-outline-secondary btnVista"

                                        data-id="<?= $producto['idProducto']; ?>">

                                        <i class="bi bi-eye"></i>

                                    </button>

                                    <button

                                        class="btn btn-outline-dark btnComparar"

                                        data-id="<?= $producto['idProducto']; ?>">

                                        <i class="bi bi-shuffle"></i>

                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    </div>

</section>