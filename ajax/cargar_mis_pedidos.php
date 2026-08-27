<?php
//=========================================================
// CoDevPro Technology
// ajax/cargar_mis_pedidos.php
//=========================================================

session_start();

/*=============================================
CLIENTE NO LOGUEADO
=============================================*/

if (!isset($_SESSION["idCliente"]) || $_SESSION["idCliente"] <= 0) {
?>

    <div class="col-12">

        <div class="alert alert-warning text-center p-5">

            <i class="bi bi-person-circle fs-1"></i>

            <h4 class="mt-3">

                Debes iniciar sesión

            </h4>

            <p class="mb-4">

                Inicia sesión para visualizar el historial de tus pedidos.

            </p>

            <a
                href="login.php"
                class="btn btn-primary">

                <i class="bi bi-box-arrow-in-right"></i>

                Iniciar sesión

            </a>

        </div>

    </div>

<?php

    exit();
}

/*=============================================
REENVIAR FILTROS AL CONTROLADOR
=============================================*/

$_GET["buscar"] = trim($_GET["buscar"] ?? "");

$_GET["estado"] = trim($_GET["estado"] ?? "");

$_GET["fecha"] = trim($_GET["fecha"] ?? "");

$_GET["metodo"] = trim($_GET["metodo"] ?? "");

$_GET["orden"] = trim($_GET["orden"] ?? "recientes");

/*=============================================
CARGAR LISTA DE PEDIDOS
=============================================*/

require_once "../includes/lista_pedidos.php";
