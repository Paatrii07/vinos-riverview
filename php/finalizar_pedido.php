<?php
session_start();
require_once '../config.php';

// 1. Seguridad: Solo usuarios logueados y con carrito con productos
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit();
}

try {
    $conexion = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Iniciamos una TRANSACCIÓN para asegurar que se guarde todo o nada
    $conexion->beginTransaction();

    // 2. Calcular el total (necesitamos los precios de la DB por seguridad)
    $total_calculado = 0;
    $ids = implode(',', array_keys($_SESSION['carrito']));
    $stmt_precios = $conexion->query("SELECT id_producto, precio_unidad FROM producto WHERE id_producto IN ($ids)");
    $precios_db = $stmt_precios->fetchAll(PDO::FETCH_KEY_PAIR); // Crea un mapa [id => precio]

    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        $total_calculado += $precios_db[$id] * $cantidad;
    }

    // 3. Insertar el PEDIDO (Simulamos pago "Transferencia" y estado "Completado")
    $sql_pedido = "INSERT INTO pedido (id_usuario, fecha, total_calculado, forma_pago, estado) 
                   VALUES (:user, NOW(), :total, 'Transferencia Bancaria', 'Pendiente de Envío')";
    $stmt_ped = $conexion->prepare($sql_pedido);
    $stmt_ped->execute([
        ':user'  => $_SESSION['usuario_id'],
        ':total' => $total_calculado
    ]);

    // Recuperamos el ID que se acaba de generar para el pedido
    $id_pedido_nuevo = $conexion->lastInsertId();

    // 4. Insertar los DETALLES del pedido
    $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario) 
                    VALUES (:id_ped, :id_prod, :cant, :precio)";
    $stmt_det = $conexion->prepare($sql_detalle);

    foreach ($_SESSION['carrito'] as $id_prod => $cantidad) {
        $stmt_det->execute([
            ':id_ped'  => $id_pedido_nuevo,
            ':id_prod' => $id_prod,
            ':cant'    => $cantidad,
            ':precio'  => $precios_db[$id_prod]
        ]);
    }

    // Si todo ha ido bien, confirmamos los cambios en la DB
    $conexion->commit();

    // 5. Limpiamos el carrito y redirigimos
    unset($_SESSION['carrito']);
    /*header("Location: perfil.php?pedido_exito=1");*/

    
    header("Location: perfil.php?pedido_exito=true");
    exit();

} catch (Exception $e) {
    if ($conexion) $conexion->rollBack();
    die("Error al procesar el pedido: " . $e->getMessage());
}