<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Conexión a MySQL
$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "consultorio_db";

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$pregunta = $input['pregunta'];

// Buscar información en la base de datos
$info_bd = buscarEnBaseDatos($pregunta, $pdo);

// Crear prompt inteligente para la IA
$prompt = construirPrompt($pregunta, $info_bd);

// Enviar a Ollama
$data = array(
    'model' => 'llama3.2',
    'prompt' => $prompt,
    'stream' => false,
    'options' => array(
        'temperature' => 0.3
    )
);

$options = array(
    'http' => array(
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    )
);

$context = stream_context_create($options);
$result = file_get_contents('http://localhost:11434/api/generate', false, $context);
$response = json_decode($result, true);

echo json_encode(array('respuesta' => $response['response']));

function buscarEnBaseDatos($pregunta, $pdo) {
    $pregunta_lower = strtolower($pregunta);
    $resultados = [];
    
    // Extraer nombres de la pregunta
    $nombres_posibles = extraerNombres($pregunta);
    
    // Buscar por nombres (búsqueda flexible)
    foreach($nombres_posibles as $nombre) {
        $citas_encontradas = buscarPorNombre($nombre, $pdo);
        if(!empty($citas_encontradas)) {
            $resultados[] = [
                'tipo' => 'busqueda_nombre',
                'nombre_buscado' => $nombre,
                'datos' => $citas_encontradas
            ];
        }
    }
    
    // Buscar por fechas
    if(contienePalabrasFecha($pregunta_lower)) {
        $citas_fecha = buscarPorFecha($pregunta_lower, $pdo);
        if(!empty($citas_fecha)) {
            $resultados[] = [
                'tipo' => 'busqueda_fecha',
                'datos' => $citas_fecha
            ];
        }
    }
    
    // Buscar información general si no hay búsquedas específicas
    if(empty($resultados)) {
        if(strpos($pregunta_lower, 'turno') !== false || 
           strpos($pregunta_lower, 'cita') !== false ||
           strpos($pregunta_lower, 'consulta') !== false) {
            $resultados[] = [
                'tipo' => 'info_general',
                'datos' => obtenerInfoGeneral($pdo)
            ];
        }
    }
    
    return $resultados;
}

function extraerNombres($pregunta) {
    $nombres = [];
    
    // Patrones para encontrar nombres
    $patrones = [
        '/(?:llamad[oa]|nombre|paciente|persona|señor|señora)\s+([a-záéíóúñ]+(?:\s+[a-záéíóúñ]+)*)/i',
        '/\b([A-ZÁÉÍÓÚÑ][a-záéíóúñ]{2,})\b/',
        '/([a-záéíóúñ]{3,})/i'
    ];
    
    foreach($patrones as $patron) {
        preg_match_all($patron, $pregunta, $matches);
        if(!empty($matches[1])) {
            $nombres = array_merge($nombres, $matches[1]);
        }
    }
    
    // Filtrar palabras comunes que no son nombres
    $palabras_comunes = ['cuando', 'donde', 'como', 'para', 'tiene', 'esta', 'son', 'que', 'con', 'los', 'las', 'una', 'uno'];
    $nombres = array_filter($nombres, function($nombre) use ($palabras_comunes) {
        return !in_array(strtolower($nombre), $palabras_comunes) && strlen($nombre) > 2;
    });
    
    return array_unique($nombres);
}

