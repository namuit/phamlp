# PHamlP #

Haml and Sass have been used in Ruby for sometime to simplify templates (Haml) and make CSS more intelligent, flexible and manageable (Sass); now they come to PHP in PHamlP.

**Features**
  * Framework independent. PHamlP can be used with any framework (wrapper functions are required to integrate with frameworks; an example for [Yii](http://www.yiiframework.com/) is provided - I'd welcome contributions for other frameworks) or standalone.
  * Indentation auto-detect. PHamlP allows use of spaces or tab as the indentation character and automatically detects which on a per file basis; and, if spaces, how many.
  * Rendering options to support readability for development and minimised whitespace for production.
  * Haml & Sass V3.x compliant (.sass and .scss SASS syntax support, [FireSass](https://addons.mozilla.org/en-US/firefox/addon/103988/FireSass) integration, new variable syntax, @extend directive)
  * Includes [Compass](http://compass-style.org/) (PHamlP V3.2 and greater).


---

## Haml ##
Haml is based on one primary principle. Markup should be beautiful.

Haml is a markup language that’s used to cleanly and simply describe the XHTML of any web document, without the use of inline code. It avoids the need for explicitly coding XHTML into the template, because it is actually an abstract description of the XHTML, with some code to generate dynamic content.

See http://haml-lang.com for details on Haml

PHamlP comes with a rich set of [predefined filters](PredefinedFilters.md) including _JavaScript_, _CSS_, _Sass_, _PHP_, and more; it can easily be extended by [writing your own](UserDefinedFilters.md).

PHamlP also has a set of [helper methods](HelperMethods.md), and the helper class can be extended to add your own.

### Example Code ###
{_bracketed comments are **not** Haml code_}
#### Haml ####
```
  .content {default tag is div. .class=<div class="class">, #id=<div id="id">}
    %p {%tag}
      %img(src="/images/logo.jpg" alt="Logo") {attributes are specified as normal}
      Haml is based on one primary principle. Markup should be beautiful.
    %ul.list
      - for($i=0; $i<3; $i++) {no need for ending semicolons or brackets for PHP code}
        %li= $i
```
#### XHTML/PHP (Nested Rendering) ####
```
  <div class="content">
    <p>
      <img src="/images/logo.jpg" alt="Logo" />
      Haml is based on one primary principle. Markup should be beautiful.
    </p>
    <ul class="list">
      <?php for($i=0; $i<3; $i++) { ?>
        <li><?php echo $i; ?></li>
      <?php } ?>
    </ul>
  </div>
```

---

## Sass ##
Sass is a meta-language on top of CSS that’s used to describe the style of a document cleanly and structurally, with more power than flat CSS allows; keeping stylesheets
powerful, manageable and [DRY](http://en.wikipedia.org/wiki/Don%27t_repeat_yourself).

**Features**
  * Nested rules. See stylesheet structure at a glance and avoid repetition.
  * Variables. Make stylesheets readable.
  * SassScript. Perform operations on variables. Unit conversion and colour operations are supported.
  * Mixins. Reuse property and rule definitions without retyping them.
  * Selector Inheritance. Tell one selector to inherit all the styles of another without duplicating the CSS properties.
  * Import. Import Sass files (.sass and .scss) into a single CSS output to save HTTP requests.
  * Caching of compiled Sass files.

Mixins are extremely powerful. They allow properties and rules to be reused without having to rewrite them. Their true power is realised with the use of arguments; this allows a mixin to be reused in different ways in different places.

The _@import_ directive imports other Sass files. This allows stylesheets to be split up into manageable sections and even modularise your stylesheets; the [Compass Project](http://compass-style.org/) is a great example of how Sass permits modularisation.
Variables and Mixins defined in imported stylesheets are available in the importing stylesheet. And the output is a single CSS file; saving HTTP requests.

From V3.2 PHamlP includes a port of [Compass](http://compass-style.org/).

See http://sass-lang.com/ for details on Sass

### Example Code ###
{_bracketed comments are_not_Sass code_}
#### Sass ####
```
  $link_colour: #556b2f {defines a variable}
  $link_visited_colour: crimson {PHamlP supports SVG colours)
  $link_hover_colour: !link_colour + #333 {SassScript has colour operations}

  =replace-text($img, $x = 50%, $y = 50%) {= defines a mixin. Uses arguments and has defaults for some)
    text-indent: -9999em
    overflow: hidden
    background:
      image: image_url($img)
      repeat: no-repeat
      position: $x $y

  h1
    +replace-text(/images/logo.png) {+ use a mixin)
    font: {save repition with nesting of selectors}
     size: 120%
     weight: bold

  h2#overview
    +replace-text(/images/overview.png, 20%) {use the mixin with different arguments}

  .content
    margin: 1% 2%
    p
      padding: 1% 2%
    a
      color: $link_colour {assign a variable}
      &:hover {& is replaced with the parent selector}
        color: $link_hover_colour
      &:visited
        color: $link_visited_colour

  @import sass_import {import sass_import.sass and make variables and mixins defined in it available here. No extension means use the current extension.}
  @import scss_import.scss {import scss_import.scss and make variables and mixins defined in it available here. Need to specify .scss in a .sass file, and vice versa}
```

#### SCSS ####
Version 3 of PHamlP brings the new SCSS syntax (note: the indented SASS syntax is _not_ deprecated and is fully supported). This is the equivalent of the above in SCSS.
```
  $link_colour: #556b2f;
  $link_visited_colour: crimson;
  $link_hover_colour: $link_colour + #333;

  @mixin replace-text($img, $x = 50%, $y = 50%) {
    text-indent: -9999em;
    overflow: hidden;
    background: {
      image: image_url($img);
      repeat: no-repeat;
      position: $x $y;
    }
  }

  h1 {
    @include replace-text(/images/logo.png);
    font: {
      size: 120%;
      weight: bold;
    }
  }

  h2#overview {
    @include replace-text(/images/overview.png, 20%);
  }

  .content {
    margin: 1% 2%;
    p {
      padding: 1% 2%;
    }
    a {
      color: $link_colour;
      &:hover {
        color: $link_hover_colour;
      }
      &:visited {
        color: $link_visited_colour;
      }
    }
  }

  @import sass_import.sass;
  @import scss_import;
```

#### CSS (Nested Rendering) ####
```
  h1 {
    text-indent: -9999em;
    overflow: hidden;
    background-image: image_url(/images/logo.png);
    background-repeat: no-repeat;
    background-position: 50% 50%;
    font-size: 120%;
    font-weight: bold;
  }

  h2#overview {
    text-indent: -9999em;
    overflow: hidden;
    background-image: image_url(/images/overview.png);
    background-repeat: no-repeat;
    background-position: 20% 50%;
  }

  .content {
    margin: 1% 2%;
  }
    .content p {
      padding: 1% 2%;
    }
      .content p a {
        color: #556b2f;
      }
        .content p a:hover {
          color: #dc143c;
        }
        .content p a:visited {
          color: #889e62;
        }
  
  {The compiled content of the included files will appear here}
```

## Credits ##
PHamlP is a port of Haml and Sass to PHP. All the genius comes from the people that  invented and develop Haml and Sass; in particular:
  * [Hampton Catlin](http://hamptoncatlin.com/),
  * [Nathan Weizenbaum](http://nex-3.com/),
  * [Chris Eppstein](http://chriseppstein.github.com/)

The bugs are mine.