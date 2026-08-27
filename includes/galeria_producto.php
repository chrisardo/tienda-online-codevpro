<?php
// La variable $imagenes viene desde obtener_detalle_producto.php

$listaImagenes = [];

while ($img = mysqli_fetch_assoc($imagenes)) {
    $listaImagenes[] = $img;
}

$imagenPrincipal = !empty($listaImagenes) ? $listaImagenes[0] : null;
?>

<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body">

        <!-- Imagen Principal -->

        <div class="text-center mb-4">

            <?php if($imagenPrincipal){ ?>

                <img
                    id="imagenPrincipal"
                    src="mostrar_imagen.php?id=<?= $producto['idProducto']; ?>&img=<?= $imagenPrincipal['id_imagen']; ?>"
                    class="img-fluid rounded"
                    style="max-height:450px; object-fit:contain;">

            <?php }else{ ?>

                <img
                    src="assets/img/sin-imagen.png"
                    class="img-fluid rounded"
                    style="max-height:450px; object-fit:contain;">

            <?php } ?>

        </div>

        <!-- Miniaturas -->

        <?php if(count($listaImagenes)>1){ ?>

        <div class="row g-2">

            <?php foreach($listaImagenes as $img){ ?>

                <div class="col-3">

                    <img

                        src="mostrar_imagen.php?id=<?= $producto['id_producto']; ?>&img=<?= $img['id_imagen']; ?>"

                        class="img-thumbnail w-100 miniatura"

                        data-imagen="mostrar_imagen.php?id=<?= $producto['id_producto']; ?>&img=<?= $img['id_imagen']; ?>"

                        style="height:90px; object-fit:contain; cursor:pointer;">

                </div>

            <?php } ?>

        </div>

        <?php } ?>

    </div>

</div>