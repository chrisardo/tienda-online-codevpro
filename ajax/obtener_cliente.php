<?php
//=====================================================
// CoDevPro Technology
// ajax/obtener_cliente.php
//=====================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

/*=============================================
VALIDAR SESIÓN
=============================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser = (int)$_SESSION["idUser"];

$idCliente = (int)($_POST["idCliente"] ?? 0);

/*=============================================
VALIDAR CLIENTE
=============================================*/

if ($idCliente <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Cliente inválido."
    ]);

    exit;
}

/*=============================================
OBTENER CLIENTE
=============================================*/

$sql = "
SELECT
    c.*,

    p.nombre AS pais,
    d.nombre AS departamento,
    pr.nombre AS provincia,
    di.nombre AS distrito,

    (
        SELECT COUNT(*)
        FROM ticket_ventas tv
        WHERE tv.idCliente = c.idCliente
    ) AS pedidos,

    (
        SELECT IFNULL(SUM(tv.total_venta), 0)
        FROM ticket_ventas tv
        WHERE tv.idCliente = c.idCliente
    ) AS total_compras,

    (
        SELECT COUNT(*)
        FROM favoritos f
        WHERE f.idCliente = c.idCliente
    ) AS favoritos,

    (
        SELECT COUNT(*)
        FROM testimonios t
        WHERE t.idCliente = c.idCliente
    ) AS testimonios

FROM clientes c

LEFT JOIN pais p
    ON p.id_pais = c.id_pais

LEFT JOIN departamento d
    ON d.id_departamento = c.id_departamento

LEFT JOIN provincia pr
    ON pr.id_provincia = c.id_provincia

LEFT JOIN distrito di
    ON di.id_distrito = c.id_distrito

WHERE c.idCliente = ?
AND c.id_user = ?
AND c.Eliminado = 0

LIMIT 1
";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Error al preparar la consulta."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idCliente,
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

/*=============================================
VALIDAR RESULTADO
=============================================*/

if (!$resultado || mysqli_num_rows($resultado) === 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Cliente no encontrado."
    ]);

    exit;
}

$cliente = mysqli_fetch_assoc($resultado);

/*=============================================
IMAGEN
=============================================*/

$imagen = "assets/img/sin_imagen.png";

if (!empty($cliente["imagen"])) {

    $imagen = "data:image/jpeg;base64," .
        base64_encode($cliente["imagen"]);
}

/*=============================================
ESTADO
=============================================*/

$estado = strtoupper(trim($cliente["estado"] ?? ""));

if ($estado === "ACTIVO") {

    $badgeEstado = '
        <span class="badge bg-success">
            ACTIVO
        </span>
    ';
} else {

    $badgeEstado = '
        <span class="badge bg-secondary">
            INACTIVO
        </span>
    ';
}

/*=============================================
DATOS SEGUROS
=============================================*/

$nombre = htmlspecialchars(
    $cliente["nombre"] ?? "",
    ENT_QUOTES,
    "UTF-8"
);

