<?php
//=====================================================
// CoDevPro Technology
// ajax/obtener_cliente_editar.php
//=====================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Sesión no válida."
    ]);

    exit;
}

$idUser    = (int) $_SESSION["idUser"];
$idCliente = (int) ($_POST["idCliente"] ?? 0);

if ($idCliente <= 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Cliente inválido."
    ]);

    exit;
}

/*=============================================
CLIENTE
=============================================*/

$sql = "SELECT *
        FROM clientes
        WHERE idCliente = ?
        AND id_user = ?
        AND Eliminado = 0
        LIMIT 1";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idCliente,
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado || mysqli_num_rows($resultado) == 0) {

    echo json_encode([
        "estado" => false,
        "mensaje" => "Cliente no encontrado."
    ]);

    exit;
}

$cliente = mysqli_fetch_assoc($resultado);

/*=============================================
VALORES SEGUROS
=============================================*/

$idPais         = (int) ($cliente["id_pais"] ?? 0);
$idDepartamento = (int) ($cliente["id_departamento"] ?? 0);
$idProvincia    = (int) ($cliente["id_provincia"] ?? 0);
$idDistrito     = (int) ($cliente["id_distrito"] ?? 0);

/*=============================================
IMAGEN
=============================================*/

$imagen = "assets/img/sin_imagen.png";

if (!empty($cliente["imagen"])) {

    $imagen = "data:image/jpeg;base64," .
        base64_encode($cliente["imagen"]);
}

/*=============================================
RUBROS
=============================================*/

$rubros = '<option value="">Seleccionar</option>';

$sqlRubros = "SELECT *
              FROM rubros
              WHERE Eliminado = 0
              AND id_user = ?
              ORDER BY nombre ASC";

$stmtRubros = mysqli_prepare(
    $conexion,
    $sqlRubros
);

mysqli_stmt_bind_param(
    $stmtRubros,
    "i",
    $idUser
);

mysqli_stmt_execute($stmtRubros);

$resRubros = mysqli_stmt_get_result($stmtRubros);

while ($rubro = mysqli_fetch_assoc($resRubros)) {

    $selected =
        ($cliente["id_rubro"] == $rubro["id_rubro"])
        ? "selected"
        : "";

    $rubros .= '
    <option
        value="' . $rubro["id_rubro"] . '"
        ' . $selected . '>

        ' . htmlspecialchars($rubro["nombre"]) . '

    </option>';
}

/*=============================================
PAISES
=============================================*/

$paises = '<option value="">Seleccionar</option>';

$sqlPais = "SELECT *
            FROM pais
            WHERE Eliminado = 0
            ORDER BY nombre ASC";

$resPais = mysqli_query($conexion, $sqlPais);

if ($resPais) {

    while ($pais = mysqli_fetch_assoc($resPais)) {

        $selected =
            ($idPais == $pais["id_pais"])
            ? "selected"
            : "";

        $paises .= '
        <option
            value="' . $pais["id_pais"] . '"
            ' . $selected . '>

            ' . htmlspecialchars($pais["nombre"]) . '

        </option>';
    }
}

/*=============================================
DEPARTAMENTOS
=============================================*/

$departamentos = '<option value="">Seleccionar</option>';

if ($idPais > 0) {

    $sqlDepartamento = "SELECT *
                        FROM departamento
                        WHERE id_pais = ?
                        ORDER BY nombre ASC";

    $stmtDepartamento = mysqli_prepare(
        $conexion,
        $sqlDepartamento
    );

    mysqli_stmt_bind_param(
        $stmtDepartamento,
        "i",
        $idPais
    );

    mysqli_stmt_execute($stmtDepartamento);

    $resDepartamento = mysqli_stmt_get_result(
        $stmtDepartamento
    );

    while ($dep = mysqli_fetch_assoc($resDepartamento)) {

        $selected =
            ($idDepartamento == $dep["id_departamento"])
            ? "selected"
            : "";

        $departamentos .= '
        <option
            value="' . $dep["id_departamento"] . '"
            ' . $selected . '>

            ' . htmlspecialchars($dep["nombre"]) . '

        </option>';
    }
}

/*=============================================
PROVINCIAS
=============================================*/

$provincias = '<option value="">Seleccionar</option>';

