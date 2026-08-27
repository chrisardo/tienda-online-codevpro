<?php
//=====================================================
// Modal Nuevo / Editar Rol
//=====================================================
?>

<div
    class="modal fade"
    id="modalRol"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">


            <!--=================================================
            HEADER
            =================================================-->

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title fw-bold"
                        id="tituloModalRol">

                        <i class="bi bi-person-badge-fill me-2 text-primary"></i>

                        Nuevo Rol

                    </h5>

                    <small class="text-muted">

                        Configure el nombre y los permisos del cargo.

                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>



            <!--=================================================
            FORMULARIO
            =================================================-->

            <form id="formRol">

                <div class="modal-body modal-rol-body">


                    <input
                        type="hidden"
                        id="idRol"
                        name="id_rol"
                        value="0">


                    <!--=========================================
                    NOMBRE
                    ==========================================-->

                    <div class="card border-0 bg-light mb-4">

                        <div class="card-body">

                            <label
                                for="nombreRol"
                                class="form-label fw-semibold">

                                Nombre del cargo / rol

                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nombreRol"
                                name="nombre"
                                maxlength="100"
                                placeholder="Ejemplo: Administrador, Vendedor, Almacén..."
                                autocomplete="off">

                            <div class="form-text">

                                El nombre identifica el nivel de acceso del empleado.

                            </div>

                        </div>

                    </div>



                    <!--=========================================
                    PERMISOS
                    ==========================================-->

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h6 class="fw-bold mb-1">

                                <i class="bi bi-shield-check me-2 text-primary"></i>

                                Permisos por módulo

                            </h6>

                            <small class="text-muted">

                                Seleccione las operaciones que podrá realizar este rol.

                            </small>

                        </div>


                        <div class="d-flex gap-2">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                id="btnSeleccionarTodos">

                                <i class="bi bi-check2-all me-1"></i>

                                Todos

                            </button>


                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary"
                                id="btnDeseleccionarTodos">

                                <i class="bi bi-x-lg me-1"></i>

                                Ninguno

                            </button>

                        </div>

                    </div>



                    <!--=================================================
                    TABLA CON SCROLL
                    =================================================-->

                    <div class="table-responsive permisos-container">

                        <table class="table table-bordered align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>
                                        Módulo
                                    </th>

                                    <th class="text-center permiso-col">
                                        Ver
                                    </th>

                                    <th class="text-center permiso-col">
                                        Crear
                                    </th>

                                    <th class="text-center permiso-col">
                                        Editar
                                    </th>

                                    <th class="text-center permiso-col">
                                        Eliminar
                                    </th>

                                </tr>

                            </thead>


                            <tbody id="tablaPermisos">

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-4">

                                        <div class="spinner-border spinner-border-sm text-primary"></div>

                                        <span class="ms-2">
                                            Cargando módulos...
                                        </span>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                </div>



                <!--=================================================
                FOOTER
                =================================================-->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarRol">

                        <i class="bi bi-check-circle me-1"></i>

                        Guardar Rol

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>