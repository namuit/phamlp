# Introduction #

PHamlP/Haml adds a few extras to Haml.

These
  * Give additional control over outer whitespace removal (HamlEnhancements #Outer\_Whitespace\_Removal)
  * Add debugging directives (HamlEnhancements #Debugging\_Directives)

## Outer Whitespace Removal ##
PHamlP adds modifiers to the standard outer whitespace control; |> and >|.

**|>** removes outer whitespace but not from before the tag - i.e. only whitespace after the tag.

**>|** removes outer whitespace but not from after the tag - i.e. only whitespace before the tag.

**>** on its own works just the same as before - i.e. removes all whitespace surrounding the tag.

Consider these this example:
```
(
%strong
  Strong text
other text:
%a(href="url1")
  Link1
,
%a(href="url2)
  Link2
)
```

What you probably want here is:(**Strong text** other text: Link1, Link2).

If no outer whitespace removal is used this renders as ( **Strong text** other text: Link1 , Link2 ), and using outer whitespace removal on the tags renders as (<b>Strong text</b>other text:Link1,Link2); neither what was wanted.

Using the modifiers the above example becomes:
```
(
%strong>|
  Strong text
%a(href="url1")|>
  Link1
,
%a(href="url2)|>
  Link2
)
```
which renders as (**Strong text** other text: Link1, Link2); just what was wanted.

This is not the only way to do this BTW. If you'd rather not have a single commas on its own line the alternative is to use inline HTML and use Haml for the layout. There is an interesting article on the use of Haml for content [here](http://chriseppstein.github.com/blog/2010/02/08/haml-sucks-for-content/).

## Debugging Directives ##
PHamlP/Haml adds debugging directives to help you with development. Debugging directives start with **?#**, followed by either **s**, **o** or both, and end with either **+** to turn the directive on, **-** to turn it off, or **!** to toggle it. The default for all directives is **off**.

The directives can be used anywhere within the source file and are placed at the same indent level as the Haml you want to start or stop debugging. e.g.
```
.not_debugged
  ?#s+
  %p
    Debug this
  ?#-
```

### Output Comments Containing Haml Source ###
**?#s** controls the output of comments containing the Haml source before each output line.

**?#s+** = turn on comments containing Haml source

**?#s-** = turn off comments containing Haml source

**?#s!** = toggle comments containing Haml source

Comments are of the form:
```
<!--
  Path_to_source_file line_number:indent_level Haml_source
-->
```

### Show Output as Code in the Browser ###
**?#o** controls output whether the (X)HTML output is rendered as a normal page or shown as code in the browser (the tags are escaped).

**?#o+** = turn on show output as code

**?#o-** = turn off show output as code

**?#o!** = toggle show output as code

You can also use:
**?#so+**

**?#so-**

**?#so!**