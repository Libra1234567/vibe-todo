<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Nur lokal testen, sonst einschränken
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$file = __DIR__ . "/../tasks.json";

// CORS Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$tasks = [];
if (file_exists($file)) {
    $tasks = json_decode(file_get_contents($file), true);
}
if (!is_array($tasks)) {
    $tasks = [];
}

// GET: Alle Aufgaben
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($tasks);
    exit;
}

// POST: Neue Aufgabe hinzufügen ODER Aufgabe löschen (shared-hosting fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

    // Entfernen via POST action=delete&text=...
    if (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
        parse_str(file_get_contents('php://input'), $formData);
        if (isset($formData['action']) && $formData['action'] === 'delete') {
            $text = isset($formData['text']) ? $formData['text'] : '';
            $id = isset($formData['id']) ? $formData['id'] : '';
            $filtered = [];
            foreach ($tasks as $t) {
                $matchesId = $id !== '' && isset($t['id']) && $t['id'] === $id;
                $matchesText = $text !== '' && isset($t['text']) && $t['text'] === $text;
                if (!$matchesId && !$matchesText) {
                    $filtered[] = $t;
                }
            }

            file_put_contents($file, json_encode($filtered, JSON_PRETTY_PRINT));
            echo json_encode(["deleted" => $id !== '' ? $id : $text]);
            exit;
        }
    }

    // JSON payload (reorder or add)
    $data = json_decode(file_get_contents("php://input"), true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid task payload"]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'reorder') {
        $incomingTasks = isset($data['tasks']) && is_array($data['tasks']) ? $data['tasks'] : null;
        if ($incomingTasks === null) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid reorder payload"]);
            exit;
        }

        file_put_contents($file, json_encode($incomingTasks, JSON_PRETTY_PRINT));
        echo json_encode(["status" => "reordered"]);
        exit;
    }

    if (!isset($data['text'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid task payload"]);
        exit;
    }

    $tasks[] = $data;
    file_put_contents($file, json_encode($tasks, JSON_PRETTY_PRINT));
    echo json_encode($data);
    exit;
}

// DELETE: Task löschen (per Text)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $text = isset($_GET['text']) ? $_GET['text'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : '';

    $filtered = [];
    foreach ($tasks as $t) {
        $matchesId = $id !== '' && isset($t['id']) && $t['id'] === $id;
        $matchesText = $text !== '' && isset($t['text']) && $t['text'] === $text;
        if (!$matchesId && !$matchesText) {
            $filtered[] = $t;
        }
    }

    file_put_contents($file, json_encode($filtered, JSON_PRETTY_PRINT));
    echo json_encode(["deleted" => $id !== '' ? $id : $text]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
