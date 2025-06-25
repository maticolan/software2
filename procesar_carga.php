<?php
session_start();
include 'conexion.php';


if (!isset($_SESSION['correo'])) {
    echo "<script>alert('Debes iniciar sesión para realizar una recarga'); window.location='login.php';</script>";
    exit();
}

$correo = $_SESSION['correo'];
$monto = $_POST['monto'] ?? '';
$metodo = $_POST['metodo_pago'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$tarjeta = $_POST['numero_tarjeta'] ?? '';
$cvv = $_POST['cvv'] ?? '';
$fecha = $_POST['fecha_vencimiento'] ?? '';

$montos_validos = [15, 30, 45, 100, 150];
$monto = isset($_POST['monto']) ? (int)$_POST['monto'] : 0;

if (!in_array($monto, $montos_validos)) {
    echo "<script>alert('Monto inválido'); window.location='cargar_saldo.php';</script>";
    exit();
}


$metodos_validos = ['yape', 'plin', 'pagoefectivo', 'tarjeta'];
if (!in_array($metodo, $metodos_validos)) {
    echo "<script>alert('Método de pago inválido'); window.location='cargar_saldo.php';</script>";
    exit();
}


if (in_array($metodo, ['yape', 'plin', 'pagoefectivo'])) {
    if (!preg_match('/^\d{9}$/', $telefono)) {
        echo "<script>alert('Número de teléfono no válido. Debe tener 9 dígitos'); window.location='cargar_saldo.php';</script>";
        exit();
    }
} elseif ($metodo === 'tarjeta') {
    if (!preg_match('/^\d{16}$/', $tarjeta) || !preg_match('/^\d{3}$/', $cvv) || empty($fecha)) {
        echo "<script>alert('Datos de tarjeta inválidos'); window.location='cargar_saldo.php';</script>";
        exit();
    }
}


$fechaHoy = date('Y-m-d');
$insertF = $conn->prepare("INSERT INTO finanza (correo, fecha, monto, completado, tipo) VALUES (?, ?, ?, 1, 's')");
$insertF->bind_param("ssd", $correo, $fechaHoy, $monto);
$insertF->execute();

$stmt = $conn->prepare("SELECT saldo FROM usuario WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "<script>alert('Usuario no encontrado'); window.location='cargar_saldo.php';</script>";
    exit();
}

$usuario = $resultado->fetch_assoc();
$nuevo_saldo = $usuario['saldo'] + $monto;

/*
FUN-091: Actualizar saldo del usuario
Actualiza el saldo del usuario en la base de datos con el nuevo monto recargado.
*/
$stmt = $conn->prepare("UPDATE usuario SET saldo = ? WHERE correo = ?");
$stmt->bind_param("ds", $nuevo_saldo, $correo);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "<script>alert('Recarga exitosa. Nuevo saldo: $nuevo_saldo'); window.location='perfil.php';</script>";
} else {
    echo "<script>alert('Error al actualizar saldo'); window.location='cargar_saldo.php';</script>";
}

$stmt->close();
?>
