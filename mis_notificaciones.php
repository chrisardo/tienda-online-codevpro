<?php
//======================================================
// CoDevPro Technology
// mis_notificaciones.php
//======================================================

session_start();

if (!isset($_SESSION["idCliente"])) {

    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <?php include "includes/head.php"; ?>

    <title>Mis Notificaciones | CoDevPro Technology</title>

</head>

<body class="bg-light">

    <!--=====================================
    NAVBAR
    ======================================-->

    <?php include "includes/navbar.php"; ?>


    <!--=====================================
    CONTENEDOR PRINCIPAL
    ======================================-->

    <div class="container py-4">


        <!--=====================================
        CABECERA
        ======================================-->

        <div class="card shadow-sm border-0 mb-3">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <div>

                        <h2 class="fw-bold mb-1">

                            <i class="bi bi-bell-fill text-primary"></i>

                            Mis Notificaciones

                        </h2>

                        <p class="text-muted mb-0">

                            Aquí podrás visualizar todas tus notificaciones.

                        </p>

                    </div>

                    <div>

                        <span
                            class="badge bg-primary fs-6"
                            id="cantidadTotalNotificaciones">

                            0 Notificaciones

                        </span>

                    </div>

                </div>

            </div>

        </div>

        <!--=====================================
        ACCIONES RÁPIDAS
        ======================================-->

        <div class="card border-0 shadow-sm mb-3">

            <div class="card-body">

                <div class="row g-2">


                    <div class="col-md-4">

                        <button
                            id="btnMarcarTodas"
                            class="btn btn-primary w-100">

                            <i class="bi bi-check2-all"></i>

                            Marcar todas como leídas

                        </button>

                    </div>


                    <div class="col-md-4">

                        <button
                            id="btnEliminarLeidas"
                            class="btn btn-danger w-100">

                            <i class="bi bi-trash-fill"></i>

                            Eliminar leídas

                        </button>

                    </div>



                    <div class="col-md-4">

                        <button
                            id="btnActualizarNotificaciones"
                            class="btn btn-success w-100">

                            <i class="bi bi-arrow-repeat"></i>

                            Actualizar

                        </button>

                    </div>


                </div>

            </div>

        </div>



        <!--=====================================
        FILTROS
        ======================================-->

        <div class="card border-0 shadow-sm mb-3">

            <div class="card-body">

                <div class="d-flex flex-wrap gap-2">


                    <button
                        class="btn btn-outline-primary filtroNotificacion"
                        data-tipo="todas">

                        Todas

                    </button>


                    <button
                        class="btn btn-outline-secondary filtroNotificacion"
                        data-tipo="sin_leer">

                        Sin leer

                    </button>



                    <button
                        class="btn btn-outline-success filtroNotificacion"
                        data-tipo="pedido">

                        Pedidos

                    </button>


                    <button
                        class="btn btn-outline-danger filtroNotificacion"
                        data-tipo="oferta">

                        Ofertas

                    </button>


                    <button
                        class="btn btn-outline-warning filtroNotificacion"
                        data-tipo="seguridad">

                        Seguridad

                    </button>


                    <button
                        class="btn btn-outline-info filtroNotificacion"
                        data-tipo="producto">

                        Productos

                    </button>


                    <button
                        class="btn btn-outline-dark filtroNotificacion"
                        data-tipo="promocion">

                        Promociones

                    </button>


                    <button
                        class="btn btn-outline-primary filtroNotificacion"
                        data-tipo="cuenta">

                        Cuenta

                    </button>


                    <button
                        class="btn btn-outline-success filtroNotificacion"
                        data-tipo="envio">

                        Envíos

                    </button>


                    <button
                        class="btn btn-outline-secondary filtroNotificacion"
                        data-tipo="testimonio">

                        Testimonios

                    </button>

                </div>

            </div>

        </div>




        <!--=====================================
        CONTENEDOR DE NOTIFICACIONES
        ======================================-->

        <div class="card border-0 shadow-sm">

            <div class="card-body">


                <div id="contenedorMisNotificaciones">

                    <!-- AJAX -->

                </div>


            </div>

        </div>




        <!--=====================================
        PAGINACIÓN
        ======================================-->

        <div
            class="d-flex justify-content-center mt-4">

            <nav>

                <ul
                    class="pagination"
                    id="paginacionNotificaciones">

                    <!-- AJAX -->

                </ul>

            </nav>

        </div>


    </div>



    <!--=====================================
    FOOTER
    ======================================-->

    <?php include "includes/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php include "includes/carrito_offcanvas.php"; ?>

    <script src="js/carrito.js"></script>

    <script src="js/favoritos.js"></script>

    <script src="js/tienda.js"></script>

    <!--=====================================
    JAVASCRIPT
    ======================================-->
    <script src="js/mis_notificaciones.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <!-- AOS -->

    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <script>
        AOS.init({

            duration: 700,

            once: true

        });
    </script>

</body>

</html>