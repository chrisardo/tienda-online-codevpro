<?php
//======================================================
// CoDevPro Technology
// modal/modal_nueva_marca.php
//======================================================
?>

<div class="modal fade"
    id="modalNuevaMarca"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">

                <h5 class="modal-title fw-bold">

                    <i class="bi bi-bookmark-plus-fill text-primary me-2"></i>

                    Nueva Marca

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form
                id="formNuevaMarca"
                enctype="multipart/form-data"
                novalidate>

                <div class="modal-body">

                    <div class="row g-4">

                        <div class="col-md-4">

                            <div class="text-center">

                                <img
                                    src="assets/img/sin_imagen.png"
                                    id="previewMarca"
                                    class="img-fluid rounded border shadow-sm"
                                    style="
                                        width:220px;
                                        height:220px;
                                        object-fit:cover;
                                    ">

                                <div class="mt-3">

                                    <input
                                        type="file"
                                        class="form-control"
                                        id="imagenMarca"
                                        name="imagen"
                                        accept="image/*">

                                </div>

                            </div>

                        </div>

                        <div class="col-md-8">

                            <div class="mb-3">

                                <label class="form-label fw-semibold">

                                    Nombre de la Marca

                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="nombre"
                                    maxlength="100"
                                    required>

                                <div class="invalid-feedback">

                                    Ingrese el nombre de la marca.

                                </div>

                            </div>

                            <div class="alert alert-info">

                                <i class="bi bi-info-circle-fill me-2"></i>

                                Registre las marcas que utilizará
                                para organizar sus productos.

                            </div>

                        </div>

                    </div>

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
                        class="btn btn-primary">

                        <i class="bi bi-save me-2"></i>

                        Guardar Marca

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>