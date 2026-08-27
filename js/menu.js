//======================================================
// CoDevPro Technology
// Menú del Administrador
// js/menu.js
//======================================================
//======================================================
// SIDEBAR
//======================================================
const btnSidebar = document.getElementById("btnSidebar");
const btnSidebarMobile = document.getElementById("btnSidebarMobile");
if (btnSidebar) {
  const estadoSidebar = localStorage.getItem("sidebar");

  if (estadoSidebar === "close") {
    document.body.classList.add("sidebar-close");
  }

  btnSidebar.addEventListener("click", () => {
    document.body.classList.toggle("sidebar-close");

    if (document.body.classList.contains("sidebar-close")) {
      localStorage.setItem("sidebar", "close");
    } else {
      localStorage.setItem("sidebar", "open");
    }
  });
}
if (btnSidebarMobile) {
  btnSidebarMobile.addEventListener("click", () => {
    document.body.classList.toggle("sidebar-close");
  });
}
//======================================================
// SUBMENÚ REPORTES
//======================================================

const btnReportes = document.getElementById("btnReportes");

const submenuReportes = document.querySelector(".submenu-reportes");

const iconReportes = document.getElementById("iconReportes");

if (btnReportes) {
  btnReportes.addEventListener("click", (e) => {
    e.preventDefault();

    submenuReportes.classList.toggle("active");

    iconReportes.classList.toggle("rotate");
  });
}
//======================================================
// SUBMENÚ PRODUCTOS
//======================================================

const btnProductos = document.getElementById("btnProductos");

const submenuProductos = document.querySelector(".submenu-productos");

const iconProductos = document.getElementById("iconProductos");

if (btnProductos) {
  btnProductos.addEventListener("click", (e) => {
    e.preventDefault();

    submenuProductos.classList.toggle("active");

    iconProductos.classList.toggle("rotate");
  });
}
//======================================================
// SUBMENÚ CLIENTES
//======================================================

const btnClientes = document.getElementById("btnClientes");

const submenuClientes = document.querySelector(".submenu-clientes");

const iconClientes = document.getElementById("iconClientes");

if (btnClientes) {
  btnClientes.addEventListener("click", (e) => {
    e.preventDefault();

    submenuClientes.classList.toggle("active");

    iconClientes.classList.toggle("rotate");
  });
}
//======================================================
// SUBMENÚ EMPLEADOS
//======================================================

const btnEmpleados = document.getElementById("btnEmpleados");

const submenuEmpleados = document.querySelector(".submenu-empleados");

const iconEmpleados = document.getElementById("iconEmpleados");

if (btnEmpleados) {
  btnEmpleados.addEventListener("click", (e) => {
    e.preventDefault();

    submenuEmpleados.classList.toggle("active");

    iconEmpleados.classList.toggle("rotate");
  });
}
//======================================================
// SUBMENÚ VENTAS
//======================================================

const btnVentas = document.getElementById("btnVentas");

const submenuVentas = document.querySelector(".submenu-ventas");

const iconVentas = document.getElementById("iconVentas");

if (btnVentas) {
  btnVentas.addEventListener("click", (e) => {
    e.preventDefault();

    submenuVentas.classList.toggle("active");

    iconVentas.classList.toggle("rotate");
  });
}
//======================================================
// SUBMENÚ PROVEEDORES
//======================================================

const btnProveedores = document.getElementById("btnProveedores");

const submenuProveedores = document.querySelector(".submenu-proveedores");

const iconProveedores = document.getElementById("iconProveedores");

if (btnProveedores) {
  btnProveedores.addEventListener("click", (e) => {
    e.preventDefault();

    submenuProveedores.classList.toggle("active");

    iconProveedores.classList.toggle("rotate");
  });
}
//======================================================
// SUBMENÚ CONFIGURACIÓN
//======================================================

const btnConfiguracion = document.getElementById("btnConfiguracion");

const submenuConfiguracion = document.querySelector(".submenu-configuracion");

const iconConfiguracion = document.getElementById("iconConfiguracion");

if (btnConfiguracion) {
  btnConfiguracion.addEventListener("click", (e) => {
    e.preventDefault();

    submenuConfiguracion.classList.toggle("active");

    iconConfiguracion.classList.toggle("rotate");
  });
}
//======================================================
// SUBMENÚ CONTABILIDAD
//======================================================

const btnContabilidad = document.getElementById("btnContabilidad");

const submenuContabilidad = document.querySelector(".submenu-contabilidad");

const iconContabilidad = document.getElementById("iconContabilidad");

if (btnContabilidad) {
  btnContabilidad.addEventListener("click", (e) => {
    e.preventDefault();

    if (submenuContabilidad) {
      submenuContabilidad.classList.toggle("active");
    }

    if (iconContabilidad) {
      iconContabilidad.classList.toggle("rotate");
    }
  });
}
