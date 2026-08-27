<!--Toda esta parte pertenece a includes/navbar.php-->
<?php


$clienteLogueado = isset($_SESSION["idCliente"]) && $_SESSION["idCliente"] > 0;
/*=========================================
FOTO DEL CLIENTE
=========================================*/

$fotoNavbar = "./assets/img/sin_imagen.png";
if ($clienteLogueado) {

    require_once "./controladores/conexion.php";

    $idCliente = (int)$_SESSION["idCliente"];

    $sql = "SELECT imagen
            FROM clientes
            WHERE idCliente = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $idCliente
        );

        mysqli_stmt_execute($stmt);

        $resultado2 = mysqli_stmt_get_result($stmt);

        if ($cliente2 = mysqli_fetch_assoc($resultado2)) {

            if (!empty($cliente2["imagen"])) {

                $fotoNavbar = "data:image/jpeg;base64," .
                    base64_encode($cliente2["imagen"]);
            }
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">

    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">

            <img src="assets/logos/logo.png"
                width="65">

            <span class="text-primary">

                CODEVPRO

            </span>

            <span class="text-success">

                TECHNOLOGY

            </span>

        </a>

        <button
            class="navbar-toggler"
            data-bs-toggle="collapse"
            data-bs-target="#menu">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div
            class="collapse navbar-collapse"
            id="menu">

            <!-- Menú -->

            <ul class="navbar-nav ms-4">

                <li class="nav-item">

                    <a class="nav-link active fw-semibold"
                        href="index.php">

                        <i class="bi bi-house"></i>

                        Inicio

                    </a>

                </li>
                <?php if (!$clienteLogueado) { ?>
                    <li class="nav-item">

                        <a class="nav-link fw-semibold"
                            href="nosotros.php">

                            <i class="bi bi-building"></i>

                            Nosotros

                        </a>

                    </li>
                <?php } ?>
                <li class="nav-item">

                    <a class="nav-link fw-semibold"
                        href="tienda.php">

                        <i class="bi bi-shop"></i>

                        Tienda

                    </a>

                </li>

                <!--<li class="nav-item">

                    <a class="nav-link fw-semibold"
                        href="#">

                        <i class="bi bi-tools"></i>

                        Servicios

                    </a>

                </li>-->

                <li class="nav-item">

                    <a class="nav-link fw-semibold"
                        href="ofertas.php">

                        <i class="bi bi-fire"></i>

                        Ofertas

                    </a>

                </li>

                <?php if ($clienteLogueado) { ?>

                    <li class="nav-item">

                        <a
                            class="nav-link fw-semibold"
                            href="mis_pedidos.php">

                            <i class="bi bi-bag-check"></i>

                            Mis pedidos

                        </a>

                    </li>

                <?php } ?>

            </ul>

            <!-- Iconos -->

            <!--=========================================
ICONOS DEL NAVBAR
==========================================-->

            <div class="d-flex align-items-center gap-2">

                <?php if ($clienteLogueado) { ?>

                    <!-- FAVORITOS -->

                    <a href="favoritos.php"
                        class="btn btn-light position-relative">

                        <i class="bi bi-heart fs-5"></i>

                        <span
                            id="contadorFavoritos"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                            0

                        </span>

                    </a>
                    <!--======================================================
                    =            NOTIFICACIONES CLIENTE
                    =======================================================-->

                    <li class="nav-item dropdown">

                        <a class="nav-link position-relative"
                            href="#"
                            id="btnNotificaciones"
                            role="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <i class="bi bi-bell fs-5"></i>

                            <!-- Contador -->
                            <span id="contadorNotificaciones"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">

                                0

                            </span>

                        </a>

                        <!-- Dropdown -->
                        <div class="dropdown-menu dropdown-menu-end shadow p-0"
                            style="width: 380px;">

                            <!--=========================================
CABECERA DEL DROPDOWN
==========================================-->

                            <div class="p-3 border-bottom">

                                <div class="d-flex justify-content-between align-items-center">

                                    <div>

                                        <h6 class="mb-0 fw-bold">

                                            <i class="bi bi-bell-fill text-warning me-1"></i>

                                            Notificaciones

                                        </h6>
                                    </div>
                                </div>

                            </div>

                            <!-- Botón marcar todas -->

                            <div class="p-2 border-bottom bg-light">

                                <div class="d-grid gap-2">

                                    <button
                                        type="button"
                                        id="btnMarcarTodasLeidas"
                                        class="btn btn-outline-primary btn-sm">

                                        <i class="bi bi-check2-all me-1"></i>

                                        Marcar todas como leídas

                                    </button>

                                </div>

                            </div>

                            <!-- Contenedor -->
                            <div
                                id="contenedorNotificaciones"
                                class="overflow-auto bg-white">

                                <!-- Aquí se cargarán las notificaciones -->

                                <div class="text-center p-4">

                                    <i class="bi bi-bell-slash fs-1 text-muted"></i>

                                    <p class="mt-3 mb-0 text-muted">

                                        No tienes notificaciones.

                                    </p>

                                </div>

                            </div>

                            <!-- Pie del Dropdown -->
                            <div class="border-top bg-light">

                                <a
                                    href="mis_notificaciones.php"
                                    class="btn btn-light w-100 rounded-0">

                                    <i class="bi bi-eye-fill me-1"></i>

                                    Ver todas las notificaciones

                                </a>

                            </div>

                        </div>

                    </li>

                <?php } ?>


                <!-- CARRITO -->

                <button
                    class="btn btn-outline-primary position-relative"
                    id="btnAbrirCarrito"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasCarrito">

                    <i class="bi bi-cart3 fs-5"></i>

                    <span
                        id="contadorCarrito"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">

                        0

                    </span>

                </button>

            </div>
            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle d-flex align-items-center"
                    href="#"
                    id="menuPerfil"
                    role="button"
                    data-bs-toggle="dropdown">

                    <?php if ($clienteLogueado) { ?>
                        <img
                            id="fotoNavbar"
                            src="<?= $fotoNavbar; ?>"
                            alt="Perfil"
                            class="rounded-circle border shadow-sm"
                            style="
                            width:40px;
                            height:40px;
                            object-fit:cover;
                        ">
                    <?php } else { ?>
                        <i class="bi bi-person fs-5"></i>
                        <span>Mi Cuenta</span>
                    <?php } ?>

                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow">

                    <?php if ($clienteLogueado) { ?>

                        <li>
                            <a class="dropdown-item" href="perfil.php">
                                <i class="bi bi-person"></i> Mi Perfil
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>

                    <?php } else { ?>

                        <li>
                            <a class="dropdown-item" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="registro_cuenta.php">
                                <i class="bi bi-person-plus"></i> Registrarme
                            </a>
                        </li>

                    <?php } ?>

                </ul>
            </li>
        </div>

    </div>

</nav>