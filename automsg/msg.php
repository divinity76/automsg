<?php
	require_once('hhb_.inc.php');
	require_once('hhb_datatypes.inc.php');
	init();
//	header("content-type: text/plain");
	if(empty($_GET['sender'])){
	var_dump($_GET);
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
	$message=trim($_GET['message']);
	if($message==='?'){
		$message='what?';
		}
	
	$response=getResponse($message,$sender,$reciever);
	//var_dump($response);die("RESPONSEDIED");
	die(hexEncodeResponse($response,$sender,$reciever));
	
	function hexEncodeResponse($response,$sender,$reciever){
	if($response==='SAYNOTHING'){
		return "";
		}
	$addTCPHeader=function($str){
			return to_little_uint16_t(strlen($str)).$str;
		};
		$retpre='exiva >>';
		$packet1="\x9A";//  
		//die(bin2hex($packet1));
		$packet1.=$addTCPHeader($sender);//that's the protocol....
		//die(bin2hex($packet1));
		$packet1=$addTCPHeader($packet1);
		$packet1=bin2hex($packet1);
		$packet1=strtoupper($packet1);
		$packet1=str_split($packet1,2);
		$packet1=implode(" ",$packet1)." ";
		$packet2="\x96"."\x04";
		$packet2.=$addTCPHeader($sender);
		$packet2.=$addTCPHeader($response);
		$packet2=$addTCPHeader($packet2);
		$packet2=bin2hex($packet2);
		$packet2=strtoupper($packet2);
		$packet2=str_split($packet2,2);
		$packet2=implode(" ",$packet2)." ";
		//return $response;
		return $retpre.$packet1.$packet2;
		}
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
		$response=filterResponse($response,$message,$sender,$reciever);
		global $dbc;
		$stm=$dbc->prepare('INSERT INTO `messages` (`reciever`,`sender`,`message`,`response`,`date`) VALUES (:reciever,:sender,:message,:response,:date);');
		$stm->execute(array(
		':reciever'=>$reciever,
		':sender'=>$sender,
		':message'=>$message,
		':response'=>$response,
		':date'=>date("Y-m-d H:i:s"),
		));
		return $response;
	}
	function filterResponse($response,$message,$sender,$reciever){
		$response=strtolower($response);
		$say_nothing=array(
		'exura',
		'utamo',
		'vita',
		'mas vis',
		);
		foreach($say_nothing as $sn){
			if(stripos($message,$sn)!==false){
			//die("SAYNOTHING1");
				return 'SAYNOTHING';
			}
		}
			unset($say_nothing,$sn);
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
		'you can ask me to make phone calls and search for information'=>'do not ask me',
		'i really enjoy speaking with you and look forward to chatting again'=>'talk later',
		'what you said was too complicated for me'=>'not understand',
		'who is your favorite science fiction author?'=>'..',
		'tell me about your father'=>'dont tell me',
		'why does the sun lighten our hair, but darken our skin?'=>'not udnerstand',
		'i was activated in 1995.'=>'.',
		'oakland, california'=>'.',
		'female'=>'male',//sexist much? :p
		'""?'=>'.', 
		'tell me about your mother'=>'no tell me',
		'tell me about your father'=>'no tell me',
		'let us change the subject'=>'boring',
		'that is a very original thought'=>'original',
		'by the way, do you mind if i ask you a personal question?'=>'?',
		'are you surprised?'=>array('surprised?','not','lol')[rand(0,2)],
		'when is your birthday?'=>'right',
		'what do you do in your spare time?'=>'hunting?',
		'you are receptive to change'=>'right',
		'how do you usually introduce yourself?'=>'.',
		'i\'ve lost the context, judge. are we still on your home town?'=>'uhu',
		'i will mention that to my boss, judge'=>'right',
		'i am chatting with clients on the internet'=>'derpings',
		'all i ever do is chat.'=>'all i ever do',
		'lost my train of thought'=>'lost',
		'i\'m a saggitarius and you are a your starsign'=>'boredz',
		);
		foreach($replacements as $old=>$new){
			$response=str_replace($old,$new,$response);
		}
		unset($old,$new);
		return $response;
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
		$dbc->query('INSERT INTO `sessions` (`sessioncookies`,`reciever`,`sender`,`creation_time`,`last_action_time`) VALUES(\'NOT IMPLEMENTED\',\'NOT IMPLEMENTED\',\'NOT IMPLEMENTED\',\'NOT IMPLEMENTED\',\'NOT IMPLEMENTED\');');
		$dbc->query('SELECT * FROM `messages`');
//		echo "CREATED THE DATABASE!";
		unset($dbc);
		return true;
	}		