<?php

// instant echo for debugging purpose 
if(!function_exists('echome')){
	function echome($val){
		echo str_repeat(' ',1024*64);
		echo $val;
	}
}

//wp-load
if ( !defined('ABSPATH') ) {
	@include('../../../wp-load.php');
}
 
// spintax
require_once('inc/class.spintax.php');
 
/* ------------------------------------------------------------------------*
* Auto Link Builder Class
* ------------------------------------------------------------------------*/
class wp_pinterest_automatic{
public $ch='';
public $db='';
public $spintax='';
public $scrfwraper=false;

/* ------------------------------------------------------------------------*
* Class Constructor
* ------------------------------------------------------------------------*/
function __construct(){
 	//plugin url
 	
	//db 
	global $wpdb;
	$this->db=$wpdb;
	$this->db->show_errors();
	@$this->db->query("set session wait_timeout=28800");

	$this->ch = curl_init();

	/*verboxe todo deactivate verbose
	
	$verbose=fopen( dirname(__FILE__).'/verbose.txt', 'w');
	curl_setopt($this->ch, CURLOPT_VERBOSE , 1);
	curl_setopt($this->ch, CURLOPT_STDERR,$verbose);
	
	*/
	
	curl_setopt($this->ch, CURLOPT_HEADER,1);
	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($this->ch, CURLOPT_TIMEOUT,20);
	curl_setopt($this->ch, CURLOPT_REFERER, 'https://pinterest.com/login/');
	curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:30.0) Gecko/20100101 Firefox/30.0');
	curl_setopt($this->ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
    curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 0); // Many login forms redirect at least once.
	//curl_setopt($this->ch, CURLOPT_COOKIEJAR , "cookie.txt"); 	
	curl_setopt($this->ch, CURLOPT_HEADER, 1);
	curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($this->ch, CURLOPT_ENCODING , "");
	
	//spintax
	$this->spintax = new WPASpintax();
	
	$this->log('ini','Pinterest automatic started ...');
}
 
function pinterest_getboards(){
	
	//we already logged get https://pinterest.com/settings/
	$sess=get_option('wp_pinterest_automatic_session',1);
	
	$this->log('Fetching boards >> after login','Fetching the boards... '.$user);
	
	//proxify
	if(! $this->proxify() ) {
		$res['status']='fail';
		$res['message']='Did not find a valid proxy';
		$this->log('Fetching boards >> Fail', $res['message'] );
		return false;
	};
	
	//get the boards url: http://pinterest.com/resource/NoopResource/get/?data={%22options%22%3A{}%2C%22module%22%3A{%22name%22%3A%22UserBoards%22%2C%22options%22%3A{%22username%22%3A%22mganna%22%2C%22secret_board_count%22%3A0}%2C%22append%22%3Afalse%2C%22errorStrategy%22%3A0}%2C%22context%22%3A{%22app_version%22%3A%22439f%22}}
	//old url: $url = "http://pinterest.com/resource/NoopResource/get/?data={%22options%22%3A{}%2C%22module%22%3A{%22name%22%3A%22UserBoards%22%2C%22options%22%3A{%22username%22%3A%22$user%22%2C%22secret_board_count%22%3A0}%2C%22append%22%3Afalse%2C%22errorStrategy%22%3A0}%2C%22context%22%3A{%22app_version%22%3A%22439f%22}}";
	$url= "https://nl.pinterest.com/resource/NoopResource/get/?data=%7B%22options%22%3A%7B%7D%2C%22module%22%3A%7B%22name%22%3A%22PinCreate%22%2C%22options%22%3A%7B%22image_url%22%3A%22http%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%22%2C%22action%22%3A%22create%22%2C%22method%22%3A%22scraped%22%2C%22link%22%3A%22http%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%22%7D%2C%22append%22%3Afalse%2C%22errorStrategy%22%3A0%7D%2C%22context%22%3A%7B%22app_version%22%3A%228c5407c%22%7D%7D&source_url=%2Fpin%2Ffind%2F%3Furl%3Dhttp%253A%252F%252Ffc05.deviantart.net%252Ffs71%252Ff%252F2012%252F002%252F2%252Fa%252Fangry_birds_png_by_christabelcstr-d4l53ez.png&module_path=App%28%29%3EImagesFeedPage%28resource%3DFindPinImagesResource%28url%3Dhttp%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%29%29%3EGrid%28%29%3EGridItems%28%29%3EPinnable%28url%3Dhttp%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%2C+type%3Dpinnable%2C+link%3Dhttp%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%29&_=1370428493460";
	
	$url = "https://nl.pinterest.com/_ngjs/resource/BoardPickerBoardsResource/get/?source_url=%2Fpin-builder%2F&data=%7B%22options%22%3A%7B%22isPrefetch%22%3Afalse%2C%22field_set_key%22%3A%22board_picker%22%2C%22shortlist_suggestions%22%3A2%7D%2C%22context%22%3A%7B%7D%7D&_=1551892303466";
	
	//localization fix
	$locale = get_option('wp_pinterest_local','www.pinterest');
	$url = str_replace('www.pinterest', $locale, $url); 
	 
	//curl get
 	
	curl_setopt($this->ch, CURLOPT_HEADER,0);
	curl_setopt($this->ch, CURLOPT_URL, trim($url));
	curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("X-Requested-With:XMLHttpRequest"));
	curl_setopt($this->ch,CURLOPT_COOKIE,'_pinterest_sess="'.$sess.'"');
	$exec=$this->curl_exec_follow ( $this->ch);
	
	$x=curl_error( $this->ch );
	 
	//verify returned content 
	if( trim($exec) == '' ){
		
		$res['status']='fail';
		$res['message']='Request to Pinterest did not return the reply with a possible connection error '.$x;
		$this->log('Fetching boards >> Fail', $res['message'] );
		print_r(json_encode($res));
		return false;
	}
	
 	
	//verify successfull login
	if( ! stristr($exec,'"email"') ){
		
		$httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		
		$res['status']='fail';

		if($httpCode == 403){
			
			$res['message']='Your server IP is blocked from PInterest, You will need to use a proxy. Please check the proxy option on the plugin settings page';
			
		}else{
		
			$res['message']='Login did not succeed using this session. either the added session is wrong or the connection did not work code:'.$httpCode;
			
		}
		
		$this->log('Fetching boards >> Fail', $res['message'] );
		print_r(json_encode($res));
		return false;
	}
	 
	if( stristr($exec, 'boards_shortlist') ){
	
		preg_match('{"all_boards":\[.*?\}\]}s', $exec,$matches_temp) ;
		$boardcontain=$matches_temp[0];
	
	}elseif(stristr($exec, '"data":[{"is_collaborative')){
	
		preg_match('{data":\[\{"is_collaborative.*?\]}s', $exec,$matches_temp) ;
		$boardcontain=$matches_temp[0];
	
	}
	
 
	preg_match_all('{board","id":"(.*?)"}s', $boardcontain,$matches);
	preg_match_all('{"name":"(.*?)",}s', $boardcontain,$matches2);
	
	 
	$titles=array();
	$ids = array();
	
	$ids = $matches[1];
	$titles = 	$matches2[1];
  
	
	//json method with sections support
	if (  stristr($exec, 'boards_shortlist')  ||  stristr($exec, '"data":[{"is_collaborative' )  ){
		
		$new_ids = array ();
		$new_names = array ();
		$jsonTxt = $exec;
		$json = (json_decode ( $jsonTxt ));
		
		$all_boards = array();
		
		
		
		if (isset ( $json->resource_response->data->all_boards )) {
			$all_boards = $json->resource_response->data->all_boards;
		}
		
		
		if (count ( $all_boards ) > 0 ) {
			
			foreach ( $all_boards as $single_board ) {
				
				$new_ids [] = $single_board->id;
				$new_names [] = $single_board->name;
				
				//sections
				if($single_board->section_count > 0){
					
			
					//get sections
					$section_url = "https://nl.pinterest.com/_ngjs/resource/BoardSectionsRepinResource/get/?source_url=%2Fpin-builder%2F&data=%7B%22options%22%3A%7B%22isPrefetch%22%3Afalse%2C%22board_id%22%3A%22{$single_board->id}%22%7D%2C%22context%22%3A%7B%7D%7D&_=1550590933185";
					
					curl_setopt($this->ch, CURLOPT_URL, trim($section_url));
					$section_jsonTxt=$this->curl_exec_follow ( $this->ch);
					$x=curl_error( $this->ch );
					
					 
					
					if( stristr ($section_jsonTxt,'resource_response') ){
						
						$section_json = (json_decode ( $section_jsonTxt ));
						
						if(isset($section_json->resource_response->data)){
							
							$sections = $section_json->resource_response->data;
							
							foreach($sections as $single_section){
								$new_ids[]  =  $single_board->id . '_' . $single_section->id;
								$new_names[]  =  ' -- ' . $single_section->title;
							}
						}
					}
				}
			}
		}
		
		if(count($new_ids) > 0 ){
			
			
			
			$res['ids']=$new_ids;
			$res['titles']=$new_names;
			$res['status'] = 'success';
			$this->log('Fetching boards >> Success', count($new_ids) . ' boards fetched successfully.'  );
			
		}
		
		update_option('wp_pinterest_boards',array('ids'=> $res['ids'] , 'titles' => $res['titles'] ));
		
	}else{
		
		$res['status']='fail';
		$res['message'] = "Although we think that this session is correct, we could not find any boards. Did you create boards in your Pinterest account?";
		$this->log('Fetching boards >> Fail',$res['message']);
	
	}
	
	print_r(json_encode($res));
}

 
/**
 * function pinterest_login: login to pinterest and return the csrftoken if success
 * @param unknown $email
 * @param unknown $pass
 * @param string $staylogged
 * @return csrftoken|false 
 */
