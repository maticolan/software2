<?php
include('conexion.php');
session_start();

/*
FUN-076: Verificar si el usuario ha iniciado sesión
Si no hay sesión activa, redirige al login.
*/
if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

$correo = $_SESSION['correo'];

/*
FUN-077: Obtener datos del usuario desde la base de datos
Consulta la base de datos para obtener los datos del usuario logueado y mostrarlos en el perfil.
*/
$sql = "SELECT correo, nombre, numero, saldo, contraseña FROM usuario WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
} else {
    echo "Usuario no encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil - MediaShop</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #ffffff;
            color: #333;
        }

        .top-bar {
            background-color: #fff;
            color: #777;
            padding: 10px 0;
            text-align: center;
            font-size: 14px;
            display: flex;
            justify-content: space-around;
            border-bottom: 1px solid #ddd;
        }

        .main-bar {
            background-color: #e0e0e0;
            color: #333;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .main-bar h1 {
            font-size: 28px;
        }

        .main-bar .icons {
            display: flex;
            gap: 20px;
        }

        .main-bar .icons i {
            font-size: 20px;
            cursor: pointer;
        }

        .perfil-container {
            padding: 40px;
            text-align: center;
        }

        .perfil-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .perfil-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            width: 60%;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .perfil-form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .perfil-form input[type="password"] {
            font-size: 16px;
        }

        .perfil-form button {
            padding: 12px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        .perfil-form button:hover {
            background-color: #45a049;
        }

        .footer {
            background-color: #e0e0e0;
            padding: 40px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            color: #333;
        }

        .footer-section {
            margin: 20px;
        }

        .footer-section h4 {
            margin-bottom: 15px;
        }

        .footer-section i {
            margin-right: 8px;
        }

        .copy-bar {
            background-color: #111;
            color: white;
            text-align: center;
            padding: 10px;
        }

        /* Estilos del modal */
        #modalCerrarCuenta {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .modal-contenido {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
        }

        .modal-contenido button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin: 10px 5px;
            cursor: pointer;
        }

        .modal-contenido button.confirmar {
            background-color: #e63946;
            color: white;
        }

        .modal-contenido button.cancelar {
            background-color: #ccc;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Barra blanca -->
<div class="top-bar">
    <span>Entrega a tiempo garantizada</span>
    <span>Atención ágil y eficiente</span>
    <span>100% original</span>
    <span>De confianza</span>
</div>

<!-- Barra gris -->
<div class="main-bar">
    <h1>MediaShop</h1>
    <div class="icons">
        <i class="fas fa-search" onclick="window.location='tienda.php'"></i>
        <i class="fas fa-user" onclick="window.location='perfil.php'"></i>
        <i class="fas fa-heart" onclick="window.location='deseos.php'"></i>
        <i class="fas fa-shopping-cart" onclick="window.location='carrito.php'"></i>
        <i class="fas fa-download" onclick="window.location='coleccion.php'"></i>
    </div>
</div>

<!-- Formulario -->
<div class="perfil-container">
    <h2>Mi Perfil</h2>
    <form class="perfil-form" action="actualizar_perfil.php" method="POST">
      <input type="text" name="nombre" placeholder="Nombre" value="<?php echo $usuario['nombre']; ?>" required>
        <input type="email" name="correo" placeholder="Correo electrónico" value="<?php echo $usuario['correo']; ?>" required disabled>
        <input type="text" name="numero" placeholder="Número de celular" value="<?php echo $usuario['numero']; ?>" required>
        <input type="password" name="contraseña" placeholder="Nueva contraseña (opcional)">
        <input type="text" name="saldo" value="Saldo: <?php echo $usuario['saldo']; ?>" disabled>
        <div>
            <button type="submit" name="guardar">Guardar cambios</button>
            <button type="button" onclick="abrirModalSaldo()">Cargar saldo</button>
            <button type="button" onclick="abrirModal()">Borrar cuenta</button>
        </div>
    </form>

</div>

<!-- Modal de Confirmación -->
<div id="modalCerrarCuenta">
    <div class="modal-contenido">
        <h3>¿Estás seguro de cerrar tu cuenta?</h3>
        <p><input type="checkbox" id="confirmarCierre"> Sí, deseo cerrar mi cuenta.</p>
        <button class="confirmar" onclick="cerrarCuenta()">Confirmar</button>
        <button class="cancelar" onclick="cerrarModal()">Cancelar</button>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <div class="footer-section">
        <h4>MediaShop</h4>
        <div>
            <i class="fab fa-facebook"></i>
            <i class="fab fa-twitter"></i>
            <i class="fab fa-instagram"></i>
        </div>
    </div>
    <div class="footer-section">
        <h4>Métodos de pago</h4>
        <div><i class="fab fa-cc-visa"></i> Visa</div>
        <div><i class="fab fa-cc-mastercard"></i> Mastercard</div>
        <div><i class="fas fa-mobile-alt"></i> Yape</div>
        <div><i class="fab fa-cc-amex"></i> American Express</div>
    </div>
    <div class="footer-section">
        <h4>Ayuda</h4>
        <div>Servicio al Cliente</div>
        <div>Preguntas Frecuentes</div>
        <div>Términos y Condiciones</div>
    </div>
    <div class="footer-section">
        <h4>Sobre nosotros</h4>
        <div>MediaShop.com</div>
    </div>
    <div class="footer-section">
        <h4>Contáctanos</h4>
        <div><i class="fas fa-phone"></i> +51 999-999-999</div>
        <div><i class="fas fa-envelope"></i> mediashop@gmail.com</div>
    </div>
</div>

<div class="copy-bar">
    © 2025 MediaShop - Todos los derechos reservados
</div>

<script>

//FUW-24: abrirModal(): abre una ventana para confirmar que el usuario quiere descativar su cuenta
function abrirModal() {
    document.getElementById('modalCerrarCuenta').style.display = 'flex';
}

//FUW-15: cerrarModal(): cierra la ventana de FUW-24
function cerrarModal() {
    document.getElementById('modalCerrarCuenta').style.display = 'none';
}

//FUW-26: cerrarCuenta(): abre una ventana para confirmar que el usuario quiere descativar su cuenta
function cerrarCuenta() {
    if (!document.getElementById('confirmarCierre').checked) {
        alert("Debes confirmar marcando la casilla.");
        return;
    }
    window.location.href = "cerrar_cuenta.php";
}

//FUW-27: abrirModalSaldo(): abre una ventana en la que el usuario hará recarga de saldo
function abrirModalSaldo() {
    document.getElementById('modalCargarSaldo').style.display = 'flex';
}

//FUW-28: cerrarModalSaldo(): cierra la ventana de FUW-28
function cerrarModalSaldo() {
    document.getElementById('modalCargarSaldo').style.display = 'none';
    document.getElementById('formCelular').style.display = 'none';
    document.getElementById('formTarjeta').style.display = 'none';
}

//FUW-29: mostrarFormulario(pago): muestra al usuario los diferentes métodos de realizar el pago de su recarga
function mostrarFormularioPago(metodo) {
    document.getElementById('formCelular').style.display = (metodo === 'yape' || metodo === 'plin' || metodo === 'pago_efectivo') ? 'block' : 'none';
    document.getElementById('formTarjeta').style.display = (metodo === 'tarjeta') ? 'block' : 'none';
}

</script>

<!-- Modal de Carga de Saldo -->
<div id="modalCargarSaldo" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:999;">
    <div style="background:white; padding:30px; border-radius:10px; width:400px; text-align:center;">
        <h3>Cargar saldo</h3>
        <form method="POST" action="procesar_carga.php">
            <label for="monto">Selecciona un monto:</label><br>
            <select name="monto" required>
                <option value="">-- Selecciona --</option>
                <option value="15">15 USD</option>
                <option value="30">30 USD</option>
                <option value="45">45 USD</option>
                <option value="100">100 USD</option>
                <option value="150">150 USD</option>
            </select><br><br>

            <label>Método de pago:</label><br>
            <select name="metodo_pago" required onchange="mostrarFormularioPago(this.value)">
                <option value="">-- Selecciona --</option>
                <option value="yape">Yape</option>
                <option value="plin">Plin</option>
                <option value="tarjeta">Tarjeta</option>
            </select><br><br>

            <div id="formCelular" style="display:none;">
                <input type="text" name="telefono" placeholder="Número de celular" maxlength="9">
            </div>

            <div id="formTarjeta" style="display:none;">
                <input type="text" name="numero_tarjeta" placeholder="Número de tarjeta"><br><br>
                <input type="text" name="nombre_tarjeta" placeholder="Nombre del titular"><br><br>
                <input type="text" name="fecha_vencimiento" placeholder="MM/AA"><br><br>
                <input type="text" name="cvv" placeholder="CVV" maxlength="3"><br>
            </div><br>

            <button type="submit">Confirmar recarga</button>
            <button type="button" onclick="cerrarModalSaldo()">Cancelar</button>
        </form>
    </div>
</div>



</body>
</html>