$dni = htmlspecialchars(
    $cliente["dni_o_ruc"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

$celular = htmlspecialchars(
    $cliente["celular"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

$email = htmlspecialchars(
    $cliente["email"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

$direccion = htmlspecialchars(
    $cliente["direccion"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

$pais = htmlspecialchars(
    $cliente["pais"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

$departamento = htmlspecialchars(
    $cliente["departamento"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

$provincia = htmlspecialchars(
    $cliente["provincia"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

$distrito = htmlspecialchars(
    $cliente["distrito"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

$fechaRegistro = htmlspecialchars(
    $cliente["fecha_registro"] ?? "-",
    ENT_QUOTES,
    "UTF-8"
);

/*=============================================
HTML
=============================================*/

$html = '

<div class="container-fluid">

    <div class="row g-0">

        <!--=====================================
        FOTO / RESUMEN
        =====================================-->

        <div class="col-lg-4">

            <div class="bg-light h-100 p-4 text-center">

                <img
                    src="' . $imagen . '"
                    class="rounded-circle border shadow"
                    width="180"
                    height="180"
                    style="object-fit:cover;"
                    alt="Imagen del cliente">

                <h4 class="fw-bold mt-3 mb-1">
                    ' . $nombre . '
                </h4>

                ' . $badgeEstado . '

                <hr>

                <div class="row g-2 text-center">

                    <!-- PEDIDOS -->

                    <div class="col-6">

                        <div class="card border-0 bg-primary-subtle">

                            <div class="card-body py-3">

                                <h5 class="mb-0 fw-bold">
                                    ' . (int)$cliente["pedidos"] . '
                                </h5>

                                <small>
                                    Pedidos
                                </small>

                            </div>

                        </div>

                    </div>

                    <!-- COMPRAS -->

                    <div class="col-6">

                        <div class="card border-0 bg-success-subtle">

                            <div class="card-body py-3">

                                <h5 class="mb-0 fw-bold">
                                    S/ ' . number_format(
    (float)$cliente["total_compras"],
    2
) . '
                                </h5>

                                <small>
                                    Compras
                                </small>

                            </div>

                        </div>

                    </div>

                    <!-- FAVORITOS -->

                    <div class="col-6">

                        <div class="card border-0 bg-warning-subtle">

                            <div class="card-body py-3">

                                <h5 class="mb-0 fw-bold">
                                    ' . (int)$cliente["favoritos"] . '
                                </h5>

                                <small>
                                    Favoritos
                                </small>

                            </div>

                        </div>

                    </div>

                    <!-- TESTIMONIOS -->

                    <div class="col-6">

                        <div class="card border-0 bg-danger-subtle">

                            <div class="card-body py-3">

                                <h5 class="mb-0 fw-bold">
                                    ' . (int)$cliente["testimonios"] . '
                                </h5>

                                <small>
                                    Opiniones
                                </small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!--=====================================
        DATOS DEL CLIENTE
        =====================================-->

        <div class="col-lg-8">

            <div class="p-4">

                <h5 class="fw-bold mb-4">

                    <i class="bi bi-person-lines-fill me-2"></i>

                    Información General

                </h5>

                <div class="row g-3">

                    <!-- DNI -->

                    <div class="col-md-6">

                        <strong>
                            <i class="bi bi-card-text me-1"></i>
                            DNI / RUC:
                        </strong>

                        <br>

                        ' . $dni . '

                    </div>

                    <!-- CELULAR -->

                    <div class="col-md-6">

                        <strong>
                            <i class="bi bi-phone me-1"></i>
                            Celular:
                        </strong>

                        <br>

                        ' . $celular . '

                    </div>

                    <!-- EMAIL -->

                    <div class="col-md-6">

                        <strong>
                            <i class="bi bi-envelope me-1"></i>
                            Email:
                        </strong>

                        <br>

                        ' . $email . '

                    </div>

                    <!-- FECHA -->

                    <div class="col-md-6">

                        <strong>
                            <i class="bi bi-calendar3 me-1"></i>
                            Fecha Registro:
                        </strong>

                        <br>

                        ' . $fechaRegistro . '

                    </div>

                    <!-- DIRECCIÓN -->

                    <div class="col-12">

                        <strong>
                            <i class="bi bi-geo-alt me-1"></i>
                            Dirección:
                        </strong>

                        <br>

                        ' . $direccion . '

                    </div>

                    <!-- PAÍS -->

                    <div class="col-md-3">

                        <strong>
                            País:
                        </strong>

                        <br>

                        ' . $pais . '

                    </div>

                    <!-- DEPARTAMENTO -->

                    <div class="col-md-3">

                        <strong>
                            Departamento:
                        </strong>

                        <br>

                        ' . $departamento . '

                    </div>

                    <!-- PROVINCIA -->

                    <div class="col-md-3">

                        <strong>
                            Provincia:
                        </strong>

                        <br>

                        ' . $provincia . '

                    </div>

                    <!-- DISTRITO -->

                    <div class="col-md-3">

                        <strong>
                            Distrito:
                        </strong>

                        <br>

                        ' . $distrito . '

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

';

/*=============================================
RESPUESTA JSON
=============================================*/

echo json_encode([
    "estado"   => true,
    "html"     => $html,

    /*
    |--------------------------------------------------------------------------
    | DATOS PARA CONTACTO
    |--------------------------------------------------------------------------
    */

    "idCliente" => $idCliente,

    "nombre"   => $cliente["nombre"] ?? "",

    "celular"  => $cliente["celular"] ?? "",

    "email"    => $cliente["email"] ?? ""
], JSON_UNESCAPED_UNICODE);

/*=============================================
CERRAR
=============================================*/

mysqli_stmt_close($stmt);

mysqli_close($conexion);
