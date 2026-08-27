<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false
    ]);

    exit;
}

$idUser      = intval($_SESSION["idUser"]);
$idCategoria = intval($_POST["idCategoria"] ?? 0);

$sql = "

SELECT *

FROM categorias

WHERE id_categorias = ?
AND id_user = ?
AND Eliminado = 0

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idCategoria,
    $idUser
);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);

if ($resultado->num_rows == 0) {

    echo json_encode([
        "estado" => false
    ]);

    exit;
}

$categoria = mysqli_fetch_assoc($resultado);

$imagen = "img/logo.png";

if (!empty($categoria["imagen"])) {

    $imagen =
        "data:image/jpeg;base64," .
        base64_encode($categoria["imagen"]);
}

ob_start();

?>

<input
    type="hidden"
    name="idCategoria"
    value="<?= $categoria["id_categorias"] ?>">

<div class="row g-4">

    <div class="col-md-4 text-center">

        <img
            id="previewEditarCategoria"
            src="<?= $imagen ?>"
            class="img-fluid rounded border shadow-sm mb-3"
            style="
                width:220px;
                height:220px;
                object-fit:cover;
            ">

        <input
            type="file"
            class="form-control"
            name="imagen"
            id="editarImagenCategoria"
            accept="image/*">

    </div>

    <div class="col-md-8">

        <label class="form-label fw-semibold">

            Nombre de Categoría

        </label>

        <input
            type="text"
            class="form-control"
            name="nombre"
            required
            maxlength="150"
            value="<?= htmlspecialchars($categoria["nombre"]) ?>">

        <div class="form-text">

            Modifica el nombre de la categoría.

        </div>

    </div>

</div>

<?php

$html = ob_get_clean();

echo json_encode([
    "estado" => true,
    "html"   => $html
]);
