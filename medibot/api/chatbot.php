<?php
// Iniciar sesión para manejar el estado del usuario
session_start();

// Incluir archivo de conexión a la base de datos
require_once '../config/database.php';

// Cabeceras para JSON
header('Content-Type: application/json');

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Obtener datos enviados
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['step']) || !isset($data['responses'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit;
}

$step = intval($data['step']);
$responses = $data['responses'];

// Definir las preguntas del chatbot
$questions = [
    1 => "Hola, soy MediBot 🤖. ¿Qué síntomas estás experimentando?",
    2 => "¿Hace cuánto tiempo tienes estos síntomas? (días, semanas)",
    3 => "¿Tienes alguna condición médica preexistente o alergias a medicamentos?"
];

// Si estamos en el paso final, recomendar medicamentos
if ($step > count($questions)) {
    try {
        // Analizar las respuestas para generar recomendaciones
        $sintomas = strtolower($responses[0]);
        $duracion = strtolower($responses[1]);
        $condiciones = strtolower($responses[2]);
        
        // Palabras clave para buscar en la base de datos
        $keywords = [];
        
        // Extraer palabras clave de los síntomas
        if (strpos($sintomas, 'dolor') !== false) {
            $keywords[] = 'dolor';
            
            if (strpos($sintomas, 'cabeza') !== false) {
                $keywords[] = 'cabeza';
            }
            if (strpos($sintomas, 'muscular') !== false || strpos($sintomas, 'músculo') !== false) {
                $keywords[] = 'muscular';
            }
            if (strpos($sintomas, 'garganta') !== false) {
                $keywords[] = 'garganta';
            }
        }
        
        if (strpos($sintomas, 'fiebre') !== false) {
            $keywords[] = 'fiebre';
        }
        
        if (strpos($sintomas, 'tos') !== false) {
            $keywords[] = 'tos';
        }
        
        if (strpos($sintomas, 'gripe') !== false || strpos($sintomas, 'gripa') !== false) {
            $keywords[] = 'gripe';
        }
        
        if (strpos($sintomas, 'alergia') !== false) {
            $keywords[] = 'alergia';
        }
        
        if (strpos($sintomas, 'estómago') !== false || strpos($sintomas, 'estomago') !== false || 
            strpos($sintomas, 'digestivo') !== false || strpos($sintomas, 'diarrea') !== false) {
            $keywords[] = 'estomago';
        }
        
        // Verificar contraindicaciones
        $contraindicaciones = [];
        if (strpos($condiciones, 'alergia') !== false) {
            if (strpos($condiciones, 'aspirina') !== false || strpos($condiciones, 'ácido acetilsalicílico') !== false) {
                $contraindicaciones[] = 'aspirina';
            }
            if (strpos($condiciones, 'ibuprofeno') !== false) {
                $contraindicaciones[] = 'ibuprofeno';
            }
        }
        
        if (strpos($condiciones, 'embarazo') !== false || strpos($condiciones, 'embarazada') !== false) {
            $contraindicaciones[] = 'no recomendado en embarazo';
        }
        
        // Construir consulta SQL
        $sql = "SELECT * FROM medicamentos WHERE 1=1";
        $params = [];
        
        // Añadir condiciones basadas en palabras clave
        if (!empty($keywords)) {
            $sql .= " AND (";
            $conditions = [];
            
            foreach ($keywords as $index => $keyword) {
                $paramName = ":keyword" . $index;
                $conditions[] = "nombre LIKE $paramName OR descripcion LIKE $paramName OR categoria LIKE $paramName";
                $params[$paramName] = "%$keyword%";
            }
            
            $sql .= implode(" OR ", $conditions) . ")";
        }
        
        // Excluir medicamentos contraindicados
        if (!empty($contraindicaciones)) {
            foreach ($contraindicaciones as $index => $contra) {
                $paramName = ":contra" . $index;
                $sql .= " AND nombre NOT LIKE $paramName AND descripcion NOT LIKE $paramName";
                $params[$paramName] = "%$contra%";
            }
        }
        
        // Limitar resultados
        $sql .= " LIMIT 3";
        
        // Preparar y ejecutar la consulta
        $stmt = $conn->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        
        // Obtener resultados
        $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no hay resultados, dar una respuesta genérica
        if (empty($medicamentos)) {
            echo json_encode([
                'success' => true,
                'message' => "Basado en tus síntomas, te recomendaría consultar a un médico para un diagnóstico preciso. No he encontrado medicamentos específicos para tu caso en nuestra base de datos.",
                'recommendations' => []
            ]);
            exit;
        }
        
        // Formatear respuesta
        $message = "Basado en tus síntomas, aquí tienes algunas recomendaciones. Recuerda que siempre es mejor consultar a un profesional de la salud:";
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'recommendations' => $medicamentos
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al consultar la base de datos: ' . $e->getMessage()
        ]);
    }
    
    exit;
}

// Si no estamos en el paso final, devolver la siguiente pregunta
echo json_encode([
    'success' => true,
    'step' => $step,
    'question' => $questions[$step]
]);
