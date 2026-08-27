<?php
//=====================================================
// CoDevPro Technology
// includes/admin_sidebar.php
// Menú principal del Administrador
//=====================================================
?>

<!-- =====================================================
     SIDEBAR
====================================================== -->

<div class="admin-sidebar">

    <!-- =================================================
         HEADER MOBILE
    ================================================== -->

    <div class="sidebar-mobile-header">

        <button
            class="btn-sidebar-mobile"
            id="btnSidebarMobile"
            type="button">

            <i class="bi bi-list"></i>

        </button>

    </div>


    <!-- =================================================
         LOGO
    ================================================== -->

    <div class="logo d-flex align-items-center">

        <i class="bi bi-code-slash fs-1 text-primary me-3"></i>

        <div>

            <h4>Tienda Online</h4>

            <span>
                CoDevPro Technology
            </span>

        </div>

    </div>


    <!-- =================================================
         DASHBOARD
    ================================================== -->

    <a
        href="admin_index.php"
        class="nav-link active">

        <i class="bi bi-house-door"></i>

        <span class="menu-text">
            Dashboard
        </span>

    </a>


    <!-- =================================================
         PRINCIPAL
    ================================================== -->

    <div class="menu-title">

        <span class="menu-text">
            Principal
        </span>

    </div>


    <!-- =================================================
         VENTAS
    ================================================== -->

    <a
        href="#"
        class="nav-link"
        id="btnVentas">

        <i class="bi bi-cart3"></i>

        <span class="menu-text">
            Ventas
        </span>

        <i
            class="bi bi-chevron-down menu-arrow"
            id="iconVentas">
        </i>

    </a>


    <!-- SUBMENÚ VENTAS -->

    <div class="submenu-ventas">
        <a
            href="adm_ventas.php"
            class="submenu-link">

            <i class="bi bi-receipt-cutoff"></i>

            <span class="menu-text">
                Lista de Ventas
            </span>

        </a>


        <a
            href="admin_pedidos_clientes.php"
            class="submenu-link">

            <i class="bi bi-bag-check-fill"></i>

            <span class="menu-text">
                Pedidos Online
            </span>

            <span class="badge-menu">
                12
            </span>

        </a>
    </div>


    <!-- =================================================
         PRODUCTOS
    ================================================== -->

    <a
        href="#"
        class="nav-link"
        id="btnProductos">

        <i class="bi bi-box-seam"></i>

        <span class="menu-text">
            Productos
        </span>

        <i
            class="bi bi-chevron-down menu-arrow"
            id="iconProductos">
        </i>

    </a>


    <!-- SUBMENÚ PRODUCTOS -->

    <div class="submenu-productos">

        <a
            href="adm_lista_productos.php"
            class="submenu-link">

            <i class="bi bi-grid"></i>

            <span class="menu-text">
                Lista de Productos
            </span>

        </a>


        <a
            href="adm_nuevo_producto.php"
            class="submenu-link">

            <i class="bi bi-plus-circle"></i>

            <span class="menu-text">
                Nuevo Producto
            </span>

        </a>


        <a
            href="categorias.php"
            class="submenu-link">

            <i class="bi bi-tags"></i>

            <span class="menu-text">
                Categorías
            </span>

        </a>


        <a
            href="marcas.php"
            class="submenu-link">

            <i class="bi bi-bookmark-star"></i>

            <span class="menu-text">
                Marcas
            </span>

        </a>


        <a
            href="adm_ofertas_descuentos.php"
            class="submenu-link">

            <i class="bi bi-percent"></i>

            <span class="menu-text">
                Ofertas y Descuentos
            </span>

        </a>

    </div>


    <!-- =================================================
         CLIENTES
    ================================================== -->

    <a
        href="#"
        class="nav-link"
        id="btnClientes">

        <i class="bi bi-people"></i>

        <span class="menu-text">
            Clientes
        </span>

        <i
            class="bi bi-chevron-down menu-arrow"
            id="iconClientes">
        </i>

    </a>


    <!-- SUBMENÚ CLIENTES -->

    <div class="submenu-clientes">

        <a
            href="clientes.php"
            class="submenu-link">

            <i class="bi bi-people-fill"></i>

            <span class="menu-text">
                Lista de Clientes
            </span>

        </a>


        <a
            href="adm_favoritos.php"
            class="submenu-link">

            <i class="bi bi-heart-fill"></i>

            <span class="menu-text">
                Favoritos
            </span>

        </a>


        <a
            href="adm_testimonios.php"
            class="submenu-link">

            <i class="bi bi-chat-left-text-fill"></i>

            <span class="menu-text">
                Testimonios
            </span>

        </a>

    </div>


    <!-- =================================================
         PROVEEDORES
    ================================================== -->

    <a
        href="#"
        class="nav-link"
        id="btnProveedores">

        <i class="bi bi-person-badge"></i>

        <span class="menu-text">
            Proveedores
        </span>

        <i
            class="bi bi-chevron-down menu-arrow"
            id="iconProveedores">
        </i>

    </a>


    <!-- SUBMENÚ PROVEEDORES -->

    <div class="submenu-proveedores">

        <a
            href="adm_lista_proveedores.php"
            class="submenu-link">

            <i class="bi bi-people-fill"></i>

            <span class="menu-text">
                Lista de Proveedores
            </span>

        </a>


        <a
            href="adm_registrar_proveedor.php"
            class="submenu-link">

            <i class="bi bi-person-plus-fill"></i>

            <span class="menu-text">
                Registrar Proveedor
            </span>

        </a>


        <a
            href="adm_productos_proveedor.php"
            class="submenu-link">

            <i class="bi bi-box-seam-fill"></i>

            <span class="menu-text">
                Productos del Proveedor
            </span>

        </a>

    </div>


    <!-- =================================================
         OPERACIONES
    ================================================== -->

    <div class="menu-title">

        <span class="menu-text">
            Operaciones
        </span>

    </div>


    <!-- =================================================
         EMPLEADOS
    ================================================== -->

    <a
        href="#"
        class="nav-link"
        id="btnEmpleados">

        <i class="bi bi-person-workspace"></i>

        <span class="menu-text">
            Empleados
        </span>

        <i
            class="bi bi-chevron-down menu-arrow"
            id="iconEmpleados">
        </i>

    </a>


    <!-- SUBMENÚ EMPLEADOS -->

    <div class="submenu-empleados">

        <a
            href="adm_lista_empleados.php"
            class="submenu-link">

            <i class="bi bi-people-fill"></i>

            <span class="menu-text">
                Lista de Empleados
            </span>

        </a>


        <a
            href="adm_registrar_empleado.php"
            class="submenu-link">

            <i class="bi bi-person-plus-fill"></i>

            <span class="menu-text">
                Registrar Empleado
            </span>

        </a>


        <a
            href="adm_roles.php"
            class="submenu-link">

            <i class="bi bi-person-badge-fill"></i>

            <span class="menu-text">
                Cargos y Roles
            </span>

        </a>

    </div>


    <!-- =================================================
         CONTABILIDAD
    ================================================== -->

    <a
        href="#"
        class="nav-link"
        id="btnContabilidad">

        <i class="bi bi-wallet2"></i>

        <span class="menu-text">
            Contabilidad
        </span>

        <i
            class="bi bi-chevron-down menu-arrow"
            id="iconContabilidad">
        </i>

    </a>


    <!-- SUBMENÚ CONTABILIDAD -->

    <div class="submenu-contabilidad">

        <a
            href="adm_contabilidad.php"
            class="submenu-link">

            <i class="bi bi-speedometer2"></i>

            <span class="menu-text">
                Resumen Contable
            </span>

        </a>


        <a
            href="adm_deposito_gasto.php"
            class="submenu-link">

            <i class="bi bi-arrow-left-right"></i>

            <span class="menu-text">
                Ingresos y Gastos
            </span>

        </a>


        <a
            href="adm_cuentas_bancarias.php"
            class="submenu-link">

            <i class="bi bi-bank"></i>

            <span class="menu-text">
                Cuentas Bancarias
            </span>

        </a>


        <a
            href="adm_pago_empleado.php"
            class="submenu-link">

            <i class="bi bi-cash-stack"></i>

            <span class="menu-text">
                Pagos a Empleados
            </span>

        </a>


        <a
            href="adm_sueldos.php"
            class="submenu-link">

            <i class="bi bi-wallet-fill"></i>

            <span class="menu-text">
                Sueldos
            </span>

        </a>


        <a
            href="adm_metodos_pago.php"
            class="submenu-link">

            <i class="bi bi-credit-card-2-front-fill"></i>

            <span class="menu-text">
                Métodos de Pago
            </span>

        </a>

    </div>


    <!-- =================================================
         SUCURSALES
    ================================================== -->

    <a
        href="adm_sucursales.php"
        class="nav-link">

        <i class="bi bi-building"></i>

        <span class="menu-text">
            Sucursales
        </span>

    </a>


    <!-- =================================================
         REPORTES
    ================================================== -->

    <div class="menu-title">

        <span class="menu-text">
            Reportes
        </span>

    </div>


    <!-- MENÚ REPORTES -->

    <a
        href="#"
        class="nav-link"
        id="btnReportes">

        <i class="bi bi-bar-chart"></i>

        <span class="menu-text">
            Reportes
        </span>

        <i
            class="bi bi-chevron-down menu-arrow"
            id="iconReportes">
        </i>

    </a>


    <!-- SUBMENÚ REPORTES -->

    <div class="submenu-reportes">

        <a
            href="adm_estadisticas_ventas.php"
            class="submenu-link">

            <i class="bi bi-graph-up-arrow"></i>

            <span class="menu-text">
                Estadísticas de Ventas
            </span>

        </a>


        <a
            href="adm_estadisticas_productos.php"
            class="submenu-link">

            <i class="bi bi-box-seam-fill"></i>

            <span class="menu-text">
                Estadísticas de Productos
            </span>

        </a>


        <a
            href="adm_estadisticas_clientes.php"
            class="submenu-link">

            <i class="bi bi-people-fill"></i>

            <span class="menu-text">
                Estadísticas de Clientes
            </span>

        </a>


        <a
            href="adm_estadisticas_empleados.php"
            class="submenu-link">

            <i class="bi bi-person-workspace"></i>

            <span class="menu-text">
                Estadísticas de Empleados
            </span>

        </a>


        <a
            href="adm_estadisticas_proveedores.php"
            class="submenu-link">

            <i class="bi bi-person-badge-fill"></i>

            <span class="menu-text">
                Estadísticas de Proveedores
            </span>

        </a>
    </div>


    <!-- =================================================
         CONFIGURACIÓN
    ================================================== -->

    <div class="menu-title">

        <span class="menu-text">
            Configuración
        </span>

    </div>


    <!-- =================================================
         CONFIGURACIÓN
    ================================================== -->

    <a
        href="#"
        class="nav-link"
        id="btnConfiguracion">

        <i class="bi bi-gear"></i>

        <span class="menu-text">
            Configuración
        </span>

        <i
            class="bi bi-chevron-down menu-arrow"
            id="iconConfiguracion">
        </i>

    </a>


    <!-- SUBMENÚ CONFIGURACIÓN -->

    <div class="submenu-configuracion">
        <a
            href="adm_mi_empresa.php"
            class="submenu-link">

            <i class="bi bi-buildings-fill"></i>

            <span class="menu-text">
                Mi Empresa
            </span>

        </a>
        <a
            href="adm_notificaciones.php"
            class="submenu-link">

            <i class="bi bi-bell-fill"></i>

            <span class="menu-text">
                Notificaciones
            </span>

        </a>


        <a
            href="adm_monedas_impuestos.php"
            class="submenu-link">

            <i class="bi bi-currency-dollar"></i>

            <span class="menu-text">
                Monedas e Impuestos
            </span>

        </a>


        <a
            href="correos.php"
            class="submenu-link">

            <i class="bi bi-file-earmark-text-fill"></i>

            <span class="menu-text">
                Política de Privacidad
            </span>

        </a>


        <a
            href="integraciones.php"
            class="submenu-link">

            <i class="bi bi-plug-fill"></i>

            <span class="menu-text">
                Términos y Condiciones
            </span>

        </a>


        <a
            href="actualizaciones.php"
            class="submenu-link">

            <i class="bi bi-info-circle-fill"></i>

            <span class="menu-text">
                Acerca del Sistema
            </span>

        </a>

    </div>

</div>