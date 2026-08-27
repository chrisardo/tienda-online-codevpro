<?php
//=====================================================
// CoDevPro Technology
// Archivo: ajax/obtener_detalles_empleado.php
// Módulo: Detalles del Empleado
// Sistema: Inventa
//=====================================================

//-----------------------------------------------------
// ESTE ARCHIVO SOLO DEVUELVE JSON
//-----------------------------------------------------

ob_start();

header('Content-Type: application/json; charset=utf-8');

//-----------------------------------------------------
// Configuración de errores
//-----------------------------------------------------

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

error_reporting(E_ALL);

//=====================================================
// FUNCIÓN RESPUESTA JSON
//=====================================================

function responderJSON(
    $success,
    $message = '',
    $data = []
) {

    // Limpiar cualquier salida accidental
    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $data
        ),
        JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
    );

    exit;
}

//=====================================================
// REGISTRAR ERROR
//=====================================================

function registrarErrorServidor($mensaje)
{
    error_log(
        '[DETALLES EMPLEADO] ' . $mensaje
    );
}

//=====================================================
// MANEJO DE ERRORES FATALES
//=====================================================

register_shutdown_function(function () {

    $error = error_get_last();

    if ($error === null) {
        return;
    }

    $tiposFatales = [
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_COMPILE_ERROR
    ];

    if (
        in_array(
            $error['type'],
            $tiposFatales,
            true
        )
    ) {

        registrarErrorServidor(
            $error['message'] .
                ' en ' .
                $error['file'] .
                ':' .
                $error['line']
        );

        if (ob_get_length()) {
            ob_clean();
        }

        echo json_encode(
            [
                'success' => false,
                'message' =>
                'Ocurrió un error interno del servidor.'
            ],
            JSON_UNESCAPED_UNICODE
        );
    }
});

//=====================================================
// SESIÓN
//=====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=====================================================
// VERIFICAR SESIÓN
//=====================================================

if (!isset($_SESSION['idUser'])) {

    responderJSON(
        false,
        'La sesión ha expirado.'
    );
}

$idUser = (int) $_SESSION['idUser'];

//=====================================================
// OBTENER ID DEL EMPLEADO
//=====================================================

$idEmpleado = isset($_POST['id_empleado'])
    ? (int) $_POST['id_empleado']
    : 0;

if ($idEmpleado <= 0) {

    responderJSON(
        false,
        'ID de empleado no válido.'
    );
}

//=====================================================
// CONEXIÓN
//=====================================================

$rutaConexion =
    dirname(__DIR__) .
    '/controladores/conexion.php';

if (!file_exists($rutaConexion)) {

    registrarErrorServidor(
        'No existe conexion.php en: ' .
            $rutaConexion
    );

    responderJSON(
        false,
        'No se pudo establecer la conexión con la base de datos.'
    );
}

require_once $rutaConexion;

//=====================================================
// VERIFICAR CONEXIÓN
//=====================================================

if (
    !isset($conexion) ||
    !$conexion
) {

    registrarErrorServidor(
        'La variable $conexion no está disponible.'
    );

    responderJSON(
        false,
        'No se pudo establecer la conexión con la base de datos.'
    );
}

//=====================================================
// CHARSET
//=====================================================

mysqli_set_charset(
    $conexion,
    'utf8mb4'
);

//=====================================================
// 1. OBTENER INFORMACIÓN DEL EMPLEADO
//=====================================================

$sqlEmpleado = "

    SELECT

        e.id_empleado,
        e.nombre,
        e.apellido,
        e.imagen,
        e.dni,
        e.celular,
        e.direccion,

        e.id_departamento,
        e.id_provincia,
        e.id_distrito,
        e.id_pais,

        e.email,
        e.estado,

        e.contrasena,

        e.id_user,
        e.fecha_registro,
        e.id_rol,
        e.fecha_actualizado,

        r.nombre AS nombre_rol,

        p.nombre AS nombre_pais,

        d.nombre AS nombre_departamento,

        pr.nombre AS nombre_provincia,

        di.nombre AS nombre_distrito

    FROM empleados e

    LEFT JOIN rol r
        ON r.id_rol = e.id_rol
        AND r.id_user = e.id_user

    LEFT JOIN pais p
        ON p.id_pais = e.id_pais
        AND p.id_user = e.id_user

    LEFT JOIN departamento d
        ON d.id_departamento = e.id_departamento
        AND d.id_user = e.id_user

    LEFT JOIN provincia pr
        ON pr.id_provincia = e.id_provincia
        AND pr.id_user = e.id_user

    LEFT JOIN distrito di
        ON di.id_distrito = e.id_distrito
        AND di.id_user = e.id_user

    WHERE e.id_empleado = ?
      AND e.id_user = ?

    LIMIT 1
