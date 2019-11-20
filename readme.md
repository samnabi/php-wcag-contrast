# WCAG Contrast Colour Adjuster

A PHP function to ensure adequate contrast between two colours. If the two colours don't have enough contrast, both colors get nudged brighter or darker until there's enough contrast between them.

## Usage example

```php
require_once('wcag-contrast.php');

adjustColors('#AAAAAA','#CCCCCC'); // result: [#919191,#eaeaea]
```

The `adjustColors()` function takes three arguments:

1. `$color1` – The first colour, in hex (string) or RGB (array) format.
2. `$color2` – The second colour, in hex (string) or RGB (array) format.
3. `$threshold` – The contrast threshold to test for. Optional. Default is 'AA'.

### Other thresholds

The `$threshold` argument accepts the following values:

- 'AA'
- 'AAA'
- 'AALarge'
- 'AAALarge'
- 'AAMediumBold'
- 'AAAMediumBold'
- Any contrast ratio as a number

