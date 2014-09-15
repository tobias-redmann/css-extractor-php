<?php
require 'vendor/autoload.php';


function RGBToHex($r, $g, $b) {
    //String padding bug found and the solution put forth by Pete Williams (http://snipplr.com/users/PeteW)
    $hex = "#";
    $hex.= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
    $hex.= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
    $hex.= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);

    return $hex;
}


//$css_file = 'https://www.tricd.de/wp-content/themes/friedrich/style.css?ver=4.0';

$css_file = 'https://test12.sevenval-fit.com/admiralmobileapp/testing/;s;m=css;cdrid=cdrid_55528;extver=c866395bbd308031097815a8daab96bc;rp=css/css/styles.mobile.xml';

$project_name = 'admiral';

$color_file_name = $project_name. '_colors.css';

$unique_colors_file = $project_name. '_unique_colors.txt';


$oCssParser = new Sabberworm\CSS\Parser(file_get_contents($css_file));

$oCssDocument = $oCssParser->parse();


/*
$all_values = $oCssDocument->getAllValues();

foreach($all_values as $value) {

    print_r($value);

}*/

$color_hdl = fopen($color_file_name, 'w+');

$unique_colors = array();


$decls = $oCssDocument->getAllDeclarationBlocks();

foreach ($decls as $decl) {

    $decl->expandShorthands();

    $selectors = $decl->getSelectors();

    $s = array();

    // get the selectors
    foreach($selectors as $selector) {


        $s[] = $selector->getSelector();

    }

    $colors = array();

    // get the rulesets

    $rules = $decl->getRules();

    foreach ($rules as $rule) {


        $rule_name = $rule->getRule();

        $value = $rule->getValue();

        if (is_string($value)) continue;

        $value_type = get_class($value);


        if (strpos($value_type, 'RuleValueList')) {


            $components = $value->getListComponents();


            foreach ($components as $component) {

                print_r($component);

                if (is_string($component)) continue;

                $component_type = get_class($component);

                if (strpos($component_type, 'Color')) {


                    $color_type = $component->getColorDescription();

                    if ($color_type == 'rgb') {

                        $color = $component->getColor();

                        $red    = $color['r']->getSize();
                        $green  = $color['g']->getSize();
                        $blue   = $color['b']->getSize();

                        $hex_value = RGBToHex($red, $green, $blue);

                        $colors[$rule_name] = $hex_value;

                        if (!in_array($hex_value, $unique_colors)) {

                            $unique_colors[] = $hex_value;

                        }

                    }

                    if ($color_type == 'rgba') {

                        $color = $component->getColor();

                        $rgba_values = array();

                        foreach ($color as $cc) {

                            $rgba_values[] = $cc->getSize();

                        }

                        $rgba = 'rgba('. implode(', ', $rgba_values) .')';

                        $colors[$rule_name] = $rgba;

                        if (!in_array($rgba, $unique_colors)) {

                            $unique_colors[] = $rgba;

                        }


                    }


                }


            }

        }

        if (strpos($value_type, 'Size')) {

            echo "- Size\n";

        }

        if (strpos($value_type, 'Color')) {



            $color_type = $value->getColorDescription();

            if ($color_type == 'rgb') {

                $color = $value->getColor();

                $red    = $color['r']->getSize();
                $green  = $color['g']->getSize();
                $blue   = $color['b']->getSize();

                $hex_value = RGBToHex($red, $green, $blue);

                $colors[$rule_name] = $hex_value;

                if (!in_array($hex_value, $unique_colors)) {

                    $unique_colors[] = $hex_value;

                }

            }

            if ($color_type == 'rgba') {

                $color = $value->getColor();

                $rgba_values = array();

                foreach ($color as $cc) {

                    $rgba_values[] = $cc->getSize();

                }

                $rgba = 'rgba('. implode(', ', $rgba_values) .')';

                $colors[$rule_name] = $rgba;

                if (!in_array($rgba, $unique_colors)) {

                    $unique_colors[] = $rgba;

                }


            }





        }

        if (strpos($value_type, 'String')) {

            /* echo "- String\n"; */

        }

        if (strpos($value_type, 'URL')) {

            /* echo "- URL\n"; */

        }




    }



    $sel = implode(', ', $s);

    if (count($colors) > 0) {


        fwrite($color_hdl, $sel. "{\n");

        // sort the array
        ksort($colors);

        foreach ($colors as $attr =>  $color) {

            fwrite($color_hdl, "\t". $attr.": ".$color.";\n");

        }

        fwrite($color_hdl,  "}\n\n");

    }



}

fclose($color_hdl);

if (count($unique_colors) > 0) {

    file_put_contents($unique_colors_file, implode("\n", $unique_colors));

}