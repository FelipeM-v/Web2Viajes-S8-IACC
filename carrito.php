<?php
require_once 'conexion.php';

// Medidas de seguridad para la sesión
ini_set('session.gc_maxlifetime', 3600); // 1 hora
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Regenerar el ID de sesión tras el inicio de sesión
if (!isset($_SESSION['regenerado'])) {
    session_regenerate_id(true);
    $_SESSION['regenerado'] = true;
}

// Validar IP y User-Agent
if (!isset($_SESSION['ip'])) {
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} else {
    if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] ||
        $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        die("Sesión inválida.");
    }
}

// Medidas para evitar expiración prematura
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), $_COOKIE[session_name()], time() + 3600, "/");
}
$inactividad = 1800; // 30 minutos
if (isset($_SESSION['timeout'])) {
    $vida_session = time() - $_SESSION['timeout'];
    if ($vida_session > $inactividad) {
        session_unset();
        session_destroy();
        header("Location: index.php?msg=Sesión expirada por inactividad");
        exit();
    }
}
$_SESSION['timeout'] = time();

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar vuelo al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_vuelo'])) {
    $id_vuelo = $_POST['id_vuelo'];
    $id_hotel = $_POST['id_hotel'] ?? null;
    
    // Obtener detalles del vuelo
    $stmt = $pdo->prepare("SELECT * FROM VUELO WHERE id_vuelo = ?");
    $stmt->execute([$id_vuelo]);
    $vuelo = $stmt->fetch();
    
    if ($vuelo) {
        $item = [
            'id_vuelo' => $id_vuelo,
            'id_hotel' => $id_hotel,
            'origen' => $vuelo['origen'],
            'destino' => $vuelo['destino'],
            'fecha' => $vuelo['fecha'],
            'precio' => $vuelo['precio']
        ];
        
        if ($id_hotel) {
            $stmt = $pdo->prepare("SELECT * FROM HOTEL WHERE id_hotel = ?");
            $stmt->execute([$id_hotel]);
            $hotel = $stmt->fetch();
            if ($hotel) {
                $item['nombre_hotel'] = $hotel['nombre'];
                $item['ubicacion_hotel'] = $hotel['ubicacion'];
                $item['tarifa_hotel'] = $hotel['tarifa_noche'];
            }
        }
        
        $_SESSION['carrito'][] = $item;
        $mensaje = "Vuelo agregado al carrito exitosamente";
    }
}

// Confirmar reserva
if (isset($_POST['confirmar_reserva']) && !empty($_SESSION['carrito'])) {
    $id_cliente = rand(1000, 9999); // Simulación de cliente
    
    foreach ($_SESSION['carrito'] as $item) {
        $stmt = $pdo->prepare("INSERT INTO RESERVA (id_cliente, fecha_reserva, id_vuelo, id_hotel) VALUES (?, NOW(), ?, ?)");
        $stmt->execute([$id_cliente, $item['id_vuelo'], $item['id_hotel']]);
    }
    
    $_SESSION['carrito'] = [];
    $mensaje = "Reserva confirmada exitosamente";
}

// Para vaciar el carrito
if (isset($_POST['vaciar_carrito'])) {
    $_SESSION['carrito'] = [];
    $mensaje = "Carrito vaciado exitosamente";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Reservas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Carrito de Reservas</h2>
        
        <?php if (isset($mensaje)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin: 15px 0;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Seleccionar Vuelo y Hotel</h3>
            <form method="POST">
                <label>Vuelo:</label>
                <select name="id_vuelo" required>
                    <option value="">Selecciona un vuelo</option>
                    <?php
                    $vuelos = $pdo->query("SELECT * FROM VUELO WHERE plazas_disponibles > 0 ORDER BY fecha")->fetchAll();
                    foreach ($vuelos as $vuelo) {
                        echo "<option value='{$vuelo['id_vuelo']}'>{$vuelo['origen']} → {$vuelo['destino']} - {$vuelo['fecha']} - $ {$vuelo['precio']}</option>";
                    }
                    ?>
                </select><br>
                
                <label>Hotel (opcional):</label>
                <select name="id_hotel">
                    <option value="">Sin hotel</option>
                    <?php
                    $hoteles = $pdo->query("SELECT * FROM HOTEL WHERE habitaciones_disponibles > 0 ORDER BY nombre")->fetchAll();
                    foreach ($hoteles as $hotel) {
                        echo "<option value='{$hotel['id_hotel']}'>{$hotel['nombre']} ({$hotel['ubicacion']}) - $ {$hotel['tarifa_noche']}/noche</option>";
                    }
                    ?>
                </select><br>
                
                <button type="submit">Agregar al carrito</button>
            </form>
        </div>

        <div class="card">
            <h3>Carrito actual</h3>
            <?php if (!empty($_SESSION['carrito'])): ?>
                <div class="grid-container">
                    <?php foreach ($_SESSION['carrito'] as $index => $item): ?>
                        <div class="paquete">
                            <h4>Vuelo: <?php echo $item['origen']; ?> → <?php echo $item['destino']; ?></h4>
                            <p><strong>Fecha:</strong> <?php echo $item['fecha']; ?></p>
                            <p><strong>Precio vuelo:</strong> $ <?php echo $item['precio']; ?></p>
                            <?php if (isset($item['nombre_hotel'])): ?>
                                <p><strong>Hotel:</strong> <?php echo $item['nombre_hotel']; ?></p>
                                <p><strong>Ubicación:</strong> <?php echo $item['ubicacion_hotel']; ?></p>
                                <p><strong>Tarifa:</strong> $ <?php echo $item['tarifa_hotel']; ?>/noche</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <form method="POST" style="text-align: center; margin-top: 20px;">
                    <input type="hidden" name="confirmar_reserva" value="1">
                    <button type="submit" style="background: linear-gradient(135deg, #28a745, #20c997);">Confirmar Reserva</button>
                </form>
                
                <form method="POST" style="text-align: center; margin-top: 10px;">
                    <input type="hidden" name="vaciar_carrito" value="1">
                    <button type="submit" style="background: linear-gradient(135deg, #dc3545, #c82333);">Vaciar Carrito</button>
                </form>
            <?php else: ?>
                <p>El carrito está vacío.</p>
            <?php endif; ?>
        </div>

        <div class="flex-container">
            <a href="index.php">Volver al inicio</a>
        </div>
    </div>
</body>
</html> 