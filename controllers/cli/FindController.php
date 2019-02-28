<?php
/**
 * Orange
 *
 * An open source extensions for CodeIgniter 3.x
 *
 * This content is released under the MIT License (MIT)
 * Copyright (c) 2014 - 2019, Project Orange Box
 */

/**
 * _ class.
 *
 * @package CodeIgniter / Orange
 * @author Don Myers
 * @copyright 2019
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://github.com/ProjectOrangeBox
 * @version v2.0.0
 * @filesource
 *
 */

class FindController extends MY_Controller
{
	public function helpCliAction()
	{
		ci('console')->help([
			['Find PHP files matching entered search parse.'=>'find/file database_model'],
		]);
	}

	/**
	 *	Search your application for files.
	 */
	public function fileCliAction($filename=null)
	{
		$filename = ci('console')->get_arg(1,true,'file search term');

		ci('console')->h1('Looking for "'.$filename.'"');

		foreach (get_packages('app','system',true) as $package) {
			foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($package)), '/(.*)(?P<search>'.preg_quote($filename).')(.*)/mi', RecursiveRegexIterator::GET_MATCH) as $match) {
				if (strpos($match[0],'/.') === false) {
					ci('console')->out(str_replace($match['search'],'<cyan>'.$match['search'].'</cyan>',str_replace(ROOTPATH, '', $match[0])));
				}
			}
		}
	}
} /* end class */
