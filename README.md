# PBPress
PHP Framework for simple website creation.
간단한 웹사이트제작을 위한 PHP 프레임워크

## Requirements
	PHP >= 5.2.17
	MySQL >= 5.x (innoDB)
		- mysql, mysqli
		
## How to install
PBPress need to initialize frontend library that based by NPM Gruntjs.
PBPress는 NPM Gruntjs 기반으로 이루어진 프론트엔드 라이브러리를 초기화해야 합니다. 
    
    > npm init
    > npm install --save-dev
    
or just use pre-compiled files.(PBPress.v.x.x.x.zip).
또는 컴파일된 파일을 사용하셔도 됩니다. (PBPress.v.x.x.x.zip).

[go to releases](https://github.com/pnbro/pbpress/releases)

the PBPress which compiled or downloaded put into your project directory.(directory must be empty.)
컴파일 또는 다운로드한 PBPress를 프로젝트 디렉토리에 넣습니다.
* * *

copy pb-config-sample.php, rename to pb-config.php, and edit it for your environment.
pb-config-sample.php 복사하여 pb-config.php로 이름을 변경하고, 환경에 맞게 편집하세요.
* * *

access project on browser.
브라우져를 통해 프로젝트에 접속해봅니다.

       http://{project_url}

* * *
you can see install screen. and complete a installation!
인스톨 화면을 볼 수 있습니다. 인스톨을 완료하세요!
	
![install](https://i.imgur.com/cnfyMC7.png)
* * *

## Build Frontend library
You have to compile, when resoure files like js,less changed.
js,less 같은 리소스파일이 변경될 경우, 컴파일을 해야합니다.

    > grunt
    > grunt dist

## automation Gruntjs

    > grunt watch
    
## Adding theme
you can make a theme that you want, and just put your theme you made into 'themes' directory. (See sample theme into themes directory)
원하는 테마를 제작할 수 있습니다. 그리고 제작한 테마를 themes 디렉토리에 넣으면 됩니다.(themes 디렉토리에 샘플 테마를 참조)


## included open sources
### Frontend 
	jquery 2.x : https://jquery.com
	jquery.blockUI : http://malsup.com/jquery/block
	jquery.cookie : https://github.com/carhartl/jquery-cookie
	imagesloaded : https://imagesloaded.desandro.com/
	jquery.ui 1.x : https://jqueryui.com
	blueimp fileupload : https://github.com/blueimp/jQuery-File-Upload
	Bootstrap 3.x : https://getbootstrap.com 
	bootstrap-datetimepicker : https://github.com/Eonasdan/bootstrap-datetimepicker
	bootstrap-select : https://developer.snapappointments.com/bootstrap-select
	jsencrypt : https://github.com/travist/jsencrypt
	momentjs : https://momentjs.com
	summernote : https://github.com/summernote/summernote

### Backend
	blueimp UploadHandler : https://github.com/blueimp/jQuery-File-Upload
	phpmailer : https://github.com/PHPMailer/PHPMailer

### Font
	NotoSansKR Webfont(Converted by theeluwin) : https://github.com/theeluwin/NotoSansKR-Hestia

## TODO
	1. Write API documentation and manuals.
	2. Developing plugin features?
	3. Adding Page Builders?
	4. Developing multi languages features?
