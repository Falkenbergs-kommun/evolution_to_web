<?php

// Endpoint som listar alla evolution-dokument 2023-12-05

require_once('functions.php');

$output = [];
$output['data'] = [];

$sql = "SELECT
fbg_evolution_documents.`id`,
fbg_evolution_documents.`folderid`,
fbg_evolution_documents.`rootfolderid`,
fbg_evolution_documents.`name`,
fbg_evolution_documents.`extension`,
fbg_evolution_documents.`version`,
fbg_evolution_documents.`createdate`,
fbg_evolution_documents.`evolution_type`,
fbg_evolution_documents.`documentmeta`,
fbg_evolution_documents.`ts_update`
FROM
`fbg_evolution_documents`";

$stmt = $pdo->prepare($sql);
$stmt->execute();

foreach ($stmt as $row) {
    $row['url'] = EV_WEBURL . $row['folderid'] . '/' . $row['id'] . $row['extension'];
    $row['documentmeta'] = json_decode($row['documentmeta'], true);
    $documents[] = $row;
    $output['data'][] = $row;
}

jsonHeader();
echo json_encode($output);

exit;