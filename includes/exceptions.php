<?php
class UnloadableClass extends Exception{
	/*
	Exception to be called when a class is attempted to be referenced but does not exist in the context of the php files
	 */
};
?>