function pinterest_login(){
	
	// Log login trial
	$this->log('Login','Trying to login ' );
	
	// Proxyfication
	if(! $this->proxify() ) return false;
	
	// Check if we are already logged in then don't login again 
	$oldsession=get_option('wp_pinterest_automatic_session','');
	$wp_pinterest_options=get_option('wp_pinterest_options',array());
	
	if(trim($oldsession) != '') return 'Qpmfgu25x4iuph7CqdBONUDcFGrRDYLN';
	
	return false;
	
	//no more used code
	if(trim($oldsession) != ''  && $staylogged == true   ){
		//good news we already logged in before let's check if the login still active

		$user=get_option('wp_pinterest_automatic_username','');
		$this->log('Login >> Success', 'Pinterest login success* for the username '.$user);
			
		 
		return 'Qpmfgu25x4iuph7CqdBONUDcFGrRDYLN';
		
		//curl get
		$x='error';
		$url='http://www.pinterest.com/';
		curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
		curl_setopt($this->ch, CURLOPT_URL, trim($url));
		curl_setopt($this->ch,CURLOPT_COOKIE, '_pinterest_sess="'.$oldsession.'"');
		
		while (trim($x) != ''  ){
			$exec=curl_exec($this->ch);
			$x=curl_error($this->ch);
		}
		
		if (stristr($exec,'find_friends')){
			
			return 'Qpmfgu25x4iuph7CqdBONUDcFGrRDYLN';
			
		}
		
	}
	
  	
  	// Not logged in let's login
  	
	// user login details
	$email=urlencode(trim($email));
	$pass=urlencode(trim($pass));
			
   	// GET login page : https://pinterest.com/login/ 
 	$url='https://nl.pinterest.com/login/?next=%2Flogin%2F';
 	
 	
 	if( $this->scrfwraper == false ){
 	
	 	// Get login page
		$x='error';
		 
		curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
		curl_setopt($this->ch, CURLOPT_URL, trim($url));
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$exec=curl_exec($this->ch);
		$x=curl_error($this->ch);
		
		if($x != 'error' & trim($x) != ''){
			$this->log('Curl Try Error',$x);
		}
	 
	
 	}else{
 		$exec=$this->scrfwraper;
 	}
 	
 	 
	
	// Proxyify or report if we need a proxy to use 
	if(stristr($exec, 'found a bot')){
		$this->log('Blacklisted IP'	,'Your server IP is blacklisted from pinterest this means connection with pinterest fails and you <strong>must use proxies</strong> to change your ip. Check <a href="http://valvepress.com/use-private-proxies-pinterest-automatic/"><strong>this tutorial</strong></a> on how to use proxies ');
		return false;
	}
	
	//get csrftocken : csrftoken=Qpmfgu25x4iuph7CqdBONUDcFGrRDYLN;
	preg_match_all("{csrftoken=(.*?);}",$exec,$matches,PREG_PATTERN_ORDER);
	
	
	$resz=$matches[1];
	$csrftoken=$resz[0];
	
	//IF FAILED CSRF TOKEN
	if(trim($csrftoken) == '') $csrftoken = 'Qpmfgu25x4iuph7CqdBONUDcFGrRDYLN';
	$this->log('Login >> csrftocken',$csrftoken);
	
	//extract  _pinterest_sess parameter
	preg_match_all("{_pinterest_sess=\"(.*?)\"}",$exec,$matches,PREG_PATTERN_ORDER);
	$res=$matches[1];
	@$sess=$res[0];

	
	if( trim($sess) == ''){
		
		if(stristr($exec, '_pinterest_sess')){
			
			preg_match_all("{_pinterest_sess=(.*?);}",$exec,$matches,PREG_PATTERN_ORDER);
			$res=$matches[1];
			@$sess=$res[0];
			
		}
	}
	
	if(trim($sess) == ''){
		$this->log('Login >> Error','Failed to fetch Pinterest session num cancelling pinning this time ... ');
		return false;
	}

	$this->log('Login >> _pinterest_sess',$sess);
	
	   	
	// POST LOGIN REQUEST
	
	// Url
	$curlurl = "https://nl.pinterest.com/resource/UserSessionResource/create/";
	
	// Post parameters
	$curlpost="source_url=%2Flogin%2F%3Freferrer%3Dhome_page&data=%7B%22options%22%3A%7B%22username_or_email%22%3A%22$email%22%2C%22password%22%3A%22$pass%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3ELoginPage%3ELogin%3EButton(text%3DLog+in%2C+size%3Dlarge%2C+class_name%3Dprimary%2C+type%3Dsubmit%2C+state_badgeValue%3D%22%22%2C+state_accessibilityText%3DLog+in%2C+state_disabled%3Dtrue)";
	
	// Error ini
	$x='error';
  
	// Header params 
	curl_setopt($this->ch, CURLOPT_HTTPHEADER, array( 'Host: nl.pinterest.com' , 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:44.0) Gecko/20100101 Firefox/44.0' , 'Accept: application/json, text/javascript, */*; q=0.01' , 'Accept-Language: en-US,en;q=0.5'  , 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' , 'X-Pinterest-AppState: active' , "X-CSRFToken: $csrftoken" , 'X-NEW-APP: 1' , 'X-APP-VERSION: 3cd6a23' , 'X-Requested-With: XMLHttpRequest' , 'Referer: https://nl.pinterest.com'  )  );
	 
	// Cookie
	curl_setopt($this->ch, CURLOPT_COOKIE,"csrftoken=$csrftoken; _pinterest_sess=$sess");
	
	// Submit
	curl_setopt($this->ch, CURLOPT_URL, $curlurl);
	curl_setopt($this->ch, CURLOPT_POST, true);
	curl_setopt($this->ch, CURLOPT_POSTFIELDS, $curlpost);
	  
	$exec=curl_exec($this->ch);
	$x=curl_error($this->ch);
 
		
 
	// Check success login or fail 
	if(  stristr($exec,'error": null') || stristr($exec,'error":null')){
		$this->log('Login >> New form login ','Success');
		 
	}else{
		 
		$this->log('Login >> New form login ','<span style="color:red">{Fail} </span>'.urlencode($exec));
		return false;
	}
	
	
	//$url='https://nl.pinterest.com/';
	$url = 'https://www.pinterest.de/';
	
	// Get new session
	preg_match_all("{_pinterest_sess=\"(.*?)\"}",$exec,$matches,PREG_PATTERN_ORDER);
	$res=$matches[1];
	@$sess=$res[0];
	
	if( trim($sess) == ''){
	
		if(stristr($exec, '_pinterest_sess')){
				
			preg_match_all("{_pinterest_sess=(.*?);}",$exec,$matches,PREG_PATTERN_ORDER);
			$res=$matches[1];
			@$sess=$res[0];
				
		}
	}
	
	// Check New session
	if( trim($sess) == ''){
		 
		$this->log('Login >> Error','Failed to fetch Pinterest session num 2 with unexpected response '.(str_replace(';','-',$exec)));
		return false;

	}
	
	
	$this->log('Login >> _pinterest_sess:post login',$sess);

	 
	//Check if login success or not by loading main pinterest page	
	$x='error';
	 
	curl_setopt($this->ch, CURLOPT_HTTPHEADER, array( "X-Requested-With:"));
	curl_setopt($this->ch,CURLOPT_COOKIE,"_pinterest_sess=\"$sess\";__utma=229774877.1960910657.1333904477.1333904477.1333904477.1; __utmb=229774877.1.10.1333904477; __utmc=229774877; __utmz=229774877.1333904477.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utmv=229774877.|2=page_name=login_screen=1");
	curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
	curl_setopt($this->ch, CURLOPT_URL, trim($url));
	curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
	$exec=curl_exec($this->ch);
	 
	  
	$x=curl_error($this->ch);
	if($x != 'error' & trim($x) != ''){
		$this->log('Curl Try Error',$x);
	}
 
 
	 
	$cu_is_redirected = preg_match('{Location: http.*?pinterest.de/}s', $exec) ;
 
	 if (stristr($exec,'verified_identity') || stristr($exec, 'Location: http://www.pinterest.de/') || $cu_is_redirected ) { 	

	 	//check if local site like nl.pinterest.com
	 	if($cu_is_redirected ){
	 		
	 		//get redirect uri 
	 		preg_match_all('/^Location:(.*)$/mi', $exec, $matches);
	 		$urls = $matches[1];
	 		$finalurl = $urls[0];
	 			
	 		//clear www.
	 		$finalurl = str_replace('www.', '', $finalurl);
	 		
	 		//check number of .. 
	 		if( substr_count($finalurl, '.') > 1 ){
	 		
	 			preg_match('{\w*?\.pinterest}', $finalurl,$matches2);
	 			
	 			$localpinterest = $matches2[0];
	 			
	 			if(trim($localpinterest) != ''){
	 				$this->log('Pinterest Localization', $localpinterest);
	 				update_option('wp_pinterest_local' , $localpinterest);
	 			}
	 			
	 			
	 			//get the new main domain again
	 			$x='error';
	 			 
 				curl_setopt($this->ch, CURLOPT_HTTPHEADER, array( "X-Requested-With:"));
 				curl_setopt($this->ch,CURLOPT_COOKIE,"_pinterest_sess=\"$sess\";__utma=229774877.1960910657.1333904477.1333904477.1333904477.1; __utmb=229774877.1.10.1333904477; __utmc=229774877; __utmz=229774877.1333904477.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utmv=229774877.|2=page_name=login_screen=1");
 				curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
 				curl_setopt($this->ch, CURLOPT_URL, trim( str_replace('www.pinterest', $localpinterest, $url)  ));
 				curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
 				$exec=curl_exec($this->ch);
 					
 					
 				$x=curl_error($this->ch);
 				if($x != 'error' & trim($x) != ''){
 					$this->log('Curl Try Error',$x);
 				}
	 			
	 			 
	 		}else{
	 			delete_option('wp_pinterest_local');
	 		}
	 			 
	 		
	 	}
	 	 
	 	// Get username 
	 	//preg_match_all("{is_employee\":false,\"username\":\"(.*?)\"}",$exec,$matches,PREG_PATTERN_ORDER);
	 	//preg_match_all("{username\":\\s?\"([^\"]*?)\"\\s,\\s\"verified_identity}",$exec,$matches,PREG_PATTERN_ORDER);
	 	//preg_match_all('{UserResource"\s?,\s?"options"\s?:\s?\{\s?"username"\s?:\s?"(.*?)"}',$exec,$matches,PREG_PATTERN_ORDER);
	 	
	 	preg_match('{username":\s?"(\w*?)"}',$exec,$matches);
	 	
	 	$user = '';
	 	if( isset( $matches[1] ) ){
	 		$user = 	$matches[1] ;
	 	}
	  	 
	 	update_option('wp_pinterest_automatic_username',$user);
	 	 
	 	$this->log('Login >> Success', 'Pinterest login success for the username '.$user);
	 	update_option('wp_pinterest_automatic_session',$sess);
	 	return $csrftoken;
	 }else{
	 	$this->log('Login >> Failed login ', 'Pinterest Login Failed');
	 	return false;
	 }
	
}

function pinterest_pin($tocken,$board,$details,$link,$imgsrc ,$wp_pinterest_options = false,$post_id = false){
 
 
 	//validate board
 	//getting board
	$wp_pinterest_boards = get_option ( 'wp_pinterest_boards', array (
			'ids' => array (),
			'titles' => array ()
	) );
	
	$wp_pinterest_boards_ids = $wp_pinterest_boards ['ids'];
	$wp_pinterest_boards_titles = $wp_pinterest_boards ['titles'];
	
	//default board
	$wp_pinterest_board = get_option('wp_pinterest_board');
	
	
	//empty board validation
	if(trim($board) == ''){
		$board = $wp_pinterest_board ;
		$this->log('Notice','Empty board was chosen, Reverting to the default board');
	}
	
	//board existance validation
	if( ! in_array($board,$wp_pinterest_boards_ids)){
		$this->log('Notice','Chosen board with id = '.$board. ' was not found among boards list, may be it was deleted so reverting to the default board');
		$board = $wp_pinterest_board ;
	}
	
 
	//strip slashes 
	$details = stripslashes($details);
	
	//pin link
	$wp_pinterest_pin_link_to = get_option('wp_pinterest_pin_link_to','post') ;
	
	if($wp_pinterest_pin_link_to == 'fixed'){

		$wp_pinterest_pin_link_to_link = get_option('wp_pinterest_pin_link_to_link','');
		if(stristr($wp_pinterest_pin_link_to_link,'http')){
		
			// if multiple links
			if(stristr(  $wp_pinterest_pin_link_to_link  , ',')){
				$links_arr = explode(',', $wp_pinterest_pin_link_to_link);
				$link = $links_arr[array_rand($links_arr)];
			}else{
				$link = trim($wp_pinterest_pin_link_to_link);
			}
		   
		}
		
		
		
	}elseif($wp_pinterest_pin_link_to == 'custom'){
		
		$wp_pinterest_pin_link_to_link = get_option('wp_pinterest_pin_link_to_link','');
		
		if(trim($wp_pinterest_pin_link_to_link)!= '' && is_numeric($post_id)){
			$possibleLink = get_post_meta( $post_id , $wp_pinterest_pin_link_to_link , true );
			
			if(is_array($possibleLink)){
				
				
				$serialized_val =  serialize($possibleLink);
				
				preg_match_all( '{"(http.*?)"}' , $serialized_val , $possibleLinksMatchs );
				
				$possibleLinksMatchs = $possibleLinksMatchs[1];
				
				$possibleLink = reset($possibleLinksMatchs);
				
			}
			
			  
			if(stristr($possibleLink,'http')){
				$link = trim($possibleLink);
			}
		}
	}
	
	
	
	// public options
	if($wp_pinterest_options == false){
		$wp_pinterest_options = get_option('wp_pinterest_options',array());
	}
	
	//attachments link
	if( in_array('OPT_ATTACH_LINK' ,  $wp_pinterest_options ) ){
		 	
		$attach_imgsrc = preg_replace('{-\d*x\d*(\.[a-z]*$)}', "$1", $imgsrc);
		
		if(stristr($attach_imgsrc,$_SERVER['HTTP_HOST'])){
			
			global $wpdb;
			 
			$query="SELECT * FROM {$wpdb->prefix}posts where post_type='attachment' and  guid='$attach_imgsrc' limit 1";
			 
			$rows=$wpdb->get_results($query);
			 
			
			if(count($rows) > 0){
				
				$row = $rows[0];
				$attach_id = $row->ID;
				$attach_link = get_attachment_link($attach_id);
				
				if(stristr($attach_link , 'http') ){
					$link = $attach_link;
				}
				
			}
			
		}
		
		
		
		
	}
	
	  
	//search and replace at links
	$wp_pinterest_search_replace =  stripslashes( get_option('wp_pinterest_search_replace_lnk','') );
	
	if(trim($wp_pinterest_search_replace) != ''){
		
		$wp_pinterest_search_replace_arr =  explode("\n",trim( $wp_pinterest_search_replace));
		$wp_pinterest_search_replace_arr =  array_filter($wp_pinterest_search_replace_arr);
		
		foreach($wp_pinterest_search_replace_arr as $searchPattern){
			
			if( stristr($searchPattern, '|') ){
				
				$searchPatternArr  = explode('|', $searchPattern);
				
				$searchText = $searchPatternArr[0];
				
				if(isset($searchPatternArr[1])){
					$replaceText = $searchPatternArr[1];
				}else{
					$replaceText = '';
				}
				
				//replacing
				if(trim($searchText) !=  ''){
					
				
				 $link = preg_replace('{' . trim($searchText)  . '}' ,  trim($replaceText) , $link  );
					//$link = str_replace(trim($searchText), trim($replaceText), $link);
				
				  
				 
				}
				
			}
			
		}
		
	 
	}
	

	
	
	// additional params 
	if(in_array('OPT_PARAM' , $wp_pinterest_options)){
		
		$wp_pinterest_automatic_param = get_option('wp_pinterest_automatic_param','');
		
		if(trim($wp_pinterest_automatic_param) != ''){

			if(! stristr ($link,'?')) $link = trim($link.'?');
			
			if(stristr($wp_pinterest_automatic_param,'?')) $wp_pinterest_automatic_param = str_replace('?','',$wp_pinterest_automatic_param);
			
			$link.=$wp_pinterest_automatic_param;
			
		}
		
	}

	 

	//trim
	$imgsrc = trim($imgsrc);
	
	//search and replace
	$wp_pinterest_search_replace = get_option('wp_pinterest_search_replace','');
	 
	if(trim($wp_pinterest_search_replace) != ''){
		
		$wp_pinterest_search_replace_arr =  explode("\n",trim( $wp_pinterest_search_replace));
		$wp_pinterest_search_replace_arr =  array_filter($wp_pinterest_search_replace_arr); 
		
		foreach($wp_pinterest_search_replace_arr as $searchPattern){
			
			if( stristr($searchPattern, '|') ){
				
				$searchPatternArr  = explode('|', $searchPattern);
				
				$searchText = $searchPatternArr[0];
				
				if(isset($searchPatternArr[1])){
					$replaceText = $searchPatternArr[1];
				}else{
					$replaceText = '';
				}
				
				//replacing
				if(trim($searchText) !=  ''){
					$imgsrc = str_replace(trim($searchText), trim($replaceText), $imgsrc);
				}
				
			}
			
		}
		
	}
	
	//replace in pin hash tags
	if(stristr($details, '#')){

		//search and replace
		$wp_pinterest_search_replace_txt = get_option('wp_pinterest_search_replace_txt','');
		
		if(trim($wp_pinterest_search_replace_txt) != ''){
		
			$wp_pinterest_search_replace_arr =  explode("\n",trim( $wp_pinterest_search_replace_txt));
			$wp_pinterest_search_replace_arr =  array_filter($wp_pinterest_search_replace_arr);
		
			foreach($wp_pinterest_search_replace_arr as $searchPattern){
					
				if( stristr($searchPattern, '|') ){
		
					$searchPatternArr  = explode('|', $searchPattern);
		
					$searchText = $searchPatternArr[0];
		
					if(isset($searchPatternArr[1])){
						$replaceText = $searchPatternArr[1];
					}else{
						$replaceText = '';
					}
		
					//replacing
					if(trim($searchText) !=  ''){
 
						while ( preg_match("{(#\w*)$searchText}u", $details ) ){
							$details = preg_replace("{(#\w*)$searchText}u",  "$1".trim($replaceText) , $details);
						}
							 
					}
		
				}
					
			}
		
		}
		
	}
	
	$this->log('Pinning','Trying to pin an  <a href="'.urlencode($imgsrc) .'" >image</a>');
 
	$deactive = get_option('wp_pinterest_automatic_deactivate', 5 );
	
	// Timthumb check http://askatef.com/wp1/wp-content/plugins/justified-image-grid-old/timthumb.php?src=http%3A%2F%2Faskatef.com%2Fwp1%2Fwp-content%2Fgallery%2Fgallery-1%2Fbg-squares-3d.jpg&h=310&q=90&f=.jpg
	if(stristr($imgsrc, 'timthumb.php')){
	
		if(stristr($imgsrc, 'src=')){
	
			preg_match_all('{src=(.*?)&}', $imgsrc,$timthumbMatchs);
	
			$timthumbMatch = $timthumbMatchs[1];
			$timThumbImgSrc = $timthumbMatch[0];
	
			if(trim($timThumbImgSrc) != ''){
					
				$timThumbImgSrc = urldecode($timThumbImgSrc);
					
				$this->log('TimThumb image?', 'This image may be a resized version. We will see if <a href="'.$timThumbImgSrc.'">full sized</a> exists.');
				
				if($this->curl_file_exists($timThumbImgSrc) ){
					$this->log('Full image','Good,Full image exists');
					$imgsrc =$timThumbImgSrc;
				}else{
					$this->log('Full image','Full image does not exists using the current one');
				}
				
			}
	 
		}
	
	}
	
	
	//get the full sized image http://teamsocialwork.com/wp-content/uploads/Pagoda-298x300.png 
	
	if( preg_match('{-\d*x\d*\.[a-z]*$}', $imgsrc,$matches )){
	
		//we have a resized image of an original one
		$newimgsrc = preg_replace('{-\d*x\d*(\.[a-z]*$)}', "$1", $imgsrc);
		
		
		//log resized
		$this->log('Resized image?', 'This image may be a resized version. We will see if <a href="'.$newimgsrc.'">full sized</a> exists.');
	 	
	 	if( ! in_array('OPT_FULL_SIZE',$wp_pinterest_options)){
	 		
	 		if($this->curl_file_exists($newimgsrc)){
	 			$this->log('Full image','Good,Full image exists');
	 			$imgsrc =$newimgsrc;
	 		}else{
	 			$this->log('Full image','Full image does not exists using the current one');
	 		}
	 		
	 	}else{
	 		
	 		$this->log('Full image','Using the full size image without checking');
	 		$imgsrc =$newimgsrc;
	 		
	 	}
		
	
	}
	
	//correct // image source start
	if(preg_match('{^\/\/}', $imgsrc)){
		$imgsrc = 'http:'.$imgsrc;
	}
	

 	
	// srtip shortcodes
	$details = strip_shortcodes($details);
	
	// manually strip shortcodes
	$details = preg_replace("{\[.*?\]}s", '', $details);
	
	//pin title separation from pin text 
	if( stristr($details,':separator:') ){
		
		$details_parts = explode( ':separator:' , $details );
		
		$details =  $details_parts[0];
		$pin_title = $details_parts[1];
		$pin_title = strip_tags($pin_title); //plain text
		
		 //100 char limit for title 
		$short_pin_title = wp_pinterest_substr($pin_title, 0 , 93);
		
		if($pin_title != $short_pin_title) $pin_title = $short_pin_title . '...';
		
	}else{
		$pin_title = '';
	}

	//check if we are deactivated and need more link
	if( (time() < $deactive || in_array('OPT_REGULAR', $wp_pinterest_options)  ) && ! stristr($details, '[post_link]') ){
		$wp_pinterest_default_more = get_option('wp_pinterest_default_more','Check more at [post_link]');
		$wp_pinterest_default_more = str_replace('[post_link]', $link.' ', $wp_pinterest_default_more);
		$details = $details.' '.$wp_pinterest_default_more;
	}
	 
 
	
	//maximum 500 check
	if(function_exists('mb_strlen')){
		$char_count = mb_strlen($details);
	}else{
		$char_count = strlen($details);
	}
	 
	 //strip slashes
	 $details = stripslashes($details);
	 $pin_title = stripslashes($pin_title);
	 
	 
	 //amp problem &#038;  '&amp;'
	 $details = str_replace('&amp;', '&', $details);
	 $pin_title = str_replace('&amp;', '&', $pin_title);
	 
	 $details = str_replace('&#038;', '&', $details);
	 $pin_title = str_replace('&#038;', '&', $pin_title);

	 // Limit chars count
	 if( $char_count > 500 ){
	 	$details =  $this->truncateHtml($details,400,'...');
	 }
	 
	 
	 //skip double quotes as pinterest do escape them before encoding
	 $details = str_replace('"', '\"', $details);
	 $pin_title = str_replace('"', '\"', $pin_title);
	 
	 $details= urlencode($details);
	 $pin_title = urlencode($pin_title);
	 
	 //replace the %0D%0A by %5Cn  strange but %0D is \r and %0A is \n but pinterest do convert new lines to \n before encoding so after encodging it transform to %5Cn  
	 $details = str_replace('%0D%0A', '%5Cn', $details);
	 $details = str_replace('%0D', '%5Cn', $details);
	 $details = str_replace('%0A', '%5Cn', $details);

	 $pin_title = str_replace('%0D%0A', '%5Cn', $pin_title);
	 $pin_title = str_replace('%0D', '%5Cn', $pin_title);
	 $pin_title = str_replace('%0A', '%5Cn', $pin_title);
	 
	 
	//curl post url http://pinterest.com/resource/PinResource/create/
	
	$curlurl = 'https://nl.pinterest.com/resource/PinResource/create/';
	$curlurl  = "https://nl.pinterest.com/_ngjs/resource/PinResource/create/";
	 
	$locale = get_option('wp_pinterest_local','www.pinterest');
	$curlurl = str_replace('www.pinterest', $locale, $curlurl);   
	
	
	$original_link=$link;
	$original_src=$imgsrc;

	if( !  ( stristr($link, 'http') ||  stristr($link, 'www') ) ){
		$link= $_SERVER['HTTP_HOST'] .$link;
	}
	
	if( ! ( stristr($imgsrc, 'http') ||  stristr($imgsrc, 'www')  ) ){
		$imgsrc= $_SERVER['HTTP_HOST'] .$imgsrc;
	}
	
	//session
	$sess=get_option('wp_pinterest_automatic_session',1);
	
	//check we are deactivated
	
	//section injection
	if(stristr($board,'_')){
		 $destination_board = str_replace('_','%22%2C%22section%22%3A%22',$board);
	}else{
		$destination_board = $board;
	}
	
	 
	
	if( time() < $deactive  || in_array('OPT_REGULAR', $wp_pinterest_options) ) {
		
		//deactivated lets upload the image
		$this->log('Pinning >> upload', 'Uploading the image, Cause linkable pins not active ');
 
		/*
		//downloading image...
		$img_real_path = $this->cache_image($imgsrc);
		
		if( $img_real_path == false){
			return false;
		}else{
			$this->log('Pinning >> local path',$img_real_path);
		}
		
		
		//posting the image
		$img_base_name = basename($img_real_path);
		preg_match('{\.([a-z]*)}', $img_real_path,$ext_matches);
		$img_ext = $ext_matches[1];
		
		$up_url = "http://www.pinterest.com/upload-image/?img=". ($img_base_name);

		//curl post
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("X-CSRFToken:$tocken","X-File-Name:$img_base_name","X-Requested-With:XMLHttpRequest" ));
		curl_setopt($this->ch, CURLOPT_COOKIE,'_pinterest_sess="'.$sess.'";csrftoken='.$tocken);
		curl_setopt($this->ch, CURLOPT_REFERER, 'http://pinterest.com/');
		$curlpost=array('img' => "@$img_real_path;type=image/$img_ext");
		curl_setopt($this->ch, CURLOPT_URL, $up_url);
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $curlpost); 
		$img_exec=curl_exec($this->ch);
		$x=curl_error($this->ch);

		if(trim($img_exec) == ''){
			$this->log('Pinning >> upload error' ,'can not upload image to pinterest '.$x );
			return false;
		}
		
		 
		preg_match('{image_url.*"(http:.*?)"}', $img_exec,$img_aws_matches) ;
		$img_aws =  $img_aws_matches[1];
		
		if(trim($img_aws)==''){
			$this->log('Pinning >> upload error' ,'can not find image url '.$img_exec );
			return false;
		}else{
			$this->log('Pinning >> upload success' ,'image uploaded to be pinned successfully '.$img_aws );
		}
		
		//good we have an image now that is hosted on aws
		*/
		$curlpost ="source_url=%2F&data=%7B%22options%22%3A%7B%22board_id%22%3A%22$destination_board%22%2C%22title%22%3A%22$pin_title%22%2C%22description%22%3A%22$details%22%2C%22link%22%3A%22%22%2C%22share_facebook%22%3Afalse%2C%22image_url%22%3A%22".urlencode($imgsrc)."%22%2C%22method%22%3A%22uploaded%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=PinUploader(show_title%3Dtrue%2C+shrinkToFit%3Dtrue)%23Modal(module%3DPinCreate())";
		//$curlpost = "source_url=%2Fpin-builder%2F&data=%7B%22options%22%3A%7B%22board_id%22%3A%22331507291260412197%22%2C%22field_set_key%22%3A%22create_success%22%2C%22skip_pin_create_log%22%3Atrue%2C%22description%22%3A%22tst%22%2C%22link%22%3A%22https%3A%2F%2Fuploadc.wikimedia.org%2Fwikipedia%2Fcommons%2Fthumb%2Fa%2Faf%2FAll_Gizah_Pyramids.jpg%2F290px-All_Gizah_Pyramids.jpg%22%2C%22title%22%3A%22%22%2C%22image_url%22%3A%22https%3A%2F%2Fupload.wikimedia.org%2Fwikipedia%2Fcommons%2Fthumb%2Fa%2Faf%2FAll_Gizah_Pyramids.jpg%2F290px-All_Gizah_Pyramids.jpg%22%2C%22method%22%3A%22scraped%22%2C%22scrape_metric%22%3A%7B%22source%22%3A%22www_url_scrape%22%7D%7D%2C%22context%22%3A%7B%7D%7D";
		
	}else{
		
		//not deactivated 
		//curl post data data=%7B%22options%22%3A%7B%22board_id%22%3A%2285638899103223404%22%2C%22description%22%3A%22test%22%2C%22link%22%3A%22http%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%22%2C%22image_url%22%3A%22http%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%22%2C%22method%22%3A%22scraped%22%7D%2C%22context%22%3A%7B%22app_version%22%3A%2291bf%22%7D%7D&source_url=%2Fpin%2Ffind%2F%3Furl%3Dhttp%253A%252F%252Ffc05.deviantart.net%252Ffs71%252Ff%252F2012%252F002%252F2%252Fa%252Fangry_birds_png_by_christabelcstr-d4l53ez.png&module_path=App()%3EImagesFeedPage(resource%3DFindPinImagesResource(url%3Dhttp%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png))%3EGrid()%3EPinnable(url%3Dhttp%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%2C+link%3Dhttp%3A%2F%2Ffc05.deviantart.net%2Ffs71%2Ff%2F2012%2F002%2F2%2Fa%2Fangry_birds_png_by_christabelcstr-d4l53ez.png%2C+type%3Dpinnable)%23Modal(module%3DPinCreate())
		//$curlpost="data=%7B%22options%22%3A%7B%22board_id%22%3A%22$board%22%2C%22description%22%3A%22$details%22%2C%22link%22%3A%22$link%22%2C%22image_url%22%3A%22".urlencode($imgsrc)."%22%2C%22method%22%3A%22scraped%22%7D%2C%22context%22%3A%7B%22app_version%22%3A%2291bf%22%7D%7D&source_url=%2Fpin%2Ffind%2F%3Furl%3D$imgsrc&module_path=App()%3EImagesFeedPage(resource%3DFindPinImagesResource(url%3D$imgsrc))%3EGrid()%3EPinnable(url%3D$imgsrc%2C+link%3D$link%2C+type%3Dpinnable)%23Modal(module%3DPinCreate())";
		//$curlpost ="source_url=%2Fpin%2Ffind%2F%3Furl%3D".urlencode($imgsrc)."&data=%7B%22options%22%3A%7B%22board_id%22%3A%22$board%22%2C%22description%22%3A%22$details%22%2C%22link%22%3A%22".urlencode($imgsrc)."%22%2C%22image_url%22%3A%22".urlencode($imgsrc)."%22%2C%22method%22%3A%22scraped%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App()%3EImagesFeedPage(resource%3DFindPinImagesResource(url%3D".urlencode($imgsrc)."))%3EGrid()%3EGridItems()%3EPinnable()%3EShowModalButton(module%3DPinCreate)%23Modal(module%3DPinCreate())";
		//$curlpost = "source_url=%2Fpin%2Ffind%2F%3Furl%3D".urlencode($imgsrc)."&data=%7B%22options%22%3A%7B%22method%22%3A%22scraped%22%2C%22description%22%3A%22$details%22%2C%22link%22%3A%22$link%22%2C%22image_url%22%3A%22".urlencode($imgsrc)."%22%2C%22share_facebook%22%3Afalse%2C%22board_id%22%3A%$board%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EModalManager%3EModal%3EPinCreate%3EBoardPicker%3ESelectList(view_type%3DpinCreate%2C+selected_section_index%3Dundefined%2C+selected_item_index%3Dundefined%2C+highlight_matched_text%3Dtrue%2C+suppress_hover_events%3Dundefined%2C+scroll_selected_item_into_view%3Dtrue%2C+select_first_item_after_update%3Dfalse%2C+item_module%3D%5Bobject+Object%5D)";
		
		
		$curlpost = "source_url=%2Fpin%2Ffind%2F%3Furl%3D".urlencode( urlencode($imgsrc))."&data=%7B%22options%22%3A%7B%22method%22%3A%22scraped%22%2C%22title%22%3A%22$pin_title%22%2C%22description%22%3A%22$details%22%2C%22link%22%3A%22".urlencode( $link )."%22%2C%22image_url%22%3A%22".urlencode($imgsrc)."%22%2C%22share_facebook%22%3Afalse%2C%22board_id%22%3A%22$destination_board%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App%3EModalManager%3EModal%3EPinCreate%3EBoardPicker%3ESelectList(view_type%3DpinCreate%2C+selected_section_index%3Dundefined%2C+selected_item_index%3Dundefined%2C+highlight_matched_text%3Dtrue%2C+suppress_hover_events%3Dundefined%2C+scroll_selected_item_into_view%3Dtrue%2C+select_first_item_after_update%3Dfalse%2C+item_module%3D%5Bobject+Object%5D)";
	
		//base64 test

		/*
		$data = file_get_contents('file:///Users/mohamed/Desktop/Sure-Ill-go-camping...-if-its-like-this-Glamping-in-Jackson-Hole-www.ifits.jpg');
		$base64 = urlencode(base64_encode($data));
		$curlpost= "source_url=%2Fpin-builder%2F&data=%7B%22options%22%3A%7B%22description%22%3A%22test%22%2C%22link%22%3A%22https%3A%2F%2Fglamping.center%2Fsure-ill-go-camping-if-its-like-this-glamping-in-jackson-hole-www-ifits%2F%22%2C%22title%22%3A%22%22%2C%22board_id%22%3A%22331507291260220920%22%2C%22field_set_key%22%3A%22create_success%22%2C%22image_base64%22%3A%22data%3Aimage%2Fjpeg%3Bbase64%2C{$base64}3D%22%2C%22method%22%3A%22uploaded%22%2C%22upload_metric%22%3A%7B%22source%22%3A%22partner_upload_standalone%22%7D%7D%2C%22context%22%3A%7B%7D%7D";
		*/
	}
	
	 //no title? remove parameter
	if( trim($pin_title) == '' )  $curlpost = str_replace('%22%2C%22title%22%3A%22', '' ,$curlpost);
 
	$link=$original_link;
	$imgsrc=$original_src;
	
	
	
    $x='error';
    
    
	//curl post
 	//@curl_setopt($this->ch, CURLOPT_HTTPHEADER, "HOST:pinterest.com");
 	//curl_setopt($this->ch, CURLOPT_HTTPHEADER, array( "Origin: https://www.pinterest.com","X-CSRFToken:$tocken","X-Requested-With:XMLHttpRequest","Accept:application/json, text/javascript, */*; q=0.01","Accept-Language:en-US,en;q=0.5","Content-Type:application/x-www-form-urlencoded; charset=UTF-8"));
 	//curl_setopt($this->ch,CURLOPT_COOKIE,'_pinterest_sess="'.$sess.'";csrftoken='.$tocken.';_pinterest_pfob=disabled');
 	
    $origin = 'https://nl.pinterest.com';
    $origin = str_replace('www.pinterest', $locale, $origin);
    
    
    curl_setopt($this->ch, CURLOPT_HTTPHEADER, array( "Origin: $origin","Accept:application/json, text/javascript, */*; q=0.01","Accept-Language: en-US,en;q=0.8,ar;q=0.6","X-Requested-With: XMLHttpRequest","X-NEW-APP: 1","X-CSRFToken: Qpmfgu25x4iuph7CqdBONUDcFGrRDYLN","X-APP-VERSION: 5567f7f","X-Pinterest-AppState: active","User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/40.0.2214.111 Chrome/40.0.2214.111 Safari/537.36","Content-Type: application/x-www-form-urlencoded; charset=UTF-8","Accept: application/json, text/javascript, */*; q=0.01","Referer: $origin"  ));
    curl_setopt($this->ch,CURLOPT_COOKIE,'csrftoken=Qpmfgu25x4iuph7CqdBONUDcFGrRDYLN; _pinterest_sess="'.$sess.'"; c_dpr=1');
    
 
	curl_setopt($this->ch, CURLOPT_URL, $curlurl);
	curl_setopt($this->ch, CURLOPT_POST, true);
	curl_setopt($this->ch, CURLOPT_POSTFIELDS, $curlpost); 
	$exec=curl_exec($this->ch);
	$x=curl_error($this->ch);
	if($x != 'error' & trim($x) != ''){
		$this->log('Curl Try Error',$x);
	}
	
	 
	
	if ( stristr($exec,'status":"success') || stristr($exec,'error":null')  ){
 
 		//extract pin url
		//preg_match_all("{\"board\",\"id\":\"(.*?)\"\},\"id\":\"(.*?)\"\},\"error\":null}",$exec,$matches,PREG_PATTERN_ORDER);
		//preg_match_all("{type\":\"pin\",\"id\":\"(.*?)\"}",$exec,$matches,PREG_PATTERN_ORDER);
		//preg_match_all('{type"\s?:\s?"pin"\s?,\s?"id"\s?:\s?"(.*?)"}',$exec,$matches,PREG_PATTERN_ORDER);
		//preg_match_all('{"id":"(\d*?)",}',$exec,$matches,PREG_PATTERN_ORDER);
		
		if( stristr($exec,'object_id_str')  ){
			preg_match_all('{"object_id_str":"(\d*?)"}',$exec,$matches,PREG_PATTERN_ORDER);
		}else{
			preg_match_all('{"id":"(\d*?)","images}',$exec,$matches,PREG_PATTERN_ORDER);
		}
		
		
	 
		
		$res=$matches[1];
		$res= array_reverse($res);
		$pin=$res[0];
		
		 
		
		$url= 'http://pinterest.com/pin/'.$pin;
		
		
		 
		$index = array_search($board	, $wp_pinterest_boards_ids);
		
		
		$this->log('Pinning >> Success',"successful <a href=\"$url\">Pin</a> to board " . $wp_pinterest_boards_titles[$index]  ) ;
		
		//update last pin vars
		update_option('wp_automatic_last_pin_url',$url);
		update_option('wp_automatic_last_pin_src',$imgsrc);
		
		return true;		
	}else{
		 
	 
		if (stristr($exec,'captcha') && ! stristr($exec,'web_unauth_show_recaptcha_for_login_ip_rate_limit') ){
			
			
			//$this->log('Pinning >> Fail',"Pinterest asked for captcha please login manually to your account do a pin solve the captcha and pinning should back without problem also please don't pin very fast just let it be as a manual pin as posible") ;
			
			//this case is no more correct, Pinterest now have the capacha text withen the content even if no capacha was displayed
			
		}else{
			
			//below block was here 			
			
		}

		//extracting error message message": "Your image is too small. Please choose a larger image and try again."
		preg_match_all('{"message":"(.*?)"}s',$exec,$matches);
		
		$message_arr=$matches[1];
		$message = $message_arr[0];
		
		 
		if(stristr($exec,'api_error_code":1')){
			$message = 'Something went wrong at our end '.$message;
		}
		
	
		
		if(trim($message) != '') $message = 'Pinterest Say:<strong>'.$message.'</strong><br><br>';
		
		$this->log('Pinning >> <span style=\"color:red\">{Fail} </span>',"{$message}Pin failed. Please make sure you can login and pin manually without a problem. If the problem presists, Please contact the author  <a href=\"http://codecanyon.net/user/DeanDev\">Here</a> and describe the problem exactly also copy the code below on your mail if available.  <br><br>".urlencode($exec)) ;
		//end of block
	
		
		
		if ( stristr($exec,'429 Unknown') ||  stristr($exec,'combat spam') || stristr($exec, 'had to block pins') || stristr($exec, 'reset your password') ||  stristr($exec,'429 Too Many Requests') ||  stristr($exec,'api_error_code":9') ){
			
				
			if(stristr($exec,'combat spam') || stristr($exec,'429 Unknown') ||  stristr($exec,'429 Too Many Requests') ||  stristr($exec,'api_error_code":9') ){
				$this->log('Slow Request',"Pinterest told us that we have pinned alot and we should slow down, The plugin will idle for one hour then try to pin again. ") ;
			}elseif(stristr($exec, 'had to block pins')){
				$this->log('Bad Server IP',"Server ip is flagged for spam , we will deactivate pinning for one hour ") ;
			}elseif(stristr($exec, 'reset your password')){
				$this->log('Password Reset Required',"Pinterest says you must rest your password . automatic pinning will be deactivated for one hour. please reset your password") ;
			}elseif ( stristr($exec,'429 Unknown') ) {
				$this->log(' 429 ',"Pinterest asked to slow down pinning will be deactivated for one hour") ;
			}
			
			$now=  time() ;
			
			$deactivetill = $now + 3600 ; //seconds
			
			update_option('wp_pinterest_automatic_deactivate', $deactivetill);

				
		}
		
		 
		//check if authorization failed so we delete current session 
		if(stristr($exec, 'api_error_code":3')){
			$this->log('Clearing session',"Last pin trial experienced authorization problem. This means the current Session Cookie is no more valid. Please add a new one at the plugin settings page as we deleted it.") ;
			delete_option('wp_pinterest_automatic_session');
		}
		
		//check if localization redirect delete session
		if(stristr($exec, 'Location:')){
			
			$this->log('Clearing session',"Seems that pinterest is now localized. deleting currens saved session so the plugin log in again.") ;
			delete_option('wp_pinterest_automatic_session');
			
		}
		
 
		return false;
	}	
	 
	
	}

