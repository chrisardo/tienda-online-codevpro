<?php
//=====================================================
// CoDevPro Technology
// Modal: Nuevo / Editar Sueldo
// Sistema: Inventa
//=====================================================
?>

<div
    class="modal fade"
    id="modalSueldo"
    tabindex="-1"
    aria-labelledby="tituloModalSueldo"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content modal-sueldo">


            <!--=================================================
            HEADER
            =================================================-->

            <div class="modal-header flex-shrink-0">

                <div class="d-flex align-items-center gap-3">

                    <div class="sueldo-modal-icon">

                        <i class="bi bi-cash-stack"></i>

                    </div>

                    <div>

                        <h5
                            class="modal-title fw-bold mb-1"
                            id="tituloModalSueldo">

                            Asignar Sueldo

                        </h5>

                        <small class="text-muted">

                            Configure la remuneración del empleado.

                        </small>

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
            =================================================-->

            <form
                id="formSueldo"
                class="d-flex flex-column flex-grow-1">


                <!--=================================================
                CONTENIDO CON SCROLL
                =================================================-->

                <div class="modal-body">


                    <input
                        type="hidden"
                        id="idSueldo"
                        name="id_sueldo"
                        value="0">


                    <!--=================================================
                    EMPLEADO
                    =================================================-->

                    <div class="mb-4">

                        <label
                            for="idEmpleadoSueldo"
                            class="form-label fw-semibold">

                            Empleado

                            <span class="text-danger">*</span>

                        </label>


                        <select
                            class="form-select"
                            id="idEmpleadoSueldo"
                            name="id_empleado"
                            required>

                            <option value="">
                                Seleccione un empleado...
                            </option>

                        </select>


                        <div class="form-text">

                            Solo se mostrarán empleados registrados en tu cuenta.

                        </div>

                    </div>


                    <!--=================================================
                    INFORMACIÓN DEL EMPLEADO
                    =================================================-->

                    <div
                        id="infoEmpleadoSueldo"
                        class="sueldo-info-empleado mb-4 d-none">

                        <div class="d-flex align-items-center gap-3">


                            <!-- AVATAR -->

                            <div id="avatarEmpleadoSueldo">

                            </div>


                            <!-- INFORMACIÓN -->

                            <div class="min-w-0">

                                <div
                                    class="fw-bold"
                                    id="nombreEmpleadoSueldo">

                                </div>


                                <small
                                    class="text-muted"
                                    id="cargoEmpleadoSueldo">

                                </small>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                    INFORMACIÓN SALARIAL
                    =================================================-->

                    <div class="card border-0 bg-light mb-4">

                        <div class="card-body">


                            <h6 class="fw-bold mb-3">

                                <i class="bi bi-wallet2 text-primary me-2"></i>

                                Información salarial

                            </h6>


                            <div class="row g-3">


                                <!--=================================================
                                SUELDO BASE
                                =================================================-->

                                <div class="col-12 col-md-6">

                                    <label
                                        for="sueldoBase"
                                        class="form-label fw-semibold">

                                        Sueldo base

                                        <span class="text-danger">*</span>

                                    </label>


                                    <div class="input-group">

                                        <span class="input-group-text">
                                            S/
                                        </span>


                                        <input
                                            type="number"
                                            class="form-control"
                                            id="sueldoBase"
                                            name="sueldo_base"
                                            min="0"
                                            step="0.01"
                                            placeholder="0.00"
                                            required>

                                    </div>

                                </div>


                                <!--=================================================
                                PERIODICIDAD
                                =================================================-->

                                <div class="col-12 col-md-6">

                                    <label
                                        for="tipoBase"
                                        class="form-label fw-semibold">

                                        Periodicidad

                                        <span class="text-danger">*</span>

                                    </label>


                                    <select
                                        class="form-select"
                                        id="tipoBase"
                                        name="tipo_base"
                                        required>

                                        <option value="MENSUAL">
                                            Mensual
                                        </option>

                                        <option value="QUINCENAL">
                                            Quincenal
                                        </option>

                                        <option value="SEMANAL">
                                            Semanal
                                        </option>

                                        <option value="DIARIO">
                                            Diario
                                        </option>

                                    </select>

                                </div>


                                <!--=================================================
                                FECHA INICIO
                                =================================================-->

                                <div class="col-12 col-md-6">

                                    <label
                                        for="fechaInicioSueldo"
                                        class="form-label fw-semibold">

                                        Fecha de inicio

                                        <span class="text-danger">*</span>

                                    </label>


                                    <input
                                        type="date"
                                        class="form-control"
                                        id="fechaInicioSueldo"
                                        name="fecha_inicio"
                                        required>

                                </div>


                                <!--=================================================
                                FECHA FIN
                                =================================================-->

                                <div class="col-12 col-md-6">

                                    <label
                                        for="fechaFinSueldo"
                                        class="form-label fw-semibold">

                                        Fecha de fin

                                    </label>


                                    <input
                                        type="date"
                                        class="form-control"
                                        id="fechaFinSueldo"
                                        name="fecha_fin">


                                    <div class="form-text">

                                        Déjelo vacío si el sueldo continúa vigente.

                                    </div>

                                </div>


                                <!--=================================================
                                ESTADO
                                =================================================-->

                                <div class="col-12">

                                    <label
                                        for="estadoSueldo"
                                        class="form-label fw-semibold">

                                        Estado

                                    </label>


                                    <select
                                        class="form-select"
                                        id="estadoSueldo"
                                        name="estado">

                                        <option value="ACTIVO">
                                            Activo
                                        </option>

                                        <option value="INACTIVO">
                                            Inactivo
                                        </option>

                                    </select>

                                </div>


                                <!--=================================================
                                OBSERVACIÓN
                                =================================================-->

                                <div class="col-12">

                                    <label
                                        for="observacionSueldo"
                                        class="form-label fw-semibold">

                                        Observación

                                    </label>


                                    <textarea
                                        class="form-control"
                                        id="observacionSueldo"
                                        name="observacion"
                                        rows="3"
                                        maxlength="255"
                                        placeholder="Ejemplo: Incremento salarial, contratación inicial, cambio de cargo..."></textarea>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                    ADVERTENCIA
                    =================================================-->

                    <div class="alert alert-info d-flex gap-2 align-items-start mb-0">

                        <i class="bi bi-info-circle-fill mt-1"></i>


                        <div>

                            <strong>Importante:</strong>

                            al asignar un nuevo sueldo activo al mismo empleado,
                            el sistema desactivará automáticamente su sueldo
                            activo anterior.

                        </div>

                    </div>


                </div>


                <!--=================================================
                FOOTER FIJO
                =================================================-->

                <div class="modal-footer flex-shrink-0">


                    <!-- CANCELAR -->

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <!-- GUARDAR -->

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarSueldo">

                        <i class="bi bi-check-circle me-1"></i>

                        Guardar Sueldo

                    </button>


                </div>


            </form>

        </div>

    </div>

</div>