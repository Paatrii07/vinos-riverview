<?php
session_start();
require_once '../config.php';

// Seguridad: Solo usuarios logueados y con carrito con productos
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit();
}

// Solo aceptamos envío por POST desde el modal de pago
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: carrito.php");
    exit();
}

// Validación básica de los datos del pago simulado
$titular = trim($_POST['titular'] ?? '');
$numero_tarjeta = preg_replace('/\D/', '', $_POST['numero_tarjeta'] ?? '');
$caducidad = trim($_POST['caducidad'] ?? '');
$cvv = trim($_POST['cvv'] ?? '');

if (
    empty($titular) ||
    strlen($numero_tarjeta) !== 16 ||
    !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $caducidad) ||
    !preg_match('/^\d{3,4}$/', $cvv)
) {
    header("Location: carrito.php?error=pago");
    exit();
}

$conexion = null;

try {
    $conexion = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Iniciamos una transacción
    $conexion->beginTransaction();

    // Obtener los IDs del carrito
    $ids_carrito = array_keys($_SESSION['carrito']);
    $placeholders = implode(',', array_fill(0, count($ids_carrito), '?'));

    // Consultar precios y stock real de la base de datos
    $stmt_productos = $conexion->prepare("
        SELECT id_producto, precio_unidad, stock_actual
        FROM producto
        WHERE id_producto IN ($placeholders)
    ");
    $stmt_productos->execute($ids_carrito);
    $productos_db = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    // Crear mapa de productos
    $datos_productos = [];
    foreach ($productos_db as $producto) {
        $datos_productos[$producto['id_producto']] = [
            'precio' => (float) $producto['precio_unidad'],
            'stock'  => (int) $producto['stock_actual']
        ];
    }

    // Calcular total y comprobar stock
    $total_calculado = 0;
    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $id = (int) $id;
        $cantidad = (int) $cantidad;

        if ($cantidad < 1) {
            throw new Exception("Cantidad no válida en el carrito.");
        }

        if (!isset($datos_productos[$id])) {
            throw new Exception("Producto no encontrado.");
        }

        if ($datos_productos[$id]['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para uno de los productos.");
        }

        $total_calculado += $datos_productos[$id]['precio'] * $cantidad;
    }

    // Insertar pedido
    $sql_pedido = "INSERT INTO pedido (id_usuario, fecha, total_calculado, forma_pago, estado)
                   VALUES (:user, NOW(), :total, 'Tarjeta bancaria (simulada)', 'Pendiente')";
    $stmt_ped = $conexion->prepare($sql_pedido);
    $stmt_ped->execute([
        ':user'  => $_SESSION['usuario_id'],
        ':total' => $total_calculado
    ]);

    $id_pedido_nuevo = $conexion->lastInsertId();

    // Insertar detalles
    $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario)
                    VALUES (:id_ped, :id_prod, :cant, :precio)";
    $stmt_det = $conexion->prepare($sql_detalle);

    // Actualizar stock
    $sql_stock = "UPDATE producto
                  SET stock_actual = stock_actual - :cantidad
                  WHERE id_producto = :id_producto AND stock_actual >= :cantidad";
    $stmt_stock = $conexion->prepare($sql_stock);

    foreach ($_SESSION['carrito'] as $id_prod => $cantidad) {
        $id_prod = (int) $id_prod;
        $cantidad = (int) $cantidad;

        // Insertar línea de detalle
        $stmt_det->execute([
            ':id_ped'  => $id_pedido_nuevo,
            ':id_prod' => $id_prod,
            ':cant'    => $cantidad,
            ':precio'  => $datos_productos[$id_prod]['precio']
        ]);

        // Restar stock de forma segura
        $stmt_stock->execute([
            ':cantidad'    => $cantidad,
            ':id_producto' => $id_prod
        ]);

        if ($stmt_stock->rowCount() === 0) {
            throw new Exception("No se pudo actualizar el stock de un producto.");
        }
    }

    // Confirmar todo
    $conexion->commit();

    // Vaciar carrito
    unset($_SESSION['carrito']);

    header("Location: perfil.php?pedido_exito=true");
    exit();

} catch (Exception $e) {
    if ($conexion && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log("Error al procesar pedido con pago simulado: " . $e->getMessage());
    header("Location: carrito.php?error=stock");
    exit();
}
?>
