<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/adm_deposito_gasto.ajax.php
// Módulo: Ingresos y Gastos
//=====================================================


//=====================================================
// INICIAR SESIÓN
//=====================================================

if (session_status() == PHP_SESSION_NONE) {

    session_start();
}


//=====================================================
// RESPUESTA JSON
//=====================================================

header(
    'Content-Type: application/json; charset=utf-8'
);


//=====================================================
// CONEXIÓN
//=====================================================

require_once "../controladores/conexion.php";


//=====================================================
// VALIDAR USUARIO
//=====================================================

$idUser = isset($_SESSION["idUser"])
    ? (int) $_SESSION["idUser"]
    : 0;


if ($idUser <= 0) {

    echo json_encode(
        [
            "status" => "error",
            "mensaje" => "Usuario no identificado."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


//=====================================================
// ACCIÓN
//=====================================================

$accion = $_POST["accion"] ?? "";


//=====================================================
// OBTENER KPI
//=====================================================

if ($accion === "obtenerKpi") {

    try {

        //=================================================
        // CONSULTA
        //=================================================

        $sql = "

            SELECT

                COALESCE(
                    SUM(
                        CASE
                            WHEN UPPER(TRIM(tipo)) = 'INGRESO'
                            THEN monto_pago
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_ingresos,

                COALESCE(
                    SUM(
                        CASE
                            WHEN UPPER(TRIM(tipo)) = 'GASTO'
                            THEN monto_pago
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_gastos,

                COUNT(*) AS total_movimientos

            FROM deposito_gasto

            WHERE id_user = ?

            AND (
                Eliminado = 0
                OR Eliminado IS NULL
            )

        ";


        //=================================================
        // PREPARAR
        //=================================================

        $stmt =
            $conexion->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "No se pudo preparar la consulta KPI."
            );
        }


        //=================================================
        // BIND
        //=================================================

        $stmt->bind_param(
            "i",
            $idUser
        );


        //=================================================
        // EJECUTAR
        //=================================================

        if (!$stmt->execute()) {

            throw new Exception(
                "No se pudo ejecutar la consulta KPI."
            );
        }


        //=================================================
        // RESULTADO
        //=================================================

        $resultado =
            $stmt->get_result();


        $datos =
            $resultado->fetch_assoc();


        //=================================================
        // VALORES
        //=================================================

        $totalIngresos =
            (float) (
                $datos["total_ingresos"] ?? 0
            );


        $totalGastos =
            (float) (
                $datos["total_gastos"] ?? 0
            );


        $totalMovimientos =
            (int) (
                $datos["total_movimientos"] ?? 0
            );


        //=================================================
        // BALANCE
        //=================================================

        $balance =
            $totalIngresos -
            $totalGastos;


        //=================================================
        // CERRAR
        //=================================================

        $stmt->close();


        //=================================================
        // RESPUESTA
        //=================================================

        echo json_encode(
            [

                "status" => "success",

                "datos" => [

                    "total_ingresos" =>
                    $totalIngresos,

                    "total_gastos" =>
                    $totalGastos,

                    "balance" =>
                    $balance,

                    "total_movimientos" =>
                    $totalMovimientos

                ]

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    } catch (Exception $e) {

        echo json_encode(
            [

                "status" => "error",

                "mensaje" =>
                "No se pudieron obtener los indicadores."

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}


//=====================================================
// OBTENER CUENTAS BANCARIAS
//=====================================================

if ($accion === "obtenerCuentasBancarias") {

    try {

        //=================================================
        // CONSULTA
        //=================================================

        $sql = "

            SELECT

                id_cuenta_bancaria,

                nombre,

                balance

            FROM cuenta_banco

            WHERE id_user = ?

            AND (
                Eliminado = 0
                OR Eliminado IS NULL
            )

            ORDER BY nombre ASC

        ";


        //=================================================
        // PREPARAR
        //=================================================

        $stmt =
            $conexion->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "No se pudo preparar la consulta de cuentas."
            );
        }


        //=================================================
        // BIND
        //=================================================

        $stmt->bind_param(
            "i",
            $idUser
        );


        //=================================================
        // EJECUTAR
        //=================================================

        if (!$stmt->execute()) {

            throw new Exception(
                "No se pudo ejecutar la consulta de cuentas."
            );
        }


        //=================================================
        // RESULTADO
        //=================================================

        $resultado =
            $stmt->get_result();


        $cuentas = [];


        //=================================================
        // RECORRER CUENTAS
        //=================================================

        while (
            $fila =
            $resultado->fetch_assoc()
        ) {

            $cuentas[] = [

                "id_cuenta_bancaria" =>
                (int) $fila["id_cuenta_bancaria"],

                "nombre" =>
                $fila["nombre"],

                "balance" =>
                (float) (
                    $fila["balance"] ?? 0
                )

            ];
        }


        //=================================================
        // CERRAR
        //=================================================

        $stmt->close();


        //=================================================
        // RESPUESTA
        //=================================================

        echo json_encode(
            [

                "status" => "success",

                "datos" => $cuentas

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    } catch (Exception $e) {

        echo json_encode(
            [

                "status" => "error",

                "mensaje" =>
                "No se pudieron obtener las cuentas bancarias."

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}


//=====================================================
// OBTENER CATEGORÍAS
//=====================================================

if ($accion === "obtenerCategorias") {

    try {

        //=================================================
        // CONSULTA
        //=================================================

        $sql = "

            SELECT

                id_categorias,

                nombre

            FROM categorias

            WHERE id_user = ?

            AND (
                Eliminado = 0
                OR Eliminado IS NULL
            )

            ORDER BY nombre ASC

        ";


        //=================================================
        // PREPARAR
        //=================================================

        $stmt =
            $conexion->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "No se pudo preparar la consulta de categorías."
            );
        }


        //=================================================
        // BIND
        //=================================================

        $stmt->bind_param(
            "i",
            $idUser
        );


        //=================================================
        // EJECUTAR
        //=================================================

        if (!$stmt->execute()) {

            throw new Exception(
                "No se pudo ejecutar la consulta de categorías."
            );
        }


        //=================================================
        // RESULTADO
        //=================================================

        $resultado =
            $stmt->get_result();


        $categorias = [];


        //=================================================
        // RECORRER CATEGORÍAS
        //=================================================

        while (
            $fila =
            $resultado->fetch_assoc()
        ) {

            $categorias[] = [

                "id_categorias" =>
                (int) $fila["id_categorias"],

                "nombre" =>
                $fila["nombre"]

            ];
        }


        //=================================================
        // CERRAR
        //=================================================

        $stmt->close();


        //=================================================
        // RESPUESTA
        //=================================================

        echo json_encode(
            [

                "status" => "success",

                "datos" => $categorias

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    } catch (Exception $e) {

        echo json_encode(
            [

                "status" => "error",

                "mensaje" =>
                "No se pudieron obtener las categorías."

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}


//=====================================================
// OBTENER PROVEEDORES
//=====================================================

if ($accion === "obtenerProveedores") {

    try {

        //=================================================
        // CONSULTA
        //=================================================

        $sql = "

            SELECT

                id_provedor,

                nombre

            FROM provedores

            WHERE id_user = ?

            AND (
                Eliminado = 0
                OR Eliminado IS NULL
            )

            ORDER BY nombre ASC

        ";


        //=================================================
        // PREPARAR
        //=================================================

        $stmt =
            $conexion->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "No se pudo preparar la consulta de proveedores."
            );
        }


        //=================================================
        // BIND
        //=================================================

        $stmt->bind_param(
            "i",
            $idUser
        );


        //=================================================
        // EJECUTAR
        //=================================================

        if (!$stmt->execute()) {

            throw new Exception(
                "No se pudo ejecutar la consulta de proveedores."
            );
        }


        //=================================================
        // RESULTADO
        //=================================================

        $resultado =
            $stmt->get_result();


        $proveedores = [];


        //=================================================
        // RECORRER PROVEEDORES
        //=================================================

        while (
            $fila =
            $resultado->fetch_assoc()
        ) {

            $proveedores[] = [

                "id_provedor" =>
                (int) $fila["id_provedor"],

                "nombre" =>
                $fila["nombre"]

            ];
        }


        //=================================================
        // CERRAR
        //=================================================

        $stmt->close();


        //=================================================
        // RESPUESTA
        //=================================================

        echo json_encode(
            [

                "status" => "success",

                "datos" => $proveedores

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    } catch (Exception $e) {

        echo json_encode(
            [

                "status" => "error",

                "mensaje" =>
                "No se pudieron obtener los proveedores."

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}
//=====================================================
// OBTENER PROVEEDORES
//=====================================================

if ($accion === "obtenerProveedores") {

    try {

        //=================================================
        // CONSULTA
        //=================================================

        $sql = "

            SELECT

                id_provedor,

                nombre

            FROM provedores

            WHERE id_user = ?

            AND (
                Eliminado = 0
                OR Eliminado IS NULL
            )

            ORDER BY nombre ASC

        ";


        //=================================================
        // PREPARAR
        //=================================================

        $stmt =
            $conexion->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "No se pudo preparar la consulta de proveedores."
            );
        }


        //=================================================
        // BIND
        //=================================================

        $stmt->bind_param(
            "i",
            $idUser
        );


        //=================================================
        // EJECUTAR
        //=================================================

        if (!$stmt->execute()) {

            throw new Exception(
                "No se pudo ejecutar la consulta de proveedores."
            );
        }


        //=================================================
        // RESULTADO
        //=================================================

        $resultado =
            $stmt->get_result();


        $proveedores = [];


        //=================================================
        // RECORRER PROVEEDORES
        //=================================================

        while (
            $fila =
            $resultado->fetch_assoc()
        ) {

            $proveedores[] = [

                "id_provedor" =>
                (int) $fila["id_provedor"],

                "nombre" =>
                $fila["nombre"]

            ];
        }


        //=================================================
        // CERRAR
        //=================================================

        $stmt->close();


        //=================================================
        // RESPUESTA
        //=================================================

        echo json_encode(
            [

                "status" => "success",

                "datos" => $proveedores

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    } catch (Exception $e) {

        echo json_encode(
            [

                "status" => "error",

                "mensaje" =>
                "No se pudieron obtener los proveedores."

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}
//=====================================================
// OBTENER MÉTODOS DE PAGO
//=====================================================

if ($accion === "obtenerMetodosPago") {

    try {

        //=================================================
        // CONSULTA
        //=================================================

        $sql = "

            SELECT

                id_metodo_pago,

                nombre

            FROM metodo_pago

            WHERE id_user = ?

            AND (
                Eliminado = 0
                OR Eliminado IS NULL
            )

            ORDER BY nombre ASC

        ";


        //=================================================
        // PREPARAR
        //=================================================

        $stmt =
            $conexion->prepare($sql);


        if (!$stmt) {

            throw new Exception(
                "No se pudo preparar la consulta de métodos de pago."
            );
        }


        //=================================================
        // BIND
        //=================================================

        $stmt->bind_param(
            "i",
            $idUser
        );


        //=================================================
        // EJECUTAR
        //=================================================

        if (!$stmt->execute()) {

            throw new Exception(
                "No se pudo ejecutar la consulta de métodos de pago."
            );
        }


        //=================================================
        // RESULTADO
        //=================================================

        $resultado =
            $stmt->get_result();


        $metodosPago = [];


        //=================================================
        // RECORRER MÉTODOS DE PAGO
        //=================================================

        while (
            $fila =
            $resultado->fetch_assoc()
        ) {

            $metodosPago[] = [

                "id_metodo_pago" =>
                (int) $fila["id_metodo_pago"],

                "nombre" =>
                $fila["nombre"]

            ];
        }


        //=================================================
        // CERRAR
        //=================================================

        $stmt->close();


        //=================================================
        // RESPUESTA
        //=================================================

        echo json_encode(
            [

                "status" => "success",

                "datos" => $metodosPago

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    } catch (Exception $e) {

        echo json_encode(
            [

                "status" => "error",

                "mensaje" =>
                "No se pudieron obtener los métodos de pago."

            ],
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}
//=====================================================
// ACCIÓN NO VÁLIDA
//=====================================================

echo json_encode(
    [

        "status" => "error",

        "mensaje" => "Acción no válida."

    ],
    JSON_UNESCAPED_UNICODE
);

exit;
