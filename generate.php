<?php
include("colors.php"); // Gets used for highlighting values
$details = [ // Clean up labeling
	"pedal-name" => "Pedal Name",
	"based-on" => "Based On",
	"pedal-type" => "Pedal Type",
	"control-layout" => "Control Layout",
	"controls" => "Controls",
	"notes" => "Notes"
];
if(isset($_POST)) {
	// Input sanitazation should probably be more robust
	$i = array_map( 'trim', array_map( 'strip_tags', $_POST ));
	// Sort & Count Resistors, Capacitors, Diodes, ICs, Transistors and LDRs
	if(isset($i['components']) && !empty ($i['components'])) {
		$out = [];
		$lines = explode("\r\n", $i['components']);
		foreach($lines as $l) { // Cycle through each component line
			$subj = strtolower($l);
			$pattern = '/(?P<type>^R|C|D|IC|Q|LDR)(?P<num>\d+)\s?(?P<val>.+)/i';
			preg_match($pattern, $subj, $matches); // Check each line for component matching regex
			if($matches !== false && isset($matches['type'])) { // If there are regex matches, sort into component type arrays
				$matches['num'] += 0; // Strip Leading Zeroes
				switch (strtoupper($matches['type'])) {
					case 'R': // Resistors
						$normal = preg_replace('/(\d+?)(\.)(\d+?)(k|m)(f?)/', '$1$4$3', $matches['val']);
						if(is_numeric($normal)) { // if a value has no unit prefix (ie, 400 ohms)
							$normal = $normal . "R";
						}
						$out['resistors'][$matches['num']] = $normal;
						ksort($out['resistors']);
						break;
					case 'C': // Capacitors
						$search = ['uf', 'pf', 'nf'];
						$replace = ['u', 'p', 'n'];
						$text = str_ireplace($search, $replace, $matches['val']); // make nF, uF, etc uniform
						$notation = preg_replace('/(\d+?)(\.)(\d+?)(n|u)/', '$1$4$3', $text); // fix 1.2uf to 1u2
						$out['capacitors'][$matches['num']] = $notation;
						ksort($out['capacitors']);
						break;
					case 'Q': // Transistors
						$out['transistors'][$matches['num']] = $matches['val'];
						ksort($out['transistors']);
						break;
					case 'D': // Diodes
						$out['diodes'][$matches['num']] = $matches['val'];
						ksort($out['diodes']);
						break;
					case 'IC': // Integrated Circuits
						$out['ic'][$matches['num']] = $matches['val'];
						ksort($out['ic']);
						break;
					case 'LDR': // Light Dependent Resistors
						$out['ldr'][$matches['num']] = $matches['val'];
						ksort($out['ldr']);
						break;
					default:
						break;
				}
			}
		} /* End Components */

		// Count and Sort Electromechanical
		// Potentiometers, Switches, Etc
		if(isset($i['electromechanical'])) {
			$explode = explode("\r\n", $i['electromechanical']);
			foreach($explode as $em_lines) {
				$subj = $em_lines;
				$pattern = '/(?<name>\w+)\s(?<val>\w+)/i';
				preg_match($pattern, $subj, $matches);
				if($matches !== false && isset($matches['name']) && isset($matches['val'])) {
					$out['electromechanical'][] = array($matches['name'], $matches['val']);
				}
			}

		} /* End Electromech */

		if(isset($i['syntax'])) { // Universalize Notation
			switch ($i['syntax']) {
				case 'prefix': // eg 4u7
					$out['resistors'] = preg_replace('/^(\d)(\.)(\d)(m|k)/mi', '$1$4$3', $input_lines);
					$out['capacitors'] = preg_replace('/^(\d)(\.)(\d)(u|n)/mi', '$1$4$3', $input_lines);
					break;
				case 'decimal': // eg 4.7u
					$out['resistors'] = preg_replace('/^(\d)(m|k)(\d)/mi', '$1.$3$2', $out['resistors']);
					$out['capacitors'] = preg_replace('/^(\d)(u|m|k|n)(\d)/mi', '$1.$3$2', $out['capacitors']);
					break;
				default:
					break;
			}
		}

		if(isset($i['use-ohms'])) { // Add ohm symbol to end of resistor values
			foreach($out['resistors'] as $k => $v) {
				$search = ["Ω", " Ω", "Ω", " Ω"];
				$replace = ["","","",""];
				$out['resistors'][$k] = str_replace($search, $replace, $v) . "Ω";
			}
		}
		if(isset($i['unit-case'])) { // 4u7 or 4U7
			/* Why did I not just write this as an array_map ? */
			if($i['unit-case'] == "upper") {
				if(array_key_exists('resistors', $out)) {
					foreach($out['resistors'] as $k => $v) {
						$out['resistors'][$k] = strtoupper($v);
					}
				}
				if(array_key_exists('capacitors', $out)) {
					foreach($out['capacitors'] as $k => $v) {
						$out['capacitors'][$k] = strtoupper($v);
					}
				}
			}
			if($i['unit-case'] == "lower") {
				if(array_key_exists('resistors', $out)) {
					foreach($out['resistors'] as $k => $v) {
						$out['resistors'][$k] = strtolower($v);
					}
				}
				if(array_key_exists('capacitors', $out)) {
					foreach($out['capacitors'] as $k => $v) {
						$out['capacitors'][$k] = strtolower($v);
					}
				}
			}
		}
		if(isset($i['use-mu'])) { // Use mu symbol in place of "u" for micro prefix
			$out['capacitors'] = preg_replace('/u/imU', '&micro;', $out['capacitors']);
		}

		// Start mapping to output

		foreach($out as $name => $arr) {
			if($name !== "electromechanical") {
				// Counts
				$counts[$name] = array_count_values($arr);

				// Find Neighbors
				if(isset($i['combine-positions'])) { // If the output should be like "R1-R15 100K" as opposed to "R1 100K R2 100K ... "
					$arrayRange = []; // positions['caps']['value']['start']['end']
					for($pp = 1; $pp < count($arr); $pp++){
							if(count($arrayRange) == 0) {
									// The multidimensional array is still empty
									$arrayRange[0] = [
										"value" => $arr[$pp],
										"start" => $pp,
										"end" => $pp
									];
							}
							else {
								if($arr[$pp] == $arrayRange[count($arrayRange)-1]['value']) {
											// It's still the same value, update the value of the last key
											$arrayRange[count($arrayRange)-1]['end'] = $pp;
								 }
								else {
											// It's a new value, insert a new array
									 $arrayRange[count($arrayRange)] = [
										"value" => $arr[$pp],
										"start" => $pp,
										"end" => $pp
									];
								 }
							}
					}
					$positions[$name] = [];
					foreach($arrayRange as $index => $array) {
						if($array['end'] - $array['start'] == 0) {
							$positions[$name][$array['start']] = $array['value'];
						}
						if($array['end'] - $array['start'] > 0) {
							$range = $array['start'] . "-" . $array['end'];
							$positions[$name][$range] = $array['value'];
						}
					}
					$out[$name] = $positions[$name];
				}
				else {
						$positions[$name] = $arr;
				} // End Find Neighbors
				// Much later comment: I have no idea where I sourced the above function from or how it works
				arsort($counts[$name]);
			}
			if($name === "electromechanical") {
				$counts['electromechanical'] = array_count_values(array_column($arr, 1));
			}
		}

		if(isset($i['combine-semiconductors'])) { // Semiconductor Category instead of individual categories for Diodes, ICs, Transistors
			$diodes = isset($out['diodes']) ? count($out['diodes']) : 0;
			$ic = isset($out['ic']) ? count($out['ic']) : 0;
			$q = isset($out['transistors']) ? count($out['transistors']) : 0;
			if($diodes + $ic + $q < 9) { // Arbitrary cutoff of 9
				if($diodes) {
					// $out['semiconductors']['diodes'] = [];
					foreach($out['diodes'] as $n => $v) {
						$out['semiconductors'][] = "D" . $n . strtoupper($v);
					}
					foreach($counts['diodes'] as $n => $v) {
						$counts['semiconductors'][strtoupper($n)] = strtoupper($v);
					}
				}
				if($ic) {
					// $out['semiconductors']['ic'] = [];
					foreach($out['ic'] as $n => $v) {
						$out['semiconductors'][] = "IC" . $n .  strtoupper($v);
					}
					foreach($counts['ic'] as $n => $v) {
						$counts['semiconductors'][strtoupper($n)] = strtoupper($v);
					}
				}
				if($q) {
					// $out['semiconductors']['transistors'] = [];
					foreach($out['transistors'] as $n => $v) {
						$out['semiconductors'][] = 'Q' . $n . " " . strtoupper($v);
					}
					foreach($counts['transistors'] as $n => $v) {
						$counts['semiconductors'][strtoupper($n)] = strtoupper($v);
					}
				}
				unset($out['diodes'], $out['ic'], $out['transistors']);
				unset($counts['diodes'], $counts['ic'], $counts['transistors']);
			}
		} // End Combine Semiconductors


		// Control Layout
		if($i['control-layout']) {
			if($i['control-layout'] == "na") {
				unset($i['control-layout']);
			}
			else {
				$terms = [
					"2k1" => "2 Knob Type 1",
					"3k1" => "3 Knob Type 1",
					"4k1" => "4 Knob Type 1",
					"5k1" => "5 Knob Type 1",
					"5k2" => "5 Knob Type 2",
					"6k1" => "6 Knob Type 1"
				];
				$i['control-layout'] = $terms[$i['control-layout']] ?? $i['control-layout'];
			}
		}

		if(isset($i['highlight-same'])) { // Highlight same values
			$highlight = [];
			foreach($counts as $type => $details) {
				foreach($details as $val => $count) {
					if($count > 3) {
						$highlight["{$type}"][$val]['num'] = $count;
						// Old function, not sure what changed 
						/* if($i['highlight'] == "color") {
								$highlight["{$type}"][$val]['hex'] = randcolor();
						}
						if($i['highlight'] == "gray") {
							$highlight["{$type}"][$val]['hex'] = randcolor(true);
						}*/

					}
				}
			}
			foreach($highlight as $type => $array) {
				$num = count($array);
				if($i['highlight'] == "gray") { // Divide into equidistant grayscale values
						$start = 0;
						$end = 85;
						$increment = ($end - $start) / $num;
						$current = $start;
						foreach($array as $value => $contents) {
							$highlight[$type][$value]['hex'] = "hsl(0, 0%, {$current}%)";
							$current = $current + $increment;
						}
				}
				if($i['highlight'] == "color") {
					$increment = 360/$num;
					$current = rand(0,360);
					foreach($array as $value => $contents) {
						$s = rand(45,100);
						$l = rand(45,90);
						$highlight[$type][$value]['hex'] = "hsl({$current}, {$s}%, {$l}%)";
						$current = $current + $increment;
						if($current > 360) {
								$current = $current - 360;
						}
					}
				}
			}

			echo "<style type='text/css'>";
			foreach($highlight as $category => $details) {
				foreach($details as $n => $f) {
					echo "._{$n} {";
					echo "background-color:";
					echo $f['hex'];
					echo ";\n";
					 preg_match('/\d+(?=%\))/im', $f['hex'], $l);
					if(isset($l[0]) && $l[0] < 50) {
						echo "color: white;\n";
					}
					echo "}\n";
				}
			}
			echo "</style>";

		}

		$htmlbody = isset($i['list-checkboxes']) ? "list-checkboxes" : "";
		$htmlbody .= isset($i['header-checkboxes']) ? " header-checkboxes" : "";

		$pagesizes = ['usletter', 'indexcard', 'a4'];
		if(in_array($i['page-size'], $pagesizes)) {
			$print = $i['page-size'];
		}
		else {
			$print = $i['usletter'];
		}

		?>
	<html lang="en" dir="ltr">
		<head>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta charset="utf-8">
			<title>Bill of Materials Component Counter</title>
			<link rel='stylesheet' href='generate.css'>
			<link rel='stylesheet' href='print/<?=$print;?>.css'>
		</head>
		<body class='<?=$htmlbody;?>'>
			<div class='wrapper'>
<?php
echo "<div class='details-container'>";
foreach($details as $k => $v) {
	if(!empty($i[$k])) {
		echo "<div class='details {$k}'>";
		echo "<div class='caption {$k}'>{$v}</div>";
		echo "<div class='value {$k}'>";
		echo $i[$k];
		echo "</div>";
		echo "</div>";
	}
}
/*
From here, we basically build an array in the correct order and then output it
*/
echo "</div>";
$final_order = [
	"resistors",
	"capacitors",
	"diodes",
	"transistors",
	"ic",
	"semiconductors",
	"electromechanical"
];

$final = [];
foreach($final_order as $name) {
	if(array_key_exists($name, $out)) {
		$final[$name] = [];
		$final[$name] = $out[$name];
	}
}
$out = $final;
foreach($out as $type => $arr) {
	echo "<div class='type-out {$type}'>";
	echo "<h2 class='{$type}-head'>" . ucwords($type) . "</h2>";
	$counts_shown = false;
	if($type !== "electromechanical" && isset($i['show-counts']) && count($arr) > 6) {
		$counts_shown = true;
		echo "<h3>Counts</h3>";
		echo "<div class='count {$type}-count-container'>";
		echo "<ul class='{$type}-count'>";
		foreach($counts[$type] as $val => $n) {
			$text = $val;
			echo "<li class='count-item {$type} {$val}'>";
			echo "<span class='count-val'>";
			echo $text . "</span> &times; <span class='count-n'>" . $n;
			echo "</span></li>";
		}
		echo "</ul></div>";
	}
	$prefixes = [
		"resistors" => "R",
		"transistors" => "Q",
		"diodes" => "D",
		"capacitors" => "C",
		"ic" => "IC",
	];
	// if(is_array($arr[array_key_first($arr)]) === false) {
	if($type !== "electromechanical") {
		$curr = $prefixes[$type] ?? "";
		if($counts_shown == true) {
			echo "<h3>Values</h3>";
		}
		echo "<div class='{$type}-vals {$type} vals'>";
		echo "<ul class='components {$type} {$type}-values'>";
		foreach($arr as $n => $val) {
			if($type == "semiconductors") {
				$ex = explode(" ", $val);
				$namep = $ex[0];
				$text = $ex[1];
			}
			else {
				$namep = $type != "semiconductors" ? $curr . $n : "";
				$text = $val;
			}
			$cssclass = str_ireplace(" ", " _", $text);
			echo "<li id='{$namep}' class='{$namep} {$val}'><span class='value-outer _{$cssclass}'>";
			echo "<span class='namep'>";
			echo $namep . "</span> <span class='comp-val'>" . $text;
			echo "</span></span></li>";
		}
		echo "</ul>";
		echo "</div>";
	}
	else {
		echo "<div class='electromechanical-vals vals'>";
		echo "<ul class='electromechanical'>";
		foreach($arr as $em) {
			echo "<li>";
			echo implode(" ", $em);
			echo "</li>";
		}
		echo "</ul>";
		echo "</div>";
	}
	unset($counts_shown);
	echo "</div>";
}
	}
}
?>

</body>
</html>