<?php
// 1. Importamos la lógica matemática
require_once 'funciones.php';

// 2. Inicializamos las variables
$resultados = null;
$datosIngresados = [];
$errores = []; // Nuevo arreglo para guardar los errores de validación

// 3. Verificamos si el usuario presionó "CALCULAR"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recolectar datos. Usamos cadena vacía por defecto para las validaciones
    $datosIngresados = [
        'camaras'        => $_POST['camaras'] ?? '',
        'resolucion'     => $_POST['resolucion'] ?? '',
        'codec'          => $_POST['codec'] ?? '',
        'fps'            => $_POST['fps'] ?? '',
        'horas_dia'      => $_POST['horas_dia'] ?? '',
        'dias_retencion' => $_POST['dias_retencion'] ?? ''
    ];

    // ==========================================
    // VALIDACIONES DEL SERVIDOR
    // ==========================================
    
    if (empty($datosIngresados['camaras']) || $datosIngresados['camaras'] < 1 || $datosIngresados['camaras'] > 256) {
        $errores['camaras'] = "Ingresa un número válido de cámaras (1-256).";
    }

    if (!in_array($datosIngresados['resolucion'], ['2', '4', '5', '8'])) {
        $errores['resolucion'] = "Por favor, selecciona una resolución válida.";
    }

    if (!in_array($datosIngresados['codec'], ['H264', 'H265'])) {
        $errores['codec'] = "Por favor, selecciona un códec válido.";
    }

    if (empty($datosIngresados['fps']) || $datosIngresados['fps'] < 1 || $datosIngresados['fps'] > 30) {
        $errores['fps'] = "Los FPS deben estar entre 1 y 30.";
    }

    if (empty($datosIngresados['horas_dia']) || $datosIngresados['horas_dia'] < 1 || $datosIngresados['horas_dia'] > 24) {
        $errores['horas_dia'] = "Las horas deben ser de 1 a 24.";
    }

    if (empty($datosIngresados['dias_retencion']) || $datosIngresados['dias_retencion'] < 1 || $datosIngresados['dias_retencion'] > 365) {
        $errores['dias_retencion'] = "Los días de retención deben ser de 1 a 365.";
    }

    // ==========================================
    // CÁLCULO (Solo si no hay errores)
    // ==========================================
    if (empty($errores)) {
        $resultados = calcularAlmacenamientoCCTV($datosIngresados);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Almacenamiento CCTV</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        /* Estilo para los mensajes de error */
        .error-msg {
            color: #e50914; /* Rojo para destacar el error */
            font-size: 13px;
            margin-top: 5px;
            display: block;
            font-weight: bold;
        }
        /* Resalta el borde del input si tiene error */
        .input-error {
            border: 2px solid #e50914 !important;
        }
    </style>
</head>
<body>
    <main class="calculator-container" <?php if($resultados) echo 'style="max-width: 600px;"'; ?>>
        
        <?php if ($resultados && empty($errores)): ?>
            <!-- ========================================== -->
            <!-- VISTA DE RESULTADOS (Se muestra si se calculó sin errores) -->
            <!-- ========================================== -->
            <h1>Resultados del Cálculo</h1>
            
            <div class="form-group" style="background-color: #242424; padding: 20px; border-radius: 4px; border-left: 4px solid #e50914; margin-bottom: 25px;">
                <ul style="list-style: none; color: #ffffff; line-height: 2; font-size: 15px;">
                    <li><strong>1. Bitrate por cámara:</strong> <?php echo number_format($resultados['bitrateCamara'], 2); ?> kbps</li>
                    <li><strong>2. Almacenamiento cámara/día:</strong> <?php echo number_format($resultados['gbCamDia'], 2); ?> GB</li>
                    <li><strong>3. Almacenamiento Total (Neto):</strong> <?php echo number_format($resultados['tbTotales'], 2); ?> TB</li>
                    <li><strong>4. Almacenamiento (+20% Margen):</strong> <?php echo number_format($resultados['tbMargen'], 2); ?> TB</li>
                    
                    <li style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #333333; font-size: 18px; color: #e50914;">
                        <strong>5. Disco Recomendado: <br> <?php echo $resultados['discoRecomendado']; ?></strong>
                    </li>
                </ul>
            </div>
            <h2 style="text-align: center; color: #b3b3b3; font-size: 18px; margin-bottom: 20px; text-transform: uppercase;">Ajustar Parámetros</h2>
        <?php else: ?>
            <h1>Calculador de Almacenamiento (NVR/DVR)</h1>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- FORMULARIO (Mantiene los datos y muestra errores) -->
        <!-- ========================================== -->
        <form action="index.php" method="POST" id="form-calculadora">
            
            <div class="form-group">
                <label for="camaras">Cantidad de cámaras:</label>
                <input type="number" id="camaras" name="camaras" placeholder="Ej. 8" 
                       class="<?php echo isset($errores['camaras']) ? 'input-error' : ''; ?>"
                       value="<?php echo htmlspecialchars($datosIngresados['camaras'] ?? ''); ?>">
                <?php if(isset($errores['camaras'])) echo "<span class='error-msg'>{$errores['camaras']}</span>"; ?>
            </div>

            <div class="form-group">
                <label for="resolucion">Resolución:</label>
                <select id="resolucion" name="resolucion" class="<?php echo isset($errores['resolucion']) ? 'input-error' : ''; ?>">
                    <option value="" disabled <?php echo empty($datosIngresados['resolucion']) ? 'selected' : ''; ?>>Seleccione una opción</option>
                    <option value="2" <?php echo (($datosIngresados['resolucion'] ?? '') == '2') ? 'selected' : ''; ?>>2MP (1080p)</option>
                    <option value="4" <?php echo (($datosIngresados['resolucion'] ?? '') == '4') ? 'selected' : ''; ?>>4MP</option>
                    <option value="5" <?php echo (($datosIngresados['resolucion'] ?? '') == '5') ? 'selected' : ''; ?>>5MP</option>
                    <option value="8" <?php echo (($datosIngresados['resolucion'] ?? '') == '8') ? 'selected' : ''; ?>>8MP (4K)</option>
                </select>
                <?php if(isset($errores['resolucion'])) echo "<span class='error-msg'>{$errores['resolucion']}</span>"; ?>
            </div>

            <div class="form-group">
                <label for="codec">Códec de compresión:</label>
                <select id="codec" name="codec" class="<?php echo isset($errores['codec']) ? 'input-error' : ''; ?>">
                    <option value="" disabled <?php echo empty($datosIngresados['codec']) ? 'selected' : ''; ?>>Seleccione un códec</option>
                    <option value="H264" <?php echo (($datosIngresados['codec'] ?? '') == 'H264') ? 'selected' : ''; ?>>H.264</option>
                    <option value="H265" <?php echo (($datosIngresados['codec'] ?? '') == 'H265') ? 'selected' : ''; ?>>H.265</option>
                </select>
                <?php if(isset($errores['codec'])) echo "<span class='error-msg'>{$errores['codec']}</span>"; ?>
            </div>

            <div class="form-group">
                <label for="fps">Cuadros por segundo (FPS):</label>
                <input type="number" id="fps" name="fps" placeholder="1 a 30"
                       class="<?php echo isset($errores['fps']) ? 'input-error' : ''; ?>"
                       value="<?php echo htmlspecialchars($datosIngresados['fps'] ?? ''); ?>">
                <?php if(isset($errores['fps'])) echo "<span class='error-msg'>{$errores['fps']}</span>"; ?>
            </div>

            <div class="form-group">
                <label for="horas_dia">Horas de grabación por día:</label>
                <input type="number" id="horas_dia" name="horas_dia" placeholder="1 a 24"
                       class="<?php echo isset($errores['horas_dia']) ? 'input-error' : ''; ?>"
                       value="<?php echo htmlspecialchars($datosIngresados['horas_dia'] ?? ''); ?>">
                <?php if(isset($errores['horas_dia'])) echo "<span class='error-msg'>{$errores['horas_dia']}</span>"; ?>
            </div>

            <div class="form-group">
                <label for="dias_retencion">Días de retención:</label>
                <input type="number" id="dias_retencion" name="dias_retencion" placeholder="1 a 365"
                       class="<?php echo isset($errores['dias_retencion']) ? 'input-error' : ''; ?>"
                       value="<?php echo htmlspecialchars($datosIngresados['dias_retencion'] ?? ''); ?>">
                <?php if(isset($errores['dias_retencion'])) echo "<span class='error-msg'>{$errores['dias_retencion']}</span>"; ?>
            </div>

            <button type="submit" class="btn-submit">
                <?php echo ($resultados && empty($errores)) ? 'RECALCULAR' : 'CALCULAR'; ?>
            </button>
            
            <?php if ($resultados || !empty($errores)): ?>
                <a href="index.php" style="display: block; text-align: center; color: #b3b3b3; text-decoration: none; margin-top: 15px; font-size: 14px;">Limpiar y empezar de nuevo</a>
            <?php endif; ?>

        </form>

    </main>
</body>
</html>