";

//-----------------------------------------------------
// Preparar
//-----------------------------------------------------

$stmtEmpleado = mysqli_prepare(
    $conexion,
    $sqlEmpleado
);

if (!$stmtEmpleado) {

    registrarErrorServidor(
        'Error preparando empleado: ' .
            mysqli_error($conexion)
    );

    responderJSON(
        false,
        'No se pudo consultar la información del empleado.'
    );
}

//-----------------------------------------------------
// Parámetros
//-----------------------------------------------------

mysqli_stmt_bind_param(
    $stmtEmpleado,
    'ii',
    $idEmpleado,
    $idUser
);

//-----------------------------------------------------
// Ejecutar
//-----------------------------------------------------

if (!mysqli_stmt_execute($stmtEmpleado)) {

    registrarErrorServidor(
        'Error ejecutando empleado: ' .
            mysqli_stmt_error($stmtEmpleado)
    );

    mysqli_stmt_close(
        $stmtEmpleado
    );

    responderJSON(
        false,
        'No se pudo consultar la información del empleado.'
    );
}

//-----------------------------------------------------
// Store result
//-----------------------------------------------------

mysqli_stmt_store_result(
    $stmtEmpleado
);

//-----------------------------------------------------
// Verificar existencia
//-----------------------------------------------------

if (
    mysqli_stmt_num_rows(
        $stmtEmpleado
    ) === 0
) {

    mysqli_stmt_close(
        $stmtEmpleado
    );

    responderJSON(
        false,
        'El empleado no existe o no pertenece a esta empresa.'
    );
}

//=====================================================
// VARIABLES DE RESULTADO
//=====================================================

$dbIdEmpleado = null;
$dbNombre = null;
$dbApellido = null;
$dbImagen = null;
$dbDni = null;
$dbCelular = null;
$dbDireccion = null;

$dbIdDepartamento = null;
$dbIdProvincia = null;
$dbIdDistrito = null;
$dbIdPais = null;

$dbEmail = null;
$dbEstado = null;

$dbContrasena = null;

$dbIdUser = null;
$dbFechaRegistro = null;
$dbIdRol = null;
$dbFechaActualizado = null;

$dbNombreRol = null;

$dbNombrePais = null;
$dbNombreDepartamento = null;
$dbNombreProvincia = null;
$dbNombreDistrito = null;

//=====================================================
// VINCULAR RESULTADOS
//=====================================================

mysqli_stmt_bind_result(
    $stmtEmpleado,

    $dbIdEmpleado,
    $dbNombre,
    $dbApellido,
    $dbImagen,
    $dbDni,
    $dbCelular,
    $dbDireccion,

    $dbIdDepartamento,
    $dbIdProvincia,
    $dbIdDistrito,
    $dbIdPais,

    $dbEmail,
    $dbEstado,

    $dbContrasena,

    $dbIdUser,
    $dbFechaRegistro,
    $dbIdRol,
    $dbFechaActualizado,

    $dbNombreRol,

    $dbNombrePais,
    $dbNombreDepartamento,
    $dbNombreProvincia,
    $dbNombreDistrito
);

//-----------------------------------------------------
// Obtener fila
//-----------------------------------------------------

mysqli_stmt_fetch(
    $stmtEmpleado
);

mysqli_stmt_close(
    $stmtEmpleado
);

//=====================================================
// 2. IMAGEN DEL EMPLEADO
//=====================================================

$imagenBase64 = null;

