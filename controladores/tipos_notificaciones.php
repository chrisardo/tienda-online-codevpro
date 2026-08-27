<?php
//======================================================
// CoDevPro Technology
// controladores/tipos_notificaciones.php
//======================================================


/*======================================================
=            OBTENER INFORMACIÓN DEL TIPO
======================================================*/

function obtenerTipoNotificacion($tipo)
{

    switch ($tipo) {


        /*=========================================
        BIENVENIDA
        =========================================*/

        case "bienvenida":

            return [

                "nombre" => "BIENVENIDA",
                "icono" => "bi-stars",
                "color" => "primary"

            ];


            /*=========================================
        PEDIDO
        =========================================*/

        case "pedido":

            return [

                "nombre" => "PEDIDO",
                "icono" => "bi-bag-check-fill",
                "color" => "success"

            ];


            /*=========================================
        ENVÍO
        =========================================*/

        case "envio":

            return [

                "nombre" => "ENVÍO",
                "icono" => "bi-truck",
                "color" => "primary"

            ];


            /*=========================================
        OFERTA FLASH
        =========================================*/

        case "oferta":

            return [

                "nombre" => "OFERTA FLASH",
                "icono" => "bi-fire",
                "color" => "danger"

            ];


            /*=========================================
        PROMOCIÓN
        =========================================*/

        case "promocion":

            return [

                "nombre" => "PROMOCIÓN",
                "icono" => "bi-percent",
                "color" => "danger"

            ];


            /*=========================================
        PRODUCTO NUEVO
        =========================================*/

        case "producto":

            return [

                "nombre" => "NUEVO PRODUCTO",
                "icono" => "bi-box-seam-fill",
                "color" => "warning"

            ];


            /*=========================================
        SEGURIDAD
        =========================================*/

        case "seguridad":

            return [

                "nombre" => "SEGURIDAD",
                "icono" => "bi-shield-lock-fill",
                "color" => "warning"

            ];


            /*=========================================
        PERFIL
        =========================================*/

        case "perfil":

            return [

                "nombre" => "MI PERFIL",
                "icono" => "bi-person-fill",
                "color" => "info"

            ];


            /*=========================================
        FAVORITOS
        =========================================*/

        case "favorito":

            return [

                "nombre" => "FAVORITOS",
                "icono" => "bi-heart-fill",
                "color" => "danger"

            ];


            /*=========================================
        CARRITO
        =========================================*/

        case "carrito":

            return [

                "nombre" => "CARRITO",
                "icono" => "bi-cart-fill",
                "color" => "success"

            ];


            /*=========================================
        PAGO
        =========================================*/

        case "pago":

            return [

                "nombre" => "PAGO",
                "icono" => "bi-credit-card-fill",
                "color" => "success"

            ];


            /*=========================================
        TESTIMONIO
        =========================================*/

        case "testimonio":

            return [

                "nombre" => "TESTIMONIO",
                "icono" => "bi-chat-square-text-fill",
                "color" => "secondary"

            ];


            /*=========================================
        CUENTA
        =========================================*/

        case "cuenta":

            return [

                "nombre" => "CUENTA",
                "icono" => "bi-person-circle",
                "color" => "primary"

            ];


            /*=========================================
        SISTEMA
        =========================================*/

        case "sistema":

            return [

                "nombre" => "SISTEMA",
                "icono" => "bi-gear-fill",
                "color" => "dark"

            ];


            /*=========================================
        POR DEFECTO
        =========================================*/

        default:

            return [

                "nombre" => "NOTIFICACIÓN",
                "icono" => "bi-bell-fill",
                "color" => "primary"

            ];
    }
}
