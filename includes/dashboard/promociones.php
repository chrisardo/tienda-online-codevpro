<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/promociones.php
//=====================================================


//-----------------------------------------
// DATOS TEMPORALES
//-----------------------------------------

$promociones = [

    [

        "titulo" => "Oferta Gamer",
        "descuento" => "30%",
        "descripcion" => "Accesorios Gamer seleccionados.",
        "fecha" => "Hasta el 31 de Julio",
        "color" => "primary"

    ],

    [

        "titulo" => "SSD en Oferta",
        "descuento" => "20%",
        "descripcion" => "Todos los SSD con descuento.",
        "fecha" => "Hasta el 28 de Julio",
        "color" => "success"

    ],

    [

        "titulo" => "Licencias Office",
        "descuento" => "15%",
        "descripcion" => "Office 2021 y Office 365.",
        "fecha" => "Hasta el 15 de Agosto",
        "color" => "warning"

    ],

];

?>

<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body">


        <!-- TITULO -->

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h5 class="fw-bold mb-0">

                Promociones Activas

            </h5>

            <span class="badge bg-danger">

                <?php echo count($promociones); ?>

                Activas

            </span>

        </div>


        <!-- PROMOCIONES -->

        <div class="row g-4">


            <?php foreach ($promociones as $promo): ?>


                <div class="col-lg-4">

                    <div class="card card-promocion border-0 shadow-sm">


                        <!-- CABECERA -->

                        <div class="card-header bg-<?php echo $promo["color"]; ?> text-white py-4">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small>

                                        DESCUENTO

                                    </small>

                                    <div class="descuento">

                                        <?php echo $promo["descuento"]; ?>

                                    </div>

                                </div>


                                <i class="bi bi-megaphone-fill fs-1"></i>

                            </div>

                        </div>



                        <!-- CONTENIDO -->

                        <div class="card-body">


                            <div class="titulo-promocion mb-2">

                                <?php echo $promo["titulo"]; ?>

                            </div>


                            <div class="descripcion-promocion mb-3">

                                <?php echo $promo["descripcion"]; ?>

                            </div>


                            <div class="fecha-promocion mb-4">

                                <i class="bi bi-calendar-event me-1"></i>

                                <?php echo $promo["fecha"]; ?>

                            </div>



                            <!-- BOTONES -->

                            <div class="d-grid gap-2">


                                <a href="#"
                                    class="btn btn-outline-<?php echo $promo["color"]; ?>">

                                    Ver Promoción

                                </a>


                                <a href="#"
                                    class="btn btn-<?php echo $promo["color"]; ?>">

                                    Editar

                                </a>


                            </div>


                        </div>


                    </div>

                </div>


            <?php endforeach; ?>


        </div>



        <!-- RESUMEN -->


        <hr class="my-4">


        <div class="row text-center">


            <div class="col-md-4">

                <h4 class="fw-bold text-primary">

                    3

                </h4>

                <small class="text-muted">

                    Promociones activas

                </small>

            </div>


            <div class="col-md-4">

                <h4 class="fw-bold text-success">

                    1,245

                </h4>

                <small class="text-muted">

                    Productos vendidos

                </small>

            </div>


            <div class="col-md-4">

                <h4 class="fw-bold text-danger">

                    18%

                </h4>

                <small class="text-muted">

                    Incremento en ventas

                </small>

            </div>


        </div>


    </div>

</div>