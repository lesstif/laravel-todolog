# 쉽게 배우는 라라벨 5 프로그래밍 정오표

* [1.7 의존성 주입과 제어 역전](#17-의존성-주입과-제어-역전)
  * [p.38 <em>implements RepositoryInterface</em> 구문 삭제](#p38-implements-repositoryinterface-구문-삭제)
  * [p.36 ~ p.38 까지 변수 선언 앞에 타입 지정(<em>RepositoryInterface</em>) 삭제](#p36--p38-까지-변수-선언-앞에-타입-지정repositoryinterface-삭제)
* [p.102 오타 수정](#p102-오타-수정)
* [p.112 첫 번째 줄 오타 수정](#p112-첫-번째-줄-오타-수정)
* [p.124 오타 수정](#p124-오타-수정)
* [p.159 오타 수정](#p159-오타-수정)
* [p.197 Route::controller deprecated](#p197-route-controller-deprecated)
* [p.203 SQL 조건 누락 수정](#p203-sql-조건-누락-수정)
* [p.349 오타 수정](#p349-오타-수정)
* [p.424 cron 스케줄러 설정](#p424-cron-스케줄러-설정)
* [14.6 동적 프로퍼티/메서드 생성](#146-동적-프로퍼티메서드-생성)

#### 1.7 의존성 주입과 제어 역전

##### p.38 *implements RepositoryInterface* 구문 삭제

생성자를 통해 의존성을 주입하므로 implements 구문 불필요

```diff
-class UserRepository implements RepositoryInterface {
+class UserRepository {
```

##### p.36 ~ p.38 까지 변수 선언 앞에 타입 지정(*RepositoryInterface*) 삭제

```diff
-RepositoryInterface $repos = new UserRepository();
+$repos = new UserRepository();
```

```diff
-RepositoryInterface $repos = new MySQLUserRepository();
+$repos = new MySQLUserRepository();
```

##### p.102 오타 수정

Illuminate 패키지 네임스페이스 오타 수정

```diff
-use llluminate\Http\Response;
+use Illuminate\Http\Response;
```

##### p.112 첫 번째 줄 오타 수정

```diff
-\>는 \&lt;로 변경
+\>는 \&gt;로 변경
```

##### p.124 오타 수정

```diff
-테이블에 입력한 key와 a에서 
+테이블에 입력한 key와 1번에서
```

##### p.159 오타 수정

```diff
-ADD Commnet varchar(100) 
+ADD Comment varchar(100) 
```

##### p.197 route controller deprecated

[이동한](https://github.com/linuxwife)님이 알려주신 대로 라라벨 5.2 부터 *Route::controller* 는 deprecated 되었고 5.3에서는 삭제 예정입니다.

책 p.148 에서도 암시적 컨트롤러는 전체 라우팅이 가려지므로 권장하지 않는다고 기술했는데 p.197 에서 사용한 이유는 ORM 테스트용 예제 메서드를 일일이 라우팅에 기술하기 불편했기 때문입니다.

독자분들은 실전에서는 *Route::controller* 를 사용하지 않으시기 바랍니다.


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

```
where('id', '>', 10)와 같이 공백이 있는 다음과 같은 PDO
```

**변경후**

설명대로라면 아래와 같이 공백이 추가되어야 하지만 누락되어 있습니다. 그리고 라라벨 5.2 에서는 PDOException 이 발생하지 않습니다.

```php
where('id', '> ', 10)와 같이 공백이 있는 다음과 같은 PDO
```

##### p.310 세션 키 이름 오타

세션 키 이름으로 'github**_**id' 를 선언하고 사용할 때는 'github**-**id' 로 사용. - 대신 _ 를 사용해야 함.

##### p.349 오타 수정

349페이지: 부트스트랩 *datetimepicker* 를 *datepicker* 로 잘못 작성.

**변경전**

```diff
-$("#due_date").datepicker({
+$("#due_date").datetimepicker({
```

폼에서 기한 부분 name attribute가 빠져 있어서 업데이트가 되지 않습니다.(laravel-todolog#18)

**변경전**

```diff
-<input type="text" class="form-control" value="{{ $task->due_date }}">
+<input type="text" class="form-control" name="due_date" value="{{ $task->due_date }}">
```

#### 14.5 익명 함수

##### p.447 익명 함수 뒤에 세미콜론(;) 누락

**예제 14.18 : 

```diff
$hello = function ($name, $age) {
  return "name:" . $name . " age: " . $age;
-}
+};
```

**예제 14.19 : 

```diff
$hello = function() use ($name, $age) {
  return "name:" . $name . " age: " . $age;
-}
+};
```

##### p.424 cron 스케줄러 설정

**변경전**

9번째 줄

스케줄러가 구동되도록 **cron -e** 로 작업 등록 기능을 실행한 후...

**변경후**

스케줄러가 구동되도록 **crontab -e** 로 작업 등록 기능을 실행한 후...

#### 14.6 동적 프로퍼티/메서드 생성

p.449 마지막 줄 *__callstatic* 을 *__callStatic* 으로 변경
