<?php
/* SVN FILE: $Id$ */
/**
 * HamlParser class file.
 * HamlParser allows you to write view files in
 * {@link http://haml-lang.com/ Haml}.
 * Please see the {@link http://haml-lang.com/docs/yardoc/file.Haml_REFERENCE.html#plain_text Haml documentation} for the syntax.
 * Notes
 * <ul>
 * <li>Debug (addition)<ul>
 * <li>Source debug - adds comments to the output showing each source line above
 * the result - ?#s+ to turn on, ?#s- to turn off, ?#s! to toggle</li>
 * <li>Output debug - shows the output directly in the browser - ?#o+ to turn on, ?#o- to turn off, ?#o! to toggle</li>
 * <li>Control both at once - ?#so+ to turn on, ?#so- to turn off, ?#so! to toggle</li>
 * <li>Ugly mode can be controlled by the template</li>
 * <liUugly mode strips comments in the output by default</li>
 * <li>Ugly mode is turned off when in debug</li></ul></li>
 * <li>"-" command (notes)<ul>
 * <li>PHP does not require ending ";"</li>
 * <li>PHP control blocks are automatically bracketed</li>
 * <li>Switch Case statements do not end with ":"
 * <li>do-while control blocks are written as "do (expression)"</li></ul></li>
 * </ul>
 * Comes with filters that run "out of the box":
 * + <b>plain</b>: useful for large chunks of text to ensure Haml doesn't do anything.
 * + <b>escaped</b>: like plain but the output is (x)html escaped.
 * + <b>preserve</b>: like plain but preserves the whitespace.
 * + <b>cdata</b>: wraps the content in CDATA tags.
 * + <b>javascript</b>: wraps the content in <script> and CDATA tags. Useful for adding inline JavaScript.
 * + <b>css</b>: wraps the content in <style> and CDATA tags. Useful for adding inline CSS.
 * + <b>php</b>: wraps the content in <?php tags. The content is PHP code.
 * There are two filters that require external classes to work. See {@link http://code.google.com/p/phamlp/wiki/PredefinedFilters PredefinedFilters on the PHamlP wiki} for details of how to use them.
 * + <b>markdown</b>: Parses the filtered text with Markdown.
 * + <b>textile</b>: Parses the filtered text with Textile.
 * PHP can be used in all the filters (except php) by wrapping expressions in #().
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml
 */

require_once('tree/HamlNode.php');
require_once('HamlException.php');

/**
 * HamlParser class.
 * Parses {@link http://haml-lang.com/ Haml} view files.
 * @package			PHamlP
 * @subpackage	Haml
 */
class HamlParser {
	/**#@+
	 * Debug modes
	 */
	const DEBUG_NONE = 0;
	const DEBUG_SHOW_SOURCE = 1;
	const DEBUG_SHOW_OUTPUT = 2;
	const DEBUG_SHOW_ALL = 3;
	/**#@-*/

	/**#@+
	 * Regexes used to parse the document
	 */
	const REGEX_Haml = '/(?m)^([ \x09]*)((?::(\w*))?(?:%(\w*))?(?:\.([-_:a-zA-Z]+[-:\w.]*))?(?:#([_:a-zA-Z]+[-_:a-zA-Z0-9]*))?(?:\[(.+)\])?(?:(\()(?:(.*?(?:(?<!\\\\)#\{(?:.+\}?)\}.*?)*\)))?)?(?:(\{)(?:(.*?(?:(?<!\\\\)#\{(?:.+\}?)\}.*?)*\}))?)?(>?<?) *((?:\?#)|!!!|\/\/|\/|-#|!=|&=|!|&|=|-|~|\\\\)? *(.*?)(?:\s(\|)?)?)$/'; // Haml line
	const REGEX_ATTRIBUTES = '/:?(\w+(?:[-:]\w+)*)\s*=>?\s*(?(?=([\'"]))(?:[\'"](.*?)\2)|([^\s,]+))/';
	const REGEX_ATTRIBUTE_FUNCTION = '/^\$?[_a-zA-Z]\w*(?(?=->)(->[_a-zA-Z]\w*)+|(::[_a-zA-Z]\w*)?)\(.+\)$/'; // Matches functions and instantiated and static object methods
	const REGEX_WHITESPACE_CONTROL = '/(.*?)\s+$/s';
	const REGEX_WHITESPACE_CONTROL_DEBUG = '%(.*?)(?:<br />\s)$%s'; // whitespace control when showing output
	//const REGEX_CODE_INTERPOLATION = '/(?:(?<!\\\\)#{(.+?(?:\(.*?\).*?)*)})/';
	/**#@-*/
	const MATCH_INTERPOLATION = '/(?<!\\\\)#\{(.*?)\}/';
	const INTERPOLATE = '<?php echo \1; ?>';


