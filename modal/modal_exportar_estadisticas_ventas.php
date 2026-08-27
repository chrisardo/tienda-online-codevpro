<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_exportar_estadisticas_ventas.php
// Módulo: Estadísticas de Ventas
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EXPORTAR ESTADÍSTICAS DE VENTAS
======================================================-->

<div
    class="modal fade"
    id="modalExportarEstadisticasVentas"
    tabindex="-1"
    aria-labelledby="modalExportarEstadisticasVentasLabel"
    aria-hidden="true">


    <div class="modal-dialog modal-dialog-centered">


        <div class="modal-content modal-exportar-estadisticas">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header modal-exportar-header">


                <div class="modal-exportar-header-info">


                    <div class="modal-exportar-icon">

                        <i class="bi bi-file-earmark-excel"></i>

                    </div>


                    <div>

                        <h5
                            class="modal-title"
                            id="modalExportarEstadisticasVentasLabel">

                            Exportar estadísticas

                        </h5>


                        <p class="modal-exportar-subtitle mb-0">

                            Genera un archivo Excel con la información
                            de tus ventas.

                        </p>

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

            <div class="modal-body">
                <!--=================================================
                    OPCIONES DE EXPORTACIÓN
                ==================================================-->

                <div class="exportar-opciones mt-4">


                    <label class="form-label exportar-label">

                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>

                        Información a exportar

                    </label>



                    <!-- RESUMEN -->

                    <div class="exportar-opcion">


                        <div class="form-check">


                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="exportarResumen"
                                name="exportarResumen"
                                value="1"
                                checked>


                            <label
                                class="form-check-label"
                                for="exportarResumen">

                                <span class="exportar-opcion-icon">

                                    <i class="bi bi-bar-chart-line"></i>

                                </span>


                                <span>

                                    <strong>

                                        Resumen de estadísticas

                                    </strong>


                                    <small>

                                        Indicadores generales de ventas,
                                        ingresos, productos, utilidad y
                                        clientes.

                                    </small>

                                </span>

                            </label>


                        </div>


                    </div>



                    <!-- DETALLE -->

                    <div class="exportar-opcion">


                        <div class="form-check">


                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="exportarDetalle"
                                name="exportarDetalle"
                                value="1"
                                checked>


                            <label
                                class="form-check-label"
                                for="exportarDetalle">

                                <span class="exportar-opcion-icon">

                                    <i class="bi bi-receipt"></i>

                                </span>


                                <span>

                                    <strong>

                                        Detalle de ventas

                                    </strong>


                                    <small>

                                        Fecha, comprobante, cliente,
                                        empleado, método de pago,
                                        productos, total y estado.

                                    </small>

                                </span>

                            </label>


                        </div>


                    </div>



                    <!-- GRÁFICOS -->

                    <div class="exportar-opcion">


                        <div class="form-check">


                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="exportarGraficos"
                                name="exportarGraficos"
                                value="1"
                                checked>


                            <label
                                class="form-check-label"
                                for="exportarGraficos">

                                <span class="exportar-opcion-icon">

                                    <i class="bi bi-pie-chart"></i>

                                </span>


                                <span>

                                    <strong>

                                        Datos de gráficos y rankings

                                    </strong>


                                    <small>

                                        Evolución de ventas, métodos de
                                        pago, categorías, sucursales,
                                        productos y clientes.

                                    </small>

                                </span>

                            </label>


                        </div>


                    </div>


                </div>



                <!--=================================================
                    PERÍODO
                ==================================================-->

                <div class="exportar-periodo mt-4">


                    <div class="exportar-periodo-header">


                        <i class="bi bi-calendar3"></i>


                        <span>

                            Período seleccionado

                        </span>


                    </div>


                    <div
                        class="exportar-periodo-texto"
                        id="periodoExportacion">

                        Todos los registros

                    </div>


                </div>



                <!--=================================================
                    FORMATO
                ==================================================-->

                <div class="exportar-formato mt-3">


                    <div class="exportar-formato-icon">

                        <i class="bi bi-file-earmark-excel"></i>

                    </div>


                    <div>

                        <span class="exportar-formato-titulo">

                            Formato de archivo

                        </span>


                        <strong>

                            Microsoft Excel (.xlsx)

                        </strong>

                    </div>


                </div>


            </div>



            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer modal-exportar-footer">


                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnExportarEstadisticasVentas">

                    <i class="bi bi-file-earmark-excel me-2"></i>

                    Exportar Excel

                </button>


            </div>


        </div>

    </div>

</div>