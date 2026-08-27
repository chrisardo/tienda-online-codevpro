<?php
//=====================================================
// CoDevPro Technology
// modal/modal_nueva_categoria.php
//=====================================================
?>

<div class="modal fade"
    id="modalNuevaCategoria"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <!-- HEADER -->

            <div class="modal-header border-0 pb-0">

                <div>

                    <h4 class="fw-bold mb-1">

                        <i class="bi bi-tags-fill text-primary me-2"></i>

                        Nueva Categoría

                    </h4>

                    <p class="text-muted mb-0">

                        Crea una nueva categoría para organizar tus productos.

                    </p>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <!-- FORMULARIO -->

            <form
                id="formNuevaCategoria"
                enctype="multipart/form-data"
                novalidate>

                <div class="modal-body pt-4">

                    <div class="row g-4">

                        <!-- IMAGEN -->

                        <div class="col-lg-5">

                            <label class="form-label fw-semibold">

                                Imagen de Categoría

                            </label>

                            <div
                                class="border rounded-4 p-3 text-center bg-light position-relative"
                                style="min-height:280px;">

                                <img
                                    id="previewCategoria"
                                    src="img/logo.png"
                                    class="img-fluid rounded mb-3"
                                    style="
                                        max-height:180px;
                                        object-fit:contain;
                                    ">

                                <input
                                    type="file"
                                    class="form-control"
                                    id="imagenCategoria"
                                    name="imagen"
                                    accept="image/*">

                                <small class="text-muted d-block mt-2">

                                    JPG, PNG o WEBP
                                    (Máx. 2.7 MB)

                                </small>

                            </div>

                        </div>

                        <!-- DATOS -->

                        <div class="col-lg-7">

                            <div class="mb-4">

                                <label class="form-label fw-semibold">

                                    Nombre de Categoría

                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="text"
                                    class="form-control form-control-lg"
                                    id="nombreCategoria"
                                    name="nombre"
                                    maxlength="100"
                                    required>

                                <div class="invalid-feedback">

                                    Debe ingresar un nombre.

                                </div>

                            </div>

                            <!-- TARJETA INFO -->

                            <div class="card border-0 bg-light">

                                <div class="card-body">

                                    <h6 class="fw-bold">

                                        <i class="bi bi-lightbulb me-2"></i>

                                        Recomendaciones

                                    </h6>

                                    <ul class="small text-muted mb-0">

                                        <li>
                                            Usa nombres cortos y claros.
                                        </li>

                                        <li>
                                            Evita categorías duplicadas.
                                        </li>

                                        <li>
                                            Usa una imagen representativa.
                                        </li>

                                        <li>
                                            Facilita la búsqueda de productos.
                                        </li>

                                    </ul>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- FOOTER -->

                <div class="modal-footer border-0">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardarCategoria">

                        <i class="bi bi-save me-2"></i>

                        Guardar Categoría

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- ===================================== -->
<!-- PREVIEW IMAGEN -->
<!-- ===================================== -->

<script>
    document.addEventListener("DOMContentLoaded", () => {

        const imagen =
            document.getElementById("imagenCategoria");

        const preview =
            document.getElementById("previewCategoria");

        imagen.addEventListener("change", function() {

            const archivo = this.files[0];

            if (!archivo) return;

            if (archivo.size > 2.7 * 1024 * 1024) {

                alert(
                    "La imagen no puede superar los 2.7 MB"
                );

                this.value = "";

                return;
            }

            const reader = new FileReader();

            reader.onload = function(e) {

                preview.src = e.target.result;

            };

            reader.readAsDataURL(archivo);

        });

    });
</script>

<!-- ===================================== -->
<!-- VALIDACIÓN BOOTSTRAP -->
<!-- ===================================== -->

<script>
    (() => {

        'use strict';

        const forms =
            document.querySelectorAll('#formNuevaCategoria');

        Array.from(forms).forEach(form => {

            form.addEventListener(
                'submit',
                event => {

                    if (!form.checkValidity()) {

                        event.preventDefault();

                        event.stopPropagation();

                    }

                    form.classList.add('was-validated');

                },

                false
            );

        });

    })();
</script>