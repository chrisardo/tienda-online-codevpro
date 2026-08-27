<!--=====================================
MODAL EXPORTAR EXCEL
======================================-->

<div
    class="modal fade"
    id="modalExportarVentasExcel"
    tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-success text-white">

                <h5 class="modal-title">

                    <i class="bi bi-file-earmark-excel-fill"></i>

                    Exportar Ventas

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="alert alert-info">

                    Selecciona los campos que deseas incluir.

                </div>

                <div class="row">

                    <div class="col-md-6">

                        <h6 class="fw-bold">

                            Datos Generales

                        </h6>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="id_ticket_ventas"
                                checked>
                            <label class="form-check-label">
                                ID Venta
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="fecha_venta"
                                checked>
                            <label class="form-check-label">
                                Fecha Venta
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="tipo_comprobante"
                                checked>
                            <label class="form-check-label">
                                Comprobante
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="total_venta"
                                checked>
                            <label class="form-check-label">
                                Total Venta
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="estado_venta"
                                checked>
                            <label class="form-check-label">
                                Estado Venta
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="estado_envio"
                                checked>
                            <label class="form-check-label">
                                Estado Envío
                            </label>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <h6 class="fw-bold">

                            Cliente y Pago

                        </h6>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="cliente"
                                checked>
                            <label class="form-check-label">
                                Cliente
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="dni_o_ruc">
                            <label class="form-check-label">
                                DNI / RUC
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="celular">
                            <label class="form-check-label">
                                Celular
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="metodo_pago"
                                checked>
                            <label class="form-check-label">
                                Método Pago
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input campoExcel"
                                type="checkbox"
                                value="empleado">
                            <label class="form-check-label">
                                Empleado
                            </label>
                        </div>

                    </div>

                </div>

                <hr>

                <div class="form-check form-switch">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="exportarProductos">

                    <label class="form-check-label">

                        Incluir productos vendidos

                    </label>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Cancelar

                </button>

                <button
                    class="btn btn-success"
                    id="btnGenerarExcelVentas">

                    <i class="bi bi-download"></i>

                    Generar exportacion

                </button>

            </div>

        </div>

    </div>

</div>