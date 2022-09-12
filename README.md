# Pedal Bom / Shopping List Generator
This repository is the source of my guitar pedal bom generator located at https://fine.rip/audiotools/bomcounter/

## Overview
This tool is not a lot more than a few regular expression searches acted on the input text. Matches are sorted into arrays and formatted for output.
The meat of everything happens on `generate.php`

----


### Purpose
This was made highly focused on guitar pedal pcbs, and more specifically [pedalpcb](https://pedalpcb.com) boards, but has worked well with all pedal bills of materials I have used. This is an extended version of a script I wrote for myself a while back, with more output options so mine aren't as imposed.
The output of this tool has also been designed with extensive print stylesheets as printing physical copies on 3"x5" notecards was my own end goal so I wouldn't be fumbling with 5 pages of build docs while looking for a resistor.

### Usage Instructions
Place all but electromechanical components (potentiometers, switches, etc) in the components input. If a component is extremely rare and uses an odd prefix, the electroemchanical input is more forgiving to strange prefixes.
