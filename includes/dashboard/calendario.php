<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/calendario.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";


/*=============================================
=            VALIDAR USUARIO
=============================================*/

$idUser = $_SESSION["idUser"] ?? 0;

date_default_timezone_set("America/Lima");

$fechaHoy = date("Y-m-d");
$mesActual = date("Y-m");


/*=============================================
=            PEDIDOS PENDIENTES
=============================================*/

$sql = "SELECT COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio = 'PENDIENTE'";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$pedidosPendientes = mysqli_fetch_assoc($resultado)["total"] ?? 0;


/*=============================================
=            PEDIDOS PREPARANDO
=============================================*/

$sql = "SELECT COUNT(*) AS total
        FROM ticket_ventas
        WHERE id_user = ?
        AND estado_envio = 'PREPARANDO'";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$pedidosPreparando = mysqli_fetch_assoc($resultado)["total"] ?? 0;


/*=============================================
=            STOCK BAJO
=============================================*/

$sql = "SELECT COUNT(*) AS total
        FROM producto
        WHERE id_user = ?
        AND stock <= 5
        AND Eliminado = 0";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$stockBajo = mysqli_fetch_assoc($resultado)["total"] ?? 0;


/*=============================================
=            TESTIMONIOS PENDIENTES
=============================================*/

$sql = "SELECT COUNT(*) AS total
        FROM testimonios
        WHERE id_user = ?
        AND estado = 'PENDIENTE'";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$testimoniosPendientes = mysqli_fetch_assoc($resultado)["total"] ?? 0;


/*=============================================
=            CLIENTES DEL MES
=============================================*/

$sql = "SELECT COUNT(*) AS total
        FROM clientes
        WHERE id_user = ?
        AND DATE_FORMAT(fecha_registro,'%Y-%m') = ?";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "is", $idUser, $mesActual);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$clientesMes = mysqli_fetch_assoc($resultado)["total"] ?? 0;


/*=============================================
=            PRODUCTOS DEL MES
=============================================*/

$sql = "SELECT COUNT(*) AS total
        FROM producto
        WHERE id_user = ?
        AND DATE_FORMAT(fecha_registro,'%Y-%m') = ?
        AND Eliminado = 0";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "is", $idUser, $mesActual);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$productosMes = mysqli_fetch_assoc($resultado)["total"] ?? 0;

?>


<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">


        <!--=====================================
        TITULO
        =====================================-->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                Calendario

            </h5>

            <span class="badge bg-primary">

                <?= date("F"); ?>

            </span>

        </div>


        <!--=====================================
        CALENDARIO
        =====================================-->

        <input type="text"
            id="calendarioDashboard"
            class="form-control mb-4">


        <!--=====================================
        EVENTOS AUTOMÁTICOS
        =====================================-->



        <!-- Pedidos Pendientes -->

        <div class="evento-item">

            <div class="d-flex">

                <div class="evento-color bg-warning me-3"></div>

                <div>

                    <div class="fw-semibold">

                        Pedidos pendientes

                    </div>

                    <small class="text-muted">

                        <?= $pedidosPendientes; ?>
                        pedidos esperando confirmación.

                    </small>

                </div>

            </div>

        </div>



        <!-- Pedidos preparando -->

        <div class="evento-item">

            <div class="d-flex">

                <div class="evento-color bg-info me-3"></div>

                <div>

                    <div class="fw-semibold">

                        Pedidos preparando

                    </div>

                    <small class="text-muted">

                        <?= $pedidosPreparando; ?>
                        pedidos en preparación.

                    </small>

                </div>

            </div>

        </div>



        <!-- Stock Bajo -->

        <div class="evento-item">

            <div class="d-flex">

                <div class="evento-color bg-danger me-3"></div>

                <div>

                    <div class="fw-semibold">

                        Productos con stock bajo

                    </div>

                    <small class="text-muted">

                        <?= $stockBajo; ?>
                        productos necesitan reposición.

                    </small>

                </div>

            </div>

        </div>



        <!-- Testimonios -->

        <div class="evento-item">

            <div class="d-flex">

                <div class="evento-color bg-success me-3"></div>

                <div>

                    <div class="fw-semibold">

                        Testimonios pendientes

                    </div>

                    <small class="text-muted">

                        <?= $testimoniosPendientes; ?>
                        testimonios esperando aprobación.

                    </small>

                </div>

            </div>

        </div>



        <!-- Clientes -->

        <div class="evento-item">

            <div class="d-flex">

                <div class="evento-color bg-primary me-3"></div>

                <div>

                    <div class="fw-semibold">

                        Nuevos clientes

                    </div>

                    <small class="text-muted">

                        <?= $clientesMes; ?>
                        registrados este mes.

                    </small>

                </div>

            </div>

        </div>



        <!-- Productos -->

        <div class="evento-item mb-0">

            <div class="d-flex">

                <div class="evento-color bg-secondary me-3"></div>

                <div>

                    <div class="fw-semibold">

                        Productos registrados

                    </div>

                    <small class="text-muted">

                        <?= $productosMes; ?>
                        agregados este mes.

                    </small>

                </div>

            </div>

        </div>


    </div>

</div>