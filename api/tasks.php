<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Nur lokal testen, sonst einschränken
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$file = __DIR__ . "/../tasks.json";

// JSON-Datei anlegen, falls nicht vorhanden
if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}

// CORS Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// GET: Alle Aufgaben
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo file_get_contents($file);
    exit;
}

// POST: Neue Aufgabe hinzufügen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) { http_response_code(400); exit("Invalid JSON"); }

    $tasks = json_decode(file_get_contents($file), true);
    $tasks[] = $data;
    file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
    echo json_encode($data);
    exit;
}

// DELETE: Task löschen (per Text)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $text = isset($_GET['text']) ? $_GET['text'] : '';
    $tasks = json_decode(file_get_contents($file), true);

    $filtered = array();
    foreach($tasks as $t) {
        if ($t['text'] !== $text) {
            $filtered[] = $t;
        }
    }

    file_put_contents($file, json_encode($filtered, JSON_PRETTY_PRINT));
    echo json_encode(array("deleted"=>$text));
    exit;
}


// DELETE: Aufgabe löschen (per Text-Field)
// if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
// $text = isset($_GET['text']) ? $_GET['text'] : '';
    // $tasks = json_decode(file_get_contents($file), true);
    // $tasks = array_filter($tasks, fn($t) => $t['text'] !== $text);
    // file_put_contents($file, json_encode(array_values($tasks), JSON_PRETTY_PRINT));
    // echo json_encode(["deleted" => $text]);
    // exit;
// }

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
