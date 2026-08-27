<?php

require_once __DIR__ . "/BaseController.php";

class CarritoController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | CONSULTA BASE DEL CARRITO
    |--------------------------------------------------------------------------
    */

    private function consultaBase()
    {

        $sql = "SELECT

                    c.idCarrito,
                    c.idProducto,
                    c.cantidad,
                    c.precio,
                    c.fecha,
                    c.estado,

                    p.nombre,
                    p.codigo,
                    p.stock,
                    p.descripcion,
                    p.oferta,
                    p.descuento,
                    p.precio_anterior,

                    ca.nombre AS categoria,
                    m.nombre AS marca,

                    (
                        SELECT i.id_imagen
                        FROM imagenes i
                        WHERE i.idProducto = p.idProducto
                        ORDER BY i.orden ASC
                        LIMIT 1
                    ) AS imagen

                FROM carrito_online c

                INNER JOIN producto p
                    ON p.idProducto = c.idProducto

                LEFT JOIN categorias ca
                    ON ca.id_categorias = p.id_categorias

                LEFT JOIN marcas m
                    ON m.id_marca = p.id_marca

                WHERE c.estado='pendiente'";

        return $sql;
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRO POR CLIENTE/TOKEN
    |--------------------------------------------------------------------------
    */

    private function filtroCarrito()
    {

        if ($this->clienteLogueado()) {

            return [
                "sql" => " AND c.idCliente=? ",
                "tipo" => "i",
                "valor" => $this->idCliente()
            ];
        }

        return [

            "sql" => " AND c.token=? ",

            "tipo" => "s",

            "valor" => $this->token()

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER PRODUCTOS DEL CARRITO
    |--------------------------------------------------------------------------
    */

    public function obtenerCarrito()
    {

        $base = $this->consultaBase();

        $filtro = $this->filtroCarrito();

        $sql = $base . $filtro["sql"] . " ORDER BY c.idCarrito DESC";

        $stmt = mysqli_prepare($this->conexion, $sql);

        mysqli_stmt_bind_param(

            $stmt,

            $filtro["tipo"],

            $filtro["valor"]

        );

        mysqli_stmt_execute($stmt);

        return mysqli_stmt_get_result($stmt);
    }

    /*
    |--------------------------------------------------------------------------
    | CONTADOR
    |--------------------------------------------------------------------------
    */

    public function obtenerContador()
    {

        if ($this->clienteLogueado()) {

            $sql = "SELECT

                        IFNULL(SUM(cantidad),0) total

                    FROM carrito_online

                    WHERE idCliente=?

                    AND estado='pendiente'";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(

                $stmt,

                "i",

                $this->idCliente()

            );
        } else {

            $sql = "SELECT

                        IFNULL(SUM(cantidad),0) total

                    FROM carrito_online

                    WHERE token=?

                    AND estado='pendiente'";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(

                $stmt,

                "s",

                $this->token()

            );
        }

        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        $fila = mysqli_fetch_assoc($res);

        return intval($fila["total"]);
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL DEL CARRITO
    |--------------------------------------------------------------------------
    */

    public function obtenerTotal()
    {

        $carrito = $this->obtenerCarrito();

        $total = 0;

        while ($item = mysqli_fetch_assoc($carrito)) {

            $total += ($item["cantidad"] * $item["precio"]);
        }

        return $total;
    }

    /*
    |--------------------------------------------------------------------------
    | SUBTOTAL
    |--------------------------------------------------------------------------
    */

    public function obtenerSubtotal($cantidad, $precio)
    {

        return $cantidad * $precio;
    }

    /*
    |--------------------------------------------------------------------------
    | CANTIDAD DE PRODUCTOS DIFERENTES
    |--------------------------------------------------------------------------
    */

    public function obtenerItems()
    {

        if ($this->clienteLogueado()) {

            $sql = "SELECT COUNT(*) total
                    FROM carrito_online
                    WHERE idCliente=?
                    AND estado='pendiente'";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $this->idCliente()
            );
        } else {

            $sql = "SELECT COUNT(*) total
                    FROM carrito_online
                    WHERE token=?
                    AND estado='pendiente'";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $this->token()
            );
        }

        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        $fila = mysqli_fetch_assoc($res);

        return intval($fila["total"]);
    }

    /*
    |--------------------------------------------------------------------------
    | CARRITO VACÍO
    |--------------------------------------------------------------------------
    */

    public function carritoVacio()
    {

        return $this->obtenerItems() == 0;
    }
    /*
|--------------------------------------------------------------------------
| OBTENER PRODUCTO
|--------------------------------------------------------------------------
*/

    private function obtenerProducto($idProducto)
    {
        $sql = "SELECT
                idProducto,
                nombre,
                precio,
                stock,
                Eliminado
            FROM producto
            WHERE idProducto = ?
            LIMIT 1";

        $stmt = mysqli_prepare($this->conexion, $sql);

        mysqli_stmt_bind_param($stmt, "i", $idProducto);

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($resultado);
    }
    /*
|--------------------------------------------------------------------------
| BUSCAR PRODUCTO EN CARRITO
|--------------------------------------------------------------------------
*/

    private function buscarProductoCarrito($idProducto)
    {
        if ($this->clienteLogueado()) {

            $sql = "SELECT *
                FROM carrito_online
                WHERE idCliente=?
                AND idProducto=?
                AND estado='pendiente'
                LIMIT 1";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $this->idCliente(),
                $idProducto
            );
        } else {

            $sql = "SELECT *
                FROM carrito_online
                WHERE token=?
                AND idProducto=?
                AND estado='pendiente'
                LIMIT 1";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $this->token(),
                $idProducto
            );
        }

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($resultado);
    }
    /*
|--------------------------------------------------------------------------
| VALIDAR STOCK
|--------------------------------------------------------------------------
*/

    private function validarStock($stock, $cantidad)
    {
        return $stock >= $cantidad;
    }
    /*
|--------------------------------------------------------------------------
| INSERTAR PRODUCTO
|--------------------------------------------------------------------------
*/

    private function insertarProducto($producto, $cantidad)
    {
        $sql = "INSERT INTO carrito_online(

                idCliente,
                token,
                idProducto,
                cantidad,
                precio,
                fecha,
                estado

            )

            VALUES(

                ?,
                ?,
                ?,
                ?,
                ?,
                NOW(),
                'pendiente'

            )";

        $stmt = mysqli_prepare($this->conexion, $sql);

        mysqli_stmt_bind_param(

            $stmt,

            "isiid",

            $this->idCliente(),
            $this->token(),
            $producto["idProducto"],
            $cantidad,
            $producto["precio"]

        );

        return mysqli_stmt_execute($stmt);
    }
    /*
|--------------------------------------------------------------------------
| ACTUALIZAR CANTIDAD
|--------------------------------------------------------------------------
*/

    private function actualizarCantidadExistente($idCarrito, $cantidad)
    {
        $sql = "UPDATE carrito_online
            SET cantidad = ?
            WHERE idCarrito = ?";

        $stmt = mysqli_prepare($this->conexion, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $cantidad,
            $idCarrito
        );

        return mysqli_stmt_execute($stmt);
    }
    /*
|--------------------------------------------------------------------------
| AGREGAR PRODUCTO
|--------------------------------------------------------------------------
*/

    public function agregarProducto($idProducto, $cantidad = 1)
    {
        try {

            $this->begin();

            $producto = $this->obtenerProducto($idProducto);

            if (!$producto) {

                $this->rollback();

                return [
                    "success" => false,
                    "message" => "Producto no encontrado."
                ];
            }

            if ($producto["Eliminado"] == 1) {

                $this->rollback();

                return [
                    "success" => false,
                    "message" => "Producto eliminado."
                ];
            }

            if (!$this->validarStock($producto["stock"], $cantidad)) {

                $this->rollback();

                return [
                    "success" => false,
                    "message" => "Stock insuficiente."
                ];
            }

            $existe = $this->buscarProductoCarrito($idProducto);

            if ($existe) {

                $nuevaCantidad = $existe["cantidad"] + $cantidad;

                if (!$this->validarStock($producto["stock"], $nuevaCantidad)) {

                    $this->rollback();

                    return [
                        "success" => false,
                        "message" => "No existe suficiente stock."
                    ];
                }

                $this->actualizarCantidadExistente(
                    $existe["idCarrito"],
                    $nuevaCantidad
                );
            } else {

                $this->insertarProducto(
                    $producto,
                    $cantidad
                );
            }

            $this->commit();

            return [

                "success" => true,

                "message" => "Producto agregado correctamente.",

                "contador" => $this->obtenerContador()

            ];
        } catch (Exception $e) {

            $this->rollback();

            return [

                "success" => false,

                "message" => $e->getMessage()

            ];
        }
    }

    /*
|--------------------------------------------------------------------------
| OBTENER PRODUCTO DEL CARRITO
|--------------------------------------------------------------------------
*/

    private function obtenerProductoCarrito($idCarrito)
    {
        if ($this->clienteLogueado()) {

            $sql = "SELECT
                    c.*,
                    p.stock
                FROM carrito_online c
                INNER JOIN producto p
                    ON p.idProducto = c.idProducto
                WHERE c.idCarrito = ?
                AND c.idCliente = ?
                AND c.estado='pendiente'
                LIMIT 1";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $idCarrito,
                $this->idCliente()
            );
        } else {

            $sql = "SELECT
                    c.*,
                    p.stock
                FROM carrito_online c
                INNER JOIN producto p
                    ON p.idProducto = c.idProducto
                WHERE c.idCarrito = ?
                AND c.token = ?
                AND c.estado='pendiente'
                LIMIT 1";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "is",
                $idCarrito,
                $this->token()
            );
        }

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        return mysqli_fetch_assoc($resultado);
    }
    /*
|--------------------------------------------------------------------------
| ACTUALIZAR CANTIDAD
|--------------------------------------------------------------------------
*/

    public function actualizarCantidad($idCarrito, $accion)
    {
        try {

            $this->begin();

            $producto = $this->obtenerProductoCarrito($idCarrito);

            if (!$producto) {

                $this->rollback();

                return [
                    "success" => false,
                    "message" => "Producto no encontrado."
                ];
            }

            $cantidad = intval($producto["cantidad"]);

            switch ($accion) {

                case "sumar":

                    $cantidad++;

                    if ($cantidad > $producto["stock"]) {

                        $this->rollback();

                        return [
                            "success" => false,
                            "message" => "No hay suficiente stock."
                        ];
                    }

                    break;

                case "restar":

                    $cantidad--;

                    break;

                default:

                    $this->rollback();

                    return [
                        "success" => false,
                        "message" => "Acción inválida."
                    ];
            }

            if ($cantidad <= 0) {

                return $this->eliminarProducto($idCarrito);
            }

            $sql = "UPDATE carrito_online
              SET cantidad=?
              WHERE idCarrito=?";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $cantidad,
                $idCarrito
            );

            mysqli_stmt_execute($stmt);

            $this->commit();

            return [

                "success" => true,

                "message" => "Cantidad actualizada.",

                "contador" => $this->obtenerContador()

            ];
        } catch (Exception $e) {

            $this->rollback();

            return [

                "success" => false,

                "message" => $e->getMessage()

            ];
        }
    }
    /*
|--------------------------------------------------------------------------
| ELIMINAR PRODUCTO
|--------------------------------------------------------------------------
*/

    public function eliminarProducto($idCarrito)
    {
        try {

            $this->begin();

            $producto = $this->obtenerProductoCarrito($idCarrito);

            if (!$producto) {

                $this->rollback();

                return [

                    "success" => false,

                    "message" => "Producto no encontrado."

                ];
            }

            $sql = "DELETE FROM carrito_online
              WHERE idCarrito=?";

            $stmt = mysqli_prepare($this->conexion, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $idCarrito
            );

            mysqli_stmt_execute($stmt);

            $this->commit();

            return [

                "success" => true,

                "message" => "Producto eliminado.",

                "contador" => $this->obtenerContador()

            ];
        } catch (Exception $e) {

            $this->rollback();

            return [

                "success" => false,

                "message" => $e->getMessage()

            ];
        }
    }
    /*
|--------------------------------------------------------------------------
| VACIAR CARRITO
|--------------------------------------------------------------------------
*/

    public function vaciarCarrito()
    {
        try {

            $this->begin();

            if ($this->clienteLogueado()) {

                $sql = "DELETE
                  FROM carrito_online
                  WHERE idCliente=?
                  AND estado='pendiente'";

                $stmt = mysqli_prepare($this->conexion, $sql);

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $this->idCliente()
                );
            } else {

                $sql = "DELETE
                  FROM carrito_online
                  WHERE token=?
                  AND estado='pendiente'";

                $stmt = mysqli_prepare($this->conexion, $sql);

                mysqli_stmt_bind_param(
                    $stmt,
                    "s",
                    $this->token()
                );
            }

            mysqli_stmt_execute($stmt);

            $this->commit();

            return [

                "success" => true,

                "message" => "Carrito vaciado correctamente.",

                "contador" => 0

            ];
        } catch (Exception $e) {

            $this->rollback();

            return [

                "success" => false,

                "message" => $e->getMessage()

            ];
        }
    }
}
