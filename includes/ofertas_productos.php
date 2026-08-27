<?php

require_once './controladores/obtener_ofertas.php';

?>
<section class="ofertas-section">

    <div class="container">

        <div class="row mb-5">

            <div class="col-lg-8">

                <span class="badge bg-danger fs-6">

                    🔥 OFERTAS FLASH

                </span>

                <h2 class="ofertas-title mt-3">

                    Aprovecha nuestras

                    <span>

                        Super Ofertas

                    </span>

                </h2>

                <p class="text-secondary">

                    Los mejores descuentos en laptops, accesorios,
                    componentes, redes, CCTV y mucho más.

                </p>

            </div>

            <div class="col-lg-4 text-lg-end">

                <a href="ofertas.php"

                    class="btn btn-primary btn-lg">

                    Ver Todas las Ofertas

                </a>

            </div>

        </div>

        <div class="row g-4">

            <?php

            while ($producto = mysqli_fetch_assoc($ofertas)) {
            ?>
                <div class="col-xl-3 col-lg-4 col-md-6">

                    <div class="card oferta-card h-100 shadow-sm">

                        <!-- Imagen del Producto -->
                        <div class="product-image">

                            <!-- Descuento -->
                            <span class="badge bg-danger badge-descuento">
                                -<?= $producto['descuento']; ?>%
                            </span>

                            <!-- Producto Nuevo -->
                            <span class="badge bg-success badge-nuevo">
                                Nuevo
                            </span>

                            <!-- Imagen -->
                            <img
                                src="assets/productos/<?= $producto['imagen']; ?>"
                                class="img-fluid oferta-img"
                                alt="<?= htmlspecialchars($producto['nombre']); ?>">

                            <!-- Botones flotantes -->
                            <div class="acciones-producto">

                                <button
                                    class="btn-float btn-favorito btnFavorito"
                                    data-id="<?= $producto['idProducto']; ?>">

                                    <i class="bi bi-heart"></i>

                                </button>

                                <button
                                    class="btn-float btn-vista btnVista"
                                    data-id="<?= $producto['idProducto']; ?>">

                                    <i class="bi bi-eye"></i>

                                </button>

                                <button
                                    class="btn-float btn-comparar btnComparar"
                                    data-id="<?= $producto['idProducto']; ?>">

                                    <i class="bi bi-arrow-left-right"></i>

                                </button>

                            </div>

                        </div>

                        <!-- Información -->

                        <div class="card-body d-flex flex-column">

                            <!-- Categoría -->

                            <small class="text-primary fw-semibold">

                                <?= htmlspecialchars($producto['nombreCategoria']); ?>

                            </small>

                            <!-- Marca -->

                            <div class="mb-2">

                                <span class="badge bg-light text-dark border">

                                    <?= htmlspecialchars($producto['nombreMarca']); ?>

                                </span>

                            </div>

                            <!-- Nombre -->

                            <h5 class="fw-bold mb-2">

                                <?= htmlspecialchars($producto['nombre']); ?>

                            </h5>

                            <!-- Rating -->

                            <div class="rating mb-3">

                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>

                                <small class="text-secondary ms-2">

                                    (124)

                                </small>

                            </div>

                            <!-- Precio -->

                            <div class="mb-3">

                                <div class="precio-anterior">

                                    S/
                                    <?= number_format($producto['precio_anterior'], 2); ?>

                                </div>

                                <div class="precio-oferta">

                                    S/
                                    <?= number_format($producto['precio'], 2); ?>

                                </div>

                            </div>

                            <!-- Información -->

                            <div class="mb-3">

                                <div class="info-extra mb-1">

                                    <i class="bi bi-truck text-success"></i>

                                    Envío Gratis

                                </div>

                                <div class="info-extra mb-1">

                                    <i class="bi bi-shield-check text-primary"></i>

                                    Garantía Oficial

                                </div>

                                <div class="info-extra">

                                    <i class="bi bi-box-seam text-warning"></i>

                                    Stock:
                                    <?php if ($producto['stock'] > 10) { ?>

                                        <div class="stock-disponible">

                                            <i class="bi bi-check-circle-fill"></i>

                                            Stock Disponible

                                            (<?= $producto['stock']; ?>)

                                        </div>

                                    <?php } else { ?>

                                        <div class="stock-bajo">

                                            <i class="bi bi-exclamation-circle-fill"></i>

                                            Quedan solamente

                                            <?= $producto['stock']; ?>

                                        </div>

                                    <?php } ?>

                                </div>

                            </div>

                            <!-- Botones -->

                            <div class="mt-auto d-grid gap-2">

                                <a
                                    href="producto.php?id=<?= $producto['idProducto']; ?>"
                                    class="btn btn-outline-primary">

                                    <i class="bi bi-search"></i>

                                    Ver Detalle

                                </a>

                                <button
                                    class="btn btn-success btn-comprar btnAgregar"
                                    data-id="<?= $producto['idProducto']; ?>">

                                    <i class="bi bi-cart-plus-fill"></i>

                                    Agregar al Carrito

                                </button>

                            </div>

                        </div>

                    </div>

                </div>
            <?php } ?>

        </div>

    </div>

