<?php

if(!defined('PB_DOCUMENT_PATH')){
	die( '-1' );
}

abstract class PBMailSender{
	abstract function send($to_, $subject_, $body_, $attachments_ = array(), $options_ = array());
}

$default_mail_sender_type_ = null;

if(version_compare(PHP_VERSION, '5.5.0') >= 0){
	require(PB_DOCUMENT_PATH."includes/common/mailsender-newer.php");
	$default_mail_sender_type_ = "newer";
}else{
	require(PB_DOCUMENT_PATH."includes/common/mailsender-older.php");
	$default_mail_sender_type_ = "older";
}

function pb_mail_sender_types(){
	return pb_hook_apply_filters("pb_mail_sender_types", array());
}

global $pb_mail_sender;
$mail_sender_types_ = pb_mail_sender_types();
$pb_mail_sender = new $mail_sender_types_[pb_hook_apply_filters('pb_mail_default_sender', $default_mail_sender_type_)]();

function pb_mail_send($to_, $subject_, $body_, $attachments_ = array(), $options_ = array()){
	global $pb_mail_sender;
	return $pb_mail_sender->send($to_, $subject_, $body_, $attachments_, $options_);
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

	global $pb_config;
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
				<?php pb_editor_load_trumbowyg_library(); ?>

				<script type="text/javascript">
					jQuery(document).ready(function(){
						$("[name='mail_smtp_auth']").click(function(){
							var bool_ = $("[name='mail_smtp_auth']:checked").val() === "Y";
							$("[data-mail-smtp-auth-subdata]").toggle(bool_);
						});

						var bool_ = $("[name='mail_smtp_auth']:checked").val() === "Y";
						$("[data-mail-smtp-auth-subdata]").toggle(bool_);

						$("#pb-manage-site-form-mail_template").trumbowyg({
							lang : "<?=pb_current_locale(true)?>",
						});

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