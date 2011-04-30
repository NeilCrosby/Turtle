<?php

require_once 'PHPUnit/Framework.php';
require_once 'Turtle.php';

class TurtleTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @dataProvider validCommandProvider
     **/
    public function testValidCommandsResultInNoErrorAndGiveExpectedOutput($input, $expected) {
        $turtle = new Turtle($input);
        $this->assertEquals(
            $expected,
            $turtle->getNormalisedTokens()
        );
    }
    
    /**
     * @dataProvider validCommandProvider
     **/
    public function testValidCommandsCreateExpectedImage($input, $expected) {
        $turtle = new Turtle($input);
        
        $expectedFilename = ( '' === $expected )
                          ? 'empty-output'
                          : str_replace(
                              array(' ', ':', '"'), 
                              array('-', 'c', 'q'), 
                              $expected
                            );
        $expectedFilename = "test-images/$expectedFilename.png";
        
        if (!file_exists($expectedFilename)) {
            $this->markTestSkipped(
                "No test image available for: $expected"
            );
        }
        
        // md5 is being performed on both the outputs of the imges to make
        // the phpunit output more palettable when there is an error 
        $this->assertEquals(
            md5(file_get_contents($expectedFilename)),
            md5($turtle->getImage()),
            "Generated image does not match expected image."
        );
    }
    
    /**
     * @dataProvider invalidCommandProvider
     * @expectedException Exception
     **/
    public function testInvalidCommandsResultInException($input) {
        $turtle = new Turtle($input);
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
    public function testCommandsAreNormalisedToASingleLine($input, $expected) {
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
            $this->validMultiLineCommandsProvider(),
            $this->validCommandsWithCommentsProvider()
        );
    }
    
    public function validSingleLineCommandsProvider() {
        return array(
            array("forward 27", 'FD 27'),
            array("back 70", 'BK 70'),
            array("left 45", 'LT 45'),
            array("right 90", 'RT 90'),
            array("fd 70", 'FD 70'),
            array("bk 75", 'BK 75'),
            array("lt 24", 'LT 24'),
            array("rt 9", 'RT 9'),
            array("FORWARD 27", 'FD 27'),
            array("BACK 70", 'BK 70'),
            array("LEFT 45", 'LT 45'),
            array("RIGHT 90", 'RT 90'),
            array("FD 70", 'FD 70'),
            array("BK 75", 'BK 75'),
            array("LT 24", 'LT 24'),
            array("RT 9", 'RT 9'),
            array("penup", 'PU'),
            array("pendown", 'PD'),
            array("PENUP", 'PU'),
            array("PENDOWN", 'PD'),
            array('pu', 'PU'),
            array('pd', 'PD'),
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
                , 'FD 50 RT 90 FD 50 RT 90 FD 20 PU FD 25 PD FD 25 RT 60 FD 70'),
            array (<<<LOGO
repeat 55 [ 
    rt 15 
    repeat 8 [ 
        fd 30 
        rt 45
    ]
]
LOGO
                , 'REPEAT 55 [ RT 15 REPEAT 8 [ FD 30 RT 45 ] ]'
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
                , 'TO CHAIR REPEAT 4 [ FD 10 RT 90 ] FD 20 END TO STAR REPEAT 8 [ CHAIR RT 45 PU FD 10 PD ] END PU RT 90 FD 50 LT 90 PD REPEAT 4 [ PU FD 50 LT 90 FD 50 PD STAR ]'
            ),
            array(
                <<<LOGO
forward 20
setc 127
forward 20
setc "0,255,0
forward 20
LOGO
                , 'FD 20 SETC 127 FD 20 SETC "0,255,0 FD 20'
            ),
            array(
                <<<LOGO
                TO hexagon
                        REPEAT 6 [ FD 50 RT 60 ]
                    END


                REPEAT 12 [ 
                    SETC "0,127,0
                    hexagon RT 15 
                    SETC "255,0,0
                    hexagon RT 15 
                ]
LOGO
                , 'TO HEXAGON REPEAT 6 [ FD 50 RT 60 ] END REPEAT 12 [ SETC "0,127,0 HEXAGON RT 15 SETC "255,0,0 HEXAGON RT 15 ]'
            ),
            array(
                <<<LOGO
TO hexagon :size :color
    SETC :color
    REPEAT 6 [ FD :size RT 60 ]
END

REPEAT 12 [ 
    hexagon 50 "0,127,0 
    RT 15 
    hexagon 30 "255,0,0 
    RT 15 
]
LOGO
                , 'TO HEXAGON :SIZE :COLOR SETC :COLOR REPEAT 6 [ FD :SIZE RT 60 ] END REPEAT 12 [ HEXAGON 50 "0,127,0 RT 15 HEXAGON 30 "255,0,0 RT 15 ]'
            ),
            array(
                <<<LOGO
BK 5;PENUP
MAKE "x 90
RT :x
FD 25
LOGO
                , 'BK 5 MAKE "X 90 RT :X FD 25'
            ),
            array(
                <<<LOGO
BK 5
MAKE "y 60
MAKE "x :y
RT :x
FD 25
LOGO
                , 'BK 5 MAKE "Y 60 MAKE "X :Y RT :X FD 25'
            ),
            array(
                <<<LOGO
BK 5
MAKE "x "45
RT :x
FD 25
LOGO
                , 'BK 5 MAKE "X "45 RT :X FD 25'
            ),
            array(
                <<<LOGO
MAKE "x sum 20 70
RT :x
FD 25
rt 90
fd sum 15 20
LOGO
                , 'MAKE "X SUM 20 70 RT :X FD 25 RT 90 FD SUM 15 20'
            ),
            array(
                <<<LOGO
to spiral :size :angle
    forward :size
    right :angle
end

make "size 5
make "angle 91
repeat 50 [ 
    spiral :size :angle 
    make "size sum :size 2 
]
LOGO
                , 'TO SPIRAL :SIZE :ANGLE FD :SIZE RT :ANGLE END MAKE "SIZE 5 MAKE "ANGLE 91 REPEAT 50 [ SPIRAL :SIZE :ANGLE MAKE "SIZE SUM :SIZE 2 ]'
            ),
            array(
                <<<LOGO
repeat 4 [
    repeat 3 [
        fd 20
        rt 30
    ]
]
LOGO
                , 'REPEAT 4 [ REPEAT 3 [ FD 20 RT 30 ] ]'
            )
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
                "BK 5;PENUP",
                "BK 5"
            ),
            array(
                <<<LOGO
; another comment
BK 5
LOGO
                , "BK 5"
            ),
            array(
                '; a comment on its own',
                ''
            ),
        );
    }

    public function invalidCommandProvider() {
        return array(
            array("FISHCAKE forward 27"),
        );
    }
}