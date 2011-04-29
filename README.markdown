# A simple Logo parser, built in PHP

This parser was written as part of a competition at work.  The task was to:

> Write a simple interpreter for a simple version of the Logo programming language.

This is my attempt at that.

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





