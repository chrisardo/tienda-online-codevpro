<?php
//=====================================================
// CoDevPro Technology
// ajax/editar_cliente.php
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

$idUser = (int) $_SESSION["idUser"];

try {

    /*=========================================
    DATOS
    =========================================*/

    $idCliente       = (int) ($_POST["idCliente"] ?? 0);
    $nombre          = trim($_POST["nombre"] ?? "");
    $dni_ruc         = trim($_POST["dni_ruc"] ?? "");
    $celular         = trim($_POST["celular"] ?? "");
    $email           = trim($_POST["email"] ?? "");
    $direccion       = trim($_POST["direccion"] ?? "");
    $estado          = trim($_POST["estado"] ?? "ACTIVO");

    $idPais          = (int) ($_POST["id_pais"] ?? 0);
    $idDepartamento  = (int) ($_POST["id_departamento"] ?? 0);
    $idProvincia     = (int) ($_POST["id_provincia"] ?? 0);
    $idDistrito      = (int) ($_POST["id_distrito"] ?? 0);
    $idRubro         = (int) ($_POST["id_rubro"] ?? 0);

    $password        = trim($_POST["password"] ?? "");

    if ($idCliente <= 0) {

        throw new Exception("Cliente no válido.");
    }

    if (empty($nombre)) {

        throw new Exception("Ingrese el nombre del cliente.");
    }

    if (empty($dni_ruc)) {

        throw new Exception("Ingrese DNI o RUC.");
    }

    if (empty($email)) {

        throw new Exception("Ingrese correo electrónico.");
    }

    /*=========================================
    VALIDAR CLIENTE EXISTE
    =========================================*/

    $sql = "SELECT idCliente
            FROM clientes
            WHERE idCliente = ?
            AND id_user = ?
            AND Eliminado = 0";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $idCliente,
        $idUser
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (!mysqli_num_rows($resultado)) {

        throw new Exception("Cliente no encontrado.");
    }

    mysqli_stmt_close($stmt);

    /*=========================================
    VALIDAR DNI/RUC DUPLICADO
    =========================================*/

    $sql = "SELECT idCliente
            FROM clientes
            WHERE dni_o_ruc = ?
            AND id_user = ?
            AND idCliente <> ?
            AND Eliminado = 0
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $dni_ruc,
        $idUser,
        $idCliente
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) > 0) {

        throw new Exception("El DNI/RUC ya pertenece a otro cliente.");
    }

    mysqli_stmt_close($stmt);

    /*=========================================
    VALIDAR EMAIL DUPLICADO
    =========================================*/

    $sql = "SELECT idCliente
            FROM clientes
            WHERE email = ?
            AND id_user = ?
            AND idCliente <> ?
            AND Eliminado = 0
            LIMIT 1";

    $stmt = mysqli_prepare($conexion, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sii",
        $email,
        $idUser,
        $idCliente
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($resultado) > 0) {

        throw new Exception("El correo ya está registrado.");
    }

    mysqli_stmt_close($stmt);

    /*=========================================
    IMAGEN
    =========================================*/

    $actualizarImagen = false;
    $imagen = null;

    if (
        isset($_FILES["imagenCliente"]) &&
        $_FILES["imagenCliente"]["error"] === UPLOAD_ERR_OK
    ) {

        $imagen = file_get_contents(
            $_FILES["imagenCliente"]["tmp_name"]
        );

        $actualizarImagen = true;
    }

    /*=========================================
    CONTRASEÑA
    =========================================*/

    $actualizarPassword = false;
    $passwordHash = "";

    if (!empty($password)) {

        if (strlen($password) < 6) {

            throw new Exception(
                "La contraseña debe tener mínimo 6 caracteres."
            );
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $actualizarPassword = true;
    }

    /*=========================================
    ARMAR SQL
    =========================================*/

    $campos = [];

    $campos[] = "nombre = ?";
    $campos[] = "dni_o_ruc = ?";
    $campos[] = "celular = ?";
    $campos[] = "email = ?";
    $campos[] = "direccion = ?";
    $campos[] = "estado = ?";
    $campos[] = "id_pais = ?";
    $campos[] = "id_departamento = ?";
    $campos[] = "id_provincia = ?";
    $campos[] = "id_distrito = ?";
    $campos[] = "id_rubro = ?";
    $campos[] = "fecha_actualizado = CURDATE()";

    if ($actualizarPassword) {

        $campos[] = "contrasena = ?";
    }

    if ($actualizarImagen) {

        $campos[] = "imagen = ?";
    }

    $sql = "UPDATE clientes SET "
        . implode(", ", $campos)
        . " WHERE idCliente = ?
             AND id_user = ?";

    $stmt = mysqli_prepare($conexion, $sql);

    $tipos = "ssssssiiiii";

    $params = [
        $nombre,
        $dni_ruc,
        $celular,
        $email,
        $direccion,
        $estado,
        $idPais,
        $idDepartamento,
        $idProvincia,
        $idDistrito,
        $idRubro
    ];

    if ($actualizarPassword) {

        $tipos .= "s";
        $params[] = $passwordHash;
    }

    if ($actualizarImagen) {

        $tipos .= "b";
        $params[] = $imagen;
    }

    $tipos .= "ii";

    $params[] = $idCliente;
    $params[] = $idUser;

    mysqli_stmt_bind_param(
        $stmt,
        $tipos,
        ...$params
    );

    if ($actualizarImagen) {

        $indiceImagen = count($params) - 3;

        mysqli_stmt_send_long_data(
            $stmt,
            $indiceImagen,
            $imagen
        );
    }

    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_error($conexion)
        );
    }

    mysqli_stmt_close($stmt);

    echo json_encode([

        "estado" => true,
        "mensaje" => "Cliente actualizado correctamente."

    ]);
} catch (Exception $e) {

    echo json_encode([

        "estado" => false,
        "mensaje" => $e->getMessage()

    ]);
}

mysqli_close($conexion);
