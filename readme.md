# 쉽게 배우는 라라벨 5 프로그래밍 실전 예제 코드

실전 프로젝트 파일 및 코드이며 실제 동작하는 소스는 **[https://todolog.lesstif.com](https://todolog.lesstif.com)** 에서 확인할 수 있습니다. 

설치는 다음 절차를 통해서 하시면 됩니다.

## 사전 준비 작업

1. VirtualBox 와 vagrant 를 설치합니다.)
2. 라라벨 홈스테드를 설치합니다.
3. 서비스 url 인 todolog.app 를 hosts 파일에 추가합니다.
  * Windows : c:\windows\system32\drivers\etc\hosts
  * OS X or Linux : /etc/hosts

```
192.168.10.10 todolog.app
```

## 프로젝트 다운로드

1. 사용하는 git 클라이언트로 프로젝트 소스를 로컬에 다운로드 받습니다.
```
git clone https://github.com/lesstif/laravel-todolog
```

2. 다운 받은 소스로 이동합니다
```
cd laravel-todolog
```

## 의존성 설치

컴포저로 의존성 라이브러리를 설치합니다.

```
composer install
```

## 환경 설정

1. 설정 파일을 복사합니다.
```
 cp .env.example .env
```

2. 세션 암호화등에 사용하는 애플리케이션 키를 생성합니다.

```
 php artisan key:gen
```

3. 데이타 마이그레이션을 적용합니다.

```
php artisan migrate:refresh
```

3. 초기 데이타를 생성합니다.

```
php artisan db:seed
```

4. github 로 로그인하기를 사용하려면 .env 에 github 설정을 추가합니다.

```
GITHUB_ID=id-here
GITHUB_SECRET=secret-here
GITHUB_URL=http://todolog.app/auth/github/callback
```


## 테스트


브라우저에서 http://todolog.app 에 연결하세요.


1부의 예제 코드는 [laravel-example] 폴더를 참고하세요.