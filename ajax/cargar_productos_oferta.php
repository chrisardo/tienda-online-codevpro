<?php
//==========================================================
// CoDevPro Technology
// ajax/cargar_productos_oferta.php
//==========================================================

session_start();

require_once "../controladores/conexion.php";

$idCliente = $_SESSION["idCliente"] ?? 0;

/*=============================================
PAGINACIÓN
=============================================*/

$pagina = isset($_POST["pagina"]) ? intval($_POST["pagina"]) : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$limite = 12;

$inicio = ($pagina - 1) * $limite;

/*=============================================
ORDEN
=============================================*/

$orden = $_POST["orden"] ?? "recientes";

switch ($orden) {

    case "precio_asc":
        $order = "p.precio ASC";
        break;

    case "precio_desc":
        $order = "p.precio DESC";
        break;

    case "vendidos":
        $order = "IFNULL(v.cantidad_total,0) DESC";
        break;

    case "descuento":
        $order = "p.descuento DESC";
        break;

    case "destacados":
        $order = "p.destacado DESC, p.idProducto DESC";
        break;

    case "nombre_asc":
        $order = "p.nombre ASC";
        break;

    case "nombre_desc":
        $order = "p.nombre DESC";
        break;

    default:
        $order = "p.idProducto DESC";
}

/*=============================================
FILTROS
=============================================*/

$categoria    = intval($_POST["categoria"] ?? 0);
$marca        = intval($_POST["marca"] ?? 0);
$precioMin    = floatval($_POST["precioMin"] ?? 0);
$precioMax    = floatval($_POST["precioMax"] ?? 0);
$stock        = intval($_POST["stock"] ?? 0);
$envioGratis  = intval($_POST["envioGratis"] ?? 0);

/*=============================================
WHERE DINÁMICO
=============================================*/

$where = "";

if ($categoria > 0) {
    $where .= " AND p.id_categorias = $categoria";
}

if ($marca > 0) {
    $where .= " AND p.id_marca = $marca";
}

if ($precioMin > 0) {
    $where .= " AND p.precio >= $precioMin";
}

if ($precioMax > 0) {
    $where .= " AND p.precio <= $precioMax";
}

if ($stock == 1) {
    $where .= " AND p.stock > 0";
}

if ($envioGratis == 1) {
    $where .= " AND p.envio_gratis = 1";
}

/*=============================================
PRODUCTOS EN OFERTA
=============================================*/

$sql = "SELECT

            p.*,

            m.nombre AS marca,

            i.id_imagen,

            CASE
                WHEN f.id_favorito IS NULL THEN 0
                ELSE 1
            END AS favorito,
            IFNULL(v.cantidad_total,0) AS vendidos

        FROM producto p

        LEFT JOIN marcas m
            ON p.id_marca = m.id_marca

        LEFT JOIN cantidad_producto_vendido v
            ON p.idProducto = v.idProducto

        LEFT JOIN imagenes i
            ON i.idProducto = p.idProducto
            AND i.orden = 1

        LEFT JOIN favoritos f
            ON f.idProducto = p.idProducto
            AND f.idCliente = $idCliente
        WHERE

            p.tipo = 'producto'

            AND p.oferta = 1

            AND p.Eliminado = 0

            $where

        ORDER BY $order

        LIMIT $inicio,$limite";

$resultado = mysqli_query($conexion, $sql);

/*=============================================
SIN PRODUCTOS
=============================================*/

if (mysqli_num_rows($resultado) == 0) {

?>

    <div class="col-12">

        <div class="alert alert-warning text-center">

            No existen productos en oferta.

        </div>

    </div>

<?php

    exit();
}
?>
<?php

