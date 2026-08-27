<?php
//=====================================================
// CoDevPro Technology
// Archivo: modal/modal_editar_pago_empleado.php
// Módulo: Pagos a Empleados
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL EDITAR PAGO DEL EMPLEADO
======================================================-->

<div class="modal fade"
    id="modalEditarPagoEmpleado"
    tabindex="-1"
    aria-labelledby="modalEditarPagoEmpleadoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header bg-primary text-white">

                <div>

                    <h5 class="modal-title mb-1"
                        id="modalEditarPagoEmpleadoLabel">

                        <i class="bi bi-pencil-square me-2"></i>

                        Editar Pago de Empleado

                    </h5>

                    <small class="opacity-75">

                        Modifique la información del pago registrado.

                    </small>

                </div>


                <button type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>


            <!--=================================================
                FORMULARIO
            ==================================================-->

            <form id="formEditarPagoEmpleado"
                autocomplete="off">

                <div class="modal-body">


                    <!--=================================================
                        ID OCULTO
                    ==================================================-->

                    <input type="hidden"
                        id="editar_id_pago"
                        name="id_pago">


                    <!--=================================================
                        INFORMACIÓN DEL EMPLEADO
                    ==================================================-->

                    <div class="card border-0 bg-light mb-4">

                        <div class="card-body">

                            <div class="row g-3">

                                <div class="col-md-8">

                                    <label class="form-label fw-semibold">

                                        <i class="bi bi-person-badge me-1"></i>

                                        Empleado

                                    </label>

                                    <input type="text"
                                        class="form-control"
                                        id="editar_empleado"
                                        name="empleado"
                                        readonly>

                                </div>


                                <div class="col-md-4">

                                    <label class="form-label fw-semibold">

                                        Estado

                                    </label>

                                    <select
                                        class="form-select"
                                        id="editar_estado"
                                        name="estado"
                                        required>

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

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                        PERÍODO
                    ==================================================-->

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-calendar-range text-primary me-2"></i>

                        Período del pago

                    </h6>


                    <div class="row g-3 mb-4">


                        <div class="col-md-6">

                            <label
                                for="editar_periodo_inicio"
                                class="form-label">

                                Fecha de inicio

                            </label>

                            <input type="date"
                                class="form-control"
                                id="editar_periodo_inicio"
                                name="periodo_inicio"
                                required>

                        </div>


                        <div class="col-md-6">

                            <label
                                for="editar_periodo_fin"
                                class="form-label">

                                Fecha de fin

                            </label>

                            <input type="date"
                                class="form-control"
                                id="editar_periodo_fin"
                                name="periodo_fin"
                                required>

                        </div>

                    </div>


                    <!--=================================================
                        CONCEPTOS DEL PAGO
                    ==================================================-->

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-cash-stack text-success me-2"></i>

                        Detalle del pago

                    </h6>


                    <div class="row g-3 mb-4">


                        <!-- MONTO BASE -->

                        <div class="col-md-4">

                            <label
                                for="editar_monto_base"
                                class="form-label">

                                Monto base

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    S/.

                                </span>

                                <input type="number"
                                    class="form-control"
                                    id="editar_monto_base"
                                    name="monto_base"
                                    min="0"
                                    step="0.01"
                                    required>

                            </div>

                        </div>


                        <!-- BONIFICACIONES -->

                        <div class="col-md-4">

                            <label
                                for="editar_bonificaciones"
                                class="form-label">

                                Bonificaciones

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    S/.

                                </span>

                                <input type="number"
                                    class="form-control"
                                    id="editar_bonificaciones"
                                    name="bonificaciones"
                                    min="0"
                                    step="0.01"
                                    value="0">

                            </div>

                        </div>


                        <!-- DESCUENTOS -->

                        <div class="col-md-4">

                            <label
                                for="editar_descuentos"
                                class="form-label">

                                Descuentos

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    S/.

                                </span>

                                <input type="number"
                                    class="form-control"
                                    id="editar_descuentos"
                                    name="descuentos"
                                    min="0"
                                    step="0.01"
                                    value="0">

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                        TOTAL
                    ==================================================-->

                    <div class="card border-success mb-4">

                        <div class="card-body">

                            <div class="row align-items-center">

                                <div class="col-md-7">

                                    <div class="text-muted small">

                                        MONTO TOTAL DEL PAGO

                                    </div>

                                    <div class="fw-bold text-success">

                                        Monto base + bonificaciones - descuentos

                                    </div>

                                </div>


                                <div class="col-md-5">

                                    <div class="input-group input-group-lg">

                                        <span
                                            class="input-group-text bg-success text-white">

                                            S/.

                                        </span>

                                        <input type="number"
                                            class="form-control fw-bold text-end"
                                            id="editar_monto_total"
                                            name="monto_total"
                                            min="0"
                                            step="0.01"
                                            readonly>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                        INFORMACIÓN DEL PAGO
                    ==================================================-->

                    <h6 class="fw-bold mb-3">

                        <i class="bi bi-credit-card text-primary me-2"></i>

                        Información del pago

                    </h6>


                    <div class="row g-3 mb-4">


                        <!-- FECHA DE PAGO -->

                        <div class="col-md-4">

                            <label
                                for="editar_fecha_pago"
                                class="form-label">

                                Fecha de pago

                            </label>

                            <input type="date"
                                class="form-control"
                                id="editar_fecha_pago"
                                name="fecha_pago"
                                required>

                        </div>


                        <!-- CUENTA BANCARIA -->

                        <div class="col-md-4">

                            <label
                                for="editar_id_cuenta_bancaria"
                                class="form-label">

                                Cuenta bancaria

                            </label>

                            <select
                                class="form-select"
                                id="editar_id_cuenta_bancaria"
                                name="id_cuenta_bancaria">

                                <option value="">

                                    Seleccionar cuenta

                                </option>

                            </select>

                        </div>


                        <!-- MÉTODO DE PAGO -->

                        <div class="col-md-4">

                            <label
                                for="editar_id_metodo_pago"
                                class="form-label">

                                Método de pago

                            </label>

                            <select
                                class="form-select"
                                id="editar_id_metodo_pago"
                                name="id_metodo_pago">

                                <option value="">

                                    Seleccionar método

                                </option>

                            </select>

                        </div>

                    </div>


                    <!--=================================================
                        OBSERVACIÓN
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="editar_observacion"
                            class="form-label">

                            <i class="bi bi-chat-left-text me-1"></i>

                            Observación

                        </label>

                        <textarea
                            class="form-control"
                            id="editar_observacion"
                            name="observacion"
                            rows="3"
                            maxlength="255"
                            placeholder="Ingrese una observación sobre el pago..."></textarea>

                        <div class="form-text">

                            Máximo 255 caracteres.

                        </div>

                    </div>


                    <!--=================================================
                        MENSAJE DE VALIDACIÓN
                    ==================================================-->

                    <div
                        id="mensajeEditarPagoEmpleado"
                        class="alert d-none mb-0"
                        role="alert">
                    </div>


                </div>


                <!--=================================================
                    FOOTER
                ==================================================-->

                <div class="modal-footer bg-light">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnActualizarPagoEmpleado">

                        <i class="bi bi-check-circle me-1"></i>

                        Guardar cambios

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!--=====================================================
    JAVASCRIPT DEL MODAL
