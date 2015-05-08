# Haml Options #

## Introduction ##
This page describes the options used with Haml.

Options are passed to the Haml parser as name=>value pairs at instantiation; e.g.
```
  $haml = new HamlParser(array('style'=>'nested', 'ugly'=>false));
```

## Options ##
**attrWrapper** (string) The character that should wrap element attributes. Characters of this type within attributes will be escaped (e.g. by replacing them with &apos;) if the character is an apostrophe or a quotation mark.<br />
Default = '"' (a quotation mark).

**debug** (integer) Initial debug setting:<br />
HamlParser::DEBUG\_NONE: no debug<br />
HamlParser::DEBUG\_SHOW\_SOURCE: show source in the output<br />
HamlParser::DEBUG\_SHOW\_OUTPUT: show output in the browser<br />
HamlParser::DEBUG\_SHOW\_ALL: show output and debug in the browser<br />
Default = HamlParser::DEBUG\_NONE.<br />

**doctype** (string) Custom Doctype. If not null and the Doctype declaration in the Haml Document is not a [built in Doctype](http://haml-lang.com/docs/yardoc/file.HAML_REFERENCE.html#doctype_) this will be used as the Doctype. This allows Haml to be used for non-(X)HTML documents that are XML compliant.<br />
Default = null.<br />
See emptyTags, inlineTags, minimizedAttributes

**emptyTags** (array) A list of tag names that should be automatically self-closed
if they have no content.<br />
Default = array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base')

**escapeHtml** (boolean) Whether or not to escape X(HT)ML-sensitive characters in script. If this is true, = behaves like &=; otherwise, it behaves like !=. Note that if this is set, != should be used for yielding to subtemplates and rendering partials.<br />
Default = false.

**filterDir** (string) Path to the directory containing user defined filters. If specified this directory will be searched before PHamlP looks for the filter in it's collection. This allows the default filters to be overridden and new filters to be installed.<br />
Note: No trailing directory separator.

**format** (string) Doctype format.  Determines how the Doctype is rendered using a built in Doctype.<br />
Built in Doctypes are:

**_format == xhtml_**
| **Haml** | **Doctype** | **DOCTYPE** |
|:---------|:------------|:------------|
| !!! | XHTML 1.0 Transitional | `<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">` |
| !!! Strict | XHTML 1.0 Strict | `<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">` |
| !!! Frameset | XHTML 1.0 Frameset | `<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">` |
| !!! 5 | XHTML 5 | `<!DOCTYPE html>` |
| !!! 1.1 | XHTML 1.1 | `<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">` |
| !!! Basic | XHTML Basic 1.1 | `<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">` |
| !!! Mobile | XHTML Mobile 1.2 | `<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">` |

**_format == html4_**
| **Haml** | **Doctype** | **DOCTYPE** |
|:---------|:------------|:------------|
| !!! | HTML 4.01 Transitional | `<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">` |
| !!! Strict | HTML 4.01 Strict | `<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">` |
| !!! Frameset | HTML 4.01 Frameset | `<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">` |

**_format == html5_**
| **Haml** | **Doctype** | **DOCTYPE** |
|:---------|:------------|:------------|
| !!! | XHTML 5 | `<!DOCTYPE html>` |

See doctype<br />
Default = 'xhtml'.

**inlineTags** (array) A list of inline tags.<br /><br />
Default = array('a', 'abbr', 'accronym', 'b', 'big', 'cite', 'code', 'dfn', 'em', 'i', 'kbd', 'q', 'samp', 'small', 'span', 'strike', 'strong', 'tt', 'u', 'var')

**minimizedAttributes** (array) A list of attributes that are minimised.
Default = array('compact', 'checked', 'declare', 'readonly', 'disabled', 'selected', 'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize')

**preserve** (array) A list of tag names that should automatically have their newlines preserved.<br />
Default = array('pre', 'textarea')

**preserveComments** (boolean) If true comments are preserved in ugly mode. If not in ugly mode comments are always output.<br />
Defaults = false.

**style** (string) Output style.<br />
Available output styles:<br />
_nested_: output is nested according to the indent level in the source<br />
_expanded_: block tags have their own lines as does content which is indented<br />
_compact_: block tags and their content go on one line<br />
_compressed_: all unnecessary whitepace is removed. If ugly is true this style is used.<br />
Default = 'nested'.<br />
Note: ugly must be false to allow style.<br />
See ugly.

**suppressEval** (boolean) Whether or not attribute hashes and scripts designated by = or ~ should be evaluated. If true, the scripts are rendered as empty strings.<br />
Default = false.

**ugly** (boolean) If true no attempt is made to properly indent or format the output. Reduces size of output file but is not very readable; equivalent of style == compressed.<br />
Note: ugly must be false to allow style.<br />
Default = true.<br />
See style.

**helperFile** (string) Path to a file containing a class with user defined helper methods.
The filename is also the classname. The class must extend HamlHelpers. e.g.<br />
helperFile == /path/to/MyHamlHelpers.php
```
<?php
class MyHamlHelpers extends HamlHelpers {
  public static function myHelperMethod($block, $a0, $a1) {
    return <do something to block with $a0 and $a1>;
  }
}
?>
```