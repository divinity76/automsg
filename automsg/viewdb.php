<?php
header("content-type: text/plain;charset=utf8");
//echo hex2bin(str_replace(' ','','0F 00 9A 0C 00 4C 6F 6F 74 20 6F 66 20 4F 6D 65 6E 2C 00 96 04 0C 00 4C 6F 6F 74 20 6F 66 20 4F 6D 65 6E 1A 00 61 72 65 20 79 6F 75 20 74 65 6C 6C 69 6E 67 20 74 68 65 20 74 72 75 74 68 3F '));
//die();
//var_dump(mt_getrandmax(),PHP_INT_MAX-mt_getrandmax());die();
$dbpath='chats.sqlite3db';
assert(file_exists($dbpath));
$db=new PDO('sqlite:'.$dbpath,'','',array(
PDO::ATTR_EMULATE_PREPARES => false, 
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
));

$foo=$db->query('SELECT name FROM sqlite_master WHERE type = \'table\'');
$tables=$foo->fetchAll(PDO::FETCH_ASSOC);
echo 'found '.count($tables).' tables.'.PHP_EOL.PHP_EOL;

foreach($tables as $table){
	$foo=$db->query('SELECT * FROM `'.$table['name'].'`');
	echo 'table '.$table['name'].': '.PHP_EOL;
	$res=$foo->fetchAll(PDO::FETCH_ASSOC);
	var_dump(array_reverse($res,true));
}
die(PHP_EOL."THAT WAS ALL");
?>


http://127.0.0.1/automsg/msg.php?sender=test&reciever=test2&message=are%20you%20a%20cat?


?name={$urlencode:{$lastsender$}$}&message={$urlencode:{$lastmsg$}$}$


$httpget:http://127.0.0.1/automsg/msg.php?sender={$urlencode:{$lastsender$}$}&message={$urlencode:{$lastmsg$}$}&reciever={$urlencode:{$charactername$}$}$

