<?php
require_once 'funciones.php';

$resultados = null;
$datosIngresados = [];
$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $datosIngresados = [
        'camaras'        => $_POST['camaras'] ?? '',
        'resolucion'     => $_POST['resolucion'] ?? '',
        'codec'          => $_POST['codec'] ?? '',
        'fps'            => $_POST['fps'] ?? '',
        'horas_dia'      => $_POST['horas_dia'] ?? '',
        'dias_retencion' => $_POST['dias_retencion'] ?? ''
    ];

    // ==========================================
    // VALIDACIONES ESTRICTAS EN CASCADA
    // ==========================================
    // Esta variable nos avisará si un campo superior falló,
    // para bloquear y mostrar error en todos los campos inferiores.
    $huboErrorPrevio = false;
    $mensajeCascada = "Corrige el error en el campo anterior primero.";

    // 1. Cámaras
    if ($huboErrorPrevio) {
        $errores['camaras'] = $mensajeCascada;
    } else {
        if ($datosIngresados['camaras'] === '' || filter_var($datosIngresados['camaras'], FILTER_VALIDATE_INT) === false || $datosIngresados['camaras'] < 1 || $datosIngresados['camaras'] > 256) {
            $errores['camaras'] = "Especifica un número entero válido (1 a 256).";
            $huboErrorPrevio = true;
        }
    }

    // 2. Resolución
    if ($huboErrorPrevio) {
        $errores['resolucion'] = $mensajeCascada;
    } else {
        if ($datosIngresados['resolucion'] === '' || !in_array($datosIngresados['resolucion'], ['2', '4', '5', '8'], true)) {
            $errores['resolucion'] = "Especifica una resolución válida de la lista.";
            $huboErrorPrevio = true;
        }
    }

    // 3. Códec
    if ($huboErrorPrevio) {
        $errores['codec'] = $mensajeCascada;
    } else {
        if ($datosIngresados['codec'] === '' || !in_array($datosIngresados['codec'], ['H264', 'H265'], true)) {
            $errores['codec'] = "Especifica un códec válido de la lista.";
            $huboErrorPrevio = true;
        }
    }

    // 4. FPS
    if ($huboErrorPrevio) {
        $errores['fps'] = $mensajeCascada;
    } else {
        if ($datosIngresados['fps'] === '' || filter_var($datosIngresados['fps'], FILTER_VALIDATE_INT) === false || $datosIngresados['fps'] < 1 || $datosIngresados['fps'] > 30) {
            $errores['fps'] = "Especifica los cuadros por segundo (1 a 30).";
            $huboErrorPrevio = true;
        }
    }

    // 5. Horas por día
    if ($huboErrorPrevio) {
        $errores['horas_dia'] = $mensajeCascada;
    } else {
        if ($datosIngresados['horas_dia'] === '' || filter_var($datosIngresados['horas_dia'], FILTER_VALIDATE_INT) === false || $datosIngresados['horas_dia'] < 1 || $datosIngresados['horas_dia'] > 24) {
            $errores['horas_dia'] = "Especifica las horas de grabación (1 a 24).";
            $huboErrorPrevio = true;
        }
    }

    // 6. Retención
    if ($huboErrorPrevio) {
        $errores['dias_retencion'] = $mensajeCascada;
    } else {
        if ($datosIngresados['dias_retencion'] === '' || filter_var($datosIngresados['dias_retencion'], FILTER_VALIDATE_INT) === false || $datosIngresados['dias_retencion'] < 1 || $datosIngresados['dias_retencion'] > 365) {
            $errores['dias_retencion'] = "Especifica los días de retención (1 a 365).";
            $huboErrorPrevio = true;
        }
    }

    // ==========================================
    // CÁLCULO (Solo si no hubo intentos de romper el formulario)
    // ==========================================
    if (empty($errores)) {
        $resultados = calcularAlmacenamientoCCTV($datosIngresados);
        
        // Si la función nos devuelve el array de error (por manipulación avanzada)
        if (isset($resultados['error'])) {
            $errores['general'] = $resultados['error'];
            $resultados = null; // Anulamos los resultados para que no intente imprimirlos
        }
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
        /* Error general que viene de funciones.php */
        .alerta-general {
            background-color: #330000;
            color: #e50914;
            padding: 15px;
            border: 1px solid #e50914;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <main class="calculator-container" <?php if($resultados) echo 'style="max-width: 600px;"'; ?>>
        
        <?php if ($resultados && empty($errores)): ?>
            <!-- ========================================== -->
            <!-- VISTA DE RESULTADOS -->
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
        <!-- FORMULARIO -->
        <!-- ========================================== -->
        <form action="index.php" method="POST" id="form-calculadora">
            
            <?php if(isset($errores['general'])): ?>
                <div class="alerta-general"><?php echo $errores['general']; ?></div>
            <?php endif; ?>

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