<?php

require_once 'vendor/autoload.php';
use Symfony\Component\CssSelector\CssSelector;

class WP_Web_Scraper_Parser {
	
	public $html;
	public $charset;
	public $error;
	public $result;
	public $count;
	public $selector;
	public $xpath;
    public $regex;
	
	public function __construct( $html, $charset = 'UTF-8' ){
		
		$this->html = $html;
		$this->charset = $charset;
		$this->error = null;
		$this->result = null;
		$this->count = 0;
		$this->selector = null;
		$this->xpath = null;
        $this->regex = null;
		
	}
	
	public function parse_selector( $selector ){
		
		$this->selector = $selector;
		try {
			@$this->xpath = CssSelector::toXPath( $selector );
		} catch (Exception $e) {
			$this->error = 'Invalid CSS selector';
		}
		
		if($this->error === null)
			return $this->parse_xpath( $this->xpath );
			
		return $this->result;
		
	}

	public function parse_xpath( $xpath ){
		
		$this->xpath = $xpath;
		$doc = new DOMDocument();
		@$doc->loadHTML('<?xml encoding="'.$this->charset.'" ?>'.$this->html);
		$xpath = new DomXPath($doc);
		@$elements = $xpath->query($this->xpath);
		
		$elements_html = array();
		
		if(is_object($elements)){
			foreach ($elements as $element)
				$elements_html[] = trim($doc->saveHTML($element));
            if( !empty($elements_html) ){
                $this->result = $elements_html;
                $this->count = $elements->length;
            } else {
                $this->error = 'Query returned empty response';
            }
		} elseif($elements === false){
			$this->error = 'Invalid XPath expression';
		}
		
		return $this->result;

	}
    
    public function parse_regex( $regex ){
      
        $this->regex = $regex;
        @$preg_matches = preg_match_all($this->regex, $this->html, $elements, PREG_SET_ORDER);
        
        $elements_html = array();
        
		if($preg_matches !== false && is_array($elements)){
			foreach ($elements as $element)
				$elements_html[] = trim($element[0]);
            if( !empty($elements_html) ){
                $this->result = $elements_html;
                $this->count = $elements->length;
            } else {
                $this->error = 'Query returned empty response';
            }
		} elseif($preg_matches === false){
			$this->error = 'Invalid PREG pattern';
		}
		
		return $this->result;        
        
    }
    
	public function replace_selector( $selector, $with ){
		
		$this->selector = $selector;
		try {
			@$this->xpath = CssSelector::toXPath( $selector );
		} catch (Exception $e) {
            $this->xpath = null;
		}
		
		return $this->replace_xpath( $this->xpath, $with );
		
	}    
    
    public function replace_xpath( $xpath, $with = '' ){
        
        $this->xpath = $xpath;
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="'.$this->charset.'" ?>'.$this->html);
        $xpath = new DomXPath($doc);
        @$elements = $xpath->query($this->xpath);
        
        $elements_remove = array();
        $elements_replace = array();
        
        if(is_object($elements)){
            if($with === ''){
                foreach ($elements as $element)
                    $elements_remove[] = $element;
                foreach( $elements_remove as $element_remove )
                    $element_remove->parentNode->removeChild($element_remove);
            } else {    
                $with_element = $doc->createDocumentFragment();
                foreach ($elements as $element)
                    $elements_replace[] = $element; 
                foreach( $elements_replace as $element_replace ){    
                    $with_element->appendXML($with);
                    $element_replace->parentNode->replaceChild($with_element, $element_replace);                      
                }
            }
        }
        
        return str_replace(array('<body>','</body>'), '', trim($doc->saveHTML($doc->getElementsByTagName('body')->item(0))));
        
    } 
    
    public function basehref($base){
        
        require_once 'vendor/phpuri/phpuri.php';
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="'.$this->charset.'" ?>'.$this->html);   
        
        foreach ($doc->getElementsByTagName('*') as $item){
            if($item->getAttribute('href') != '')
                $item->setAttribute('href', phpUri::parse($base)->join($item->getAttribute('href')));
                //$item->setAttribute('href', $this->rel2abs($item->getAttribute('href'), $base));
            if($item->getAttribute('src') != '')
                $item->setAttribute('src', phpUri::parse($base)->join($item->getAttribute('src')));
                //$item->setAttribute('src', $this->rel2abs($item->getAttribute('src'), $base));            
        }
        
        return str_replace(array('<body>','</body>'), '', trim($doc->saveHTML($doc->getElementsByTagName('body')->item(0))));     
        
    }
    
    public function a_target($target){
        
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="'.$this->charset.'" ?>'.$this->html);   
        
        foreach ($doc->getElementsByTagName('a') as $item)
            $item->setAttribute('target', $target);
        
        return str_replace(array('<body>','</body>'), '', trim($doc->saveHTML($doc->getElementsByTagName('body')->item(0))));      
        
    }    
    
    private function rel2abs($rel, $base) {
        if (strpos($rel, "//") === 0) 
            return $rel;
        /* return if  already absolute URL */
        if (parse_url($rel, PHP_URL_SCHEME) !== null)
            return $rel;
        /* queries and  anchors */
        if ($rel[0] == '#' || $rel[0] == '?')
            return $base . $rel;
        /* parse base URL  and convert to local variables:
          $scheme, $host,  $path */
        extract(parse_url($base));
        /* remove  non-directory element from path */
        $path = preg_replace('#/[^/]*$#', '', $path);
        /* destroy path if  relative url points to root */
        if ($rel[0] == '/')
            $path = '';
        /* dirty absolute  URL */
        $abs = "$host$path/$rel";
        /* replace '//' or  '/./' or '/foo/../' with '/' */
        $re = array('#(/.?/)#', '#/(?!..)[^/]+/../#');
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n));
        /* absolute URL is  ready! */
        $abs = str_replace('//','/', $abs); 
        return $scheme . '://' . $abs;
    }

}