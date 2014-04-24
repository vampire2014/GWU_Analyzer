<?php

/*
Plugin Name: QuestionPeach - Analyzer
Plugin URI:	http://questionpeach.com
Description: To Analyze the questionnior
Author: Analyzer Team
Version:	1.0
*/


/**************Hooks******************/

/* Runs when plugin is activated */
register_activation_hook(__FILE__,array('Analyzer','analyzer_install'));

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, array('Analyzer','analyzer_remove') );

/**************ShortCode******************/
add_shortcode( 'QP_JS', 'getJavaScript' );
add_shortcode( "QP_CSS", "getCss" );
add_shortcode( "QP_AdminForm", "getAdminForm" );
add_shortcode( "QP_QuestionnaireList", "getQuestionnaireList" );
add_shortcode( "QP_GeoChart", "getGeoChart" );


/**************PHP Code******************/
class Analyzer{


	/*install function, which create tables and
	create analyzer user interface (GUI)*/

	function analyzer_install(){
		global $wpdb;
		//ibou's create table code

		//alem's create page code


		$the_page_title = 'QuestionPeach Analyzer GUI';
		$the_page_name = 'QuestionPeach-analyzer';

		// the menu entry...
		delete_option("my_plugin_page_title");
		add_option("my_plugin_page_title", $the_page_title, '', 'yes');
		// the slug...
		delete_option("my_plugin_page_name");
		add_option("my_plugin_page_name", $the_page_name, '', 'yes');
		// the id...
		delete_option("my_plugin_page_id");
		add_option("my_plugin_page_id", '0', '', 'yes');

		$the_page = get_page_by_title( $the_page_title );

		if ( ! $the_page ) {

			$admin = new Analyzer();
			// Create post object
			$_p = array();
			$_p['post_title'] = $the_page_title;
			$_p['post_content'] = $admin->{'creatUI'}();
			$_p['post_status'] = 'publish';
			$_p['post_type'] = 'page';
			$_p['comment_status'] = 'closed';
			$_p['ping_status'] = 'closed';
			$_p['post_category'] = array(1); // the default 'Uncatrgorised'

			// Insert the post into the database
			$the_page_id = wp_insert_post( $_p );

		}
		else {
			// the plugin may have been previously active and the page may just be trashed...

			$the_page_id = $the_page->ID;

			//make sure the page is not trashed...
			$the_page->post_status = 'publish';
			$the_page_id = wp_update_post( $the_page );

		}

		delete_option( 'my_plugin_page_id' );
		add_option( 'my_plugin_page_id', $the_page_id );
	}

	/*remove function, which drop tables and
	remove analyzer user interface (GUI)*/

	function analyzer_remove(){
		global $wpdb;
		//ibou's drop table code

		//alem's remove page code
		$the_page_title = 'QuestionPeach Analyzer GUI';
		$the_page_name = 'QuestionPeach-analyzer';


		//  the id of our page...
		$the_page_id = get_option( 'my_plugin_page_id' );
		if( $the_page_id ) {

			wp_delete_post( $the_page_id ); // this will trash, not delete

		}

		delete_option("my_plugin_page_title");
		delete_option("my_plugin_page_name");
		delete_option("my_plugin_page_id");
	}

	//createUI to create a admin panel for analyzer
	function creatUI(){
		$admin = new Analyzer();
		$resultMsg = '';
		if(isset($_SESSION['message']))
		{


			$resultMsg = '<div class="message" class="updated highlight">';

			foreach($_SESSION['message'] as &$error)
			{

				$resultMsg = $resultMsg.'<p> <strong><?php echo "{$error}"; ?></strong></p>';
			}
			unset($_SESSION['message']);

			$resultMsg = $resultMsg.'</div>';

		}

		$resultMsg = $resultMsg. do_shortcode('[QP_CSS]');
		$resultMsg = $resultMsg.'[QP_JS]';
		$resultMsg = $resultMsg.'[QP_GeoChart]';
		$resultMsg = $resultMsg.'[QP_AdminForm]';



		return $resultMsg;
	}



	function getTopPanel(){
		// get top panel

		$msg = '<table class="table">
		<tbody>';

		return $msg;
	}





	function export_data(){
		// to export data form the the builder table and copy to star schema

		if(true){
			$_SESSION['message'] = array("You have sucessfully Export the database.");

		} else {
			$_SESSION['message'] = array("ERROR".mysql_error());

		}
	}

	function refresh_data(){
		//to refresh the star schema or analyzer tables

	}


}


global $msg;
global $analyzer_tbls;

////////////////////////// Plugin shortcode ////////////////////////////////
add_shortcode('analyzer_create_shtz', function(){
	return analyzer_create_tbl();
});

add_shortcode('analyzer_drop_shtz', function(){
	return analyzer_drop_tblXXX();
});

add_shortcode('analyzer_migrate_builder_data_shtz', function(){
	return analyzer_migrate_builder_data();
});

add_shortcode('analyzer_cron_job_actv_shtz', function(){
	analyzer_cron_job_activation();
	return true;
});

add_shortcode('analyzer_cron_job_deactv_shtz', function(){
	analyzer_cron_job_deactivation();
	return true;
});



