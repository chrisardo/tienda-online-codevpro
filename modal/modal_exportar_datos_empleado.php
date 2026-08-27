<!--=====================================================
MODAL EXPORTAR DATOS DEL EMPLEADO
======================================================-->

<div class="modal fade"
    id="modalExportarDatosEmpleado"
    tabindex="-1"
    aria-labelledby="modalExportarDatosEmpleadoLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content border-0 shadow">

            <!--=================================================
            HEADER
            ==================================================-->

            <div class="modal-header">

                <div>

                    <h5 class="modal-title fw-bold"
                        id="modalExportarDatosEmpleadoLabel">

                        <i class="bi bi-file-earmark-excel-fill text-success me-2"></i>

                        Exportar datos de empleados

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

                <div class="alert alert-info d-flex align-items-start gap-2">

                    <i class="bi bi-info-circle-fill mt-1"></i>

                    <div class="small">

                        El archivo se generará en formato
                        <strong>Excel (.xlsx)</strong>.

                        La información se organizará en diferentes
                        hojas para mantener correctamente las
                        relaciones entre empleados, roles, sueldos,
                        pagos y ventas.

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
                            id="btnSeleccionarTodoExportacion">

                            <i class="bi bi-check2-square me-1"></i>

                            Seleccionar todo

                        </button>

                        <button type="button"
                            class="btn btn-sm btn-outline-secondary"
                            id="btnDeseleccionarTodoExportacion">

                            <i class="bi bi-square me-1"></i>

                            Deseleccionar todo

                        </button>

                    </div>

                    <span class="small text-muted">

                        <span id="contadorExportacion">
                            0
                        </span>
                        opciones seleccionadas

                    </span>

                </div>


                <div class="row g-3">


                    <!--=================================================
                    DATOS DEL EMPLEADO
                    ==================================================-->

                    <div class="col-12">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div class="form-check">

                                    <input class="form-check-input
                                                  categoria-exportacion"
                                        type="checkbox"
                                        id="exportarEmpleadoCategoria"
                                        checked>

                                    <label class="form-check-label fw-bold"
                                        for="exportarEmpleadoCategoria">

                                        <i class="bi bi-person-vcard me-2"></i>

                                        Datos del empleado

                                    </label>

                                </div>

                            </div>

                            <div class="card-body">

                                <div class="row g-2">


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="empleado_datos"
                                                id="exportEmpleadoDatos"
                                                checked>

                                            <label class="form-check-label"
                                                for="exportEmpleadoDatos">

                                                Datos personales

                                            </label>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="empleado_contacto"
                                                id="exportEmpleadoContacto"
                                                checked>

                                            <label class="form-check-label"
                                                for="exportEmpleadoContacto">

                                                Contacto y dirección

                                            </label>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="empleado_estado"
                                                id="exportEmpleadoEstado"
                                                checked>

                                            <label class="form-check-label"
                                                for="exportEmpleadoEstado">

                                                Estado del empleado

                                            </label>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="empleado_fechas"
                                                id="exportEmpleadoFechas"
                                                checked>

                                            <label class="form-check-label"
                                                for="exportEmpleadoFechas">

                                                Fechas de registro y actualización

                                            </label>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                    INFORMACIÓN LABORAL
                    ==================================================-->

                    <div class="col-12">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div class="form-check">

                                    <input class="form-check-input
                                                  categoria-exportacion"
                                        type="checkbox"
                                        id="exportarLaboralCategoria"
                                        checked>

                                    <label class="form-check-label fw-bold"
                                        for="exportarLaboralCategoria">

                                        <i class="bi bi-briefcase me-2"></i>

                                        Información laboral

                                    </label>

                                </div>

                            </div>

                            <div class="card-body">

                                <div class="row g-2">


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="rol"
                                                id="exportRol"
                                                checked>

                                            <label class="form-check-label"
                                                for="exportRol">

                                                Rol / cargo

                                            </label>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="ubicacion"
                                                id="exportUbicacion"
                                                checked>

                                            <label class="form-check-label"
                                                for="exportUbicacion">

                                                Ubicación

                                            </label>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="permisos"
                                                id="exportPermisos">

                                            <label class="form-check-label"
                                                for="exportPermisos">

                                                Permisos del rol

                                            </label>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                    INFORMACIÓN ECONÓMICA
                    ==================================================-->

                    <div class="col-12">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div class="form-check">

                                    <input class="form-check-input
                                                  categoria-exportacion"
                                        type="checkbox"
                                        id="exportarEconomicaCategoria">

                                    <label class="form-check-label fw-bold"
                                        for="exportarEconomicaCategoria">

                                        <i class="bi bi-cash-stack me-2"></i>

                                        Información económica

                                    </label>

                                </div>

                            </div>

                            <div class="card-body">

                                <div class="row g-2">


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="sueldo"
                                                id="exportSueldo">

                                            <label class="form-check-label"
                                                for="exportSueldo">

                                                Sueldo configurado

                                            </label>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <div class="form-check">

                                            <input class="form-check-input
                                                          opcion-exportacion"
                                                type="checkbox"
                                                name="exportar[]"
                                                value="pagos"
                                                id="exportPagos">

                                            <label class="form-check-label"
                                                for="exportPagos">

                                                Historial de pagos

                                            </label>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                    ACTIVIDAD
                    ==================================================-->

                    <div class="col-12">

                        <div class="card border">

                            <div class="card-header bg-light">

                                <div class="form-check">

                                    <input class="form-check-input
                                                  categoria-exportacion"
                                        type="checkbox"
                                        id="exportarActividadCategoria">

                                    <label class="form-check-label fw-bold"
                                        for="exportarActividadCategoria">

                                        <i class="bi bi-bar-chart-line me-2"></i>

                                        Actividad

                                    </label>

                                </div>

                            </div>

                            <div class="card-body">

                                <div class="form-check">

                                    <input class="form-check-input
                                                  opcion-exportacion"
                                        type="checkbox"
                                        name="exportar[]"
                                        value="ventas"
                                        id="exportVentas">

                                    <label class="form-check-label"
                                        for="exportVentas">

                                        Ventas asignadas al empleado

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

                <button type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>

                <button type="button"
                    class="btn btn-success"
                    id="btnEjecutarExportacionEmpleado">

                    <i class="bi bi-file-earmark-excel-fill me-2"></i>

                    Exportar a Excel

                </button>

            </div>

        </div>

    </div>

</div>