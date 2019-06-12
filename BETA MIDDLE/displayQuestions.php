<?php
    //Manjot Singh
    //CS 490 Summer
    //BETA | Middle
    $url = 'https://web.njit.edu/~pm369/back/beta/displayQuestions.php';
function displayQ($data_obj, $url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_obj);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $r_decoded = json_decode($response, true);
    curl_close($ch);
    return $r_decoded;
}
$displayQ_obj = file_get_contents('php://input');

$displayQ_res = getStudents($displayQ_obj, $url);

$response_obj = json_encode($displayQ_res, true);
echo $response_obj; 
?>
