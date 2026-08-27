<?php
//======================================================
// CoDevPro Technology
// Menú lateral del perfil
//======================================================
?>

<div class="card border-0 shadow-sm sticky-top" style="top:90px;">

    <!--=========================================
    MENÚ
    ==========================================-->

    <div class="list-group list-group-flush">

        <a
            href="#"
            class="list-group-item list-group-item-action border-0 active menuPerfil"
            data-vista="vistaDashboard">

            <i class="bi bi-speedometer2 me-2"></i>

            Dashboard

        </a>

        <a
            href="#"
            class="list-group-item list-group-item-action border-0 menuPerfil"
            data-vista="vistaPerfil">

            <i class="bi bi-person-fill me-2"></i>

            Mi información

        </a>
        <a
            href="#"
            class="list-group-item list-group-item-action border-0 menuPerfil"
            data-vista="vistaSeguridad">

            <i class="bi bi-shield-lock-fill me-2"></i>

            Seguridad

        </a>

        <a
            href="#"
            class="list-group-item list-group-item-action border-0 menuPerfil"
            data-vista="vistaPreferencias">

            <i class="bi bi-sliders me-2"></i>

            Preferencias

        </a>

    </div>

    <!--=========================================
    ACCESOS RÁPIDOS
    ==========================================-->

    <div class="card-body border-top">

        <h6 class="text-uppercase text-muted mb-3">

            Accesos rápidos

        </h6>

        <div class="d-grid gap-2">

            <a
                href="mis_pedidos.php"
                class="btn btn-outline-primary btn-sm">

                <i class="bi bi-bag-check-fill"></i>

                Mis pedidos

            </a>

            <a
                href="favoritos.php"
                class="btn btn-outline-danger btn-sm">

                <i class="bi bi-heart-fill"></i>

                Favoritos

            </a>

            <a
                href="tienda.php"
                class="btn btn-outline-success btn-sm">

                <i class="bi bi-cart-fill"></i>

                Seguir comprando

            </a>

        </div>

    </div>

    <!--=========================================
    CERRAR SESIÓN
    ==========================================-->

    <div class="card-footer bg-white">

        <div class="d-grid">

            <a
                href="logout.php"
                class="btn btn-danger">

                <i class="bi bi-box-arrow-right"></i>

                Cerrar sesión

            </a>

        </div>

    </div>

</div>