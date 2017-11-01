<?php

require_once 'Zend/Validate/StringLength.php';    
 
class Zend_Validate_MbstringLength extends Zend_Validate_StringLength 
{ 
    public function isValid($value) 
    { 
        $valueString = (string) $value; 
        $this->_setValue($valueString); 
        $length = mb_strlen($valueString);  // ←ここを変更 
        if ($length < $this->_min) { 
            $this->_error(self::TOO_SHORT); 
        } 
        if (null !== $this->_max && $this->_max < $length) { 
            $this->_error(self::TOO_LONG); 
        } 
        if (count($this->_messages)) { 
            return false; 
        } else { 
            return true; 
        } 
    }    
 }
