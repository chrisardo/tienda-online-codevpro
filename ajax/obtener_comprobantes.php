<?php
//=========================================================
// CoDevPro Technology
// ajax/obtener_comprobantes.php
//=========================================================

session_start();

header("Content-Type: application/json; charset=utf-8");

ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once "../controladores/conexion.php";


/*=========================================================
VALIDAR SESIÓN
=========================================================*/

if (!isset($_SESSION["idUser"])) {

    echo json_encode([

        "estado" => false,

        "mensaje" => "Sesión expirada"

    ]);

    exit;
}


$idUser = intval($_SESSION["idUser"]);



/*=========================================================
RECIBIR DATOS AJAX
=========================================================*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);



$buscar = $data["buscar"] ?? "";

$fechaInicio = $data["fechaInicio"] ?? "";

$fechaFin = $data["fechaFin"] ?? "";

$tipo = $data["tipo"] ?? "";

$estado = $data["estado"] ?? "";

$metodoPago = $data["metodoPago"] ?? "";

$empleado = $data["empleado"] ?? "";

$cliente = $data["cliente"] ?? "";

$montoMin = $data["montoMin"] ?? "";

$montoMax = $data["montoMax"] ?? "";



$pagina = intval($data["pagina"] ?? 1);

$limite = intval($data["limite"] ?? 20);



if ($pagina < 1) {

    $pagina = 1;
}


if ($limite < 1) {

    $limite = 20;
}



$inicio = ($pagina - 1) * $limite;



/*=========================================================
WHERE DINÁMICO
=========================================================*/


$where = "

WHERE t.id_user = ?

";



$params = [

    $idUser

];


$types = "i";




/*=========================================================
BUSCADOR
=========================================================*/


if ($buscar != "") {


    $where .= "

    AND (

        t.serie LIKE ?

        OR t.numero LIKE ?

        OR c.nombre LIKE ?

        OR c.dni_o_ruc LIKE ?

    )

    ";


    $buscarLike = "%" . $buscar . "%";


    $params[] = $buscarLike;

    $params[] = $buscarLike;

    $params[] = $buscarLike;

    $params[] = $buscarLike;


    $types .= "ssss";
}



/*=========================================================
FECHAS
=========================================================*/


if ($fechaInicio != "" && $fechaFin != "") {


    $where .= "

    AND t.fecha_venta BETWEEN ? AND ?

    ";


    $params[] = $fechaInicio;

    $params[] = $fechaFin;


    $types .= "ss";
}




/*=========================================================
TIPO
=========================================================*/


if ($tipo != "") {


    $where .= "

    AND t.tipo_comprobante = ?

    ";


    $params[] = $tipo;


    $types .= "s";
}



/*=========================================================
ESTADO
=========================================================*/


if ($estado != "") {


    $where .= "

    AND t.estado_venta = ?

    ";


    $params[] = $estado;


    $types .= "s";
}




/*=========================================================
METODO PAGO
=========================================================*/


if ($metodoPago != "") {


    $where .= "

    AND t.id_metodo_pago = ?

    ";


    $params[] = intval($metodoPago);


    $types .= "i";
}




/*=========================================================
EMPLEADO
=========================================================*/


if ($empleado != "") {


    $where .= "

    AND t.id_empleado = ?

    ";


    $params[] = intval($empleado);


    $types .= "i";
}




/*=========================================================
CLIENTE
=========================================================*/


if ($cliente != "") {


    $where .= "

    AND t.idCliente = ?

    ";


    $params[] = intval($cliente);


    $types .= "i";
}




/*=========================================================
MONTO MINIMO
=========================================================*/


if ($montoMin != "") {


    $where .= "

    AND t.total_venta >= ?

    ";


    $params[] = floatval($montoMin);


    $types .= "d";
}





/*=========================================================
MONTO MAXIMO
=========================================================*/


if ($montoMax != "") {


    $where .= "

    AND t.total_venta <= ?

    ";


    $params[] = floatval($montoMax);


    $types .= "d";
}





/*=========================================================
ORDENAMIENTO
=========================================================*/


$orden = $data["ordenar"] ?? "fecha_desc";


switch ($orden) {


    case "fecha_asc":

        $orderSQL = "
        t.fecha_venta ASC,
        t.hora_venta ASC
        ";

        break;



    case "monto_desc":

        $orderSQL = "
        t.total_venta DESC
        ";

        break;



    case "monto_asc":

        $orderSQL = "
        t.total_venta ASC
        ";

        break;



    case "cliente_asc":

        $orderSQL = "
        c.nombre ASC
        ";

        break;



    case "cliente_desc":

        $orderSQL = "
        c.nombre DESC
        ";

        break;



    default:

        $orderSQL = "
        t.fecha_venta DESC,
        t.hora_venta DESC
        ";

        break;
}




/*=========================================================
CONTAR TOTAL REGISTROS
=========================================================*/


$sqlCount = "

SELECT COUNT(*) AS total

FROM ticket_ventas t


LEFT JOIN clientes c

ON t.idCliente = c.idCliente



$where

";



$stmtCount = mysqli_prepare(
    $conexion,
    $sqlCount
);



mysqli_stmt_bind_param(

    $stmtCount,

    $types,

    ...$params

);



mysqli_stmt_execute($stmtCount);



$resultCount = mysqli_stmt_get_result($stmtCount);



$totalRegistros = intval(
    mysqli_fetch_assoc($resultCount)["total"]
);



$totalPaginas = ceil(
    $totalRegistros / $limite
);




/*=========================================================
CONSULTA PRINCIPAL
=========================================================*/


$sql = "

SELECT


t.id_ticket_ventas,

t.tipo_comprobante,

t.serie,

t.numero,

t.fecha_venta,

t.hora_venta,

t.total_venta,

t.estado_venta,

t.estado_envio,

t.aplica_igv,



c.nombre AS cliente,

c.dni_o_ruc,



m.nombre AS metodo_pago,



CONCAT(
e.nombre,
' ',
e.apellido
) AS empleado



FROM ticket_ventas t



LEFT JOIN clientes c

ON t.idCliente = c.idCliente



LEFT JOIN metodo_pago m

ON t.id_metodo_pago = m.id_metodo_pago



LEFT JOIN empleados e

ON t.id_empleado = e.id_empleado



$where



ORDER BY

$orderSQL



LIMIT ?,?


";





$paramsFinal = $params;


$paramsFinal[] = $inicio;

$paramsFinal[] = $limite;



$typesFinal = $types . "ii";





$stmt = mysqli_prepare(
    $conexion,
    $sql
);





mysqli_stmt_bind_param(

    $stmt,

    $typesFinal,

    ...$paramsFinal

);





mysqli_stmt_execute($stmt);





$resultado = mysqli_stmt_get_result($stmt);





$comprobantes = [];





while ($fila = mysqli_fetch_assoc($resultado)) {


    $comprobantes[] = $fila;
}





/*=========================================================
RESPUESTA JSON
=========================================================*/


echo json_encode([


    "estado" => true,


    "comprobantes" => $comprobantes,


    "totalRegistros" => $totalRegistros,


    "totalPaginas" => $totalPaginas,


    "paginaActual" => $pagina



], JSON_UNESCAPED_UNICODE);




mysqli_stmt_close($stmt);

mysqli_stmt_close($stmtCount);


mysqli_close($conexion);