if (!empty($dbImagen)) {

    $mime = 'image/jpeg';

    //-------------------------------------------------
    // Detectar MIME real
    //-------------------------------------------------

    if (function_exists('finfo_open')) {

        $finfo = finfo_open(
            FILEINFO_MIME_TYPE
        );

        if ($finfo) {

            $mimeDetectado =
                finfo_buffer(
                    $finfo,
                    $dbImagen
                );

            if (!empty($mimeDetectado)) {
                $mime = $mimeDetectado;
            }

            finfo_close($finfo);
        }
    }

    //-------------------------------------------------
    // Validar tipos de imagen
    //-------------------------------------------------

    $mimesPermitidos = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif'
    ];

    if (
        !in_array(
            $mime,
            $mimesPermitidos,
            true
        )
    ) {

        $mime = 'image/jpeg';
    }

    //-------------------------------------------------
    // Base64
    //-------------------------------------------------

    $imagenBase64 =
        'data:' .
        $mime .
        ';base64,' .
        base64_encode($dbImagen);
}

//=====================================================
// 3. NOMBRE COMPLETO
//=====================================================

$nombreCompleto = trim(
    ($dbNombre ?? '') .
        ' ' .
        ($dbApellido ?? '')
);

if ($nombreCompleto === '') {
    $nombreCompleto = 'Empleado sin nombre';
}

//=====================================================
// 4. DETERMINAR TIPO DE PERFIL
//=====================================================
//
// IMPORTANTE:
//
// No todos los empleados son vendedores.
//
// La tabla rol solamente tiene:
//
// id_rol
// nombre
// id_user
//
// Por ello se determina el perfil utilizando
// el nombre del rol.
//
// Esto NO modifica la base de datos.
//

$nombreRolNormalizado =
    mb_strtolower(
        trim(
            (string) $dbNombreRol
        ),
        'UTF-8'
    );

//-----------------------------------------------------
// Quitar tildes para facilitar comparación
//-----------------------------------------------------

$nombreRolNormalizado = strtr(
    $nombreRolNormalizado,
    [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u'
    ]
);

//-----------------------------------------------------
// Palabras comerciales
//-----------------------------------------------------

$rolesComerciales = [
    'vendedor',
    'ventas',
    'cajero',
    'cajera',
    'comercial',
    'asesor comercial',
    'asesora comercial',
    'atencion al cliente',
    'atencion cliente',
    'ejecutivo de ventas',
    'ejecutiva de ventas'
];

//-----------------------------------------------------
// Palabras administrativas
//-----------------------------------------------------

$rolesAdministrativos = [
    'administrador',
    'administradora',
    'gerente',
    'gerencia',
    'supervisor',
    'supervisora',
    'jefe',
    'jefa',
    'recursos humanos',
    'contabilidad',
    'contador',
    'contadora'
];

//-----------------------------------------------------
// Palabras operativas
//-----------------------------------------------------

$rolesOperativos = [
    'almacen',
    'almacenero',
    'almacenera',
    'logistica',
    'logistico',
    'logistica',
    'repartidor',
    'repartidora',
    'delivery',
    'tecnico',
    'tecnica',
    'soporte',
    'produccion',
    'operario',
    'operaria'
];

//-----------------------------------------------------
// Perfil predeterminado
//-----------------------------------------------------

$tipoPerfil = 'GENERAL';

$esVendedor = false;

$esAdministrativo = false;

$esOperativo = false;

//-----------------------------------------------------
// Determinar perfil comercial
//-----------------------------------------------------

foreach (
    $rolesComerciales
    as $rolComercial
) {

    if (
        mb_strpos(
            $nombreRolNormalizado,
            $rolComercial
        ) !== false
    ) {

        $tipoPerfil = 'COMERCIAL';

        $esVendedor = true;

        break;
    }
}

//-----------------------------------------------------
// Determinar perfil administrativo
//-----------------------------------------------------

if (!$esVendedor) {

    foreach (
        $rolesAdministrativos
        as $rolAdministrativo
    ) {

        if (
            mb_strpos(
                $nombreRolNormalizado,
                $rolAdministrativo
            ) !== false
        ) {

            $tipoPerfil = 'ADMINISTRATIVO';

            $esAdministrativo = true;

            break;
        }
    }
}

//-----------------------------------------------------
// Determinar perfil operativo
//-----------------------------------------------------

