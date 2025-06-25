<?php
session_start();
include 'conexion.php';

$correo = $_SESSION['correo'] ?? null;


if (!$correo) {
    echo "<script>alert('Debes iniciar sesión para ver tu lista de deseos'); window.location='tienda.php';</script>";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $prodEliminar = $_POST['nombre_prod'] ?? '';
    if (!empty($prodEliminar)) {
        //PRO-33: eliminar_producto_deseo(correo_usuario VARCHAR(255), nombre_prod VARCHAR(255)) Elimina un producto específico de la lista de deseos del usuario.
        $stmt = $conn->prepare("DELETE FROM listadeseo WHERE correo_usuario = ? AND nombre_prod = ?");
        $stmt->bind_param("ss", $correo, $prodEliminar);
        $stmt->execute();
        header("Location: deseos.php");
        exit();
    }
}

//FUB-17: consultar_productos_deseados(correo_usuario VARCHAR(255)) Obtiene todos los productos de la lista de deseos del usuario, incluyendo tipo y ruta de preview.
$consulta = $conn->prepare("SELECT c.nombre_prod, c.precio, c.ruta_preview, t.tipo
    FROM contenido c
    JOIN listadeseo l ON c.nombre_prod = l.nombre_prod
    JOIN tipo_archivo t ON c.tipo_archivo_id = t.id
    WHERE l.correo_usuario = ?");
$consulta->bind_param("s", $correo);
$consulta->execute();
$resultado = $consulta->get_result();

$productos = [];
while ($row = $resultado->fetch_assoc()) {
    $productos[] = $row;
}

if (empty($productos)) {
    echo "<script>alert('No tienes productos en tu lista de deseos'); window.location='tienda.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Deseos | MediaShop</title>
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
        .producto { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 10px; display: flex; gap: 20px; align-items: center; }
        .producto img { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; }
        .producto-info { flex-grow: 1; }
        .producto-info h3 { margin: 0 0 10px; }
        .producto-info p { margin: 5px 0; }
        .botones { display: flex; gap: 10px; margin-top: 10px; }
        .botones a, .botones button {
            padding: 8px 15px;
            background: #6a0dad;
            color: white;
            border: none;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .botones button {
            background: #e63946;
        }
        .volver { margin-top: 20px; display: inline-block; padding: 10px 20px; background: #6a0dad; color: white; text-decoration: none; border-radius: 5px; }
        .footer {
            background-color: #e0e0e0;
            padding: 40px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            color: #333;
            margin-top: 40px;
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
            margin-top: 20px;
        }
    </style>
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

<h2>Mis Productos en Deseo</h2  >

<?php foreach ($productos as $prod): ?>
    <div class="producto">
        <img src="<?= htmlspecialchars($prod['ruta_preview']) ?>" alt="<?= htmlspecialchars($prod['nombre_prod']) ?>">
        <div class="producto-info">
            <h3><?= htmlspecialchars($prod['nombre_prod']) ?></h3>
            <p>Precio: $<?= htmlspecialchars($prod['precio']) ?></p>
            <div class="botones">
                <?php
                    $detallePage = match ($prod['tipo']) {
                        'audio' => 'detalle_audio.php',
                        'video' => 'detalle_video.php',
                        'imagen' => 'detalle_imagen.php',
                        default => '#'
                    };
                ?>
                <a href="<?= $detallePage ?>?id=<?= urlencode($prod['nombre_prod']) ?>">Ver detalles</a>

                <form method="POST" style="display:inline;">
                    <input type="hidden" name="nombre_prod" value="<?= htmlspecialchars($prod['nombre_prod']) ?>">
                    <button type="submit" name="eliminar">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<a href="tienda.php" class="volver">Volver a la tienda</a>

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

</body>
</html>
