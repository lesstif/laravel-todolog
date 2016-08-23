# 쉽게 배우는 라라벨 5 프로그래밍 정오표

* [1.7 의존성 주입과 제어 역전](#17-의존성-주입과-제어-역전)
  * [p.38 <em>implements RepositoryInterface</em> 구문 삭제](#p38-implements-repositoryinterface-구문-삭제)
  * [p.36 ~ p.38 까지 변수 선언 앞에 타입 지정(<em>RepositoryInterface</em>) 삭제](#p36--p38-까지-변수-선언-앞에-타입-지정repositoryinterface-삭제)
* [p.102 오타 수정](#p102-오타-수정)
* [p.112 첫 번째 줄 오타 수정](#p112-첫-번째-줄-오타-수정)
* [p.124 오타 수정](#p124-오타-수정)
* [p.159 오타 수정](#p159-오타-수정)
* [p.203 SQL 조건 누락 수정](#p203-sql-조건-누락-수정)
* [p.349 오타 수정](#p349-오타-수정)
* [p.424 cron 스케줄러 설정](#p424-cron-스케줄러-설정)
* [14.6 동적 프로퍼티/메서드 생성](#146-동적-프로퍼티메서드-생성)

#### 1.7 의존성 주입과 제어 역전

##### p.38 *implements RepositoryInterface* 구문 삭제

생성자를 통해 의존성을 주입하므로 implements 구문 불필요

**변경전**

```php
class UserRepository implements RepositoryInterface {
```

**변경후**

```php
class UserRepository {
```

##### p.36 ~ p.38 까지 변수 선언 앞에 타입 지정(*RepositoryInterface*) 삭제

**변경전**

```php
RepositoryInterface $repos = new UserRepository();
```

**변경후**

```php
$repos = new UserRepository();
```

**변경전**

```php
RepositoryInterface $repos = new MySQLUserRepository();
```

**변경후**

```php
$repos = new MySQLUserRepository();
```

##### p.102 오타 수정

Illuminate 패키지 네임스페이스 오타 수정

**변경전**

```php
use llluminate\Http\Response;
```

**변경후**

```php
use Illuminate\Http\Response;
```


##### p.112 첫 번째 줄 오타 수정

**변경전**

\>는 **\&lt;**로 변경

**변경후**

\>는 **\&gt;**로 변경

##### p.124 오타 수정

**변경전**

테이블에 입력한 key와 **a**에서 

**변경후**

테이블에 입력한 key와 **1번**에서

##### p.159 오타 수정

**변경전**

ADD **Commnet** varchar(100) 

**변경후**

ADD **Comment** varchar(100) 

##### p.203 SQL 조건 누락 수정

**변경전**

최종 검색 조건은 아래와 같이 SQL로 변환되어 id가 10보다 크고 20보다 작은 모델을 name 칼럼을 기준으로 내림차순으로 정렬한후에 ..

```sql
select * from task where id > '10' and id < '20' order by name desc limit 3 offset 5;
```

**변경후**

최종 검색 조건은 아래와 같이 SQL로 변환되어 id가 10보다 크고 20보다 작고 **name 칼럼이 'Ta'로 시작하는** 모델을 name 칼럼을 기준으로 내림차순으로 정렬한후에 ..

```sql
select * from task where id > '10' and id < '20' and name like 'Ta%' order by name desc limit 3 offset 5;
```

**변경전**

```php
RepositoryInterface $repos = new MySQLUserRepository();
```

**변경후**

```php
$repos = new MySQLUserRepository();
```

**변경전**

```
where('id', '>', 10)와 같이 공백이 있는 다음과 같은 PDO
```

**변경후**

설명대로라면 아래와 같이 공백이 추가되어야 하며 라라벨 5.2에서는 PDOException 이 발생하지 않습니다.

```
where('id', '> ', 10)와 같이 공백이 있는 다음과 같은 PDO
```

##### p.349 오타 수정

349페이지: 부트스트랩 datetimepicker 사용 코드

**변경전**

```php
$("#due_date").datepicker({
```

**변경후**

```php
$("#due_date").datetimepicker({
```

폼에서 기한 부분 name attribute가 빠져 있어서 업데이트가 되지 않습니다.(laravel-todolog#18)

**변경전**

```php
<input type="text" class="form-control" value="{{ $task->due_date }}">
```

**변경후**

```php
<input type="text" class="form-control" name="due_date" value="{{ $task->due_date }}">
```




#### 14.5 익명 함수

##### p.447 익명 함수 뒤에 세미콜론(;) 누락

**예제 14.18 : 변경전**

```php
$hello = function ($name, $age) {
  return "name:" . $name . " age: " . $age;
}
```

**변경후**

```php
$hello = function ($name, $age) {
  return "name:" . $name . " age: " . $age;
};	// 수정
```

**예제 14.19 : 변경전**

```php
$hello = function() use ($name, $age) {
  return "name:" . $name . " age: " . $age;
}
```

**변경후**

```php
$hello = function() use ($name, $age) {
  return "name:" . $name . " age: " . $age;
};	// 수정
```

##### p.424 cron 스케줄러 설정

**변경전**

9번째 줄

스케줄러가 구동되도록 **cron -e** 로 작업 등록 기능을 실행한 후...

**변경후**

스케줄러가 구동되도록 **crontab -e** 로 작업 등록 기능을 실행한 후...

#### 14.6 동적 프로퍼티/메서드 생성

p.449 마지막 줄 *__callstatic* 을 *__callStatic* 으로 변경
