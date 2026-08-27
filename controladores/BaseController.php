<?php

require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/token_carrito.php";

class BaseController
{
    protected $conexion;
    protected $idCliente;
    protected $idUsuario;
    protected $token;

    public function __construct()
    {
        global $conexion;

        if (!$conexion) {
            throw new Exception("No existe conexión con la base de datos.");
        }

        $this->conexion = $conexion;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->idCliente = isset($_SESSION["idCliente"])
            ? intval($_SESSION["idCliente"])
            : 0;

        $this->idUsuario = isset($_SESSION["usId"])
            ? intval($_SESSION["usId"])
            : 0;

        $this->token = obtenerTokenCarrito();
    }

    /*
    |--------------------------------------------------------------------------
    | RESPUESTAS JSON
    |--------------------------------------------------------------------------
    */

    protected function success($mensaje = "", $data = [])
    {
        header("Content-Type: application/json; charset=utf-8");

        echo json_encode([
            "success" => true,
            "message" => $mensaje,
            "data" => $data
        ]);

        exit;
    }

    protected function error($mensaje = "", $data = [])
    {
        header("Content-Type: application/json; charset=utf-8");

        echo json_encode([
            "success" => false,
            "message" => $mensaje,
            "data" => $data
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACCIONES MYSQLI
    |--------------------------------------------------------------------------
    */

    protected function begin()
    {
        mysqli_begin_transaction($this->conexion);
    }

    protected function commit()
    {
        mysqli_commit($this->conexion);
    }

    protected function rollback()
    {
        mysqli_rollback($this->conexion);
    }

    /*
    |--------------------------------------------------------------------------
    | LIMPIAR CADENAS
    |--------------------------------------------------------------------------
    */

    protected function limpiar($texto)
    {
        return htmlspecialchars(trim($texto), ENT_QUOTES, "UTF-8");
    }

    /*
    |--------------------------------------------------------------------------
    | CONVERTIR ENTERO
    |--------------------------------------------------------------------------
    */

    protected function entero($valor)
    {
        return intval($valor);
    }

    /*
    |--------------------------------------------------------------------------
    | CONVERTIR DECIMAL
    |--------------------------------------------------------------------------
    */

    protected function decimal($valor)
    {
        return floatval($valor);
    }

    /*
    |--------------------------------------------------------------------------
    | FECHA
    |--------------------------------------------------------------------------
    */

    protected function fecha()
    {
        return date("Y-m-d");
    }

    /*
    |--------------------------------------------------------------------------
    | HORA
    |--------------------------------------------------------------------------
    */

    protected function hora()
    {
        return date("H:i:s");
    }

    /*
    |--------------------------------------------------------------------------
    | FECHA COMPLETA
    |--------------------------------------------------------------------------
    */

    protected function fechaHora()
    {
        return date("Y-m-d H:i:s");
    }

    /*
    |--------------------------------------------------------------------------
    | TOKEN
    |--------------------------------------------------------------------------
    */

    protected function token()
    {
        return $this->token;
    }

    /*
    |--------------------------------------------------------------------------
    | CLIENTE
    |--------------------------------------------------------------------------
    */

    protected function idCliente()
    {
        return $this->idCliente;
    }

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRADOR
    |--------------------------------------------------------------------------
    */

    protected function idUsuario()
    {
        return $this->idUsuario;
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN CLIENTE
    |--------------------------------------------------------------------------
    */

    protected function clienteLogueado()
    {
        return $this->idCliente > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN ADMIN
    |--------------------------------------------------------------------------
    */

    protected function administradorLogueado()
    {
        return $this->idUsuario > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAR CÓDIGO ALEATORIO
    |--------------------------------------------------------------------------
    */

    protected function generarCodigo($longitud = 10)
    {
        return strtoupper(substr(bin2hex(random_bytes($longitud)), 0, $longitud));
    }

    /*
    |--------------------------------------------------------------------------
    | EJECUTAR CONSULTA PREPARADA
    |--------------------------------------------------------------------------
    */

    protected function ejecutar($sql)
    {
        return mysqli_prepare($this->conexion, $sql);
    }

    /*
    |--------------------------------------------------------------------------
    | EXISTE PRODUCTO
    |--------------------------------------------------------------------------
    */

    protected function existeProducto($idProducto)
    {
        $sql = "SELECT idProducto
                FROM producto
                WHERE idProducto=?
                LIMIT 1";

        $stmt = mysqli_prepare($this->conexion, $sql);

        mysqli_stmt_bind_param($stmt, "i", $idProducto);

        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        return mysqli_num_rows($res) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK
    |--------------------------------------------------------------------------
    */

    protected function obtenerStock($idProducto)
    {
        $sql = "SELECT stock
                FROM producto
                WHERE idProducto=?
                LIMIT 1";

        $stmt = mysqli_prepare($this->conexion, $sql);

        mysqli_stmt_bind_param($stmt, "i", $idProducto);

        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        if ($fila = mysqli_fetch_assoc($res)) {

            return intval($fila["stock"]);

        }

        return 0;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMATO MONEDA
    |--------------------------------------------------------------------------
    */

    protected function moneda($valor)
    {
        return number_format($valor, 2, ".", "");
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER TOTAL DEL CARRITO
    |--------------------------------------------------------------------------
    */

    protected function totalCarrito()
    {
        if ($this->clienteLogueado()) {

            $sql = "SELECT IFNULL(SUM(cantidad),0) total
                    FROM carrito_online
                    WHERE idCliente=?
                    AND estado='pendiente'";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $this->idCliente
            );

        } else {

            $sql = "SELECT IFNULL(SUM(cantidad),0) total
                    FROM carrito_online
                    WHERE token=?
                    AND estado='pendiente'";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $this->token
            );

        }

        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        $fila = mysqli_fetch_assoc($res);

        return intval($fila["total"]);
    }

    /*
    |--------------------------------------------------------------------------
    | RESPUESTA 404
    |--------------------------------------------------------------------------
    */

    protected function notFound()
    {
        http_response_code(404);

        exit("404");
    }

    /*
    |--------------------------------------------------------------------------
    | RESPUESTA 403
    |--------------------------------------------------------------------------
    */

    protected function forbidden()
    {
        http_response_code(403);

        exit("403");
    }
}