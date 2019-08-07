<?php


function pb_mail_send($to_, $subject_, $body_, $attachments_ = array(), $options_ = array()){
	include_once(PB_DOCUMENT_PATH."includes/common/lib/phpmailer/class.phpmailer.php");
	include_once(PB_DOCUMENT_PATH."includes/common/lib/phpmailer/class.smtp.php");

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

	$result_ = $phpmailer_->send();


	if(!$result_){
		return new PBError(-1, "메일발송실패", $phpmailer_->ErrorInfo);
	}

	return true;
}
function pb_mail_template_send($to_, $subject_, $data_ = array(), $attachments_ = array(), $options_ = array()){
	$mail_template_upload_path_ = pb_option_value('mail_template_upload', "");

	if(strlen($mail_template_upload_path_)){
		$mail_template_upload_path_ = PB_DOCUMENT_PATH."uploads/".$mail_template_upload_path_;

		global $_pb_last_mail_template_path, $_pb_last_mail_template_file_content;

		if($_pb_last_mail_template_path === $mail_template_upload_path_){
			$mail_template_ = $_pb_last_mail_template_file_content;
		}else{
			if(file_exists($mail_template_upload_path_)){

				$_pb_last_mail_template_path = $mail_template_upload_path_;
				$_pb_last_mail_template_file_content = file_get_contents($mail_template_upload_path_);
				
				$mail_template_ = $_pb_last_mail_template_file_content;
				
			}else{
				$mail_template_ = pb_option_value('mail_template', "{content}");
			}
		}
	}else{
		$mail_template_ = pb_option_value('mail_template', "{content}");
	}	

	$mail_body_ = $mail_template_;
	foreach($data_ as $key_ => $value_){
		$mail_body_ = str_replace("{".$key_."}",$value_,$mail_body_);
	}

	return pb_mail_send($to_, $subject_, $mail_body_, $attachments_, $options_);
}


function _pb_mail_hook_register_manage_site_menu_list($results_){
	$results_['mail'] = array(
		'name' => '메일설정',
		'renderer' => '_pb_mail_hook_render_manage_site',
	);
	return $results_;
}
pb_hook_add_filter('pb-admin-manage-site-menu-list', "_pb_mail_hook_register_manage_site_menu_list");

