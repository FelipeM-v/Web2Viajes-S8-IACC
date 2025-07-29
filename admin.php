<?php
require_once 'conexion.php';

// Para determinar qué acción realizar según el parámetro GET
$action = $_GET['action'] ?? 'dashboard';

// Para procesar formularios cuando se envían
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add_vuelo') {
        $origen = $_POST['origen'];
        $destino = $_POST['destino'];
        $fecha = $_POST['fecha'];
        $precio = $_POST['precio'];
        $plazas = $_POST['plazas_disponibles'];
        
        $stmt = $pdo->prepare("INSERT INTO VUELO (origen, destino, fecha, precio, plazas_disponibles) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$origen, $destino, $fecha, $precio, $plazas])) {
            $mensaje = "Vuelo registrado exitosamente";
        } else {
            $error = "Error al registrar el vuelo";
        }
    }
    
    if ($action === 'add_hotel') {
        $nombre = $_POST['nombre'];
        $ubicacion = $_POST['ubicacion'];
        $estrellas = $_POST['estrellas'];
        $precio_noche = $_POST['precio_noche'];
        
        $stmt = $pdo->prepare("INSERT INTO HOTEL (nombre, ubicacion, estrellas, precio_noche) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nombre, $ubicacion, $estrellas, $precio_noche])) {
            $mensaje = "Hotel registrado exitosamente";
        } else {
            $error = "Error al registrar el hotel";
        }
    }
    
    if ($action === 'create_reservas') {
        // Para crear reservas automáticas coherentes
        $vuelos = $pdo->query("SELECT * FROM VUELO")->fetchAll();
        $reservasCreadas = 0;
        
        foreach ($vuelos as $vuelo) {
            // Para encontrar hoteles en el mismo destino
            $hoteles = $pdo->prepare("SELECT * FROM HOTEL WHERE ubicacion = ? LIMIT 1");
            $hoteles->execute([$vuelo['destino']]);
            $hotel = $hoteles->fetch();
            
            if ($hotel) {
                $stmt = $pdo->prepare("INSERT INTO RESERVA (id_vuelo, id_hotel, fecha_reserva, estado) VALUES (?, ?, NOW(), 'confirmada')");
                if ($stmt->execute([$vuelo['id_vuelo'], $hotel['id_hotel']])) {
                    $reservasCreadas++;
                }
            }
        }
        $mensaje = "Se crearon $reservasCreadas reservas automáticas";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Administración - Agencia de Viajes</title>
</head>
<body>
    <div class="container">
        <h1>Panel de Administración</h1>
        
        <!-- Para navegar entre diferentes acciones -->
        <div class="nav-menu">
            <a href="index.php">← Volver al Inicio</a>
            <a href="admin.php?action=dashboard">Dashboard</a>
            <a href="admin.php?action=add_vuelo">Registrar Vuelo</a>
            <a href="admin.php?action=add_hotel">Registrar Hotel</a>
            <a href="admin.php?action=create_reservas">Crear Reservas</a>
        </div>

        <!-- Para mostrar mensajes de éxito o error -->
        <?php if (isset($mensaje)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin: 20px 0; text-align: center;">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin: 20px 0; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Para mostrar el contenido según la acción -->
        <?php if ($action === 'dashboard'): ?>
            <div class="card">
                <h2>Dashboard de Administración</h2>
                <div class="flex-container">
                    <div style="text-align: center; padding: 20px; background: rgba(102, 126, 234, 0.1); border-radius: 10px; margin: 10px;">
                        <h3>Vuelos</h3>
                        <p>Gestiona los vuelos disponibles</p>
                        <a href="admin.php?action=add_vuelo" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px; text-decoration: none;">Registrar Vuelo</a>
                    </div>
                    <div style="text-align: center; padding: 20px; background: rgba(102, 126, 234, 0.1); border-radius: 10px; margin: 10px;">
                        <h3>Hoteles</h3>
                        <p>Gestiona los hoteles registrados</p>
                        <a href="admin.php?action=add_hotel" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px; text-decoration: none;">Registrar Hotel</a>
                    </div>
                    <div style="text-align: center; padding: 20px; background: rgba(102, 126, 234, 0.1); border-radius: 10px; margin: 10px;">
                        <h3>Reservas</h3>
                        <p>Crea reservas automáticas</p>
                        <a href="admin.php?action=create_reservas" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px; text-decoration: none;">Crear Reservas</a>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'add_vuelo'): ?>
            <div class="card">
                <h2>Registrar Nuevo Vuelo</h2>
                <form method="POST">
                    <label>Origen:</label>
                    <input type="text" name="origen" required><br>
                    <label>Destino:</label>
                    <input type="text" name="destino" required><br>
                    <label>Fecha:</label>
                    <input type="date" name="fecha" required><br>
                    <label>Precio:</label>
                    <input type="number" name="precio" min="0" step="0.01" required><br>
                    <label>Plazas Disponibles:</label>
                    <input type="number" name="plazas_disponibles" min="1" required><br>
                    <button type="submit">Registrar Vuelo</button>
                </form>
            </div>

        <?php elseif ($action === 'add_hotel'): ?>
            <div class="card">
                <h2>Registrar Nuevo Hotel</h2>
                <form method="POST">
                    <label>Nombre del Hotel:</label>
                    <input type="text" name="nombre" required><br>
                    <label>Ubicación:</label>
                    <input type="text" name="ubicacion" required><br>
                    <label>Estrellas:</label>
                    <select name="estrellas" required>
                        <option value="1">1 Estrella</option>
                        <option value="2">2 Estrellas</option>
                        <option value="3">3 Estrellas</option>
                        <option value="4">4 Estrellas</option>
                        <option value="5">5 Estrellas</option>
                    </select><br>
                    <label>Precio por Noche:</label>
                    <input type="number" name="precio_noche" min="0" step="0.01" required><br>
                    <button type="submit">Registrar Hotel</button>
                </form>
            </div>

        <?php elseif ($action === 'create_reservas'): ?>
            <div class="card">
                <h2>Crear Reservas Automáticas</h2>
                <p>Esta acción creará reservas automáticas coherentes entre vuelos y hoteles del mismo destino.</p>
                <form method="POST">
                    <button type="submit" style="background: linear-gradient(135deg, #28a745, #20c997);">Crear Reservas Automáticas</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 