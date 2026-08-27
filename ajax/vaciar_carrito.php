<?php
//Todo esto pertenece a ajax/vaciar_carrito.php
session_start();

require_once "../controladores/CarritoController.php";

$carrito = new CarritoController();

header('Content-Type: application/json; charset=utf-8');

