<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_exportar_estadisticas_empleados.php
// Módulo: Estadísticas de Empleados
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EXPORTAR ESTADÍSTICAS DE EMPLEADOS
======================================================-->

<div
    class="modal fade"
    id="modalExportarEstadisticasEmpleados"
    tabindex="-1"
    aria-labelledby="modalExportarEstadisticasEmpleadosLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header border-0">

                <div class="d-flex align-items-center">

                    <div
                        class="d-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success"
                        style="width: 48px; height: 48px;">

                        <i class="bi bi-file-earmark-excel-fill fs-4"></i>

                    </div>

                    <div class="ms-3">

                        <h5
                            class="modal-title fw-bold mb-0"
                            id="modalExportarEstadisticasEmpleadosLabel">

                            Exportar estadísticas

                        </h5>

                        <small class="text-muted">

                            Estadísticas de empleados

                        </small>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!--=================================================
                BODY
            ==================================================-->

            <div class="modal-body px-4 pb-4">


                <!--=================================================
                    INFORMACIÓN
                ==================================================-->

                <div class="alert alert-light border d-flex align-items-start mb-4">

                    <i class="bi bi-info-circle-fill text-primary me-2 mt-1"></i>

                    <div>

                        <div class="fw-semibold">

                            Exportar información actual

                        </div>

                        <small class="text-muted">

                            El archivo incluirá las estadísticas obtenidas
                            con los filtros actualmente aplicados.

                        </small>

                    </div>

                </div>


                <!--=================================================
                    CONTENIDO DEL ARCHIVO
                ==================================================-->

                <div class="mb-2">

                    <label class="form-label fw-semibold">

                        El archivo incluirá:

                    </label>

                </div>


                <div class="row g-2 mb-4">

                    <div class="col-6">

                        <div class="border rounded-3 p-2 d-flex align-items-center">

                            <i class="bi bi-bar-chart-fill text-primary me-2"></i>

                            <small>

                                Resumen

                            </small>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="border rounded-3 p-2 d-flex align-items-center">

                            <i class="bi bi-person-lines-fill text-primary me-2"></i>

                            <small>

                                Rendimiento

                            </small>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="border rounded-3 p-2 d-flex align-items-center">

                            <i class="bi bi-wallet2 text-primary me-2"></i>

                            <small>

                                Pagos

                            </small>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="border rounded-3 p-2 d-flex align-items-center">

                            <i class="bi bi-trophy-fill text-primary me-2"></i>

                            <small>

                                Ranking

                            </small>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="border rounded-3 p-2 d-flex align-items-center">

                            <i class="bi bi-graph-up text-primary me-2"></i>

                            <small>

                                Evolución de ventas

                            </small>

                        </div>

                    </div>


                    <div class="col-6">

                        <div class="border rounded-3 p-2 d-flex align-items-center">

                            <i class="bi bi-diagram-3-fill text-primary me-2"></i>

                            <small>

                                Roles

                            </small>

                        </div>

                    </div>

                </div>


                <!--=================================================
                    FILTROS ACTUALES
                ==================================================-->

                <div class="card bg-light border-0">

                    <div class="card-body p-3">

                        <div class="d-flex align-items-center mb-2">

                            <i class="bi bi-funnel-fill text-muted me-2"></i>

                            <small class="fw-semibold">

                                Filtros actuales

                            </small>

                        </div>


                        <div class="small text-muted">

                            <div class="d-flex justify-content-between mb-1">

                                <span>

                                    Empleado:

                                </span>

                                <span
                                    class="fw-semibold text-dark"
                                    id="exportarFiltroEmpleado">

                                    Todos los empleados

                                </span>

                            </div>


                            <div class="d-flex justify-content-between mb-1">

                                <span>

                                    Rol:

                                </span>

                                <span
                                    class="fw-semibold text-dark"
                                    id="exportarFiltroRol">

                                    Todos los roles

                                </span>

                            </div>


                            <div class="d-flex justify-content-between mb-1">

                                <span>

                                    Estado:

                                </span>

                                <span
                                    class="fw-semibold text-dark"
                                    id="exportarFiltroEstado">

                                    Todos

                                </span>

                            </div>


                            <div class="d-flex justify-content-between">

                                <span>

                                    Período:

                                </span>

                                <span
                                    class="fw-semibold text-dark"
                                    id="exportarFiltroFecha">

                                    Sin filtro

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer border-0 px-4 pb-4">

                <button
                    type="button"
                    class="btn btn-light border"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-circle me-1"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-success"
                    id="btnExportarEstadisticas">

                    <i class="bi bi-file-earmark-excel me-1"></i>

                    Exportar Excel

                </button>

            </div>


        </div>

    </div>

</div>