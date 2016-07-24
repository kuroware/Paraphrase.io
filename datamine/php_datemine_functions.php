<?php
ini_set('mysql.connect_timeout', '');
ini_set('default_socket_timeout', '');
ini_set('max_execution_time', '');

$mysqli = new mysqli('localhost', 'root', 'universa12A', 'paraphrase');

class Database {
	public static function sanitize($input) {
		/*
		(Mixed) -> Mixed
		 */
		global $mysqli;
		if (is_array($input)) {
			foreach ($input as $key=>$val) {
				$input[$key] = mysqli_real_escape_string($mysqli, trim($val));
			}
		}
		else {
			$input = mysqli_real_escape_string($mysqli, trim($input));
		}
		return $input;
	}
}
$php_link = 'http://php.net/manual/en/';

//Begin loop through the directory
$dir = new DirectoryIterator('functions/');
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $filename = $fileinfo->getFilename();
        $link = basename($filename, '.html') . '.php';
        $function_link = $php_link . $link;

        //Parse the file
        $html_string = file_get_contents('functions/' . $filename);

		//Get the function name
		$function_name = Database::sanitize(explode('</h1>', explode('<h1 class="refname">', $html_string)[1])[0]);

		if ($function_name) {
			//Get the function description
			//Get the primay description in the title tag
			$function_title_description = trim(Database::sanitize(strip_tags(explode('</title>', explode('<title>', $html_string)[1])[0])));
			echo $function_title_description;
			$function_description = trim(Database::sanitize(strip_tags(explode('</p>', explode('<p class="para rdfs-comment">', $html_string)[1])[0])));

			if (!$function_description) {
				echo 'Descip failed for ' . $filename . '<br/>';
			}

			//Combine the function descriptions into one
			$function_description = $function_title_description . "\n" . $function_description;

			//Get the function syntax
			$function_syntax = Database::sanitize(strip_tags(explode('</div>', explode('<div class="methodsynopsis dc-description">', $html_string)[1])[0]));

			if (!$function_syntax) {
				$function_syntax = null;
				echo 'Syntax failed for ' . $filename . '<br/><br/><hr>';
			}
			else {
				$function_syntax = preg_replace("/(\r?\n){2,}/", "\n\n", $function_syntax);
				$function_syntax = preg_replace("/(\r?\n){2,}/", "\n\n", $function_syntax);
				$function_syntax = preg_replace("/(?:\s{2,}|\n)/", "", $function_syntax); 
			}

			//Get the function parameters
			$function_parameters = explode('<div class="refsect1 parameters"', $html_string)[1];
			if ($function_parameters) {
				$pos = strpos($function_parameters, '>');
				$function_parameters = substr($function_parameters, $pos + 1);
				$function_parameters = explode('</div>', $function_parameters)[0];

				//Get the parameter names
				$parameter_names = array();
				$search_names = explode('<code class="parameter">', $function_parameters);
				array_shift($search_names);
				foreach ($search_names as $val) {
					$name = explode('</code>', $val)[0];
					$parameter_names[] = $name;
				}

				//print_r($parameter_names);

				$search_values = explode('<p class="para">', $function_parameters);
				array_shift($search_values);
				array_shift($search_values);
				$parameter_values = array();
				foreach ($search_values as $val) {
					$value = explode('</p>', $val)[0];
					$parameter_values[] = $value;
				}
				
				//print_r($parameter_values);
				$function_parameters = array_combine($parameter_names, $parameter_values);
			}
			else {
				$function_parameters = array();
			}
	/*		array_shift($function_parameters);

			$parameters = array(); //Associative array of parameter_name => parameter_description

			foreach ($function_parameters as $key=>$parameter) {
				$parameter_name = Database::sanitize(strip_tags(explode('</code></dt>', $parameter)[0]));
				$parameter_description = Database::sanitize(strip_tags(explode('</p>', explode('<p class="para">', $parameter)[1])[0]));
				$parameters[$parameter_name] = $parameter_description;
			}*/

			//Get the return value
			$function_returns = explode('<div class="refsect1 returnvalues"', $html_string)[1];
			if ($function_returns) {
				$pos = strpos($function_returns, '>');
				if ($pos) {
					$function_returns = substr($function_returns, $pos + 1);
					$function_returns = explode('</div>', $function_returns)[0];
					$function_returns = explode('<p class="para">', $function_returns);
					array_shift($function_returns);
					$returns = array();
					foreach ($function_returns as $key=>$return) {
						$x = explode('</p>', $return)[0];
						$x = Database::sanitize(trim(strip_tags($x)));
						$x = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $x)));
						$x = trim(preg_replace('/\s\s+/', ' ', $x));
						$returns[] = $x;
					}
					//print_r($returns);
					$function_returns = implode("\n", $returns);

					//To get the return name, take the first word of the syntax
					$name = explode(' ', $function_syntax)[0];
				}
				else {
					$name = null;
					$function_returns = null;
				}
			}
			else {
				$name = null;
				$function_returns = null;
			}

			$insert = "INSERT INTO functions (function_name, syntax, description, language, link) VALUES ('$function_name', '$function_syntax', '$function_description', 0, '$function_link')";
			$result = $mysqli->query($insert)
			or die ($mysqli->error);
			$function_id = $mysqli->insert_id;

			foreach ($function_parameters as $parameter_name => $parameter_value) {
				list($parameter_name, $parameter_value) = Database::sanitize(array($parameter_name, $parameter_value));
				$insert = "INSERT INTO `function_parameters` (parameter_name, parameter_description, function_id, type) VALUES ('$parameter_name', '$parameter_value', '$function_id', 0)";
				$result = $mysqli->query($insert)
				or die ($mysqli->error);
			}

			if ($name && $function_returns) {
				//Insert the return
				$sql = "INSERT INTO `function_parameters` (parameter_name, parameter_description, function_id, type) VALUES ('$name', '" . $function_returns . "', '$function_id', 1)";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
			}
		}
		else {
			echo 'Failed for ' . $filename;
		}
		//die();
    }
}
?>