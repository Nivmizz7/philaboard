<?php
header('Content-Type: application/json');

$db_file = __DIR__ . '/db.json';
$upload_dir = __DIR__ . '/uploads';

if (!file_exists($db_file)) {
    file_put_contents($db_file, json_encode([]));
}
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$data = json_decode(file_get_contents($db_file), true);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode($data);
    exit;
}

if ($method === 'POST') {
    // Upload image si présent
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . '/' . $fileName);
    } else {
        $fileName = $_POST['currentImage'] ?? '';
    }

    if ($_POST['action'] === 'add') {
        $data[] = [
            'id' => uniqid(),
            'nom' => $_POST['nom'] ?? '',
            'annee' => $_POST['annee'] ?? '',
            'nyt' => $_POST['nyt'] ?? '',
            'album' => $_POST['album'] ?? '',
            'pays' => $_POST['pays'] ?? '',
            'categorie' => $_POST['categorie'] ?? '',
            'etat' => $_POST['etat'] ?? '',
            'quantite' => $_POST['quantite'] ?? 1,
            'image' => $fileName
        ];
    }
    elseif ($_POST['action'] === 'update') {
        foreach ($data as &$stamp) {
            if ($stamp['id'] === $_POST['id']) {
                $stamp['nom'] = $_POST['nom'] ?? '';
                $stamp['annee'] = $_POST['annee'] ?? '';
                $stamp['nyt'] = $_POST['nyt'] ?? '';
                $stamp['album'] = $_POST['album'] ?? '';
                $stamp['pays'] = $_POST['pays'] ?? '';
                $stamp['categorie'] = $_POST['categorie'] ?? '';
                $stamp['etat'] = $_POST['etat'] ?? '';
                $stamp['quantite'] = $_POST['quantite'] ?? 1;
                $stamp['image'] = $fileName;
                break;
            }
        }
    }
    elseif ($_POST['action'] === 'delete') {
        $data = array_values(array_filter($data, fn($s) => $s['id'] !== $_POST['id']));
    }

    file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode(['status' => 'ok']);
}
