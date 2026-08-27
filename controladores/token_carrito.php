<?php
//Todo esta parte es de controladores/token_carrito.php
function obtenerTokenCarrito()
{
    if (isset($_COOKIE["carrito_token"]) && $_COOKIE["carrito_token"] != "") {
        return $_COOKIE["carrito_token"];
    }

    $token = bin2hex(random_bytes(32));

    setcookie(
        "carrito_token",
        $token,
        time() + (60 * 60 * 24 * 30),
        "/",
        "",
        false,
        true
    );

    return $token;
}