</section>
<div class="modal fade" id="modalVistaRapida" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header">

                <h5 class="modal-title">

                    Vista rápida

                </h5>

                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div
                class="modal-body"
                id="contenidoVistaRapida">

                <div class="text-center p-5">

                    <div class="spinner-border text-primary"></div>

                </div>

            </div>

        </div>

    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {

        /* ==========================
           AGREGAR CARRITO
        ========================== */

        document.querySelectorAll(".btnAgregar").forEach(btn => {

            btn.onclick = function() {

                const id = this.dataset.id;

                fetch("ajax/agregar_carrito.php", {

                        method: "POST",

                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },

                        body: "idProducto=" + id

                    })

                    .then(r => r.json())

                    .then(res => {

                        if (res.estado == "ok") {

                            Swal.fire({

                                icon: "success",

                                title: "Producto agregado",

                                text: "Se agregó al carrito correctamente",

                                timer: 1800,

                                showConfirmButton: false

                            });

                            actualizarContador();

                        } else {

                            Swal.fire({

                                icon: "error",

                                title: "Error",

                                text: res.mensaje

                            });

                        }

                    });

            }

        });

        /* ==========================
           FAVORITOS
        ========================== */

        document.querySelectorAll(".btnFavorito").forEach(btn => {

            btn.onclick = function() {

                const id = this.dataset.id;

                fetch("ajax/favoritos.php", {

                        method: "POST",

                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },

                        body: "idProducto=" + id

                    })

                    .then(r => r.json())

                    .then(res => {

                        Swal.fire({

                            icon: "success",

                            title: "Favoritos",

                            text: res.mensaje,

                            timer: 1500,

                            showConfirmButton: false

                        });

                    });

            }

        });

        /* ==========================
           COMPARAR
        ========================== */

        document.querySelectorAll(".btnComparar").forEach(btn => {

            btn.onclick = function() {

                const id = this.dataset.id;

                fetch("ajax/comparar.php", {

                        method: "POST",

                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },

                        body: "idProducto=" + id

                    })

                    .then(r => r.json())

                    .then(res => {

                        Swal.fire({

                            icon: "info",

                            title: "Comparador",

                            text: res.mensaje,

                            timer: 1500,

                            showConfirmButton: false

                        });

                    });

            }

        });

        /* ==========================
           VISTA RÁPIDA
        ========================== */

        document.querySelectorAll(".btnVista").forEach(btn => {

            btn.onclick = function() {

                const id = this.dataset.id;

                fetch("ajax/vista_rapida.php?id=" + id)

                    .then(r => r.text())

                    .then(html => {

                        document.getElementById("contenidoVistaRapida").innerHTML = html;

                        const modal = new bootstrap.Modal(

                            document.getElementById("modalVistaRapida")

                        );

                        modal.show();

                    });

            }

        });

    });

    /* ==========================
       CONTADOR CARRITO
    ========================== */

    function actualizarContador() {

        fetch("./ajax/contador_carrito.php")

            .then(r => r.text())

            .then(total => {

                const contador = document.getElementById("contadorCarrito");

                if (contador) {

                    contador.innerHTML = total;

                }

            });

    }
</script>