while ($productos = mysqli_fetch_assoc($resultado)) {

?>

    <div class="col-lg-3 col-md-6 col-sm-6 mb-4 fade-card">

        <div class="card product-card h-100 shadow-sm">

            <!-- Descuento -->

            <?php if ($productos["descuento"] > 0) { ?>

                <span class="badge bg-danger badge-sale">

                    -<?= intval($productos["descuento"]); ?>%

                </span>

            <?php } ?>

            <!-- Acciones -->

            <div class="product-actions">

                <button
                    class="action-btn btnFavorito"
                    data-id="<?= $productos["idProducto"]; ?>">

                    <i class="bi <?= $productos["favorito"] ? "bi-heart-fill text-danger" : "bi-heart"; ?>"></i>

                </button>

                <button
                    class="btn btn-outline-secondary btnVista"
                    data-id="<?= $productos["idProducto"]; ?>">

                    <i class="bi bi-eye"></i>

                </button>

                <button
                    class="action-btn btnComparar"
                    data-id="<?= $productos["idProducto"]; ?>">

                    <i class="bi bi-arrow-left-right"></i>

                </button>

            </div>

            <!-- Imagen -->

            <?php if (!empty($productos["id_imagen"])) { ?>

                <img

                    src="mostrar_imagen.php?id=<?= $productos["idProducto"]; ?>&img=<?= $productos["id_imagen"]; ?>"

                    class="img-fluid rounded"

                    style="max-height:250px; object-fit:contain;">

            <?php } else { ?>

                <img

                    src="assets/img/sin_imagen.png"

                    class="img-fluid rounded"

                    style="max-height:250px; object-fit:contain;">

            <?php } ?>

            <div class="card-body d-flex flex-column">

                <span class="badge bg-success mb-2">

                    Oferta

                </span>

                <h5 class="fw-bold">

                    <?= htmlspecialchars($productos["nombre"]); ?>

                </h5>

                <small class="text-muted mb-2">

                    <?= htmlspecialchars($productos["marca"]); ?>

                </small>

                <?php if ($productos["precio_anterior"] > $productos["precio"]) { ?>

                    <div class="price-old">

                        S/ <?= number_format($productos["precio_anterior"], 2); ?>

                    </div>

                <?php } ?>

                <div class="price-new mb-2">

                    S/ <?= number_format($productos["precio"], 2); ?>

                </div>

                <?php if ($productos["stock"] > 0) { ?>

                    <div class="text-success small">

                        <i class="bi bi-check-circle"></i>

                        Stock Disponible

                    </div>

                <?php } else { ?>

                    <div class="text-danger small">

                        <i class="bi bi-x-circle"></i>

                        Sin Stock

                    </div>

                <?php } ?>

                <div class="small text-secondary mt-2">

                    <?= intval($productos["vendidos"]); ?>

                    vendidos

                </div>

                <div class="mt-auto d-grid gap-2 pt-3">

                    <?php if ($productos["stock"] > 0) { ?>

                        <button

                            class="btn btn-primary btnAgregar"

                            data-id="<?= $productos["idProducto"]; ?>">

                            <i class="bi bi-cart-plus"></i>

                            Agregar al carrito

                        </button>

                    <?php } else { ?>

                        <button

                            class="btn btn-secondary"

                            disabled>

                            Sin Stock

                        </button>

                    <?php } ?>

                </div>

            </div>

        </div>

    </div>

<?php

}

?>
<?php

/*=============================================
TOTAL DE PRODUCTOS
=============================================*/

$sqlTotal = "

SELECT COUNT(*) AS total

FROM producto p

WHERE

    p.tipo='producto'

    AND p.oferta=1

    AND p.Eliminado=0

    $where

";

$resultadoTotal = mysqli_query($conexion, $sqlTotal);

$total = mysqli_fetch_assoc($resultadoTotal);

/*=============================================
INPUTS PARA PAGINACIÓN AJAX
=============================================*/

?>

<input
    type="hidden"
    id="totalProductosAjax"
    value="<?= intval($total["total"]); ?>">

<input
    type="hidden"
    id="totalPaginasAjax"
    value="<?= ceil($total["total"] / $limite); ?>">