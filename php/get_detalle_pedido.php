<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    exit('Acceso denegado.');
}

$id_pedido = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_pedido <= 0) {
    echo "<tr><td colspan='4'>Pedido no válido.</td></tr>";
    exit();
}

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Si es admin, puede ver cualquier pedido
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') {
        $sql = "SELECT dp.*, p.nombre
                FROM detalle_pedido dp
                JOIN producto p ON dp.id_producto = p.id_producto
                WHERE dp.id_pedido = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id_pedido]);

    } else {
        // Si es cliente, solo sus propios pedidos
        $sql = "SELECT dp.*, p.nombre
                FROM detalle_pedido dp
                JOIN producto p ON dp.id_producto = p.id_producto
                JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                WHERE dp.id_pedido = :id
                  AND ped.id_usuario = :id_usuario";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id' => $id_pedido,
            ':id_usuario' => $_SESSION['usuario_id']
        ]);
    }

    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($detalles) {
        foreach ($detalles as $item) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($item['nombre']) . "</td>";
            echo "<td>" . (int)$item['cantidad'] . "</td>";
            echo "<td>" . number_format($item['precio_unitario'], 2, ',', '.') . "€</td>";
            echo "<td class='fw-bold'>" . number_format($item['precio_unitario'] * $item['cantidad'], 2, ',', '.') . "€</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No hay detalles disponibles.</td></tr>";
    }

} catch (Exception $e) {
    error_log("Error al obtener detalle del pedido: " . $e->getMessage());
    echo "<tr><td colspan='4'>Error técnico.</td></tr>";
}
?>
