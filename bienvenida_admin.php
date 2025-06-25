<?php
include('conexion.php');
session_start();

// PRO-06: Obtener el total de usuarios activos (clientes) registrados en el sistema.
// 1. Preparación de la llamada al procedimiento.
//    No requiere parámetros, porque el procedimiento simplemente ejecuta el conteo a nivel de servidor.
// 2. Se ejecuta la llamada al procedimiento.
// 3. Se obtiene el resultado devuelto por el procedimiento como un objeto de tipo mysqli_result.
// 4. Luego, se obtiene la primera (y única) fila de resultados como un array asociativo.
// 5. Se asigna el total obtenido al valor $totalUsuarios para que pueda ser utilizado en el resto de la aplicación 
// 6. Se cierra correctamente el resultado liberando los recursos de la conexión.
$resUsuarios = $conn->prepare("CALL GetTotalUsuarios()");
$resUsuarios->execute();
$resUsuarios = $resUsuarios->get_result();
$rowUsuarios = $resUsuarios->fetch_assoc();
$totalUsuarios = $rowUsuarios['total'];
$resUsuarios->close();

// PRO-07: Obtener el número total de descargas realizadas en la plataforma.
// 1. Se crea una consulta preparada que llama al procedimiento GetTotalDescargas().
// 2. Se ejecuta el procedimiento. MySQL cuenta el total de registros de la tabla descarga.
// 3. Se obtiene el único registro retornado como array asociativo, cuyo índice 'total' contiene el número de descargas.
// 4. Se guarda el total de descargas en la variable $totalDescargas para su uso posterior en la aplicación.
// 5. Se cierra el resultado para liberar memoria y recursos de conexión.
$resDescargas = $conn->prepare("CALL GetTotalDescargas()");
$resDescargas->execute();
$resDescargas = $resDescargas->get_result();
$rowDescargas = $resDescargas->fetch_assoc();
$totalDescargas = $rowDescargas['total'];
$resDescargas->close();

// PRO-08: Calcular el ingreso total generado por todas las descargas de la plataforma.
// 1. Se prepara la llamada al procedimiento GetTotalIngresos().
//    - No requiere parámetros de entrada.
// 2. Se ejecuta el procedimiento.
//     La base de datos calcula la suma total de los montos de todas las descargas.
// 3. Se recupera el único registro como array asociativo con la clave 'total' que contiene la suma calculada.
// 4. Se asigna el total de ingresos a la variable $totalIngresos.
//    - Si la suma es NULL (por ejemplo, cuando no hay registros en descarga), se asigna el valor 0 como ingreso total.
// 5. Se cierra el conjunto de resultados liberando recursos de la conexión.
$resIngresos = $conn->prepare("CALL GetTotalIngresos()");
$resIngresos->execute();
$resIngresos = $resIngresos->get_result();
$rowIngresos = $resIngresos->fetch_assoc();
$totalIngresos = $rowIngresos['total'] ?? 0;
$resIngresos->close();

// PRO-09: Obtener el listado de los contenidos más vendidos en la plataforma, ordenados de mayor a menor según la cantidad de descargas realizadas.
// 1. Se prepara la llamada al procedimiento GetContenidosMasVendidos().
//    - No se reciben parámetros de entrada
// 2. Se obtiene el conjunto de registros resultante: cada registro contiene un contenido y su número de descargas totales.
// 3. Se transforma el resultado de la ejecución en un objeto result-set manipulable en PHP.
// 4.1. Se recorre cada fila obtenida.
// 4.2. Cada fila es un array asociativo con las claves:
//      - 'contenido' -> nombre del producto
//      - 'total_descargas' -> número de veces que ha sido descargado
//      - Se va almacenando todo el conjunto de resultados en el array PHP $vendidos.
// 5. Se cierra el conjunto de resultados liberando recursos del servidor.
$resVendidos = $conn->prepare("CALL GetContenidosMasVendidos()");
$resVendidos->execute();
$resVendidos = $resVendidos->get_result();
$vendidos = [];
while ($fila = $resVendidos->fetch_assoc()) {
    $vendidos[] = $fila;
}
$resVendidos->close();

