<!--=====================================================
    MODAL - EXPORTAR ESTADÍSTICAS DE PROVEEDORES
======================================================-->

<div
    class="modal fade"
    id="modalExportarEstadisticasProveedores"
    tabindex="-1"
    aria-labelledby="modalExportarEstadisticasProveedoresLabel"
    aria-hidden="true">

    <div
        class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title fw-bold"
                        id="modalExportarEstadisticasProveedoresLabel">

                        <i class="bi bi-download me-2 text-primary"></i>

                        Exportar estadísticas

                    </h5>

                    <small class="text-muted">

                        Selecciona la información y formato que deseas exportar.

                    </small>

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

            <div class="modal-body">


                <!--=================================================
                    FILTROS ACTUALES
                ==================================================-->

                <div
                    class="bg-light rounded-3 p-3 mb-4">


                    <div
                        class="d-flex align-items-center gap-2 mb-3">

                        <i class="bi bi-funnel-fill text-primary"></i>

                        <span class="fw-semibold">

                            Filtros actuales

                        </span>

                    </div>


                    <div class="row g-2">


                        <div class="col-12">

                            <div class="d-flex justify-content-between">

                                <span class="text-muted">
                                    Proveedor
                                </span>

                                <span
                                    class="fw-semibold text-end"
                                    id="exportarFiltroProveedor">

                                    Todos los proveedores

                                </span>

                            </div>

                        </div>


                        <div class="col-12">

                            <div class="d-flex justify-content-between">

                                <span class="text-muted">
                                    Período
                                </span>

                                <span
                                    class="fw-semibold text-end"
                                    id="exportarFiltroFecha">

                                    Todo el período

                                </span>

                            </div>

                        </div>


                        <div class="col-12">

                            <div class="d-flex justify-content-between">

                                <span class="text-muted">
                                    Estado
                                </span>

                                <span
                                    class="fw-semibold text-end"
                                    id="exportarFiltroEstado">

                                    Todos

                                </span>

                            </div>

                        </div>


                    </div>

                </div>



                <!--=================================================
                    INFORMACIÓN A EXPORTAR
                ==================================================-->

                <div class="mb-4">

                    <label
                        for="tipoExportacionProveedores"
                        class="form-label fw-semibold">

                        Información a exportar

                    </label>


                    <select
                        class="form-select"
                        id="tipoExportacionProveedores">


                        <option value="completo">

                            Reporte completo

                        </option>


                        <option value="resumen">

                            Resumen general

                        </option>


                        <option value="ranking">

                            Ranking de proveedores

                        </option>


                        <option value="productos">

                            Productos más vendidos

                        </option>


                        <option value="gastos">

                            Gastos de proveedores

                        </option>


                    </select>

                </div>



                <!--=================================================
                    FORMATO
                ==================================================-->

                <div class="mb-2">

                    <label
                        class="form-label fw-semibold">

                        Formato

                    </label>


                    <div class="row g-2">


                        <!-- EXCEL -->

                        <div class="col-6">

                            <input
                                type="radio"
                                class="btn-check"
                                name="formatoExportacionProveedores"
                                id="formatoExcelProveedores"
                                value="excel"
                                checked>


                            <label
                                class="btn btn-outline-success w-100 py-3"
                                for="formatoExcelProveedores">

                                <i
                                    class="bi bi-file-earmark-excel fs-4 d-block mb-1">
                                </i>

                                <span class="fw-semibold">

                                    Excel

                                </span>

                            </label>

                        </div>



                        <!-- CSV -->

                        <div class="col-6">

                            <input
                                type="radio"
                                class="btn-check"
                                name="formatoExportacionProveedores"
                                id="formatoCSVProveedores"
                                value="csv">


                            <label
                                class="btn btn-outline-secondary w-100 py-3"
                                for="formatoCSVProveedores">

                                <i
                                    class="bi bi-filetype-csv fs-4 d-block mb-1">
                                </i>

                                <span class="fw-semibold">

                                    CSV

                                </span>

                            </label>

                        </div>


                    </div>

                </div>


            </div>


            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-circle me-1"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnConfirmarExportacionProveedores">

                    <i class="bi bi-download me-1"></i>

                    Exportar

                </button>

            </div>


        </div>

    </div>

</div>