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

require_once 'Exception.php';

class JS_Extractor_Element extends DOMElement
{
	public function __call($method, $arguments)
	{
		if (method_exists($this, $name = '_' . $this->tagName . '_' . $method)) {
			return call_user_func_array(array($this, $name), $arguments);
		}
		throw new JS_Extractor_Exception("Call to unknown method '$method' for element type '$this->tagName'");
	}
	
	protected function _table_splitCells()
	{
		$this->_splitCells();
		foreach ($this->query('thead') as $thead) {
			$thead->splitCells();
		}
		foreach ($this->query('tfoot') as $tfoot) {
			$tfoot->splitCells();
		}
		foreach ($this->query('tbody') as $tbody) {
			$tbody->splitCells();
		}
		return $this;
	}

	protected function _thead_splitCells()
	{
		$this->_splitCells();
		return $this;
	}
	
	protected function _tfoot_splitCells()
	{
		$this->_splitCells();
		return $this;
	}
	
	protected function _tbody_splitCells()
	{
		$this->_splitCells();
		return $this;
	}
	
	protected function _splitCells()
	{
		foreach ($trs = $this->query('tr') as $r => $tr) {
			foreach ($tr->query('th|td') as $c => $td) {
				if ($td->hasAttribute('rowspan') && is_numeric($rowspan = $td->getAttribute('rowspan'))) {
					$td->removeAttribute('rowspan');
					for ($s = 1; $s < $rowspan; $s++) {
						if ($next = $trs->item($r + $s)) {
							$next->insertBefore($td->cloneNode(true), $next->query('th|td')->item($c));
						}
					}
				}
				if ($td->hasAttribute('colspan') && is_numeric($colspan = $td->getAttribute('colspan'))) {
					$td->removeAttribute('colspan');
					for ($s = 1; $s < $colspan; $s++) {
						$tr->insertAfter($td->cloneNode(true), $td);
					}
				}
			}
		}
	}
	
	public function extract($expressions, $type = JS_Extractor::EXTRACT_TEXT, $attribute = null)
	{
		$data = array();
		$expressions = (array) $expressions;
		$i = key($expressions);
		$parts = (array) array_shift($expressions);
		foreach ($parts as $j => $part) {
			if (!is_int($j)) {
				$name = $j;
			} elseif (!is_int($i)) {
				$name = $i;
			} else {
				$name = null;
			}
			foreach ($this->query($part) as $element) {
				if (isset($name) && !isset($data[$name])) {
					$data[$name] = array();
				}
				if (isset($name)) {
					$pointer =& $data[$name];
				} else {
					$pointer =& $data;
				}
				if (empty($expressions)) {
					switch ($type) {
						case JS_Extractor::EXTRACT_TEXT:
							$pointer[] = trim(preg_replace('/\s+/', ' ', $element->textContent));
						break;
						case JS_Extractor::EXTRACT_ATTRIBUTE:
							$pointer[] = $element->getAttribute($attribute);
						break;
						case JS_Extractor::EXTRACT_ELEMENT:
							$pointer[] = $element;
						break;
					}
				} else {
					$pointer[] = $element->extract($expressions, $type, $attribute);
				}
			}
		}
		return $data;
	}
	
	public function query($expression)
	{
		$xpath = new DOMXPath($this->ownerDocument);
		return $xpath->query($expression, $this);
	}
	
	public function insertAfter($newnode, $refnode = null)
	{
		if (!isset($refnode) || $refnode === $this->parentNode->lastChild) {
			$this->parentNode->appendChild($newnode);
		} else {
			$this->insertBefore($newnode, $refnode->nextSibling);
		}
	}
}