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

    protected $_userDefinedCommands = array();
    
    protected $_currentX = 100;
    protected $_currentY = 100;
    protected $_currentAngle = 270;
    protected $_isPenDown = true;
              
    protected $_tokens;
    protected $_image;
    protected $_color;
    
    public function __construct($input, $width=200, $height=200) {
        $this->_width = $width;
        $this->_height = $height;
        
        $this->_currentX = intval($width / 2 );
        $this->_currentY = intval($height / 2 );

        $this->_image = imagecreatetruecolor($this->_width, $this->_height);
        $this->_color = imagecolorallocate($this->_image, 255, 0, 0);
        
        $this->_tokens = $this->_getTokens($input);
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
        $tokens = array(); 

        $lines = explode("\n", strtoupper($input));
        foreach ($lines as $line) {
            // first, remove anything after semi colons on any line
            $semiColonPosition = strpos($line, ';');
            if ( false !== $semiColonPosition) {
                $line = substr($line, 0, $semiColonPosition);
                $line = trim($line);
                
                // if the line then becomes empty, we'll ignore it completely
                if (!$line) {
                    continue;
                }
            }
            
            // then, force spaces around any square brackets
            $line = str_replace('[', ' [ ', $line);
            $line = str_replace(']', ' ] ', $line);

            // then add all the tokens from this line onto the tokens array
            $lineTokens = explode(' ', $line);
            $tokens = array_merge($tokens, $lineTokens);
        }
        
        
        foreach ($tokens as $key=>$value) {
            // normalise the resulting command value to a short command
            foreach ($this->_commands as $longCommand => $shortCommand) {
                $value = preg_replace(
                    "/^$longCommand$/",
                    $shortCommand,
                    trim($value)
                );
            }

            // and put the result back into the tokens array, and trim it
            // to get rid of any unsightly newlines or overabundance of spaces
            $tokens[$key] = trim($value);
        }

        // and finally finally get rid of any blank tokens left by the trimming procedure
        $tokens = array_filter($tokens, array('self', '_reductionCallback'));
        $tokens = array_merge($tokens);

        return $tokens;
    }
    
    /**
     * Callback used by array_filter call in _getTokens to determine whether
     * a token should be removed from the list of tokens or not.
     **/
    protected function _reductionCallback($input) {
        if ( 0 === $input || '0' === $input) {
            return true;
        }
        
        return (bool)$input;
    } 

    public function _parseTokens($tokens, $passedInVariables=array(), $expectedVariables=array()) {
        $tokenPointer = -1;
        while ($tokenPointer < sizeof($tokens) - 1) {
            $command = $this->_getNextToken($tokens, $tokenPointer, $passedInVariables);
        }
    }
    
    protected function _getNextToken($tokens, &$tokenPointer, &$variables, $evaluate=true) {
        $tokenPointer++;
        
        if (!$evaluate) {
            return $tokens[$tokenPointer];
        }
        
        return $this->_evaluateToken($tokens, $tokenPointer, $variables);
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

        if (is_numeric($token)) {
            return $token;
        }
        
        // " means 'the word is evaluated as itself'
        if ( '"' === substr($token, 0, 1) ) {
            return substr($token, 1);
        }
        
        // : means 'the contents of'
        if ( ':' === substr($token, 0, 1) ) {
            $variableName = substr($token, 1);
            if (!isset($variables[$variableName])) {
                throw new Exception('Unknown variable: '.$variableName);
            }
            
            return $variables[$variableName];
        }
        
        switch ($token) {
            /* ************************************************************ *\
             * First, all the commands that return something.               *
            \* ************************************************************ */

            // If the token denotes a list of commands, then we'll end up
            // returning that list of commands
            case '[':
                $startingPoint = $tokenPointer + 1;
                $openBrackets = 1;

                // now start looking for the closing bracket to go with this opening one
                while ($tokenPointer < sizeof($tokens) && 0 !== $openBrackets) {
                    $newToken = $this->_getNextToken($tokens, $tokenPointer, $variables, false);
                    if ( '[' === $newToken ) {
                        $openBrackets++;
                    } else if ( ']' === $newToken ) {
                        $openBrackets--;
                    }
                }

                return $commands = array_slice(
                    $tokens,
                    $startingPoint,
                    $tokenPointer - $startingPoint
                );
                break;
            case 'SUM':
                $item1 = $this->_getNextToken($tokens, $tokenPointer, $variables);
                $item2 = $this->_getNextToken($tokens, $tokenPointer, $variables);

                return $item1 + $item2;
                break;

            /* ************************************************************ *\
             * Now, all the commands that don't return anything, but just   *
             * do something.                                                *
            \* ************************************************************ */
            case 'FD':
                $argument = $this->_getNextToken($tokens, $tokenPointer, $variables);
                $this->_movePointer($argument);
                break;
            case 'BK':
                $argument = $this->_getNextToken($tokens, $tokenPointer, $variables);
                $this->_movePointer(-$argument);
                break;
            case 'RT':
                $argument = $this->_getNextToken($tokens, $tokenPointer, $variables);
                $this->_changeAngleBy($argument);
                break;
            case 'LT':
                $argument = $this->_getNextToken($tokens, $tokenPointer, $variables);
                $this->_changeAngleBy(-$argument);
                break;
            case 'PD':
                $this->_isPenDown = true;
                break;
            case 'PU':
                $this->_isPenDown = false;
                break;
            case 'SETC':
                $argument = $this->_getNextToken($tokens, $tokenPointer, $variables);

                $colors = explode(',', $argument);
                $colors = array_pad($colors, 3, 0);
                $this->_color = imagecolorallocate(
                    $this->_image, 
                    $colors[0], $colors[1], $colors[2]
                );
                break;
            case 'REPEAT':
                $argument = $this->_getNextToken($tokens, $tokenPointer, $variables);
                $list     = $this->_getNextToken($tokens, $tokenPointer, $variables);
                if ( !is_array($list)) {
                    throw new Exception("REPEAT must be followed by a number, then a list");
                }
                
                for ( $i = 0; $i < $argument; $i++ ) {
                    // We're passing $variables by reference since the
                    // code inside a REPEAT block is still within
                    // the scope of the code surrounding it.
                    $this->_parseTokens($list, &$variables);                    
                }
                
                break;
            case 'TO':
                $functionName = $this->_getNextToken($tokens, $tokenPointer, $variables, false);
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
                $namedVariable = $this->_getNextToken($tokens, $tokenPointer, $variables);

                if ( '"' !== substr($tokens[$tokenPointer], 0, 1) ) {
                    throw new Exception("MAKE requires its first parameter to be a named variable");
                }
                
                $value = $this->_getNextToken($tokens, $tokenPointer, $variables);
                
                $variables[$namedVariable] = $value;
                break;
            default:
                if (array_key_exists($token, $this->_userDefinedCommands)) {
                    $variablesToPass = array();
                    foreach ($this->_userDefinedCommands[$token]['expectedVariables'] as $expectedVariable) {
                        $variablesToPass[$expectedVariable] = $this->_getNextToken($tokens, $tokenPointer, $variables);
                    }
                    
                    $this->_parseTokens(
                        $this->_userDefinedCommands[$token]['commands'],
                        $variablesToPass,
                        $this->_userDefinedCommands[$token]['expectedVariables']
                    );
                } else if ( isset($tokens[$tokenPointer - 1]) && 'TO' === $tokens[$tokenPointer - 1]) {
                    // we're trying to define this procedure
                    return $token;
                } else {
                    throw new Exception("$token is undefined.");
                }
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
    
    protected function _movePointer($distance) {
        $newPosition = $this->_getNewPosition($distance);

        if ($this->_isPenDown) {
            imageline(
                $this->_image, 
                $this->_currentX, $this->_currentY,
                $newPosition['x'], $newPosition['y'],
                $this->_color
            );
        }

        $this->_currentX = $newPosition['x'];
        $this->_currentY = $newPosition['y'];
    }
}
?>