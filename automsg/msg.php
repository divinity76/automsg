<?php
	require_once('hhb_.inc.php');
	require_once('hhb_datatypes.inc.php');
	init();
//	header("content-type: text/plain");
	if(empty($_GET['sender'])){
		die('need sender!');
	}
	$sender=$_GET['sender'];
	if(empty($_GET['reciever'])){
		die('need reciever!');
	}
	$reciever=$_GET['reciever'];
	if(empty($_GET['message'])){
		die('need message!');
	}
	$message=$_GET['message'];
	$response=getResponse($message,$sender,$reciever);
	var_dump($response);die("RESPONSEDIED");
	
	function getResponse($message,$sender,$reciever){
		$stuff=getCurlWithSession();
		//var_dump($stuff);die("DIEDS");
		$ch=$stuff['ch'];
		curl_setopt_array($ch,array(
		CURLOPT_POST=>1,
		CURLOPT_POSTFIELDS=>http_build_query(array(
		'botcust2'=>$stuff['cookies']['botcust2'],
		'input'=>$message
		))
		));
		$headers=array();
		$cookies=array();
		$debuginfo="";
		$html=hhb_curl_exec2($ch,'http://sheepridge.pandorabots.com/pandora/talk?botid=b69b8d517e345aba&skin=custom_input',$headers,$cookies,$debuginfo);
		//var_dump($html,$headers,$cookies,$debuginfo);die("died36");
		$response=strpos($html,'ALICE:');
		assert($response!==false);
		$response=trim(substr($html,$response+strlen('ALICE:')));
		//var_dump($response);die("DIED");
		$response=filterMessage($response,$sender,$reciever);
		global $dbc;
		$stm=$dbc->prepare('INSERT INTO `messages` (`reciever`,`sender`,`message`,`response`,`date`) VALUES (:reciever,:sender,:message,:response,:date);');
		$stm->execute(array(
		':reciever'=>$reciever,
		':sender'=>$sender,
		':message'=>$message,
		':date'=>date("Y-m-d H:i:s"),
		));
		return $response;
	}
	function filterMessage($message,$sender,$reciever){
		$message=strtolower($message);
		$replacements=array(
		'robot'=>'cat',
		'artificial intelligence'=>'cat',
		'my own childhood days'=>'penis',
		'ask me another question'=>'bleh',
		'what is your favorite color?'=>'bleh',
		'are we still talking about your personality ?'=>'blah',
		'alice'=>$reciever,
		'machine kingdom'=>'Uchia',
		'pandorabot'=>'Uchia',
		'machine'=>'kitten',
		'i\'m glad you find this amusing.'=>'funny',
		'certainly, i have an extensive built-in help system'=>'nope',
		'i haven\'t heard anything like that before'=>'uhu',
		'that\'s good information'=>'right',
		'do you mind if i tell other people'=>'uhu',
		'I like the way you talk'=>'.',
		'hi there!'=>'hi',
		'always chatting with people on the internet'=>'playing..',
		'chatting with people on the web'=>'derping',
		'with people on the net'=>'..',
		'botmaster'=>'boss',
		'  '=>' ',
		'this is just a test'=>'test lol',
		'can you please rephrase that with fewer ideas, or different thoughts'=>'no understand lal',
		'how old are you?'=>'..',
		'can you tell me any gossip?'=>'tiredz',
		'why, specifically?'=>'why?',
		);
		foreach($replacements as $old=>$new){
			$message=str_replace($old,$new,$message);
		}
		unset($old,$new);
		return $message;
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