<?php

require_once 'controladores/obtener_marcas.php';

?>

<style>
</style>

<section class="marcas-section">

    <div class="container">

        <div class="text-center mb-5">

            <span class="badge bg-primary fs-6">

                MARCAS

            </span>

            <h2 class="display-5 fw-bold mt-3">

                Trabajamos con las mejores marcas

            </h2>

            <p class="text-secondary">

                Productos originales con garantía.

            </p>

        </div>

        <div class="row g-4">

            <?php while ($marca = mysqli_fetch_assoc($marcas)) { ?>

                <div class="col-xl-3 col-lg-4 col-md-6">

                    <div class="card marca-card h-100 shadow-sm">

                        <div class="card-body text-center">

                            <div class="logo-marca mb-3">
                                <?php if (!empty($marca['imagen'])) : ?>

                                    <img
                                        src="data:image/jpeg;base64,<?= base64_encode($marca['imagen']) ?>"
                                        width="50"
                                        height="50"
                                        style="object-fit:cover;">

                                <?php else : ?>

                                    <?= strtoupper(substr($marca['nombre'], 0, 1)); ?>

                                <?php endif; ?>
                            </div>

                            <h4 class="fw-bold">

                                <?= $marca['nombre']; ?>

                            </h4>

                            <p class="text-secondary">

                                <?= $marca['totalProductos']; ?>

                                Productos

                            </p>

                            <a

                                href="tienda.php?marca=<?= $marca['id_marca']; ?>"

                                class="btn btn-outline-primary rounded-pill">

                                Ver Productos

                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>

        </div>

    </div>

</section>