if (
    !$esVendedor &&
    !$esAdministrativo
) {

    foreach (
        $rolesOperativos
        as $rolOperativo
    ) {

        if (
            mb_strpos(
                $nombreRolNormalizado,
                $rolOperativo
            ) !== false
        ) {

            $tipoPerfil = 'OPERATIVO';

            $esOperativo = true;

            break;
        }
    }
}

//=====================================================
// 5. ESTADÍSTICAS COMERCIALES
//=====================================================
//
// Solamente se consideran actividad comercial
// para perfiles identificados como comerciales.
//
// Sin embargo, también se consultan las ventas
// asociadas al empleado para no perder información
// histórica.
//

$totalVentas = 0;

$totalVendido = 0;

$ultimaVenta = null;

//-----------------------------------------------------
// Consulta ventas
//-----------------------------------------------------

$sqlVentas = "

    SELECT

        COUNT(*) AS total_ventas,

        COALESCE(
            SUM(total_venta),
            0
        ) AS total_vendido,

        MAX(
            CONCAT(
                fecha_venta,
                ' ',
                hora_venta
            )
        ) AS ultima_venta

    FROM ticket_ventas

    WHERE id_empleado = ?

      AND id_user = ?
";

//-----------------------------------------------------
// Preparar
//-----------------------------------------------------

$stmtVentas = mysqli_prepare(
    $conexion,
    $sqlVentas
);

if ($stmtVentas) {

    mysqli_stmt_bind_param(
        $stmtVentas,
        'ii',
        $idEmpleado,
        $idUser
    );

    //-------------------------------------------------
    // Ejecutar
    //-------------------------------------------------

    if (
        mysqli_stmt_execute(
            $stmtVentas
        )
    ) {

        mysqli_stmt_bind_result(
            $stmtVentas,

            $dbTotalVentas,
            $dbTotalVendido,
            $dbUltimaVenta
        );

        //-------------------------------------------------
        // Obtener resultado
        //-------------------------------------------------

        if (
            mysqli_stmt_fetch(
                $stmtVentas
            )
        ) {

            $totalVentas =
                (int) (
                    $dbTotalVentas ?? 0
                );

            $totalVendido =
                (float) (
                    $dbTotalVendido ?? 0
                );

            $ultimaVenta =
                !empty($dbUltimaVenta)
                ? $dbUltimaVenta
                : null;
        }
    } else {

        registrarErrorServidor(
            'Error estadísticas ventas: ' .
                mysqli_stmt_error($stmtVentas)
        );
    }

    mysqli_stmt_close(
        $stmtVentas
    );
}

//=====================================================
// 6. VENTAS / PEDIDOS POR ESTADO
//=====================================================

$ventasEstados = [

    'PENDIENTE' => 0,

    'CONFIRMADO' => 0,

    'PREPARANDO' => 0,

    'ENVIADO' => 0,

    'ENTREGADO' => 0,

    'CANCELADO' => 0
];

//-----------------------------------------------------
// Consulta
//-----------------------------------------------------

$sqlEstados = "

    SELECT

        estado_envio,

        COUNT(*) AS cantidad

    FROM ticket_ventas

    WHERE id_empleado = ?

      AND id_user = ?

    GROUP BY estado_envio
";

//-----------------------------------------------------
// Preparar
//-----------------------------------------------------

$stmtEstados = mysqli_prepare(
    $conexion,
    $sqlEstados
);

if ($stmtEstados) {

    mysqli_stmt_bind_param(
        $stmtEstados,
        'ii',
        $idEmpleado,
        $idUser
    );

    //-------------------------------------------------
    // Ejecutar
    //-------------------------------------------------

    if (
        mysqli_stmt_execute(
            $stmtEstados
        )
    ) {

        mysqli_stmt_store_result(
            $stmtEstados
        );

        mysqli_stmt_bind_result(
            $stmtEstados,

            $dbEstadoEnvio,
            $dbCantidadEstado
        );

        //-------------------------------------------------
        // Recorrer
        //-------------------------------------------------

        while (
            mysqli_stmt_fetch(
                $stmtEstados
            )
        ) {

            if (
                $dbEstadoEnvio !== null &&
                isset(
                    $ventasEstados[$dbEstadoEnvio]
                )
            ) {

                $ventasEstados[$dbEstadoEnvio] =
                    (int) $dbCantidadEstado;
            }
        }
    } else {

        registrarErrorServidor(
            'Error estados ventas: ' .
                mysqli_stmt_error($stmtEstados)
        );
    }

    mysqli_stmt_close(
        $stmtEstados
    );
}

