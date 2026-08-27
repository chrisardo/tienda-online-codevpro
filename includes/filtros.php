<!--Toda esta parte es de includes/filtros.php-->
<div class="container py-4">

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="row g-3 align-items-center">

                <div class="col-lg-6">

                    <div class="input-group">

                        <span class="input-group-text bg-white">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                            type="text"
                            id="buscarProducto"
                            class="form-control form-control-lg"
                            placeholder="Buscar productos tecnológicos..."
                            value="<?= htmlspecialchars($busquedaInicial ?? '') ?>">

                    </div>

                </div>

                <div class="col-lg-3">

                    <select
                        class="form-select form-select-lg"
                        id="ordenar">

                        <option value="recientes">

                            Más recientes

                        </option>

                        <option value="precioAsc">

                            Precio menor

                        </option>

                        <option value="precioDesc">

                            Precio mayor

                        </option>

                        <option value="nombre">

                            Nombre A-Z

                        </option>

                    </select>

                </div>

                <div class="col-lg-3">

                    <button
                        class="btn btn-primary btn-lg w-100">

                        <i class="bi bi-funnel-fill"></i>

                        Filtrar

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>