/**
 * Function proxify: connect curl with a proxy 
 */	
function proxify(){
	
	 
	//ini
	$wp_pinterest_options=get_option('wp_pinterest_options',array());
	$wp_pinterest_proxies = get_option('wp_pinterest_proxies','');
	
	if(in_array('OPT_PROXY', $wp_pinterest_options)    ){
		
		// Good we are asked to proxify
		if(  trim($wp_pinterest_proxies) != ''  ){
			//we have proxies set
			
			//parsing proxies
			$proxies= array_filter (explode("\n", $wp_pinterest_proxies));
			
			foreach($proxies as $proxy){
				 
				if(trim($proxy) !='' && stristr($proxy, ':') ){
					$validProxies[]=$proxy;
				}
				 
			}
			
			foreach($validProxies as $validProxy){
			
				$this->log('Try proxy', 'Trying to use the proxy '.$validProxy);
				 
				$proxy=$validProxy;
				curl_setopt($this->ch, CURLOPT_HTTPPROXYTUNNEL, 0);
				$url='https://nl.pinterest.com/';
				curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
				curl_setopt($this->ch, CURLOPT_URL, trim($url));
				
				$proxy_parts=explode(':', $proxy);
				 
				if(count($proxy_parts) > 2){
			
					//authentication
					$proxyUrl = trim($proxy_parts[0]).':'.trim($proxy_parts[1]);
					$loginpassw = trim($proxy_parts[2]).':'.trim($proxy_parts[3]);
					
					curl_setopt($this->ch, CURLOPT_PROXY,  $proxyUrl );
					curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $loginpassw);
					
			
				}else{
					curl_setopt($this->ch, CURLOPT_PROXY, trim($proxy) );
				}
				
				//sock5
				if( in_array('OPT_PROXY_SOCK', $wp_pinterest_options)    ){
					curl_setopt($this->ch , CURLOPT_PROXYTYPE,  CURLPROXY_SOCKS5 );
				}
				
 				 
				$exec=curl_exec($this->ch);
				$x=curl_error($this->ch);
				
				

				if(trim($exec) == ''){
					
					//bad proxy 
					$this->rotate_proxies();
					$this->log('Proxy fail','Empty reply from Pinterest with possible curl error '.$x);

					
				}else{
					
					//good we have a content let's validate
					if(stristr($exec, 'found a bot')){
						
						$this->rotate_proxies();
						$this->log('Proxy fail','Proxy ip blocked from pinterest trying another');
						
					}else{
						
						//not 400 may be a valid response
						if(stristr($exec, 'signup') || (stristr($exec,'Location') && stristr($exec,'pinterest'))  ){
							
							$this->log('Working proxy','This proxy seems to be working lets use.');
							
							//this is a valid proxy
							$this->scrfwraper=$exec; 
							return true;
							
						}else{
							
							$this->log('Proxy fail','Even if this proxy returned content we can not find a pattern to validate this content. debug can show more ');
							$this->rotate_proxies();
						}
						
					}
					
				}
				  
			}
			
			
			//end of all proxies test we are here and none returned success
			$this->log('Proxyify','No valid proxy found to use will skip pinning..');
			return false;
			
			
		}else{
			$this->log('Proxyify','You checked the use proxy option but did not add proxies to use ignoring..');
			return false;
		}
		
		
	}else{
		
		//proxification not set
		return true;
		
	} 
	 
	
	
}	

