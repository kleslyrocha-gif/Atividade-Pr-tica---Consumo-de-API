<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode(["erro" => "Busca vazia"]);
    exit;
}

$q = urlencode($_GET['q']);
$page = $_GET['page'] ?? 1;

$url = "https://openlibrary.org/search.json?q={$q}&page={$page}";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["erro" => curl_error($ch)]);
    exit;
}

curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['docs'])) {
    echo json_encode(["erro" => "Erro na API"]);
    exit;
}

$resultado = [];

foreach (array_slice($data['docs'], 0, 10) as $livro) {

    $capa = isset($livro['cover_i']) 
        ? "https://covers.openlibrary.org/b/id/{$livro['cover_i']}-M.jpg"
        : "https://via.placeholder.com/80x120?text=Sem+Capa";

    $resultado[] = [
        "titulo" => $livro['title'] ?? "Sem título",
        "autor" => isset($livro['author_name']) ? implode(", ", $livro['author_name']) : "Desconhecido",
        "ano" => $livro['first_publish_year'] ?? "N/A",
        "capa" => $capa
    ];
}

echo json_encode($resultado);