<?php
$functions = explode('<dl class="data">', $html_string);
array_shift($function); //Remove first extraneous value
foreach ($functions as $key=>$function) {
	$function = explode('</dl>', $function)[0];
	
	//Get the function name
	$function_name = explode('<dt id="', $function)[1];
	$pos = strpos($function_name, '">');
	$function_name = substr($function_name, 0, $pos);


	


}
?>