# 쉽게 배우는 라라벨 5 프로그래밍 실전 예제 코드

실전 프로젝트 파일 및 코드이며 실제 동작하는 소스는 **[https://todolog.lesstif.com](https://todolog.lesstif.com)** 에서 확인할 수 있습니다. 

소스 관련 문의 및 오탈자 신고는 [깃허브 이슈 페이지](https://github.com/lesstif/laravel-todolog/issues/new) 에 해주세요

- [정오표](errata.md) 참고
- [vagrant FAQ 및 에러 처리 내역](https://www.lesstif.com/pages/viewpage.action?pageId=28606663) - VirtualBox 와 Vagrant 로 개발 환경 구성시 발생하는 문제 정리

**문의시 요청 사항**

내용 문의시 원활한 지원을 위해 사용 환경을 같이 기술해 주세요.

1. 환경이 Homestead 가 아닌 경우 **PHP 의 버전**을 같이 기술해 주세요.

  ```
  $ php -v

  PHP 7.0.2-1+deb.sury.org~trusty+1 (cli) ( NTS )
  Copyright (c) 1997-2015 The PHP Group
  Zend Engine v3.0.0, Copyright (c) 1998-2015 Zend Technologies
    with Zend OPcache v7.0.6-dev, Copyright (c) 1999-2015, by Zend Technologies
  ```

1. laravel 버전

  아래 명령어로 라라벨 버전을 확인후 알려주세요.

  ```
  $ php artisan --version

  Laravel Framework version 5.2.29
  ```

**VirtualBox/Vagrant Homestead 설정 문의**

*VirtualBox/Vagrant Homestead* 설정 오류는 저자의 지원 능력을 벗어나지만 최대한 제가 재연하고 도와드릴수 있도록 아래처럼 상세한 버전을 적어 주셔야 합니다.

  ```sh
  Win 7, VirtualBox - 5.0.20, Vagrant 1.8.1, Homestead 0.4.4
  ```


  ```sh
  Mac OS 10.10.2 , VirtualBox - 5.0.18, Vagrant 1.8.1, Homestead 0.4.4
  ```

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


브라우저에서 http://todolog.app (VM 환경) 또는 http://todolog.app:포트번호/ (로컬 환경) 에 연결해서 초기 화면이 보이는지 확인합니다.


1부의 예제 코드는 [laravel-example] 폴더를 참고하세요.

## 배포

배포 환경을 구성하기 위해 사용한 명령어들은 아래 gist 를 참고하세요.


* [ubuntu 16 LTS](https://gist.github.com/lesstif/789b69158028040f234c8b853ecf13b6)
* [ubuntu 14 LTS](https://gist.github.com/lesstif/5bd471dfa6c7fd15e0af)
* [RHEL/CentOS 7] - 추가 예정