<?php
include('conexion.php');
session_start();

// Determinar si se quiere mostrar ex-clientes
$mostrar_exclientes = isset($_GET['exclientes']) && $_GET['exclientes'] == '1';
$tipo = $mostrar_exclientes ? 'ex-cliente' : 'cliente';

// Consulta filtrando primero admin = 0 y luego tipo de cliente
$query = "SELECT nombre, correo FROM usuario WHERE admin = 0 AND tipo_cli = '$tipo'";
$resultado = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - MediaShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... mismos estilos que ya tienes ... */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        header {
            background-color: #000;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 50px;
            z-index: 1000;
        }
        header h1 a,
        header h1 a:visited,
        header h1 a:hover,
        header h1 a:active {
            color: white;
            text-decoration: none;
            margin: 0;
                    font-size: 24px;
        }

        .header-icons {
            display: flex;
            gap: 15px;
        }
        .header-icons a {
            color: white;
            text-decoration: none;
            font-size: 20px;
        }
        .sidebar {
            width: 220px;
            background-color: #c0c0c0;
            height: 100vh;
            position: fixed;
            top: 50px;
            left: 0;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
        }
        .sidebar button {
            background: none;
            border: none;
            padding: 15px 20px;
            text-align: left;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .sidebar button:hover {
            background-color: #bbb;
        }
         .main {
            margin-left: 240px;
            margin-top: 70px;
            padding: 20px;
        }
        .breadcrumb {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }
        .search-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-bar input {
            width: 100%;
            padding: 8px;
            border: 1px solid #aaa;
            border-radius: 4px;
            margin-right: 10px;
        }
        .user-list {
            background-color: #fcefff;
            border-radius: 8px;
            padding: 20px;
        }
        .user-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .user-left {
            display: flex;
            align-items: center;
        }
        .user-circle {
            width: 35px;
            height: 35px;
            background-color: #c0aaff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        .user-actions i {
            margin-left: 15px;
            cursor: pointer;
            color: #333;
            transition: color 0.3s;
        }
        .user-actions i:hover {
            color: #000;
        }
        .btn-ver {
            margin-top: 20px;
            background-color: #6a5acd;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            display: block;
            margin-left: auto;
            text-decoration: none;
            text-align: center;
        }
        .cards {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
        }
        .card {
            background-color: #c0c0c0;
            width: 28%;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 18px;
        }
        .card strong {
            display: block;
            margin-top: 10px;
            font-size: 24px;
        }
        .data-block {
            background-color: #c0c0c0;
            margin: 20px 0;
            padding: 20px;
            border-radius: 8px;
        }
        .data-block h4 {
            text-align: center;
            margin: 0 0 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #ddd;
        }
        .footer {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            background-color: #c0c0c0;
            padding: 20px;
            margin-top: 40px;
        }
        .footer-section {
            flex: 1;
            min-width: 200px;
            margin: 10px;
        }
        .footer-section h4 {
            margin-bottom: 10px;
        }
        .footer-section div {
            margin: 5px 0;
        }
        .footer-section i {
            margin-right: 8px;
        }
        .copy-bar {
            text-align: center;
            padding: 10px;
            background-color: #aaa;
            font-size: 14px;
        }
        .modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-content {
            background-color: white;
            padding: 40px 60px;
            border-radius: 10px;
            text-align: center;
        }

        .modal-content button {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            margin: 10px;
        }

        .btn-confirm {
            background-color: #2e86de;
            color: white;
        }

        .btn-cancel {
            background-color: #e74c3c;
            color: white;
        }

        .modal-success {
            font-size: 20px;
            font-weight: bold;
        }

        .check-icon {
            margin-top: 20px;
            font-size: 40px;
            color: #2ecc71;
        }
    </style>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php">MediaShop</a></h1>
    <div class="header-icons">

    </div>
</header>

<div class="sidebar">
    <!-- sidebar -->
     <a href="perfiles_admin.php">
        <button style="background-color:#bbb; font-weight:bold;">Gestión de Usuarios</button>
    </a>
    
    <a href="contenidos_admin.php">
        <button>Gestión de Contenidos</button>
    </a>

    <a href="categorias_admin.php">
        <button>Gestión de Categorías</button>
    </a>


    <a href="promociones_admin.php">
        <button>Gestión de Promociones</button>
    </a>

    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
    </a>
</div>

<div class="main">
    <div class="breadcrumb">
        Administración del Portal > Gestión Usuarios
    </div>

    <div class="search-bar">
        <input type="text" placeholder="Buscar usuario...">
        <i class="fas fa-search"></i>
    </div>

    <div class="user-list">
        <h3>Mostrando: <?php echo ucfirst($tipo) . 's'; ?></h3>
        <?php while ($fila = $resultado->fetch_assoc()): ?>
            <div class="user-item">
                <div class="user-left">
                    <div class="user-circle"><?php echo strtoupper(substr($fila['nombre'], 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($fila['nombre']); ?></span>
                </div>
                <div class="user-actions">
                    <a href="editar_usuario.php?correo=<?php echo urlencode($fila['correo']); ?>">
                        <i class="fas fa-edit" title="Editar usuario"></i>
                    </a>

                    <?php if (!$mostrar_exclientes): ?>
                        <i class="fas fa-trash" title="Marcar como ex-cliente" onclick="confirmarBorrado('<?php echo $fila['correo']; ?>')"></i>
                    <?php endif; ?>
                </div>

            </div>
        <?php endwhile; ?>
    </div>

    <a href="?exclientes=<?php echo $mostrar_exclientes ? '0' : '1'; ?>">
        <button class="btn-ver">
            <?php echo $mostrar_exclientes ? 'Ver clientes' : 'Ver ex-clientes'; ?>
        </button>
    </a>
</div>

<!-- MODAL DE CONFIRMACIÓN -->
<div id="modalConfirmacion" class="modal-overlay">
    <div class="modal-content">
        <p>¿Está seguro que quiere que el usuario sea ex-cliente?</p>
        <div>
            <button class="btn-confirm" id="btnConfirmar">Continuar</button>
            <button class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
        </div>
    </div>
</div>

<!-- MODAL DE ÉXITO -->
<?php if (isset($_GET['eliminado'])): ?>
<div id="modalExito" class="modal-overlay" style="display: flex;">
    <div class="modal-content">
        <p class="modal-success">El usuario ahora es un ex-cliente</p>
        <div class="check-icon"><i class="fas fa-check-circle"></i></div>
    </div>
</div>
<script>
    setTimeout(() => {
        document.getElementById("modalExito").style.display = "none";
        window.location.href = "perfiles_admin.php";
    }, 2500);
</script>
<?php endif; ?>

<script>
    let correoSeleccionado = '';

    function confirmarBorrado(correo) {
        correoSeleccionado = correo;
        document.getElementById("modalConfirmacion").style.display = "flex";
    }

    function cerrarModal() {
        document.getElementById("modalConfirmacion").style.display = "none";
    }

    document.getElementById("btnConfirmar").addEventListener("click", function () {
        window.location.href = "cambiar_estado.php?correo=" + encodeURIComponent(correoSeleccionado);
    });
</script>



</body>
</html>
