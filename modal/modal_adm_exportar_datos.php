<!-- Toda esta parte pertenece a modal/modal_adm_exportar_datos.php-->
<div class="modal fade"
    id="modalExportar"
    tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">

                <h5 class="modal-title">

                    <i class="bi bi-download me-2"></i>

                    Exportar Datos

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>
            <div class="alert alert-primary mt-4">

                <div class="fw-bold">

                    Resumen de exportación
                </div>

                <small id="resumenExportacion">

                    Se exportarán todos los productos.
                </small>

            </div>
            <div class="modal-body">

                <input
                    type="hidden"
                    id="tipoExportacion">

                <h6 class="fw-bold mb-3">

                    ¿Qué deseas exportar?

                </h6>

                <div class="form-check mb-2">

                    <input
                        class="form-check-input"
                        type="radio"
                        name="exportScope"
                        value="todos"
                        checked>

                    <label class="form-check-label">

                        Todos los productos

                    </label>

                </div>

                <div class="form-check mb-2">

                    <input
                        class="form-check-input"
                        type="radio"
                        name="exportScope"
                        value="filtrados">

                    <label class="form-check-label">

                        Productos filtrados actualmente

                    </label>

                </div>

                <div class="form-check mb-4">

                    <input
                        class="form-check-input"
                        type="radio"
                        name="exportScope"
                        value="seleccionados">

                    <label class="form-check-label">

                        Solo seleccionados

                    </label>

                </div>

                <hr>

                <h6 class="fw-bold mb-3">
                    Campos a incluir
                </h6>

                <div class="row">

                    <div class="col-md-6">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expCodigo" checked>
                            <label class="form-check-label">Código</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expNombre" checked>
                            <label class="form-check-label">Nombre</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expTipo" checked>
                            <label class="form-check-label">Tipo</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expCategoria" checked>
                            <label class="form-check-label">Categoría</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expMarca" checked>
                            <label class="form-check-label">Marca</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expProveedor" checked>
                            <label class="form-check-label">Proveedor</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expSucursal" checked>
                            <label class="form-check-label">Sucursal</label>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expPrecio" checked>
                            <label class="form-check-label">Precio</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expPrecioAnterior">
                            <label class="form-check-label">Precio Anterior</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expCostoCompra">
                            <label class="form-check-label">Costo Compra</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expStock" checked>
                            <label class="form-check-label">Stock</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expVendidos" checked>
                            <label class="form-check-label">Vendidos</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expOferta">
                            <label class="form-check-label">Oferta</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expDestacado">
                            <label class="form-check-label">Destacado</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expNuevo">
                            <label class="form-check-label">Nuevo</label>
                        </div>

                    </div>

                </div>

                <hr>

                <div class="row">

                    <div class="col-md-6">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expDescuento">
                            <label class="form-check-label">Descuento (%)</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expEnvioGratis">
                            <label class="form-check-label">Envío Gratis</label>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expFechaRegistro">
                            <label class="form-check-label">Fecha Registro</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="expFechaActualizado">
                            <label class="form-check-label">Fecha Actualización</label>
                        </div>

                    </div>

                </div>

                <hr>

                <div class="form-check">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="expDescripcion">

                    <label class="form-check-label">

                        Descripción completa

                    </label>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">

                    Cancelar

                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnConfirmarExportacion">

                    <i class="bi bi-download me-1"></i>

                    Exportar

                </button>

            </div>

        </div>

    </div>

</div>