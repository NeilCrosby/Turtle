# Simple Turtle Graphics Parser (written in PHP)

This parser was written as part of a competition at work.  The task was to:

> Write a simple interpreter for a simple version of the Logo programming language.

This is my attempt at that.

An installed version of the code can be found at http://projects.thecodetrain.co.uk/turtle/

## Syntax

Any commands may be written either in upper or lowercase. Any text in brackets is a shortened form of the command and may be used instead of the long form.

### FORWARD (FD) _number_

Causes the turtle to move forwards by _number_ pixels.

    FD 50
    
### BACK (BK) _number_

Causes the turtle to move back by _number_ pixels.

    BK 50
    
### LEFT (LT) _number_

Causes the turtle to rotate left (anti-clockwise) by _number_ degrees from its previous bearing.

    LT 45

### RIGHT (RT) _number_

Causes the turtle to rotate right (clockwise) by _number_ degrees from its previous bearing.

    RT 90

### PENUP (PU)

Lifts the turtle's pen. Nothing new will be drawn on the canvas until `PENDOWN` has been called.

    PU
    
### PENDOWN (PD)

Lowers the turtle's pen. When the turtle's pen is down, it will draw on the canvas.

    PD

### ; _comment_

A semicolon is used by Logo to denote a comment.  Anything after the semicolon on a line is treated as if it did not exist by the parser.

    FD 50 ; a comment
    LT 90
    ; another comment
    FD 45

The previous code would be parsed as:

    FD 50
    LT 90
    FD 45

### SETCOLOR (SETC) _number or comma separated numbers_

Sets the color of the pen.  The numbers available to be used are 0-255. If a single number is given, then the pen color will vary between black (0) and bright red (255). If three comma separated numbers are given, then any RGB color can be defined.

    SETC 127
    SETC 255,170,187
    
NB: I am not comfortable about how the comma separated color setting currently works.  This may end up changing at some point. Unfortunately I didn't find anything in any of the documentation I read that dealt with "big" colours.
    
### REPEAT _number_ _list_

Repeats a _list_ of commands a given _number_ of times.

In Logo, a list is surrounded by square brackets, and can contain other lists.

The following will create a simple hexagon:

    REPEAT 6 [ FD 50 RT 60 ]
    
The following will create a simple hexagon twelve times, with a 30 degree rotation between each one:

    REPEAT 12 [
        REPEAT 6 [ FD 50 RT 60 ]
        RT 30
    ]
    
### TO _procedure-name_ _commands_ END

Creates a procedure, which can be called by name from later places within your script.

Taking our hexagon example (and adding in a bit of colour swapping for good measure):

    TO hexagon
        REPEAT 6 [ FD 50 RT 60 ]
    END

    REPEAT 12 [ 
        SETC 0,127,0
        hexagon RT 15 
        SETC 255,0,0
        hexagon RT 15 
    ]
    
A procedure may also take variables. If we wanted our `hexagon` procedure to take a `size` and `color` as parameters, we could do the following:

    TO hexagon :size :color
        SETC :color
        REPEAT 6 [ FD :size RT 60 ]
    END

    REPEAT 12 [ 
        hexagon 50 0,127,0 
        RT 15 
        hexagon 30 255,0,0 
        RT 15 
    ]
    
### MAKE "_variable-name_ _word_

Use the `MAKE` command to create a variable. For example:

    MAKE "x 45 ; set x to 45
    LT :x      ; rotate the turtle left 45 degrees
    FD 50
    
Variables can also be made from other variables:

    MAKE "y 60 ; set y to 60
    MAKE "x :y ; x will take the value of y
    RT :x      ; rotate the turtle right 60 degrees
    FD 50

### SUM _number_ _number_

Sums two values together

    MAKE "x 60
    MAKE "y 30
    MAKE "z SUM :x :y ; z becomes 90
    MAKE "z SUM 60 -40 ; z becomes 20

## Tests

Lots of tests have been written, and can be run on the commandline as follows:

    phpunit TurtleTest

## Further reading

A lot of the choices about how the Logo language parser works came from various pieces of documentation around the web. This documentation included:

* http://en.wikipedia.org/wiki/Logo_(programming_language)#Syntax
* http://el.media.mit.edu/logo-foundation/logo/turtle.html
* http://groups.yahoo.com/group/LogoForum/ (and the ATARI Logo doc file found within the files section)
