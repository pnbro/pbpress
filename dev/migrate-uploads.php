<?php

if(PHP_SAPI !== 'cli'){
	http_response_code(404);
	exit;
}

define('PB_DOCUMENT_PATH', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__DIR__)).'/');
include(PB_DOCUMENT_PATH.'pb-config.php');
include(PB_DOCUMENT_PATH.'includes/common/config.php');
include(PB_DOCUMENT_PATH.'includes/common/utils.php');
include(PB_DOCUMENT_PATH.'includes/common/hook.php');
include(PB_DOCUMENT_PATH.'includes/common/error.php');
include(PB_DOCUMENT_PATH.'includes/common/fileupload.storage.php');

function pb_upload_migration_usage(){
	echo "Usage:\n";
	echo "  php ".__FILE__." [--apply] [--reverse]\n\n";
	echo "Options:\n";
	echo "  --apply    Copy files. Without this option the command is dry-run only.\n";
	echo "  --reverse  Copy private storage files back to legacy public uploads for rollback.\n";
	echo "  --help     Show this help.\n";
}

function pb_upload_migration_excluded($relative_path_){
	$relative_path_ = str_replace('\\', '/', $relative_path_);
	$path_parts_ = explode('/', $relative_path_);
	foreach($path_parts_ as $path_part_){
		if(!strlen($path_part_) || $path_part_[0] === '.') return true;
		if($path_part_ === '_temp' || $path_part_ === '_chunk_text_temp') return true;
	}

	$basename_ = basename($relative_path_);
	return preg_match('/(?:\.part[0-9]+|\.upload|\.assembling_[a-f0-9]+)\z/D', $basename_) === 1;
}

function pb_upload_migration_destination($reverse_, $apply_){
	if($reverse_){
		$legacy_path_ = rtrim(PB_DOCUMENT_PATH, '/\\').DIRECTORY_SEPARATOR.'uploads';
		if(!is_dir($legacy_path_) && $apply_ && !@mkdir($legacy_path_, 0755, true)) return false;
		return is_dir($legacy_path_) ? realpath($legacy_path_) : pb_upload_resolve_candidate_path($legacy_path_);
	}

	if($apply_) return pb_upload_root_path(true);
	$storage_path_ = pb_upload_configured_storage_path();
	return $storage_path_ === false ? false : pb_upload_resolve_candidate_path($storage_path_);
}

function pb_upload_migration_copy_exclusive($source_path_, $target_path_, $destination_root_, $reverse_){
	$destination_root_ = realpath($destination_root_);
	if($destination_root_ === false || !pb_upload_path_string_is_same_or_inside($destination_root_, $target_path_)) return false;

	$relative_target_path_ = ltrim(substr($target_path_, strlen($destination_root_)), '/\\');
	$relative_directory_ = str_replace('\\', '/', dirname($relative_target_path_));
	$target_directory_ = $relative_directory_ === '.'
		? $destination_root_
		: pb_upload_directory($destination_root_, $relative_directory_, $reverse_ ? 0755 : 0750);
	if($target_directory_ === false) return false;
	$target_path_ = $target_directory_.DIRECTORY_SEPARATOR.basename($relative_target_path_);
	if(file_exists($target_path_) || is_link($target_path_)) return false;

	$source_instance_ = @fopen($source_path_, 'rb');
	$target_instance_ = @fopen($target_path_, 'x+b');
	if($source_instance_ === false || $target_instance_ === false){
		if(is_resource($source_instance_)) fclose($source_instance_);
		if(is_resource($target_instance_)) fclose($target_instance_);
		if(file_exists($target_path_)) @unlink($target_path_);
		return false;
	}

	$copied_size_ = stream_copy_to_stream($source_instance_, $target_instance_);
	fflush($target_instance_);
	fclose($source_instance_);
	fclose($target_instance_);

	$source_size_ = filesize($source_path_);
	$target_size_ = filesize($target_path_);
	$copied_ = $copied_size_ !== false && $source_size_ !== false && $target_size_ !== false
		&& (int)$copied_size_ === (int)$source_size_ && (int)$target_size_ === (int)$source_size_
		&& hash_file('sha256', $source_path_) === hash_file('sha256', $target_path_);
	if(!$copied_) @unlink($target_path_);
	return $copied_;
}

$options_ = getopt('', array('apply', 'reverse', 'help'));
if(isset($options_['help'])){
	pb_upload_migration_usage();
	exit(0);
}

$apply_ = isset($options_['apply']);
$reverse_ = isset($options_['reverse']);
$source_root_ = $reverse_ ? pb_upload_root_path(false) : pb_upload_legacy_root_path();
$destination_root_ = pb_upload_migration_destination($reverse_, $apply_);

if($source_root_ === false || !is_dir($source_root_)){
	fwrite(STDERR, "Source storage does not exist.\n");
	exit(1);
}
if($destination_root_ === false){
	fwrite(STDERR, "Destination storage is invalid or publicly accessible.\n");
	exit(1);
}

$summary_ = array(
	'mode' => $reverse_ ? 'reverse' : 'forward',
	'apply' => $apply_,
	'source' => $source_root_,
	'destination' => $destination_root_,
	'planned' => 0,
	'copied' => 0,
	'identical' => 0,
	'excluded' => 0,
	'conflicts' => 0,
	'errors' => 0,
);

$iterator_ = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($source_root_, FilesystemIterator::SKIP_DOTS),
	RecursiveIteratorIterator::LEAVES_ONLY
);

foreach($iterator_ as $file_info_){
	$source_path_ = $file_info_->getPathname();
	$relative_path_ = ltrim(str_replace('\\', '/', substr($source_path_, strlen($source_root_))), '/');
	if($file_info_->isLink() || !$file_info_->isFile() || pb_upload_migration_excluded($relative_path_)){
		$summary_['excluded']++;
		continue;
	}

	$target_path_ = rtrim($destination_root_, '/\\').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative_path_);
	if(file_exists($target_path_) || is_link($target_path_)){
		if(is_file($target_path_) && !is_link($target_path_)
			&& filesize($source_path_) === filesize($target_path_)
			&& hash_file('sha256', $source_path_) === hash_file('sha256', $target_path_)){
			$summary_['identical']++;
			continue;
		}
		$summary_['conflicts']++;
		echo "CONFLICT ".$relative_path_."\n";
		continue;
	}

	$summary_['planned']++;
	if(!$apply_){
		echo "PLAN ".$relative_path_."\n";
		continue;
	}

	if(pb_upload_migration_copy_exclusive($source_path_, $target_path_, $destination_root_, $reverse_)){
		$summary_['copied']++;
		echo "COPIED ".$relative_path_."\n";
	}else{
		$summary_['errors']++;
		echo "ERROR ".$relative_path_."\n";
	}
}

echo json_encode($summary_, JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($summary_['conflicts'] > 0 || $summary_['errors'] > 0 ? 2 : 0);

?>
