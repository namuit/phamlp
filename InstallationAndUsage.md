# Introduction #

PHamlP is a PHP port of Haml (http://haml-lang.com/) and Sass (http://sass-lang.com/).

phamlp\_2.2\_r is the equivalent of the Haml/Sass V2.2 (Powerful Penny).

Requires PHP5.2 or later.

# Installation #

Download and unzip the files.

# Useage #
Include (or import if using a framework) HamlParser.php and/or SassParser.php

## Haml ##
Instantiate a HamlParser object with the options you require, then call the parse method with the path to the Haml file to parse. The parsed content is returned as a string.

```
<?php
  $haml = new HamlParser(array(<haml parser options>));
  $xhtml = $haml->parse('path/to/haml/sourcefile.haml');
?>
```

## Sass ##
Instantiate a SassParser object with the options you require, then call the "toCss" method with the path to the sass file to parse. The parsed content is returned as a string.

```
<?php
  $sass = new SassParser(array(<sass parser options>));
  $css = $sass->toCss('path/to/sass/sourcefile.sass');
?>
```

### Indentation ###
Both Haml and Sass are whitespace sensitive, i.e. indentation has meaning. In PHamlP spaces or tabs can be used as the indentation character and which is used is automatically detected on a per file basis; the first indented line determines the indentation character for that file; you can not mix characters in the same file.

If spaces are being used, the number of spaces at the start of the first indented line determine the number of spaces used for indentation. e.g. if the first indented line starts with 2 spaces valid indentations are 2 spaces, 4 spaces, 6 spaces, etc.; if it is 3 spaces the valid indentations are 3 spaces, 6 spaces, 9 spaces, etc.

If a tab is used each tab character in a line is an indentation level.