// PRO-10: Obtener el listado de los clientes que han realizado más descargas en la plataforma, ordenados de mayor a menor según la cantidad de descargas que han efectuado.
// 1. Se prepara la llamada al procedimiento GetClientesMasDescargadores().
// 2. El procedimiento devuelve un conjunto de registros, donde cada registro representa a un cliente con su respectiva cantidad de descargas.
// 3. Se transforma el resultado de la ejecución en un objeto result-set manejable en PHP.
// 4.1. Se recorre cada fila obtenida.
// 4.2. Cada fila es un array asociativo con las claves:
//      - correo -> correo del cliente
//      - nombre -> nombre del cliente
//      - total_descargas -> cantidad de descargas realizadas por ese cliente.
// 4.3. Se almacena cada fila dentro del array $clientes.
// 5. Se cierra el conjunto de resultados, liberando los recursos.
$resClientes = $conn->prepare("CALL GetClientesMasDescargadores()");
$resClientes->execute();
$resClientes = $resClientes->get_result();
$clientes = [];
while ($fila = $resClientes->fetch_assoc()) {
    $clientes[] = $fila;
}
$resClientes->close();

// PRO-11: Obtener el listado de contenidos que han recibido calificaciones por parte de los usuarios, calculando el promedio de esas calificaciones, y mostrando los contenidos ordenados por ese promedio (de mayor a menor).
// 1. Se prepara la invocación al procedimiento GetContenidoValorados().
// 2. Devuelve un conjunto de registros: cada uno corresponde a un contenido con su respectivo promedio de calificación.
// 3. Se convierte el resultado en un result set de PHP.
// 4.1. Se recorre cada fila obtenida.
// 4.2. Cada fila es un array asociativo con las claves:
//      - contenido -> nombre del contenido
//      - promedio -> el promedio de todas las notas registradas sobre dicho contenido
// 4.3. Cada fila es agregada al array $valorados para su posterior uso (por ejemplo: mostrar en un ranking de los mejores valorados).
// 5. Se cierra el result set liberando los recursos de conexión.
$resValorados = $conn->prepare("CALL GetContenidoValorados()");
$resValorados->execute();
$resValorados = $resValorados->get_result();
$valorados = [];
while ($fila = $resValorados->fetch_assoc()) {
    $valorados[] = $fila;
}
$resValorados->close();



