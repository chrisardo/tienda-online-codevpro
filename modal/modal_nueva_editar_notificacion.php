<!--=====================================================
    MODAL NUEVA / EDITAR NOTIFICACIÓN
======================================================-->
<div
    class="modal fade"
    id="modalNotificacion"
    tabindex="-1"
    aria-labelledby="modalNotificacionLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">


            <!--=================================================
                HEADER
            ==================================================-->

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title fw-bold"
                        id="modalNotificacionLabel">

                        <i class="bi bi-bell-fill text-primary me-2"></i>

                        Nueva notificación

                    </h5>

                    <small class="text-muted">

                        Envía una notificación a un cliente.

                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar">
                </button>

            </div>



            <!--=================================================
                BODY
            ==================================================-->

            <div class="modal-body">

                <form id="formNotificacion">


                    <!-- ID OCULTO -->

                    <input
                        type="hidden"
                        id="idNotificacion"
                        name="idNotificacion"
                        value="0">



                    <!--=================================================
                        CLIENTE
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="idClienteNotificacion"
                            class="form-label fw-semibold">

                            Cliente

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <select
                            class="form-select"
                            id="idClienteNotificacion"
                            name="idCliente"
                            required>

                            <option value="">

                                Seleccione un cliente

                            </option>

                        </select>

                        <div class="form-text">

                            Selecciona el cliente que recibirá la notificación.

                        </div>

                    </div>



                    <!--=================================================
                        TITULO
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="tituloNotificacion"
                            class="form-label fw-semibold">

                            Título

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="tituloNotificacion"
                            name="titulo"
                            maxlength="255"
                            placeholder="Ejemplo: Tu pedido ha sido confirmado"
                            required>

                    </div>



                    <!--=================================================
                        MENSAJE
                    ==================================================-->

                    <div class="mb-3">

                        <label
                            for="mensajeNotificacion"
                            class="form-label fw-semibold">

                            Mensaje

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <textarea
                            class="form-control"
                            id="mensajeNotificacion"
                            name="mensaje"
                            rows="4"
                            placeholder="Escribe el mensaje de la notificación..."
                            required></textarea>

                    </div>



                    <!--=================================================
                        TIPO / ICONO / COLOR
                    ==================================================-->

                    <div class="row g-3">


                        <!-- TIPO -->

                        <div class="col-md-4">

                            <label
                                for="tipoNotificacion"
                                class="form-label fw-semibold">

                                Tipo

                            </label>

                            <select
                                class="form-select"
                                id="tipoNotificacion"
                                name="tipo">

                                <option value="SISTEMA">
                                    Sistema
                                </option>

                                <option value="PEDIDO">
                                    Pedido
                                </option>

                                <option value="PRODUCTO">
                                    Producto
                                </option>

                                <option value="OFERTA">
                                    Oferta
                                </option>

                                <option value="PROMOCION">
                                    Promoción
                                </option>

                                <option value="OTRO">
                                    Otro
                                </option>

                            </select>

                        </div>



                        <!-- ICONO -->

                        <div class="col-md-4">

                            <label
                                for="iconoNotificacion"
                                class="form-label fw-semibold">

                                Icono

                            </label>

                            <div class="input-group">

                                <span class="input-group-text">

                                    <i
                                        class="bi bi-bell-fill"
                                        id="vistaIconoNotificacion">
                                    </i>

                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="iconoNotificacion"
                                    name="icono"
                                    value="bi-bell-fill"
                                    placeholder="bi-bell-fill">

                            </div>

                        </div>



                        <!-- COLOR -->

                        <div class="col-md-4">

                            <label
                                for="colorNotificacion"
                                class="form-label fw-semibold">

                                Color

                            </label>

                            <select
                                class="form-select"
                                id="colorNotificacion"
                                name="color">

                                <option value="primary">
                                    Azul
                                </option>

                                <option value="success">
                                    Verde
                                </option>

                                <option value="warning">
                                    Amarillo
                                </option>

                                <option value="danger">
                                    Rojo
                                </option>

                                <option value="info">
                                    Celeste
                                </option>

                                <option value="secondary">
                                    Gris
                                </option>

                                <option value="dark">
                                    Oscuro
                                </option>

                            </select>

                        </div>

                    </div>



                    <!--=================================================
                        URL
                    ==================================================-->

                    <div class="mt-3 mb-3">

                        <label
                            for="urlNotificacion"
                            class="form-label fw-semibold">

                            URL de destino

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="urlNotificacion"
                            name="url"
                            maxlength="255"
                            placeholder="Ejemplo: mis_pedidos.php">

                        <div class="form-text">

                            Página a la que será dirigido el cliente al pulsar la notificación.

                        </div>

                    </div>



                    <!--=================================================
                        ESTADO
                    ==================================================-->

                    <div class="form-check form-switch mb-2">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="notificacionLeida"
                            name="leido"
                            value="1">

                        <label
                            class="form-check-label fw-semibold"
                            for="notificacionLeida">

                            Marcar como leída

                        </label>

                    </div>


                </form>

            </div>



            <!--=================================================
                FOOTER
            ==================================================-->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    <i class="bi bi-x-circle me-2"></i>

                    Cancelar

                </button>


                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnGuardarNotificacion">

                    <i class="bi bi-check-circle me-2"></i>

                    Guardar notificación

                </button>

            </div>

        </div>

    </div>

</div>