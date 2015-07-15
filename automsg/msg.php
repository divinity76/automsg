<?php
	require_once('hhb_.inc.php');
	require_once('hhb_datatypes.inc.php');
	init();
	header("content-type: text/plain");
	if(empty($_GET['sender'])){
		die('need sender!');
		}
	if(empty($_GET['reciever'])){
		die('need reciever!');
		}
	if(empty($_GET['message'])){
		die('need message!');
		}
		
	$stuff=getCurlWithSession();
	var_dump($stuff);die("DIEDS");
	
	
	
	
	
	
	function filterMessage($message,$reciever,$sender){
		
	}
	
	function getCurlWithSession(){
		//stored sessions is (intentionally) not yet implemented. will create a new session each time.
		$ch=hhb_curl_init();
		$headers=array();
		$cookies=array();
		$verboseDebugInfo=array();
		$html=hhb_curl_exec2($ch,'http://sheepridge.pandorabots.com/pandora/talk?botid=b69b8d517e345aba&skin=custom_input',$headers,$cookies,$verboseDebugInfo);
		return array('ch'=>$ch,'html'=>$html,'headers'=>$headers,'cookies'=>$cookies,'verboseDebugInfo'=>$verboseDebugInfo); 
		
		}
	
	
	
	function init(){
		hhb_init();
		$db_path=hhb_combine_filepaths(__DIR__,'chats.sqlite3db');
		$GLOBALS['db_path']=$db_path;
		if(!file_exists($db_path)){
			createDatabase($db_path);
			} else {
			//echo "THE DATABASE EXISTED ALREADY.";
		}
		$dbc=new PDO('sqlite:'.$db_path,'','',array(
		PDO::ATTR_EMULATE_PREPARES => false, 
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		$GLOBALS['dbc']=$dbc;
		return true;
	}
	function createDatabase($db_path){
		$rc=fopen($db_path,'w+b');
		assert($rc!==false);
		assert(fclose($rc));
		unset($rc);
		$dbc=new PDO('sqlite:'.$db_path,'','',array(
		PDO::ATTR_EMULATE_PREPARES => false, 
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		));
		$dbc->query('DROP TABLE IF EXISTS `messages`;');
		$dbc->query('CREATE TABLE `messages` (
		`id`	INTEGER,
		`reciever`	VARCHAR(255) NOT NULL DEFAULT NULL,
		`sender`	VARCHAR(255) NOT NULL DEFAULT NULL,
		`message`	VARCHAR(255) NOT NULL DEFAULT NULL,
		`response`	VARCHAR(255) DEFAULT NULL,
		`date`	VARCHAR(255) NOT NULL DEFAULT NULL,
		PRIMARY KEY(`id` ASC)
		);');
		$dbc->query('DROP TABLE IF EXISTS `sessions`;');
		$dbc->query('CREATE TABLE `sessions` (
		`id`	INTEGER,
		`sessioncookies`	VARCHAR(255) NOT NULL DEFAULT NULL,
		`reciever`	VARCHAR(255) NOT NULL DEFAULT NULL,
		`sender`	VARCHAR(255) NOT NULL DEFAULT NULL,
		`creation_time`	VARCHAR(255) NOT NULL DEFAULT NULL,
		`last_action_time`	VARCHAR(255) NOT NULL DEFAULT NULL,
		PRIMARY KEY(`id` ASC)
		);');
		$dbc->query('INSERT INTO `sessions` (`sessioncookies`,`reciever`,`sender`,`creation_time`,`last_action_time`,) VALUES(\'NOT IMPLEMENTED\',\'NOT IMPLEMENTED\',\'NOT IMPLEMENTED\',\'NOT IMPLEMENTED\',\'NOT IMPLEMENTED\');');
		$dbc->query('SELECT * FROM `messages`');
		echo "CREATED THE DATABASE!";
		unset($dbc);
		return true;
	}	