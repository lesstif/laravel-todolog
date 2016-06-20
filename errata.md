# 쉽게 배우는 라라벨 5 프로그래밍 정오표

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

#### 14.6 동적 프로퍼티/메서드 생성

p.449 마지막 줄 *__callstatic* 을 *__callStatic* 으로 변경
