<?php
//=====================================================
// CoDevPro Technology
// includes/dashboard/filtro_fecha.php
//=====================================================

$fechaInicio = $_GET["fecha_inicio"] ?? date("Y-m-01");
$fechaFin    = $_GET["fecha_fin"] ?? date("Y-m-d");
?>

<div class="card border-0 shadow-sm rounded-4 mb-4">

    <div class="card-body">
        <div class="mb-3">

            <button type="button"
                class="btn btn-sm btn-outline-primary btnFiltroRapido"
                data-inicio="<?= date('Y-m-d'); ?>"
                data-fin="<?= date('Y-m-d'); ?>">
                Hoy
            </button>

            <button type="button"
                class="btn btn-sm btn-outline-success btnFiltroRapido"
                data-inicio="<?= date('Y-m-01'); ?>"
                data-fin="<?= date('Y-m-d'); ?>">
                Este Mes
            </button>

            <button type="button"
                class="btn btn-sm btn-outline-warning btnFiltroRapido"
                data-inicio="<?= date('Y-01-01'); ?>"
                data-fin="<?= date('Y-m-d'); ?>">
                Este Año
            </button>

        </div>
        <form id="formFiltroDashboard">

            <div class="row align-items-end">

                <div class="col-lg-4">

                    <label class="form-label fw-semibold">

                        Fecha Inicio

                    </label>

                    <input type="date"
                        name="fecha_inicio"
                        class="form-control"
                        id="fecha_inicio"
                        value="<?= $fechaInicio; ?>">

                </div>


                <div class="col-lg-4">

                    <label class="form-label fw-semibold">

                        Fecha Fin

                    </label>

                    <input type="date"
                        name="fecha_fin"
                        id="fecha_fin"
                        class="form-control"
                        value="<?= $fechaFin; ?>">

                </div>


                <div class="col-lg-4">

                    <button type="button"
                        id="btnFiltrarDashboard"
                        class="btn btn-primary w-100">

                        <i class="bi bi-funnel-fill me-2"></i>

                        Filtrar Dashboard

                    </button>

                </div>
                <div class="mt-3 text-muted">

                    <small>

                        Mostrando datos desde

                        <strong>
                            <?= date("d/m/Y", strtotime($fechaInicio)); ?>
                        </strong>

                        hasta

                        <strong>
                            <?= date("d/m/Y", strtotime($fechaFin)); ?>
                        </strong>

                    </small>

                </div>
            </div>

        </form>

    </div>

</div>