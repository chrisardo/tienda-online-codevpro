<?php
//Todo esto es de carrito.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";
require_once "controladores/token_carrito.php";

/*
|--------------------------------------------------------------------------
| DATOS EMPRESA
|--------------------------------------------------------------------------
*/

$sqlEmpresa = "SELECT *
               FROM usuario_acceso
               ORDER BY id_user ASC
               LIMIT 1";

$resultEmpresa = mysqli_query($conexion, $sqlEmpresa);

$empresa = mysqli_fetch_assoc($resultEmpresa);

/*
|--------------------------------------------------------------------------
| TOKEN DEL CARRITO
|--------------------------------------------------------------------------
*/

$token = obtenerTokenCarrito();

/*
|--------------------------------------------------------------------------
| CONTADOR DEL CARRITO
|--------------------------------------------------------------------------
*/

$idCliente = $_SESSION["idCliente"] ?? 0;

if ($idCliente > 0) {

    $sqlContador = "SELECT IFNULL(SUM(cantidad),0) total
                    FROM carrito_online
                    WHERE idCliente=?
                    AND estado='pendiente'";

    $stmt = mysqli_prepare($conexion, $sqlContador);

    mysqli_stmt_bind_param($stmt, "i", $idCliente);
} else {

    $sqlContador = "SELECT IFNULL(SUM(cantidad),0) total
                    FROM carrito_online
                    WHERE token=?
                    AND estado='pendiente'";

    $stmt = mysqli_prepare($conexion, $sqlContador);

    mysqli_stmt_bind_param($stmt, "s", $token);
}

mysqli_stmt_execute($stmt);

$resContador = mysqli_stmt_get_result($stmt);

$filaContador = mysqli_fetch_assoc($resContador);

$contadorCarrito = intval($filaContador["total"]);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>

        Carrito de Compras |

        <?= htmlspecialchars($empresa["nombreEmpresa"]); ?>

    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">
    <?php include "includes/navbar.php"; ?>
    <div class="container py-4">
        <nav aria-label="breadcrumb">

            <ol class="breadcrumb">

                <li class="breadcrumb-item">

                    <a href="index.php">

                        Inicio

                    </a>

                </li>

                <li class="breadcrumb-item">

                    <a href="tienda.php">

                        Tienda

                    </a>

                </li>

                <li class="breadcrumb-item active">

                    Carrito

                </li>

            </ol>

        </nav>
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <div>

                        <h2 class="fw-bold mb-1">

                            <i class="bi bi-cart3 text-primary"></i>

                            Carrito de Compras

                        </h2>

                        <p class="text-muted mb-0">

                            Revisa tus productos antes de finalizar tu compra.

                        </p>

                    </div>

                    <div>

                        <span class="badge bg-primary fs-6">

                            <span id="contadorCarritoPagina">

                                <?= $contadorCarrito; ?>

                            </span>

                            Productos

                        </span>

                    </div>

                </div>

            </div>

        </div>
        <div class="row">

            <div class="col-lg-8 mb-4">

                <div
                    class="card border-0 shadow-sm">

                    <div
                        class="card-header bg-white fw-bold">

                        <i class="bi bi-bag"></i>

                        Productos agregados

                    </div>

                    <div
                        class="card-body p-0"
                        id="contenedorCarrito">

                        <?php
                        include "includes/obtener_carrito_pagina.php";
                        ?>

                    </div>

                </div>

            </div>
            <div class="col-lg-4">

                <div
                    class="checkout-resumen"
                    id="resumenCompra">

                    <?php
                    include "includes/resumen_compra.php";
                    ?>

                </div>

            </div>

        </div>
    </div>
    <?php include "includes/carrito_offcanvas.php"; ?>
    <script src="js/carrito.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/notificaciones.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</body>

</html>