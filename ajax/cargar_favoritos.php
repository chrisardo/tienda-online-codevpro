<?php
//=========================================================
// CoDevPro Technology
// ajax/cargar_favoritos.php
//=========================================================

session_start();

/*=============================================
CLIENTE NO LOGUEADO
=============================================*/

if (!isset($_SESSION["idCliente"])) {
?>

    <div class="col-12">

        <div class="alert alert-warning text-center p-5">

            <i class="bi bi-person-circle fs-1"></i>

            <h4 class="mt-3">

                Debes iniciar sesión

            </h4>

            <p class="mb-4">

                Inicia sesión para visualizar tus productos favoritos.

            </p>

            <a href="login.php" class="btn btn-primary">

                <i class="bi bi-box-arrow-in-right"></i>

                Iniciar sesión

            </a>

        </div>

    </div>

<?php

    exit();
}

/*=============================================
CARGAR LISTA
=============================================*/

require_once "../includes/lista_favoritos.php";