	/**#@+
	 * Haml regex match positions
	 */
	const Haml_Haml									=  0;
	const Haml_INDENT								=  1;
	const Haml_SOURCE								=  2;
	const Haml_FILTER								=  3;
	const Haml_TAG									=  4;
	const Haml_CLASS								=  5;
	const Haml_ID										=  6;
	const Haml_OBJECT_REFERENCE			=  7;
	const Haml_OPEN_XML_ATTRIBUTES	=  8;
	const Haml_XML_ATTRIBUTES 			=  9;
	const Haml_OPEN_RUBY_ATTRIBUTES = 10;
	const Haml_RUBY_ATTRIBUTES			= 11;
	const Haml_WHITESPACE_CONTROL		= 12;
	const Haml_TOKEN								= 13;
	const Haml_CONTENT							= 14;
	const Haml_MULTILINE						= 15;
	/**#@-*/

	/**#@+
	 * Haml tokens
	 */
	const DOCTYPE = '!!!';
	const Haml_COMMENT = '!(-#|//)!';
	const XML_COMMENT = '/';
	const SELF_CLOSE_TAG = '/';
	const ESCAPE_XML = '&=';
	const UNESCAPE_XML = '!=';
	const INSERT_CODE = '=';
	const INSERT_CODE_PRESERVE_WHITESPACE = '~';
	const RUN_CODE = '-';
	const REMOVE_INNER_WHITESPACE = '<';
	const REMOVE_OUTER_WHITESPACE = '>';
	/**#@-*/

	/**#@+
	 * Attribute tokens
	 */
	const OPEN_XML_ATTRIBUTES = '(';
	const CLOSE_XML_ATTRIBUTES = ')';
	const OPEN_RUBY_ATTRIBUTES = '{';
	const CLOSE_RUBY_ATTRIBUTES = '}';
	/**#@-*/

	/**#@+
	 * Directives
	 */
	const DIRECTIVE = '?#';
	const SOURCE_DEBUG = 's';
	const OUTPUT_DEBUG = 'o';
	/**#@-*/

	const IS_XML_PROLOG = 'XML';
	const XML_PROLOG = "<?php echo \"<?xml version='1.0' encoding='{encoding}' ?>\n\"; ?>";
	const DEFAULT_XML_ENCODING = 'utf-8';
	const XML_ENCODING = '{encoding}';

	/**
	 * @var string Doctype format. Determines how the Haml Doctype declaration is 
	 * rendered.
	 * @see doctypes
	 */
	private $format = 'xhtml';
	/**
	 * @var string Custom Doctype. If not null and the Doctype declaration in the
	 * Haml Document is not a built in Doctype this will be used as the Doctype.
	 * This allows Haml to be used for non-(X)HTML documents that are XML compliant.
	 * @see doctypes
	 * @see emptyTags
	 * @see inlineTags
	 * @see minimizedAttributes
	 */
	 private $doctype;
	/**
	 * @var boolean whether or not to escape X(HT)ML-sensitive characters in script.
	 * If this is true, = behaves like &=; otherwise, it behaves like !=.
	 * Note that if this is set, != should be used for yielding to subtemplates
	 * and rendering partials. Defaults to false.
	 */
	private $escapeHtml = false;
  /**
   * @var boolean Whether or not attribute hashes and scripts designated by
   * = or ~ should be evaluated. If true, the scripts are rendered as empty strings.
   * Defaults to false.
   */
	private $suppressEval = false;
  /**
	 * @var string The character that should wrap element attributes. Characters
	 * of this type within attributes will be escaped (e.g. by replacing them with
	 * &apos;) if the character is an apostrophe or a quotation mark.
	 * Defaults to " (an quotation mark).
	 */
	private $attrWrapper = '"';
	/**
	 * @var array available output styles:
	 * nested: output is nested according to the indent level in the source
	 * expanded: block tags have their own lines as does content which is indented
	 * compact: block tags and their content go on one line
	 * compressed: all unneccessary whitepaces is removed. If ugly is true this style is used.
	 */
	private $styles = array('nested', 'expanded', 'compact', 'compressed');
	/**
	 * @var string output style. Note: ugly must be false to allow style.
	 */
	private $style = 'nested';
  /**
	 * @var boolean if true no attempt is made to properly indent or format
	 * the output. Reduces size of output file but is not very readable;
	 * equivalent of style == compressed. Note: ugly must be false to allow style.
	 * Defaults to true.
	 * @see style
	 */
	private $ugly = true;
	/**
	 * @var boolean if true comments are preserved in ugly mode. If not in
	 * ugly mode comments are always output. Defaults to false.
	 */
	private $preserveComments = false;
	/**
	 * @var integer Initial debug setting:
	 * no debug, show source, show output, or show all.
	 * Debug settings can be controlled in the template
	 * Defaults to DEBUG_NONE.
	 */
	private $debug = self::DEBUG_NONE;
	/**
	 * @var string Path to the directory containing user defined filters. If 
	 * specified this dirctory will be searched before PHamlP looks for the filter
	 * in it's collection. This allows the default filters to be overridden and
	 * new filters to be installed. Note: No trailing directory separator.
	 */
	private $filterDir;

