<?php
// 1. Importamos la lógica matemática
require_once 'funciones.php';

// 2. Inicializamos las variables
$resultados = null;
$datosIngresados = null;

// 3. Verificamos si el usuario presionó "CALCULAR" (Petición POST)
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

    // Llamamos a la función y guardamos el cálculo
    $resultados = calcularAlmacenamientoCCTV($datosIngresados);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Almacenamiento CCTV</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <!-- Ajustamos el ancho del contenedor dinámicamente si estamos mostrando resultados -->
    <main class="calculator-container" <?php if($resultados) echo 'style="max-width: 600px;"'; ?>>
        
        <?php if ($resultados && $datosIngresados): ?>
            
            <!-- ========================================== -->
            <!-- VISTA 1: RESULTADOS (Se muestra tras calcular) -->
            <!-- ========================================== -->
            <h1>Resultados del Cálculo</h1>
            
            <div class="form-group" style="background-color: #242424; padding: 20px; border-radius: 4px; border-left: 4px solid #333333; margin-bottom: 25px;">
                <h2 style="color: #b3b3b3; font-size: 16px; margin-bottom: 15px;">RESUMEN DE DATOS</h2>
                <ul style="list-style: none; color: #ffffff; line-height: 1.8; font-size: 15px;">
                    <li><strong>Cámaras:</strong> <?php echo htmlspecialchars($datosIngresados['camaras']); ?></li>
                    <li><strong>Resolución:</strong> <?php echo htmlspecialchars($datosIngresados['resolucion']); ?> MP</li>
                    <li><strong>Códec:</strong> <?php echo htmlspecialchars($datosIngresados['codec']); ?></li>
                    <li><strong>FPS:</strong> <?php echo htmlspecialchars($datosIngresados['fps']); ?></li>
                    <li><strong>Grabación por día:</strong> <?php echo htmlspecialchars($datosIngresados['horas_dia']); ?> horas</li>
                    <li><strong>Retención:</strong> <?php echo htmlspecialchars($datosIngresados['dias_retencion']); ?> días</li>
                </ul>
            </div>

            <div class="form-group" style="background-color: #242424; padding: 20px; border-radius: 4px; border-left: 4px solid #e50914;">
                <h2 style="color: #e50914; font-size: 16px; margin-bottom: 15px;">ALMACENAMIENTO REQUERIDO</h2>
                <ul style="list-style: none; color: #ffffff; line-height: 2; font-size: 15px;">
                    <li><strong>1. Bitrate por cámara:</strong> <?php echo number_format($resultados['bitrateCamara'], 2); ?> kbps</li>
                    <li><strong>2. Almacenamiento cámara/día:</strong> <?php echo number_format($resultados['gbCamDia'], 2); ?> GB</li>
                    <li><strong>3. Almacenamiento Total:</strong> <?php echo number_format($resultados['tbTotales'], 2); ?> TB</li>
                    <li><strong>4. Almacenamiento (+20% Margen):</strong> <?php echo number_format($resultados['tbMargen'], 2); ?> TB</li>
                    
                    <li style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #333333; font-size: 18px; color: #e50914;">
                        <strong>5. Disco Recomendado: <br> <?php echo $resultados['discoRecomendado']; ?></strong>
                    </li>
                </ul>
            </div>

            <!-- El botón ahora redirige al mismo index.php (por método GET) para limpiar la pantalla -->
            <a href="index.php" class="btn-submit" style="display: block; text-align: center; text-decoration: none; box-sizing: border-box; margin-top: 25px;">HACER OTRO CÁLCULO</a>
            
        <?php else: ?>

            <!-- ========================================== -->
            <!-- VISTA 2: FORMULARIO (Se muestra al entrar a la página) -->
            <!-- ========================================== -->
            <h1>Calculador de Almacenamiento (NVR/DVR)</h1>
            
            <!-- El action apunta a index.php para enviarse los datos a sí mismo -->
            <form action="index.php" method="POST" id="form-calculadora">
                
                <div class="form-group">
                    <label for="camaras">Cantidad de cámaras:</label>
                    <input type="number" id="camaras" name="camaras" min="1" max="256" required placeholder="Ej. 8">
                </div>

                <div class="form-group">
                    <label for="resolucion">Resolución:</label>
                    <select id="resolucion" name="resolucion" required>
                        <option value="" disabled selected>Seleccione una opción</option>
                        <option value="2">2MP (1080p)</option>
                        <option value="4">4MP</option>
                        <option value="5">5MP</option>
                        <option value="8">8MP (4K)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="codec">Códec de compresión:</label>
                    <select id="codec" name="codec" required>
                        <option value="" disabled selected>Seleccione un códec</option>
                        <option value="H264">H.264</option>
                        <option value="H265">H.265</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fps">Cuadros por segundo (FPS):</label>
                    <input type="number" id="fps" name="fps" min="1" max="30" required placeholder="1 a 30">
                </div>

                <div class="form-group">
                    <label for="horas_dia">Horas de grabación por día:</label>
                    <input type="number" id="horas_dia" name="horas_dia" min="1" max="24" required placeholder="1 a 24">
                </div>

                <div class="form-group">
                    <label for="dias_retencion">Días de retención:</label>
                    <input type="number" id="dias_retencion" name="dias_retencion" min="1" max="365" required placeholder="1 a 365">
                </div>

                <button type="submit" class="btn-submit">CALCULAR</button>
            </form>

        <?php endif; ?>

    </main>
</body>
</html>