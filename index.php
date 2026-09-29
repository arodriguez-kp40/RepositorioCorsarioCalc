<?php
// Incluir el archivo con la lógica matemática
require_once 'funciones.php';

// Variables para manejar los datos
$resultados = null;
$datosIngresados = null;

// Verificar si la petición viene del formulario por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recolectar datos de forma segura
    $datosIngresados = [
        'camaras'        => $_POST['camaras'] ?? 0,
        'resolucion'     => $_POST['resolucion'] ?? 0,
        'codec'          => $_POST['codec'] ?? '',
        'fps'            => $_POST['fps'] ?? 0,
        'horas_dia'      => $_POST['horas_dia'] ?? 0,
        'dias_retencion' => $_POST['dias_retencion'] ?? 0
    ];

    // Llamar a la función principal
    $resultados = calcularAlmacenamientoCCTV($datosIngresados);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - Calculadora de CCTV</title>
    <!-- Importamos la paleta negra/roja que hicimos antes -->
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <main class="calculator-container" style="max-width: 600px;">
        
        <?php if ($resultados && $datosIngresados): ?>
            <h1>Resultados del Cálculo</h1>
            
            <!-- 6. Resumen de los datos ingresados -->
            <div class="form-group" style="background-color: #242424; padding: 20px; border-radius: 4px; border-left: 4px solid #e50914; margin-bottom: 25px;">
                <h2 style="color: #e50914; font-size: 16px; margin-bottom: 15px;">RESUMEN DE DATOS INGRESADOS</h2>
                <ul style="list-style: none; color: #ffffff; line-height: 1.8; font-size: 15px;">
                    <li><strong>Cámaras:</strong> <?php echo htmlspecialchars($datosIngresados['camaras']); ?></li>
                    <li><strong>Resolución:</strong> <?php echo htmlspecialchars($datosIngresados['resolucion']); ?> MP</li>
                    <li><strong>Códec:</strong> <?php echo htmlspecialchars($datosIngresados['codec']); ?></li>
                    <li><strong>FPS:</strong> <?php echo htmlspecialchars($datosIngresados['fps']); ?></li>
                    <li><strong>Grabación por día:</strong> <?php echo htmlspecialchars($datosIngresados['horas_dia']); ?> horas</li>
                    <li><strong>Retención:</strong> <?php echo htmlspecialchars($datosIngresados['dias_retencion']); ?> días</li>
                </ul>
            </div>

            <!-- Resultados matemáticos -->
            <div class="form-group" style="background-color: #242424; padding: 20px; border-radius: 4px; border-left: 4px solid #e50914;">
                <h2 style="color: #e50914; font-size: 16px; margin-bottom: 15px;">REQUERIMIENTOS DE ALMACENAMIENTO</h2>
                <ul style="list-style: none; color: #ffffff; line-height: 2; font-size: 15px;">
                    <li><strong>1. Bitrate por cámara:</strong> <?php echo number_format($resultados['bitrateCamara'], 2); ?> kbps</li>
                    <li><strong>2. Almacenamiento cámara/día:</strong> <?php echo number_format($resultados['gbCamDia'], 2); ?> GB</li>
                    <li><strong>3. Almacenamiento Total (Neto):</strong> <?php echo number_format($resultados['tbTotales'], 2); ?> TB</li>
                    <li><strong>4. Almacenamiento (+20% Margen):</strong> <?php echo number_format($resultados['tbMargen'], 2); ?> TB</li>
                    
                    <!-- Destacamos fuertemente la recomendación final del disco -->
                    <li style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #333333; font-size: 18px; color: #ffff;">
                        <strong>5. Disco Recomendado: <br> <?php echo $resultados['discoRecomendado']; ?></strong>
                    </li>
                </ul>
            </div>

            <!-- Botón para regresar al formulario y calcular de nuevo -->
            <a href="formulario.html" class="btn-submit" style="display: block; text-align: center; text-decoration: none; box-sizing: border-box; margin-top: 25px;">HACER OTRO CÁLCULO</a>
            
        <?php else: ?>
            
            <!-- Mensaje de error por si se accede a index.php directamente sin pasar por el formulario -->
            <h1>Acceso A La Calculadora</h1>
            <p style="text-align: center; color: #b3b3b3; margin-bottom: 20px;">Por favor, ingresa los datos desde la calculadora principal para obtener un resultado.</p>
            <a href="formulario.html" class="btn-submit" style="display: block; text-align: center; text-decoration: none; box-sizing: border-box;">IR A LA CALCULADORA</a>
            
        <?php endif; ?>

    </main>
</body>
</html>