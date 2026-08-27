<?php

require_once "./controladores/obtener_productos_relacionados.php";

?>

<section class="mt-5">

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h3 class="fw-bold mb-0">

                    <i class="bi bi-grid"></i>

                    Productos relacionados

                </h3>

                <a href="tienda.php"

                    class="btn btn-outline-primary">

                    Ver todos

                </a>

            </div>

            <?php

            if (mysqli_num_rows($productosRelacionados) > 0) {

            ?>

                <div class="row g-4">

                    <?php

                    while ($producto = mysqli_fetch_assoc($productosRelacionados)) {

                        include "card_producto.php";
                    }

                    ?>

                </div>

            <?php

            } else {

            ?>

                <div class="alert alert-warning mb-0">

                    No existen productos relacionados.

                </div>

            <?php

            }

            ?>

        </div>

    </div>

</section>