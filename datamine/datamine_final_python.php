<?php
error_reporting(E_ALL);
ini_set('max_execution_time', '');
$string = file_get_contents('json.txt');
$mysqli = new mysqli('localhost', 'root', 'universa12A', 'paraphrase');
$object = json_decode($string);
echo (count($object));
foreach ($object as $key=>$val) {
	$function_name = $val->name;

	$sql = "SELECT function_id FROM functions_python WHERE function_name = '$function_name'";
	$result = $mysqli->query($sql)
	or die ($mysqli->error);

	$id = mysqli_fetch_row($result)[0];
	$sql = "UPDATE functions_python SET official_type = '" . $val->type . "' WHERE function_id = '$id'";
	$result = $mysqli->query($sql)
	or die ($mysqli->error);


	if (false) {
		if (strpos($val->path, '#')) {
			$link = $val->path;
			$pos = strpos($link, '#');
			$end = trim(substr($link, $pos));
			
			$link = explode('#', $link);
			$link = 'https://docs.python.org/3/' . $link[0] . '.html#' . $link[1];
		//	echo $link;
	/*		$link = 'https://docs.python.org/3/' . $val->path;*/
			$function_name = $val->name;
	/*		echo $end;
			echo $link;*/
			$content = file_get_contents($link);
			if ($content) {
				$fetch = explode($end, $content);
				if (count($fetch) == 2) {
					$i = 0;
					$description = explode('</dd>', explode('<dd>', $content)[1])[0];
					$description = trim(strip_tags($description));
				}
				elseif (count($fetch) == 3) {
					$i = 1;
					$description = explode('</dd>', explode('<dd>', $content)[2])[0];
					$description = trim(strip_tags($description));
				}
				$syntax_fetch = end(explode('<dt id=', $fetch[$i]));
				$syntax = end(explode('<tt class="descclassname">', $syntax_fetch));
				if ($syntax != $syntax_fetch) {
					$syntax = explode('<a ', $syntax)[0];
					$syntax = trim(strip_tags($syntax));
				}
				else {
					$syntax = end(explode('<tt class="descname">', $syntax_fetch));
					if ($syntax) {
					//	echo $syntax;
						$syntax = explode('<a ', $syntax)[0];
						$syntax = trim(strip_tags($syntax));
					}
					else {
						$syntax = null;
					}
				}
				if (!$syntax) {
					echo 'Unable to fetch syntax <br/>';
					$syntax = null;
				}
				else {
					echo $syntax;
				}
				if (!$description) {
					echo 'Unable to fetch description <br/>';
					$description = null;
				}
				else {
					echo $description . '<Br/>';
				}			
				
				echo '<hr>';

				$description = mysqli_real_escape_string($mysqli, $description);
				$syntax = mysqli_real_escape_string($mysqli, $syntax);
				$insert = "INSERT INTO functions_python (function_name, syntax, link, description) VALUES ('$function_name', '$syntax', '$link', '$description')
				ON DUPLICATE KEY UPDATE function_id = function_id";
				$result = $mysqli->query($insert)
				or die ($mysqli->error);
			}
			else {
				//Notify warning
				echo "Failed for name" . $val->name . " in path " . $val->path . "<br/><hr>";
			}
		}
		sleep(1); //Prevent blocking of HTTP ports
	}
}
?>