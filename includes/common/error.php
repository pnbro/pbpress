<?

if(!defined('PB_DOCUMENT_PATH')){
    die( '-1' );
}

class PBError{
    private $_error_code = null;
    private $_error_title = null;
    private $_error_message = null;

    function __construct($error_code_, $error_title_, $error_message_){
        $this->_error_code = $error_code_;      
        $this->_error_title = $error_title_;
        $this->_error_message = $error_message_;
    }

    function error_code(){
        return $this->_error_code;
    }
    function error_title(){
        return $this->_error_title;
    }
    function error_message(){
        return $this->_error_message;
    }
}

function pb_is_error($obj_){
    return @get_class($obj_) === "PBError";
}
    
?>