<?php
/*

$options =>
	[name] => lex_lowercase # helper name
	[hash] => Array # key value pair
		[size] => 123
		[fullname] => Don Myers
	[contexts] => ... # full context as object
	[_this] => Array # current context
		[name] => John
		[phone] => 933.1232
		[age] => 21
	['fn']($options['_this']) # if ??? - don't forget to send in the current context
	['inverse']($options['_this']) # else ???- don't forget to send in the current context

	_this is a the data array sent in

	{{xo.block name="age"}}
*/

$plugin['xo:block'] = function ($options) {
	return $options['fn']($options['_this']); /* parse inter block content */
};
