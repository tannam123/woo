<?php


if(! class_exists('WPASpintax')){

class WPASpintax {
	
	//spin 
	function spin($html){
		
		for($i = 0 ;$i < 4 ; $i++){
			
			$html = $this->spintax_this($html);
			
			if(! stristr($html, '{')){
				break;
			}
			
		}
		
		return $html;
		
	}

	//one level spin function
	function spintax_this($html){
	
		preg_match_all('{\{([^{}]*?)\}}s', $html, $matches);
	
		$spintaxed_with_brackets = $matches[0];
		$spintaxed_without_brackets = $matches[1];
	
		$i = 0;
		foreach( $spintaxed_without_brackets as $set){
			
				//valid set let's explode to parts
				$parts = explode('|', $set);
				$random = rand(0,count($parts) -1);
				$random_part = $parts[$random];
					
				//replacing the set with the random part
				$html = str_replace($spintaxed_with_brackets[$i],  $random_part , $html);
			 
	
			$i++;
		}
	
		return $html;
	
	}//one level spin
	
}

}//class exists
?>