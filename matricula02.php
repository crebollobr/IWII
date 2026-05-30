<?php
/**
 * API de Matrículas + Agenda — ETEC Interfaces Web II
 * ----------------------------------------------------
 * Este é o ARQUIVO MODELO. No servidor existem 20 cópias idênticas:
 * matricula01.php, matricula02.php, ... matricula20.php.
 *
 * Cada aluno escolhe um número e usa o SEU arquivo. Cada arquivo é um
 * sandbox isolado: usa o próprio banco SQLite, derivado do nome do
 * arquivo (matricula05.php -> matricula05.db). Por isso NÃO existe
 * "aluno_id": o isolamento é o próprio arquivo.
 *
 * DOIS RECURSOS, escolhidos por ?recurso= :
 *   recurso=matriculas -> { id, nome, cpf, curso, email, ativo, criado_em }
 *   recurso=agenda     -> { id, dia, hora, texto, criado_em }
 *
 * ENDPOINTS (NN = seu número, ex: 05):
 *   GET    matriculaNN.php?recurso=matriculas        -> lista tudo
 *   GET    matriculaNN.php?recurso=matriculas&id=7   -> um registro
 *   POST   matriculaNN.php?recurso=matriculas        -> cria   (corpo: campos)
 *   PUT    matriculaNN.php?recurso=matriculas&id=7   -> atualiza (corpo: campos)
 *   DELETE matriculaNN.php?recurso=matriculas&id=7   -> apaga
 *
 * Aceita corpo POST/PUT como JSON ou form-urlencoded.
 * Banco SQLite criado automaticamente na 1ª chamada. Sem login — didático.
 */

// ---------- CORS ----------
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ---------- Banco próprio deste arquivo ----------
$base   = pathinfo(__FILE__, PATHINFO_FILENAME);   // ex: "matricula05"
$dbPath = __DIR__ . '/' . $base . '.db';

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS matriculas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome  TEXT NOT NULL,
        cpf   TEXT NOT NULL DEFAULT '',
        curso TEXT NOT NULL DEFAULT '',
        email TEXT NOT NULL DEFAULT '',
        ativo INTEGER NOT NULL DEFAULT 1,
        criado_em TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS agenda (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        dia   TEXT NOT NULL,
        hora  TEXT NOT NULL,
        texto TEXT NOT NULL,
        criado_em TEXT NOT NULL DEFAULT (datetime('now','localtime'))
    )");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'banco indisponível']);
    exit;
}

// ---------- Helpers ----------
function entrada() {
    $raw = file_get_contents('php://input');
    $j = json_decode($raw, true);
    if (is_array($j)) return $j;          // veio como JSON
    parse_str($raw, $p);                  // veio como form-urlencoded
    if (!empty($p)) return $p;
    return $_POST;
}
function jsonResp($d, $http = 200) { http_response_code($http); echo json_encode($d); exit; }
function exige($cond, $msg, $http = 400) {
    if (!$cond) { http_response_code($http); echo json_encode(['erro' => $msg]); exit; }
}

// ---------- Configuração dos recursos ----------
$recursos = [
    'matriculas' => ['nome', 'cpf', 'curso', 'email', 'ativo'],
    'agenda'     => ['dia', 'hora', 'texto'],
];
$recurso = isset($_GET['recurso']) ? $_GET['recurso'] : 'matriculas';
exige(isset($recursos[$recurso]), 'recurso inválido (use matriculas ou agenda)', 404);
$campos = $recursos[$recurso];
$tabela = $recurso;   // nome da tabela = nome do recurso

$metodo = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($metodo) {

    case 'GET':
        if ($id) {
            $st = $db->prepare("SELECT * FROM $tabela WHERE id = ?");
            $st->execute([$id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            exige($row, 'não encontrado', 404);
            jsonResp($row);
        }
        $rows = $db->query("SELECT * FROM $tabela ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        jsonResp($rows);
        break;

    case 'POST':
        $b = entrada();
        $cols = []; $vals = [];
        foreach ($campos as $c) {
            if (isset($b[$c])) { $cols[] = $c; $vals[] = $b[$c]; }
        }
        exige(!empty($cols), 'nenhum campo enviado');
        // exigências mínimas por recurso
        if ($recurso === 'matriculas') exige(trim($b['nome'] ?? '') !== '', 'nome é obrigatório');
        if ($recurso === 'agenda') {
            exige(trim($b['dia'] ?? '') !== '' && trim($b['hora'] ?? '') !== '', 'dia e hora são obrigatórios');
            exige(trim($b['texto'] ?? '') !== '', 'texto é obrigatório');
        }
        $ph = implode(',', array_fill(0, count($cols), '?'));
        $st = $db->prepare("INSERT INTO $tabela (" . implode(',', $cols) . ") VALUES ($ph)");
        $st->execute($vals);
        $novoId = $db->lastInsertId();
        $st2 = $db->prepare("SELECT * FROM $tabela WHERE id = ?");
        $st2->execute([$novoId]);
        jsonResp($st2->fetch(PDO::FETCH_ASSOC), 201);
        break;

    case 'PUT':
        exige($id, 'id é obrigatório (na query string)');
        $b = entrada();
        $sets = []; $vals = [];
        foreach ($campos as $c) {
            if (isset($b[$c])) { $sets[] = "$c = ?"; $vals[] = $b[$c]; }
        }
        exige(!empty($sets), 'nenhum campo para atualizar');
        $vals[] = $id;
        $st = $db->prepare("UPDATE $tabela SET " . implode(',', $sets) . " WHERE id = ?");
        $st->execute($vals);
        $st2 = $db->prepare("SELECT * FROM $tabela WHERE id = ?");
        $st2->execute([$id]);
        $row = $st2->fetch(PDO::FETCH_ASSOC);
        exige($row, 'não encontrado', 404);
        jsonResp($row);
        break;

    case 'DELETE':
        exige($id, 'id é obrigatório');
        $st = $db->prepare("DELETE FROM $tabela WHERE id = ?");
        $st->execute([$id]);
        jsonResp(['ok' => true, 'id' => $id]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'método não suportado']);
}
