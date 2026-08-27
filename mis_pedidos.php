<?php
//======================================================
// CoDevPro Technology
// Archivo: mis_pedidos.php
// Módulo: Mis Pedidos
// Sistema: Inventa
//======================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//======================================================
// VALIDAR CLIENTE
//======================================================

if (!isset($_SESSION["idCliente"]) || (int)$_SESSION["idCliente"] <= 0) {

    header("Location: login.php");
    exit;
}
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
require_once "controladores/conexion.php";
/*=========================================
DATOS EMPRESA
=========================================*/

$sqlEmpresa = "SELECT nombreEmpresa, imagen
FROM usuario_acceso
LIMIT 1";

$resEmpresa = mysqli_query($conexion, $sqlEmpresa);

$empresa = mysqli_fetch_assoc($resEmpresa);
?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mis pedidos | <?= $empresa['nombreEmpresa']; ?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/hero.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/categorias.css">
    <link rel="stylesheet" href="css/card_producto.css">
    <link rel="stylesheet" href="css/ofertas_flash.css">
    <link rel="stylesheet" href="css/ofertas_productos.css">
    <link rel="stylesheet" href="css/marca.css">
    <link rel="stylesheet" href="css/ofertas.css">
    <link rel="stylesheet" href="css/ofertas_pagina.css">
    <!-- Bootstrap Icons -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- AOS -->

    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- SweetAlert -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body class="bg-light">
    <?php
    include "includes/navbar.php";
    ?>

    <!--=====================================
BREADCRUMB
======================================-->

    <section class="container mt-3">

        <nav aria-label="breadcrumb">

            <ol class="breadcrumb bg-white shadow-sm rounded p-3 mb-0">

                <li class="breadcrumb-item">

                    <a href="index.php" class="text-decoration-none">

                        <i class="bi bi-house"></i>

                        Inicio

                    </a>

                </li>

                <li class="breadcrumb-item active" aria-current="page">

                    Mis pedidos

                </li>

            </ol>

        </nav>

    </section>


    <!--=====================================
ENCABEZADO
======================================-->

    <section class="container mt-4 mb-4">

        <div class="card border-0 shadow">

            <div class="card-body">

                <div class="row align-items-center g-3">

                    <div class="col-lg-8">

                        <h2 class="fw-bold mb-2">

                            <i class="bi bi-bag-check-fill text-primary"></i>

                            Mis pedidos

                        </h2>

                        <p class="text-muted mb-0">

                            Consulta el historial de tus compras y realiza seguimiento
                            del estado de cada pedido.

                        </p>

                    </div>

                    <div class="col-lg-4 text-lg-end">

                        <a
                            href="tienda.php"
                            class="btn btn-primary">

                            <i class="bi bi-cart-plus"></i>

                            Seguir comprando

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!--=====================================
DASHBOARD DE ESTADOS
======================================-->

    <section class="container mb-4">

        <div class="row g-3">

            <!--=====================================
        PENDIENTES
        ======================================-->

            <div class="col-md-6 col-lg-4 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <small class="text-muted">

                                    Pendientes

                                </small>

                                <h2
                                    class="fw-bold mb-0"
                                    id="totalPendientes">

                                    0

                                </h2>

                            </div>

                            <div class="rounded-circle bg-warning bg-opacity-25 p-3">

                                <i class="bi bi-hourglass-split text-warning fs-3"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================
        CONFIRMADOS
        ======================================-->

            <div class="col-md-6 col-lg-4 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <small class="text-muted">

                                    Confirmados

                                </small>

                                <h2
                                    class="fw-bold mb-0"
                                    id="totalConfirmados">

                                    0

                                </h2>

                            </div>

                            <div class="rounded-circle bg-secondary bg-opacity-25 p-3">

                                <i class="bi bi-check2-square text-secondary fs-3"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================
        PREPARANDO
        ======================================-->

            <div class="col-md-6 col-lg-4 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <small class="text-muted">

                                    Preparando

                                </small>

                                <h2
                                    class="fw-bold mb-0"
                                    id="totalPreparando">

                                    0

                                </h2>

                            </div>

                            <div class="rounded-circle bg-info bg-opacity-25 p-3">

                                <i class="bi bi-box-seam text-info fs-3"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================
        EN CAMINO
        ASIGNADO + OBTENIDO + ENVIADO
        ======================================-->

            <div class="col-md-6 col-lg-4 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <small class="text-muted">

                                    En camino

                                </small>

                                <h2
                                    class="fw-bold mb-0"
                                    id="totalEnCamino">

                                    0

                                </h2>

                            </div>

                            <div class="rounded-circle bg-primary bg-opacity-25 p-3">

                                <i class="bi bi-truck text-primary fs-3"></i>

                            </div>

                        </div>

                        <small class="text-muted">

                            Asignado, recogido o enviado

                        </small>

                    </div>

                </div>

            </div>


            <!--=====================================
        ENTREGADOS
        ======================================-->

            <div class="col-md-6 col-lg-4 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <small class="text-muted">

                                    Entregados

                                </small>

                                <h2
                                    class="fw-bold mb-0"
                                    id="totalEntregados">

                                    0

                                </h2>

                            </div>

                            <div class="rounded-circle bg-success bg-opacity-25 p-3">

                                <i class="bi bi-check-circle text-success fs-3"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================
        NO ENTREGADOS
        ======================================-->

            <div class="col-md-6 col-lg-4 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <small class="text-muted">

                                    No entregados

                                </small>

                                <h2
                                    class="fw-bold mb-0"
                                    id="totalNoEntregados">

                                    0

                                </h2>

                            </div>

                            <div class="rounded-circle bg-danger bg-opacity-25 p-3">

                                <i class="bi bi-exclamation-circle text-danger fs-3"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=====================================
        CANCELADOS
        ======================================-->

            <div class="col-md-6 col-lg-4 col-xl-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <small class="text-muted">

                                    Cancelados

                                </small>

                                <h2
                                    class="fw-bold mb-0"
                                    id="totalCancelados">

                                    0

                                </h2>

                            </div>

                            <div class="rounded-circle bg-dark bg-opacity-10 p-3">

                                <i class="bi bi-x-circle text-danger fs-3"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!--=====================================
