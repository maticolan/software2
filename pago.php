<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['correo'])) {
    echo "<script>alert('Debes iniciar sesión para continuar'); window.location='login.php';</script>";
    exit();
}

$correo = $_SESSION['correo'];
$total = $_SESSION['total_compra'] ?? 0;
$es_regalo = $_SESSION['regalo'] ?? false;
$correo_receptor = $_SESSION['correo_receptor'] ?? null;
$fechaHoy = date('Y-m-d');

if ($total <= 0) {
    echo "<script>alert('No hay monto pendiente de pago'); window.location='carrito.php';</script>";
    exit();
}


//FUW-22: generarOperacionID(): genera de manera aleatoria el número para la operación que se acaba de realizar
function generarOperacionID() {
    return 'OP' . strtoupper(bin2hex(random_bytes(4)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pago']) && isset($_POST['metodo_pago'])) {
    $metodo = $_POST['metodo_pago'];
    $operacionID = generarOperacionID();

    // Insertar en tabla finanza

    //FUB-41: insertar_finanza(correo VARCHAR(255), fecha DATE, monto DECIMAL(10,2)) Inserta el registro de la transacción financiera al confirmar el pago.
    $insertF = $conn->prepare("INSERT INTO finanza (correo, fecha, monto, completado, tipo) VALUES (?, ?, ?, 1, 'c')");
    $insertF->bind_param("ssd", $correo, $fechaHoy, $total);
    $insertF->execute();

    // Obtener productos
    //FUB-42: obtener_productos_carrito(correo_usuario VARCHAR(255)) Recupera todos los productos que el usuario tiene actualmente en su carrito de compras.
    $stmtProd = $conn->prepare("SELECT nombre_prod FROM carrito WHERE correo_usuario = ?");
    $stmtProd->bind_param("s", $correo);
    $stmtProd->execute();
    $productos = $stmtProd->get_result();

    if ($es_regalo && $correo_receptor) {
        //FUB-43: insertar_regalo(correo_emisor VARCHAR(255), correo_receptor VARCHAR(255), nombre_prod VARCHAR(255)) Inserta el registro del regalo cuando el usuario compra para otra persona.
        $insert = $conn->prepare("INSERT INTO regalo (correo_emisor, correo_receptor, nombre_prod) VALUES (?, ?, ?)");
        $insertDescarga = $conn->prepare("INSERT INTO descarga (correo_usuario, contenido, monto, estado, nota) VALUES (?, ?, ?, 'pendiente', '')");
        while ($row = $productos->fetch_assoc()) {
            $insert->bind_param("sss", $correo, $correo_receptor, $row['nombre_prod']);
            $insert->execute();

            $insertDescarga->bind_param("ssd", $correo_receptor, $row['nombre_prod'], $total);
            $insertDescarga->execute();
        }
        //FUB-44: actualizar_novedad_receptor(correo_receptor VARCHAR(255)) Marca al receptor como pendiente de novedad (tiene un regalo disponible).
        $conn->query("UPDATE usuario SET novedad = 1 WHERE correo = '$correo_receptor'");
    } else {
        //FUB-45: insertar_descarga(correo_usuario VARCHAR(255), contenido VARCHAR(255), monto DECIMAL(10,2)) Registra el contenido adquirido para que esté disponible en la colección del usuario.
        $insert = $conn->prepare("INSERT INTO descarga (correo_usuario, contenido, monto, estado, nota) VALUES (?, ?, ?, 'pendiente', '')");
        while ($row = $productos->fetch_assoc()) {
            $insert->bind_param("ssd", $correo, $row['nombre_prod'], $total);
            $insert->execute();
        }
    }

    //FUB-46: eliminar_carrito_usuario(correo_usuario VARCHAR(255)) Elimina todos los productos del carrito del usuario una vez que la compra ha sido procesada.
    $del = $conn->prepare("DELETE FROM carrito WHERE correo_usuario = ?");
    $del->bind_param("s", $correo);
    $del->execute();

    unset($_SESSION['total_compra'], $_SESSION['regalo'], $_SESSION['correo_receptor']);

    echo "<script>alert('Compra exitosa. ID de Operación: $operacionID'); window.location='coleccion.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pasarela de Pago | MediaShop</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; text-align: center; background: #f8f8f8; }
        .pago-box { background: #fff; padding: 30px; border-radius: 10px; display: inline-block; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; }
        .metodo-form { display: none; margin-top: 20px; text-align: left; }
        .metodo-form input { display: block; margin: 10px auto; padding: 10px; width: 80%; }
        .boton { padding: 12px 20px; background: #6a0dad; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
    </style>
    <script>

        //FUW-23: mostrarFormulario(metodo) muestra el formulario correspondiente al método de pago que realizaremos
        function mostrarFormulario(metodo) {
            document.querySelectorAll('.metodo-form').forEach(form => form.style.display = 'none');
            document.getElementById(metodo).style.display = 'block';
            document.getElementById('metodo_pago').value = metodo;
        }
    </script>
</head>
<body>
<div class="pago-box">
    <h2>Pago pendiente</h2>
    <p>Total a pagar: <strong>$<?= number_format($total, 2) ?></strong></p>

    <h3>Método de Pago</h3>
    <button onclick="mostrarFormulario('yape')">Yape</button>
    <button onclick="mostrarFormulario('plin')">Plin</button>
    <button onclick="mostrarFormulario('tarjeta')">Tarjeta</button>
    <button onclick="mostrarFormulario('efectivo')">Pago Efectivo</button>

    <form method="POST">
        <input type="hidden" name="metodo_pago" id="metodo_pago" required>

        <div class="metodo-form" id="yape">
    <label>Número de celular (máx 9 dígitos):</label>
    <input type="text" name="numero_yape" maxlength="9">
</div>

<div class="metodo-form" id="plin">
    <label>Número de celular (máx 9 dígitos):</label>
    <input type="text" name="numero_plin" maxlength="9">
</div>

<div class="metodo-form" id="tarjeta">
    <label>Número de tarjeta (16 dígitos):</label>
    <input type="text" name="tarjeta" maxlength="16">
    <label>Nombre en tarjeta:</label>
    <input type="text" name="nombre_tarjeta" maxlength="50">
    <label>Fecha de expiración:</label>
    <input type="text" name="exp" placeholder="MM/AA" maxlength="5">
    <label>CVV:</label>
    <input type="text" name="cvv" maxlength="3">
</div>

<div class="metodo-form" id="efectivo">
    <label>Código de operación (estético):</label>
    <input type="text" name="codigo_efectivo" maxlength="10">
</div>


        <button type="submit" name="confirmar_pago" class="boton">Confirmar pago</button>
    </form>
</div>
</body>
</html>
