<?

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

define('PB_RANDOM_STRING_ALL', "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
define('PB_RANDOM_STRING_NUMLOWER', "0123456789abcdefghijklmnopqrstuvwxyz");
define('PB_RANDOM_STRING_NUMUPPER', "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ");
define('PB_RANDOM_STRING_NUM', "0123456789");
define('PB_RANDOM_STRING_ALPHABET', "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
define('PB_RANDOM_STRING_LOWER', "abcdefghijklmnopqrstuvwxyz");
define('PB_RANDOM_STRING_UPPER', "ABCDEFGHIJKLMNOPQRSTUVWXYZ");

function pb_random_string($length_ = 20, $characters_ = PB_RANDOM_STRING_ALL){
	$characters_length_ = mb_strlen($characters_, 'UTF-8');
	$random_string_ = "";
	for($i_ = 0; $i_ < $length_; $i_++){
		$random_string_ .= mb_substr($characters_, rand(0, $characters_length_ - 1), 1, 'UTF-8');
	}
	return $random_string_;
}


function pb_append_url($base_url_, $path_){

	$check_path_ = strpos($path_, "/");
	if($check_path_ !== false && $check_path_ == 0){
		$path_ = substr($path_, 1);
	}

	if((strrpos($base_url_, "/") + 1) >= mb_strlen($base_url_)){
		$base_url_ .= $path_;
	}else{
		$base_url_ .= "/".$path_;
	}
	
	return $base_url_;
}
function pb_make_url($base_url_, $params_ = array()){
	$concat_char_ = "?";

	foreach($params_ as $key_ => $value_){
		if(strpos($base_url_, "?") > 0)
			$concat_char_ = "&";
		else $concat_char_ = "?";

		$base_url_ .= $concat_char_.$key_."=".urlencode($value_);			
	}

	return $base_url_;
}

function pb_current_time( $type = "mysql", $gmt = 0 ) {
	$gmt_offset_ = date('Z');
	switch ( $type ) {
		case 'mysql':
			return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( $gmt_offset_ ) ) );
		case 'timestamp':
			return ( $gmt ) ? time() : time() + ( $gmt_offset_ );
		default:
			return ( $gmt ) ? date( $type ) : date( $type, time() + ( $gmt_offset_ ) );
	}
}

function pb_selected($selected_, $current_ = true, $echo_ = true){
	if($selected_ !== $current_) return;
	if($echo_) echo "selected='selected'";
	return "selected='selected'";
}
function pb_checked($checked_, $current_ = true, $echo_ = true ){
	if($checked_ !== $current_) return;
	if($echo_) echo "checked='checked'";
	return "checked='checked'";
}
function pb_disabled($val_, $current_ = true, $echo_ = true ){
	if($val_ !== $current_) return;
	if($echo_) echo "disabled='disabled'";
	return "disabled='disabled'";
}

function pb_alphabet_sequence($length_, $sequence_){
	$alphabet_length_ = strlen(PB_RANDOM_STRING_LOWER);

	$temp_val_ = $length_;
	$digit_ = 1;

	while(true){
		$check_ = $temp_val_ - pow($alphabet_length_, $digit_) - ($alphabet_length_);
		
		if($check_ < 1.0){
			break;
		}
		
		++$digit_;
	}

	$count_ = 0;
	$result_ = null;
	while($count_ <= $sequence_){
		if($result_ === null) $result_ = "a";
		else ++$result_;
		++$count_;
	}

	return str_pad($result_, $digit_, "a", STR_PAD_LEFT);
}
	
?>