<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/ubicaciones_proveedor.php
// Módulo: Proveedores
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// RESPUESTA JSON
//=====================================================

header("Content-Type: application/json; charset=utf-8");

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;

if ($idUser <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Sesión no válida."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// ACCIÓN
//=====================================================

$accion = isset($_GET["accion"])
    ? trim($_GET["accion"])
    : "";

//=====================================================
// CARGAR PAÍSES
//=====================================================

if ($accion === "paises") {

    try {

        $sql = "
            SELECT
                id_pais,
                nombre
            FROM pais
            WHERE id_user = ?
              AND Eliminado = 0
            ORDER BY nombre ASC
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "i",
            $idUser
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        $paises = [];

        while ($fila = $resultado->fetch_assoc()) {

            $paises[] = [
                "id_pais" => (int) $fila["id_pais"],
                "nombre" => $fila["nombre"]
            ];
        }

        $stmt->close();

        echo json_encode([
            "success" => true,
            "paises" => $paises
        ], JSON_UNESCAPED_UNICODE);

        exit;
    } catch (Throwable $e) {

        echo json_encode([
            "success" => false,
            "message" => "Error al cargar los países."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

//=====================================================
// CARGAR DEPARTAMENTOS
//=====================================================

if ($accion === "departamentos") {

    try {

        /*
         * IMPORTANTE:
         *
         * Si id_pais viene vacío:
         *
         *     Se cargan TODOS los departamentos
         *     del usuario.
         *
         * Si id_pais viene informado:
         *
         *     Se cargan solamente los departamentos
         *     pertenecientes a ese país.
         */

        $idPais = isset($_GET["id_pais"])
            ? (int) $_GET["id_pais"]
            : 0;

        //=================================================
        // TODOS LOS DEPARTAMENTOS
        //=================================================

        if ($idPais <= 0) {

            $sql = "
                SELECT
                    id_departamento,
                    nombre
                FROM departamento
                WHERE id_user = ?
                  AND Eliminado = 0
                ORDER BY nombre ASC
            ";

            $stmt = $conexion->prepare($sql);

            $stmt->bind_param(
                "i",
                $idUser
            );
        } else {

            //=================================================
            // DEPARTAMENTOS POR PAÍS
            //=================================================

            $sql = "
                SELECT
                    id_departamento,
                    nombre
                FROM departamento
                WHERE id_user = ?
                  AND id_pais = ?
                  AND Eliminado = 0
                ORDER BY nombre ASC
            ";

            $stmt = $conexion->prepare($sql);

            $stmt->bind_param(
                "ii",
                $idUser,
                $idPais
            );
        }

        $stmt->execute();

        $resultado = $stmt->get_result();

        $departamentos = [];

        while ($fila = $resultado->fetch_assoc()) {

            $departamentos[] = [
                "id_departamento" => (int) $fila["id_departamento"],
                "nombre" => $fila["nombre"]
            ];
        }

        $stmt->close();

        echo json_encode([
            "success" => true,
            "departamentos" => $departamentos
        ], JSON_UNESCAPED_UNICODE);

        exit;
    } catch (Throwable $e) {

        echo json_encode([
            "success" => false,
            "message" => "Error al cargar los departamentos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

//=====================================================
// CARGAR PROVINCIAS
//=====================================================

if ($accion === "provincias") {

    $idDepartamento = isset($_GET["id_departamento"])
        ? (int) $_GET["id_departamento"]
        : 0;

    if ($idDepartamento <= 0) {

        echo json_encode([
            "success" => false,
            "message" => "Departamento no válido."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    try {

        $sql = "
            SELECT
                id_provincia,
                nombre
            FROM provincia
            WHERE id_user = ?
              AND id_departamento = ?
              AND Eliminado = 0
            ORDER BY nombre ASC
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ii",
            $idUser,
            $idDepartamento
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        $provincias = [];

        while ($fila = $resultado->fetch_assoc()) {

            $provincias[] = [
                "id_provincia" => (int) $fila["id_provincia"],
                "nombre" => $fila["nombre"]
            ];
        }

        $stmt->close();

        echo json_encode([
            "success" => true,
            "provincias" => $provincias
        ], JSON_UNESCAPED_UNICODE);

        exit;
    } catch (Throwable $e) {

        echo json_encode([
            "success" => false,
            "message" => "Error al cargar las provincias."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

//=====================================================
// CARGAR DISTRITOS
//=====================================================

if ($accion === "distritos") {

    $idProvincia = isset($_GET["id_provincia"])
        ? (int) $_GET["id_provincia"]
        : 0;

    if ($idProvincia <= 0) {

        echo json_encode([
            "success" => false,
            "message" => "Provincia no válida."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    try {

        $sql = "
            SELECT
                id_distrito,
                nombre
            FROM distrito
            WHERE id_user = ?
              AND id_provincia = ?
              AND Eliminado = 0
            ORDER BY nombre ASC
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ii",
            $idUser,
            $idProvincia
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        $distritos = [];

        while ($fila = $resultado->fetch_assoc()) {

            $distritos[] = [
                "id_distrito" => (int) $fila["id_distrito"],
                "nombre" => $fila["nombre"]
            ];
        }

        $stmt->close();

        echo json_encode([
            "success" => true,
            "distritos" => $distritos
        ], JSON_UNESCAPED_UNICODE);

        exit;
    } catch (Throwable $e) {

        echo json_encode([
            "success" => false,
            "message" => "Error al cargar los distritos."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

//=====================================================
// ACCIÓN NO VÁLIDA
//=====================================================

echo json_encode([
    "success" => false,
    "message" => "Acción no válida."
], JSON_UNESCAPED_UNICODE);
