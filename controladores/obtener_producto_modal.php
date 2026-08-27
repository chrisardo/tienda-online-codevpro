<?php

include "../conexion.php";

$idProducto = $_POST["idProducto"] ?? 0;


if ($idProducto <= 0) {

    echo "Producto no encontrado";
    exit;
}


/*=========================================
PRODUCTO
=========================================*/

$sqlProducto = "
SELECT
    p.*,
    c.nombre AS categoria,
    m.nombre AS marca,
    i.id_imagen

FROM producto p

LEFT JOIN categorias c
ON p.id_categorias = c.id_categorias

LEFT JOIN marcas m
ON p.id_marca = m.id_marca

LEFT JOIN imagenes i
ON i.idProducto=p.idProducto
AND i.orden=1

WHERE p.idProducto='$idProducto'
";


$resultProducto = mysqli_query($conexion, $sqlProducto);


$producto = mysqli_fetch_assoc($resultProducto);



if (!$producto) {

    echo "Producto no encontrado";
    exit;
}


/*=========================================
TESTIMONIOS DEL PRODUCTO
=========================================*/

$sqlTestimonios = "
SELECT

t.calificacion,
t.comentario,
t.fecha,

c.nombre,
c.imagen

FROM testimonios t


INNER JOIN clientes c
ON t.idCliente=c.idCliente


WHERE

t.idProducto='$idProducto'

AND t.estado='APROBADO'


ORDER BY t.fecha DESC

LIMIT 5

";


$resultTestimonios = mysqli_query($conexion, $sqlTestimonios);



?>


<div class="row">


    <!-- IMAGEN -->

    <div class="col-md-5 text-center">


        <img

            src="mostrar_imagen.php?id=<?= $producto['idProducto']; ?>&img=<?= $producto['id_imagen']; ?>"

            class="img-fluid rounded"

            style="max-height:350px;object-fit:contain;">


    </div>




    <!-- INFORMACIÓN -->

    <div class="col-md-7">


        <h3 class="fw-bold">

            <?= $producto["nombre"]; ?>

        </h3>


        <p class="text-muted">

            <?= $producto["descripcion"]; ?>

        </p>


        <h4 class="text-primary">

            S/ <?= number_format($producto["precio"], 2); ?>

        </h4>



        <p>

            <i class="bi bi-box"></i>

            Stock:

            <?= $producto["stock"]; ?>

        </p>


        <button

            class="btn btn-primary btnAgregar"

            data-id="<?= $producto["idProducto"]; ?>">

            <i class="bi bi-cart-plus"></i>

            Agregar al carrito

        </button>


    </div>


</div>



<hr>



<h4 class="fw-bold mt-4">

    <i class="bi bi-chat-left-text"></i>

    Opiniones del producto

</h4>



<?php if (mysqli_num_rows($resultTestimonios) > 0) { ?>


    <?php while ($test = mysqli_fetch_assoc($resultTestimonios)) { ?>


        <div class="card mb-3 border-0 shadow-sm">


            <div class="card-body">


                <div class="d-flex align-items-center">


                    <div>


                        <strong>

                            <?= $test["nombre"]; ?>

                        </strong>


                        <br>


                        <small class="text-muted">

                            <?= $test["fecha"]; ?>

                        </small>


                    </div>


                </div>



                <div class="mt-2">


                    <?php for ($i = 1; $i <= 5; $i++) { ?>


                        <?php if ($i <= $test["calificacion"]) { ?>


                            <i class="bi bi-star-fill text-warning"></i>


                        <?php } else { ?>


                            <i class="bi bi-star text-secondary"></i>


                        <?php } ?>


                    <?php } ?>


                </div>



                <p class="mt-2">

                    <?= $test["comentario"]; ?>

                </p>


            </div>


        </div>


    <?php } ?>


<?php } else { ?>


    <div class="alert alert-light text-center">

        <i class="bi bi-chat"></i>

        Este producto todavía no tiene opiniones.

    </div>


<?php } ?>