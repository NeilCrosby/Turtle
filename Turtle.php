<?php

class Turtle {


    protected $_commands = array(
        'FORWARD' => 'FD', 
        'BACK'    => 'BK', 
        'RIGHT'   => 'RT', 
        'LEFT'    => 'LT', 
        'PENUP'   => 'PU', 
        'PENDOWN' => 'PD',
        'REPEAT'  => 'REPEAT',
        'TO'      => 'TO',
        'END'     => 'END',
        'SETCOLOR'=> 'SETC',
        'MAKE'    => 'MAKE',
    );

    protected $_commandsNeedingArguments = array(
        'FD',
        'BK',
        'RT',
        'LT',
        'REPEAT',
        'SETC',
        'MAKE',
    );
    
    protected $_userDefinedCommands = array();
    
    protected $_currentX = 100;
    protected $_currentY = 100;
    protected $_currentAngle = 270;
    protected $_isPenDown = true;
              
    protected $_tokens;
    protected $_image;
    protected $_color;
    
    public function __construct($input, $width=200, $height=200) {
        $this->_tokens = $this->_getTokens($input);
        
        $this->_width = $width;
        $this->_height = $height;
        
        $this->_currentX = intval($width / 2 );
        $this->_currentY = intval($height / 2 );

        $this->_image = imagecreatetruecolor($this->_width, $this->_height);
        $this->_color = imagecolorallocate($this->_image, 255, 0, 0);
        
        $this->_parseTokens($this->_tokens);
    }
    
    public function getImage() {
        ob_start();
        imagepng($this->_image);
        $outputImage = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($this->_image);
        
        return $outputImage;
    }
    
    public function getNormalisedTokens() {
        return implode(' ', $this->_tokens);
    }
    
    protected function _getTokens($input) {
        $commands = array(); 


        $tempCommands = explode("\n", $input);
        foreach ($tempCommands as $key=>$value) {
            // first, remove anything after semi colons on any line
            $semiColonPosition = strpos($value, ';');
            if ( false !== $semiColonPosition) {
                $value = substr($value, 0, $semiColonPosition);
            }
            
            $value = str_replace('[', ' [ ', $value);
            $value = str_replace(']', ' ] ', $value);

            $tempInnerCommands = explode(' ', $value);
            
            foreach ($tempInnerCommands as $tempInnerCommand) {
                $commands[] = $tempInnerCommand;
            }
        }
        
        foreach ($commands as $key=>$value) {
            // and try all lines to get rid of whitespace /  newlines
            $value = trim(strtoupper($value));

            // normalise the resulting command value to a short command
            foreach ($this->_commands as $longCommand => $shortCommand) {
                $value = preg_replace(
                    "/^$longCommand/",
                    $shortCommand,
                    $value
                );
            }

            // and put the result back into the commands array
            $commands[$key] = $value;
        }

        // get rid of any blank lines left by the  trimming procedure
        $commands = array_filter($commands, array('self', 'reductionCallback'));

        // join all the commands into one long space separated string
        $temp = implode(' ', $commands);
        // and then finally tokenise everything into one long sequential array
        $tokens = explode(' ', $temp);
        
        // and finally finally get rid of any blank lines left by the trimming procedure
        $tokens = array_filter($tokens, array('self', 'reductionCallback'));

        return $tokens;
    }
    
    public function reductionCallback($input) {
        if ( 0 === $input || '0' === $input) {
            return true;
        }
        
        return (bool)$input;
    } 

