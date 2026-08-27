<!--=====================================================
    MODAL: NUEVO MOVIMIENTO
======================================================-->

<div
    class="modal fade"
    id="modalNuevoMovimiento"
    tabindex="-1"
    aria-labelledby="modalNuevoMovimientoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content movimiento-modal">


            <!-- HEADER -->

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title"
                        id="modalNuevoMovimientoLabel">

                        <i class="bi bi-plus-circle me-2"></i>

                        Nuevo movimiento

                    </h5>

                    <p class="modal-subtitle mb-0">

                        Registra un nuevo ingreso o gasto.

                    </p>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!-- BODY -->

            <div class="modal-body">

                <form id="formNuevoMovimiento">


                    <!-- TIPO -->

                    <div class="tipo-movimiento-selector mb-4">

                        <label class="form-label">

                            Tipo de movimiento

                        </label>


                        <div class="row g-3">


                            <div class="col-md-6">

                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="tipoMovimiento"
                                    id="tipoIngreso"
                                    value="INGRESO"
                                    autocomplete="off"
                                    checked>

                                <label
                                    class="tipo-option tipo-ingreso"
                                    for="tipoIngreso">

                                    <span class="tipo-option-icon">

                                        <i class="bi bi-arrow-down-left"></i>

                                    </span>

                                    <span>

                                        <strong>
                                            Ingreso
                                        </strong>

                                        <small>
                                            Entrada de dinero
                                        </small>

                                    </span>

                                </label>

                            </div>


                            <div class="col-md-6">

                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="tipoMovimiento"
                                    id="tipoGasto"
                                    value="GASTO"
                                    autocomplete="off">

                                <label
                                    class="tipo-option tipo-gasto"
                                    for="tipoGasto">

                                    <span class="tipo-option-icon">

                                        <i class="bi bi-arrow-up-right"></i>

                                    </span>

                                    <span>

                                        <strong>
                                            Gasto
                                        </strong>

                                        <small>
                                            Salida de dinero
                                        </small>

                                    </span>

                                </label>

                            </div>

                        </div>

                    </div>


                    <div class="row g-3">


                        <!-- CUENTA -->

                        <div class="col-md-6">

                            <label
                                for="idCuentaBancaria"
                                class="form-label">

                                Cuenta bancaria
                                <span class="text-danger">*</span>

                            </label>

                            <select
                                class="form-select"
                                id="idCuentaBancaria"
                                name="id_cuenta_bancaria"
                                required>

                                <option value="">
                                    Seleccionar cuenta
                                </option>

                            </select>

                        </div>


                        <!-- CATEGORÍA -->

                        <div class="col-md-6">

                            <label
                                for="idCategoria"
                                class="form-label">

                                Categoría
                                <span class="text-danger">*</span>

                            </label>

                            <select
                                class="form-select"
                                id="idCategoria"
                                name="id_categoria"
                                required>

                                <option value="">
                                    Seleccionar categoría
                                </option>

                            </select>

                        </div>


                        <!-- PROVEEDOR -->

                        <div class="col-md-6">

                            <label
                                for="idProveedor"
                                class="form-label">

                                Proveedor

                            </label>

                            <select
                                class="form-select"
                                id="idProveedor"
                                name="id_proveedor">

                                <option value="">
                                    Seleccionar proveedor
                                </option>

                            </select>

                            <div class="form-text">

                                Opcional para este movimiento.

                            </div>

                        </div>


                        <!-- MÉTODO DE PAGO -->

                        <div class="col-md-6">

                            <label
                                for="idMetodoPago"
                                class="form-label">

                                Método de pago
                                <span class="text-danger">*</span>

                            </label>

                            <select
                                class="form-select"
                                id="idMetodoPago"
                                name="id_metodo_pago"
                                required>

                                <option value="">
                                    Seleccionar método
                                </option>

                            </select>

                        </div>


                        <!-- FECHA -->

                        <div class="col-md-6">

                            <label
                                for="fechaMovimiento"
                                class="form-label">

                                Fecha
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i class="bi bi-calendar3"></i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="fechaMovimiento"
                                    name="fecha"
                                    placeholder="dd/mm/aaaa"
                                    required>

                            </div>

                        </div>


                        <!-- MONTO -->

                        <div class="col-md-6">

                            <label
                                for="montoMovimiento"
                                class="form-label">

                                Monto
                                <span class="text-danger">*</span>

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="montoMovimiento"
                                    name="monto_pago"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="0.00"
                                    required>

                            </div>

                        </div>


                        <!-- CONCEPTO -->

                        <div class="col-12">

                            <label
                                for="conceptoMovimiento"
                                class="form-label">

                                Concepto
                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="conceptoMovimiento"
                                name="concepto"
                                maxlength="255"
                                placeholder="Ej. Compra de suministros"
                                required>

                        </div>


                        <!-- DESCRIPCIÓN -->

                        <div class="col-12">

                            <label
                                for="descripcionMovimiento"
                                class="form-label">

                                Descripción

                            </label>

                            <textarea
                                class="form-control"
                                id="descripcionMovimiento"
                                name="descripcion"
                                rows="3"
                                placeholder="Agrega información adicional sobre el movimiento..."></textarea>

                        </div>

                    </div>

                </form>

            </div>


            <!-- FOOTER -->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">

                    Cancelar

                </button>

                <button
                    type="submit"
                    form="formNuevoMovimiento"
                    class="btn btn-primary">

                    <i class="bi bi-check-lg me-2"></i>

                    Guardar movimiento

                </button>

            </div>

        </div>

    </div>

</div>