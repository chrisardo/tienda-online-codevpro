<!--=====================================================
    MODAL REGISTRAR PAGO A EMPLEADO
======================================================-->

<div
    class="modal fade"
    id="modalRegistrarPago"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">


            <!-- HEADER -->

            <div class="modal-header">

                <div>

                    <h5 class="modal-title fw-bold">

                        <i class="bi bi-wallet2 text-primary me-2"></i>

                        Registrar Pago a Empleado

                    </h5>

                    <small class="text-muted">

                        Registra el pago correspondiente al periodo del empleado.

                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <!-- FORMULARIO -->

            <form
                id="formRegistrarPago"
                autocomplete="off">

                <div class="modal-body">


                    <div class="row g-3">


                        <!-- EMPLEADO -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Empleado
                                <span class="text-danger">*</span>

                            </label>

                            <select
                                class="form-select"
                                id="pagoEmpleado"
                                name="id_empleado"
                                required>

                                <option value="">
                                    Seleccione un empleado
                                </option>

                            </select>

                        </div>


                        <!-- SUELDO -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Sueldo asignado

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="pagoSueldo"
                                    readonly
                                    value="0.00">

                            </div>

                            <input
                                type="hidden"
                                id="pagoSueldoId"
                                name="id_sueldo">

                            <small
                                class="text-muted"
                                id="pagoTipoPago">

                            </small>

                        </div>


                        <!-- PERIODO INICIO -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Periodo Inicio
                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="pagoPeriodoInicio"
                                name="periodo_inicio"
                                required>

                        </div>


                        <!-- PERIODO FIN -->

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">

                                Periodo Fin
                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="pagoPeriodoFin"
                                name="periodo_fin"
                                required>

                        </div>


                        <div class="col-12">

                            <hr>

                            <h6 class="fw-bold">

                                Detalle del pago

                            </h6>

                        </div>


                        <!-- MONTO BASE -->

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">

                                Monto Base

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="montoBase"
                                    name="monto_base"
                                    step="0.01"
                                    min="0"
                                    value="0.00"
                                    readonly>

                            </div>

                        </div>


                        <!-- BONIFICACIONES -->

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">

                                Bonificaciones

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="bonificaciones"
                                    name="bonificaciones"
                                    step="0.01"
                                    min="0"
                                    value="0.00">

                            </div>

                        </div>


                        <!-- DESCUENTOS -->

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">

                                Descuentos

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="descuentos"
                                    name="descuentos"
                                    step="0.01"
                                    min="0"
                                    value="0.00">

                            </div>

                        </div>


                        <!-- TOTAL -->

                        <div class="col-12">

                            <div class="pago-total-box">

                                <div>

                                    <small class="text-muted">

                                        MONTO TOTAL

                                    </small>

                                    <h3
                                        class="fw-bold mb-0"
                                        id="montoTotalTexto">

                                        S/ 0.00

                                    </h3>

                                </div>


                                <input
                                    type="hidden"
                                    id="montoTotal"
                                    name="monto_total"
                                    value="0.00">

                                <i class="bi bi-cash-coin"></i>

                            </div>

                        </div>


                        <!-- FECHA DE PAGO -->

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">

                                Fecha de Pago
                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="fechaPago"
                                name="fecha_pago"
                                required>

                        </div>


                        <!-- CUENTA BANCARIA -->

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">

                                Cuenta Bancaria

                            </label>

                            <select
                                id="cuentaBancaria"
                                name="id_cuenta_bancaria"
                                class="form-select"
                                required>

                                <option value="">
                                    Seleccione una cuenta bancaria
                                </option>

                            </select>

                        </div>


                        <!-- MÉTODO DE PAGO -->

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">

                                Método de Pago
                                <span class="text-danger">*</span>

                            </label>

                            <select
                                class="form-select"
                                id="metodoPago"
                                name="id_metodo_pago"
                                required>

                                <option value="">
                                    Seleccione un método
                                </option>

                            </select>

                        </div>


                        <!-- ESTADO -->

                        <div class="col-md-4">

                            <label class="form-label fw-semibold">

                                Estado

                            </label>

                            <select
                                class="form-select"
                                name="estado"
                                id="estadoPago">

                                <option value="PENDIENTE">
                                    Pendiente
                                </option>

                                <option value="PAGADO">
                                    Pagado
                                </option>

                                <option value="ANULADO">
                                    Anulado
                                </option>

                            </select>

                        </div>


                        <!-- OBSERVACIÓN -->

                        <div class="col-md-8">

                            <label class="form-label fw-semibold">

                                Observación

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="observacion"
                                maxlength="255"
                                placeholder="Observación del pago">

                        </div>


                    </div>

                </div>


                <!-- FOOTER -->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarPago">

                        <i class="bi bi-check-circle me-1"></i>

                        Registrar Pago

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>