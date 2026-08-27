<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/actividad_reciente.php
//=====================================================

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/conexion.php";


/*=====================================================
=            USUARIO LOGUEADO
=====================================================*/

$idUser = $_SESSION["idUser"] ?? 0;
$fechaInicio = $_SESSION["dashboard_fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_SESSION["dashboard_fecha_fin"] ?? date("Y-m-d");
$actividades = [];


/*=====================================================
=            NUEVOS PEDIDOS
=====================================================*/

$sql = "SELECT
            id_ticket_ventas,
            fecha_venta,
            hora_venta

        FROM ticket_ventas

        WHERE id_user = ?
        AND fecha_venta BETWEEN ? AND ?

        ORDER BY id_ticket_ventas DESC

        LIMIT 3";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


while ($fila = mysqli_fetch_assoc($resultado)) {

    $fecha = $fila["fecha_venta"] . " " . $fila["hora_venta"];

    $actividades[] = [

        "icono" => "bi-bag-check-fill",
        "color" => "primary",
        "titulo" => "Nuevo pedido recibido",
        "descripcion" => "Pedido #" . $fila["id_ticket_ventas"] . " registrado correctamente.",
        "fecha" => $fecha

    ];
}



/*=====================================================
=            CLIENTES NUEVOS
=====================================================*/

$sql = "SELECT
            nombre,
            fecha_registro

        FROM clientes

        WHERE id_user = ?
        AND fecha_registro BETWEEN ? AND ?

        ORDER BY idCliente DESC

        LIMIT 2";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


while ($fila = mysqli_fetch_assoc($resultado)) {

    $actividades[] = [

        "icono" => "bi-person-plus-fill",
        "color" => "success",
        "titulo" => "Nuevo cliente registrado",
        "descripcion" => $fila["nombre"] . " creó una cuenta.",
        "fecha" => $fila["fecha_registro"]

    ];
}



/*=====================================================
=            TESTIMONIOS
=====================================================*/

$sql = "SELECT
            fecha,
            calificacion

        FROM testimonios

        WHERE id_user = ?
        AND fecha BETWEEN ? AND ?

        ORDER BY id_testimonio DESC

        LIMIT 2";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


while ($fila = mysqli_fetch_assoc($resultado)) {

    $actividades[] = [

        "icono" => "bi-chat-heart-fill",
        "color" => "danger",
        "titulo" => "Nuevo testimonio",
        "descripcion" => "Un cliente dejó una valoración de " . $fila["calificacion"] . " estrellas.",
        "fecha" => $fila["fecha"]

    ];
}



/*=====================================================
=            VENTAS REALIZADAS
=====================================================*/

$sql = "SELECT
            total_venta,
            fecha_venta,
            hora_venta

        FROM ticket_ventas

        WHERE id_user = ?
        AND fecha_venta BETWEEN ? AND ?
        AND estado_envio = 'ENTREGADO'

        ORDER BY id_ticket_ventas DESC

        LIMIT 2";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


while ($fila = mysqli_fetch_assoc($resultado)) {

    $fecha = $fila["fecha_venta"] . " " . $fila["hora_venta"];

    $actividades[] = [

        "icono" => "bi-cart-check-fill",
        "color" => "warning",
        "titulo" => "Venta realizada",
        "descripcion" => "Se registró una venta por S/. " . number_format($fila["total_venta"], 2),
        "fecha" => $fecha

    ];
}



/*=====================================================
=            PAGOS CONFIRMADOS
=====================================================*/

$sql = "SELECT

            tv.fecha_venta,
            tv.hora_venta,
            mp.nombre

        FROM ticket_ventas tv

        INNER JOIN metodo_pago mp
        ON tv.id_metodo_pago = mp.id_metodo_pago

        WHERE tv.id_user = ?
        AND tv.fecha_venta BETWEEN ? AND ?
        AND tv.estado_envio = 'ENTREGADO'

        ORDER BY tv.id_ticket_ventas DESC

        LIMIT 2";


$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $idUser,
    $fechaInicio,
    $fechaFin
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);


while ($fila = mysqli_fetch_assoc($resultado)) {

    $fecha = $fila["fecha_venta"] . " " . $fila["hora_venta"];

    $actividades[] = [

        "icono" => "bi-cash-coin",
        "color" => "info",
        "titulo" => "Pago confirmado",
        "descripcion" => "Pago recibido mediante " . $fila["nombre"] . ".",
        "fecha" => $fecha

    ];
}


/*=====================================================
=            ORDENAR ACTIVIDADES
=====================================================*/

usort($actividades, function ($a, $b) {

    return strtotime($b["fecha"]) - strtotime($a["fecha"]);
});


$actividades = array_slice($actividades, 0, 5);



/*=====================================================
=            TIEMPO TRANSCURRIDO
=====================================================*/

function tiempoTranscurrido($fecha)
{

    $ahora = time();

    $fechaActividad = strtotime($fecha);

    $diferencia = $ahora - $fechaActividad;


    if ($diferencia < 60) {

        return "Hace unos segundos";
    }

    if ($diferencia < 3600) {

        return "Hace " . floor($diferencia / 60) . " minutos";
    }

    if ($diferencia < 86400) {

        return "Hace " . floor($diferencia / 3600) . " horas";
    }

    return "Hace " . floor($diferencia / 86400) . " días";
}

?>


<div class="card border-0 shadow-sm rounded-4 h-100">

    <div class="card-body">


        <!-- TITULO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                Actividad Reciente

            </h5>

            <span class="badge bg-primary">
                <?php if (
                    $fechaInicio == date("Y-m-01") &&
                    $fechaFin == date("Y-m-d")
                ): ?>

                    Este Mes

                <?php else: ?>

                    Filtrado

                <?php endif; ?>
            </span>

        </div>



        <?php if (!empty($actividades)): ?>


            <?php foreach ($actividades as $actividad): ?>


                <div class="timeline-item">


                    <!-- ICONO -->

                    <div class="timeline-icon bg-<?= $actividad["color"]; ?>-subtle text-<?= $actividad["color"]; ?>">

                        <i class="bi <?= $actividad["icono"]; ?>"></i>

                    </div>



                    <!-- TITULO -->

                    <div class="titulo-actividad fw-bold">

                        <?= $actividad["titulo"]; ?>

                    </div>


                    <!-- DESCRIPCIÓN -->

                    <div class="descripcion-actividad text-muted">

                        <?= htmlspecialchars($actividad["descripcion"]); ?>

                    </div>


                    <!-- HORA -->

                    <div class="hora-actividad mt-1 small text-secondary">

                        <?= tiempoTranscurrido($actividad["fecha"]); ?>

                    </div>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="text-center py-5">

                <i class="bi bi-clock-history display-5 text-muted"></i>

                <h6 class="mt-3">

                    No hay actividades recientes.

                </h6>

                <p class="text-muted mb-0">

                    Las actividades del sistema aparecerán aquí automáticamente.

                </p>

            </div>


        <?php endif; ?>


    </div>

</div>