    public function _parseTokens($tokens, $passedInVariables=array(), $expectedVariables=array()) {
        // now, lets start doing something with these tokens
        $tokenPointer = 0;
        while ($tokenPointer < sizeof($tokens)) {
            $command = $tokens[$tokenPointer];
            $argument = null;

            if (in_array($command, $this->_commandsNeedingArguments)) {
                $tokenPointer++;
                $argument = $this->_evaluateToken($tokens, $tokenPointer, $passedInVariables);
            }

            switch ($command) {
                case 'FD':
                    $newPosition = $this->_getNewPosition($argument);
                    $this->_drawLineTo($newPosition);
                    $this->_currentX = $newPosition['x'];
                    $this->_currentY = $newPosition['y'];
                    break;
                case 'BK':
                    $newPosition = $this->_getNewPosition(-$argument);
                    $this->_drawLineTo($newPosition);
                    $this->_currentX = $newPosition['x'];
                    $this->_currentY = $newPosition['y'];
                    break;
                case 'RT':
                    $this->_changeAngleBy($argument);
                    break;
                case 'LT':
                    $this->_changeAngleBy(-$argument);
                    break;
                case 'PD':
                    $this->_isPenDown = true;
                    break;
                case 'PU':
                    $this->_isPenDown = false;
                    break;
                case 'SETC':
                    $colors = explode(',', $argument);
                    $colors = array_pad($colors, 3, 0);
                    $this->_color = imagecolorallocate(
                        $this->_image, 
                        $colors[0], $colors[1], $colors[2]
                    );
                    break;
                case 'REPEAT':
                    $tokenPointer++;
                    if ('[' !== $tokens[$tokenPointer]) {
                        throw new Exception("REPEAT must be followed by a number, then an opening square bracket");
                    }
                    
                    $tokenPointer++;
                    $startingPoint = $tokenPointer;
                    
                    $openBrackets = 1;
                    // now start looking for the closing bracket to go with this opening one
                    while ($tokenPointer < sizeof($tokens) && 0 !== $openBrackets) {
                        if ( '[' === $tokens[$tokenPointer] ) {
                            $openBrackets++;
                        } else if ( ']' === $tokens[$tokenPointer] ) {
                            $openBrackets--;
                        }
                        
                        if ( 0 === $openBrackets) {
                            for ($i=0; $i < $argument; $i++) {
                                $commands = array_slice(
                                    $tokens,
                                    $startingPoint,
                                    $tokenPointer - $startingPoint
                                );

                                $this->_parseTokens(
                                    $commands,
                                    &$passedInVariables
                                );
                                $newX = $this->_currentX;
                                $newY = $this->_currentY;
                            }
                            continue;
                        }
                        
                        
                        $tokenPointer++;
                    }
                    
                    break;
                case 'TO':
                    $tokenPointer++;
                    
                    $functionName = $tokens[$tokenPointer];
                    if (in_array($functionName, $this->_commands)) {
                        throw new Exception("$functionName is a reserved word, and cannot be used as a function name.");
                    }
                    
                    $tokenPointer++;

                    // now, find any variables that want to be passed into the
                    // procedure
                    $variables = array();
                    while($tokenPointer < sizeof($tokens) && ':' === substr($tokens[$tokenPointer], 0, 1)) {
                        $variables[] = substr($tokens[$tokenPointer], 1);
                        $tokenPointer++;
                    }
                    
                    $startingPoint = $tokenPointer;
                    
                    $foundEnd = false;
                    while ($tokenPointer < sizeof($tokens) && !$foundEnd) {
                        if ( 'END' === $tokens[$tokenPointer] ) {
                            $commands = array_slice(
                                $tokens,
                                $startingPoint,
                                $tokenPointer - $startingPoint
                            );
                            
                            $this->_userDefinedCommands[$functionName] = array(
                                'expectedVariables' => $variables,
                                'commands'  => $commands,
                            );
                            $foundEnd = true;
                            continue;
                        }
                        
                        $tokenPointer++;
                    }
                    
                    if (!$foundEnd) {
                        throw new Exception("Could not find end of function for $functionName");
                        return;
                    }
                
                    break;
                    
                case 'MAKE':
                    if ( '"' !== substr($tokens[$tokenPointer], 0, 1) ) {
                        throw new Exception("MAKE requires its first parameter to be a named variable");
                        return;
                    }
                    
                    $namedVariable = $this->_evaluateToken($tokens, $tokenPointer, $passedInVariables);

                    $tokenPointer++;
                    $value = $this->_evaluateToken($tokens, $tokenPointer, $passedInVariables);
                    
                    $passedInVariables[$namedVariable] = $value;
                    break;
                default:
                    if (array_key_exists($command, $this->_userDefinedCommands)) {
                        $variables = array();
                        foreach ($this->_userDefinedCommands[$command]['expectedVariables'] as $expectedVariable) {
                            $tokenPointer++;
                            $variables[$expectedVariable] = $this->_evaluateToken($tokens, $tokenPointer, $passedInVariables);
                        }
                        
                        $this->_parseTokens(
                            $this->_userDefinedCommands[$command]['commands'],
                            $variables,
                            $this->_userDefinedCommands[$command]['expectedVariables']
                        );
                    } else {
                        throw new Exception("$command is undefined.");
                    }
            }

            // finally:
            //  and always increase the token pointer by one
            $tokenPointer++;
        }
    }
    
    /**
     * Evaluates the token pointed to by tokenPointer and moves tokenPointer
     * to point at the final token used in that evaluation.
     **/
    protected function _evaluateToken($tokens, &$tokenPointer, &$variables) {
        if (!isset($tokens[$tokenPointer])) {
            throw new Exception('Attempting to parse past the end of the script!');
        }
        
        $token = $tokens[$tokenPointer];

        // " means 'the word is evaluated as itself'
        if ( '"' === substr($token, 0, 1) ) {
            return substr($token, 1);
        }
        
        // : means 'the contents of'
        if ( ':' === substr($token, 0, 1) ) {
            $variableName = substr($token, 1);
            if (isset($variables[$variableName])) {
                return $variables[$variableName];
            }
            
            throw new Exception('Unknown variable: '.$variableName);
        }
        
        switch ($token) {
            case 'SUM':
                $tokenPointer++;
                $item1 = $this->_evaluateToken($tokens, $tokenPointer, $variables);
                $tokenPointer++;
                $item2 = $this->_evaluateToken($tokens, $tokenPointer, $variables);
                
                return $item1 + $item2;
                break;
            default:
                // do nothing
        }
        
        return $tokens[$tokenPointer];
    }
    
    protected function _getNewPosition($argument) {
        $newX = $this->_currentX;
        $newY = $this->_currentY;

        $deg = $this->_currentAngle;
        if ( 0 === $deg % 360 ) {
            $newX += $argument;
        } else if ( 90 === $deg % 360 ) {
            $newY += $argument;
        } else if ( 180 === $deg % 360 ) {
            $newX -= $argument;
        } else if ( 270 === $deg % 360 ) {
            $newY -= $argument;
        } else {
            $newX = $this->_currentX + cos(deg2rad($deg)) * $argument;
            $newY = $this->_currentY + sin(deg2rad($deg)) * $argument;
        }
        
        return array(
            'x' => $newX,
            'y' => $newY,
        );
    }
    
    protected function _changeAngleBy($angleChange) {
        $this->_currentAngle += $angleChange;
        
        while ( $this->_currentAngle < 0) {
            $this->_currentAngle += 360;
        }
    }
    
    protected function _drawLineTo($newPosition) {
        if (!$this->_isPenDown) {
            return;
        }
        
//        echo "<p>{$this->_currentX},{$this->_currentY} - {$newPosition['x']},{$newPosition['y']}</p>";
        
        imageline(
            $this->_image, 
            $this->_currentX, $this->_currentY,
            $newPosition['x'], $newPosition['y'],
            $this->_color
        );
        
    }
    
}
?>