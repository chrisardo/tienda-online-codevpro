<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/listar_empleados_sueldo.php
// Módulo: Sueldos y Pagos
// Sistema: Inventa
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

//=====================================================
// VALIDAR SESIÓN
//=====================================================

$idUser = isset($_SESSION['idUser'])
    ? (int) $_SESSION['idUser']
    : 0;

if ($idUser <= 0) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'mensaje' => 'Sesión no válida.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";

//=====================================================
// VALIDAR CONEXIÓN
//=====================================================

if (!isset($conexion) || !($conexion instanceof mysqli)) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'mensaje' => 'No se pudo establecer la conexión con la base de datos.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

//=====================================================
// CONFIGURACIÓN MYSQL
//=====================================================

$conexion->set_charset("utf8mb4");

//=====================================================
// CONSULTAR EMPLEADOS
//=====================================================

try {

    $sql = "
        SELECT
            e.id_empleado,
            e.nombre,
            e.apellido,
            e.dni,
            e.imagen,
            e.id_rol,
            COALESCE(r.nombre, 'Sin cargo') AS cargo

        FROM empleados e

        LEFT JOIN rol r
            ON r.id_rol = e.id_rol
            AND r.id_user = e.id_user

        WHERE
            e.id_user = ?
            AND e.estado = 'ACTIVO'

        ORDER BY
            e.nombre ASC,
            e.apellido ASC
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            "Error al preparar la consulta: " .
                $conexion->error
        );
    }

    $stmt->bind_param(
        "i",
        $idUser
    );

    if (!$stmt->execute()) {

        throw new Exception(
            "Error al ejecutar la consulta: " .
                $stmt->error
        );
    }

    $resultado = $stmt->get_result();

    $empleados = [];

    //=================================================
    // RECORRER EMPLEADOS
    //=================================================

    while ($fila = $resultado->fetch_assoc()) {

        $idEmpleado = (int) $fila['id_empleado'];

        $nombre = trim(
            (string) ($fila['nombre'] ?? '')
        );

        $apellido = trim(
            (string) ($fila['apellido'] ?? '')
        );

        $dni = trim(
            (string) ($fila['dni'] ?? '')
        );

        $cargo = trim(
            (string) ($fila['cargo'] ?? '')
        );

        if ($cargo === '') {
            $cargo = 'Sin cargo';
        }

        //=================================================
        // IMAGEN
        //=================================================

        $imagen = '';

        if (
            isset($fila['imagen']) &&
            $fila['imagen'] !== null &&
            $fila['imagen'] !== ''
        ) {

            $imagenBinaria = $fila['imagen'];

            $mimeType = 'image/jpeg';

            //=================================================
            // DETECTAR MIME
            //=================================================

            if (function_exists('finfo_open')) {

                $finfo = finfo_open(FILEINFO_MIME_TYPE);

                if ($finfo !== false) {

                    $mimeDetectado = finfo_buffer(
                        $finfo,
                        $imagenBinaria
                    );

                    if (
                        is_string($mimeDetectado) &&
                        strpos($mimeDetectado, 'image/') === 0
                    ) {

                        $mimeType = $mimeDetectado;
                    }

                    finfo_close($finfo);
                }
            }

            //=================================================
            // BASE64
            //=================================================

            $imagen = 'data:' .
                $mimeType .
                ';base64,' .
                base64_encode($imagenBinaria);
        }

        //=================================================
        // AGREGAR EMPLEADO
        //=================================================

        $empleados[] = [

            'id_empleado' => $idEmpleado,

            'nombre' => $nombre,

            'apellido' => $apellido,

            'dni' => $dni,

            'cargo' => $cargo,

            'imagen' => $imagen
        ];
    }

    $stmt->close();

    //=====================================================
    // RESPUESTA
    //=====================================================

    echo json_encode(
        [
            'success' => true,

            'empleados' => $empleados,

            'total' => count($empleados)
        ],
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode(
        [
            'success' => false,

            'mensaje' => $e->getMessage(),

            'archivo' => basename($e->getFile()),

            'linea' => $e->getLine()
        ],
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}
