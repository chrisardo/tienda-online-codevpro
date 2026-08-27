<?php
require_once "./controladores/obtener_filtros.php";
?>
<!--Toda esta parte es de includes/sidebar_filtros.php-->
<div class="card shadow-sm border-0 sticky-top" style="top:90px;">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            <i class="bi bi-funnel-fill"></i>

            Filtros

        </h5>

    </div>

    <div class="card-body">

        <!-- Categorías -->

        <h6 class="fw-bold">

            Categorías

        </h6>

        <?php while($categoria=mysqli_fetch_assoc($categorias)){ ?>

            <div class="form-check mb-2">

                <input

                    class="form-check-input filtroCategoria"

                    type="checkbox"

                    value="<?= $categoria['id_categorias'];?>"

                    id="cat<?= $categoria['id_categorias'];?>">

                <label

                    class="form-check-label"

                    for="cat<?= $categoria['id_categorias'];?>">

                    <?= $categoria['nombre'];?>

                </label>

            </div>

        <?php } ?>

        <hr>

        <!-- Marcas -->

        <h6 class="fw-bold">

            Marcas

        </h6>

        <?php while($marca=mysqli_fetch_assoc($marcas)){ ?>

            <div class="form-check mb-2">

                <input

                    class="form-check-input filtroMarca"

                    type="checkbox"

                    value="<?= $marca['id_marca'];?>"

                    id="marca<?= $marca['id_marca'];?>">

                <label

                    class="form-check-label"

                    for="marca<?= $marca['id_marca'];?>">

                    <?= $marca['nombre'];?>

                </label>

            </div>

        <?php } ?>

        <hr>

        <!-- Precio -->

        <h6 class="fw-bold">

            Precio

        </h6>

        <div class="mb-3">

            <label>

                Desde

            </label>

            <input

                type="number"

                id="precioMin"

                class="form-control"

                value="<?= $precio['minimo'];?>">

        </div>

        <div class="mb-3">

            <label>

                Hasta

            </label>

            <input

                type="number"

                id="precioMax"

                class="form-control"

                value="<?= $precio['maximo'];?>">

        </div>

        <hr>

        <!-- Disponibilidad -->

        <h6 class="fw-bold">

            Disponibilidad

        </h6>

        <div class="form-check">

            <input

                class="form-check-input"

                type="checkbox"

                id="stockDisponible">

            <label

                class="form-check-label"

                for="stockDisponible">

                Solo productos disponibles

            </label>

        </div>

        <hr>

        <!-- Botones -->

        <div class="d-grid gap-2">

            <button

                id="btnAplicarFiltros"

                class="btn btn-primary">

                <i class="bi bi-search"></i>

                Aplicar filtros

            </button>

            <button

                id="btnLimpiar"

                class="btn btn-outline-secondary">

                <i class="bi bi-arrow-clockwise"></i>

                Limpiar

            </button>

        </div>

    </div>

</div>