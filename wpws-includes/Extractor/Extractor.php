<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://jacksleight.com/code/licence/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@jacksleight.com so I can send you a copy immediately.
 *
 * @category   JS
 * @package    JS_Extractor
 * @copyright  Copyright (c) 2007-2008 Jack Sleight (http://jacksleight.com)
 * @license    http://jacksleight.com/code/licence/new-bsd     New BSD License
 */

require_once 'Element.php';

class JS_Extractor extends DOMDocument
{
	const EXTRACT_TEXT		= 'EXTRACT_TEXT';
	const EXTRACT_ATTRIBUTE	= 'EXTRACT_ATTRIBUTE';
	const EXTRACT_ELEMENT	= 'EXTRACT_ELEMENT';

	public function __construct($data, $version = null, $encoding = null)
	{
		parent::__construct($version, $encoding);
		$this->registerNodeClass('DOMDocument', 'JS_Extractor');
		$this->registerNodeClass('DOMElement', 'JS_Extractor_Element');
		@$this->loadHTML($data);
	}
	
	public function query($expression)
	{
		$xpath = new DOMXPath($this);
		return $xpath->query($expression);
	}
}