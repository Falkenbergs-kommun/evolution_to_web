<?php

// Ladda ned fil via Evolution Document Web

require_once('functions.php');

$folder = htmlspecialchars($_GET['folder']);
$document = htmlspecialchars($_GET['document']);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => EV_BASEURL . 'download/'.$document.'/'.$folder.'.pdf',
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
    'Accept: application/pdf'
  ),
));

$response = curl_exec($curl);

$headerSize = curl_getinfo( $curl , CURLINFO_HEADER_SIZE );

curl_close($curl);

$headerStr = substr( $response , 0 , $headerSize );
$bodyStr = substr( $response , $headerSize );

$headers = headersToArray( $headerStr );

$contenttype = $headers["Content-Type"];
$contentdisposition = $headers["Content-Disposition"];

header("Content-type:$contenttype");
header("Content-Disposition:$contentdisposition");

echo $bodyStr;

exit;


function headersToArray( $str )
{
    $headers = array();
    $headersTmpArray = explode( "\r\n" , $str );
    for ( $i = 0 ; $i < count( $headersTmpArray ) ; ++$i )
    {
        // we dont care about the two \r\n lines at the end of the headers
        if ( strlen( $headersTmpArray[$i] ) > 0 )
        {
            // the headers start with HTTP status codes, which do not contain a colon so we can filter them out too
            if ( strpos( $headersTmpArray[$i] , ":" ) )
            {
                $headerName = substr( $headersTmpArray[$i] , 0 , strpos( $headersTmpArray[$i] , ":" ) );
                $headerValue = substr( $headersTmpArray[$i] , strpos( $headersTmpArray[$i] , ":" )+1 );
                $headers[$headerName] = $headerValue;
            }
        }
    }
    return $headers;
}