# 쉽게 배우는 라라벨 5 프로그래밍 실전 예제 코드

실전 프로젝트 파일 및 코드이며 실제 동작하는 소스는 **[https://todolog.lesstif.com](https://todolog.lesstif.com)** 에서 확인할 수 있습니다. 

오탈자 문의 및 내용 문의는 [깃허브 이슈 페이지](https://github.com/lesstif/laravel-todolog/issues/new) 에 해주시고 정오표는 [errata.md](errata.md) 를 참고하세요

설치는 다음 절차를 통해서 하시면 됩니다.

## 사전 작업


### Homestead 개발 환경 구성 - VM 환경

1. VirtualBox 와 vagrant 를 설치합니다.
1. 라라벨 홈스테드를 설치합니다.
1. 서비스 url 인 todolog.app 를 hosts 파일에 추가합니다.

  ```
  192.168.10.10 todolog.app
  ```

 * Windows
   * c:\windows\system32\drivers\etc\hosts
 * OS X or Linux 
   * /etc/hosts


### 로컬 개발 환경 구성

여러 가지 이유로 VirtualBox 와 Vagrant 를 설치/설정할 수 없는 경우에 로컬 개발 환경 구성하는 법입니다.

로컬 개발 환경은 redis 등의 패키지를 별도로 설치해야 nginx 가 아닌 apache httpd 이므로 2부에서 다루는 "라라벨 배포" 시에도 문제가 될 수 있으니 VM 에서 개발 환경 구성 하는 것을 권장합니다.

#### Windows - XAMPP

[Windows에 WAMP 와 composer 설치하기](https://www.lesstif.com/pages/viewpage.action?pageId=24445298) 를 참고해서 XAMPP 스택과 컴포저를 설치합니다.

#### Mac OS X - MAMP

[Mac OS X 에 MAMP 와 composer 설치](https://www.lesstif.com/pages/viewpage.action?pageId=24445298) 를 참고해서 MAMP 스택과 컴포저를 설치합니다.

#### AMP 설치후 할 일

1. 서비스 url 인 todolog.app 를 hosts 파일에 추가합니다. 로컬 환경이므로 IP는 127.0.0.1 입니다.

  ```
  127.0.0.1 todolog.app
  ```

1. php 버전 확인

 윈도라면 cmd.exe를 Mac OS 는 터미널을 열고 다음 명령을 실행하여 PHP 의 버전을 확인합니다.
  ```
  php -v
  ```

1. php 내장 웹 서버 구동

 8000 포트에 내장 웹 서버를 띄웁니다.
 ```
 php -S 0.0.0.0:8000
 ```

1. phpinfo 작성

 php 내장 웹 서버를 구동한 폴더에 *phpinfo()* 함수를 호출하는 i.php 파일을 만듭니다.

 ```php
 <?php

 phpinfo();
 ```

1. 브라우저로 연결

 브라우저로 http://todolog.app:8000/i.php 포트에 연결해서 정상 동작을 확인합니다.

## 설정

### 프로젝트 다운로드

1. 사용하는 git 클라이언트로 프로젝트 소스를 로컬에 다운로드 받습니다.
    ```
    git clone https://github.com/lesstif/laravel-todolog
    ```

2. 다운 받은 소스로 이동합니다
    ```
    cd laravel-todolog
    ```

### 의존성 설치

컴포저로 의존성 라이브러리를 설치합니다.

```
composer install
```

### 환경 설정

1. 설정 파일을 복사합니다.
    ```
     cp .env.example .env
    ```

1. 세션 암호화등에 사용하는 애플리케이션 키를 생성합니다.
    ```
     php artisan key:gen
    ```

1. 데이타 마이그레이션을 적용합니다.
    ```
    php artisan migrate:refresh
    ```

1. 초기 데이타를 생성합니다.
    ```
    php artisan db:seed
    ```

1. github 로 로그인하기를 사용하려면 .env 에 github 설정을 추가합니다.
    ```
    GITHUB_ID=id-here
    GITHUB_SECRET=secret-here
    GITHUB_URL=http://todolog.app/auth/github/callback
    ```

1. mailgun 으로 이메일을 전송하기 위해 .env 에 mailgun 설정을 추가합니다.
    ```
    MAIL_DRIVER=mailgun
    MAILGUN_DOMAIN=your-host.mailgun.org
    MAILGUN_SECRET=key-your-mailgun-secret
    ```

### 테스트


브라우저에서 http://todolog.app(VM 환경) 또는 http://todolog.app:8000/(로컬 환경) 에 연결해서 초기 화면이 보이는지 확인합니다.


1부의 예제 코드는 [laravel-example] 폴더를 참고하세요.

## 배포

배포 환경을 구성하기 위해 사용한 명령어들은 아래 gist 를 참고하세요.


* [ubuntu 16 LTS](https://gist.github.com/lesstif/789b69158028040f234c8b853ecf13b6)
* [ubuntu 14 LTS](https://gist.github.com/lesstif/5bd471dfa6c7fd15e0af)
* [RHEL/CentOS 7] - 추가 예정