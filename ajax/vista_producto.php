<?php

// ======================================================
// CoDevPro Technology
// ajax/vista_producto.php
// ======================================================

require_once "../controladores/obtener_producto2.php";


// ======================================================
// FUNCIONES DE FORMATO
// ======================================================

function moneda($valor)
{
    global $simboloMoneda;
    global $decimales;

    return $simboloMoneda . ' ' .
        number_format(
            floatval($valor),
            $decimales,
            '.',
            ','
        );
}

?>


<div class="container-fluid">

    <!-- ==================================================
         PRODUCTO
    ================================================== -->

    <div class="row g-4">


        <!-- ==================================================
             IMÁGENES
        ================================================== -->

        <div class="col-lg-6">

            <div class="card border-0 shadow-sm rounded-4">

                <div id="carouselProducto"
                    class="carousel slide">

                    <div class="carousel-inner rounded-4">


                        <?php

                        $activo = true;

                        if (
                            $imagenes &&
                            mysqli_num_rows($imagenes) > 0
                        ):

                            while (
                                $imagen =
                                mysqli_fetch_assoc($imagenes)
                            ):

                        ?>

                                <div
                                    class="carousel-item
                                <?= $activo ? 'active' : '' ?>">

                                    <img
                                        src="mostrar_imagen.php?id=<?= $producto['idProducto']; ?>&img=<?= $imagen['id_imagen']; ?>"
                                        class="d-block w-100"
                                        style="
                                        height:420px;
                                        object-fit:contain;
                                        background:#f8f9fa;
                                    "
                                        alt="<?= htmlspecialchars(
                                                    $producto['nombre']
                                                ); ?>">

                                </div>


                            <?php

                                $activo = false;

                            endwhile;

                        else:

                            ?>

                            <div class="carousel-item active">

                                <img
                                    src="assets/img/sin_imagen.png"
                                    class="d-block w-100"
                                    style="
                                        height:420px;
                                        object-fit:contain;
                                    "
                                    alt="Sin imagen">

                            </div>

                        <?php endif; ?>


                    </div>


                    <?php
                    if (
                        $imagenes &&
                        mysqli_num_rows($imagenes) > 1
                    ):
                    ?>

                        <button
                            class="carousel-control-prev"
                            type="button"
                            data-bs-target="#carouselProducto"
                            data-bs-slide="prev">

                            <span
                                class="carousel-control-prev-icon bg-dark rounded-circle"></span>

                        </button>


                        <button
                            class="carousel-control-next"
                            type="button"
                            data-bs-target="#carouselProducto"
                            data-bs-slide="next">

                            <span
                                class="carousel-control-next-icon bg-dark rounded-circle"></span>

                        </button>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- ==================================================
             INFORMACIÓN
        ================================================== -->

        <div class="col-lg-6">


            <!-- NOMBRE -->

            <h2 class="fw-bold">

                <?= htmlspecialchars(
                    $producto['nombre']
                ); ?>

            </h2>


            <!-- MARCA -->

            <?php if (!empty($producto['marca'])): ?>

                <p class="text-muted">

                    <i class="bi bi-tag"></i>

                    <?= htmlspecialchars(
                        $producto['marca']
                    ); ?>

                </p>

            <?php endif; ?>


            <!-- ==================================================
                 CALIFICACIÓN
            ================================================== -->

            <div class="mb-3">

                <?php

                $estrellaCompleta =
                    floor($promedio);

                $media =
                    (
                        $promedio -
                        $estrellaCompleta
                    ) >= 0.5
                    ? 1
                    : 0;

                $estrellaVacia =
                    5 -
                    (
                        $estrellaCompleta +
                        $media
                    );

                ?>


                <?php
                for (
                    $i = 0;
                    $i < $estrellaCompleta;
                    $i++
                ):
                ?>

                    <i class="bi bi-star-fill text-warning"></i>

                <?php endfor; ?>


                <?php if ($media): ?>

                    <i class="bi bi-star-half text-warning"></i>

                <?php endif; ?>


                <?php
                for (
                    $i = 0;
                    $i < $estrellaVacia;
                    $i++
                ):
                ?>

                    <i class="bi bi-star text-secondary"></i>

                <?php endfor; ?>


                <span class="fw-bold ms-2">

                    <?= number_format(
                        $promedio,
                        1
                    ); ?>

                </span>


                <?php if ($totalOpiniones > 0): ?>

                    <small class="text-muted">

                        (<?= $totalOpiniones; ?>
                        opiniones)

                    </small>

                <?php else: ?>

                    <small class="text-muted">

                        Sin opiniones

                    </small>

                <?php endif; ?>

            </div>


            <!-- ==================================================
                 PRECIO
            ================================================== -->

            <div class="mb-4">


                <?php if (
                    floatval(
                        $producto['precio_anterior']
                    ) > 0
                ): ?>

                    <div class="text-muted text-decoration-line-through">

                        <?= moneda(
                            $producto['precio_anterior']
                        ); ?>

                    </div>

                <?php endif; ?>


                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <h1 class="text-primary fw-bold mb-0">

                        <?= moneda(
                            $precioFinal
                        ); ?>

                    </h1>


                    <?php if (
                        intval(
                            $producto['descuento']
                        ) > 0
                    ): ?>

                        <span class="badge bg-danger">

                            -<?= intval(
                                    $producto['descuento']
                                ); ?>%

                        </span>

                    <?php endif; ?>

                </div>
            </div>


            <!-- ==================================================
                 BENEFICIOS
            ================================================== -->

            <div class="row g-2 mb-4">


                <!-- ENVÍO -->

                <div class="col-4">

                    <div
                        class="
                            card
                            text-center
                            border
                            h-100
                        ">

                        <div class="card-body p-2">

                            <i
                                class="
                                    bi
                                    bi-truck
                                    text-success
                                    fs-3
                                "></i>

                            <div class="small">

                                <?= !empty($producto['envio_gratis'])
                                    ? 'Envío gratis'
                                    : 'Envío'; ?>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- GARANTÍA -->

                <div class="col-4">

                    <div
                        class="
                            card
                            text-center
                            border
                            h-100
                        ">

                        <div class="card-body p-2">

                            <i
                                class="
                                    bi
                                    bi-shield-check
                                    text-primary
                                    fs-3
                                "></i>

                            <div class="small">

                                Garantía

                            </div>

                        </div>

                    </div>

                </div>


                <!-- STOCK -->

                <div class="col-4">

                    <div
                        class="
                            card
                            text-center
                            border
                            h-100
                        ">

                        <div class="card-body p-2">


                            <?php if (
                                intval(
                                    $producto['stock']
                                ) > 0
                            ): ?>

                                <i
                                    class="
                                        bi
                                        bi-box-seam
                                        text-success
                                        fs-3
                                    "></i>

                                <div
                                    class="
                                        small
                                        fw-bold
                                        text-success
                                    ">

                                    Disponible

                                </div>

                                <small class="text-muted">

                                    <?= intval(
                                        $producto['stock']
                                    ); ?>

                                    unidades

                                </small>

                            <?php else: ?>

                                <i
                                    class="
                                        bi
                                        bi-x-circle
                                        text-danger
                                        fs-3
                                    "></i>

                                <div
                                    class="
                                        small
                                        fw-bold
                                        text-danger
                                    ">

                                    Agotado

                                </div>

                                <small class="text-muted">

                                    Sin stock

                                </small>

                            <?php endif; ?>


                        </div>

                    </div>

                </div>


            </div>


            <!-- ==================================================
                 BOTONES
            ================================================== -->

            <div class="d-grid gap-2">


                <?php if (
                    intval(
                        $producto['stock']
                    ) > 0
                ): ?>


                    <button
                        class="
                            btn
                            btn-primary
                            btn-lg
                            btnAgregar
                        "
                        data-id="<?= $producto['idProducto']; ?>">

                        <i class="bi bi-cart-plus"></i>

                        Agregar al carrito

                    </button>


                <?php else: ?>


                    <button
                        class="
                            btn
                            btn-secondary
                            btn-lg
                        "
                        disabled>

                        <i class="bi bi-x-circle"></i>

                        Producto agotado

                    </button>


                <?php endif; ?>


                <button
                    class="
                        btn
                        btn-outline-danger
                        btnFavorito
                    "
                    data-id="<?= $producto['idProducto']; ?>">

                    <i class="bi bi-heart"></i>

                    Agregar a favoritos

                </button>


            </div>


        </div>

    </div>


    <!-- ==================================================
         DESCRIPCIÓN
    ================================================== -->

    <div
        class="
            card
            border-0
            shadow-sm
            mt-4
        ">

        <div class="card-header bg-white">

            <h5 class="fw-bold mb-0">

                <i class="bi bi-card-text me-2"></i>

                Descripción del producto

            </h5>

        </div>


        <div class="card-body">

            <?= nl2br(
                htmlspecialchars(
                    $producto['descripcion'] ?? ''
                )
            ); ?>

        </div>

    </div>


    <!-- ==================================================
         TESTIMONIOS
    ================================================== -->

    <div class="mt-4">

        <h4 class="fw-bold mb-3">

            <i class="bi bi-chat-square-text"></i>

            Opiniones de clientes

        </h4>


        <?php
        if (
            $testimonios &&
            mysqli_num_rows($testimonios) > 0
        ):
        ?>


            <?php
            while (
                $test =
                mysqli_fetch_assoc(
                    $testimonios
                )
            ):
            ?>


                <div
                    class="
                        card
                        shadow-sm
                        border-0
                        mb-3
                    ">

                    <div class="card-body">


                        <div
                            class="
                                d-flex
                                justify-content-between
                            ">

                            <div>

                                <h6 class="fw-bold mb-0">

                                    <?= htmlspecialchars(
                                        $test['nombre']
                                    ); ?>

                                </h6>

                                <small class="text-success">

                                    Cliente verificado

                                </small>

                            </div>


                            <div>

                                <?php
                                for (
                                    $i = 1;
                                    $i <= 5;
                                    $i++
                                ):
                                ?>

                                    <i
                                        class="
                                            bi
                                            <?= $i <=
                                                intval(
                                                    $test['calificacion']
                                                )
                                                ? 'bi-star-fill text-warning'
                                                : 'bi-star text-secondary'; ?>
                                        "></i>

                                <?php endfor; ?>

                            </div>

                        </div>


                        <p class="mt-3">

                            <?= nl2br(
                                htmlspecialchars(
                                    $test['comentario']
                                )
                            ); ?>

                        </p>


                        <small class="text-muted">

                            <?= htmlspecialchars(
                                $test['fecha']
                            ); ?>

                        </small>


                    </div>

                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="alert alert-light text-center">

                Este producto todavía no tiene opiniones.

            </div>


        <?php endif; ?>


    </div>

</div>