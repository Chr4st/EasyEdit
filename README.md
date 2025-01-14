# EasyEdit

[![](https://poggit.pmmp.io/shield.state/EasyEdit)](https://poggit.pmmp.io/p/EasyEdit)

Feature-rich WorldEditor for PocketMine-MP

## Features

- large variety of commands
- High performance:
    - async editing, allowing the server to run normally while editing in the background
    - low memory consumption by splitting your actions into multiple smaller edits
- support for unique Patterns
    - set blocks in effectively infinite ways
    - see [Pattern Documentation](#Patterns)
- selection axe & brushes
- undo & redo your actions
- tile support
- load & save java selections (load MCEdit & Sponge format, save to Sponge)
- rotate & flip support

## Commands

\<argument> - required Argument<br>
[argument] - optional Argument

Selection:

Command | Description | Permission | Aliases/Notice
---|---|---|---
//pos1 [x] [y] [z]| Set the first Position | easyedit.select | //1<br>left click a block in creative with a wooden axe
//pos2 [x] [y] [z]| Set the first Position | easyedit.select | //2<br>break a block in creative with a wooden axe
//extend [count\|vertical] | Extend the selected Area | easyedit.select | //expand<br>Look into the direction you want to extend to
//set \<pattern> | Set the selected Area | easyedit.edit
//replace \<block> \<pattern> | Replace the selected Area | easyedit.edit
//naturalize \[pattern] \[pattern] \[pattern] | Naturalize the selected Area | easyedit.edit
//smooth | Smooth the selected Area | easyedit.edit
//center [block] | Set the center Blocks (1-8) | easyedit.edit | //middle
//walls [pattern] | Set walls of the selected area | easyedit.edit | //wall
//sides [pattern] | Set sides of the selected area | easyedit.edit | //side
//move \<count> | Move the selected area | easyedit.edit | Look into the direction you want the selected blocks to move into
//stack \<count> | Stack the selected area | easyedit.edit | Look into the direction you want the selected blocks to stack into
//istack \<count> | Stack the selected area without overwriting existing terrain | easyedit.edit
//count [radius] | Count selected blocks | easyedit.select
//extinguish [radius] | Extinguish fire | easyedit.edit | //ext

History:

Command | Description | Permission | Aliases/Notice
---|---|---|---
//undo [count] | Revert your latest change | easyedit.history easyedit.edit
//undo \<target> [count] | Revert targets latest change | easyedit.history easyedit.edit easyedit.edit.other | Can be disabled via config
//redo [count] | Revert your latest undo | easyedit.history easyedit.edit
//redo \<target> [count] | Revert targets latest undo | easyedit.history easyedit.edit easyedit.edit.other | Can be disabled via config

Clipboard:

Command | Description | Permission | Aliases/Notice
---|---|---|---
//copy | Copy the selected Area | easyedit.clipboard
//cut | Cut the selected Area | easyedit.clipboard easyedit.edit | Copies and replaces with air
//paste | Paste the Clipboard | easyedit.clipboard easyedit.edit
//insert | Insert the Clipboard | easyedit.clipboard easyedit.edit | Paste only into air blocks
//rotate | Rotate the Clipboard | easyedit.clipboard | Rotates by 90 Degrees
//flip | Flip the Clipboard | easyedit.clipboard | Flips on axis you look on, always uses selected point as "mirror"
//loadschematic \<schematicName> | Load a saved schematic | easyedit.readdisk easyedit.clipboard | //load
//saveschematic \<schematicName> | Save your clipboard into a schematic | easyedit.writedisk easyedit.clipboard | //save

Generation:

Command | Description | Permission | Aliases/Notice
---|---|---|---
//sphere \<radius> \<pattern> | Set a sphere | easyedit.generate easyedit.edit | //sph
//hsphere \<radius> \<pattern> [thickness] | Set a hollow sphere | easyedit.generate easyedit.edit | //hsph //hollowsphere
//cylinder \<radius> \<height> \<pattern> | Set a cylinder | easyedit.generate easyedit.edit | //cy
//hcylinder \<radius> \<height> \<pattern> [thickness] | Set a hollow cylinder | easyedit.generate easyedit.edit | //hcy //hollowcylinder
//noise [type] | Generate with a simple noise function | easyedit.generate easyedit.edit

Utility:

Command | Description | Permission | Aliases/Notice
---|---|---|---
//brush sphere \[radius] \[pattern]<br>//brush smooth \[radius]<br>//brush naturalize \[radius] \[topBlock] \[middleBlock] \[bottomBlock]<br>//brush cylinder \[radius] \[height] \[pattern] | Create a new Brush | easyedit.brush <br> (To use: easyedit.edit)| //br
//blockinfo | Get a blockinfo stick | easyedit.util | //bi
//status | Check on the EditThread | easyedit.manage
//cancel | Cancel the current task | easyedit.manage
//benchmark | Start a benchmark | easyedit.manage | This will create a temporary world and edit a few preset actions

## Patterns

### Block Patterns

Block Patterns are just blocks, they just consist out of the name of the block or its numeric ID

Examples:<br>

- stone
- 4
- red_wool
- stone:1

The keyword "hand" represents the block you hold in your hand (or air for items/nothing) and can be used like normal blocks

### Random Pattern

The Random Pattern as it name suggests selects a random Pattern<br>
The patterns are separated by a comma and can be used in any order

Examples:<br>
```dirt,stone,air```<br>
```red_wool,green_wool,yellow_wool,orange_wool```

### Weighted Patterns

When one pattern should be more likely than another, the weighted notation can be used: <br>
```propability%pattern```

Example: <br>
```70%dirt,30%grass```

If the sum of given percentages is smaller than 100, there is a chance to not change anything:<br>
```10%stone,10%dirt``` - 80% of the selected area will not be affected

If the sum of given percentages is greater than 100, given probabilities are scaled accordingly:<br>
```150%stone,50%dirt``` - 75% will be set to stone, 25% will be set to dirt

## Complex Patterns

Complex patterns follow strict rules and as such allow the creation of complex structures

Usage of Complex Patterns: patternName;arg1;arg2...(subPattern1,subPattern2...)

Complex patterns can be chained together with dots to create a logic construct: <br>
```block;stone(dirt).grass``` - Replace all stone blocks with dirt and everything else with grass

Chained constructs are executed from left to right until a valid block is found, otherwise the block will stay unaffected

They can also be used with the default comma notation and are selected randomly, or in combination: <br>
```stone,block;stone(dirt).grass,wool``` - Places either stone, wool or following the pattern described above

### Logic Patterns

These Patterns allow control over when to set certain blocks

These are especially useful in complex structures or even nested: <br>
```odd;x(odd;z(black_wool).white_wool).odd;z(white_wool).black_wool``` - A 2d checkers pattern

\<argument> - required Argument<br>
[argument] - optional Argument<br>
patterns - children patterns, can be separated by a comma

Pattern | Description
---|---
block;\<block>(patterns) | Executes Patterns if the block is the same as the specified block (like in //replace)
above;\<block>(patterns) | Executes Patterns if the block is above the specified block
around;\<block>(patterns) | Executes Patterns if the block is next to the specified block
below;\<block>(patterns) | Executes Patterns if the block is below the specified block
not(condition(patterns)) | Executes Patterns of next Pattern is false (only works when nested)
odd;\[x];\[y];\[z](patterns) | Executes Patterns if the block is at odd coordinates at x, y and z Axis, the x, y and z can be left out (only given ones will be checked)
even;\[x];\[y];\[z](patterns) | Executes Patterns if the block is at even coordinates (see odd for more info)
divisible;\<number>;\[x];\[y];\[z](patterns) | Executes Patterns if the block is at coordinates which are divisible by the given number (see odd for more info)
walls;\[thickness](patterns) | Executes Patterns if the block is one of the walls of the selections
sides;\[thickness](patterns) | Executes Patterns if the block is one of the sides of the selections (walls + bottom and top)

### Functional Patterns

These Patterns have a unique use and are mostly used for the default commands

\<argument> - required Argument<br>
[argument] - optional Argument<br>
patterns - children patterns, can be separated by a comma

Pattern | Description
---|---
smooth | makes your terrain smoother
naturalize(\[pattern],\[pattern],\[pattern]) | makes your selection more natural (1 layer pattern1, 3 layers pattern2, pattern3)