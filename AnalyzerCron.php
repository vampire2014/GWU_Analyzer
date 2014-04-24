<?php

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


?>