<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require(PB_DOCUMENT_PATH."includes/common/lib/phpmailer/newer/Exception.php");
require(PB_DOCUMENT_PATH."includes/common/lib/phpmailer/newer/PHPMailer.php");
require(PB_DOCUMENT_PATH."includes/common/lib/phpmailer/newer/SMTP.php");

class PBMailSender_newer extends PBMailSender{
	function send($to_, $subject_, $body_, $attachments_ = array(), $options_ = array()){
		if(!isset($options_['from'])){
		$options_['from'] = pb_option_value("mail_sender");	
	}

	if(!strlen($options_['from'])){
		return new PBError(-1, "SMTP정보없음", "SMT정보가 없습니다.");
	}
	
	$mail_receipt_ = pb_option_value('mail_receipt');
	$mail_smtp_host_ = pb_option_value('mail_smtp_host');
	$mail_smtp_port_ = pb_option_value('mail_smtp_port');
	$mail_smtp_auth_ = pb_option_value('mail_smtp_auth');
	$mail_smtp_user_id_ = pb_option_value('mail_smtp_user_id');
	$mail_smtp_user_pass_ = pb_option_value('mail_smtp_user_pass');
	$mail_smtp_secure_ = pb_option_value('mail_smtp_secure');

	$phpmailer_ = new PHPMailer;

	global $pb_config;

	$phpmailer_->CharSet = $pb_config->charset;
	$phpmailer_->isSMTP();
	$phpmailer_->Host = $mail_smtp_host_;
	$phpmailer_->SMTPAuth = ($mail_smtp_auth_ === "Y" ? true : false);

	if($phpmailer_->SMTPAuth){
		$phpmailer_->Username = $mail_smtp_user_id_;
		$phpmailer_->Password = $mail_smtp_user_pass_;
	}

	$phpmailer_->SMTPSecure = $mail_smtp_secure_;
	$phpmailer_->Port = $mail_smtp_port_;

	$phpmailer_->setFrom($options_['from']);
	$phpmailer_->addAddress($to_);

	foreach($attachments_ as $file_path_){
		$phpmailer_->addAttachment($file_path_);
	}

	if(isset($options_['cc'])){
		if(gettype($options_['cc']) !== "array"){
			$options_['cc'] = array($options_['cc']);
		}

		foreach($options_['cc'] as $cc_){
			$phpmailer_->addCC($cc_);
		}
	}

	if(isset($options_['bcc'])){
		if(gettype($options_['bcc']) !== "array"){
			$options_['bcc'] = array($options_['bcc']);
		}

		foreach($options_['bcc'] as $bcc_){
			$phpmailer_->addBCC($bcc_);
		}
	}

	if(isset($options_['replyto'])){
		if(gettype($options_['replyto']) !== "array"){
			$options_['replyto'] = array($options_['replyto']);
		}

		foreach($options_['replyto'] as $replyto_){
			$phpmailer_->addReplyTo($replyto_);
		}
	}

	$options_['is_html'] = isset($options_['is_html']) ? $options_['is_html'] : true;

	$phpmailer_->isHTML($options_['is_html']);
	$phpmailer_->Subject = $subject_;
	$phpmailer_->Body = $body_;

	$result_ = @$phpmailer_->send();

	if(!$result_){
		return new PBError(-1, "메일발송실패", $phpmailer_->ErrorInfo);
	}

	return true;
	}
}

function _pb_mail_sender_types_newer($results_){
	$results_['newer'] = 'PBMailSender_newer';
	return $results_;
}
pb_hook_add_filter('pb_mail_sender_types', '_pb_mail_sender_types_newer');

?>