BUSCADOR + FILTROS
======================================-->

    <section class="container mb-4">

        <div class="card border-0 shadow">

            <div class="card-body">

                <div class="row g-3">

                    <!--=====================================
                BUSCAR
                ======================================-->

                    <div class="col-lg-4">

                        <label class="form-label">

                            Buscar pedido

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-search"></i>

                            </span>

                            <input
                                type="text"
                                class="form-control"
                                id="buscarPedido"
                                placeholder="N° pedido, producto...">

                        </div>

                    </div>
                    <!--=====================================
ESTADO
======================================-->

                    <div class="col-lg-2">

                        <label class="form-label">

                            Estado

                        </label>

                        <select
                            class="form-select"
                            id="estadoPedido">

                            <option value="">

                                Todos

                            </option>

                            <option value="PENDIENTE">

                                Pendiente

                            </option>

                            <option value="CONFIRMADO">

                                Confirmado

                            </option>

                            <option value="PREPARANDO">

                                Preparando

                            </option>

                            <option value="EN_CAMINO">

                                En camino

                            </option>

                            <option value="ENTREGADO">

                                Entregado

                            </option>

                            <option value="NO_ENTREGADO">

                                No entregado

                            </option>

                            <option value="CANCELADO">

                                Cancelado

                            </option>

                        </select>

                    </div>
                    <!--=====================================
                FECHA
                ======================================-->

                    <div class="col-lg-2">

                        <label class="form-label">

                            Fecha

                        </label>

                        <select
                            class="form-select"
                            id="fechaPedido">

                            <option value="">

                                Todos

                            </option>

                            <option value="hoy">

                                Hoy

                            </option>

                            <option value="7dias">

                                Últimos 7 días

                            </option>

                            <option value="mes">

                                Último mes

                            </option>

                            <option value="anio">

                                Último año

                            </option>

                        </select>

                    </div>


                    <!--=====================================
                MÉTODO DE PAGO
                ======================================-->

                    <div class="col-lg-2">

                        <label class="form-label">

                            Pago

                        </label>

                        <select
                            class="form-select"
                            id="metodoPago">

                            <option value="">

                                Todos

                            </option>

                            <?php

                            include "controladores/conexion.php";

                            $metodo_pago = mysqli_query(
                                $conexion,
                                "
                            SELECT
                                id_metodo_pago,
                                nombre
                            FROM metodo_pago
                            WHERE Eliminado = 0
                            ORDER BY nombre ASC
                            "
                            );

                            if ($metodo_pago) {

                                while ($mp = mysqli_fetch_assoc($metodo_pago)) {

                                    echo "
                                    <option value='" .
                                        (int)$mp["id_metodo_pago"] .
                                        "'>" .
                                        htmlspecialchars(
                                            $mp["nombre"],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ) .
                                        "</option>
                                ";
                                }
                            }

                            ?>

                        </select>

                    </div>


                    <!--=====================================
                ORDEN
                ======================================-->

                    <div class="col-lg-2">

                        <label class="form-label">

                            Ordenar

                        </label>

                        <select
                            class="form-select"
                            id="ordenPedido">

                            <option value="recientes">

                                Más recientes

                            </option>

                            <option value="antiguos">

                                Más antiguos

                            </option>

                            <option value="mayor">

                                Mayor importe

                            </option>

                            <option value="menor">

                                Menor importe

                            </option>

                        </select>

                    </div>


                    <!--=====================================
                LIMPIAR
                ======================================-->

                    <div class="col-lg-12 text-end">

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="btnLimpiarFiltros">

                            <i class="bi bi-arrow-counterclockwise"></i>

                            Limpiar filtros

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!--=====================================
LISTA DE PEDIDOS
======================================-->

    <section class="container mb-5">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">

                        <i class="bi bi-bag-check-fill text-primary"></i>

                        Mis pedidos

                    </h5>

                    <span
                        id="contadorPedidos"
                        class="badge bg-primary">

                        0 pedidos

                    </span>

                </div>

            </div>


            <!--=====================================
        CONTENEDOR AJAX
        ======================================-->

            <div id="contenedorPedidos">

                <div class="card-body">

                    <div class="text-center py-5">

                        <div class="spinner-border text-primary"></div>

                        <p class="text-muted mt-3 mb-0">

                            Cargando pedidos...

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>

    <?php include "modal/modal_confirmar_entrega_cliente.php"; ?>
    <?php include "includes/footer.php"; ?>

    <?php include "includes/carrito_offcanvas.php"; ?>


    <!--=====================================
SCRIPTS
======================================-->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="js/notificaciones.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="js/mis_pedidos.js"></script>

    <script src="js/carrito.js"></script>