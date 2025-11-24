<?php

/**
 * @category  Social_Engineering
 * @package   FacebookToolkit++
 * @author    Wahyu Arif Purnomo <hi@warifp.co>
 * @copyright 2019 WARIFP
 * @license   MIT License <https://opensource.org/licenses/MIT>
 * @version   1.7
 * @link      https://github.com/warifp/FacebookToolkit
 * @since     15 June 2019
 * 
 * Get Latest Posts - Endpoint para obtener las últimas publicaciones
 * en formato JSON para consumir desde un frontend
 */

error_reporting(0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Número de posts a obtener (por defecto 3)
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;

// Validar límite (máximo 10 para evitar sobrecarga)
if ($limit < 1) $limit = 1;
if ($limit > 10) $limit = 10;

// Campos a obtener del feed
$fields = 'id,message,created_time,full_picture,permalink_url,shares,reactions.summary(true)';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url_based . "/v3.0/me?fields=feed.limit(" . $limit . "){" . $fields . "}&access_token=" . $token);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($curl);
curl_close($curl);

$decode = json_decode($response, true);

// Preparar respuesta estructurada para el frontend
$result = [
    'success' => true,
    'count' => 0,
    'posts' => []
];

if (isset($decode['feed']['data']) && is_array($decode['feed']['data'])) {
    $posts = $decode['feed']['data'];
    $result['count'] = count($posts);
    
    foreach ($posts as $post) {
        $postData = [
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
        $result['posts'][] = $postData;
    }
} else {
    $result['success'] = false;
    $result['error'] = 'No se pudieron obtener las publicaciones';
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);