<?php
/*
 Manjot Singh
 CS 490 Summer
 Beta | Middle
 */
$loginUrl= 'https://web.njit.edu/~pm369/back/beta/login.php';
$studentUrl = 'https://web.njit.edu/~sp2492/front/beta/student.php';

function Login($data_obj, $url){
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
$login_obj = file_get_contents('php://input');
$loginType_obj = json_encode($loginType, true);
echo $loginType_obj;

?>
