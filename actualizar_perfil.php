<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

$correo = $_SESSION['correo'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nuevo_nombre = $_POST['nombre'] ?? '';
    $nuevo_numero = $_POST['numero'] ?? '';
    $nueva_contraseña = $_POST['contraseña'] ?? '';

    if (empty($nuevo_nombre) || empty($nuevo_numero)) {
        echo "<script>alert('Nombre y número son obligatorios'); window.history.back();</script>";
        exit();
    }

    if (!empty($nueva_contraseña)) {
// PRO-03: actualizar_perfil_con_contraseña (IN correo_in VARCHAR(100), IN nombre_in VARCHAR(100), IN numero_in INT, 
// IN contraseña_in VARCHAR(100)): Actualiza los datos de un usuario incluyendo su contraseña
// 1. Se abre una llamada al procedimiento almacenado UpdateUsuario con 4 parámetros y se almacenará en la variavle $stmt.
// 2. Tipos de parámtros:
//    s = string, i = integer
// 3. Se ejecuta el procedimiento almacenado pasando los valores proporcionados por el usuario mediante execute().
// 4. Buscar en la tabla usuario el registro cuyo correo sea igual a correo_in.
//    Una vez ejecutado este bloque, hay 2 posibles resultados:
//    - Si existe, actualizará:
//      nombre -> nombre_in
//      numero -> numero_in
//      contraseña -> contraseña_in
//    - Si no existe, no realizará cambios.
        $stmt = $conn->prepare("CALL UpdateUsuario(?, ?, ?, ?)");
        $stmt->bind_param("siss", $nuevo_nombre, $nuevo_numero, $nueva_contraseña, $correo);
    } else {
// PRO-04: actualizar_perfil_con_contraseña (IN correo_in VARCHAR(100), IN nombre_in VARCHAR(100), IN numero_in INT): 
// Actualiza los datos de un usuario sin incluir su contraseña
// 1. Se prepara el llamado al procedimiento almacenado UpdateUsuarioNombreNumero con 3 parámetros y se almacena en la variable $stmt.
// 2. Tipos de parámetros:
//    - s = string, i = integer
// 3. El procedimiento almacenado realiza la actualización de los campos nombre y numero en la tabla usuario, buscando el registro por el correo recibido.
// 4. Buscar en la tabla usuario el registro cuyo correo sea igual a correo_in.
//    Una vez ejecutado este bloque, hay 2 posibles resultados:
//    - Si existe, actualizará:
//      nombre -> nombre_in
//      numero -> numero_in
//    - Si no existe, no realizará cambios.
        $stmt = $conn->prepare("CALL UpdateUsuarioNombreNumero(?, ?, ?)");
        $stmt->bind_param("sis", $nuevo_nombre, $nuevo_numero, $correo);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Perfil actualizado correctamente'); window.location='perfil.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar perfil'); window.history.back();</script>";
    }

    $stmt->close();
} else {
    header("Location: perfil.php");
    exit();
}
?>
