<?php

/**
 * @param $color_code: rgb array | hex string
 * @param int $percentage_adjuster
 * @return array | string
 * @author Jaspreet Chahal
 */
function adjustLuminosity($color_code,$percentage_adjuster = 0) {
    $percentage_adjuster = round($percentage_adjuster/100,2);
    if(is_array($color_code)) {
        $r = $color_code[0] - (round($color_code[0])*$percentage_adjuster);
        $g = $color_code[1] - (round($color_code[1])*$percentage_adjuster);
        $b = $color_code[2] - (round($color_code[2])*$percentage_adjuster);

        return [
          round(max(0,min(255,$r))),
          round(max(0,min(255,$g))),
          round(max(0,min(255,$b)))
        ];
    }
    else if(preg_match("/#/",$color_code)) {
        $hex = str_replace("#","",$color_code);
        $r = (strlen($hex) == 3)? hexdec(substr($hex,0,1).substr($hex,0,1)):hexdec(substr($hex,0,2));
        $g = (strlen($hex) == 3)? hexdec(substr($hex,1,1).substr($hex,1,1)):hexdec(substr($hex,2,2));
        $b = (strlen($hex) == 3)? hexdec(substr($hex,2,1).substr($hex,2,1)):hexdec(substr($hex,4,2));
        $r = round($r - ($r*$percentage_adjuster));
        $g = round($g - ($g*$percentage_adjuster));
        $b = round($b - ($b*$percentage_adjuster));

        return "#".str_pad(dechex( max(0,min(255,$r)) ),2,"0",STR_PAD_LEFT)
            .str_pad(dechex( max(0,min(255,$g)) ),2,"0",STR_PAD_LEFT)
            .str_pad(dechex( max(0,min(255,$b)) ),2,"0",STR_PAD_LEFT);
    }
}


/**
 * calculates the luminosity of an given RGB color
 * the color code must be in the format of RRGGBB
 * the luminosity equations are from the WCAG 2 requirements
 * http://www.w3.org/TR/WCAG20/#relativeluminancedef
 * @param $color: array | string
 * @return float
 */ 
function calculateLuminosity($color) {
    if(is_array($color)) {
      $r = $color[0]; // red value
      $g = $color[1]; // green value
      $b = $color[2]; // blue value
    } else if(preg_match("/#/",$color)) {
      $r = hexdec(substr($color, 0, 2)) / 255; // red value
      $g = hexdec(substr($color, 2, 2)) / 255; // green value
      $b = hexdec(substr($color, 4, 2)) / 255; // blue value
    }
    if ($r <= 0.03928) {
        $r = $r / 12.92;
    } else {
        $r = pow((($r + 0.055) / 1.055), 2.4);
    }
    if ($g <= 0.03928) {
        $g = $g / 12.92;
    } else {
        $g = pow((($g + 0.055) / 1.055), 2.4);
    }
    if ($b <= 0.03928) {
        $b = $b / 12.92;
    } else {
        $b = pow((($b + 0.055) / 1.055), 2.4);
    }
    $luminosity = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    return $luminosity;
}

/**
 * http://www.w3.org/TR/WCAG20/#visual-audio-contrast (1.4.3)
 * http://www.w3.org/TR/WCAG20/#larger-scaledef
 * @param $color1: array | string
 * @param $color2: array | string
 * @param $threshold: string | float
 * @return bool
 */ 
function evaluateContrast($color1, $color2, $threshold) {
    
  // Calculate luminoaisty ratio
  $l1 = calculateLuminosity($color1);
  $l2 = calculateLuminosity($color2);
  if ($l1 > $l2) {
      $ratio = (($l1 + 0.05) / ($l2 + 0.05));
  } else {
      $ratio = (($l2 + 0.05) / ($l1 + 0.05));
  }

  // Compare ratio to the threshold
  if (!isset($threshold) or is_string($threshold)) {
    switch ($threshold) {
      case 'AALarge':
      case 'AAMediumBold':
        return $ratio >= 3 ? true : false;
        break;
        
      case 'AAA':
        return $ratio >= 7 ? true : false;
        break;
      
      case 'AA':
      case 'AAALarge':
      case 'AAAMediumBold':
      default:
        return $ratio >= 4.5 ? true : false;
        break;
    }
  } else {
    // If $threshold isn't a string, treat it as a luminosity ratio
    return $ratio >= floatval($threshold) ? true : false;
  }
}

/**
 * Adjust colors to meet contrast threshold
 * Both colors get nudged until there's enough contrast between them
 * @param $color1: array [r,g,b] | string
 * @param $color2: array [r,g,b] | string
 * @param $threshold: string | float
 * @return array
 */
function adjustColors($color1, $color2, $threshold = 'AA') {

  // Find out which color is darker
  $l1 = calculateLuminosity($color1);
  $l2 = calculateLuminosity($color2);
  if ($l1 > $l2) {
    $colorLight = $color1;
    $colorDark = $color2;
  } else {
    $colorLight = $color2;
    $colorDark = $color1;
  }

  $isAccessible = false;
  $i = 0;
  while ($isAccessible == false) {
    $isAccessible = evaluateContrast($colorLight, $colorDark, $threshold);
    if ($isAccessible === true) {
      continue; // Great, the colours have enough contrast
    } else {
      // Lighten the background colour by 1%
      $colorLight = adjustLuminosity($colorLight, -1);
      // Darken the accent colour by 1%
      $colorDark = adjustLuminosity($colorDark, 1);
    }
  }
  
  if ($l1 > $l2) {
    return [$colorLight, $colorDark];
  } else {
    return [$colorDark, $colorLight];
  }
}

?>