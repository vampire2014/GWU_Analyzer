<?php
$questionnaire=$_GET['questionnaire'];


include "connection.php";
include('graph/phpgraphlib.php');
$dir = plugin_dir_path( __FILE__ );

$msg = '';

$questions = mysql_query("SELECT question_id, topic, title
FROM wp_question_dim A, wp_questionnaire_dim B
WHERE A.questionnaire_id = B.questionnaire_id
AND A.questionnaire_id = $questionnaire");
$survey = mysql_fetch_row($questions);
$msg = $msg.'<h4>Topic: '.$survey[1].'</h4><h5>Title: '.$survey[2].'</h5>';

 if (mysql_data_seek($questions, 0))
 {}
 	
if(mysql_num_rows($questions) > 0){
	while($question = mysql_fetch_assoc($questions)) {
    
		$qid = $question['question_id'];

		// For The Questionnaire information
		$result = mysql_query("SELECT question_id, questionnaire_id, question_text, ans_type, response_content, COUNT( * ) AS total
		FROM wp_question_dim Q, wp_question_response QR
		WHERE questionnaire_id = $questionnaire
		AND question_id = $qid
		AND QR.questionnaire_dim_questionnaire_id = Q.questionnaire_id
		AND QR.question_dim_question_id = Q.question_id
		GROUP BY response_content");
		if(mysql_num_rows($result) > 0){

			$row = mysql_fetch_row($result);
			if (mysql_data_seek($result, 0))
 {}
			$text = '<ul>';
			// if the answer type is text
			if(trim($row[3])=='Text Box'){
				while($rows = mysql_fetch_assoc($result)) {
					$question_id = $rows['question_id'];
					$question_txt = $rows['question_text'];
					$text = $text.'<li>'.$rows['response_content'].'</li>';
				}
				$text = $text.'</ul>';
				$msg =$msg.'<table class="table">
				<tbody>
				<tr class="tr">
				<td colspan="4" class="td"><b>'.$question_id.'. '.$question_txt.'<b><br></td>
				</tr>
				<tr class="tr">
				<td colspan="4" class="td">'.$text.'</td>
				</tr>
				</tbody>
				</table>';
			} else{
				$dataArray = array();
				while($rows = mysql_fetch_assoc($result)) {
					$question_id = $rows['question_id'];
					$question_txt = $rows['question_text'];
					$key = $rows['response_content'];
					$value = $rows['total'];
					$dataArray[$key]=$value;
				}



				//graph

			
				$graph = new PHPGraphLib(800,350,$dir.'chart'.$qid.$questionnaire.'.png');
				$graph->addData($dataArray);
				$graph->setBarColor('green');
				$graph->setTitle($question_id.'. '.$question_txt);
				$graph->setupYAxis(12, 'green');
				$graph->setupXAxis(8);
				$graph->setGrid(true);
				$graph->setTitleLocation('left');
				$graph->setDataValues(true);
				$graph->setDataValueColor('red');
				$graph->setTextColor('black');
				$graph->setTitleColor('blue');
				$graph->setXValuesHorizontal(true);
				$graph->createGraph();


				$msg =$msg.'<table class="table">
				<tbody>
				<tr class="tr">
				<td colspan="4" class="td"><b>'.$question_id.'. '.$question_txt.'</b><br></td>
				</tr>
				<tr class="tr">
				<td colspan="4" class="td">
				<img align="center" src="/wp-content/plugins/QuestionPeachAnalyzer/chart'.$qid.$questionnaire.'.png" ><br>

				</td>
				</tr>
				</tbody>
				</table>';
			}



		} else {

			$msg = $msg.'<table class="table">
			<tbody>
			<tr class="tr">
			<td colspan="4" class="td">There is no result available for this question</td>
			</tr></tr>
			</tbody>
			</table>';
		}
	}
}


echo $msg;
?>