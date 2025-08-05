# PBPress
PHP Framework for simple website creation.
간단한 웹사이트제작을 위한 PHP 프레임워크

## Features
* powerful backend functions
* Data Object (less query)
* expandable adminpage
* theme, plugins
* page managing (page builder)
* menu managing (menu editor)

## Requirements
	PHP >= 7.0(Recommend higher than 7.x)
	MySQL >= 5.x (innoDB)
		- mysql, mysqli, pdo
		
## How to install
PBPress need to initialize frontend library that based by NPM Gruntjs.
PBPress는 NPM Gruntjs 기반으로 이루어진 프론트엔드 라이브러리를 초기화해야 합니다. 
    
    > npm install
    
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
설치화면을 볼 수 있습니다. 설치를 완료하세요!
	
![install](https://i.imgur.com/cnfyMC7.png)
* * *

## Access Adminpage
You can access to the adminpage through the url below.
아래 URL을 통하여 관리자페이지를 접속할 수 있습니다.

	http://{project_url}/admin


## Build Frontend library
You have to compile, when resoure files like js,less changed in original source.
PBPress 원본소스에서 js,less 같은 리소스파일이 변경될 경우, 컴파일을 해야합니다.

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
	jquery 4.x : https://jquery.com
	jquery.blockUI : http://malsup.com/jquery/block
	jquery.cookie : https://github.com/carhartl/jquery-cookie
	imagesloaded : https://imagesloaded.desandro.com/
	Bootstrap 3.x : https://getbootstrap.com 
	bootstrap-datetimepicker : https://github.com/Eonasdan/bootstrap-datetimepicker
	bootstrap-select : https://developer.snapappointments.com/bootstrap-select
	jsencrypt : https://github.com/travist/jsencrypt
	momentjs : https://momentjs.com
	trumbowyg : https://alex-d.github.io/Trumbowyg/
	summernote : https://summernote.org/
	colorpicker : https://farbelous.io/bootstrap-colorpicker/v2/
	sortablejs : https://sortablejs.github.io/Sortable/

### Backend
	phpmailer : https://github.com/PHPMailer/PHPMailer

### Font
	NotoSansKR Webfont(Converted by theeluwin) : https://github.com/theeluwin/NotoSansKR-Hestia

## license
개인 또는 업체가 PBPress를 사용한 테마 및 플러그인 개발하여 상업적으로 납품이 가능하나, PBPress 자체를 판매 및 수익활동을 금지합니다. 기타 라이센스 규약은 MIT 라이센스를 따릅니다.
	
## TODO
	1. Write API documentation and manuals.