function buscarPorNombre($nombre, $pdo) {
    // Búsqueda flexible: LIKE, SOUNDEX y similitud
    $sql = "SELECT * FROM appointments WHERE 
            patient_name LIKE :nombre_like OR 
            patient_name LIKE :nombre_inicio OR
            patient_name LIKE :nombre_fin OR
            SOUNDEX(patient_name) = SOUNDEX(:nombre_soundex)
            ORDER BY appointment_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre_like' => "%$nombre%",
        ':nombre_inicio' => "$nombre%",
        ':nombre_fin' => "%$nombre",
        ':nombre_soundex' => $nombre
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function contienePalabrasFecha($pregunta) {
    $palabras_fecha = ['fecha', 'cuando', 'dia', 'mes', 'año', 'hoy', 'mañana', 'ayer', 'semana', 'proximo', 'anterior'];
    foreach($palabras_fecha as $palabra) {
        if(strpos($pregunta, $palabra) !== false) {
            return true;
        }
    }
    return false;
}

function buscarPorFecha($pregunta, $pdo) {
    $resultados = [];
    
    // Buscar fechas específicas en la pregunta
    if(preg_match('/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $pregunta, $matches)) {
        $fecha = $matches[3] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $sql = "SELECT * FROM appointments WHERE appointment_date = :fecha ORDER BY appointment_time";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':fecha' => $fecha]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Buscar por mes/año
    elseif(preg_match('/(\d{1,2})[\/\-](\d{4})/', $pregunta, $matches)) {
        $mes = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $año = $matches[2];
        $sql = "SELECT * FROM appointments WHERE YEAR(appointment_date) = :año AND MONTH(appointment_date) = :mes ORDER BY appointment_date, appointment_time";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':año' => $año, ':mes' => $mes]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Búsquedas relativas
    elseif(strpos($pregunta, 'hoy') !== false) {
        $sql = "SELECT * FROM appointments WHERE appointment_date = CURDATE() ORDER BY appointment_time";
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    elseif(strpos($pregunta, 'mañana') !== false) {
        $sql = "SELECT * FROM appointments WHERE appointment_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) ORDER BY appointment_time";
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    elseif(strpos($pregunta, 'semana') !== false) {
        $sql = "SELECT * FROM appointments WHERE appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY appointment_date, appointment_time";
        $stmt = $pdo->query($sql);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $resultados;
}

function obtenerInfoGeneral($pdo) {
    $info = [];
    
    // Total de citas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments");
    $info['total_citas'] = $stmt->fetch()['total'];
    
    // Próximas citas
    $stmt = $pdo->query("SELECT * FROM appointments WHERE appointment_date >= CURDATE() ORDER BY appointment_date, appointment_time LIMIT 10");
    $info['proximas_citas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Citas de hoy
    $stmt = $pdo->query("SELECT * FROM appointments WHERE appointment_date = CURDATE() ORDER BY appointment_time");
    $info['citas_hoy'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Pacientes únicos
    $stmt = $pdo->query("SELECT DISTINCT patient_name FROM appointments ORDER BY patient_name");
    $info['pacientes'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    return $info;
}

function construirPrompt($pregunta_original, $info_bd) {
    $prompt = "Eres un asistente inteligente del consultorio médico. ";
    $prompt .= "Tienes acceso completo a la base de datos de citas/turnos y puedes ayudar con consultas sobre pacientes, fechas, horarios, etc.\n\n";
    
    $prompt .= "ESTRUCTURA DE LA BASE DE DATOS:\n";
    $prompt .= "- Tabla: appointments\n";
    $prompt .= "- Campos: id, appointment_date (fecha), appointment_time (hora), patient_name (nombre paciente), obra_social\n\n";
    
    if(!empty($info_bd)) {
        $prompt .= "INFORMACIÓN ENCONTRADA:\n\n";
        foreach($info_bd as $resultado) {
            switch($resultado['tipo']) {
                case 'busqueda_nombre':
                    $prompt .= "🔍 BÚSQUEDA POR NOMBRE: '{$resultado['nombre_buscado']}'\n";
                    if(!empty($resultado['datos'])) {
                        $prompt .= "Citas encontradas:\n";
                        foreach($resultado['datos'] as $cita) {
                            $fecha_formateada = date('d/m/Y', strtotime($cita['appointment_date']));
                            $hora_formateada = date('H:i', strtotime($cita['appointment_time']));
                            $prompt .= "- {$cita['patient_name']} - {$fecha_formateada} a las {$hora_formateada}";
                            if(!empty($cita['obra_social'])) {
                                $prompt .= " (Obra Social: {$cita['obra_social']})";
                            }
                            $prompt .= "\n";
                        }
                    } else {
                        $prompt .= "No se encontraron citas para ese nombre.\n";
                    }
                    break;
                    
                case 'busqueda_fecha':
                    $prompt .= "📅 BÚSQUEDA POR FECHA:\n";
                    if(!empty($resultado['datos'])) {
                        foreach($resultado['datos'] as $cita) {
                            $fecha_formateada = date('d/m/Y', strtotime($cita['appointment_date']));
                            $hora_formateada = date('H:i', strtotime($cita['appointment_time']));
                            $prompt .= "- {$cita['patient_name']} - {$fecha_formateada} a las {$hora_formateada}";
                            if(!empty($cita['obra_social'])) {
                                $prompt .= " (Obra Social: {$cita['obra_social']})";
                            }
                            $prompt .= "\n";
                        }
                    }
                    break;
                    
                case 'info_general':
                    $info = $resultado['datos'];
                    $prompt .= "📊 INFORMACIÓN GENERAL DEL CONSULTORIO:\n";
                    $prompt .= "- Total de citas registradas: {$info['total_citas']}\n";
                    $prompt .= "- Pacientes registrados: " . count($info['pacientes']) . "\n";
                    if(!empty($info['citas_hoy'])) {
                        $prompt .= "- Citas de hoy: " . count($info['citas_hoy']) . "\n";
                    }
                    if(!empty($info['proximas_citas'])) {
                        $prompt .= "- Próximas citas: " . count($info['proximas_citas']) . "\n";
                    }
                    break;
            }
            $prompt .= "\n";
        }
    } else {
        $prompt .= "No se encontró información específica en la base de datos para esta consulta.\n\n";
    }
    
    $prompt .= "PREGUNTA DEL USUARIO: $pregunta_original\n\n";
    $prompt .= "INSTRUCCIONES:\n";
    $prompt .= "- Si encontraste información, preséntala de forma clara y organizada\n";
    $prompt .= "- Si hay búsquedas por nombre aproximado, menciona que encontraste nombres similares\n";
    $prompt .= "- Formatea las fechas en formato dd/mm/yyyy y las horas en formato HH:mm\n";
    $prompt .= "- Si no encontraste información exacta, sugiere búsquedas alternativas\n";
    $prompt .= "- Responde en español de manera amigable y profesional\n";
    
    return $prompt;
}
?>