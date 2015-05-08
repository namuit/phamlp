Also see PredefinedFilters

# Introduction #
Filters provide a means to extend the functionality of Haml. They allows you to pass an indented block of text as input to another filtering program and add the result to the output of Haml.

This page describes how to write your own filter for use with PHamlP.

# Details #
Your filter class must be named "Haml`<FilterName`>Filter", where `<FilterName`> = ucfirst(filterName). So if the name of your filter is "refine" your filter class must be named "HamlRefineFilter".

The file containing you filter must be named "Haml`<FilterName`>Filter.php" and be placed in the filterDir directory.

Your filter class must extend "HamlBaseFilter" or a child class .

Your filter class must implement the "run($text)" method or may override it if defined in a child class you are extending.<br />
$text is the input text.<br />
This method must return a string that is the filtered text.

Your filter class may implement the "init()" method to provide initialisation of the filter. This method should call the parent init() method.

It is a good idea to provide PHP interpolation #{PHP code} in your filter. See the example below and the built in filters for examples of how this is done.

## Usage ##
Using your filter is the same as using a built in filter, i.e. a colon followed by the name of the filter.

So to use the "refine" filter:
```
:refine
  Text to refine
```

## An Example Filter ##
```
<?php
include 'pathToPHamlP'.DIRECTORY_SEPARATOR.'haml'.DIRECTORY_SEPARATOR.'filters'.DIRECTORY_SEPARATOR.'HamlBaseFilter.php';
include 'pathToRefiner'.DIRECTORY_SEPARATOR.'refinerClass.php';

class HamlRefineFilter extends HamlBaseFilter {
  /**
   * The refiner
   */
  private $refiner;

  /**
   * Initialise the filter
   */
  public init() {
    $this->refiner = new Refiner();
  }

  /**
   * Run the filter
   * @param string text to filter
   * @return string filtered text
   */
  public function run($text) {
    return '<?php echo $this->refiner->refine("'.preg_replace(HamlParser::MATCH_INTERPOLATION, '".\1."', $text).'");?>';
  }
}
```