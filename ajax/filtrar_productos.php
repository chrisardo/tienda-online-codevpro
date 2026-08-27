<?php
require_once "../controladores/conexion.php";

/*=========================================================
RECIBIR FILTROS
=========================================================*/

$buscar     = trim($_POST["buscar"] ?? "");
$categorias = $_POST["categorias"] ?? [];
$marcas     = $_POST["marcas"] ?? [];
$precioMin  = $_POST["precioMin"] ?? "";
$precioMax  = $_POST["precioMax"] ?? "";
$stock      = $_POST["stock"] ?? "";
$orden      = $_POST["orden"] ?? "recientes";
$idCliente = $_SESSION["idCliente"] ?? 0;
/*=========================================================
CONSULTA BASE
=========================================================*/

$sql = "SELECT

            p.*,

            c.nombre AS categoria,

            m.nombre AS marca,

            i.id_imagen,
            i.imagenes as imagen,
            CASE
                WHEN f.id_favorito IS NULL THEN 0
                ELSE 1
            END AS favorito

        FROM producto p

        LEFT JOIN categorias c
            ON c.id_categorias=p.id_categorias

        LEFT JOIN marcas m
            ON m.id_marca=p.id_marca

        LEFT JOIN imagenes i
            ON i.idProducto=p.idProducto
            AND i.orden=1
        LEFT JOIN favoritos f
                    ON f.idProducto=p.idProducto
                    AND f.idCliente=$idCliente

        WHERE p.Eliminado=0
        AND p.tipo='producto'";



/*=========================================================
BUSCADOR
=========================================================*/

if ($buscar != "") {

    $buscar = mysqli_real_escape_string($conexion, $buscar);

    $sql .= " AND (

                p.nombre LIKE '%$buscar%'

                OR p.codigo LIKE '%$buscar%'

                OR p.descripcion LIKE '%$buscar%'

            )";
}

/*=========================================================
CATEGORÍAS
=========================================================*/

if (!empty($categorias)) {

    $categorias = array_map("intval", $categorias);

    $sql .= " AND p.id_categorias IN (" . implode(",", $categorias) . ")";
}

/*=========================================================
MARCAS
=========================================================*/

if (!empty($marcas)) {

    $marcas = array_map("intval", $marcas);

    $sql .= " AND p.id_marca IN (" . implode(",", $marcas) . ")";
}

/*=========================================================
PRECIO
=========================================================*/

if ($precioMin !== "") {

    $sql .= " AND p.precio >= " . floatval($precioMin);
}

if ($precioMax !== "") {

    $sql .= " AND p.precio <= " . floatval($precioMax);
}

/*=========================================================
STOCK
=========================================================*/

if ($stock == "1") {

    $sql .= " AND p.stock>0";
}

/*=========================================================
ORDEN
=========================================================*/

switch ($orden) {

    case "precioAsc":

        $sql .= " ORDER BY p.precio ASC";

        break;

    case "precioDesc":

        $sql .= " ORDER BY p.precio DESC";

        break;

    case "nombre":

        $sql .= " ORDER BY p.nombre ASC";

        break;

    default:

        $sql .= " ORDER BY p.idProducto DESC";

        break;
}

$resultado = mysqli_query($conexion, $sql);

/*=========================================================
SIN RESULTADOS
=========================================================*/

if (mysqli_num_rows($resultado) == 0) {
?>

    <div class="col-12">

        <div class="alert alert-warning text-center">

            <i class="bi bi-search"></i>

            No se encontraron productos.

        </div>

    </div>

<?php
    exit;
}

/*=========================================================
PRODUCTOS
=========================================================*/

while ($producto = mysqli_fetch_assoc($resultado)) {
?>

    <div class="col-xl-3 col-lg-4 col-md-6">

        <div class="card h-100 shadow-sm border-0 rounded-4">

            <div class="position-relative">

                <?php if ($producto['oferta'] == 1) { ?>

                    <span class="badge bg-danger position-absolute m-3">

                        -<?= $producto['descuento']; ?>%

                    </span>

                <?php } ?>

                <?php if (!empty($producto['imagen'])) { ?>
                    <img
                        src="data:image/jpeg;base64,<?= base64_encode($producto['imagen']) ?>"
                        class="d-block w-100 rounded">
                <?php } else { ?>

                    <img
                        src="./assets/img/sin_imagen.png"
                        class="img-fluid rounded"
                        alt="Sin imagen">

                <?php } ?>

            </div>

            <div class="card-body d-flex flex-column">

                <span class="text-primary small">

                    <?= htmlspecialchars($producto['categoria']); ?>

                </span>

                <h5 class="fw-bold">

                    <?= htmlspecialchars($producto['nombre']); ?>

                </h5>

                <p class="text-secondary small">

                    <?= htmlspecialchars(mb_substr($producto['descripcion'], 0, 80)); ?>...

                </p>

                <div class="mb-3">

                    <?php if ($producto['oferta'] == 1) { ?>

                        <div class="text-decoration-line-through text-secondary">

                            S/

                            <?= number_format($producto['precio_anterior'], 2); ?>

                        </div>

                    <?php } ?>

                    <div class="fs-3 fw-bold text-primary">

                        S/

                        <?= number_format($producto['precio'], 2); ?>

                    </div>

                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">

                    <?php if ($producto["stock"] > 0) { ?>

                        <span class="badge bg-success">

                            Disponible

                        </span>

                    <?php } else { ?>

                        <span class="badge bg-danger">

                            Agotado

                        </span>

                    <?php } ?>

                    <small class="text-muted">

                        <?= htmlspecialchars($producto["marca"]); ?>

                    </small>

                </div>

                <div class="d-grid gap-2 mt-auto">

                    <button

                        class="btn btn-primary btnAgregar"

                        data-id="<?= $producto["idProducto"]; ?>">

                        <i class="bi bi-cart-plus"></i>
                    </button>

                    <div class="btn-group">

                        <button

                            class="btn btn-outline-danger btnFavorito"

                            data-id="<?= $producto["idProducto"]; ?>">

                            <i class="bi <?= $producto["favorito"] ? "bi-heart-fill text-danger" : "bi-heart"; ?>"></i>

                        </button>

                        <button

                            class="btn btn-outline-secondary btnVista"

                            data-id="<?= $producto["idProducto"]; ?>">

                            <i class="bi bi-eye"></i>

                        </button>

                        <button

                            class="btn btn-outline-dark btnComparar"

                            data-id="<?= $producto["idProducto"]; ?>">

                            <i class="bi bi-shuffle"></i>

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

<?php
}
?>