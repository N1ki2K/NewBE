<?php
// ====================================================
// CORS Headers Handler
// ====================================================

// 1. Дефинирайте всички домейни, които имат право да достъпват API-то.
const ALLOWED_ORIGINS = [
    'https://nukgsz.com',      // Вашият производствен домейн
    'http://localhost:5173'  // Вашият сървър за локална разработка
];

// Вземаме домейна, от който идва заявката
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// 2. Проверяваме дали идващият домейн е в нашия "бял списък".
if (in_array($origin, ALLOWED_ORIGINS)) {
    // Ако е в списъка, го разрешаваме изрично.
    header("Access-Control-Allow-Origin: $origin");
}

// 3. Задаваме останалите нужни хедъри
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400"); // Кешира preflight за 24 часа

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
