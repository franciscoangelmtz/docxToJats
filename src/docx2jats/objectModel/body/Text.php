<?php namespace docx2jats\objectModel\body;

use docx2jats\objectModel\DataObject;

class Text extends DataObject {
	const DOCX_TEXT_BOLD = 1;
	const DOCX_TEXT_ITALIC = 2;
	const DOCX_TEXT_SUPERSCRIPT = 3;
	const DOCX_TEXT_SUBSCRIPT = 4;
	const DOCX_TEXT_STRIKETHROUGH = 5;
	const DOCX_TEXT_EXTLINK = 6;
	
	private $properties;
	private $text;
	private $type = array();
	
	public function __construct(\DOMElement $domElement) {
		parent::__construct($domElement);
		$this->properties = $this->setProperty('w:rPr/child::node()');
		$this->text = $this->setText('w:t');
		$this->type = $this->setType();
	}
	
	/**
	 * @return string
	 */
	
	private function setText(string $xpathExpression) {
		$stringText = '';
		$contentNodes = $this->getXpath()->evaluate($xpathExpression, $this->getDomElement());
		/* @var $contentNode \DOMElement */
		foreach ($contentNodes as $contentNode) {
			$stringText = $stringText . $contentNode->nodeValue;
		}
		
		return $stringText;
	}
	
	/**
	 * @return string
	 */
	public function getContent(): string {
		return $this->text;
	}
	
	/**
	 * @return array
	 */
	public function getProperties(): array {
		return $this->properties;
	}
	
	/**
	 * @return array
	 */
	private function setType() {
		$type = array();
		
		$properties = $this->getXpath()->query('w:rPr/child::node()', $this->getDomElement());
		foreach ($properties as $property) {
			switch($property->nodeName) {
				case "w:b":
					$type[] = $this::DOCX_TEXT_BOLD;
					break;
				case "w:i":
					$type[] = $this::DOCX_TEXT_ITALIC;
					break;
				case "w:vertAlign":
					if ($property->hasAttribute('w:val')) {
						$attrValue = $property->getAttribute('w:val');
						if ($attrValue === "superscript") {
							$type = $this::DOCX_TEXT_SUPERSCRIPT;
						} elseif ($attrValue === "subscript") {
							$type = $this::DOCX_TEXT_SUBSCRIPT;
						}
					}
					break;
				case "w:strike":
					$type[] = $this::DOCX_TEXT_STRIKETHROUGH;
					break;
			}
		}
		
		return $type;
	}
	
	public function addType(string $type): void {
		$this->type[] = $type;
	}
}