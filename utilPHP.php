/*
//	PHP Singleton
*/

class utilPHP {
	
	function utilPHP() {
		
	}
	
	//Render Function (String, Array (Key {Match} Replace Content)
	function render($template, $data = array()) {
		if (!is_array($data)) return null;
		foreach($data as $key => $val)
			$template = str_replace("{{$key}}", $val, $template);
		return $template;
	}
}