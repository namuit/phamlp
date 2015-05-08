#Change Log.

# 3.2 #
  * Inclusion of Compass as an extension to Sass
  * Fixed SassScript parsing in SCSS files
  * HamlHelpers can now accept arrays as arguments

# 3.1 #
  * Added support for SassNumbers with complex units
  * @import directive can import multiple files
  * @import now looks for files with the current syntax, then the other syntax, if the extension is not given. e.g. @import foo; in a .scss file will look for foo.scss first, then foo.sass; in a .sass file it will look for foo.sass first, then foo.scss

# 3.0 #
  * Added Sass V3 support; SCSS syntax (and to re-iterate the the Sass team's message, the SASS syntax is **not** deprecated), new variable and assignment syntax, @extend directive, etc.
  * FireSass integration.