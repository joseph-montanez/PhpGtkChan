<?php
namespace Gorilla3D;

class Process
{
    protected $_cwd;
    protected $_command;
    protected $_pipes;
    protected $_errors;
    
    protected static $_spec = array(
        0 => array("pipe", "r"), 
        1 => array("pipe", "w"), 
        2 => array("pipe", "w")
    );
    
    private $__process;
    private $__written;
    
    /**
     * Sets the current working directory
     * 
     * @param string $command Command to process
     * @param string $cwd The current working directory to run the command on 
     */
    public function __construct($command = null, $cwd = null)
    {
        if ($cwd !== null) {
            $this->setCwd($cwd);
        }
        
        if ($command !== null) {
            $this->setCommand($command);
        }
    }
    
    public function __destruct()
    {
        $this->close();
        unset($this->__process);
    }
    
    /**
     * Sets the command to process
     * 
     * @return Process 
     */
    public function setCommand($command)
    {
        $this->_command = $command;
        return $this;
    }
    
    /**
     * Gets the command to process
     * 
     * @return string 
     */
    public function getCommand()
    {
        return $this->_command;
    }
    
    /**
     * Sets the current working directory
     * 
     * @return Process 
     */
    public function setCwd($path)
    {
        $this->_cwd = $path;
        return $this;
    }
    
    /**
     * Gets the current working directory
     * 
     * @return string 
     */
    public function getCwd()
    {
        return $this->_cwd;
    }
    
    /**
     * Starts up the process and opens pipes
     * 
     * @param string $command Command to process
     * @return Process 
     */
    public function open($command = null) 
    {
        if ($command !== null) {
            $this->setCommand($command);
        }
        $this->__process = proc_open(
            $this->getCommand(), 
            self::$_spec, 
            $this->_pipes, 
            $this->_cwd, 
            null
        );
        return $this;
    }
    
    /**
     * Writes to pipe
     * 
     * @param string $string
     * @return Process 
     */
    public function write($string)
    {
        // If there no newline in the pipe then add one! Its very dangerous
        // to not end the write pipe before reading :( If there is another way
        // around this then please let me know
        if (!strstr($this->__written, "\n")) {
            $string .= "\n";
        }
        fwrite($this->_pipes[0], $string);
        return $this;
    }
    
    /**
     * Read a line from incoming pipe
     * 
     * @param string &$output The referenced output
     * @param int &$length How far out to read
     * @return Process 
     */
    public function readError(&$output, $length = 4096)
    {
        $output = fgets($this->_pipes[2], 4096);
        return $this;
    }
    
    /**
     * Read a line from incoming pipe
     * 
     * @param string &$output The referenced output
     * @param int &$length How far out to read
     * @return Process 
     */
    public function readLine(&$output, $length = 4096)
    {
        $output = fgets($this->_pipes[1], 4096);
        return $this;
    }
    
    /**
     * Read until eof from incoming pipe
     * 
     * @param string &$output The referenced output
     * @return Process 
     */    
    public function read(&$output) 
    {
        $buffer = true;
        $output = '';
        while ($buffer) {
            $this->readLine($buffer);
            $output .= $buffer;
        }
    }
    
    /**
     * Safely close all pipes and then the process
     * 
     * @return Process
     */
    public function close()
    {
        if (is_resource($this->__process) and isset($this->_pipes)) {
            fclose($this->_pipes[0]);
            fclose($this->_pipes[1]);
            fclose($this->_pipes[2]);
            $this->_return = proc_close($this->__process);
        }
        return $this;
    }
}
?>
