<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body p-4">

        <!-- Categoría -->

        <span class="badge bg-primary mb-2">

            <?= htmlspecialchars($producto['categoria']); ?>

        </span>

        <!-- Marca -->

        <span class="badge bg-secondary mb-2">

            <?= htmlspecialchars($producto['marca']); ?>

        </span>

        <!-- Nombre -->

        <h2 class="fw-bold mt-2">

            <?= htmlspecialchars($producto['nombre']); ?>

        </h2>

        <!-- Código -->

        <p class="text-muted mb-3">

            Código:

            <strong><?= htmlspecialchars($producto['codigo']); ?></strong>

        </p>

        <hr>

        <!-- Precio -->

        <?php if($producto['oferta']==1){ ?>

            <div class="mb-2">

                <span class="text-decoration-line-through text-secondary fs-5">

                    S/ <?= number_format($producto['precio_anterior'],2); ?>

                </span>

                <span class="badge bg-danger ms-2">

                    -<?= $producto['descuento']; ?>%

                </span>

            </div>

        <?php } ?>

        <h1 class="text-primary fw-bold">

            S/ <?= number_format($producto['precio'],2); ?>

        </h1>

        <hr>

        <!-- Stock -->

        <?php if($producto['stock']>0){ ?>

            <div class="alert alert-success">

                <i class="bi bi-check-circle-fill"></i>

                Stock disponible:

                <strong><?= $producto['stock']; ?></strong>

                unidades

            </div>

        <?php }else{ ?>

            <div class="alert alert-danger">

                <i class="bi bi-x-circle-fill"></i>

                Producto agotado

            </div>

        <?php } ?>

        <!-- Cantidad -->

        <label class="form-label fw-bold">

            Cantidad

        </label>

        <div class="input-group mb-4" style="max-width:200px;">

            <button
                class="btn btn-outline-secondary"
                id="btnMenos"
                type="button">

                <i class="bi bi-dash"></i>

            </button>

            <input
                type="number"
                class="form-control text-center"
                id="cantidadProducto"
                value="1"
                min="1"
                max="<?= $producto['stock']; ?>">

            <button
                class="btn btn-outline-secondary"
                id="btnMas"
                type="button">

                <i class="bi bi-plus"></i>

            </button>

        </div>

        <!-- Botones -->

        <div class="d-grid gap-3">

            <button

                class="btn btn-primary btn-lg"

                id="btnAgregarCarrito"

                data-id="<?= $producto['idProducto']; ?>">

                <i class="bi bi-cart-plus-fill"></i>

                Agregar al carrito

            </button>

            <button

                class="btn btn-success btn-lg"

                id="btnComprarAhora"

                data-id="<?= $producto['id_producto']; ?>">

                <i class="bi bi-lightning-fill"></i>

                Comprar ahora

            </button>

        </div>

        <hr>

        <!-- Beneficios -->

        <div class="row text-center">

            <div class="col-4">

                <i class="bi bi-shield-check display-6 text-success"></i>

                <small class="d-block mt-2">

                    Garantía

                </small>

            </div>

            <div class="col-4">

                <i class="bi bi-truck display-6 text-primary"></i>

                <small class="d-block mt-2">

                    Envíos

                </small>

            </div>

            <div class="col-4">

                <i class="bi bi-credit-card display-6 text-warning"></i>

                <small class="d-block mt-2">

                    Pago seguro

                </small>

            </div>

        </div>

        <hr>

        <!-- Descripción -->

        <h5 class="fw-bold">

            Descripción

        </h5>

        <p class="text-secondary">

            <?= nl2br(htmlspecialchars($producto['descripcion'])); ?>

        </p>

    </div>

</div>