if ($idDepartamento > 0) {

    $sqlProvincia = "SELECT *
                     FROM provincia
                     WHERE id_departamento = ?
                     ORDER BY nombre ASC";

    $stmtProvincia = mysqli_prepare(
        $conexion,
        $sqlProvincia
    );

    mysqli_stmt_bind_param(
        $stmtProvincia,
        "i",
        $idDepartamento
    );

    mysqli_stmt_execute($stmtProvincia);

    $resProvincia = mysqli_stmt_get_result(
        $stmtProvincia
    );

    while ($prov = mysqli_fetch_assoc($resProvincia)) {

        $selected =
            ($idProvincia == $prov["id_provincia"])
            ? "selected"
            : "";

        $provincias .= '
        <option
            value="' . $prov["id_provincia"] . '"
            ' . $selected . '>

            ' . htmlspecialchars($prov["nombre"]) . '

        </option>';
    }
}

/*=============================================
DISTRITOS
=============================================*/

$distritos = '<option value="">Seleccionar</option>';

if ($idProvincia > 0) {

    $sqlDistrito = "SELECT *
                    FROM distrito
                    WHERE id_provincia = ?
                    ORDER BY nombre ASC";

    $stmtDistrito = mysqli_prepare(
        $conexion,
        $sqlDistrito
    );

    mysqli_stmt_bind_param(
        $stmtDistrito,
        "i",
        $idProvincia
    );

    mysqli_stmt_execute($stmtDistrito);

    $resDistrito = mysqli_stmt_get_result(
        $stmtDistrito
    );

    while ($dist = mysqli_fetch_assoc($resDistrito)) {

        $selected =
            ($idDistrito == $dist["id_distrito"])
            ? "selected"
            : "";

        $distritos .= '
        <option
            value="' . $dist["id_distrito"] . '"
            ' . $selected . '>

            ' . htmlspecialchars($dist["nombre"]) . '

        </option>';
    }
}

/*=============================================
HTML
=============================================*/

$html = '

<div class="row">

    <div class="col-lg-3 text-center">

        <img
            src="' . $imagen . '"
            id="previewEditarCliente"
            class="img-fluid rounded-circle border mb-3"
            style="width:180px;height:180px;object-fit:cover;">

        <input
            type="file"
            class="form-control"
            id="editarImagenCliente"
            name="imagenCliente"
            accept="image/*">

    </div>

    <div class="col-lg-9">

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Nombre</label>

                <input
                    type="text"
                    class="form-control"
                    name="nombre"
                    value="' . htmlspecialchars($cliente["nombre"]) . '"
                    required>
            </div>

            <div class="col-md-3">
                <label class="form-label">DNI / RUC</label>

                <input
                    type="text"
                    class="form-control"
                    name="dni_ruc"
                    value="' . htmlspecialchars($cliente["dni_o_ruc"]) . '"
                    required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Celular</label>

                <input
                    type="text"
                    class="form-control"
                    name="celular"
                    value="' . htmlspecialchars($cliente["celular"]) . '">
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>

                <input
                    type="email"
                    class="form-control"
                    name="email"
                    value="' . htmlspecialchars($cliente["email"]) . '">
            </div>

            <div class="col-md-6">
                <label class="form-label">Rubro</label>

                <select
                    class="form-select"
                    name="id_rubro">

                    ' . $rubros . '

                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Dirección</label>

                <input
                    type="text"
                    class="form-control"
                    name="direccion"
                    value="' . htmlspecialchars($cliente["direccion"]) . '">
            </div>

            <div class="col-md-3">
                <label class="form-label">País</label>

                <select
                    class="form-select"
                    id="editarPaisCliente"
                    name="id_pais">

                    ' . $paises . '

                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Departamento</label>

                <select
                    class="form-select"
                    id="editarDepartamentoCliente"
                    name="id_departamento">

                    ' . $departamentos . '

                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Provincia</label>

                <select
                    class="form-select"
                    id="editarProvinciaCliente"
                    name="id_provincia">

                    ' . $provincias . '

                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Distrito</label>

                <select
                    class="form-select"
                    id="editarDistritoCliente"
                    name="id_distrito">

                    ' . $distritos . '

                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Estado</label>

                <select
                    class="form-select"
                    name="estado">

                    <option value="ACTIVO" ' . ($cliente["estado"] == "ACTIVO" ? "selected" : "") . '>ACTIVO</option>

                    <option value="INACTIVO" ' . ($cliente["estado"] == "INACTIVO" ? "selected" : "") . '>INACTIVO</option>

                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nueva Contraseña</label>

                <input
                    type="password"
                    class="form-control"
                    name="password">

                <small class="text-muted">
                    Dejar vacío para conservar la actual.
                </small>
            </div>

        </div>

    </div>

</div>';

echo json_encode([
    "estado" => true,
    "html" => $html
]);

mysqli_close($conexion);
