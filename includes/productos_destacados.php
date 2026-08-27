<?php

include './controladores/obtener_productos_destacados.php';

?>

<section class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <span class="badge bg-primary">

                DESTACADOS

            </span>

            <h2 class="fw-bold">

                Productos Recomendados

            </h2>

        </div>

        <a href="tienda.php" class="btn btn-outline-primary">

            Ver todos

        </a>

    </div>

    <div class="row g-4">

        <?php

        while ($producto = mysqli_fetch_assoc($resultadoProductos)) {

            include 'card_producto.php';
        }

        ?>

    </div>

</section>