//=====================================================
// 7. PERMISOS DEL ROL
//=====================================================

$permisos = [];

//-----------------------------------------------------
// Solo si tiene rol
//-----------------------------------------------------

if (
    !empty($dbIdRol)
) {

    $sqlPermisos = "

        SELECT

            m.id_modulo,

            m.nombre,

            m.codigo,

            m.icono,

            m.orden,

            pr.ver,

            pr.crear,

            pr.editar,

            pr.eliminar

        FROM permisos_rol pr

        INNER JOIN modulos m

            ON m.id_modulo = pr.id_modulo

            AND m.id_user = pr.id_user

        WHERE pr.id_rol = ?

          AND pr.id_user = ?

          AND m.estado = 1

        ORDER BY

            m.orden ASC,

            m.nombre ASC
    ";

    //-------------------------------------------------
    // Preparar
    //-------------------------------------------------

    $stmtPermisos = mysqli_prepare(
        $conexion,
        $sqlPermisos
    );

    if ($stmtPermisos) {

        mysqli_stmt_bind_param(
            $stmtPermisos,
            'ii',
            $dbIdRol,
            $idUser
        );

        //-------------------------------------------------
        // Ejecutar
        //-------------------------------------------------

        if (
            mysqli_stmt_execute(
                $stmtPermisos
            )
        ) {

            mysqli_stmt_store_result(
                $stmtPermisos
            );

            //-------------------------------------------------
            // Variables
            //-------------------------------------------------

            $dbIdModulo = null;
            $dbNombreModulo = null;
            $dbCodigoModulo = null;
            $dbIconoModulo = null;
            $dbOrdenModulo = null;

            $dbVer = null;
            $dbCrear = null;
            $dbEditar = null;
            $dbEliminar = null;

            //-------------------------------------------------
            // Bind
            //-------------------------------------------------

            mysqli_stmt_bind_result(
                $stmtPermisos,

                $dbIdModulo,
                $dbNombreModulo,
                $dbCodigoModulo,
                $dbIconoModulo,
                $dbOrdenModulo,

                $dbVer,
                $dbCrear,
                $dbEditar,
                $dbEliminar
            );

            //-------------------------------------------------
            // Recorrer
            //-------------------------------------------------

            while (
                mysqli_stmt_fetch(
                    $stmtPermisos
                )
            ) {

                $permisos[] = [

                    'id_modulo' =>
                    (int) $dbIdModulo,

                    'nombre' =>
                    $dbNombreModulo,

                    'codigo' =>
                    $dbCodigoModulo,

                    'icono' =>
                    $dbIconoModulo,

                    'orden' =>
                    (int) $dbOrdenModulo,

                    'ver' =>
                    (int) $dbVer,

                    'crear' =>
                    (int) $dbCrear,

                    'editar' =>
                    (int) $dbEditar,

                    'eliminar' =>
                    (int) $dbEliminar
                ];
            }
        } else {

            registrarErrorServidor(
                'Error permisos rol: ' .
                    mysqli_stmt_error(
                        $stmtPermisos
                    )
            );
        }

        mysqli_stmt_close(
            $stmtPermisos
        );
    } else {

        registrarErrorServidor(
            'Error preparando permisos: ' .
                mysqli_error($conexion)
        );
    }
}

//=====================================================
// 8. RESUMEN DE PERMISOS
//=====================================================

$totalModulos = count(
    $permisos
);

$totalPermisosActivos = 0;

foreach (
    $permisos as $permiso
) {

    $totalPermisosActivos +=
        (int) $permiso['ver'];

    $totalPermisosActivos +=
        (int) $permiso['crear'];

    $totalPermisosActivos +=
        (int) $permiso['editar'];

    $totalPermisosActivos +=
        (int) $permiso['eliminar'];
}