======================================================-->

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const montoBase =
            document.getElementById("editar_monto_base");

        const bonificaciones =
            document.getElementById("editar_bonificaciones");

        const descuentos =
            document.getElementById("editar_descuentos");

        const montoTotal =
            document.getElementById("editar_monto_total");


        /**
         * Calcular monto total
         *
         * TOTAL =
         * MONTO BASE + BONIFICACIONES - DESCUENTOS
         */
        function calcularMontoTotal() {

            const base =
                parseFloat(montoBase?.value) || 0;

            const bonos =
                parseFloat(bonificaciones?.value) || 0;

            const descuentosValor =
                parseFloat(descuentos?.value) || 0;


            let total =
                base + bonos - descuentosValor;


            if (total < 0) {

                total = 0;

            }


            if (montoTotal) {

                montoTotal.value =
                    total.toFixed(2);

            }

        }


        if (montoBase) {

            montoBase.addEventListener(
                "input",
                calcularMontoTotal
            );

        }


        if (bonificaciones) {

            bonificaciones.addEventListener(
                "input",
                calcularMontoTotal
            );

        }


        if (descuentos) {

            descuentos.addEventListener(
                "input",
                calcularMontoTotal
            );

        }


        /**
         * Validar período
         */
        const periodoInicio =
            document.getElementById("editar_periodo_inicio");

        const periodoFin =
            document.getElementById("editar_periodo_fin");


        function validarPeriodo() {

            if (
                periodoInicio?.value &&
                periodoFin?.value
            ) {

                if (
                    periodoFin.value <
                    periodoInicio.value
                ) {

                    periodoFin.setCustomValidity(
                        "La fecha final no puede ser anterior a la fecha inicial."
                    );

                } else {

                    periodoFin.setCustomValidity("");

                }

            }

        }


        if (periodoInicio) {

            periodoInicio.addEventListener(
                "change",
                validarPeriodo
            );

        }


        if (periodoFin) {

            periodoFin.addEventListener(
                "change",
                validarPeriodo
            );

        }


    });
</script>