# Introduction #

While PHamlP/Haml is pretty straight forward there are a few conventions to bear in mind.

# Conventions #

## Class and ID ##

Class must always be defined before ID when both are used. e.g.
```
.div-class#div-id
  span.span-class#span-id
    Span content
```

The way I remember this is to go from the least specific to the most specific; tag->class->id

## Attributes ##
The conventions/rules for attributes apply to both XML and Ruby style attributes.

Attributes must be declared as name::value pairs - even for minimized attributes. e.g.
```
%option(value="Value" selected="selected")
```

Attribute values must be enclosed in double quotes (") and any code to be evaluated enclosed in #{}. e.g.
```
%a{:href=>"#{$var1.($var2 ? 'this' : 'that').'.html'}"}
```