<?php
require_once "./controladores/obtener_categorias.php";
?>

<section class="container my-5">

    <div class="text-center mb-5">

        <span class="badge bg-primary px-3 py-2 fs-6">
            Explora nuestras categorías
        </span>

        <h2 class="fw-bold mt-3">
            Todo lo que necesitas en tecnología
        </h2>

        <p class="text-secondary">
            Productos de calidad y servicios profesionales para empresas y hogares.
        </p>

    </div>

    <div class="row g-4">

        <?php while ($categoria = mysqli_fetch_assoc($resultadoCategorias)) { ?>

            <div class="col-xl-2 col-lg-3 col-md-4 col-6">

                <a
                    href="tienda.php?id=<?= $categoria["id_categorias"] ?>"
                    class="text-decoration-none">

                    <div class="card categoria-card border-0 shadow-sm h-100 text-center p-4">

                        <div class="mb-3">

                            <?php if (!empty($categoria["imagen"])) { ?>

                                <img

                                    src="mostrar_categoria.php?id=<?= $categoria["id_categorias"] ?>"

                                    class="img-fluid rounded-circle border"

                                    style="width:90px;height:90px;object-fit:cover;">

                            <?php } else { ?>

                                <div
                                    class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"

                                    style="width:90px;height:90px;">

                                    <i class="bi bi-grid fs-2"></i>

                                </div>

                            <?php } ?>

                        </div>

                        <h5 class="fw-bold">

                            <?= htmlspecialchars($categoria["nombre"]) ?>

                        </h5>

                        <small class="text-muted">

                            <?= intval($categoria["totalProductos"]) ?>

                            productos

                        </small>

                    </div>

                </a>

            </div>

        <?php } ?>

    </div>

</section>