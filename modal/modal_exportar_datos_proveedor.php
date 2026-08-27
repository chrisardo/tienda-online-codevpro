<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_exportar_datos_proveedor.php
// Módulo: Lista de Proveedores
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
MODAL EXPORTAR DATOS DEL PROVEEDOR
======================================================-->

<div class="modal fade"
    id="modalExportarDatosProveedor"
    tabindex="-1"
    aria-labelledby="modalExportarDatosProveedorLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content border-0 shadow">


            <!--=================================================
            HEADER
            ==================================================-->

            <div class="modal-header">

                <div>

                    <h5 class="modal-title fw-bold"
                        id="modalExportarDatosProveedorLabel">

                        <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>

                        Exportar datos de proveedores

                    </h5>

                    <small class="text-muted">

                        Selecciona la información que deseas incluir
                        en el archivo Excel.

                    </small>

                </div>


                <button type="button"
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
                INFORMACIÓN
                ==================================================-->

                <div class="alert alert-info d-flex align-items-start gap-2">

                    <i class="bi bi-info-circle-fill mt-1"></i>

                    <div class="small">

                        El archivo se generará en formato
                        <strong>Excel (.xlsx)</strong>.

                        La información relacionada con los proveedores
                        se organizará en diferentes hojas para mantener
                        correctamente las relaciones con productos,
                        ubicaciones y movimientos.

                    </div>

                </div>


                <!--=================================================
                CONTROLES GENERALES
                ==================================================-->

                <div class="d-flex
                            flex-wrap
                            justify-content-between
                            align-items-center
                            gap-2
                            mb-3">

                    <div>

                        <button type="button"
                            class="btn btn-sm btn-outline-primary"
                            id="btnSeleccionarTodoExportacionProveedor">

                            <i class="bi bi-check2-square me-1"></i>

                            Seleccionar todo

                        </button>


                        <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            id="btnDeseleccionarTodoExportacionProveedor">

                            <i class="bi bi-square me-1"></i>

                            Deseleccionar todo

                        </button>

                    </div>


                    <span class="small text-muted">

                        <span id="contadorExportacionProveedor">
                            0
                        </span>

                        opciones seleccionadas

                    </span>

                </div>


                <div class="row g-3">


                    <!--=================================================
                    DATOS DEL PROVEEDOR
                    ==================================================-->

                    <div class="col-12">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div class="form-check">

                                    <input
                                        class="form-check-input categoria-exportacion-proveedor"
                                        type="checkbox"
                                        id="exportarProveedorCategoria"
                                        checked>

                                    <label
                                        class="form-check-label fw-bold"
                                        for="exportarProveedorCategoria">

                                        <i class="bi bi-person-vcard me-2"></i>

                                        Datos del proveedor

                                    </label>

                                </div>

                            </div>


                            <div class="card-body">

                                <div class="row g-2">


                                    <!-- DATOS GENERALES -->

                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input opcion-exportacion-proveedor"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="proveedor_datos"
                                                id="exportProveedorDatos"
                                                checked>

                                            <label
                                                class="form-check-label"
                                                for="exportProveedorDatos">

                                                Datos generales

                                            </label>

                                        </div>

                                    </div>


                                    <!-- CONTACTO -->

                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input opcion-exportacion-proveedor"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="proveedor_contacto"
                                                id="exportProveedorContacto"
                                                checked>

                                            <label
                                                class="form-check-label"
                                                for="exportProveedorContacto">

                                                Contacto

                                            </label>

                                        </div>

                                    </div>


                                    <!-- ESTADO -->

                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input opcion-exportacion-proveedor"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="proveedor_estado"
                                                id="exportProveedorEstado"
                                                checked>

                                            <label
                                                class="form-check-label"
                                                for="exportProveedorEstado">

                                                Estado del proveedor

                                            </label>

                                        </div>

                                    </div>


                                    <!-- FECHAS -->

                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input opcion-exportacion-proveedor"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="proveedor_fechas"
                                                id="exportProveedorFechas"
                                                checked>

                                            <label
                                                class="form-check-label"
                                                for="exportProveedorFechas">

                                                Fechas de registro y actualización

                                            </label>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                    UBICACIÓN
                    ==================================================-->

                    <div class="col-12">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div class="form-check">

                                    <input
                                        class="form-check-input categoria-exportacion-proveedor"
                                        type="checkbox"
                                        id="exportarUbicacionProveedorCategoria"
                                        checked>

                                    <label
                                        class="form-check-label fw-bold"
                                        for="exportarUbicacionProveedorCategoria">

                                        <i class="bi bi-geo-alt-fill me-2"></i>

                                        Ubicación

                                    </label>

                                </div>

                            </div>


                            <div class="card-body">

                                <div class="form-check">

                                    <input
                                        class="form-check-input opcion-exportacion-proveedor"
                                        type="checkbox"
                                        name="exportar[]"
                                        value="ubicacion"
                                        id="exportProveedorUbicacion"
                                        checked>

                                    <label
                                        class="form-check-label"
                                        for="exportProveedorUbicacion">

                                        País, departamento, provincia,
                                        distrito y dirección

                                    </label>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                    INFORMACIÓN COMERCIAL
                    ==================================================-->

                    <div class="col-12">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div class="form-check">

                                    <input
                                        class="form-check-input categoria-exportacion-proveedor"
                                        type="checkbox"
                                        id="exportarComercialProveedorCategoria">

                                    <label
                                        class="form-check-label fw-bold"
                                        for="exportarComercialProveedorCategoria">

                                        <i class="bi bi-box-seam-fill me-2"></i>

                                        Información comercial

                                    </label>

                                </div>

                            </div>


                            <div class="card-body">

                                <div class="row g-2">


                                    <!-- PRODUCTOS -->

                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input opcion-exportacion-proveedor"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="productos"
                                                id="exportProveedorProductos">

                                            <label
                                                class="form-check-label"
                                                for="exportProveedorProductos">

                                                Productos asociados

                                            </label>

                                        </div>

                                    </div>


                                    <!-- RESUMEN -->

                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input
                                                class="form-check-input opcion-exportacion-proveedor"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="resumen_productos"
                                                id="exportProveedorResumenProductos">

                                            <label
                                                class="form-check-label"
                                                for="exportProveedorResumenProductos">

                                                Resumen de productos

                                            </label>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                    ACTIVIDAD / GASTOS
                    ==================================================-->

                    <div class="col-12">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div class="form-check">

                                    <input
                                        class="form-check-input categoria-exportacion-proveedor"
                                        type="checkbox"
                                        id="exportarActividadProveedorCategoria">

                                    <label
                                        class="form-check-label fw-bold"
                                        for="exportarActividadProveedorCategoria">

                                        <i class="bi bi-bar-chart-line-fill me-2"></i>

                                        Actividad

                                    </label>

                                </div>

                            </div>


                            <div class="card-body">

                                <div class="form-check">

                                    <input
                                        class="form-check-input opcion-exportacion-proveedor"
                                        type="checkbox"
                                        name="exportar[]"
                                        value="gastos"
                                        id="exportProveedorGastos">

                                    <label
                                        class="form-check-label"
                                        for="exportProveedorGastos">

                                        Gastos y movimientos asociados

                                    </label>

                                </div>

                            </div>

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

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-success"
                    id="btnEjecutarExportacionProveedor">

                    <i class="bi bi-file-earmark-excel-fill me-2"></i>

                    Exportar a Excel

                </button>

            </div>

        </div>

    </div>

</div>