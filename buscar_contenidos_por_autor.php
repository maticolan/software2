<?php
include('conexion.php');

if (isset($_POST['autor'])) {
    $autor = $_POST['autor'];
    // PRO-12: Obtener el listado de contenidos (productos) que fueron subidos por un autor específico. Permite filtrar todos los registros del catálogo de contenidos, según el autor que los haya creado o registrado.
    // 1. Se prepara la invocación al procedimiento almacenado GetContenidosPorAutor().
    //    - Este procedure espera recibir un parámetro: el nombre exacto del autor.
    // 2. Se le pasa el valor de la variable $autor, que debe contener el nombre del autor a buscar.
    //    - s -> string
    // 3.Se ejecuta el procedimiento con el autor indicado.
    //   - Retorna un conjunto de resultados con los contenidos de ese autor.
    // 4. Se convierte el resultado en un conjunto de filas manejable desde PHP.
    //    - Cada fila representa un contenido perteneciente al autor consultado.
    $stmt = $conn->prepare("CALL GetContenidosPorAutor(?)");
    $stmt->bind_param("s", $autor);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<p>No hay contenidos de este autor.</p>";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo "<div style='display:flex; align-items:center; margin-bottom:10px;'>
                    <img src='".$row['ruta_preview']."' alt='preview' style='width:50px; height:50px; margin-right:10px;'>
                    <a href='editar_contenido.php?id=".urlencode($row['nombre_prod'])."'>
                        ".htmlspecialchars($row['nombre_prod'])."
                    </a>
                </div>";

        }
    }
    $stmt->close();
}
?>
