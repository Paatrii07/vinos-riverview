<?php
require_once '../config.php';
$id_pedido = $_GET['id'] ?? 0;

try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    // Hacemos un JOIN para traer el nombre del producto de la tabla 'producto'
    $sql = "SELECT dp.*, p.nombre 
            FROM detalle_pedido dp 
            JOIN producto p ON dp.id_producto = p.id_producto 
            WHERE dp.id_pedido = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_pedido]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolvemos los datos en formato HTML para meterlos directamente al modal
    if ($detalles) {
        foreach ($detalles as $item) {
            echo "<tr>
                    <td>{$item['nombre']}</td>
                    <td>{$item['cantidad']}</td>
                    <td>".number_format($item['precio_unitario'], 2, ',', '.')."€</td>
                    <td class='fw-bold'>".number_format($item['precio_unitario'] * $item['cantidad'], 2, ',', '.')."€</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No hay detalles disponibles.</td></tr>";
    }
} catch(Exception $e) { echo "Error técnico."; }
?>