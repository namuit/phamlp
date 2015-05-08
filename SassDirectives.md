# Introduction #

PHamlP supports Sass Directives and provides some enhancements; described below.


# Details #
## @for Directive ##
PHamlP adds _step_; _step_ is optional and defaults to 1 (one).

**Example**
```
  @for !i from 1 through 9 step 2
    .item-#{!i}
      width = 2em * !i
```
compiles to:
```
  .item-1 {
    width: 2em; }
  .item-3 {
    width: 6em; }
  .item-5 {
    width: 10em; }
  .item-7 {
    width: 14em; }
  .item-9 {
    width: 18em; }
```

## @do Directive ##
PHamlP adds support for _do_ loops. The condition for a _do_ loop is evaluated at the end of the loop, meaning that the loop will be executed at least once.

**Example**
```
  !i = 2
  @do !i < 5
    .item-#{!i}
      width = 2em * !i
    !i = !i + 2
```
compiles to:
```
  .item-2 {
    width: 4em; }
  .item-4 {
    width: 8em; }
```

**Example**
```
  !i = 6
  @do !i < 5
    .item-#{!i}
      width = 2em * !i
    !i = !i + 2
```
compiles to:
```
  .item-6 {
    width: 12em; }
```

