<?php
include 'conexion.php'; // Incluye el archivo de conexión a la base de datos

// FUN-008: Validación de campos obligatorios del formulario de registro
if (
    empty($_POST['nombre']) ||
    empty($_POST['correo']) ||
    empty($_POST['numero']) ||
    empty($_POST['contraseña']) ||
    !isset($_POST['terminos'])
) {
    // Si falta algún campo o no se aceptan los términos, muestra alerta y regresa
    echo "<script>alert('Por favor, complete todos los campos y acepte los términos.'); window.history.back();</script>";
    exit;
}

// FUN-009: Obtención de datos del formulario
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$numero = $_POST['numero'];
$contraseña = $_POST['contraseña'];
$novedad = 0; // Valor por defecto para novedad
$gasto = 0; // Valor por defecto para gasto

// FUN-010: Consulta preparada para insertar el nuevo usuario en la base de datos
$sql = "INSERT INTO usuario (correo, nombre, contraseña, tipo_cli, saldo, admin, foto_perfil, numero, novedad, gasto) 
        VALUES (?, ?, ?, 'cliente', 0, 0, '', ?, 0, 0)";

$stmt = $conn->prepare($sql); // Prepara la consulta
$stmt->bind_param("sssi", $correo, $nombre, $contraseña, $numero);

// FUN-011: Ejecución de la consulta y manejo del resultado
if ($stmt->execute()) {
    // Si el registro es exitoso, muestra una ventana modal de éxito
    echo '
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal {
            width: 300px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .modal img {
            width: 60px;
            margin-top: 15px;
        }
        .cerrar {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 18px;
            cursor: pointer;
        }
    </style>
    <div class="modal">
        <div class="cerrar" onclick="window.location.href=\'index.php\'">&times;</div>
        <h2>¡Registro completo!</h2>
        <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" alt="Check">
    </div>
    ';
} else {
    // Si hay error en el registro, muestra alerta
    echo "<script>alert('Error al registrar el usuario'); window.history.back();</script>";
}

$stmt->close(); // FUN-012: Cierra la consulta preparada
$conn->close(); // FUN-013: Cierra la conexión a la base de datos
?>
