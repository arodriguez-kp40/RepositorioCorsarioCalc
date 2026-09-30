<?php

function calcularAlmacenamientoCCTV($datos) {
    // Extraer variables del arreglo de entrada
    $camaras = (int)$datos['camaras'];
    $resolucion = (int)$datos['resolucion'];
    $codec = $datos['codec'];
    $fps = (int)$datos['fps'];
    $horas_dia = (int)$datos['horas_dia'];
    $dias_retencion = (int)$datos['dias_retencion'];

    // 1. Bitrate base (H.264 a 30 FPS) según resolución
    $bitratesBase = [
        2 => 4096,
        4 => 6144,
        5 => 8192,
        8 => 12288
    ];
    
    // Lista de códecs permitidos
    $codecsPermitidos = ['H264', 'H265'];

    // ==========================================
    // VALIDACIÓN ESTRICTA (Protección contra el Inspector)
    // ==========================================
    // Si la resolución no existe en el arreglo, o si el códec no está en la lista permitida, 
    // detenemos todo y retornamos un error.
    if (!isset($bitratesBase[$resolucion]) || !in_array($codec, $codecsPermitidos)) {
        return [
            'error' => 'Error de seguridad: Se han detectado valores manipulados que no son válidos.'
        ];
    }
    
    // Obtener bitrate base validado (ya no asume 2MP por defecto)
    $bitrateBase = $bitratesBase[$resolucion];
    
    // Asignar Factor de códec validado
    $factorCodec = ($codec === 'H265') ? 0.5 : 1.0;

    // Fórmula: bitrateCamara
    $bitrateCamara = $bitrateBase * $factorCodec * ($fps / 30);

    // 2. Almacenamiento por cámara por día (GB)
    $bytesPorSegundo = ($bitrateCamara * 1000) / 8;
    $gbCamDia = ($bytesPorSegundo * 3600 * $horas_dia) / 1000000000;

    // 3. Almacenamiento total requerido (TB)
    $tbTotales = ($gbCamDia * $dias_retencion * $camaras) / 1000;

    // 4. Almacenamiento con margen (TB)
    $tbMargen = $tbTotales * 1.20;

    // 5. Disco Recomendado
    $discosDisponibles = [1, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20];
    $discoRecomendado = "";

    if ($tbMargen <= 20) {
        // Buscar el primer disco que sea igual o mayor al margen
        foreach ($discosDisponibles as $tamano) {
            if ($tamano >= $tbMargen) {
                $discoRecomendado = "1 disco de {$tamano} TB";
                break;
            }
        }
    } else {
        // Si pasa de 20 TB, calcular cuántos discos de 20TB se necesitan
        $cantidadDiscos = ceil($tbMargen / 20);
        $discoRecomendado = "{$cantidadDiscos} discos de 20 TB";
    }

    // Retornar los resultados empaquetados
    return [
        'bitrateCamara'    => $bitrateCamara,
        'gbCamDia'         => $gbCamDia,
        'tbTotales'        => $tbTotales,
        'tbMargen'         => $tbMargen,
        'discoRecomendado' => $discoRecomendado
    ];
}
?>