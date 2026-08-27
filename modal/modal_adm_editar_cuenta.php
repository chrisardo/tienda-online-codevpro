<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_adm_editar_cuenta.php
// Módulo: Cuentas Bancarias
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EDITAR CUENTA BANCARIA
======================================================-->

<div
    class="modal fade"
    id="modalEditarCuenta"
    tabindex="-1"
    aria-labelledby="modalEditarCuentaLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">


            <!--=================================================
                CABECERA
            ==================================================-->

            <div class="modal-header border-0">

                <div class="d-flex align-items-center">

                    <div class="modal-cuenta-icono me-3">

                        <i class="bi bi-pencil-square"></i>

                    </div>

                    <div>

                        <h5
                            class="modal-title mb-1"
                            id="modalEditarCuentaLabel">

                            Editar cuenta bancaria

                        </h5>

                        <p class="text-muted small mb-0">

                            Actualiza los datos de la cuenta bancaria.

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
                FORMULARIO
            ==================================================-->

            <form
                id="formEditarCuenta"
                autocomplete="off">

                <!--=================================================
                    CUERPO
                ==================================================-->

                <div class="modal-body">


                    <!--=================================================
                        ID CUENTA
                    ==================================================-->

                    <input
                        type="hidden"
                        name="id_cuenta_bancaria"
                        id="idCuentaBancariaEditar"
                        value="">


                    <!--=================================================
                        NOMBRE DE LA CUENTA
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="nombreCuentaEditar"
                            class="form-label fw-semibold">

                            Nombre de la cuenta

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-bank"></i>

                            </span>


                            <input
                                type="text"
                                class="form-control"
                                id="nombreCuentaEditar"
                                name="nombre"
                                placeholder="Ej. Cuenta BCP"
                                maxlength="100"
                                required>

                        </div>


                        <div class="form-text">

                            Ingresa el nombre con el que identificarás
                            esta cuenta.

                        </div>

                    </div>


                    <!--=================================================
                        BALANCE
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="balanceCuentaEditar"
                            class="form-label fw-semibold">

                            Balance

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">

                                S/.

                            </span>


                            <input
                                type="number"
                                class="form-control"
                                id="balanceCuentaEditar"
                                name="balance"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                required>

                        </div>


                        <div class="form-text">

                            Puedes ingresar el saldo actual de la cuenta.

                        </div>

                    </div>


                    <!--=================================================
                        ESTADO
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="estadoCuentaEditar"
                            class="form-label fw-semibold">

                            Estado de la cuenta

                        </label>


                        <select
                            class="form-select"
                            id="estadoCuentaEditar"
                            name="Eliminado">

                            <option value="0">
                                Activa
                            </option>

                            <option value="1">
                                Inactiva
                            </option>

                        </select>


                        <div class="form-text">

                            Una cuenta inactiva no estará disponible
                            como cuenta activa.

                        </div>

                    </div>


                    <!--=================================================
                        MENSAJE
                    ==================================================-->

                    <div
                        id="mensajeEditarCuenta"
                        class="alert mt-3"
                        style="display: none;"
                        role="alert">
                    </div>

                </div>


                <!--=================================================
                    FOOTER
                ==================================================-->

                <div class="modal-footer border-0">


                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarCambiosCuenta">

                        <i class="bi bi-check-circle me-1"></i>

                        Guardar cambios

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>