	/**
	 * @var array built in Doctypes
	 * @see format
	 * @see doctype
	 */
	private $doctypes = array (
		'html4' => array (
			'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', //HTML 4.01 Transitional
			'Strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD 4.01 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">', //HTML 4.01 Strict
			'Frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD 4.01 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">', //HTML 4.01 Frameset
		),
		'html5' => array (
			'<!DOCTYPE html>', // XHTML 5
		),
		'xhtml' => array (
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">', //XHTML 1.0 Transitional
			'Strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">', //XHTML 1.0 Strict
			'Frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">', //XHTML 1.0 Frameset
			'5' => '<!DOCTYPE html>', // XHTML 5
			'1.1' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">', // XHTML 1.1
			'Basic' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">', //XHTML Basic 1.1
			'Mobile' => '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">', //XHTML Mobile 1.2
		)
	);
	/**
	 * @var array A list of tag names that should be automatically self-closed
	 * if they have no content.
	 */
	private $emptyTags = array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base');
	/**
	 * @var array A list of inline tags.
	 */
	private $inlineTags = array('a', 'abbr', 'accronym', 'b', 'big', 'cite', 'code', 'dfn', 'em', 'i', 'kbd', 'q', 'samp', 'small', 'span', 'strike', 'strong', 'tt', 'u', 'var');
	/**
	 * @var array attributes that are minimised
	 */
	 private $minimizedAttributes = array('compact', 'checked', 'declare', 'readonly', 'disabled', 'selected', 'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize');
	/**
	 * @var array A list of tag names that should automatically have their newlines preserved.
	 */
	private $preserve = array('pre', 'textarea');
	/**#@-*/

	/**
	 * @var string the character used for indenting. Space or tab.
	 * @see indentSpaces
	 */
	private $indentChar;
	/**
	 * @var array allowable characters for indenting
	 */
	private $indentChars = array(' ', "\t");
	/**
	 * @var integer number of spaces for indentation.
	 * Used on source if {@link indentChar} is space.
	 * Used on output if {@link ugly} is false.
	 */
	private $indentSpaces;
	/**
	 * @var array loaded filters
	 */
	private $filters = array();
	/**
	 * @var boolean whether line is in a filter
	 */
	private $inFilter;
	/**
	 * @var boolean whether to show the output in the browser for debug
	 */
	private $showOutput;
	/**
	 * @var boolean whether to show the source in the browser for debug
	 */
	private $showSource;
	/**
	 * @var integer line number of line being parsed
	 */
	private $lineNumber;
	/**
	 * @var string name of file being parsed
	 */
	private $filename;

	/**
	 * HamlParser constructor.
	 * @param array options
	 * @return HamlParser
	 */
	public function __construct($options) {
		foreach ($options as $name => $value) {
			$this->$name = $value;
		} // foreach
		
		if ($this->ugly) {
			$this->style = 'compressed';
		}

		$this->format = strtolower($this->format);
		if (is_null($this->doctype) &&
				!array_key_exists($this->format, $this->doctypes)) {
			$formats = join(', ', array_keys($this->doctypes));
			throw new HamlException("Invalid format ({$this->format}). Format option must be one of {$formats}.");
		}

		$this->showSource = $this->debug & HamlParser::DEBUG_SHOW_SOURCE;
		$this->showOutput = $this->debug & HamlParser::DEBUG_SHOW_OUTPUT;
	}

	/**
	 * Parses a Haml file.
	 * @param string path to file to parse
	 * @return string parsed file
	 */
	public function parse($filename) {
		$this->lineNumber = 0;
		$this->filename = $filename;
		$source = file_get_contents($filename);
		return $this->toTree($source)->render();

	}