/**
 * function rotate proxies: roatate the proxies sending the failed one to the bottom
 */
function rotate_proxies(){
	
	$wp_pinterest_proxies = get_option('wp_pinterest_proxies','');
	
	$proxies= array_filter (explode("\n", $wp_pinterest_proxies));
	
	for($i = 1; $i < count($proxies);$i++){
		$newProxies[] = $proxies[$i];
	} 
	
	if(isset($proxies[0])) $newProxies[]= $proxies[0];
	
	
	$newProxiesPlain = implode("\n", $newProxies);
	
	
	update_option('wp_pinterest_proxies', $newProxiesPlain);
	

}

/* ------------------------------------------------------------------------*
* Logging Function
* ------------------------------------------------------------------------*/	
function log($type,$data){
	//$now= date("F j, Y, g:i a");
	$now = date( 'Y-m-d H:i:s',current_time('timestamp'));
	$data=addslashes($data);
	$query="INSERT INTO wp_pinterest_automatic (action,date,data) values('$type','$now','$data')";
	$this->db->query($query);
	
	$insert = $this->db->insert_id;
	
	$insert_below_100 = $insert -100 ;
	
	if($insert_below_100 > 0){
		//delete
		$query="delete from wp_pinterest_automatic where id < $insert_below_100 " ;
		$this->db->query($query);
	}
	
}
	