add_shortcode('rpt', function(){

	return analyzer_show_tbls();
});

/////////////////////////////analyzer_create_tbl///////////////////
function analyzer_create_tbl()
{
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;
	$analyzer_tbls= array('wp_respondee_dim', 'wp_time_dim', 'wp_question_dim', 'wp_location_dim', 'wp_questionnaire_dim','wp_question_response');

	$msg.="<h4> 1. Verifying if table the following tables exist:</h4><br> ".loop_arr($analyzer_tbls);

	if( $wpdb->get_var( "SHOW TABLES LIKE '".$analyzer_tbls[0]."'" ) != $analyzer_tbls[0] )
	{
		$msg.="<h4>2. Now creating tables </h4><i>".loop_arr($analyzer_tbls)."</i>";

		$sql = "

		CREATE TABLE wp_respondee_dim (
		respondee_id INTEGER UNSIGNED NOT NULL,
		survey_completed BOOL NULL,
		survey_taken_date DATE NULL,
		username VARCHAR(100) NULL,
		ip VARCHAR(20) NULL,
		duration TIME NULL,
		PRIMARY KEY(respondee_id)
		);

		CREATE TABLE wp_time_dim (
		time_id BIGINT NOT NULL,
		date DATE NOT NULL,
		day_2 CHAR(10) NULL,
		day_of_week INT NULL,
		day_of_month INT NULL,
		day_of_year INT NULL,
		weekend CHAR(10) NOT NULL DEFAULT 'Weekday',
		week_of_year CHAR(2) NULL,
		month_3 CHAR(10) NULL,
		month_of_year CHAR(2) NULL,
		quarter_of_year INT NULL,
		year_3 INT NULL,
		PRIMARY KEY(time_id),
		UNIQUE INDEX time_dim_uniq(date)
		);

		DELETE FROM wp_time_dim;

		DROP TABLE IF EXISTS numbers_small;
		CREATE TABLE numbers_small (number INT);

		INSERT INTO numbers_small VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9);


		DROP TABLE IF EXISTS numbers;

		CREATE TABLE numbers (number BIGINT);
		INSERT INTO numbers
		SELECT thousands.number * 1000 + hundreds.number * 100 + tens.number * 10 + ones.number
		FROM numbers_small thousands, numbers_small hundreds, numbers_small tens, numbers_small ones
		LIMIT 1000000;



		INSERT INTO wp_time_dim (time_id, date)
		SELECT number, DATE_ADD( '2010-01-01', INTERVAL number DAY )
		FROM numbers
		WHERE DATE_ADD( '2010-01-01', INTERVAL number DAY ) BETWEEN '2010-01-01' AND '2015-12-31'
		ORDER BY number;


		UPDATE wp_time_dim SET
		day_2             = DATE_FORMAT( date, \"%W\" ),
		day_of_week     = DAYOFWEEK(date),
		day_of_month    = DATE_FORMAT( date, \"%d\" ),
		day_of_year     = DATE_FORMAT( date, \"%j\" ),
		weekend         = IF( DATE_FORMAT( date, \"%W\" ) IN ('Saturday','Sunday'), 'Weekend', 'Weekday'),
		week_of_year    = DATE_FORMAT( date, \"%V\" ),
		month_3           = DATE_FORMAT( date, \"%M\"),
		month_of_year   = DATE_FORMAT( date, \"%m\"),
		quarter_of_year = QUARTER(date),
		year_3            = DATE_FORMAT( date, \"%Y\" );



		CREATE TABLE wp_question_dim (
		question_id INTEGER UNSIGNED NOT NULL,
		questionnaire_id INTEGER(20) UNSIGNED NOT NULL,
		question_text TEXT NULL,
		ans_type VARCHAR(100) NULL,
		PRIMARY KEY(question_id, questionnaire_id)
		);

		CREATE TABLE wp_location_dim (
		location_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
		city VARCHAR(50) NULL,
		country VARCHAR(50) NULL,
		PRIMARY KEY(location_id)
		);

		CREATE TABLE wp_questionnaire_dim (
		questionnaire_id INTEGER(20) UNSIGNED NOT NULL,
		topic VARCHAR(100) NULL,
		date_created DATE NULL,
		allow_anonymous BOOL NULL,
		allow_multiple BOOL NULL,
		title VARCHAR(100) NULL,
		creator_name VARCHAR(100) NULL,
		PRIMARY KEY(questionnaire_id)
		);

		CREATE TABLE wp_question_response (
		response_id int(10) unsigned NOT NULL,
		question_dim_questionnaire_id int(20) unsigned NOT NULL,
		question_dim_question_id int(10) unsigned NOT NULL,
		time_dim_time_id bigint(20) DEFAULT NULL,
		respondee_dim_respondee_id int(10) unsigned NOT NULL,
		questionnaire_dim_questionnaire_id int(20) unsigned NOT NULL,
		location_dim_location_id int(10) unsigned DEFAULT NULL,
		response_content text,
		response_type varchar(100) DEFAULT NULL,
		PRIMARY KEY (response_id)
		);
		$charset_collate;";

		dbDelta( $sql );

	}
}


/////////////////////////////analyzer_drop_tbl///////////////////
function analyzer_drop_tbl()
{
	$analyzer_tbls= array('wp_respondee_dim', 'wp_time_dim', 'wp_question_dim', 'wp_location_dim', 'wp_questionnaire_dim','wp_question_response');

	$msg=loop_arr($analyzer_tbls);
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;


	if($wpdb->get_var( "SHOW TABLES LIKE '".$analyzer_tbls[0]."'" ) == $analyzer_tbls[0])
	{
		$wpdb->query( 'DROP TABLE IF EXISTS wp_respondee_dim,
		wp_time_dim,
		wp_question_dim,
		wp_location_dim,
		wp_questionnaire_dim,
		numbers,
		numbers_small,
		wp_question_response');

	}
}

/////////////////////////////analyzer_migration /////////////////////////////////////////////////
function analyzer_migration()
{
	global $wpdb;

	$sql_0="INSERT INTO wp_location_dim (city, country)
	SELECT distinct City, Country
	FROM gwu_session
	WHERE City    NOT IN(select distinct city    from wp_location_dim)
	AND   Country NOT IN(select distinct country from wp_location_dim)";

	$sql_1="INSERT INTO wp_question_dim (question_id, questionnaire_id, question_text, ans_type)
	SELECT questsequence, QuestionnaireID, gwu_question.text, AnsType
	FROM gwu_question";


	$sql_2="INSERT INTO wp_respondee_dim(respondee_id, survey_completed, survey_taken_date, username, ip, duration)
	SELECT SessionID, SurveyCompleted, SurveyTakenDate, Username, IP, Duration
	FROM gwu_session";

	$sql_3="INSERT INTO wp_questionnaire_dim (questionnaire_id, topic, date_created, allow_anonymous, allow_multiple, title, creator_name)
	SELECT QuestionnaireID, Topic, DateCreated, AllowAnnonymous, AllowMultiple, Title, CreatorName
	FROM gwu_questionnaire";


	$sql_4="INSERT INTO wp_question_response (response_id, response_content, response_type, questionnaire_dim_questionnaire_id, question_dim_questionnaire_id, question_dim_question_id, respondee_dim_respondee_id)
	SELECT ResponseID, ResponseContent, ResponseType, QuestionnaireID, QuestionnaireID, QuestSequence, SessionID
	FROM gwu_response";


	$sql_5="UPDATE wp_question_response SET
	time_dim_time_id =
	(SELECT time_id
	FROM wp_time_dim, gwu_session
	WHERE wp_question_response.respondee_dim_respondee_id = gwu_session.SessionID
	AND wp_time_dim.date = gwu_session.SurveyTakenDate)";


	$sql_6="UPDATE wp_question_response SET
	location_dim_location_id =
	(SELECT location_id
	FROM wp_location_dim, gwu_session
	WHERE wp_question_response.respondee_dim_respondee_id = gwu_session.SessionID
	AND wp_location_dim.country = gwu_session.Country
	AND wp_location_dim.city = gwu_session.city)";


	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

	$wpdb->query($wpdb->prepare($sql_0));
	$wpdb->query($wpdb->prepare($sql_1));
	$wpdb->query($wpdb->prepare($sql_2));
	$wpdb->query($wpdb->prepare($sql_3));
	$wpdb->query($wpdb->prepare($sql_4));
	$wpdb->query($wpdb->prepare($sql_5));
	$wpdb->query($wpdb->prepare($sql_6));

}
/////////////////////////////analyzer_migration_cron /////////////////////////////////////////////////
function analyzer_migration_cron()
{
	global $wpdb;

	$sql_0="INSERT INTO wp_location_dim (city, country)
	SELECT distinct City, Country
	FROM gwu_session ";
	//WHERE City    NOT IN(select distinct city    from wp_location_dim)
	//AND   Country NOT IN(select distinct country from wp_location_dim) ";

	$sql_1="INSERT INTO wp_question_dim (question_id, questionnaire_id, question_text, ans_type)
	SELECT gwu_question.questsequence, gwu_question.QuestionnaireID, gwu_question.text, AnsType
	FROM gwu_question, gwu_questionnaire
	WHERE gwu_questionnaire.QuestionnaireID = gwu_question.QuestionnaireID
	AND gwu_questionnaire.PublishDate = date_add(curdate(), interval -1 day) ";


	$sql_2="INSERT INTO wp_respondee_dim(respondee_id, survey_completed, survey_taken_date, username, ip, duration)
	SELECT SessionID, SurveyCompleted, SurveyTakenDate, Username, IP, Duration
	FROM gwu_session
	WHERE surveytakendate = date_add(curdate(), interval -1 day) ";


	$sql_3="INSERT INTO wp_questionnaire_dim (questionnaire_id, topic, date_created, allow_anonymous, allow_multiple, title, creator_name)
	SELECT QuestionnaireID, Topic, DateCreated, AllowAnnonymous, AllowMultiple, Title, CreatorName
	FROM gwu_questionnaire
	WHERE PublishDate = date_add(curdate(), interval -1 day) ";


	$sql_4="INSERT INTO wp_question_response (response_id, response_content, response_type, questionnaire_dim_questionnaire_id, question_dim_questionnaire_id, question_dim_question_id, respondee_dim_respondee_id)
	SELECT ResponseID, ResponseContent, ResponseType, QuestionnaireID, QuestionnaireID, QuestSequence, gwu_response.SessionID
	FROM gwu_response, gwu_session
	WHERE gwu_response.SessionID = gwu_session.SessionID
	AND SurveyTakenDate = date_add(curdate(), interval -1 day) ";


	$sql_5="UPDATE wp_question_response SET
	time_dim_time_id =
	(SELECT time_id
	FROM wp_time_dim, gwu_session
	WHERE wp_question_response.respondee_dim_respondee_id = gwu_session.SessionID
	AND wp_time_dim.date = gwu_session.SurveyTakenDate) ";


	$sql_6="UPDATE wp_question_response SET
	location_dim_location_id =
	(SELECT location_id
	FROM wp_location_dim, gwu_session
	WHERE wp_question_response.respondee_dim_respondee_id = gwu_session.SessionID
	AND wp_location_dim.country = gwu_session.Country
	AND wp_location_dim.city = gwu_session.city) ";


	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

	$wpdb->query($wpdb->prepare($sql_0));
	$wpdb->query($wpdb->prepare($sql_1));
	$wpdb->query($wpdb->prepare($sql_2));
	$wpdb->query($wpdb->prepare($sql_3));
	$wpdb->query($wpdb->prepare($sql_4));
	$wpdb->query($wpdb->prepare($sql_5));
	$wpdb->query($wpdb->prepare($sql_6));

}

///////////////////////////// analyzer_get_rec_count($qry) ////////////////////////////////////
function analyzer_get_rec_count($qry)
{
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;

	$cnt= $wpdb->get_var($qry);

	return $cnt;
}


///////////////////////////// analyzer_show_tbls ////////////////////////////////////
function analyzer_show_tbls()
{
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;
	$wpdb->show_errors();
	$qry="select TABLE_NAME from INFORMATION_SCHEMA.TABLES

	WHERE  table_name LIKE 'wp_%dim'
	or  table_name='wp_question_response' ";
	$arr_tbl= $wpdb->get_results($qry);

	$res_tbl.="<div class='wrap'>";
	$res_tbl.="<h3>".$title."</h3>";
	$res_tbl.=" <table>";
	$res_tbl.="  <tr>";
	$res_tbl.="   <th>Analyzer tables</th>";
	$res_tbl.="  </tr>";
	foreach($arr_tbl as $i)
	{
		$res_tbl.="  <tr>";
		$res_tbl.="   <td>".$i->TABLE_NAME."</td>";
		$res_tbl.=" </tr>";
	}
	$res_tbl.="  <tr>";
	$res_tbl.="   <td colspan=2>Mgration Errors:</td><td>".$wpdb->print_error()."</td>";
	$res_tbl.=" </tr>";
	$res_tbl.=" </table>";
	$res_tbl.="<div>";
	return $res_tbl;
}
/////////////////////////////analyzer_exec_sql///////////////////
function analyzer_exec_sql($qry, $qry_type)
{
	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;

	$msg.="<br>".$db_table_name;
	$sql_res=null;
	$res_view="";
	if($qry_type=='exec')
	{
		$sql_res = $wpdb->get_results($qry);

		$res_view.='<ul>';
		foreach($sql_res as $i)
		{
			$res_view.='<li>'.$i->emp_id.'</li>';
		}
		$res_view.='</ul>';
		$msg.="<br>".$res_view;
	}
	else if($qry_type=='update')
	{
		$sql_res = $wpdb->query($qry);
		$msg.='<br>'.'table updated';
	}

	return $msg;
}

//////////////////////////////////////// analyzer_cron_job_activation functions ////////////////////////////

function analyzer_cron_job_activation()
{
	if(!wp_next_scheduled('analyzer_data_migration'))
	{
		wp_schedule_event(current_time('timestamp'), 'everyminute', 'analyzer_data_migration');
	}
}

function analyzer_task_to_exec()
{
	return analyzer_migration_cron();
	//analyzer_exec_sql('INSERT wp_res_2 SELECT * FROM wp_res_1', 'update');
}

/////////////////////////////////// analyzer_cron_job_intervals //////////////////////////////////////////////
function analyzer_cron_job_intervals($schedules)
{
	$schedules['everyminute'] = array(
	'interval' => 60,
	'display' => __( 'Once Every Minute' )
	);
	return $schedules;
}

////////////////////////////////////// analyzer_cron_job_deactivation ///////////////////////////////////////////
function analyzer_cron_job_deactivation()
{
	wp_clear_scheduled_hook('analyzer_data_migration');
}
///////////////////////////////////// analyzer_cron_jobs action hooks //////////////////////////////////////////
add_action('wp', analyzer_cron_job_activation);
add_filter('cron_schedules', 'analyzer_cron_job_intervals');
add_action ('analyzer_data_migration', 'analyzer_task_to_exec');


/////////////////////// register_activation_hooks ////////////////////////////////
register_activation_hook(__FILE__, 'analyzer_create_tbl');
register_deactivation_hook(__FILE__, 'analyzer_drop_tbl');
register_activation_hook(__FILE__, 'analyzer_migration');
register_activation_hook(__FILE__, 'analyzer_cron_job_activation');
register_deactivation_hook(__FILE__, 'analyzer_cron_job_deactivation');


/////////////////////////////////// analyzer_utils loop_arr /////////////////////////////////
function loop_arr($arr)
{
	$res='<ul>';
	foreach ($arr as $i)
	{
		$res.='<li>'.$i .'<li>';

	}
	$res.='</ul>';

	return $res;
}

/////////////////////////////////// analyzer_utils loop_arr_tbl /////////////////////////////////
function loop_arr_tbl($arr, $arr_cols, $title)
{
	$res_tbl.="<div class='wrap'>";
	$res_tbl.="<h3>".$title."</h3>";
	$res_tbl.=" <table class='wp-list-table widefat fixed'>";
	$res_tbl.="  <tr>";
	foreach($arr_cols as $i)
	{
		$res_tbl.="   <th>".$i."</th>";
	}
	$res_tbl.="  </tr>";
	foreach($arr as $j)
	{
		$res_tbl.="  <tr>";
		$res_tbl.="   <td>".$j."</td>";
		$res_tbl.=" </tr>";
	}
	$res_tbl.=" </table>";
	$res_tbl.="<div>";

	return $res_tbl;
}

/**************Template Code******************/
add_action( 'wp_enqueue_scripts', 'getCss' );

/**
* Enqueue plugin style-file
*/

function getCss(){

	// Respects SSL, Style.css is relative to the current file
	wp_register_style( 'prefix-style', plugins_url('/css/alem.css', __FILE__) );
	wp_enqueue_style( 'prefix-style' );
}

function getJavaScript(){

	$msg = '<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
	<script  type="text/javascript">
	$(function() {
	$( ".datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
	});
	</script>';
	return $msg;
}

function getQuestionnaireList(){
	global $wpdb;
	$msg = '';
	$url =  plugins_url( 'findQuestionnair.php' , __FILE__ );
	$questionnaire=$_GET['questionnaire'];
	$question=$_GET['question'];

	$sql = 'SELECT questionnaire_id as id , title
	FROM wp_questionnaire_dim order by questionnaire_id DESC';
	$res = $wpdb->get_results($sql);

	if(!empty($res)){
		$msg =$msg.'<table class="table"><tr class="tr1">
		<td class="td"><strong>Questionnaire</strong></td>
		<td class="td" colspan="3">
		<select name="questionnaire" onChange="reload(this.form)"> <option value="-1"> Select Questionnaire </option>';
		foreach ($res as $rs) {
			if($rs->id == $questionnaire){
				$msg = $msg.'<option selected value="'.$rs->id.'">'.$rs->title.'</option>';
			} else{
				$msg = $msg.'<option value="'.$rs->id.'">'.$rs->title.'</option>';
			}
		}

		$msg = $msg.'</select></td>
		</tr>';
		if(isset($questionnaire) and strlen($questionnaire) > 0){


			//for question
			$msg = $msg.'<tr class="tr1">
			<td class="td"><strong>Filter By:</strong></td>
			<td class="td">';

			$result = mysql_query("select question_id, question_text from wp_question_dim
			where questionnaire_id = $questionnaire");
			$msg = $msg.'	<select name="question" onChange="reload(this.form)"><option value=""> Select Question </option>';
			while($rows = mysql_fetch_assoc($result)) {
				if($rows['question_id'] == $question){
					$msg = $msg.'<option selected value="'.$rows['question_id'].'">'.$rows['question_text'].'</option>';
				} else {
					$msg = $msg.'<option value="'.$rows['question_id'].'">'.$rows['question_text'].'</option>';
				}
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
			<br></td><td class="td"><strong>Start Date:</strong>
			<br><input type="text" name="start" class="datepicker" value="" />
			<br><strong>End Date:</strong>
			<br><input type="text" name="end" class="datepicker" value="" /></td>

			<td class="td1">
			<input class="button-primary" type="submit" name="submit" value="Execute" />
			</td>


			</tr></table>';

			// For The Questionnaire information
			$result = mysql_query("SELECT Q.questionnaire_id, Q.title, Q.creator_name, Q.date_created, COUNT( DISTINCT QR.respondee_dim_respondee_id ) total_Response, AVG( R.duration ) AS ave_time
			FROM wp_questionnaire_dim Q, wp_question_response QR, wp_respondee_dim R
			WHERE Q.questionnaire_id = $questionnaire
			AND QR.question_dim_questionnaire_id = Q.questionnaire_id
			AND QR.respondee_dim_respondee_id = R.respondee_id");


			if(!empty($result)){

				$msg =$msg.'<table class="table">
				<tr class="tr">
				<td width="80%" colspan="2" class="td"><input class="button-primary" type="submit" name="submit" value="Export Data" onclick="exportCSV('.$questionnaire.')"/></td>
				<td width="10%" class="td"></td>
				<td width="10%" class="td"><input class="button-primary" type="submit" name="submit" value="Refresh" /></td>
				</tr>
				<tr class="tr">
				<td colspan="4" class="td">
				<table>
				<tr>
				<th>Title</th>
				<th>Date Created</th>
				<th>Created By</th>
				<th>Number Response</th>
				<th>&nbsp;</th>
				</tr>';

				while($rows = mysql_fetch_assoc($result)) {
					$completed = $rows['total_Response'];
					$ave_time = round($rows['ave_time'],2);
					$ave_time = gmdate("H:i:s", (int)$rows['ave_time']);
					$msg =$msg.'<tr>';
					$msg = $msg.'<td>'.$rows['title'].'</td><td>'.$rows['date_created'].'</td>
					<td>'.$rows['creator_name'].'</td><td>'.$rows['total_Response'].'</td>
					<td><a href="/wp-content/plugins/QuestionPeachAnalyzer/viewAll.php?questionnaire='.$rows['questionnaire_id'].'">View Result</a></td>';
					$msg =$msg.'</tr>';
				}

				$msg = $msg.'</table></td>
				</tr>
				<tr class="tr">
				<td class="td" width="20%">&nbsp; </td>
				<td class="td" width="30%"><p style="text-align:center; border:2px solid black;"><b>Completed</b> <br>'.  $completed.' </p></td>
				<td class="td" width="30%"><p style="text-align:center; border:2px solid black;"><b>Average Time</b> <br>'.  $ave_time.' </p></td>
				<td class="td" width="20%">&nbsp; </td>
				</tr>

				</table>';
			}
			//Geo Map
			$result = mysql_query("SELECT count(distinct QR.respondee_dim_respondee_id) total, country FROM wp_question_response QR, wp_location_dim L
			WHERE L.location_id = QR.location_dim_location_id
			AND QR.question_dim_questionnaire_id = $questionnaire group by country");
			if(!empty($result)){

				//$country = array(array('Country'), array('USA'), array('India'), array('China'));
				$country = '[	["Country", "Popularity"],';
				while($rows = mysql_fetch_assoc($result)) {
					$country = $country.'["'.$rows['country'].'",'.$rows['total'].'],';
				}
			}

			$country = $country.']';

			$msg = $msg.'<tr class="tr">
			<td class="td" colspan="4">
			<table class="table">
			<tbody>
			<tr class="tr1">
			<td colspan="4" class="td">
			<div id="map_div" style="width: 600px; height: 322px; margin:0 auto;"></div>
			</td>
			</tr>
			</tbody>
			</table>
			</td>
			</tr>
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<script type="text/javascript">
			google.load("visualization", "1", {"packages": ["geochart"]});
			google.setOnLoadCallback(drawRegionsMap);

			function drawRegionsMap() {
			var data = google.visualization.arrayToDataTable('.$country.');
			var options = {};
			var chart = new google.visualization.GeoChart(document.getElementById("map_div"));
			chart.draw(data, options);
			};

			</script>';

			//question result
			$msg=$msg.'<tr class="tr">
			<td class="td" colspan="4">';
			if(isset($question) and strlen($question) > 0){
				$result = mysql_query("SELECT question_id, questionnaire_id, question_text, ans_type, response_content, COUNT( * ) AS total
				FROM wp_question_dim Q, wp_question_response QR
				WHERE questionnaire_id = $questionnaire
				AND question_id = $question
				AND QR.questionnaire_dim_questionnaire_id = Q.questionnaire_id
				AND QR.question_dim_question_id = Q.question_id
				GROUP BY response_content");
				if(mysql_num_rows($result) > 0){
					$row = mysql_fetch_row($result);
					if (mysql_data_seek($result, 0))
					{}

					$text = '<ul>';

					// if the answer type is text
					if(trim($row[3]) == "Text Box"){
						while($rows = mysql_fetch_assoc($result)) {
							$question_id = $rows['question_id'];
							$question_txt = $rows['question_text'];
							$text = $text.'<li>'.$rows['response_content'].'</li>';
						}
						$text = $text.'</ul>';
						$msg =$msg.'<table class="table">
						<tbody>
						<tr class="tr1">
						<td colspan="4" class="td"><b>'.$question_id.'. '.$question_txt.'</b><br></td>
						</tr>
						<tr class="tr">
						<td colspan="4" class="td">'.$text.'</td>
						</tr>
						</tbody>
						</table>';
					} else if(trim($row[3]) == "NPS"){
						$nps = mysql_query("SELECT COUNT( * ) detractors
						FROM ( SELECT CONVERT( response_content, UNSIGNED INTEGER ) AS response_content
						FROM wp_question_response
						WHERE questionnaire_dim_questionnaire_id =$questionnaire
						AND question_dim_question_id =$question
						AND response_type = 'NPS'
						AND CONVERT( response_content, UNSIGNED INTEGER )
						BETWEEN 0
						AND 6
						) AS detractors");
						$detractors = 0;
						if(!empty($nps)){
							while($row = mysql_fetch_assoc($nps)) {
							$detractors = $row['detractors'];
							}
						}
						$nps1 = mysql_query("SELECT COUNT( * ) passives
						FROM (

						SELECT CONVERT( response_content, UNSIGNED INTEGER ) AS response_content
						FROM wp_question_response
						WHERE questionnaire_dim_questionnaire_id =$questionnaire
						AND question_dim_question_id =$question
						AND response_type =  'NPS'
						AND CONVERT( response_content, UNSIGNED INTEGER )
						BETWEEN 7
						AND 8
						) AS passives");
						$passives =0;
							if(!empty($nps1)){
							while($row = mysql_fetch_assoc($nps1)) {
							
							$passives = $row['passives'];
						}
						}
						$nps2 = mysql_query("SELECT COUNT( * ) promoters
						FROM (SELECT CONVERT( response_content, UNSIGNED INTEGER ) AS response_content
						FROM wp_question_response
						WHERE questionnaire_dim_questionnaire_id =$questionnaire
						AND question_dim_question_id =$question
						AND response_type =  'NPS'
						AND CONVERT( response_content, UNSIGNED INTEGER )
						BETWEEN 9
						AND 10
						) AS promoters");
						$promoters = 0;
							if(!empty($nps2)){
							while($row = mysql_fetch_assoc($nps2)) {
							
							$promoters = $row['promoters'];
						}
						}
						
						$total = $detractors + $passives + $promoters;
						$promoters_per = ($promoters / $total)* 100;
						$detractors_per = ($detractors / $total)* 100;

						$npsFinal = $promoters_per - $detractors_per;
						$dataArray = '[	["Promoter", "value"]';
						$dataArray = $dataArray.',["Promoters",'.$promoters.']';
						$dataArray = $dataArray.',["Detractors",'.$detractors.']';
						$dataArray = $dataArray.',["Passives",'.$passives.']';
						$dataArray = $dataArray.']';

						$msg =$msg.'<table class="table">
						<tbody>
						<script type="text/javascript" src="https://www.google.com/jsapi"></script>
						<script type="text/javascript">
						google.load("visualization", "1", {packages:["corechart"]});
						google.setOnLoadCallback(drawChart);
						function drawChart() {
						var data = google.visualization.arrayToDataTable('.$dataArray.');
						var options = {
						title: "'.$question_id.'. '.$question_txt.'"
						};

						var chart = new google.visualization.PieChart(document.getElementById("chart_div"));
						chart.draw(data, options);
						}
						</script><tr class="tr1">
						<td colspan="4" class="td">
						<div id="chart_div" style="width: 800px; height: 400px; margin:0 auto;"></div>
						</td>
						</tr>
						<tr class="tr">
						<td colspan="4" class="td"><b>NPS: </b>'.$npsFinal.'<br></td>
						</tr>
						
						</tbody>
						</table>';




					} else{

						$dataArray = '[	["Answer", "Responses"]';
						while($rows = mysql_fetch_assoc($result)) {
							$question_id = $rows['question_id'];
							$question_txt = $rows['question_text'];
							$key = $rows['response_content'];
							$value = $rows['total'];
							$dataArray = $dataArray.',["'.$key.'",'.$value.']';

						}

						$dataArray = $dataArray.']';


						$msg=$msg.'<table class="table">
						<tbody>

						<script type="text/javascript" src="https://www.google.com/jsapi"></script>
						<script type="text/javascript">
						google.load("visualization", "1", {packages:["corechart"]});
						google.setOnLoadCallback(drawChart);
						function drawChart() {
						var data = google.visualization.arrayToDataTable('.$dataArray.');
						var options = {
						title: "'.$question_id.'. '.$question_txt.'",
						vAxis: {title: "Answer",  titleTextStyle: {color: "red"}}
						};

						var chart = new google.visualization.BarChart(document.getElementById("chart_div"));
						chart.draw(data, options);
						}
						</script><tr class="tr1">
						<td colspan="4" class="td">
						<div id="chart_div" style="width: 800px; height: 400px; margin:0 auto;"></div>
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
			$msg=$msg.'</td>
			</tr>';

		} else {
			$msg = $msg.'<tr class="tr1">
			<td class="td"><strong>Filter By:</strong></td>
			<td class="td">
			<select name="question">
			<option value="">Select Question</option>
			</select>
			<br>
			<br>
			<select name="location">
			<option value="">Select Location</option>
			</select>
			<br>
			<br>

			<select name="responder">
			<option value="">Select Responder</option>
			</select>

			</td><td class="td"><strong>Start Date:</strong>
			<br><input type="text" name="start" class="datepicker" value="" />
			<br><strong>End Date:</strong>
			<br><input type="text" name="end" class="datepicker" value="" /></td>

			<td class="td1">
			<input class="button-primary" type="submit" name="submit" value="Execute" />
			</td>


			</tr></table>';
		}

	} else {
		$msg = $msg.'There is no survey in the system.';
	}
	return $msg;
}

function getAdminForm(){

	$msg = '<div class="loader"></div><form>
	<table class="table">
	<tbody><tr><td colspan="4">'.do_shortcode('[QP_QuestionnaireList]').'</td>
	</tr>

	<tbody>
	</table>
	<br>
	<br>


	<!-- the code below should be seen when the query is executed  -->



	</form>';

	return $msg;
}

function exportCSV($id){

	$sql = mysql_query("SELECT question_id, questionnaire_id, question_text, ans_type, response_content, COUNT( * ) AS total
	FROM wp_question_dim Q, wp_question_response QR
	WHERE questionnaire_id = $id
	AND QR.questionnaire_dim_questionnaire_id = Q.questionnaire_id
	AND QR.question_dim_question_id = Q.question_id
	GROUP BY response_content
	ORDER BY questionnaire_id, question_id");
	if(mysql_num_rows($result) > 0){
		$filename = 'Export.csv';
		$headers = array('Question Id', 'Question Text', 'Response', 'Total');

		$handle = fopen($filename, 'w');
		fputcsv($handle, $headers, ',', '"');



		while($results = mysql_fetch_array($sql)) {
			$row = array(
			$results[0],
			$results[2],
			$results[4],
			$results[5]
			);
			$fputcsv($handle, $row, ',', '"');
		}

		fclose($handle);
	}
}





function getLocation(){
	$rows = array();
	$table = array();
	$table['cols'] = array(
	array('label' => 'country', 'type' => 'string')
	//,array('label' => 'Popularity', 'type' => 'number')
	);

	global $wpdb;
	$sql = 'SELECT * FROM  wp_location_dim';
	$res = $wpdb->get_results($sql);
	if(!empty($res)){

		foreach ($res as $rs) {
			$country = array();

			$country[] = array('v' => (string) $rs->country);
			$rows[] = array('c' => $country);
		}

	}
	$table['rows'] = $rows;

	// convert data into JSON format
	$jsonTable = json_encode($table);

	//return $jsonTable ;
	return $table;
}

function getFilterPanel(){


	global $wpdb;
	$msg = '';
	$sql = 'SELECT id, title
	FROM questionnior_fact group by id';
	$res = $wpdb->get_results($sql);

	if(!empty($res)){
		$msg ='<tr class="tr">
		<td class ="td" colspan="3">
		<select name="survey"> <option value=""> Select Survey </option>';
		foreach ($res as $rs) {
			$msg = $msg.'<option value="'.$rs->id.'">'.$rs->title.'</option>';

		}

		$msg = $msg.'</select>';

		$msg = $msg.'</td></tr>';
	}
	return $msg;
}

function getGeoChart(){

	global $wpdb;
	$sql = 'SELECT count(*) total, country FROM  wp_location_dim group by country';
	$res = $wpdb->get_results($sql);

	//$country = array(array('Country'), array('USA'), array('India'), array('China'));
	$country = '[	["Country", "Popularity"],';
	if(!empty($res)){
		foreach ($res as $rs)  {
			$country = $country.'["'.$rs->country.'",'.$rs->total.'],';
		}
	}

	$country = $country.']';

	$geoChart = '<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
	google.load("visualization", "1", {"packages": ["geochart"]});
	google.setOnLoadCallback(drawRegionsMap);

	function drawRegionsMap() {
	var data = google.visualization.arrayToDataTable('.$country.');
	var options = {};
	var chart = new google.visualization.GeoChart(document.getElementById("map_div"));
	chart.draw(data, options);
	};

	</script>';
	return $geoChart;
}




?>
<!--**************AJAX Function*********************-->
<script>
	function reload(form)
	{
		var question = form.question.options[form.question.options.selectedIndex].value;
		var val=form.questionnaire.options[form.questionnaire.options.selectedIndex].value;
		if(question){
			self.location='/questionpeach-analyzer-gui/?questionnaire=' + val +'&question='+ question;
		} else {
			self.location='/questionpeach-analyzer-gui/?questionnaire=' + val;
		}
	}




	function getXMLHTTP() { //fuction to return the xml http object
		var xmlhttp=false;
		try{
			xmlhttp=new XMLHttpRequest();
		}
		catch(e)	{
			try{
				xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e){
				try{
					xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				}
				catch(e1){
					xmlhttp=false;
				}
			}
		}

		return xmlhttp;
	}

	function getQuestion(strURL){
		getFilter(strURL);
		getDetail(strURL)
	}
	function getFilter (strURL) {

		var req = getXMLHTTP();

		if (req) {

			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						document.getElementById('Questiondiv').innerHTML=req.responseText;
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}
			}
			req.open("GET", strURL, true);
			req.send(null);
		}

	}

	function getDetail (strURL) {
		var newURL = strURL.replace("findQuestionnair","detail");
		var req = getXMLHTTP();

		if (req) {

			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						document.getElementById('Detaildiv').innerHTML=req.responseText;
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}
			}
			req.open("GET", newURL, true);
			req.send(null);
		}

	}
	function getResult(strURL) {

		var req = getXMLHTTP();

		if (req) {

			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {
						document.getElementById('resultdiv').innerHTML=req.responseText;
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}
			}
			req.open("GET", strURL, true);
			req.send(null);
		}

	}


</script>
<?
//http://devzone.co.in/populate-state-dropdown-onchange-country-dropdown-using-javascript/
?>