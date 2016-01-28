<?php 	
//throw new Exception('DO NOT USE THIS API ANYMORE. GOOGLE FUCKED THE API.');
	function free_google_translate($message="this test message is translated from English to Somali.",$language_from="en",$language_to="so"){
		static $tc=false;
		if($tc===false){
			require_once('./vendor/autoload.php');
			$tc=new Stichoza\GoogleTranslate\TranslateClient();
		}
		$tc->setSource($language_from);
		$tc->setTarget($language_to);
		return $tc->translate($message);
		//WARNING: Uses undocumented, reverse-engineered google translate api that
		// the HTML5 client at https://translate.google.com uses.
		// it could change/break at any time..
		$getTK=function(){
			/*
				a=Math.floor((new Date).getTime()/3600000)^123456;
				b=Math.sqrt(5)-1;
				b=b/2;	
				b=b*(a^654321);
				b=b%1;
				b=b*1048576;
				b=Math.floor(b);
				a+"|"+b
			*/
			// It looks like it's a time-based token, probably to expire old requests.
			// HO=function(){var a=k[ze]((new Date)[Ce]()/36E5)^123456;return a+PB+k[ze]((k.sqrt(5)-1)/2*(a^654321)%1*1048576)}
			// var a=Math.floor((new Date).getTime()/3600000)^123456; return a+"|"+Math.floor((Math.sqrt(5)-1)/2*(a^654321)%1*1048576);
			//(thanks Nanobot@§§freenode #anime )
			$oldpre=ini_get("precision");
			ini_set("precision",21);//"php is thread safe", yeah right...
			$a=floor(((time()*1000)/3600000))^123456;//Todo: for less than 1 second every hour, this function might fail
			//because time()*1000 has less precision than (new Date).getTime() .. we should use 
			//microtime()/1000 something, but with microtime's weird format, its actually non-trivial, 
			//ill fix it later. but, literally speaking, 99.98611% of the time per hour, it returns the right number.
			// circa 0.01389% of the time per hour, it *might* returns the wrong number. lol 
			$b=sqrt(5)-1;
			$b=$b/2;
			$b=$b*($a^654321);
			//$b=$b%1;
			$b=$b-(int)$b;
			$b=$b*1048576;
			$b=floor($b);
			$ret=$a.'|'.$b;
			ini_set("precision",$oldpre);
			return $ret;
		};
		$hhb_curl_init=function($custom_options_array = array()) {
			if(empty($custom_options_array)){
				$custom_options_array=array();
				//i feel kinda bad about this.. argv[1] of curl_init wants a string(url), or NULL
				//at least i want to allow NULL aswell :/
			}
			if (!is_array($custom_options_array)) {
				throw new InvalidArgumentException('$custom_options_array must be an array!');
			};
			$options_array = array(
			CURLOPT_AUTOREFERER => true,
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_COOKIESESSION => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_FORBID_REUSE => false,
			CURLOPT_HTTPGET => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT => 11,
			CURLOPT_ENCODING=>"",
			CURLOPT_COOKIEFILE=>"",
			//CURLOPT_REFERER=>'example.org',
			//CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0'
			);
			/*if (!array_key_exists(CURLOPT_COOKIEFILE, $custom_options_array)) {
				//do this only conditionally because tmpfile() call..
				static $curl_cookiefiles_arr=array();//workaround for https://bugs.php.net/bug.php?id=66014
				$curl_cookiefiles_arr[]=$options_array[CURLOPT_COOKIEFILE] = tmpfile();
				$options_array[CURLOPT_COOKIEFILE] =stream_get_meta_data($options_array[CURLOPT_COOKIEFILE]);
				$options_array[CURLOPT_COOKIEFILE]=$options_array[CURLOPT_COOKIEFILE]['uri']; 
			}*/
			//we can't use array_merge() because of how it handles integer-keys, it would/could cause corruption
			foreach($custom_options_array as $key => $val) {
				$options_array[$key] = $val;
			}
			unset($key, $val, $custom_options_array);
			$curl = curl_init();
			curl_setopt_array($curl, $options_array);
			return $curl;
		};
		
		$hhb_curl_exec=function($ch, $url) {
			static $hhb_curl_domainCache = "";
			//$hhb_curl_domainCache=&$this->hhb_curl_domainCache;
			//$ch=&$this->curlh;
			if(!is_resource($ch) || get_resource_type($ch)!=='curl')
			{
				throw new InvalidArgumentException('$ch must be a curl handle!');
			}
			if(!is_string($url))
			{
				throw new InvalidArgumentException('$url must be a string!');
			}
			$tmpvar = "";
			if (parse_url($url, PHP_URL_HOST) === null) {
				if (substr($url, 0, 1) !== '/') {
					$url = $hhb_curl_domainCache.'/'.$url;
					} else {
					$url = $hhb_curl_domainCache.$url;
				}
			};
			curl_setopt($ch, CURLOPT_URL, $url);
			$html = curl_exec($ch);
			if (curl_errno($ch)) {
				throw new Exception('Curl error (curl_errno='.curl_errno($ch).') on url '.var_export($url, true).': '.curl_error($ch));
				// echo 'Curl error: ' . curl_error($ch);
			}
			if ($html === '' && 203 != ($tmpvar = curl_getinfo($ch, CURLINFO_HTTP_CODE)) /*203 is "success, but no output"..*/ ) {
				throw new Exception('Curl returned nothing for '.var_export($url, true).' but HTTP_RESPONSE_CODE was '.var_export($tmpvar, true));
			};
			//remember that curl (usually) auto-follows the "Location: " http redirects..
			$hhb_curl_domainCache = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL), PHP_URL_HOST);
			return $html;
		};
		
		$hhb_curl_exec2=function($ch,$url,&$returnHeaders=array(),&$returnCookies=array(),&$verboseDebugInfo="") use($hhb_curl_exec){
			$returnHeaders=array();
			$returnCookies=array();
			$verboseDebugInfo="";
			if(!is_resource($ch) || get_resource_type($ch)!=='curl')
			{
				throw new InvalidArgumentException('$ch must be a curl handle!');
			}
			if(!is_string($url))
			{
				throw new InvalidArgumentException('$url must be a string!');
			}
			$verbosefileh=tmpfile();
			$verbosefile=stream_get_meta_data($verbosefileh);
			$verbosefile=$verbosefile['uri'];
			curl_setopt($ch,CURLOPT_VERBOSE,1);
			curl_setopt($ch,CURLOPT_STDERR,$verbosefileh);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			$html=$hhb_curl_exec($ch,$url);
			$verboseDebugInfo=file_get_contents($verbosefile);
			curl_setopt($ch,CURLOPT_STDERR,NULL);
			fclose($verbosefileh);
			unset($verbosefile,$verbosefileh);
			$headers=array();
			$crlf="\x0d\x0a";
			$thepos=strpos($html,$crlf.$crlf,0);
			$headersString=substr($html,0,$thepos);
			$headerArr=explode($crlf,$headersString);
			$returnHeaders=$headerArr;
			unset($headersString,$headerArr);
			$htmlBody=substr($html,$thepos+4);//should work on utf8/ascii headers... utf32? not so sure..
			unset($html);
			//I REALLY HOPE THERE EXIST A BETTER WAY TO GET COOKIES.. good grief this looks ugly..
			//at least it's tested and seems to work perfectly...
			$grabCookieName=function($str){
				$ret="";
				$i=0;
				for($i=0;$i<strlen($str);++$i){
					if($str[$i]===' '){continue;}
					if($str[$i]==='='){break;}
					$ret.=$str[$i];
				}
				return urldecode($ret);
			};
			foreach($returnHeaders as $header){
				//Set-Cookie: crlfcoookielol=crlf+is%0D%0A+and+newline+is+%0D%0A+and+semicolon+is%3B+and+not+sure+what+else
				/*Set-Cookie:ci_spill=a%3A4%3A%7Bs%3A10%3A%22session_id%22%3Bs%3A32%3A%22305d3d67b8016ca9661c3b032d4319df%22%3Bs%3A10%3A%22ip_address%22%3Bs%3A14%3A%2285.164.158.128%22%3Bs%3A10%3A%22user_agent%22%3Bs%3A109%3A%22Mozilla%2F5.0+%28Windows+NT+6.1%3B+WOW64%29+AppleWebKit%2F537.36+%28KHTML%2C+like+Gecko%29+Chrome%2F43.0.2357.132+Safari%2F537.36%22%3Bs%3A13%3A%22last_activity%22%3Bi%3A1436874639%3B%7Dcab1dd09f4eca466660e8a767856d013; expires=Tue, 14-Jul-2015 13:50:39 GMT; path=/
					Set-Cookie: sessionToken=abc123; Expires=Wed, 09 Jun 2021 10:18:14 GMT;
					//Cookie names cannot contain any of the following '=,; \t\r\n\013\014'
					//
				*/
				if(stripos($header,"Set-Cookie:")!==0){continue;/**/}
				$header=trim(substr($header,strlen("Set-Cookie:")));
				while(strlen($header)>0){
					$cookiename=$grabCookieName($header);
					$returnCookies[$cookiename]='';
					$header=substr($header,strlen($cookiename)+1);//also remove the = 
					if(strlen($header)<1){break;};
					$thepos=strpos($header,';');
					if($thepos===false){//last cookie in this Set-Cookie.
						$returnCookies[$cookiename]=urldecode($header);
						break;
					}
					$returnCookies[$cookiename]=urldecode(substr($header,0,$thepos));
					$header=trim(substr($header,$thepos+1));//also remove the ;
				}
			}
			unset($header,$cookiename,$thepos);
			return $htmlBody;
		};
		$ch=$hhb_curl_init();
		//CQkkdXJsPSdodHRwczovL3RyYW5zbGF0ZS5nb29nbGUuY29tJzsNCgkJY3VybF9zZXRvcHQoJGNoLENVUkxPUFRfSFRUUEhFQURFUixhcnJheSgNCgkJImFjY2VwdC1sYW5ndWFnZTogZW4tVVMsZW47cT0wLjgsbmI7cT0wLjYsbm87cT0wLjQsbm47cT0wLjIiLA0KCQkiaHR0cHM6IDEiLA0KCQkiYWNjZXB0OiB0ZXh0L2h0bWwsYXBwbGljYXRpb24veGh0bWwreG1sLGFwcGxpY2F0aW9uL3htbDtxPTAuOSxpbWFnZS93ZWJwLCovKjtxPTAuOCIsDQoJCSJhdXRob3JpdHk6IHRyYW5zbGF0ZS5nb29nbGUuY29tIiwNCgkJKSk7DQoJCWN1cmxfc2V0b3B0KCRjaCxDVVJMT1BUX1VTRVJBR0VOVCwidXNlci1hZ2VudDogTW96aWxsYS81LjAgKFdpbmRvd3MgTlQgNi4xOyBXT1c2NCkgQXBwbGVXZWJLaXQvNTM3LjM2IChLSFRNTCwgbGlrZSBHZWNrbykgQ2hyb21lLzQ0LjAuMjQwMy44OSBTYWZhcmkvNTM3LjM2Iik7DQoJCSRoZWFkZXJzPWFycmF5KCk7DQoJCSRjb29raWVzPWFycmF5KCk7DQoJCSRkZWJ1Z2luZm89IiI7DQoJCSRodG1sMT0kaGhiX2N1cmxfZXhlYzIoJGNoLCR1cmwsJGhlYWRlcnMsJGNvb2tpZXMsJGRlYnVnaW5mbyk7Ly90byBncmFiIGEgc2Vzc2lvbiBjb29raWUgaSBndWVzcwkNCg==		
		//Request URL:https://translate.google.com/translate_a/single?client=t&sl=en&tl=no&hl=en&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&ie=UTF-8&oe=UTF-8&otf=2&ssel=5&tsel=5&kc=1&tk=522781|764974&q=what%20are%20you%20up%20to%3F
		
		$url='https://translate.google.com/translate_a/single?client=t&';
		$url.='sl='.urlencode($language_from).'&tl='.urlencode($language_to).'&hl='.urlencode($language_from).'&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&ie=UTF-8&oe=UTF-8&source=bh&ssel=0&tsel=0&kc=1&tk='.urlencode($getTK()).'&q='.urlencode($message);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array(
		"accept-language: en-US,en;q=0.8,nb;q=0.6,no;q=0.4,nn;q=0.2",
		"user-agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36",
		"accept: */*",
		"referer: https://translate.google.com/",
		"authority: translate.google.com",
		));
		$html2=$hhb_curl_exec2($ch,$url,$headers,$cookies,$debuginfo);
		while(strpos($html2,',,')!==false){
			$html2=str_replace(',,',',null,',$html2);//a little bit hackish and can replace part of the message itself...
			//but a proper javascript parser WOULD BE A HUGE thing to implement.. so we're going with this little hack for now...
		}
		//[,
		while(strpos($html2,'[,')!==false){
			$html2=str_replace('[,','[null,',$html2);//a little bit hackish and can replace part of the message itself...
			//but a proper javascript parser WOULD BE A HUGE thing to implement.. so we're going with this little hack for now...
		}
		//header("content-type: text/plain;charset=utf8");die($html2."           ".base64_encode($html2));
		//var_dump($html2,'<<END OF HTML2',$url,$headers,$cookies,$debuginfo);die("HTML2.");
		$obj=json_decode($html2,true,1337);
		//var_dump($obj);
		$ret=$obj[0][0][0];
		return $ret;
	}