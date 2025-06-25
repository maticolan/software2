<?php
include 'conexion.php';
session_start();

$busqueda = '';

if (isset($_POST['busqueda'])) {
    $busqueda = $_POST['busqueda'];
} elseif (isset($_GET['busqueda'])) {
    $busqueda = $_GET['busqueda'];
}

$busqueda = trim($busqueda);

if ($busqueda === '') {
    echo "<p>No se ingresó ningún término de búsqueda.</p>";
    exit();
}

//PRO-13: 
$consulta = $conn->prepare("CALL GetContenidoByBusqueda(?)");
$likeBusqueda = "%$busqueda%";
$consulta->bind_param('s', $likeBusqueda);
$consulta->execute();
$resultado = $consulta->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados para <?= htmlspecialchars($busqueda) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        .search-bar {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .search-bar input {
            width: 60%;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .producto {
            display: inline-block;
            width: 150px;
            margin: 10px;
            text-align: center;
        }
        .producto img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 10px;
        }
        .producto a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            display: block;
            margin-top: 8px;
        }
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

<!-- Barra de búsqueda -->
<div class="search-bar" style="text-align:center; padding:20px;">
    <form action="buscar_productos.php" method="GET" style="display:inline-block; width: 60%;">
        <input type="text" name="busqueda" placeholder="Buscar en MediaShop..." 
               style="width: 80%; padding: 10px; font-size: 16px; border-radius: 5px; border: 1px solid #ccc;" required
               value="<?= htmlspecialchars($busqueda) ?>">
        <button type="submit" style="padding: 10px 15px; font-size: 16px; border-radius: 5px; cursor:pointer;">Buscar</button>
    </form>
</div>

<h2 style="padding-left: 20px;">Resultados para: <em><?= htmlspecialchars($busqueda) ?></em></h2>

<div style="padding-left: 20px;">
<?php if ($resultado->num_rows > 0): ?>
    <?php while ($row = $resultado->fetch_assoc()): ?>
        <?php
            $tipo = strtolower($row['tipo']); // 'audio', 'video', 'imagen', etc.
            $nombre = htmlspecialchars($row['nombre_prod']);
            $vista_previa = htmlspecialchars($row['ruta_preview']);
            $nombre_url = urlencode($nombre);

            if ($tipo == "imagen") {
                $pagina_detalle = "detalle_imagen.php?nombre=$nombre_url";
            } elseif ($tipo == "video") {
                $pagina_detalle = "detalle_video.php?nombre=$nombre_url";
            } elseif ($tipo == "audio") {
              $pagina_detalle = "detalle_audio.php?nombre=$nombre_url";
            } else {
                $pagina_detalle = "#";
            }
        ?>
        <div class="producto">
            <a href="<?= $pagina_detalle ?>">
                <img src="<?= $vista_previa ?>" alt="<?= $nombre ?>">
                <?= $nombre ?>
            </a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No se encontraron productos que coincidan.</p>
<?php endif; ?>
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








