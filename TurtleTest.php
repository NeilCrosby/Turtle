<?php

require_once 'PHPUnit/Framework.php';
require_once 'Turtle.php';

class TurtleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validCommandProvider
     **/
    public function testValidCommandsResultInNoError($input) {
        $turtle = new Turtle($input);
        $this->assertFalse($turtle->getError());
    }
    
    /**
     * @dataProvider invalidCommandProvider
     **/
    public function testInvalidCommandsResultInError() {
        $turtle = new Turtle($input);
        $this->assertType('string', $turtle->getError());
    }
    
    /**
     * @dataProvider validCommandsWithCommentsProvider
     **/
    public function testCommentsAreRemoved($input, $expected) {
        $turtle = new Turtle($input);
        $this->assertEquals($expected, $turtle->getNormalisedTokens());
    }
    
    /**
     * @dataProvider validMultiLineCommandsProvider
     **/
    public function testCommandsAreNormalisedToASingleLine($input) {
        $turtle = new Turtle($input);

        $this->assertFalse(
            strpos("\n", $turtle->getNormalisedTokens()),
            'Normalised string should not contain any newlines.'
        );
    }
    
/* ************************************************************************ *\
 * AND NOW FOR ALL THE DATA PROVIDERS                                       *
\* ************************************************************************ */
    
    public function validCommandProvider() {
        return array_merge(
            $this->validSingleLineCommandsProvider(),
            $this->validMultiLineCommandsProvider()
        );
    }
    
    public function validSingleLineCommandsProvider() {
        return array(
            array("forward 27"),
            array("back 70"),
            array("left 45"),
            array("right 90"),
            array("fd 70"),
            array("bk 75"),
            array("lt 24"),
            array("rt 9"),
            array("FORWARD 27"),
            array("BACK 70"),
            array("LEFT 45"),
            array("RIGHT 90"),
            array("FD 70"),
            array("BK 75"),
            array("LT 24"),
            array("RT 9"),
            array("penup"),
            array("pendown"),
            array("PENUP"),
            array("PENDOWN"),
        );
    }
    
    public function validMultiLineCommandsProvider() {
        return array(
            array(<<<LOGO
forward 50 
right 90 
forward 50 
right 90 forward 20
penup 
forward 25; this is a comment
pendown 
forward 25
; another comment
right 60 
forward 70
LOGO
            ),
            array (<<<LOGO
repeat 55 [ 
    rt 15 
    repeat 8 [ 
        fd 30 
        rt 45
    ]
]
LOGO
            ),
            array(<<<LOGO
to chair
REPEAT 4  [ FD 10 RT 90 ]  FD 20
end

to star
repeat 8 [ chair rt 45 pu fd 10 pd ]
end

pu
rt 90
fd 50
lt 90
pd


repeat 4 [
penup
fd 50
lt 90
fd 50
pendown
star
]
LOGO
            ),
        );
    }
    
    public function validCommandsWithCommentsProvider() {
        return array(
            array(
                "FD 50;a comment",
                "FD 50"
            ),
            array(
                "forward 70 ; a comment",
                "FD 70"
            ),
            array(
                "; another comment",
                ""
            ),
            array(
                "BK 5;PENUP",
                "BK 5"
            ),
        );
    }

    public function invalidCommandProvider() {
        return array(
            array("FISHCAKE forward 27"),
        );
    }
}