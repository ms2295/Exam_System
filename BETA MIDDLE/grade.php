<?php
    /*Manjot Singh
     CS 490 Summer
     BETA | Middle
     */
$handIn_url = 'https://web.njit.edu/~pm369/back/beta/insertGrade.php';
$test_url = 'https://web.njit.edu/~pm369/back/beta/getTest.php';
$cases_url = 'https://web.njit.edu/~pm369/back/beta/getTestCases.php';
$ques_url = 'https://web.njit.edu/~pm369/back/beta/displayQuestions.php';
$max_points = 0;
$points_recieved_arr = array();
function handIn($data_obj, $url){
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
function getTest($url, $testID){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $testID);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $r_decoded = json_decode($response, true);
    curl_close($ch);
    return $r_decoded;
}
function getCases($data_obj, $url){
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

function writeFile($python_file, $student_res){
    $handle = fopen($python_file, 'w') or die("Hello...");
    fwrite($handle, $student_res);
    fclose($handle);
}

function appendFile($python_file, $case_arr){
    $handle = fopen($python_file, 'a') or die("Can't append...");

    for ($case = 0; $case < count($case_arr); $case++){
        fwrite($handle,"\nprint(".$case_arr[$case]["Testcase"].")");
    }
    fclose($handle);
}

function compileMe($py_file){
    #$file ;
    $cmd = 'python ./'.$py_file;
    $output = array();
    exec($cmd, $output,$return_status);
    $output[] = $return_status;
    return $output;
}

function compileTestCases($py_file){
    $cmd = 'python ./'.$py_file;
    $output = array();
    exec($cmd, $output);
    return $output;
}

function gradeMe($case, $std_ans, $func_case, $case_arr, $max_points){
    $points = 0.0;
    $feedback = '';

    switch ($case){
        case 0:

			writeFile('python.py',$std_ans);
			$output = compileMe('python.py');
            if(end($output) == 0 && $std_ans != null){
                $points ++;
                $feedback = $feedback."Your program compiled!\t====> +".($points/5)*$max_points."\n";

            } else{
                $feedback = $feedback."Your program failed to compile.\t====> 0 Points";
                break;
            }
        case 1:
            if (strpos($std_ans, $func_case) == FALSE) {
				$feedback = $feedback."The function name does not match the requirements.\t====> -".(1/5)*$max_points;
				$split = explode("(", $std_ans);
				$split2 = explode(" ", $split[0]);
				$split2[1] = $func_case;
				$split2 = implode(" ",$split2);
				$answer = $split2."(".$split[1];
				writeFile('python.py', $answer);
            } else{
                $points+=1;
                $feedback = $feedback."\nThe function name matches!\t====> +".(1/5)*$max_points."\n";
            }
        case 2:
            appendFile('python.py', $case_arr);
            $output = compileTestCases('python.py');
            $ratio = 0;
            $case_ratio = 1/count($output);
            for ($case = 0; $case < count($output); $case ++){
                if ($output[$case] == $case_arr[$case]['Answer']){
                    $feedback = $feedback."\nYour output for ".$case_arr[$case]['Testcase'].
                        ": ".$output[$case]."\nCorrect output: ".$case_arr[$case]['Answer'].
                        "\t====> +".($case_ratio*(3/5))*$max_points."\n";
                    $ratio ++;
                } else{
                    $feedback = $feedback."\nYour output for ".$case_arr[$case]['Testcase'].": ".
                        $output[$case]."\nCorrect output: ".$case_arr[$case]['Answer'].
                        "\t====> -".($case_ratio*(3/5))*$max_points."\n";
                    continue;
                }
            }
            $ratio = $ratio/count($output);
            $points+=3*$ratio;
    }
    $points = $points/5.0;
    $array = ['Points' => $points, 'Feedback' => $feedback];
    return $array;
}

function percentGrade($points_array, $maxpoints){
    $sum = 0.0;
    if ($maxpoints != 0){
        for ($i=0;$i<count($points_array); $i++){
            $sum += $points_array[$i];
        }
        $grade = ($sum/$maxpoints)*100;
		$roundGrade = round($grade, 2);
		return $roundGrade;
    } else return 0;

}

$ans_obj = file_get_contents('php://input');
$ans_decoded = json_decode($ans_obj, true);

$username = $ans_decoded['User'];
$std_test ['Username'] = $username;
$std_test ['Question'] = array();
$testID = intval($ans_decoded['testID']);

$buf_obj = getTest($test_url, $testID);
$test_obj = $buf_obj['examData']; // getting questions
for ($i=0; $i < count($ans_decoded['Answers']); $i++){
    $max_points += $test_obj[$i]['Points'];
    $get_case = getCases(json_encode(["QuestionID" => $test_obj[$i]['QuestionId']]), $cases_url);
   
    $grade_res = gradeMe(0, $ans_decoded['Answers'][$i],$test_obj[$i]['Signature'], $get_case, $test_obj[$i]['Points']);
    $points_recieved_arr [] = $grade_res['Points']*$test_obj[$i]['Points'];
    $std_test ['Question'][$i] = array('QuestionId' => $test_obj[$i]['QuestionId'],
        'Response' => $ans_decoded['Answers'][$i], 'Points' => $points_recieved_arr[$i],
        'MaxPoints' => $test_obj[$i]['Points'], 'Feedback' => $grade_res['Feedback']);
}
echo json_encode($test_obj);
$std_test ['Grade'] = percentGrade($points_recieved_arr, $max_points);
$handIn_obj = json_encode($std_test);
//echo $handIn_obj;
$handIn_res = handIn($handIn_obj, $handIn_url);
//echo json_encode($handIn_res, true);
?>
