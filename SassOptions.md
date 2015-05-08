# Introduction #

This page describes the options used with Sass.

Options are passed to the Sass parser as name=>value pairs at instantiation; e.g.
```
  $sass = new SassParser(array('style'=>'nested'));
```


# Details #
**cache** (boolean) Whether parsed Sass files should be cached, allowing greater speed.<br />
Default = true

**cache\_location** (string) The path where the cached .sassc files should be written to.<br />
Default = './sass-cache'

**css\_location** (string) The path where CSS output should be written to.<br />
Default = './css'

**debug\_info** (boolean) When true the line number and file where a selector is defined is emitted into the compiled CSS in a format that can be understood by the [FireSass extension](https://addons.mozilla.org/en-US/firefox/addon/103988/FireSass).
Disabled when using the compressed output style.<br />
Default = false

**filename** (string) The filename of the file being rendered.<br />
Set automatically when rendering a .sass or .scss file, but is useful to set if the Sass template is embedded.

**line** (integer) The number of the first line of the Sass template. Used for reporting line numbers for errors.<br />
Set automatically when rendering a .sass or .scss file, but is useful to set if the Sass template is embedded.

**line\_numbers** (boolean) When true the line number and filename where a selector is defined is emitted into the compiled CSS as a comment.<br />
Useful for debugging especially when using imports and mixins.<br />
Disabled when using the compressed output style or the debug\_info option.<br />
Default = false

**load\_paths** (array) An array of filesystem paths which should be searched for Sass templates imported with the @import directive.<br />
Default = './sass-templates'

**property\_syntax** (string) Forces the document to use one syntax for properties. If the correct syntax isn't used, an error is thrown.<br />
Value can be:
  * new - forces the use of a colon or equals sign after the property name. For example	 color: #0f3 or width: $main\_width.
  * old -  forces the use of a colon before the property name. For example: :color #0f3 or :width = $main\_width.<br />
Default: either syntax is valid.<br />
Ignored for SCSS files.

**quiet** (boolean) When set to true, causes warnings to be disabled.<br />
Default = false.

**style** (string) the style of the CSS output.<br />
Value can be:
  * nested - Nested is the default Sass style, because it reflects the structure of the document in much the same way Sass does. Each selector and rule has its own line with indentation is based on how deeply the rule is nested. Nested style is very useful when looking at large CSS files as it allows you to very easily grasp the structure of the file without actually reading anything.
  * expanded - Expanded is the typical human-made CSS style, with each selector and property taking up one line. Selectors are not indented; properties are indented within the rules.
  * compact - Each CSS rule takes up only one line, with every property defined on that line. Nested rules are placed with each other while groups of rules are separated by a blank line.
  * compressed - Compressed has no whitespace except that necessary to separate selectors and properties. It's not meant to be human-readable.<br />
Default = 'nested'

**syntax** (string) The syntax of the input file. 'sass' for the indented syntax and 'scss' for the CSS-extension syntax.<br />
Set automatically when parsing a file, else defaults to 'sass'.

**template\_location** (string) Path to the root sass template directory for your application.

**vendor\_properties** (mixed) If enabled a property need only be written in the standard form and vendor specific versions will be added to the style sheet.<br />
Values can be:
  * array: vendor properties, merged with the built-in vendor properties, to automatically apply.
  * boolean: true = use built in vendor properties.
  * empty: disabled
Default = disabled.<br />
_Note:_ The same result can be obtained using a mixin.


## Default Vendor Properties ##
This is the default vendor property array. To specify your own properties use the same format: 'standard-property'=>array(vendor1-version, vendor2-version, ..., vendorN-version),
```
  private $_vendorProperties = array(
    'border-radius' => array(
      '-moz-border-radius',
      '-webkit-border-radius',
      '-khtml-border-radius'
    ),
    'border-top-right-radius' => array(
      '-moz-border-radius-topright',
      '-webkit-border-top-right-radius',
      '-khtml-border-top-right-radius'
    ),
    'border-bottom-right-radius' => array(
      '-moz-border-radius-bottomright', 
      '-webkit-border-bottom-right-radius',
      '-khtml-border-bottom-right-radius'
    ),
    'border-bottom-left-radius' => array(
      '-moz-border-radius-bottomleft',
      '-webkit-border-bottom-left-radius',
      '-khtml-border-bottom-left-radius'
    ),
    'border-top-left-radius' => array(
      '-moz-border-radius-topleft',
      '-webkit-border-top-left-radius',
      '-khtml-border-top-left-radius'
    ),
    'box-shadow' => array('-moz-box-shadow', '-webkit-box-shadow'),
    'box-sizing' => array('-moz-box-sizing', '-webkit-box-sizing'),
    'opacity' => array('-moz-opacity', '-webkit-opacity', '-khtml-opacity'),
  );
```