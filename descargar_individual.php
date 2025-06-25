<?php
session_start();
include 'conexion.php';


if (!isset($_SESSION['correo']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: coleccion.php');
    exit();
}

$correo = $_SESSION['correo'];
$nombre_prod = $_POST['contenido'] ?? '';


if (empty($nombre_prod)) {
    echo "<script>alert('Producto inválido.'); window.location='coleccion.php';</script>";
    exit();
}


//FUB-16: obtener_tipo_y_ruta_producto(nombre_prod VARCHAR(255)) Consulta el tipo y ruta del archivo de un producto específico, validando su existencia.
$stmt = $conn->prepare("SELECT t.tipo, c.ruta_archivo FROM contenido c 
                        JOIN tipo_archivo t ON c.tipo_archivo_id = t.id
                        WHERE c.nombre_prod = ?");
$stmt->bind_param("s", $nombre_prod);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<script>alert('Producto no encontrado.'); window.location='coleccion.php';</script>";
    exit();
}

$producto = $res->fetch_assoc();
$tipo = $producto['tipo'];
$ruta = $producto['ruta_archivo'];


if (!in_array($tipo, ['imagen', 'video', 'audio'])) {
    echo "<script>alert('Este tipo de producto no puede ser descargado.'); window.location='coleccion.php';</script>";
    exit();
}


if (!file_exists($ruta)) {
    echo "<script>alert('Archivo no encontrado en el servidor.'); window.location='coleccion.php';</script>";
    exit();
}


//PRO-32: marcar_producto_como_descargado(correo VARCHAR(255), contenido VARCHAR(255))Actualiza el estado del producto a 'descargado' en la tabla descarga para el usuario.
$stmt = $conn->prepare("UPDATE descarga SET estado = 'descargado' WHERE correo_usuario = ? AND contenido = ?");
$stmt->bind_param("ss", $correo, $nombre_prod);
$stmt->execute();


header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($ruta) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($ruta));
readfile($ruta);
exit();
