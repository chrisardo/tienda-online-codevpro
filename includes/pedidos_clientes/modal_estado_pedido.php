<?php
//=====================================================
// CoDevPro Technology
// Archivo: includes/pedidos_clientes/modal_estado_pedido.php
// Módulo: Gestión de Pedidos de Clientes
// Sistema: Inventa
//=====================================================
?>

<!--=====================================================
    MODAL ACTUALIZAR ESTADO DEL PEDIDO
======================================================-->

<div
    class="modal fade"
    id="modalEstadoPedido"
    tabindex="-1"
    aria-labelledby="tituloModalEstadoPedido"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header bg-success text-white">

                <h5
                    class="modal-title"
                    id="tituloModalEstadoPedido">

                    <i class="bi bi-pencil-square me-2"></i>

                    Actualizar Estado del Pedido

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>

            </div>


            <!--=================================================
                FORMULARIO
            ==================================================-->

            <form
                id="formEstadoPedido"
                autocomplete="off">

                <div class="modal-body">

                    <!--=================================================
                        ID PEDIDO
                    ==================================================-->

                    <input
                        type="hidden"
                        id="idPedidoEstado"
                        name="idPedido"
                        value="">


                    <!--=================================================
                        ESTADO DEL PEDIDO
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="estadoPedido"
                            class="form-label fw-semibold">

                            <i class="bi bi-arrow-repeat me-1"></i>

                            Estado del pedido

                        </label>

                        <select
                            class="form-select"
                            id="estadoPedido"
                            name="estadoPedido"
                            required>

                            <option value="PENDIENTE">
                                Pendiente
                            </option>

                            <option value="CONFIRMADO">
                                Confirmado
                            </option>

                            <option value="PREPARANDO">
                                Preparando
                            </option>

                            <option value="ASIGNADO">
                                Repartidor asignado
                            </option>

                            <option value="OBTENIDO">
                                Pedido obtenido
                            </option>

                            <option value="ENTREGADO">
                                Entregado
                            </option>

                            <option value="NO_ENTREGADO">
                                No entregado
                            </option>

                            <option value="CANCELADO">
                                Cancelado
                            </option>

                        </select>

                        <div class="form-text">

                            Seleccione el nuevo estado del pedido.

                        </div>

                    </div>


                    <!--=================================================
                        CONTENEDOR REPARTIDOR
                    ==================================================-->

                    <div
                        id="contenedorRepartidor"
                        class="d-none">


                        <!--=================================================
                            SELECT PARA ASIGNAR REPARTIDOR

                            Se mostrará únicamente cuando:

                            - Estado = PREPARANDO
                            - Y todavía no existe repartidor asignado
                        ==================================================-->

                        <div
                            id="contenedorSeleccionRepartidor"
                            class="mb-3">

                            <label
                                for="repartidorPedido"
                                class="form-label fw-semibold">

                                <i class="bi bi-person-badge me-1"></i>

                                Repartidor

                            </label>

                            <select
                                class="form-select"
                                id="repartidorPedido"
                                name="idEmpleado">

                                <option value="">
                                    Seleccione un repartidor
                                </option>

                            </select>

                            <div class="form-text">

                                Seleccione el empleado encargado
                                de realizar la entrega del pedido.

                            </div>

                        </div>


                        <!--=================================================
                            INFORMACIÓN DEL REPARTIDOR

                            Se mostrará cuando el pedido ya tenga
                            un repartidor asignado.

                            El usuario NO podrá cambiarlo desde
                            este modal.
                        ==================================================-->

                        <div
                            id="infoRepartidor"
                            class="alert alert-light border d-none mb-3">

                            <div class="d-flex align-items-center">

                                <!--=========================================
                                    ICONO
                                ==========================================-->

                                <div class="me-3">

                                    <div
                                        class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px;">

                                        <i
                                            class="bi bi-person-check-fill fs-3 text-success">
                                        </i>

                                    </div>

                                </div>


                                <!--=========================================
                                    DATOS
                                ==========================================-->

                                <div class="flex-grow-1">

                                    <div class="fw-semibold text-dark">

                                        Repartidor asignado

                                    </div>

                                    <div
                                        class="fw-bold"
                                        id="nombreRepartidor">

                                    </div>

                                    <div
                                        class="small text-muted"
                                        id="celularRepartidor">

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!--=================================================
                            AVISO REPARTIDOR YA ASIGNADO
                        ==================================================-->

                        <div
                            id="alertaRepartidorAsignado"
                            class="alert alert-info d-none mb-3">

                            <div class="d-flex align-items-start">

                                <i
                                    class="bi bi-info-circle-fill me-2 mt-1">
                                </i>

                                <div>

                                    <div class="fw-semibold">

                                        Repartidor ya asignado

                                    </div>

                                    <div class="small">

                                        Este pedido ya tiene un repartidor
                                        asignado. El repartidor no puede
                                        cambiarse desde este estado.

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                        OBSERVACIÓN
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="observacionPedido"
                            class="form-label fw-semibold">

                            <i class="bi bi-chat-left-text me-1"></i>

                            Observación

                        </label>

                        <textarea
                            class="form-control"
                            id="observacionPedido"
                            name="observacionPedido"
                            rows="4"
                            maxlength="1000"
                            placeholder="Escriba una observación para el cliente..."></textarea>

                        <div class="form-text">

                            Esta observación podrá utilizarse para
                            informar al cliente sobre la actualización
                            del pedido.

                        </div>

                    </div>


                    <!--=================================================
                        INFORMACIÓN GENERAL
                    ==================================================-->

                    <div
                        class="alert alert-info mb-0"
                        role="alert">

                        <div class="d-flex">

                            <div class="me-2">

                                <i class="bi bi-info-circle-fill"></i>

                            </div>

                            <div>

                                Al actualizar el estado se registrará
                                automáticamente la fecha correspondiente
                                al nuevo estado.

                            </div>

                        </div>

                    </div>


                    <!--=================================================
                        ALERTA PEDIDO ENTREGADO
                    ==================================================-->

                    <div
                        id="alertaPedidoEntregado"
                        class="alert alert-success d-none mt-3 mb-0"
                        role="alert">

                        <i class="bi bi-check-circle-fill me-2"></i>

                        Este pedido ya fue entregado y no puede
                        modificarse.

                    </div>

                </div>


                <!--=================================================
                    FOOTER
                ==================================================-->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="btn btn-success"
                        id="btnGuardarEstadoPedido">

                        <i class="bi bi-save-fill me-1"></i>

                        Guardar cambios

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>