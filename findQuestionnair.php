<?php
$questionnaire=$_GET['questionnaire'];
include "connection.php";
$url =  plugins_url( 'resultByQuestion.php' , __FILE__ );
$msg = '';

// question
$result = mysql_query("select question_id, question_text from wp_question_dim
where questionnaire_id = $questionnaire");
$msg = '	<select name="question" onChange="getResult(\''.$url.'?questionnaire='.$questionnaire.'&question=\'+this.value)"><option value=""> Select Question </option>';
while($rows = mysql_fetch_assoc($result)) {
	$msg = $msg.'<option value="'.$rows['question_id'].'">'.$rows['question_text'].'</option>';
}

$msg = $msg.'</select><br>
<br>';

//location
$country = array();
$result = mysql_query("SELECT DISTINCT location_id, city, country
FROM wp_question_response QR, wp_location_dim L
WHERE L.location_id = QR.location_dim_location_id
AND QR.question_dim_questionnaire_id = $questionnaire");
$msg = $msg.'	<select><option value=""> Select Location </option>';
while($rows = mysql_fetch_assoc($result)) {
	$country = $rows['country'];
	$msg = $msg.'<option value=\"$rows[location_id]\">'.$rows['city'].' - '.$rows['country'].'</option>';
}
$msg = $msg.'</select><br>
<br>';



//responder
$result = mysql_query("SELECT DISTINCT R.respondee_id, username
FROM wp_respondee_dim R, wp_question_response QR, wp_questionnaire_dim Q
WHERE R.respondee_id = QR.respondee_dim_respondee_id
AND QR.question_dim_questionnaire_id = Q.questionnaire_id
AND username <>  'NULL'
AND Q.questionnaire_id = $questionnaire");
$msg = $msg.'	<select><option value="-1"> Select Responder </option>';
while($rows = mysql_fetch_assoc($result)) {
	$msg = $msg.'<option value=\"$rows[respondee_id]\">'.$rows['username'].'</option>';
}

$msg = $msg.'</select><br>
<br>';

echo $msg;

?>

