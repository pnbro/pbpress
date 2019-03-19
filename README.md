# PBPress
PHP Framework for simple website creation.
간단한 웹사이트제작을 위한 PHP 프레임워크

## Requirements
PHP >= 5.2.17
MySQL >= 5.x (innoDB)

## How to install
PBPress need to initialize frontend library that based by NPM Gruntjs. or just use pre-compiled files.(PBPress.v.x.x.x.zip)
PBPress는 NPM Gruntjs 기반으로 이루어진 프론트엔드 라이브러리를 초기화해야 합니다. 또는 컴파일된 파일을 사용하셔도 됩니다. (PBPress.v.x.x.x.zip)
    
    > npm init
    > npm install --save-dev
    
## Build Frontend library
You have to compile, when resoure files like js,less changed.
js,less 같은 리소스파일이 변경될 경우, 컴파일을 해야합니다.

    > grunt
    > grunt dist

## automation Gruntjs

    > grunt less
    
## Adding theme
you can make a theme that you want, and just put your theme you made into 'themes' directory. (See sample theme into themes directory)
원하는 테마를 제작할 수 있습니다. 그리고 제작한 테마를 themes 디렉토리에 넣으면 됩니다.(themes 디렉토리에 샘플 테마를 참조)


# API documentation and manuals will be added to the distribution
