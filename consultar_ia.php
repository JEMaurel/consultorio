<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$input = json_decode(file_get_contents('php://input'), true);
$pregunta = $input['pregunta'];

// Conexión con Ollama
$data = array(
    'model' => 'llama3.2',
    'prompt' => $pregunta,
    'stream' => false
);

$options = array(
    'http' => array(
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    )
);

$context = stream_context_create($options);
$result = file_get_contents('http://localhost:11434/api/generate', false, $context);
$response = json_decode($result, true);

echo json_encode(array('respuesta' => $response['response']));
?>