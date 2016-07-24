<?php
require_once __DIR__ . '/../vars/constants.php';
/*require_once __DIR__ . '/../includes/database.php';*/

class TranslationActivity {
	/*
	The class for an activity on a translation
	 */
	public $linked_id, $type, $action_date;

	public static $identifier_restrictions = array(TRANSLATION_ACTIVITY_TYPE, COMMENT_ACTIVITY_TYPE, UPVOTED_ACTIVITY_TYPE, REQUESTED_ACTIVITY_TYPE);

	public static $defaults = array(
		'linked_id' => null,
		'action_date' => null,
		'identifier' => null);

	public function __construct(array $args) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Assign the object properites
		$this->linked_id = $args['linked_id'];
		$this->action_date = ($args['action_date']) ? Database::secondsToTime($args['action_date']) : null;
		$this->identifier = (in_array($args['identifier'], self::$identifier_restrictions)) ? $args['identifier'] : null;

		$formatted_string_date = ''; //The temporary variable to hold the formatted string date
		if ($this->action_date) {
			//echo $this->action_date;
			//Attempt to style the action date
			$x = explode(', ', $this->action_date);
			//print_r($x);
			foreach ($x as $key=>$val) {
				//echo $val;
				//Now go through and select the most relevant one, only adding the ones without 0 in it
				if (!strpos($val, 'minutes')) {
					//There are on minutes in this string so it can only be hours or days
					if (substr($val, 0, 1) != '0') {
						//echo 'found';
						$formatted_string_date .= $val;
						break; //The loop is done, grabbed the most relevant info
					}
				}
				else {
					//echo 'found';
					//Else this loop is minutes, which means the time can onnly be x minutes or x seconds
					$possible_time = explode('and ', $val);
					//print_r($possible_time);
					if (substr($val, 0, 1) == 0) {
						//Minutes is 0, only add seconds then
						$formatted_string_date .= $possible_time[1];
					}
					else {
						//Minutes is not 0, add the minutes
						$formatted_string_date .= $possible_time[0];
					}
				}
			}
			//Trim the string
			$formatted_string_date = trim($formatted_string_date);
			$this->action_date = $formatted_string_date;
		}
	}
}