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

	function mergeStrings(...$string)
        {
            $str = "";
            foreach ($string as $s)
                $str .= $s;

            return $str;
        }
	function outputLink($url, $text = null)
    	{
            return Utility::render("<a href='{$url}'>{text}</a>", 
                array("text" => ($text ==  null) ? $url:$text));
	}

	public static function outputTable($headers = array(), $data = array())
    	{
            $tableTpl = "<table><thead><tr>{head}</tr></thead><tbody>{data}</tbody></table>";
            $columnCount = count($headers);

            $header = array();

            foreach ($headers as $heading)
            {
                $header[] = "<td>{$heading}</td>";
            }

            $rowData = array();

            if(count($data) == 0) $rowData[] = "<tr><td colspan='{$columnCount}'>No data</td></tr>";

            if(count($rowData) != 1) {

            	foreach ($data as $row) {
                	$rowItem = array();
                	foreach ($row as $key => $val)
                    	    $rowItem[] = "<td>{$val}</td>";

                	$rowData[] = "<tr>" . implode("\r\n", $rowItem) . "</tr>";
            	}

        	}

        	return Utility::render($tableTpl, array(
                	"head" => implode("\r\n", $header),
                	"data" => implode("\r\n", $rowData)
            	));

    	}

}
