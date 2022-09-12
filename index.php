<html lang="en" dir="ltr">

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="utf-8">
	<title>BOM Component Counter</title>
	<link rel='stylesheet' href='style.css'>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
</head>

<body>

	<div class='wrapper'>

		<h1>Bill of Materials Component Counter</h1>

		<div class='intro'>
			<h2>Purpose</h2>
			<p>This was made highly focused on guitar pedal pcbs, and more specifically <a href='https://pedalpcb.com' target='_blank'>pedalpcb</a> boards, but has worked well with all pedal bills of materials I have used.</p>
			<p>The output of this tool has also been designed with extensive print stylesheets as printing physical copies on <a href='screenshot.png' target="_blank">3"x5" notecards was my own end goal</a> so I wouldn't be fumbling with 5 pages of build docs while looking for a resistor.</p>
			<h2>Instructions</h2>
			<p>Place all but electromechanical components (potentiometers, switches, etc) in the <em>components</em> input.</p>
			<p>If a component is extremely rare and uses an odd prefix, the electroemchanical input is more forgiving to strange prefixes.</p>
			<h3>Credit</h3>
			<p>Made by Alex Fine - known on the pedalpcb forums as <a href='https://forum.pedalpcb.com/members/finebyfine.3361/'>finebyfine</a>. Please do not hesitate to reach out with any issues either by forum direct message or emailing me at:
				alex@fine.rip</p>
			<p>Source code available upon request.</p>

		</div>

		<div id='form'>

			<form action='generate.php' method='post'>
				<fieldset>
					<legend>Board Information</legend>
					<p class='caption'>All information fields are optional. Only non-empty fields will be displayed.</p>

					<div class='form-grid'>


						<div class='col-1'>
							<label for='pedal-name' class='bold'>Pedal Name</label>
						</div>
						<div class='col-1'>
							<input type='text' name='pedal-name' id='pedal-name' class='full'>
						</div>

						<div class='col-1'>
							<label for='based-on' class='bold'>Based On</label>
						</div>
						<div class='col-1'>
							<input type='text' name='based-on' id='based-on' class='full'>
						</div
						>
						<div class='col-1'>
							<label for='pcb-no' class='bold'>Pedal / PCB #</label>
						</div>
						<div class='col-1'>
							<input type='text' name='pcb-nn' id='pcb-nn' class='full'>
						</div>

						<div class='col-1'>
							<label for='pcb-mfg' class='bold'>Board Manufacturer</label>
						</div>
						<div class='col-1'>
							<input type='text' name='pcb-mfg' id='pcb-mfg' class='full'>
						</div>

						<div class='col-1'>
							<label for='pedal-type' class='bold'>Pedal Type</label>
						</div>
						<div class='col-1'>
							<input list='type' name='pedal-type' id='pedal-type' class='full'>

							<datalist id='type'>
								<option value=''>
								<option value="Boost">
								<option value="Delay">
								<option value="Dynamics">
								<option value="Filter">
								<option value="Modulation">
								<option value="Overdrive">
								<option value="Distortion">
								<option value="Fuzz">
								<option value="Pitch">
								<option value="Reverb">
							</datalist>
						</div>

						<div class='col-1'>
							<label for='control-layout' class='bold'>Control Layout</label>
						</div>
						<div class='col-1'>
							<select name='control-layout' id='control-layout'>
								<option value='na' selected>N/A (hide on output)</option>
								<option value='other'>Other</option>
								<option value='2k1'>2 Knob Type 1</option>
								<option value='3k1'>3 Knob Type 1</option>
								<option value='4k1'>4 Knob Type 1</option>
								<option value='5k1'>5 Knob Type 1</option>
								<option value='5k2'>5 Knob Type 2</option>
								<option value='6k1'>6 Knob Type 1</option>
							</select>
							 <label for='knob-toggle'> <input type='checkbox' name='knob-toggle' id='knob-toggle'> Enter controls</label></span>
						</div>

						<div class='col-1 conditional' data-cond-option="knob-toggle" data-cond-value="on">
							<label for='controls' class='bold'>Controls</label>
						</div>
						<div class='col-1 conditional' data-cond-option="knob-toggle" data-cond-value="on">
							<textarea name='controls'></textarea>
						</div>

						<div class='col-1'>
							<label for='notes' class='bold'>Notes</label>
						</div>
						<div class='col-1'>
							<textarea width='100%' rows='5' name='notes' id='notes'></textarea></div>

					</div>
				</fieldset>

				<fieldset>
					<legend>Components</legend>
					<p>One line per item</p>
					<p class='caption'>You can include component headers (if on their own lines) to make copying and pasting from pdfs easier, this tool will ignore them.</p>
					<textarea width='100%' rows='10' name='components' id='components'></textarea>
				</fieldset>
				<fieldset>
					<legend>Electromechanical</legend>
					<textarea width='100%' rows='10' name='electromechanical' id='electromechanical'></textarea>
				</fieldset>

				<fieldset>
					<legend>Output Options</legend>

					<div class='form-grid'>

						<div class='col-2'>

							<label for='show-counts'><input type='checkbox' name='show-counts' id='show-counts' checked> Show original component positions</label>
						</div>

						<div class='col-2 conditional' data-cond-option='show-counts' data-cond-value='on'>
							<label for='combine-positions'>&emsp;<input type='checkbox' name='combine-positions' id='combine-positions'> Combine neighbor positions <div class='caption-2 combine-caption'>Q1 2N3904 Q2 2N3904 <b>becomes Q1-Q2 2N3904</b></div></label>
						</div>

						<div class='col-2 conditional' data-cond-option='show-counts' data-cond-value='on'>
							<label for='highlight-same'>&emsp;<input type='checkbox' name='highlight-same' id='highlight-same'> Highlight same values in component list (when more than 3)</label>
						</div>


						<div class="conditional col-2 grid-2" data-cond-option="highlight-same" data-cond-value="on">
							<div class='col-1'>
							&emsp;<input type='radio' id='hilight-color' name='highlight' value='color'>
							<label for='hilight-color' class='hilight-color'> Highlight with color</label>
						</div>
						<div class='col-1'>
							&emsp;<input type='radio' id='hilight-grayscale' name='highlight' value='gray'>
							<label for='hilight-grayscale' class='hilight-grayscale'> Highlight in grayscale </label>
						</div>
						</div>

						<div class='col-2'>


							<label for='list-checkboxes'><input type='checkbox' name='list-checkboxes' id='list-checkboxes'> Use checkboxes for bullets in component count lists</label>

						</div>

						<div class='col-2'>


							<label for='header-checkboxes'><input type='checkbox' name='header-checkboxes' id='header-checkboxes'> Use checkboxes in component category headers</label>

						</div>

						<div class='col-2'>


							<label for='combine-semiconductors'><input type='checkbox' name='combine-semiconductors' id='combine-semiconductors'> Combine diodes, transistors and integrated circuits into <b>Semiconductors</b> category if there's not many of each</label>

						</div>



						<div class='col-1 bottom-border top-margin'>
							<label for='syntax' class='bold'>Ohm & Farad Conventions</label>
						</div>

						<div class='col-1 top-margin'>
							<div><label for='prefix'>
									<input type='radio' name='syntax' value='4u7' id='4u7' checked> Unit prefix (e.g. 4u7)</label></div>
							<div>
								<label for='dot'>
									<input type='radio' name='syntax' value='decimal' id='dot'> Decimal point (e.g. 4.7uf)</label></div>
							<div>
								<label for='use-mu'><input type='checkbox' name='use-mu' id='use-mu'> Use &mu; in place of "u" in farads</label></div>
							<div>
								<label for='use-ohms'><input type='checkbox' name='use-ohms' id='use-ohms'> Add ohm symbol (<span class='larger'>&#8486;</span>) to resistors values</label></div>
						</div>

						<div class='col-1 bottom-border'>
							<div class='bold'>Unit Case</div>
							<p class='caption'>Note: to count components listed with potentially different naming conventions, the original formatting cannot be retained for resistors and capacitors.</p>

						</div>
						<div class='col-1 g-center'>
							<label for='upper'>
								<input type='radio' name='unit-case' value='upper' id='upper' checked> <strong>Uppercase</strong> units and prefixes (e.g. 4.7UF, 4U7)
							</label><br>
							<label for='lower'>
								<input type='radio' name='unit-case' value='lower' id='lower'> <strong>Lowercase</strong> units and prefixes (e.g. 4.7uf, 4u7)
							</label>
						</div>

						<div class='col-1'>
							<div class='bold'>Page (Paper) Size</div>
							<p class='caption'>This will not effect the browser view of the output but will set the output up to be printed.</p>
						</div>
						<div class='col-1 g-center'>

							<input type='radio' name='page-size' value='usletter' id='usletter' checked> <label for='usletter'>US Letter (8.5"x11")
							</label><br>

							<input type='radio' name='page-size' value='indexcard' id='indexcard'> <label for='indexcard'>Index Card (3"x5")
							</label><br>


						</div>

				</fieldset>



				<button id='generate'>Generate</button>

			</form>

		</div>
	</div>

	<script src="condition.js"></script>

</body>

</html>
