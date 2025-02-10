<?php

// Ladda mappar och dokument från Evolution till lokal databas

require_once('functions.php');



$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => EV_BASEURL . 'folders?',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json'
    ),
));

$response = curl_exec($curl);

curl_close($curl);

$level = 0;

if ($entries = json_decode($response, true)) {
    foreach ($entries as $entry) {

        if (!isset($entry['publishType'])) {
            $entry['publishType'] = '';
        }

        $sql = "REPLACE INTO fbg_evolution_folders (
            `id`, 
            `level`,
            `evolution_type`, 
            `unitcode`, 
            `publishtype`, 
            `name`
        ) VALUES (?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $entry['id'],
            $level,
            ($entry['type'] == null) ? '' : $entry['type'],
            ($entry['unitCode'] == null) ? '' : $entry['unitCode'],
            ($entry['publishType'] == null) ? '' : $entry['publishType'],
            ($entry['name'] == null) ? '' : $entry['name']
        ]);
    }
    foreach ($entries as $entry) {
        evFolder($entry['id']);

        echo 'Root folder: ' . $entry['name'] . "\n";
    }

    // Gallra

    $sql = "DELETE FROM `fbg_evolution_documents` WHERE (`ts_update` + INTERVAL 20 MINUTE) <( SELECT MAX(ts_update) FROM fbg_evolution_documents )";
    $stmt = $pdo->query($sql);

    $sql = "DELETE FROM `fbg_evolution_folders` WHERE (`ts_update` + INTERVAL 20 MINUTE) <( SELECT MAX(ts_update) FROM fbg_evolution_folders )";
    $stmt = $pdo->query($sql);
} else {
    // Hämtning misslyckades
}



fixEncoding('`fbg_evolution_documents`', 'name');

exit;

function evFolder($id = '')
{

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => EV_BASEURL . 'folders/' . $id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    if ($json_response = json_decode($response, true)) {

        storeEvFolder($id, 1, $json_response['folders'], $json_response['documents']);
    }
}

function storeEvFolder($id, $level, $folders, $documents)
{

    global $pdo;

    foreach ($folders as $entry) {

        if (!isset($entry['publishType'])) {
            $entry['publishType'] = '';
        }

        $sql = "REPLACE INTO fbg_evolution_folders (
            `id`, 
            `parent`,
            `level`,
            `evolution_type`, 
            `unitcode`, 
            `publishtype`, 
            `name`
        ) VALUES (?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $entry['id'],
            $id,
            $level,
            ($entry['type'] == null) ? '' : $entry['type'],
            ($entry['unitCode'] == null) ? '' : $entry['unitCode'],
            ($entry['publishType'] == null) ? '' : $entry['publishType'],
            ($entry['name'] == null) ? '' : $entry['name']
        ]);

        storeEvFolder($entry['id'], $level + 1, $entry['folders'], $entry['documents']);
    }

    storeEvDocs($documents);
}

function storeEvDocs($documents)
{

    global $pdo;

    foreach ($documents as $entry) {

        $createdate = strtotime($entry['createDate']);
        $createdate = date('Y-m-d H:i:s', $createdate);

        // echo 'Document: ' . $entry['name'] . " " .  $createdate ."\n";

        $sql = "REPLACE INTO fbg_evolution_documents (
            `id`, 
            `folderid`,
            `rootfolderid`,
            `name`, 
            `extension`, 
            `version`, 
            `createdate`,
            `evolution_type`,
            `documentmeta`
        ) VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $entry['id'],
            ($entry['folderId'] == null) ? '' : $entry['folderId'],
            ($entry['rootFolderId'] == null) ? '' : $entry['rootFolderId'],
            ($entry['name'] == null) ? '' : $entry['name'],
            ($entry['extension'] == null) ? '' : $entry['extension'],
            ($entry['version'] == null) ? '' : $entry['version'],
            $createdate,
            ($entry['type'] == null) ? '' : $entry['type'],
            json_encode($entry)
        ]);
    }
}

function fixEncoding($table, $column)
{
    global $pdo;

    $sql = "UPDATE
    $table
SET
    `$column` =
REPLACE
    (`$column`, 0x6fcc88, 'ö')
WHERE
    `$column` LIKE CONCAT('%', 0x6fcc88, '%')";

    $stmt = $pdo->query($sql);


    $sql = "UPDATE
$table
SET
`$column` =
REPLACE
(`$column`, 0x4fcc88, 'Ö')
WHERE
`$column` LIKE CONCAT('%', 0x4fcc88, '%')";

    $stmt = $pdo->query($sql);

    $sql = "UPDATE
$table
SET
`$column` =
REPLACE
(`$column`, 0x61cc88, 'ä')
WHERE
`$column` LIKE CONCAT('%', 0x61cc88, '%')";

    $stmt = $pdo->query($sql);

    $sql = "UPDATE
$table
SET
`$column` =
REPLACE
(`$column`, 0x41cc88, 'Ä')
WHERE
`$column` LIKE CONCAT('%', 0x41cc88, '%')";

    $stmt = $pdo->query($sql);

    $sql = "UPDATE
$table
SET
`$column` =
REPLACE
(`$column`, 0x61cc8a, 'å')
WHERE
`$column` LIKE CONCAT('%', 0x61cc8a, '%')";

    $stmt = $pdo->query($sql);

    $sql = "UPDATE
$table
SET
`$column` =
REPLACE
(`$column`, 0x41cc8a, 'Å')
WHERE
`$column` LIKE CONCAT('%', 0x41cc8a, '%')";

    $stmt = $pdo->query($sql);
}
