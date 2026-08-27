<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_exportar_estadisticas_productos.php
// Módulo: Estadísticas de Productos
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EXPORTAR ESTADÍSTICAS DE PRODUCTOS
======================================================-->

<div
    class="modal fade"
    id="modalExportarEstadisticasProductos"
    tabindex="-1"
    aria-labelledby="modalExportarEstadisticasProductosLabel"
    aria-hidden="true">

    <div
        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content border-0 shadow-lg">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header border-0 bg-light">

                <div>

                    <div class="d-flex align-items-center">

                        <div
                            class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center me-3"
                            style="width: 46px; height: 46px;">

                            <i class="bi bi-file-earmark-excel-fill fs-4"></i>

                        </div>

                        <div>

                            <h5
                                class="modal-title fw-bold mb-1"
                                id="modalExportarEstadisticasProductosLabel">

                                Exportar estadísticas

                            </h5>

                            <small class="text-muted">

                                Selecciona la información que deseas incluir en el archivo Excel.

                            </small>

                        </div>

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

            <div class="modal-body p-4">


                <!--=================================================
                    RESUMEN DE FILTROS
                ==================================================-->

                <div
                    class="alert alert-primary border-0 d-flex align-items-start mb-4">

                    <i class="bi bi-info-circle-fill me-2 mt-1"></i>

                    <div>

                        <strong>Filtros actuales</strong>

                        <div
                            id="resumenFiltrosExportacionProducto"
                            class="small mt-1">

                            Se exportarán los productos de acuerdo con los filtros seleccionados.

                        </div>

                    </div>

                </div>


                <!--=================================================
                    ALCANCE
                ==================================================-->

                <div class="mb-4">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-database me-2 text-primary"></i>

                        Alcance de la exportación

                    </h6>


                    <div class="row g-3">


                        <!-- TODOS -->

                        <div class="col-12 col-md-6">

                            <div class="form-check border rounded-3 p-3 h-100">

                                <input
                                    class="form-check-input ms-0 me-2"
                                    type="radio"
                                    name="alcanceExportacionProducto"
                                    id="exportarTodosProductos"
                                    value="todos"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarTodosProductos">

                                    <span class="fw-semibold d-block">

                                        Todos los productos filtrados

                                    </span>

                                    <small class="text-muted">

                                        Exporta todos los productos que coincidan con los filtros actuales.

                                    </small>

                                </label>

                            </div>

                        </div>


                        <!-- PÁGINA -->

                        <div class="col-12 col-md-6">

                            <div class="form-check border rounded-3 p-3 h-100">

                                <input
                                    class="form-check-input ms-0 me-2"
                                    type="radio"
                                    name="alcanceExportacionProducto"
                                    id="exportarPaginaProductos"
                                    value="pagina">

                                <label
                                    class="form-check-label"
                                    for="exportarPaginaProductos">

                                    <span class="fw-semibold d-block">

                                        Página actual

                                    </span>

                                    <small class="text-muted">

                                        Exporta únicamente los productos mostrados en la página actual.

                                    </small>

                                </label>

                            </div>

                        </div>


                    </div>

                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">

                    <span class="small text-muted">
                        Selecciona los campos que deseas incluir.
                    </span>

                    <div class="d-flex gap-2">

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            id="btnSeleccionarTodoExportacionProductos">

                            <i class="bi bi-check2-square me-1"></i>
                            Seleccionar todo

                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            id="btnDeseleccionarTodoExportacionProductos">

                            <i class="bi bi-square me-1"></i>
                            Ninguno

                        </button>

                    </div>

                </div>

                <!--=================================================
                    INFORMACIÓN DEL PRODUCTO
                ==================================================-->

                <div class="mb-4">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-box-seam me-2 text-primary"></i>

                        Información del producto

                    </h6>


                    <div class="row g-2">


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarCodigoProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarCodigoProducto">

                                    Código

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarNombreProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarNombreProducto">

                                    Nombre del producto

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarCategoriaProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarCategoriaProducto">

                                    Categoría

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarMarcaProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarMarcaProducto">

                                    Marca

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarSucursalProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarSucursalProducto">

                                    Sucursal

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarTipoProducto">

                                <label
                                    class="form-check-label"
                                    for="exportarTipoProducto">

                                    Tipo

                                </label>

                            </div>

                        </div>


                    </div>

                </div>


                <!--=================================================
                    INVENTARIO
                ==================================================-->

                <div class="mb-4">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-boxes me-2 text-warning"></i>

                        Inventario

                    </h6>


                    <div class="row g-2">


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarStockProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarStockProducto">

                                    Stock actual

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarCostoProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarCostoProducto">

                                    Costo de compra

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarPrecioProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarPrecioProducto">

                                    Precio de venta

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarValorInventario"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarValorInventario">

                                    Valor del inventario

                                </label>

                            </div>

                        </div>


                    </div>

                </div>


                <!--=================================================
                    VENTAS
                ==================================================-->

                <div class="mb-4">

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-cart-check me-2 text-success"></i>

                        Ventas

                    </h6>


                    <div class="row g-2">


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarUnidadesVendidas"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarUnidadesVendidas">

                                    Unidades vendidas

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarIngresosProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarIngresosProducto">

                                    Ingresos

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarGananciaProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarGananciaProducto">

                                    Ganancia estimada

                                </label>

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="exportarMargenProducto"
                                    checked>

                                <label
                                    class="form-check-label"
                                    for="exportarMargenProducto">

                                    Margen

                                </label>

                            </div>

                        </div>


                    </div>

                </div>


                <!--=================================================
                    OPCIONES
                ==================================================-->

                <div>

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-gear me-2 text-secondary"></i>

                        Opciones

                    </h6>


                    <div class="row g-3">


                        <div class="col-12 col-md-6">

                            <label
                                for="nombreArchivoExportacionProducto"
                                class="form-label fw-semibold">

                                Nombre del archivo

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-file-earmark-excel"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombreArchivoExportacionProducto"
                                    value="estadisticas_productos">

                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <label
                                for="nombreHojaExportacionProducto"
                                class="form-label fw-semibold">

                                Nombre de la hoja

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-table"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nombreHojaExportacionProducto"
                                    value="Productos">

                            </div>

                        </div>


                    </div>

                </div>


            </div>


            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer bg-light border-0">

                <button
                    type="button"
                    class="btn btn-light border"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-success"
                    id="btnConfirmarExportacionProductos">

                    <i class="bi bi-file-earmark-excel-fill me-1"></i>

                    Exportar a Excel

                </button>

            </div>


        </div>

    </div>

</div>