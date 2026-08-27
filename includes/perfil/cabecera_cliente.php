<!--======================================================
CoDevPro Technology
PERFIL DEL CLIENTE
Cabecera
=======================================================-->

<div class="card border-0 shadow-sm mb-4">

    <div class="card-body">

        <div class="row align-items-center">

            <!-- Foto -->

            <div class="col-lg-2 text-center">

                <img
                    src="assets/img/user.png"
                    class="rounded-circle border shadow"
                    style="width:130px;height:130px;object-fit:cover;">

            </div>

            <!-- Información -->

            <div class="col-lg-7 mt-4 mt-lg-0">

                <div class="d-flex align-items-center flex-wrap gap-2">

                    <h2 class="fw-bold mb-0">

                        <?= htmlspecialchars($_SESSION["nombreCliente"] ?? "Cliente"); ?>

                    </h2>

                    <span class="badge bg-success">

                        Cliente Verificado

                    </span>

                </div>
                <div class="row g-3">

                    <div class="col-md-6">

                        <div class="small text-muted">

                            <i class="bi bi-envelope"></i>

                            Correo electrónico

                        </div>

                        <div class="fw-semibold">

                           <?= htmlspecialchars($_SESSION["emailCliente"] ?? ""); ?>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="small text-muted">

                            <i class="bi bi-phone"></i>

                            Celular

                        </div>

                        <div class="fw-semibold">

                            999999999

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="small text-muted">

                            <i class="bi bi-geo-alt"></i>

                            Dirección

                        </div>

                        <div class="fw-semibold">

                            Iquitos - Perú

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="small text-muted">

                            <i class="bi bi-calendar-event"></i>

                            Cliente desde

                        </div>

                        <div class="fw-semibold">

                            15 Marzo 2026

                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>