?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MediaShop - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        header { background-color: #000; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 50px; z-index: 1000; }
        header h1 { margin: 0; font-size: 24px; }
        .header-icons { display: flex; gap: 15px; }
        .header-icons a { color: white; text-decoration: none; font-size: 20px; }
        .sidebar { width: 220px; background-color: #c0c0c0; height: 100vh; position: fixed; top: 50px; left: 0; padding-top: 20px; display: flex; flex-direction: column; }
        .sidebar button { background: none; border: none; padding: 15px 20px; text-align: left; font-size: 16px; cursor: pointer; transition: background 0.3s; }
        .sidebar button:hover { background-color: #bbb; }
        .main { margin-left: 240px; margin-top: 70px; padding: 20px; }
        .main h2 { text-align: center; margin-top: 0; }
        .main h3 { text-align: center; font-weight: normal; color: #555; }
        .cards { display: flex; justify-content: space-around; margin: 30px 0; }
        .card { background-color: #c0c0c0; width: 28%; padding: 20px; border-radius: 8px; text-align: center; font-size: 18px; }
        .card strong { display: block; margin-top: 10px; font-size: 24px; }
        .data-block { background-color: #c0c0c0; margin: 20px 0; padding: 20px; border-radius: 8px; }
        .data-block h4 { text-align: center; margin: 0 0 15px 0; }
        table { width: 100%; border-collapse: collapse; background-color: white; }
        th, td { border: 1px solid #999; padding: 8px; text-align: center; }
        th { background-color: #ddd; }
        .footer { display: flex; justify-content: space-around; flex-wrap: wrap; background-color: #c0c0c0; padding: 20px; margin-top: 40px; }
        .footer-section { flex: 1; min-width: 200px; margin: 10px; }
        .footer-section h4 { margin-bottom: 10px; }
        .footer-section div { margin: 5px 0; }
        .footer-section i { margin-right: 8px; }
        .copy-bar { text-align: center; padding: 10px; background-color: #aaa; font-size: 14px; }
        #todosClientes { display: none; margin-top: 20px; }
        .show-more { cursor: pointer; color: blue; text-decoration: underline; }
    </style>
    <script>
        // FUW-06: 
        function mostrarTodos() {
            document.getElementById("todosClientes").style.display = "block";
            document.getElementById("btnMostrarMas").style.display = "none";
        }
    </script>
</head>
<body>

<header>
    <h1><a href="bienvenida_admin.php" style="color:white;text-decoration:none;">MediaShop</a></h1>
    <div class="header-icons">
        <a href="indexadmin.php">🔍</a>
        <a href="#">👤</a>
    </div>
</header>

<div class="sidebar">
    <a href="perfiles_admin.php"><button>Gestión de Usuarios</button></a>
    <a href="contenidos_admin.php"><button>Gestión de Contenidos</button></a>
    <a href="categorias_admin.php"><button>Gestión de Categorías</button></a>
    <a href="promociones_admin.php"><button>Gestión de Promociones</button></a>
    <a href="calificaciones_admin.php"><button>Gestión de Calificaciones</button></a>
</div>

<div class="main">
    <h2>¡Bienvenido Administrador!</h2>
    <h3>Empieza tus tareas con la barra lateral de la izquierda</h3>

    <div class="cards">
        <div class="card">
            Usuarios Activos
            <strong><?= $totalUsuarios ?></strong>
        </div>
        <div class="card">
            Total Descargas
            <strong><?= $totalDescargas ?></strong>
        </div>
        <div class="card">
            Total Ingresos
            <strong>$<?= number_format($totalIngresos, 2) ?></strong>
        </div>
    </div>

    <div class="data-block">
        <h4>Contenidos más vendidos</h4>
        <table>
            <tr><th>Posición</th><th>Contenido</th><th>Ventas</th></tr>
            <?php 
            for ($i=0; $i < min(10, count($vendidos)); $i++): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($vendidos[$i]['contenido']) ?></td>
                <td><?= $vendidos[$i]['total_descargas'] ?></td>
            </tr>
            <?php endfor; ?>
        </table>
    </div>

    <div class="data-block">
        <h4>Clientes con más descargas </h4>
        <table>
            <tr><th>Posición</th><th>Cliente</th><th>Descargas</th></tr>
            <?php 
            for ($i=0; $i < min(5, count($clientes)); $i++): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($clientes[$i]['nombre']) ?> (<?= htmlspecialchars($clientes[$i]['correo']) ?>)</td>
                <td><?= $clientes[$i]['total_descargas'] ?></td>
            </tr>
            <?php endfor; ?>
        </table>

        <div id="todosClientes">
            <table style="margin-top:20px;">
                <tr><th>Posición</th><th>Cliente</th><th>Descargas</th></tr>
                <?php 
                for ($i=0; $i < count($clientes); $i++): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($clientes[$i]['nombre']) ?> (<?= htmlspecialchars($clientes[$i]['correo']) ?>)</td>
                    <td><?= $clientes[$i]['total_descargas'] ?></td>
                </tr>
                <?php endfor; ?>
            </table>
        </div>
    </div>

    <div class="data-block">
        <h4>Contenidos mejor valorados</h4>
        <table>
            <tr><th>Posición</th><th>Contenido</th><th>Promedio Nota</th></tr>
            <?php 
            for ($i=0; $i < min(10, count($valorados)); $i++): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($valorados[$i]['contenido']) ?></td>
                <td><?= number_format($valorados[$i]['promedio'], 2) ?></td>
            </tr>
            <?php endfor; ?>
        </table>
    </div>


</div>

</body>
</html>
