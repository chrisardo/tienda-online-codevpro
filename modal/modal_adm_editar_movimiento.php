<!--=====================================================
    MODAL: EDITAR MOVIMIENTO
======================================================-->

<div
    class="modal fade"
    id="modalEditarMovimiento"
    tabindex="-1"
    aria-labelledby="modalEditarMovimientoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content movimiento-modal">

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title"
                        id="modalEditarMovimientoLabel">

                        <i class="bi bi-pencil-square me-2"></i>

                        Editar movimiento

                    </h5>

                    <p class="modal-subtitle mb-0">

                        Modifica la información del movimiento.

                    </p>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <div class="modal-body">

                <form id="formEditarMovimiento">

                    <input
                        type="hidden"
                        id="editarIdDeposito"
                        name="id_deposito">


                    <div class="row g-3">


                        <!-- TIPO -->

                        <div class="col-md-6">

                            <label
                                for="editarTipo"
                                class="form-label">

                                Tipo

                            </label>

                            <select
                                class="form-select"
                                id="editarTipo"
                                name="tipo">

                                <option value="INGRESO">
                                    Ingreso
                                </option>

                                <option value="GASTO">
                                    Gasto
                                </option>

                            </select>

                        </div>


                        <!-- CUENTA -->

                        <div class="col-md-6">

                            <label
                                for="editarCuenta"
                                class="form-label">

                                Cuenta bancaria

                            </label>

                            <select
                                class="form-select"
                                id="editarCuenta"
                                name="id_cuenta_bancaria">

                                <option value="">
                                    Seleccionar cuenta
                                </option>

                            </select>

                        </div>


                        <!-- CATEGORÍA -->

                        <div class="col-md-6">

                            <label
                                for="editarCategoria"
                                class="form-label">

                                Categoría

                            </label>

                            <select
                                class="form-select"
                                id="editarCategoria"
                                name="id_categoria">

                                <option value="">
                                    Seleccionar categoría
                                </option>

                            </select>

                        </div>


                        <!-- PROVEEDOR -->

                        <div class="col-md-6">

                            <label
                                for="editarProveedor"
                                class="form-label">

                                Proveedor

                            </label>

                            <select
                                class="form-select"
                                id="editarProveedor"
                                name="id_proveedor">

                                <option value="">
                                    Seleccionar proveedor
                                </option>

                            </select>

                        </div>


                        <!-- MÉTODO -->

                        <div class="col-md-6">

                            <label
                                for="editarMetodoPago"
                                class="form-label">

                                Método de pago

                            </label>

                            <select
                                class="form-select"
                                id="editarMetodoPago"
                                name="id_metodo_pago">

                                <option value="">
                                    Seleccionar método
                                </option>

                            </select>

                        </div>


                        <!-- FECHA -->

                        <div class="col-md-6">

                            <label
                                for="editarFecha"
                                class="form-label">

                                Fecha

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="editarFecha"
                                name="fecha"
                                placeholder="dd/mm/aaaa">

                        </div>


                        <!-- CONCEPTO -->

                        <div class="col-12">

                            <label
                                for="editarConcepto"
                                class="form-label">

                                Concepto

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="editarConcepto"
                                name="concepto">

                        </div>


                        <!-- MONTO -->

                        <div class="col-md-6">

                            <label
                                for="editarMonto"
                                class="form-label">

                                Monto

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    id="editarMonto"
                                    name="monto_pago"
                                    min="0.01"
                                    step="0.01">

                            </div>

                        </div>


                        <!-- DESCRIPCIÓN -->

                        <div class="col-12">

                            <label
                                for="editarDescripcion"
                                class="form-label">

                                Descripción

                            </label>

                            <textarea
                                class="form-control"
                                id="editarDescripcion"
                                name="descripcion"
                                rows="3"></textarea>

                        </div>

                    </div>

                </form>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">

                    Cancelar

                </button>

                <button
                    type="submit"
                    form="formEditarMovimiento"
                    class="btn btn-primary">

                    <i class="bi bi-save me-2"></i>

                    Guardar cambios

                </button>

            </div>

        </div>

    </div>

</div>