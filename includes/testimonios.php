<?php

require_once "./controladores/obtener_testimonios.php";

?>

<section class="py-5 bg-light">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">

                Lo que opinan nuestros clientes

            </h2>

            <p class="text-muted">

                Miles de clientes satisfechos respaldan nuestros productos.

            </p>

        </div>

        <div class="row g-4">

            <?php

            if (mysqli_num_rows($testimonios) > 0) {

                while ($fila = mysqli_fetch_assoc($testimonios)) {

            ?>

                    <div class="col-lg-3 col-md-6">

                        <div class="card border-0 shadow h-100">

                            <div class="card-body text-center">

                                <?php if (!empty($fila["imagen"])) { ?>

                                    <img

                                        src="data:image/jpeg;base64,<?= base64_encode($fila['imagen']) ?>"

                                        class="rounded-circle mb-3"

                                        width="80"

                                        height="80"

                                        style="object-fit:cover;">

                                <?php } else { ?>

                                    <i class="bi bi-person"></i>

                                <?php } ?>

                                <h5 class="fw-bold">

                                    <?= htmlspecialchars($fila["cliente"]); ?>

                                </h5>

                                <?php if (!empty($fila["producto"])) { ?>

                                    <small class="text-primary d-block mb-2">

                                        Compró:

                                        <?= htmlspecialchars($fila["producto"]); ?>

                                    </small>

                                <?php } ?>

                                <div class="mb-3">

                                    <?php

                                    for ($i = 1; $i <= 5; $i++) {

                                        if ($i <= $fila["calificacion"]) {

                                            echo '<i class="bi bi-star-fill text-warning"></i>';
                                        } else {

                                            echo '<i class="bi bi-star text-secondary"></i>';
                                        }
                                    }

                                    ?>

                                </div>

                                <p class="text-muted">

                                    "

                                    <?= nl2br(htmlspecialchars($fila["comentario"])); ?>

                                    "

                                </p>

                            </div>

                            <div class="card-footer bg-white text-center">

                                <small class="text-secondary">

                                    <?= date("d/m/Y", strtotime($fila["fecha"])); ?>

                                </small>

                            </div>

                        </div>

                    </div>

                <?php

                }
            } else {

                ?>

                <div class="col-12">

                    <div class="alert alert-info text-center">

                        Aún no existen testimonios registrados.

                    </div>

                </div>

            <?php } ?>

        </div>

    </div>

</section>