function _pb_mail_hook_render_manage_site($menu_data_){
	?>

	<div class="manage-site-form-panel panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title">메일설정</h3>
		</div>
		<div class="panel-body">
				
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
							<label for="pb-manage-site-form-mail_sender">발송메일주소</label>
							<input type="email" name="mail_sender" placeholder="메일주소 입력" id="pb-manage-site-form-mail_sender" class="form-control" value="<?=pb_option_value("mail_sender")?>">
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
							<label for="pb-manage-site-form-mail_receipt">수신메일주소</label>
							<input type="email" name="mail_receipt" placeholder="메일주소 입력" id="pb-manage-site-form-mail_receipt" class="form-control" value="<?=pb_option_value("mail_receipt")?>">
							<div class="help-block with-errors"></div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
						

						

				<div class="form-group">
					

					<div class="row">
						<div class="col-xs-7">
							<label for="pb-manage-site-form-mail_smtp_host">SMTP 호스트</label>
							<input type="text" name="mail_smtp_host" placeholder="호스트 입력" id="pb-manage-site-form-mail_smtp_host" class="form-control" value="<?=pb_option_value("mail_smtp_host")?>">
						</div>
						<div class="col-xs-5">
							<label for="pb-manage-site-form-mail_smtp_port">SMTP 포트</label>
							<input type="text" name="mail_smtp_port" placeholder="포트번호 입력" id="pb-manage-site-form-mail_smtp_port" class="form-control" value="<?=pb_option_value("mail_smtp_port")?>">
						</div>
					</div>
					
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-site-form-mail_smtp_secure">SMTP 보안</label>
					<select class="form-control" name="mail_smtp_secure">
						<option value="">사용하지 않음</option>
						<option value="ssl" <?=pb_selected(pb_option_value("mail_smtp_secure"), "ssl")?>>SSL</option>
						<option value="tls" <?=pb_selected(pb_option_value("mail_smtp_secure"), "tls")?>>TLS</option>
					</select>
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>

				<div class="form-group">
					<label for="pb-manage-site-form-mail_smtp_auth">SMTP 인증사용</label>
					
					<div>
						<label class="radio-inline"><input type="radio" name="mail_smtp_auth" value="Y" <?= pb_checked(pb_option_value("mail_smtp_auth"), "Y") ?>> 사용</label>
						<label class="radio-inline"><input type="radio" name="mail_smtp_auth" value="N" <?= pb_checked(pb_option_value("mail_smtp_auth"), "N") ?>> 미사용</label>
					</div>
						
										
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>



				<div data-mail-smtp-auth-subdata>
					<div class="row">
						<div class="col-xs-12 col-sm-6">
							<div class="form-group">
								<label for="pb-manage-site-form-mail_smtp_user_id">SMTP 사용자ID</label>
								<input type="text" name="mail_smtp_user_id" placeholder="사용자ID 입력" id="pb-manage-site-form-mail_smtp_user_id" class="form-control" value="<?=pb_option_value("mail_smtp_user_id")?>">
								<div class="help-block with-errors"></div>
								<div class="clearfix"></div>
							</div>
						</div>
						<div class="col-xs-12 col-sm-6">
							<div class="form-group">
								<label for="pb-manage-site-form-mail_smtp_user_pass">SMTP 사용자 비밀번호</label>
								<input type="password" name="mail_smtp_user_pass" placeholder="사용자ID 입력" id="pb-manage-site-form-mail_smtp_user_pass" class="form-control" value="<?=pb_option_value("mail_smtp_user_pass")?>">
								<div class="help-block with-errors"></div>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>
							

							
				</div>

				<div class="form-group">
					<label>메일서식</label>
					<textarea id="pb-manage-site-form-mail_template" name="mail_template"><?=stripslashes(pb_option_value("mail_template", "{content}"))?></textarea>
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>
				</div>
				<div class="form-group">
					<label>메일서식업로드<br/><small class="help-block">*메일서식을 업로드 시, 위 메일서식을 대체합니다.</small></label>

					<input type="text" name="mail_template_upload" value="<?=pb_option_value("mail_template_upload", "")?>" class="hidden" id="pb-manage-site-form-mail_template_upload_r_name" >
					
					<div class="help-block with-errors"></div>
					<div class="clearfix"></div>

					<script type="text/javascript">
					jQuery(document).ready(function(){$("#pb-manage-site-form-mail_template_upload_r_name").pb_file_input();});
					</script>

				</div>

				<script type="text/javascript">
					jQuery(document).ready(function(){
						$("[name='mail_smtp_auth']").click(function(){
							var bool_ = $("[name='mail_smtp_auth']:checked").val() === "Y";
							$("[data-mail-smtp-auth-subdata]").toggle(bool_);
						});

						var bool_ = $("[name='mail_smtp_auth']:checked").val() === "Y";
						$("[data-mail-smtp-auth-subdata]").toggle(bool_);

						$("#pb-manage-site-form-mail_template").init_summernote_for_pb();

						pb_add_filter('pb-manage-site-update-settings', function($setting_data_){
							return $setting_data_;
						});
					});
				</script>

		</div>
	</div>

	<?php 
}

function _pb_mail_hook_update_site_settings($settings_data_){
	pb_option_update("mail_sender", $settings_data_['mail_sender']);
	pb_option_update("mail_receipt", $settings_data_['mail_receipt']);
	pb_option_update("mail_smtp_host", $settings_data_['mail_smtp_host']);
	pb_option_update("mail_smtp_port", $settings_data_['mail_smtp_port']);
	pb_option_update("mail_smtp_auth", isset($settings_data_['mail_smtp_auth']) ? $settings_data_['mail_smtp_auth'] : "N");

	pb_option_update("mail_smtp_user_id", isset($settings_data_['mail_smtp_user_id']) ? $settings_data_['mail_smtp_user_id'] : null);
	pb_option_update("mail_smtp_user_pass", isset($settings_data_['mail_smtp_user_pass']) ? $settings_data_['mail_smtp_user_pass'] : null);
	pb_option_update("mail_smtp_secure", $settings_data_['mail_smtp_secure']);

	pb_option_update("mail_template", $settings_data_['mail_template']);
	pb_option_update("mail_template_upload", $settings_data_['mail_template_upload']);
	
}
pb_hook_add_action('pb-admin-update-site-settings', "_pb_mail_hook_update_site_settings");

?>