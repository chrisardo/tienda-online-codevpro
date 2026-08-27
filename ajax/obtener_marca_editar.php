<?php
//======================================================
// CoDevPro Technology
// ajax/obtener_marca_editar.php
//======================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

require_once "../controladores/conexion.php";

if (!isset($_SESSION["idUser"])) {

    echo json_encode([
        "estado" => false
    ]);

    exit;
}

$idUser = intval($_SESSION["idUser"]);
$idMarca = intval($_POST["idMarca"] ?? 0);

$sql = "

SELECT *

FROM marcas

WHERE id_marca = ?
AND id_user = ?
AND Eliminado = 0

";

$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $idMarca,
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

$marca = mysqli_fetch_assoc($resultado);

$imagen = "img/logo.png";

if (!empty($marca["imagen"])) {

    $imagen =
        "data:image/jpeg;base64," .
        base64_encode($marca["imagen"]);
}

ob_start();
?>

<form
    id="formEditarMarca"
    enctype="multipart/form-data"
    novalidate>

    <input
        type="hidden"
        name="idMarca"
        value="<?= $marca["id_marca"] ?>">

    <div class="row g-4">

        <div class="col-md-4">

            <div class="text-center">

                <img
                    src="<?= $imagen ?>"
                    id="previewEditarMarca"
                    class="img-fluid rounded border shadow-sm"
                    style="
                        width:220px;
                        height:220px;
                        object-fit:cover;
                    ">

                <div class="mt-3">

                    <input
                        type="file"
                        class="form-control"
                        id="editarImagenMarca"
                        name="imagen"
                        accept="image/*">

                </div>

            </div>

        </div>

        <div class="col-md-8">

            <div class="mb-3">

                <label class="form-label fw-semibold">

                    Nombre de la Marca

                </label>

                <input
                    type="text"
                    class="form-control"
                    name="nombre"
                    maxlength="100"
                    required
                    value="<?= htmlspecialchars($marca["nombre"]) ?>">

                <div class="invalid-feedback">

                    Ingrese el nombre de la marca.

                </div>

            </div>

            <div class="alert alert-info">

                <i class="bi bi-info-circle me-2"></i>

                Puede actualizar el nombre y la imagen
                de la marca.

            </div>

        </div>

    </div>

    <hr>

    <div class="text-end">

        <button
            type="button"
            class="btn btn-light"
            data-bs-dismiss="modal">

            Cancelar

        </button>

        <button
            type="submit"
            class="btn btn-warning">

            <i class="bi bi-save me-2"></i>

            Actualizar Marca

        </button>

    </div>

</form>

<?php

$html = ob_get_clean();

echo json_encode([

    "estado" => true,

    "html" => $html

]);
