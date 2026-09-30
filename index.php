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
    // CÁLCULO
    // ==========================================
    if (empty($errores)) {
        $resultados = calcularAlmacenamientoCCTV($datosIngresados);
        
        if (isset($resultados['error'])) {
            $errores['general'] = $resultados['error'];
            $resultados = null; 
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
    <link rel="stylesheet" href="estilos.css?v=<?php echo time(); ?>">
</head>
<body>
    <main class="calculator-container <?php echo ($resultados && empty($errores)) ? 'results-mode' : ''; ?>">
        
        <?php if ($resultados && empty($errores)): ?>
            <!-- ========================================== -->
            <!-- VISTA DE RESULTADOS Y DATOS INGRESADOS -->
            <!-- ========================================== -->
            <h1 class="main-title">Resultados del Cálculo</h1>
            
            <!-- SECCIÓN 1: DATOS INGRESADOS (MODULAR GRID CSS) -->
            <section class="card-seccion seccion-datos">
                <h2 class="card-titulo">Datos Ingresados</h2>
                <div class="datos-grid">
                    <div class="dato-box">
                        <span class="dato-etiqueta">Cámaras</span>
                        <span class="dato-valor"><?php echo htmlspecialchars($datosIngresados['camaras']); ?></span>
                    </div>
                    <div class="dato-box">
                        <span class="dato-etiqueta">Resolución</span>
                        <span class="dato-valor">
                            <?php 
                                $textosResolucion = ['2' => '2MP (1080p)', '4' => '4MP', '5' => '5MP', '8' => '8MP (4K)'];
                                echo htmlspecialchars($textosResolucion[$datosIngresados['resolucion']] ?? $datosIngresados['resolucion']); 
                            ?>
                        </span>
                    </div>
                    <div class="dato-box">
                        <span class="dato-etiqueta">Códec</span>
                        <span class="dato-valor"><?php echo htmlspecialchars($datosIngresados['codec']); ?></span>
                    </div>
                    <div class="dato-box">
                        <span class="dato-etiqueta">FPS</span>
                        <span class="dato-valor"><?php echo htmlspecialchars($datosIngresados['fps']); ?></span>
                    </div>
                    <div class="dato-box">
                        <span class="dato-etiqueta">Horas / Día</span>
                        <span class="dato-valor"><?php echo htmlspecialchars($datosIngresados['horas_dia']); ?> hrs</span>
                    </div>
                    <div class="dato-box">
                        <span class="dato-etiqueta">Retención</span>
                        <span class="dato-valor"><?php echo htmlspecialchars($datosIngresados['dias_retencion']); ?> días</span>
                    </div>
                </div>
            </section>

            <!-- SECCIÓN 2: RESULTADOS OBTENIDOS -->
            <section class="card-seccion seccion-resultados">
                <h2 class="card-titulo">Almacenamiento Calculado</h2>
                <ul class="resultados-lista">
                    <li class="resultado-item">
                        <span class="res-texto"><strong class="res-num">1.</strong> Bitrate por cámara</span>
                        <span class="res-valor"><?php echo number_format($resultados['bitrateCamara'], 2); ?> kbps</span>
                    </li>
                    <li class="resultado-item">
                        <span class="res-texto"><strong class="res-num">2.</strong> Almacenamiento cámara/día</span>
                        <span class="res-valor"><?php echo number_format($resultados['gbCamDia'], 2); ?> GB</span>
                    </li>
                    <li class="resultado-item">
                        <span class="res-texto"><strong class="res-num">3.</strong> Almacenamiento Total (Neto)</span>
                        <span class="res-valor"><?php echo number_format($resultados['tbTotales'], 2); ?> TB</span>
                    </li>
                    <li class="resultado-item">
                        <span class="res-texto"><strong class="res-num">4.</strong> Almacenamiento (+20% Margen)</span>
                        <span class="res-valor"><?php echo number_format($resultados['tbMargen'], 2); ?> TB</span>
                    </li>
                </ul>

                <div class="disco-recomendado-box">
                    <span class="disco-etiqueta">5. Disco Recomendado</span>
                    <span class="disco-resultado"><?php echo $resultados['discoRecomendado']; ?></span>
                </div>
            </section>
            
            <h2 class="ajustar-titulo">Ajustar Parámetros</h2>
        <?php else: ?>
            <h1 class="main-title">Calculador de Almacenamiento (NVR/DVR)</h1>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- FORMULARIO DE ENTRADA -->
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
                <a href="index.php" class="reset-link">Limpiar y empezar de nuevo</a>
            <?php endif; ?>

        </form>
    </main>
</body>
</html>