//=====================================================
// 9. ACTIVIDAD DEL EMPLEADO
//=====================================================
//
// Esta estructura permite que el JS adapte la interfaz
// dependiendo del cargo.
//
// COMERCIAL:
//   Mostrar ventas, total vendido, pedidos, etc.
//
// ADMINISTRATIVO:
//   No presentar ventas como KPI principal.
//
// OPERATIVO:
//   No presentar ventas como KPI principal.
//
// GENERAL:
//   Mostrar información general.
//

$actividad = [

    'tipo' =>
    $tipoPerfil,

    'es_comercial' =>
    $esVendedor,

    'es_administrativo' =>
    $esAdministrativo,

    'es_operativo' =>
    $esOperativo,

    'tiene_ventas' =>
    $totalVentas > 0,

    'total_ventas' =>
    $totalVentas,

    'total_vendido' =>
    $totalVendido,

    'ultima_venta' =>
    $ultimaVenta,

    'ventas_estados' =>
    $ventasEstados
];

//=====================================================
// 10. RESUMEN DEL EMPLEADO
//=====================================================

$resumen = [

    'total_modulos' =>
    $totalModulos,

    'total_permisos_activos' =>
    $totalPermisosActivos,

    'tiene_permisos' =>
    $totalPermisosActivos > 0
];

//=====================================================
// 11. RESPUESTA JSON
//=====================================================

responderJSON(
    true,
    'Empleado encontrado correctamente.',
    [

        //=================================================
        // INFORMACIÓN DEL EMPLEADO
        //=================================================

        'empleado' => [

            'id_empleado' =>
            (int) $dbIdEmpleado,

            'nombre' =>
            $dbNombre,

            'apellido' =>
            $dbApellido,

            'nombre_completo' =>
            $nombreCompleto,

            'dni' =>
            $dbDni,

            'celular' =>
            $dbCelular,

            'direccion' =>
            $dbDireccion,

            'email' =>
            $dbEmail,

            'estado' =>
            $dbEstado,

            'fecha_registro' =>
            $dbFechaRegistro,

            'fecha_actualizado' =>
            $dbFechaActualizado,

            //=============================================
            // ROL
            //=============================================

            'id_rol' =>
            $dbIdRol !== null
                ? (int) $dbIdRol
                : null,

            'nombre_rol' =>
            $dbNombreRol,

            //=============================================
            // IMAGEN
            //=============================================

            'imagen' =>
            $imagenBase64,

            //=============================================
            // UBICACIÓN
            //=============================================

            'id_pais' =>
            $dbIdPais !== null
                ? (int) $dbIdPais
                : null,

            'pais' =>
            $dbNombrePais,

            'id_departamento' =>
            $dbIdDepartamento !== null
                ? (int) $dbIdDepartamento
                : null,

            'departamento' =>
            $dbNombreDepartamento,

            'id_provincia' =>
            $dbIdProvincia !== null
                ? (int) $dbIdProvincia
                : null,

            'provincia' =>
            $dbNombreProvincia,

            'id_distrito' =>
            $dbIdDistrito !== null
                ? (int) $dbIdDistrito
                : null,

            'distrito' =>
            $dbNombreDistrito
        ],

        //=================================================
        // PERFIL
        //=================================================

        'perfil' => [

            'tipo' =>
            $tipoPerfil,

            'nombre_rol' =>
            $dbNombreRol,

            'es_comercial' =>
            $esVendedor,

            'es_administrativo' =>
            $esAdministrativo,

            'es_operativo' =>
            $esOperativo
        ],

        //=================================================
        // ACTIVIDAD
        //=================================================

        'actividad' =>
        $actividad,

        //=================================================
        // COMPATIBILIDAD CON EL JS ACTUAL
        //=================================================

        'estadisticas' => [

            'total_ventas' =>
            $totalVentas,

            'total_vendido' =>
            $totalVendido,

            'ultima_venta' =>
            $ultimaVenta
        ],

        //=================================================
        // ESTADOS DE PEDIDOS
        //=================================================

        'ventas_estados' =>
        $ventasEstados,

        //=================================================
        // PERMISOS
        //=================================================

        'permisos' =>
        $permisos,

        //=================================================
        // RESUMEN
        //=================================================

        'resumen' =>
        $resumen
    ]
);
