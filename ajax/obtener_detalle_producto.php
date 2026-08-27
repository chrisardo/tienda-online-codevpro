<?php
//Estaparte es de ajax/obtener_detalle_producto.php
session_start();

require_once "../controladores/conexion.php";


$idProducto = intval($_POST["idProducto"] ?? 0);


$sql = "

SELECT

p.*,

c.nombre AS categoria,

m.nombre AS marca,

pr.nombre AS proveedor,

s.nombre AS sucursal

FROM producto p


LEFT JOIN categorias c
ON c.id_categorias = p.id_categorias


LEFT JOIN marcas m
ON m.id_marca = p.id_marca


LEFT JOIN provedores pr
ON pr.id_provedor = p.id_provedor


LEFT JOIN sucursal s
ON s.id_sucursal = p.id_sucursal


WHERE p.idProducto='$idProducto'

LIMIT 1

";


$query = mysqli_query($conexion, $sql);


if (mysqli_num_rows($query) == 0) {

    echo json_encode([
        "estado" => false
    ]);

    exit;
}


$p = mysqli_fetch_assoc($query);



ob_start();
?>

<div id="detalleProducto">

    <?php if ($p["tipo"] == "Producto"): ?>

        <!-- RESUMEN SUPERIOR -->

        <div class="row g-3 mb-4">

            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center">

                        <i class="bi bi-cash-stack text-success fs-1"></i>

                        <div class="text-muted">
                            Precio
                        </div>

                        <h3 class="fw-bold text-success">

                            S/ <?= number_format($p["precio"], 2); ?>

                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center">

                        <i class="bi bi-box text-primary fs-1"></i>

                        <div class="text-muted">
                            Stock
                        </div>

                        <h3 class="fw-bold">

                            <?= $p["stock"]; ?>

                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center">

                        <i class="bi bi-percent text-danger fs-1"></i>

                        <div class="text-muted">
                            Oferta
                        </div>

                        <?= $p["oferta"]
                            ? '<span class="badge bg-danger fs-6">Activa</span>'
                            : '<span class="badge bg-secondary fs-6">No</span>'; ?>

                    </div>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center">

                        <i class="bi bi-star-fill text-warning fs-1"></i>

                        <div class="text-muted">
                            Destacado
                        </div>

                        <?= $p["destacado"]
                            ? '<span class="badge bg-success fs-6">Sí</span>'
                            : '<span class="badge bg-secondary fs-6">No</span>'; ?>

                    </div>

                </div>

            </div>

        </div>

    <?php endif; ?>



    <!-- INFORMACIÓN GENERAL -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0 fw-bold">

                <i class="bi bi-info-circle me-2"></i>

                Información General

            </h5>

        </div>

        <div class="card-body">

            <table class="table table-hover align-middle mb-0">

                <tbody>

                    <tr>
                        <th width="220">Código</th>
                        <td><?= $p["codigo"]; ?></td>
                    </tr>

                    <tr>
                        <th>Nombre</th>
                        <td><?= $p["nombre"]; ?></td>
                    </tr>

                    <tr>
                        <th>Tipo</th>
                        <td>
                            <span class="badge bg-primary">
                                <?= $p["tipo"]; ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th>Precio</th>
                        <td class="fw-bold text-success">
                            S/ <?= number_format($p["precio"], 2); ?>
                        </td>
                    </tr>

                    <?php if ($p["tipo"] == "Producto"): ?>

                        <tr>
                            <th>Precio Anterior</th>
                            <td>
                                S/ <?= number_format($p["precio_anterior"], 2); ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Costo Compra</th>
                            <td>
                                S/ <?= number_format($p["costo_compra"], 2); ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Stock</th>
                            <td>
                                <?= $p["stock"]; ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Categoría</th>
                            <td>
                                <?= $p["categoria"]; ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Marca</th>
                            <td>
                                <?= $p["marca"]; ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Proveedor</th>
                            <td>
                                <?= $p["proveedor"]; ?>
                            </td>
                        </tr>

                    <?php endif; ?>

                    <tr>
                        <th>Sucursal</th>
                        <td>
                            <?= $p["sucursal"]; ?>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>



    <!-- DESCRIPCIÓN -->

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white">

            <h5 class="mb-0 fw-bold">

                <i class="bi bi-card-text me-2"></i>

                Descripción

            </h5>

        </div>

        <div class="card-body">

            <div class="bg-light rounded p-4">

                <?= nl2br($p["descripcion"]); ?>

            </div>

        </div>

    </div>

</div>

<?php


$html = ob_get_clean();



echo json_encode([

    "estado" => true,

    "html" => $html

]);
