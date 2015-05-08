# Introduction #

PHamlP adds "directives" to standard Haml to help with debugging. These allow you to include the Haml source as comments in the output, and to display output (i.e. `<p`>Text`</p`> is shown) in the browser - with or without the source.

These can be used anywhere within your template to narrow in on a particular section.

# Details #
Directives start with the characters '?#' followed by the directive.

Directives must be on their own line.

The debugging directives are:

**?#s+** Turn on source output. Source is included as comments in the output.

**?#s-** Turn off source output. Source is not be included in the output.

**?#o+** Turn on output display. Output is displayed in the browser.

**?#o-** Turn off output display. Output is the generated (X)HTML.

**?#os+** or **?#so+** Turn on source output and output display. Source is included as comments in the output and output is displayed in the browser.

**?#os-** or **?#so-** Turn off source output and output display. Source is not included as comments in the output and output is the generated (X)HTML.

