Also see UserDefinedFilters

# Introduction #
Filters provide a means to extend the functionality of Haml. They allows you to pass an indented block of text as input to another filtering program and add the result to the output of Haml.

PHamlP has a rich set of predefined filters that work "_out of the box_" and abstract filters that require external classes; both are described below.

To use a filter in a Haml template the syntax is a colon followed by the name of the filter. For example - to use the Markdown filter:
```
%p
  :markdown
    Textile
    =======

    Hello, *World*
```

PHP interpolation using #() is supported in the predefined filters. For example:
```
- $flavour = "strawberry"
#content
  :textile
    I *really* prefer _#{$flavour}_ jam.
```


# Predefined Filters #

PHamlP has the following predefined filters that work "out of the box":

**:plain** Does not parse the filtered text. This is useful for large blocks of text without HTML tags, when you don’t want lines starting with . or - to be parsed.

**:javascript** Surrounds the filtered text with `<script>` and CDATA tags. Useful for including inline Javascript.

**:cdata** Surrounds the filtered text with CDATA tags.

**:escaped** Works the same as plain, but HTML-escapes the text before placing it in the document.

**:php** Parses the filtered text with the PHP interpreter. The PHP code is evaluated in the same context as the Haml template.

**:preserve** Inserts the filtered text into the template with whitespace preserved. preserved blocks of text aren’t indented, and newlines are replaced with the HTML escape code for newlines, to preserve nice-looking output.

**:sass** Parses the filtered text with Sass to produce CSS output.

## Abstract filters ##
These filters require external - vendor - classes to operate. PHamlP provides abstract base classes that must be extended in order to provide the required external class.

Extended classes must implement the "init()" function. This must provide the path to the vendor class file (unless already imported by a framework) and the name of the vendor class (unless the default is correct).
```
  /**
   * Initialise the filter
   */
  public function init() {
    $this->vendorClass = 'vendorClass';
    $this->vendorPath = 'path/to/vendor/class/file.php';		
    parent::init();
  }
```

Abstract base classes are named "`_`Haml`<FilterName>`Filter", e.g. "`_`HamlTextileFilter". The extended class must be named "Haml`<FilterName>`Filter", e.g. "HamlTextileFilter" and placed in the  _filterDir_ directory (see HamlOptions).

**:markdown** Parses the filtered text with Markdown.<br />
Default vendorClass = Markdown\_Parser

**:textile** Parses the filtered text with Textile.<br />
Default vendorClass = Textile