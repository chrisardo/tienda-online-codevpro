<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

$idUser = $_SESSION["idUser"] ?? 0;

$idCliente = (int)($_POST["idCliente"] ?? 0);

if (!$idUser || !$idCliente) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Datos inválidos"
    ]);

    exit;
}

/*=============================================
CLIENTE
=============================================*/

$sql = "SELECT
            c.*,
            d.nombre AS departamento,
            p.nombre AS provincia,
            di.nombre AS distrito

        FROM clientes c

        LEFT JOIN departamento d
            ON d.id_departamento = c.id_departamento

        LEFT JOIN provincia p
            ON p.id_provincia = c.id_provincia

        LEFT JOIN distrito di
            ON di.id_distrito = c.id_distrito

        WHERE c.idCliente = ?
        AND c.id_user = ?
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idCliente,
    $idUser
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$cliente = mysqli_fetch_assoc($result);

if (!$cliente) {

    echo json_encode([
        "ok" => false,
        "mensaje" => "Cliente no encontrado"
    ]);

    exit;
}

/*=============================================
ESTADISTICAS
=============================================*/

$sql = "SELECT

            COUNT(*) pedidos,

            COALESCE(
                SUM(total_venta),
                0
            ) totalComprado,

            COALESCE(
                AVG(total_venta),
                0
            ) ticketPromedio,

            MAX(fecha_venta) ultimaCompra

        FROM ticket_ventas

        WHERE idCliente = ?
        AND id_user = ?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idCliente,
    $idUser
);

mysqli_stmt_execute($stmt);

$estadistica = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

/*=============================================
FAVORITOS
=============================================*/

$sql = "SELECT COUNT(*) total
        FROM favoritos
        WHERE idCliente=?";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $idCliente
);

mysqli_stmt_execute($stmt);

$favoritos = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
)["total"] ?? 0;

/*=============================================
HTML
=============================================*/

$html = '

<div class="row g-4">

    <div class="col-md-4">

        <div class="card">

            <div class="card-body text-center">

                <img
                    src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                    width="100"
                    class="rounded-circle mb-3">

                <h5>' . $cliente["nombre"] . '</h5>

                <span class="badge bg-' . (
    $cliente["estado"] == "ACTIVO"
    ? "success"
    : "danger"
) . '">
                    ' . $cliente["estado"] . '
                </span>

            </div>

        </div>

    </div>

    <div class="col-md-8">

        <div class="row g-3">

            <div class="col-md-6">

                <strong>DNI/RUC:</strong><br>
                ' . $cliente["dni_o_ruc"] . '

            </div>

            <div class="col-md-6">

                <strong>Email:</strong><br>
                ' . $cliente["email"] . '

            </div>

            <div class="col-md-6">

                <strong>Celular:</strong><br>
                ' . $cliente["celular"] . '

            </div>

            <div class="col-md-6">

                <strong>Fecha Registro:</strong><br>
                ' . $cliente["fecha_registro"] . '

            </div>

            <div class="col-md-12">

                <strong>Dirección:</strong><br>
                ' . $cliente["direccion"] . '

            </div>

            <div class="col-md-4">

                <strong>Departamento:</strong><br>
                ' . $cliente["departamento"] . '

            </div>

            <div class="col-md-4">

                <strong>Provincia:</strong><br>
                ' . $cliente["provincia"] . '

            </div>

            <div class="col-md-4">

                <strong>Distrito:</strong><br>
                ' . $cliente["distrito"] . '

            </div>

        </div>

    </div>

</div>

<hr>

<div class="row g-3">

    <div class="col-md-3">

        <div class="card text-center">

            <div class="card-body">

                <h3>' . $estadistica["pedidos"] . '</h3>

                <small>Pedidos</small>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card text-center">

            <div class="card-body">

                <h3>S/ ' . number_format($estadistica["totalComprado"], 2) . '</h3>

                <small>Total Comprado</small>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card text-center">

            <div class="card-body">

                <h3>S/ ' . number_format($estadistica["ticketPromedio"], 2) . '</h3>

                <small>Ticket Promedio</small>

            </div>

        </div>

    </div>

    <div class="col-md-3">

        <div class="card text-center">

            <div class="card-body">

                <h3>' . $favoritos . '</h3>

                <small>Favoritos</small>

            </div>

        </div>

    </div>

</div>';

echo json_encode([
    "ok" => true,
    "html" => $html
]);
