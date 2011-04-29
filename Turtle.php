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
    protected $_currentAngle = -90;
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
        $commands = array_filter($commands);

        // join all the commands into one long space separated string
        $temp = implode(' ', $commands);
        // and then finally tokenise everything into one long sequential array
        $tokens = explode(' ', $temp);
        
        // and finally finally get rid of any blank lines left by the trimming procedure
        $tokens = array_filter($tokens);

        return $tokens;
    }

    public function _parseTokens($input) {
        $tokens = $input;
        $expectedVariables = array();
        $passedInVariables = array();
        
        if (array_key_exists('commands', $input)) {
            $tokens = $input['commands'];
        }
        
        if (array_key_exists('expectedVariables', $input)) {
            $expectedVariables = $input['expectedVariables'];
        }
        
        if (array_key_exists('passedInVariables', $input)) {
            $passedInVariables = $input['passedInVariables'];
        }
        
        // now, lets start doing something with these tokens
        $tokenPointer = 0;
        while ($tokenPointer < sizeof($tokens)) {
            $newX = $this->_currentX;
            $newY = $this->_currentY;
            $commandIsDrawable = true;

            $command = $tokens[$tokenPointer];
            $argument = null;

            if (in_array($command, $this->_commandsNeedingArguments)) {
                $tokenPointer++;
                $argument = $tokens[$tokenPointer];
                
                if ( ':' === substr($argument, 0, 1) ) {
                    $argument = $this->_evaluateToken($tokens, $tokenPointer, $passedInVariables);
                }
            }

            switch ($command) {
                case 'FD':
                    $newX = $this->_currentX + cos(deg2rad($this->_currentAngle)) * $argument;
                    $newY = $this->_currentY + sin(deg2rad($this->_currentAngle)) * $argument;
                    break;
                case 'RT':
                    $commandIsDrawable = false;
                    $this->_currentAngle += $argument;
                    break;
                case 'BK':
                    $newX = $this->_currentX - cos(deg2rad($this->_currentAngle)) * $argument;
                    $newY = $this->_currentY - sin(deg2rad($this->_currentAngle)) * $argument;
                    break;
                case 'LT':
                    $commandIsDrawable = false;
                    $this->_currentAngle -= $argument;
                    break;
                case 'PD':
                    $commandIsDrawable = false;
                    $this->_isPenDown = true;
                    break;
                case 'PU':
                    $commandIsDrawable = false;
                    $this->_isPenDown = false;
                    break;
                case 'SETC':
                    $commandIsDrawable = false;
                    
                    $colors = explode(',', $argument);
                    $colors = array_pad($colors, 3, 0);
                    $this->_color = imagecolorallocate(
                        $this->_image, 
                        $colors[0], $colors[1], $colors[2]
                    );
                    break;
                case 'REPEAT':
                    $commandIsDrawable = false;
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
                                    array(
                                        'commands' => $commands,
                                        'passedInVariables' => $passedInVariables,
                                    )
                                );
                            }
                            continue;
                        }
                        
                        
                        $tokenPointer++;
                    }
                    
                    break;
                case 'TO':
                    $commandIsDrawable = false;
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
                    $commandIsDrawable = false;

                    if ( '"' !== substr($argument, 0, 1) ) {
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
                            $variables[$expectedVariable] = $tokens[$tokenPointer];
                        }
                        
                        $this->_parseTokens(
                            array_merge(
                                $this->_userDefinedCommands[$command],
                                array(
                                    'passedInVariables' => $variables
                                )
                            )
                        );
                    } else {
                        throw new Exception("$command is undefined.");
                    }
            }

            // now, lets draw a line
            if ($this->_isPenDown && $commandIsDrawable) {
                imageline(
                    $this->_image, 
                    $this->_currentX, $this->_currentY,
                    $newX, $newY,
                    $this->_color
                );
            }

            // finally:
            //  update the current x and y
            $this->_currentX = $newX;
            $this->_currentY = $newY;
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
        
        return $tokens[$tokenPointer];
    }
    
}
?>