/**
 * function curl_file_exists check if file exists and can be accessed
 */
function curl_file_exists($url){
	
	//curl get
	@curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.
	
	$x='error';
	curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
	curl_setopt($this->ch, CURLOPT_URL, trim($url));
	curl_setopt($this->ch, CURLOPT_REFERER, $url);
	curl_setopt($this->ch, CURLOPT_NOBODY, true);
	
	$exec=curl_exec($this->ch);
	$x=curl_error($this->ch);
	
	$httpCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	
	
	
	@curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION,0); // Many login forms redirect at least once.
	
	curl_setopt($this->ch, CURLOPT_NOBODY, false);
	
	if($httpCode == '200' || $httpCode == '302' || $httpCode == '301'){
		return true;
	}else{
		$this->log('http code',$httpCode);
		
	}
	
	
	return false;
}

/**
 * return server path on success and false on error
 * @param unknown $imgsrc
 */
function cache_image($imgsrc){

	//upload dir
	$dir = wp_upload_dir();
	$dir_path = $dir['path'];
	
	
	//check if the image already stored on server
	$img_parse=parse_url($imgsrc);

	if($img_parse['host'] == $_SERVER['HTTP_HOST'] && stristr($imgsrc, 'wp-content')){

		//hosted on server getting real path
			
		//after wp-cotent
		$img_after_wp_content_ex = explode('wp-content', $imgsrc) ;
		$img_after_wp_content = $img_after_wp_content_ex[1];

		//before wp-content
		$dir_ex = explode('wp-content', $dir_path);
		$dir_before_wp_content = $dir_ex[0];

		$img_real_path =  $dir_before_wp_content.'wp-content'. $img_after_wp_content;

		if(file_exists($img_real_path)){
			$this->log('Pinning -> img file','File already exists on server');
			return $img_real_path;
		}

	}//exists on server
	
	//file not exists on server let's download it  
	curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
	curl_setopt($this->ch, CURLOPT_URL, trim($imgsrc));
	curl_setopt($this->ch, CURLOPT_HEADER,0);
	$img_cont=curl_exec($this->ch);
	curl_setopt($this->ch, CURLOPT_HEADER,1);
	
	//verify response success
	if(trim($img_cont) == ''){
		$this->log('Pinning >> image ', 'Empty image ');
		return false;
	}
	
	//save the image locally
	$img_cached_path = $dir_path.'/wp_pinterest_automatic_'.basename($imgsrc);
	file_put_contents($img_cached_path, $img_cont);
	
	if(file_exists($img_cached_path)){
		$this->log('Cached','File cached locally without problem');
		return $img_cached_path;
	}else{
		$this->log('Cache problem','File can not be cached locally');
		return false;
	}
	
	
}

function curl_exec_follow( &$ch){

	$max_redir = 3;

	for ($i=0;$i<$max_redir;$i++){

		$exec=curl_exec($ch);
			
		$info = curl_getinfo($ch);


			
			
		if($info['http_code'] == 301 ||  $info['http_code'] == 302){

			//get url 
			
			preg_match_all('/^Location:(.*)$/mi', $exec, $matches);
			$urls = $matches[1];
			$finalurl = $urls[0];
			 
			curl_setopt($ch, CURLOPT_URL, $finalurl);
			$exec=curl_exec($ch);
			
			

		}else{

			//no redirect just return
			break;

		}


	}

	return $exec;

}

function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
					// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
						unset($open_tags[$pos]);
					}
					// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, strtolower($tag_matchings[1]));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= wp_pinterest_substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = wp_pinterest_substr($text, 0, $length - strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = wp_pinterest_substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}//end function
		

}//End

/**
 * function: curl with follocation that will get url if openbasedir is set or safe mode enabled
 * @param unknown $ch
 * @return mixed
 */



/* ------------------------------------------------------------------------*
* Testing the Plugin
* ------------------------------------------------------------------------*/	


?>