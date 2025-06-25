<?php
include('conexion.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_ini = $_POST['fecha_ini'];
    $fecha_fin = $_POST['fecha_fin'];
    $porcentaje = floatval($_POST['porcentaje']);
    $contenido = $_POST['contenido'];

    // Validación básica
    if (empty($contenido)) {
        header("Location: crear_promocion.php?error=Debe seleccionar un producto.");
        exit();
    }

    // Verificamos si el producto existe (opcional, por seguridad)
    $verificar = $conn->prepare("SELECT COUNT(*) FROM contenido WHERE nombre_prod = ?");
    $verificar->bind_param("s", $contenido);
    $verificar->execute();
    $verificar->bind_result($existe);
    $verificar->fetch();
    $verificar->close();

    if ($existe == 0) {
        header("Location: crear_promocion.php?error=El producto no existe.");
        exit();
    }

    // Insertamos la promoción
    $stmt = $conn->prepare("INSERT INTO promocion (fecha_ini, fecha_fin, porcentaje, contenido) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $fecha_ini, $fecha_fin, $porcentaje, $contenido);

    if ($stmt->execute()) {
        // Redirigimos con éxito
        header("Location: promociones_admin.php?success=1");
        exit();
    } else {
        header("Location: crear_promocion.php?error=Error al guardar la promoción.");
        exit();
    }
} else {
    header("Location: crear_promocion.php");
    exit();
}
?>