	/**
	 * Determine the indent character and indent spaces.
	 * The first character of the first indented line determines the character.
	 * If this is a space the number of spaces determines the indentSpaces; this
	 * is always 1 if the indent character is a tab.
	 * @throws HamlException if the indent is mixed or
	 * the indent character can not be determined
	 */
	private function setIndentChar($lines) {
		foreach ($lines as $line) {
			if (in_array($line[0], $this->indentChars)) {
				$i = 0;
				$this->indentChar = $line[$i];
				while	($line[++$i] == $this->indentChar) {}
				if (in_array($line[$i], $this->indentChars)) {
					throw new HamlException("Mixed indentation not allowed.\nLine $i: {$this->filename}");
				}
				$this->indentSpaces = ($this->indentChar == ' ' ? $i : 1);
				return;
			}
		} // foreach
		throw new HamlException("Unable to determine indent character.\nLine $i: {$this->filename}");
	}

	/**
	 * Parse Haml source into a document tree.
	 * If the tree is already created return that.
	 * @param string Haml source
	 * @return HamlNode the root of this document tree
	 */
	private function toTree($source) {
		$this->setIndentChar(explode("\n", $source));

		preg_match_all(self::REGEX_Haml, $source, $lines, PREG_SET_ORDER);
		$root = new HamlRootNode(array(
			'format' => $this->format,
			'style' => $this->style,
			'attrWrapper' => $this->attrWrapper,
			'minimizedAttributes' => $this->minimizedAttributes
		));
		$this->buildTree($root, $lines);
		return $root;
	}

	/**
	 * Builds a parse tree under the parent node.
	 * @param HamlNode the parent node
	 * @param array remaining source lines
	 */
	private function buildTree($parent, &$lines) {
		while (!empty($lines) && $this->isChildOf($parent, $lines[0])) {
			$line = $this->getLine($lines);
			if (!empty($line)) {
				$node = ($this->inFilter ?
					new HamlNode($line[self::Haml_SOURCE]) :
					$this->parseLine($line, $lines, $parent));

				if (!empty($node)) {
					$node->line = $line;
					$node->showOutput = $this->showOutput;
					$node->showSource = $this->showSource;
					$parent->addChild($node);
					$this->addChildren($node, $line, $lines);
				}
			}
		}
	}

	/**
	 * Adds children to a node if the current line has children.
	 * @param HamlNode the node to add children to
	 * @param array line to test
	 * @param array remaing in source lines
	 */
	private function addChildren($node, $line, &$lines) {
		if ($this->hasChild($line, $lines)) {
			if ($node instanceof HamlFilterNode) {
				$this->inFilter = true;
			}
			$this->buildTree($node, $lines);
			if ($node instanceof HamlFilterNode) {
				$this->inFilter = false;
			}
		}
	}

