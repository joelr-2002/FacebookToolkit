<?php

/**
 * API Endpoint para obtener posts de Facebook
 * Uso: api/posts.php?limit=3
 * 
 * @category  Social_Engineering
 * @package   FacebookToolkit++
 * @author    Wahyu Arif Purnomo <hi@warifp.co>
 * @copyright 2019 WARIFP
 * @license   MIT License <https://opensource.org/licenses/MIT>
 * @version   1.7
 * @link      https://github.com/warifp/FacebookToolkit
 */

require_once __DIR__ . '/../modules/config.php';
require_once __DIR__ . '/../modules/function.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Leer token desde archivo de configuración
$tokenFile = __DIR__ . '/../config/token.txt';
if (!file_exists($tokenFile)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Token no configurado']);
    exit;
}

token = trim(file_get_contents($tokenFile));
$url_based = "https://graph.facebook.com";

// Parámetros
$limit = isset($_GET['limit']) ? min(max(intval($_GET['limit']), 1), 10) : 3;
$fields = 'id,message,created_time,full_picture,permalink_url,shares,reactions.summary(true)';

// Llamada a la API de Facebook
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url_based . "/v3.0/me?fields=feed.limit({$limit}){{$fields}}&access_token={$token}",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode !== 200) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al conectar con Facebook API']);
    exit;
}

decode = json_decode($response, true);

// Formatear respuesta para cards del frontend
$result = [
    'success' => true,
    'count' => 0,
    'posts' => []
];

if (isset($decode['feed']['data'])) {
    foreach ($decode['feed']['data'] as $post) {
        $result['posts'][] = [
            'id' => $post['id'] ?? null,
            'message' => $post['message'] ?? '',
            'created_time' => $post['created_time'] ?? null,
            'formatted_date' => isset($post['created_time']) 
                ? date('d M Y, H:i', strtotime($post['created_time'])) 
                : null,
            'image' => $post['full_picture'] ?? null,
            'permalink' => $post['permalink_url'] ?? null,
            'shares_count' => $post['shares']['count'] ?? 0,
            'reactions_count' => $post['reactions']['summary']['total_count'] ?? 0
        ];
    }
    $result['count'] = count($result['posts']);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);