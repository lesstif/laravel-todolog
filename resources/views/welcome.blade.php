@extends('layouts.app')

@section('title')
    웰컴 페이지
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">라라벨 Todo 사이트</div> <!-- ① -->

                    <div class="panel-body">
                        <div class="container">
                            총 가입자 수 : {{ $total['user'] }}</p> <!-- ② -->
                            프로젝트  수 : {{ $total['project'] }}</p>
                            Task     수 : {{ $total['task'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