	/**
	 * Returns a value indicating if the next line is a child of the parent line
	 * @param array parent line
	 * @param array remaing in source lines
	 * @param boolean whether the source line is a comment.
	 * If it is all indented lines are regarded as children; if not the child line
	 * must only be indented by 1 or blank
	 * @return boolean true if the next line is a child of the parent line
	 * @throws Exception if the indent is invalid
	 */
	private function hasChild($line, &$lines, $isComment = false) {
		if (!empty($lines)) {
			$i = 0;
			$c = count($lines);
			while (empty($nextLine[self::Haml_SOURCE]) && $i <= $c) {
				$nextLine = $lines[$i++];
			}

			$indentLevel = $this->getIndentLevel($nextLine, $line['number'] + $i);

			if (($indentLevel == $line['indentLevel'] + 1) ||
					($isComment && $indentLevel > $line['indentLevel'])) {
				return true;
			}
			elseif ($indentLevel <= $line['indentLevel']) {
				return false;
			}
			else {
				throw new HamlException("Illegal indentation level ($indentLevel); indentation level can only increase by one.\nLine " . ($line['number'] + $i) . ": {$this->filename}");
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Returns a value indicating if $line is a child of a node.
	 * A blank line is a child of a node.
	 * @param HamlNode the node
	 * @param array the line to check
	 * @return boolean true if the line is a child of the node, false if not
	 */
	private function isChildOf($node, $line) {
		$haml = trim($line[self::Haml_Haml]);
		return empty($haml) || $this->getIndentLevel($line, $this->lineNumber) >
			$node->indentLevel;
	}

	/**
	 * Gets the next line.
	 * @param array remaining source lines
	 * @return array the next line
	 */
	private function getLine(&$lines) {
		$line = array_shift($lines);
		// Blank lines ore OK
		$haml =  trim($line[self::Haml_Haml]);
		if (empty($haml)) {
			$this->lineNumber++;
			return null;
		}
		// The regex will strip off a '<' at the start of a line
		if (empty($line[self::Haml_TAG])) {
			$line[self::Haml_CONTENT] =
				$line[self::Haml_WHITESPACE_CONTROL].$line[self::Haml_CONTENT];
		}
		$line['number'] = $this->lineNumber++;
		$line['indentLevel'] = $this->getIndentLevel($line, $this->lineNumber);
		$line['file'] = $this->filename;
		return $line;
	}

	/**
	 * Returns the indent level of the line.
	 * @param array the line
	 * @param integer line number
	 * @return integer the indent level of the line
	 * @throws Exception if the indent level is invalid
	 */
	private function getIndentLevel($line, $n) {
		if ($line[self::Haml_INDENT] && $this->indentChar === ' ') {
			$indent = strlen($line[self::Haml_INDENT]) / $this->indentSpaces;
		}
		else {
			$indent = strlen($line[self::Haml_INDENT]);
		}

		if (!is_integer($indent) ||
				preg_match("/[^{$this->indentChar}]/", $line[self::Haml_INDENT])) {
			throw new HamlException("Invalid indentation\nLine " . ++$n . ": {$this->filename}");
		}
		return $indent;
	}

	/**
	 * Parse a line of Haml into a HamlNode for the document tree
	 * @param array line to parse
	 * @param array remaining lines
	 * @return HamlNode
	 */
	private function parseLine($line, &$lines, $parent) {
		if ($this->isHamlComment($line)) {
			return $this->parseHamlComment($line, $lines);
		}
		elseif ($this->isXmlComment($line)) {
			return $this->parseXmlComment($line, $lines);
		}
		elseif ($this->isElement($line)) {
			return $this->parseElement($line, $lines, $parent);
		}
		elseif ($this->isCode($line)) {
			return $this->parseCode($line, $lines, $parent);
		}
		elseif ($this->isDirective($line)) {
			return $this->parseDirective($line);
		}
		elseif ($this->isFilter($line)) {
			return $this->parseFilter($line);
		}
		elseif ($this->isDoctype($line)) {
			return $this->parseDoctype($line);
		}
		else {
			return $this->parseContent($line);
		}
	}

	/**
	 * Return a value indicating if the line has content.
	 * @param array line
	 * @return boolean true if the line has a content, false if not
	 */
	private function hasContent($line) {
	  return !empty($line[self::Haml_CONTENT]);
	}

	/**
	 * Return a value indicating if the line is code to be run.
	 * @param array line
	 * @return boolean true if the line is code to be run, false if not
	 */
	private function isCode($line) {
		return $line[self::Haml_TOKEN] === self::RUN_CODE;
	}

	/**
	 * Return a value indicating if the line is a directive.
	 * @param array line
	 * @return boolean true if the line is a directive, false if not
	 */
	private function isDirective($line) {
		return $line[self::Haml_TOKEN] === self::DIRECTIVE;
	}

	/**
	 * Return a value indicating if the line is a doctype.
	 * @param array line
	 * @return boolean true if the line is a doctype, false if not
	 */
	private function isDoctype($line) {
		return $line[self::Haml_TOKEN] === self::DOCTYPE;
	}

	/**
	 * Return a value indicating if the line is an element.
	 * Will set the tag to div if it is an implied div.
	 * @param array line
	 * @return boolean true if the line is an element, false if not
	 */
	private function isElement(&$line) {
		if (empty($line[self::Haml_TAG]) && (
				!empty($line[self::Haml_CLASS]) ||
				!empty($line[self::Haml_ID]) ||
				!empty($line[self::Haml_XML_ATTRIBUTES]) ||
				!empty($line[self::Haml_RUBY_ATTRIBUTES]) ||
				!empty($line[self::Haml_OBJECT_REFERENCE])
		)) {
			$line[self::Haml_TAG] = 'div';
		}

	  return !empty($line[self::Haml_TAG]);
	}

	/**
	 * Return a value indicating if the line starts a filter.
	 * @param array line to test
	 * @return boolean true if the line starts a filter, false if not
	 */
	private function isFilter($line) {
	  return !empty($line[self::Haml_FILTER]);
	}

	/**
	 * Return a value indicating if the line is a Haml comment.
	 * @param array line to test
	 * @return boolean true if the line is a Haml comment, false if not
	 */
	private function isHamlComment($line) {
		return preg_match(self::Haml_COMMENT, $line[self::Haml_TOKEN]) > 0;
	}

	/**
	 * Return a value indicating if the line is an XML comment.
	 * @param array line to test
	 * @return boolean true if theline is an XML comment, false if not
	 */
	private function isXmlComment($line) {
	  return $line[self::Haml_SOURCE][0] === self::XML_COMMENT;
	}

	/**
	 * Returns a value indicating whether the line is part of a multilne group
	 * @param array the line to test
	 * @return boolean true if the line os part of a multiline group, false if not
	 */
	private function isMultiline($line) {
	  return isset($line[self::Haml_MULTILINE]);
	}

	/**
	 * Return a value indicating if the line's tag is a block level tag.
	 * @param array line
	 * @return boolean true if the line's tag is is a block level tag, false if not
	 */
	private function isBlock($line) {
	  return (!in_array($line[self::Haml_TAG], $this->inlineTags));
	}

	/**
	 * Return a value indicating if the line's tag is self-closing.
	 * @param array line
	 * @return boolean true if the line's tag is self-closing, false if not
	 */
	private function isSelfClosing($line) {
	  return (in_array($line[self::Haml_TAG], $this->emptyTags) ||
	  	$line[self::Haml_TOKEN] == self::SELF_CLOSE_TAG);
	}

	/**
	 * Gets a filter.
	 * Filters are loaded on first use.
	 * @param string filter name
	 * @throws HamlException if the filter does not exist or does not extend HamlBaseFilter
	 */
	private function getFilter($filter) {
		static $firstRun = true;
		$imported = false;
		
		if (empty($this->filters[$filter])) {
			if ($firstRun) {
				require_once('filters/HamlBaseFilter.php');
				$firstRun = false;
			}

			$filterclass = 'Haml' . ucfirst($filter) . 'Filter';
			if (isset($this->filterDir)) {
				$this->filterDir = (substr($this->filterDir, -1) == DIRECTORY_SEPARATOR?
						substr($this->filterDir, 0, -1):$this->filterDir);
				if (file_exists($this->filterDir.DIRECTORY_SEPARATOR."$filterclass.php")) {
					require_once($this->filterDir.DIRECTORY_SEPARATOR."$filterclass.php");
					$imported = true; 
				}
			}

			if (!$imported && file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'filters'.DIRECTORY_SEPARATOR."$filterclass.php")) {
				require_once("filters/$filterclass.php");
				$imported = true; 
			}
			
			if (!$imported) {
				throw new HamlException("Unable to find $filter filter - $filterclass.php.");
			}
			
			$this->filters[$filter] = new $filterclass();

			if (!($this->filters[$filter] instanceof HamlBaseFilter)) {
				throw new HamlException("Invalid filter ($filter). Haml filters must extend HamlBaseFilter.");
			}

			$this->filters[$filter]->init();
		}
		return $this->filters[$filter];
	}

	/**
	 * Parse attributes.
	 * @param array line to parse
	 * @param array remaining lines
	 * @return array attributes in name=>value pairs
	 */
	private function parseAttributes($line, &$lines) {
		$attributes = array();
		if (!empty($line[self::Haml_XML_ATTRIBUTES])) {
			$attributes = array_merge(
					$attributes,
					$this->parseAttributeHash($line[self::Haml_XML_ATTRIBUTES])
			);
		}
		if (!empty($line[self::Haml_RUBY_ATTRIBUTES])) {
			$attributes = array_merge(
					$attributes,
					$this->parseAttributeHash($line[self::Haml_RUBY_ATTRIBUTES])
			);
		}
		if (!empty($line[self::Haml_OBJECT_REFERENCE])) {
			$objectRef = explode(',', str_replace(', ', ', ', $line[self::Haml_OBJECT_REFERENCE]));
			$prefix = (isset($objectRef[1]) ? $objectRef[1] . '_' : '');
			$class = "strtolower(str_replace(' ',	'_', preg_replace('/(?<=\w)([ A-Z])/', '_\1', get_class(" . $objectRef[0] . '))))';
			$attributes['class'] = "<?php echo '$prefix' . $class; ?>";
			$attributes['id'] = "<?php echo '$prefix' . $class . '_' . {$objectRef[0]}->id; ?>";
		}
		else {
			if (!empty($line[self::Haml_CLASS])) {
				$attributes['class'] = str_replace('.', ' ', $line[self::Haml_CLASS]);
			}
			if (!empty($line[self::Haml_ID])) {
				$attributes['id'] = $line[self::Haml_ID];
			}
		}

	  return $attributes;
	}

	/**
	 * Parse attributes.
	 * @param string the attributes
	 * @return array attributes in name=>value pairs
	 */
	private function parseAttributeHash($subject) {
		$subject = substr($subject, 0, -1);
 		$attributes = array();
		if (preg_match(self::REGEX_ATTRIBUTE_FUNCTION, $subject)) {
			$attributes[0] = "<?php echo $subject; ?>";
			return $attributes;
		}
		preg_match_all(self::REGEX_ATTRIBUTES, $subject, $attrs, PREG_SET_ORDER);
		foreach ($attrs as $attr) {
			if (empty($attr[2])) {
				switch ($attr[4]) {
					case 'true':
						$attributes[$attr[1]] = $attr[1];
						break;
					case 'false':
						break;
					default:
						$attributes[$attr[1]] = "<?php echo {$attr[4]}; ?>";
						break;
				}
			}
			else {
				$attributes[$attr[1]] = preg_replace(self::MATCH_INTERPOLATION, '<?php echo \1; ?>', $attr[3]);
			}
		} // foreach
		return $attributes;
	}

	/**
	 * Parse code
	 * @param array line to parse
	 * @return HamlNode
	 */
	private function parseCode($line, &$lines, $parent) {
		if (preg_match('/^(if|foreach|for|switch|do|while)\b(.*)$/',
				$line[self::Haml_CONTENT], $block)) {
			if ($block[1] === 'do') {
				$node = new HamlCodeBlockNode('<?php do { ?>');
				$node->doWhile = 'while' . $block[2] . ';';
			}
			else {
				$node = new HamlCodeBlockNode("<?php {$line[self::Haml_CONTENT]} { ?>");
			}
		}
		elseif (strpos($line[self::Haml_CONTENT], 'else') === 0) {
			$node = new HamlCodeBlockNode("{$line[self::Haml_CONTENT]} { ?>");
			$node->line = $line;
			$node->showOutput = $this->showOutput;
			$node->showSource = $this->showSource;
			$parent->getLastChild()->addElse($node);
			$this->addChildren($node, $line, $lines);
			$node = null;
		}
		elseif (strpos($line[self::Haml_CONTENT], 'case') === 0) {
			$node = new HamlNode("{$line[self::Haml_CONTENT]}:");
		}
		else {
			$node = new HamlNode("<?php {$line[self::Haml_CONTENT]}; ?>");
		}
		return $node;
	}

	/**
	 * Parse content
	 * @param array line to parse
	 * @return HamlNode
	 */
	private function parseContent($line) {
		switch ($line[self::Haml_TOKEN]) {
		  case self::INSERT_CODE:
		  	$content = ($this->suppressEval ? '' :
						'<?php echo ' . ($this->escapeHtml ?
						'htmlentities(' . $line[self::Haml_CONTENT] . ')' :
						$line[self::Haml_CONTENT]) .
						"; ?>" .
						($this->style == HamlRenderer::STYLE_EXPANDED ||
							$this->style == HamlRenderer::STYLE_NESTED ? "\n" : ''));
		    break;
		  case self::INSERT_CODE_PRESERVE_WHITESPACE:
				$content = ($this->suppressEval ? '' :
						'<?php echo str_replace("\n", \'&#x000a\', ' . ($this->escapeHtml ?
						'htmlentities(' . $line[self::Haml_CONTENT] . ')' :
						$line[self::Haml_CONTENT]) .
						"; ?>" .
						($this->style == HamlRenderer::STYLE_EXPANDED ||
							$this->style == HamlRenderer::STYLE_NESTED ? "\n" : ''));
		    break;
		  default:
		  	$content = $line[self::Haml_CONTENT];
		    break;
		} // switch

	  return new HamlNode(
	  	preg_replace(self::MATCH_INTERPOLATION, self::INTERPOLATE, $content)
	  );
	}

	/**
	 * Parse a directive.
	 * Various options are set according to the directive
	 * @param array line to parse
	 * @return null
	 */
	private function parseDirective($line) {
		preg_match('/(\w+)(\+|-)?/', $line[self::Haml_CONTENT], $matches);
		switch ($matches[1]) {
		  case 's':
		  	$this->showSource = ($matches[2] == '+' ? true :
		  		($matches[2] == '-' ? false : $this->showSource));
		    break;
		  case 'o':
		  	$this->showOutput = ($matches[2] == '+' ? true :
		  		($matches[2] == '-' ? false : $this->showOutput));
		    break;
		  case 'os':
		  case 'so':
		  	$this->showSource = ($matches[2] == '+' ? true :
		  		($matches[2] == '-' ? false : $this->showSource));
		  	$this->showOutput = ($matches[2] == '+' ? true :
		  		($matches[2] == '-' ? false : $this->showOutput));
		    break;
		  default:
		  	if (!in_array($matches[1], $this->styles)) {
					throw new HamlException('Invalid directive (' . self::DIRECTIVE . "{$matches[0]})\nLine {$line['number']}: {$this->filename}");
		  	}
		  	$this->style = $matches[1];
		    break;
		} // switch
	}

	/**
	 * Parse a doctype declaration
	 * @param array line to parse
	 * @return HamlDoctypeNode
	 */
	private function parseDoctype($line) {
		$content = explode(' ', $line[self::Haml_CONTENT]);
		if (!empty($content)) {
			if ($content[0] === self::IS_XML_PROLOG) {
				$encoding = isset($content[1]) ? $content[1] : self::DEFAULT_XML_ENCODING;
				$output = str_replace(self::XML_ENCODING, $encoding, self::XML_PROLOG);
			}
			elseif (empty($content[0])) {
				$output = $this->doctypes[$this->format][0];
			}
			elseif (array_key_exists($content[0],
					$this->doctypes[$this->format])) {
				$output = $this->doctypes[$this->format][$content[0]];
			}
			elseif (!empty($this->doctype)) {
				$output = $this->doctype;				
			}
			else {
				$_doctypes = array_keys($this->doctypes[$this->format]);
				array_shift($_doctypes);
				$doctypes = join(', ', $_doctypes);
				throw new HamlException("Invalid doctype ({$content[0]}). Doctype must be empty or one of $doctypes for the current format ({$this->format}).");
			}
		}
		return new HamlDoctypeNode($output);
	}

	/**
	 * Parse a Haml comment.
	 * If the comment is an empty comment eat all child lines.
	 * @param array line to parse
	 * @param array remaining lines
	 */
	private function parseHamlComment($line, &$lines) {
		if (!$this->hasContent($line)) {
			while ($this->hasChild($line, $lines, true)) {
				array_shift($lines);
				$this->lineNumber++;
			}
		}
	}

	/**
	 * Parse an element.
	 * @param array line to parse
	 * @param array remaining lines
	 * @return HamlNode tag node and children
	 */
	private function parseElement($line, &$lines, $parent) {
		$node = new HamlElementNode($line[self::Haml_TAG]);
		$node->isSelfClosing = $this->isSelfClosing($line);
		$node->isBlock = $this->isBlock($line);
		$node->attributes = $this->parseAttributes($line, $lines);
		if ($this->hasContent($line)) {
			$child = $this->parseContent($line);
			$child->showOutput = $this->showOutput;
			$child->showSource = $this->showSource;
			$child->line = array(
				'indentLevel' => ($line['indentLevel'] + 1),
				'number' => $line['number']
			);
			$node->addChild($child, $parent);
		}
		$node->whitespaceControl = $this->parseWhitespaceControl($line);
	  return $node;
	}

	/**
	 * Parse a filter.
	 * @param array line to parse
	 * @param array remaining lines
	 * @return HamlNode tag node and children
	 */
	private function parseFilter($line) {
		$node = new HamlFilterNode($this->getFilter($line[self::Haml_FILTER]));
		if ($this->hasContent($line)) {
			$child = $this->parseContent($line);
			$child->showOutput = $this->showOutput;
			$child->showSource = $this->showSource;
			$child->line = array(
				'indentLevel' => ($line['indentLevel'] + 1),
				'number' => $line['number']
			);
			$node->addChild($child, $parent);
		}
	  return $node;
	}

	/**
	 * Parse an Xml comment.
	 * @param array line to parse
	 * @param array remaining lines
	 */
	private function parseXmlComment($line, &$lines) {
		return new HamlCommentNode($line[self::Haml_CONTENT]);
	}

	private function parseWhitespaceControl($line) {
		$whitespaceControl = array('inner' => false, 'outer' => false);

		if (!empty($line[self::Haml_WHITESPACE_CONTROL])) {
			if (strpos($line[self::Haml_WHITESPACE_CONTROL], self::REMOVE_INNER_WHITESPACE) !== false) {
				$whitespaceControl['inner'] = true;
			}
			if (strpos($line[self::Haml_WHITESPACE_CONTROL], self::REMOVE_OUTER_WHITESPACE) !== false) {
				$whitespaceControl['outer'] = true;
			}
		}
	  return $whitespaceControl;
	}

	/**
	 * Replace interpolated PHP contained in '#{}'.
	 * @param string the text to interpolate
	 * @return string the interpolated text
	 */
	protected function interpolate($string) {
		for ($i = 0, $n = preg_match_all(self::MATCH_INTERPOLATION, $string, $matches);
				$i < $n; $i++) {
			$matches[1][$i] = $this->evaluate($matches[1][$i], $context);
		}
	  return str_replace($matches[0], $matches[1], $string);
	}
}