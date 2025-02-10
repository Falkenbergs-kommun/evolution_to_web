<?php

// Ladda ned fil via Evolution Document Web

require_once('functions.php');

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
`fbg_evolution_documents`
LEFT JOIN fbg_evolution_copy ON fbg_evolution_documents.`id` = fbg_evolution_copy.`document` AND fbg_evolution_documents.`folderid` = fbg_evolution_copy.`folder` AND fbg_evolution_documents.`version` = fbg_evolution_copy.`version`
WHERE
fbg_evolution_copy.`document` IS NULL
LIMIT 0, 100;";

$stmt = $pdo->prepare($sql);
$stmt->execute();

foreach ($stmt as $row) {
    $row['documentmeta'] = json_decode($row['documentmeta'], true);
    $documents[] = $row;
    evolutionCopy($row['folderid'], $row['id'], $row['version'], $row['extension']);
}


exit;


function evolutionCopy($folder, $document, $version, $extension)
{

    global $pdo;

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => EV_BASEURL . 'download/' . $document . '/' . $folder,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => true,
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

    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

    curl_close($curl);

    $headerStr = substr($response, 0, $headerSize);
    $bodyStr = substr($response, $headerSize);

    $headers = headersToArray($headerStr);

    $contenttype = trim($headers["Content-Type"]);

    $savePath = EV_SAVEPATH;

    var_dump($contenttype);

    if ($contenttype) {
        // Save the downloaded file to the server
        $filePath = $savePath . $folder . '/' . $document . $extension;

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        if (file_put_contents($filePath, $bodyStr) !== false) {
            echo "File saved successfully: " . $filePath;

            $sql = "INSERT INTO fbg_evolution_copy (
            `document`, 
            `folder`, 
            `version`
        ) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE
            `version` = VALUES(`version`),
            `folder` = VALUES(`folder`)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $document,
                $folder,
                $version
            ]);
        } else {
            echo "Error saving the file.";
        }
    } else {
        echo "Invalid content type. Expected application/pdf.";
    }
}



function headersToArray($str)
{
    $headers = array();
    $headersTmpArray = explode("\r\n", $str);
    for ($i = 0; $i < count($headersTmpArray); ++$i) {
        // we dont care about the two \r\n lines at the end of the headers
        if (strlen($headersTmpArray[$i]) > 0) {
            // the headers start with HTTP status codes, which do not contain a colon so we can filter them out too
            if (strpos($headersTmpArray[$i], ":")) {
                $headerName = substr($headersTmpArray[$i], 0, strpos($headersTmpArray[$i], ":"));
                $headerValue = substr($headersTmpArray[$i], strpos($headersTmpArray[$i], ":") + 1);
                $headers[$headerName] = $headerValue;
            }
        }
    }
    return $headers;
}
