<?php
//=====================================================
// CoDevPro Technology
// includes/admin_navbar.php
//=====================================================
?>
<!-- NAVBAR -->

<div class="admin-navbar d-flex align-items-center justify-content-between">


    <!-- IZQUIERDA -->

    <div class="d-flex align-items-center gap-4">

        <!-- BOTON SIDEBAR -->

        <button class="btn-sidebar" id="btnSidebar">

            <i class="bi bi-list"></i>

        </button>


    </div>



    <!-- DERECHA -->

    <div class="d-flex align-items-center gap-3">


        <!-- NOTIFICACIONES -->

        <div class="icon-navbar">

            <i class="bi bi-bell fs-5"></i>

            <span class="notification-badge">

                8

            </span>

        </div>


        <!-- MODO OSCURO -->

        <div class="icon-navbar">

            <i class="bi bi-moon fs-5"></i>

        </div>


        <!-- PERFIL ADMIN -->

        <div class="dropdown">

            <a href="#"
                class="text-decoration-none dropdown-toggle d-flex align-items-center"
                data-bs-toggle="dropdown">

                <div class="profile-admin d-flex align-items-center">

                    <img src="https://ui-avatars.com/api/?name=Administrador&background=0D6EFD&color=fff"
                        alt="Administrador">

                    <div class="ms-3">

                        <strong class="text-dark">

                            Administrador

                        </strong>

                        <br>

                        <small>

                            Super Admin

                        </small>

                    </div>

                </div>

            </a>


            <!-- DROPDOWN -->

            <ul class="dropdown-menu dropdown-menu-end shadow border-0">

                <li>

                    <a class="dropdown-item" href="#">

                        <i class="bi bi-person-circle me-2"></i>
                        Mi Perfil

                    </a>

                </li>

                <li>

                    <a class="dropdown-item" href="#">

                        <i class="bi bi-gear me-2"></i>
                        Configuración

                    </a>

                </li>


                <li>

                    <hr class="dropdown-divider">

                </li>


                <li>

                    <a class="dropdown-item text-danger"
                        href="logout.php">

                        <i class="bi bi-box-arrow-right me-2"></i>
                        Cerrar Sesión

                    </a>

                </li>

            </ul>

        </div>

    </div>


</div>