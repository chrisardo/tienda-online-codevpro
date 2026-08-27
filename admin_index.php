<?php
//=====================================================
// CoDevPro Technology
// admin_index.php
//=====================================================
//session_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";
/*=============================================
=            VALIDAR USUARIO LOGUEADO
=============================================*/

$idUser = $_SESSION["idUser"] ?? 0;


if (!$idUser) {

    echo '<div class="alert alert-danger">
            No se pudo identificar al usuario.
          </div>';

    return;
}
/*=============================================
=            OBTENER DATOS DE LA EMPRESA
=============================================*/

$sqlEmpresa = "SELECT *
                FROM usuario_acceso
                WHERE id_user = ?";

$stmt = mysqli_prepare($conexion, $sqlEmpresa);

mysqli_stmt_bind_param($stmt, "i", $idUser);

mysqli_stmt_execute($stmt);

$resultadoEmpresa = mysqli_stmt_get_result($stmt);

$empresa = mysqli_fetch_assoc($resultadoEmpresa);
/*=============================================
=            LOGO DE LA EMPRESA
=============================================*/

$logoEmpresa = "";


if (!empty($empresa["imagen"])) {

    $logoEmpresa = "data:image/jpeg;base64," . base64_encode($empresa["imagen"]);
} else {

    $logoEmpresa = "assets/logos/logo.png";
}

/*=============================================
=            SALUDO DINÁMICO
=============================================*/

date_default_timezone_set("America/Lima");

$hora = date("H");


if ($hora >= 5 && $hora < 12) {

    $saludo = "Buenos días";
} elseif ($hora >= 12 && $hora < 18) {

    $saludo = "Buenas tardes";
} else {

    $saludo = "Buenas noches";
}


/*=============================================
=            FECHA ACTUAL
=============================================*/

$fechaActual = date("d/m/Y");
$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");

$fechaFin = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");
include "includes/head.php";
?>

<div class="d-flex">

    <!--=====================================
    =            SIDEBAR
    ======================================-->

    <?php include "includes/admin_sidebar.php"; ?>


    <!--=====================================
    =            CONTENIDO PRINCIPAL
    ======================================-->

    <div class="flex-grow-1">

        <!-- NAVBAR -->

        <?php include "includes/admin_navbar.php"; ?>


        <!-- CONTENIDO DEL DASHBOARD -->

        <div class="container-fluid py-4 px-4">


            <!--=====================================
            =            BIENVENIDA
            ======================================-->

            <?php include "includes/dashboard/bienvenida.php";
            ?>

            <?php include "includes/dashboard/filtro_fecha.php"; ?>
            <!--=====================================
            =            TARJETAS ESTADÍSTICAS
            ======================================-->

            <?php include "includes/dashboard/tarjetas_estadisticas.php"; ?>


            <!--=====================================
            =            GRÁFICAS SUPERIORES
            ======================================-->

            <div class="row g-4 mb-4">

                <div class="col-lg-4">

                    <?php include "includes/dashboard/resumen_pedidos.php"; ?>

                </div>


                <div class="col-lg-5">

                    <?php include "includes/dashboard/ventas_7_dias.php"; ?>

                </div>


                <div class="col-lg-3">

                    <?php include "includes/dashboard/alertas_importantes.php"; ?>

                </div>

            </div>
            <!-- FILA INFERIOR -->

            <div class="row g-4 mt-1">

                <!-- ACTIVIDAD RECIENTE -->
                <div class="col-lg-6">
                    <?php include "includes/dashboard/actividad_reciente.php"; ?>
                </div>
                <!-- Grafico Cantidad de ventas realizadas por mes-->
                <div class="col-lg-6">
                    <?php include "includes/dashboard/cantidad_ventas.php"; ?>
                </div>
            </div>
            <!--=====================================
            =            GRÁFICOS DE MÓDULOS INFERIORES
            ======================================-->

            <div class="row g-4 mt-1">

                <!-- PRODUCTOS MÁS VENDIDOS -->
                <div class="col-lg-3 col-md-6">
                    <?php include "includes/dashboard/productos_mas_vendidos.php"; ?>
                </div>
                <!-- CATEGORÍAS MÁS COMPRADAS -->
                <div class="col-lg-3 col-md-6">
                    <?php include "includes/dashboard/categorias_mas_compradas.php"; ?>
                </div>
                <!-- MÉTODOS DE PAGO -->
                <div class="col-lg-3 col-md-6">
                    <?php include "includes/dashboard/metodos_pago.php"; ?>
                </div>

                <!-- CLIENTES QUE MÁS COMPRAN -->
                <div class="col-lg-3 col-md-6">
                    <?php include "includes/dashboard/clientes_mas_compran.php"; ?>
                </div>

            </div>
            <!--=====================================
            =            ACCIONES RÁPIDAS
            ======================================-->

            <?php include "includes/dashboard/acciones_rapidas.php"; ?>
        </div>


    </div>


</div>



<!--=====================================
=            SCRIPTS
======================================-->

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>


<!-- Chart JS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<!-- Chart JS Data Labels -->

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>


<!-- Flatpickr -->

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


<!-- Idioma Español -->

<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<script src="js/menu.js"></script>
<!-- Dashboard -->
<script src="js/filtro_dashboard.js"></script>
<script src="js/dashboard.js"></script>

</body>

</html>