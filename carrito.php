<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['correo'])) {
    echo "<script>alert('Debes iniciar sesión para acceder al carrito'); window.location='login.php';</script>";
    exit();
}

$correo = $_SESSION['correo'];
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $nombreEliminar = $_POST['nombre_prod'] ?? '';
    if (!empty($nombreEliminar)) {
// PRO-24: 
        $stmt = $conn->prepare("CALL DeleteProductoDelCarrito(?, ?)");
        $stmt->bind_param("ss", $correo, $nombreEliminar);
        $stmt->execute();
        header('Location: carrito.php');
        exit();
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_directo'])) {
// PRO-25: 
    $stmtVerificarDescarga =$conn->prepare("CALL VerificarDescargaPorUsuario(?, ?)");
    $stmtVerificarDescarga->bind_param("ss", $correo, $nombre_prod);  // $nombre_prod es el nombre del producto
    $stmtVerificarDescarga->execute();
    $resultVerificarDescarga = $stmtVerificarDescarga->get_result();
    $stmtVerificarDescarga->close();

    // Si no está en la tabla descarga, procedemos con la transacción normal
// PRO-26: 
    $stmt = $conn->prepare("CALL GetCarritoPorUsuario(?)");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $productos = [];
    $total = 0;
    $totalDescuento = 0;
    while ($row = $result->fetch_assoc()) {
        // Verificar si el producto ya ha sido comprado antes de continuar con la compra
        $stmtVerificarDescarga->bind_param("ss", $correo, $row['nombre_prod']);
        $stmtVerificarDescarga->execute();
        $resultVerificarDescarga = $stmtVerificarDescarga->get_result();
        $stmtVerificarDescarga->close();

        // Si el producto ya ha sido comprado, mostramos un mensaje y redirigimos al carrito
    if ($resultVerificarDescarga->num_rows > 0) {
        echo "<script>alert('El producto " . htmlspecialchars($row['nombre_prod']) . " ya ha sido comprado.'); window.location='carrito.php';</script>";
        return; // Continuamos el script después de mostrar el mensaje
    }
        $precio_original = $row['precio'];
        $descuento = isset($row['porcentaje']) ? $row['porcentaje'] : 0;
        if ($descuento > 0) {
            $precio_con_descuento = $precio_original - ($precio_original * ($descuento / 100));
        } else {
            $precio_con_descuento = $precio_original; // Si no hay descuento, el precio es el original
        }
        $row['precio'] = $precio_con_descuento;
        $productos[] = $row;
        $total += $precio_con_descuento;
    }

// PRO-27: 
    $stmtGasto = $conn->prepare("CALL GetGastoUsuario(?)");
    $stmtGasto->bind_param("s", $correo);
    $stmtGasto->execute();
    $resultadoGasto = $stmtGasto->get_result();
    if($usuarioGasto = $resultadoGasto->fetch_assoc() > 20) {
        $totalDescuento = $total - ($total * (15 / 100));
    }
    else {
        $totalDescuento = $total; // Si no hay gasto previo, el total es el original
    }
    $stmtGasto->close();

// PRO-28: 
    $stmtSaldo = $conn->prepare("CALL GetSaldoUsuario(?)");
    $stmtSaldo->bind_param("s", $correo);
    $stmtSaldo->execute();
    $resultadoSaldo = $stmtSaldo->get_result();
    $usuario = $resultadoSaldo->fetch_assoc();
    $stmtSaldo->close();

    if ($usuario && $usuario['saldo'] >= $totalDescuento) {
        $nuevoSaldo = $usuario['saldo'] - $totalDescuento;
// PRO-29: 
        $updateSaldo = $conn->prepare("CALL UpdateSaldoYGasto(?, ?, ?)");
        $updateSaldo->bind_param("dss", $nuevoSaldo, $totalDescuento, $correo);
        $updateSaldo->execute();
        $updateSaldo->close(); 

// PRO-30: 
        $fechaHoy = date('Y-m-d');
        $insertF = $conn->prepare("CALL InsertarTransaccionFinanza(?, ?, ?)");
        $insertF->bind_param("ssd", $correo, $fechaHoy, $totalDescuento);
        $insertF->execute();
        $insertF->close();

// PRO-31: 
        $insertDescarga = $conn->prepare("CALL InsertarDescarga(?, ?, ?)");
        foreach ($productos as $producto) {
            $insertDescarga->bind_param("ssd", $correo, $producto['nombre_prod'], $producto['precio']);
            $insertDescarga->execute();
            $insertDescarga->close();
        }

// PRO-32: 
        $del = $conn->prepare("CALL DeleteCarritoPorUsuario(?)");
        $del->bind_param("s", $correo);
        $del->execute();
        $del->close();

        echo "<script>alert('Compra realizada con éxito. Puedes descargar tus productos en Colección.'); window.location='coleccion.php';</script>";
        exit();
    } else {
        $_SESSION['regalo'] = false;
        $_SESSION['total_compra'] = $totalDescuento;
        header("Location: pago.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_regalo'])) {
    $correo_destinatario = $_POST['correo_destinatario'] ?? '';

    if (empty($correo_destinatario)) {
        echo "<script>alert('Selecciona un destinatario.');</script>";
    } else {
// PRO-33: 
        $stmt = $conn->prepare("CALL GetProductosEnCarrito(?)");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        $productos = [];
        $total = 0;
        while ($row = $res->fetch_assoc()) {
            $precio_original = $row['precio'];
            $descuento = isset($row['porcentaje']) ? $row['porcentaje'] : 0;
            
            // Aplicar descuento si existe
            if ($descuento > 0) {
                $precio_con_descuento = $precio_original - ($precio_original * ($descuento / 100));
            } else {
                $precio_con_descuento = $precio_original; // Si no hay descuento, el precio es el original
            }

            // Actualizar el precio con descuento para el producto
            $row['precio'] = $precio_con_descuento; // Usamos el precio con descuento

            // Añadir el producto a la lista y sumar el total
            $productos[] = $row;
            $total += $precio_con_descuento;
        }

        foreach ($productos as $producto) {
// PRO-25: Verificar si el destinatario ya tiene el producto
            $stmtVerificarDescargaDestinatario = $conn->prepare("CALL VerificarDescargaPorUsuario(?, ?)");
            $stmtVerificarDescargaDestinatario->bind_param("ss", $correo_destinatario, $producto['nombre_prod']);
            $stmtVerificarDescargaDestinatario->execute();
            $resultVerificarDescargaDestinatario = $stmtVerificarDescargaDestinatario->get_result();

            if ($resultVerificarDescargaDestinatario->num_rows > 0) {
                echo "<script>alert('El destinatario ya tiene el producto " . htmlspecialchars($producto['nombre_prod']) . ".'); window.location='carrito.php';</script>";
                exit(); // Salir si algún producto ya fue regalado
            }
        }
// PRO-27: Verificar gasto del usuario
        $stmtGasto = $conn->prepare("CALL GetGastoUsuario(?)");
        $stmtGasto->bind_param("s", $correo);
        $stmtGasto->execute();
        $resultadoGasto = $stmtGasto->get_result();
        $stmtGasto->close();
        if($usuarioGasto = $resultadoGasto->fetch_assoc() > 20) {
            $totalDescuento = $total - ($total * (15 / 100));
        }
        else {
            $totalDescuento = $total; // Si no hay gasto previo, el total es el original
        }

//PRO-28: Devuelve el saldo del usuario
        $stmtSaldo = $conn->prepare("CALL GetSaldoUsuario(?)");
        $stmtSaldo->bind_param("s", $correo);
        $stmtSaldo->execute();
        $resSaldo = $stmtSaldo->get_result();
        $usuario = $resSaldo->fetch_assoc();
        $stmtSaldo->close();

        if ($usuario && $usuario['saldo'] >= $totalDescuento) {
            $nuevoSaldo = $usuario['saldo'] - $totalDescuento;
// PRO-29: Actualizar saldo y gasto del usuario
            $updateSaldo = $conn->prepare("CALL UpdateSaldoYGasto(?, ?, ?)");
            $updateSaldo->bind_param("dss", $nuevoSaldo, $totalDescuento, $correo);
            $updateSaldo->execute();
            $updateSaldo->close();

// PRO-30: Registrar transacción financiera
            $fechaHoy = date('Y-m-d');
            $insertF = $conn->prepare("CALL InsertarTransaccionFinanza(?, ?, ?)");
            $insertF->bind_param("ssd", $correo, $fechaHoy, $totalDescuento);
            $insertF->execute();
            $insertF->close();

// PRO-31: Registrar descarga de productos 
            $descarga = $conn->prepare("CALL InsertarDescarga(?, ?, ?)");
// PRO-34: 
            $regalo = $conn->prepare("CALL InsertarRegalo(?, ?, ?)");

            foreach ($productos as $p) {
                $nombre_prod = $p['nombre_prod'];
                $precio = $p['precio'];

                $descarga->bind_param("ssd", $correo_destinatario, $nombre_prod, $precio);
                $descarga->execute();
                $descarga->close();

                $regalo->bind_param("sss", $correo, $correo_destinatario, $nombre_prod);
                $regalo->execute();
                $regalo->close();
            }

// PRO-32: Eliminar productos del carrito
            $del = $conn->prepare("CALL DeleteCarritoPorUsuario(?)");
            $del->bind_param("s", $correo);
            $del->execute();

// PRO-35: 
            $updateNovedad =$conn->prepare("CALL ActualizarNovedadUsuario(?)");
            $updateNovedad->bind_param("s", $correo_destinatario);
            $updateNovedad->execute();
            $updateNovedad->close();

            echo "<script>alert('¡Regalo enviado con éxito!'); window.location='tienda.php';</script>";
            exit();
        } else {
            echo "<script>alert('Saldo insuficiente para realizar el regalo.');</script>";
        }
    }
}

// PRO-26: Consultar productos del carrito
$stmt = $conn->prepare("CALL GetCarritoPorUsuario(?)");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// PRO-27: Consultar gasto del usuario
$stmtGasto = $conn->prepare("CALL GetGastoUsuario(?)");
$stmtGasto->bind_param("s", $correo);
$stmtGasto->execute();
$resultadoGasto = $stmtGasto->get_result();
$stmtGasto->close();

$productos = [];
$total = 0;
$totalDescuento = 0;
while ($row = $result->fetch_assoc()) {
    $precio_original = $row['precio'];
    $descuento = isset($row['porcentaje']) ? $row['porcentaje'] : 0;
    if ($descuento > 0) {
        $precio_con_descuento = $precio_original - ($precio_original * ($descuento / 100));
    } else {
        $precio_con_descuento = $precio_original; // Si no hay descuento, el precio es el original
    }

    $productos[] = [
        'nombre_prod' => $row['nombre_prod'],
        'precio' => $precio_original,
        'ruta_preview' => $row['ruta_preview'],
        'tipo' => $row['tipo'],
        'descuento' => $descuento,
        'precio_con_descuento' => $precio_con_descuento
    ];

    // Sumar al total
    $total += $precio_con_descuento;
}
if($usuarioGasto = $resultadoGasto->fetch_assoc() > 20) {
    $totalDescuento = $total - ($total * (15 / 100));
}
else {
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito | MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { 
            box-sizing: border-box; 
            font-family: Arial, sans-serif;
            margin: 0; padding: 0; 
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
            justify-content: space-between;
            align-items: center;
        }

        .main-bar .icons i {
            font-size: 20px;
            margin-left: 20px;
            cursor: pointer;
        }

        .main-bar h1 {
            font-size: 28px;
        }

        h2 { 
            padding: 30px; 
        }
        h5 {
            margin: 0;
            font-size: 16px;
            font-family: Arial, sans-serif;
            font-weight: normal;
            line-height: 1.5;
            display: flex; 
            flex-wrap: wrap; 
            gap: 30px; 
            padding: 30px; 
        }
        .producto { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 10px; display: flex; gap: 20px; align-items: center; }
        .producto img { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; }
        .producto-info { flex-grow: 1; }
        .producto-info h3 { margin: 0 0 10px; }
        .botones { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .botones a, .botones button { padding: 8px 15px; background: #6a0dad; color: white; border: none; text-decoration: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .botones .eliminar { background: #e63946; }
        .fixed-total { position: fixed; bottom: 20px; right: 30px; background: #fff; border: 1px solid #ccc; padding: 15px 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); z-index: 999; text-align: right; }
        .fixed-total .total { margin: 0; font-size: 18px; font-weight: bold; }
        .fixed-total .siguiente { margin-top: 10px; display: inline-block; padding: 10px 20px; background: #6a0dad; color: white; text-decoration: none; border-radius: 5px; font-size: 16px; margin-left: 10px; cursor: pointer; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 400px; position: relative; text-align: center; }
        .modal-content input, select { width: 100%; padding: 10px; margin: 10px 0; }
        .modal-content button { margin: 10px 5px; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .modal-content .cancelar { background: #999; color: white; }
        .modal-content .confirmar { background: #6a0dad; color: white; }
        #resultados_usuarios { max-height: 150px; overflow-y: auto; text-align: left; }
        .usuario-item { padding: 8px; border-bottom: 1px solid #ccc; cursor: pointer; }
        .usuario-item:hover { background: #f0f0f0; }
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
    </style>
    <script>

        //FUW-31: abrirConfirmacion(): abre una ventana para confirmar la compra
        function abrirConfirmacion() {
            document.getElementById("modal-confirmacion").style.display = 'flex';
        }

        //FUW-32: cerrarModal(id): cierra la ventana de FUW-31
        function cerrarModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        //FUW-33: continuarCompra(regalo): el usuario decidirá si la compra es un regalo o no
        function continuarCompra(regalo) {
            if (regalo === 'no') {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'confirmar_directo';
                input.value = '1';

                document.body.appendChild(form);
                form.appendChild(input);
                form.submit();
            } else {
                document.getElementById("modal-confirmacion").style.display = 'none';
                document.getElementById("modal-regalo").style.display = 'flex';
            }
        }

        //FUW-34: buscarUsuarios(): busca al usuario receptor del regalo
        function buscarUsuarios() {
            const texto = document.getElementById("busqueda_usuario").value;
            if (texto.length >= 2) {
                fetch("buscar_usuario.php?q=" + texto)
                    .then(res => res.text())
                    .then(html => document.getElementById("resultados_usuarios").innerHTML = html);
            }
        }

        //FUW-35: seleccionarUsuario(correo): selecciona el usuario receptor del regalo
        function seleccionarUsuario(correo) {
            document.getElementById("correo_destinatario").value = correo;
            document.getElementById("busqueda_usuario").value = '';
            document.getElementById("resultados_usuarios").innerHTML = '';
        }
    </script>
</head>
<body>

<!-- Top bar -->
<div class="top-bar">
    <span>Entrega a tiempo garantizada</span>
    <span>Atención ágil y eficiente</span>
    <span>100% original</span>
    <span>De confianza</span>
</div>

<!-- Main bar -->
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

<h2>Mi Carrito</h2>

<?php if (empty($productos)): ?>
    <h5>No tienes productos en tu carrito. <a href="tienda.php">Ir a la tienda</a></h5>
<?php else: ?>
    <?php foreach ($productos as $prod): ?>
        <div class="producto">
            <img src="<?= htmlspecialchars($prod['ruta_preview']) ?>" alt="<?= htmlspecialchars($prod['nombre_prod']) ?>">
            <div class="producto-info">
                <h3><?= htmlspecialchars($prod['nombre_prod']) ?></h3>
                
                <!-- Mostrar el precio con descuento si existe -->
                <?php if ($prod['descuento'] > 0): ?>
                    <p><strong>Precio: </strong><span style="text-decoration: line-through;">$ <?= number_format($prod['precio'], 2) ?></span> 
                    <strong>$ <?= number_format($prod['precio_con_descuento'], 2) ?></strong></p>
                <?php else: ?>
                    <p><strong>Precio: $ <?= number_format($prod['precio'], 2) ?></strong></p>
                <?php endif; ?>
                
                <p>Tipo: <?= ucfirst($prod['tipo']) ?></p>
                <div class="botones">
                    <?php
                    // Determinar la página de detalles según el tipo de producto
                    $detallePage = match($prod['tipo']) {
                        'audio' => 'detalle_audio.php',
                        'video' => 'detalle_video.php',
                        'imagen' => 'detalle_imagen.php',
                        default => '#'
                    };
                    ?>
                    <a href="<?= $detallePage ?>?id=<?= urlencode($prod['nombre_prod']) ?>">Ver detalles</a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="nombre_prod" value="<?= htmlspecialchars($prod['nombre_prod']) ?>">
                        <button type="submit" name="eliminar" class="eliminar">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="fixed-total">
        <?php if ($totalDescuento > 0): ?>
            <p><strong>Total: </strong><span style="text-decoration: line-through;">$ <?= number_format($total, 2) ?></span> 
            <strong><?= number_format($totalDescuento, 2) ?></strong></p>
        <?php else: ?>
            <p><strong>Total: $ <?= number_format($total, 2) ?></strong></p>
        <?php endif; ?>
        <button onclick="abrirConfirmacion()" class="siguiente">Confirmar compra</button>
    </div>
<?php endif; ?>

<div id="modal-confirmacion" class="modal">
    <div class="modal-content">
        <h3>¿Su compra será un regalo?</h3>
        <button class="confirmar" onclick="continuarCompra('si')">Sí</button>
        <button class="cancelar" onclick="continuarCompra('no')">No</button>
    </div>
</div>

<div id="modal-regalo" class="modal">
    <div class="modal-content">
        <form method="POST">
            <h3>Buscar destinatario</h3>
            <input type="text" id="busqueda_usuario" name="busqueda_usuario" placeholder="Buscar por correo..." onkeyup="buscarUsuarios()">
            <div id="resultados_usuarios"></div>
            <input type="hidden" name="correo_destinatario" id="correo_destinatario">
            <button type="submit" name="confirmar_regalo" class="confirmar">Confirmar compra</button>
            <button type="button" onclick="cerrarModal('modal-regalo')" class="cancelar">Cancelar</button>
        </form>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <div class="footer-section">
        <h4>MediaShop</h4>
        <div><i class="fab fa-facebook"></i><i class="fab fa-twitter"></i><i class="fab fa-instagram"></i></div>
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
        <h4>Contáctanos</h4>
        <div><i class="fas fa-phone"></i> +51 999-999-999</div>
        <div><i class="fas fa-envelope"></i> mediashop@gmail.com</div>
    </div>
</div>

<div class="copy-bar">
    © <?= date('Y') ?> MediaShop - Todos los derechos reservados
</div>

</body>
</html>
