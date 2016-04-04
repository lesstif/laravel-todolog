@extends('layouts.app')

@section('title')
    태스크 목록
@endsection

@section('content')

    <div class="col-md-12">

        <form class="form-horizontal" role="form" method="GET" action="{{ route('task.index')}}">
            <div class="container">
                <div class='col-md-3'>
                    <div class="form-group">
                        <label for="시작일">검색 시작일</label>
                        <div class='input-group date' id='start_date'>
                            <input type='text' class="form-control" name="start_date"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                        </div>
                    </div>
                </div>
                <div class='col-md-3'>
                    <div class="form-group">
                        <label for="종료일">검색 종료일</label>
                        <div class='input-group date' id='end_date'>
                            <input type='text' class="form-control" name="end_date"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                        </div>
                    </div>
                </div>
                <div class='col-md-2'>
                    <div class="form-group">
                        <label for="순위">우선순위</label>
                        <select class="form-control" name="priority">
                            @foreach(['all', '낮음', '보통', '높음'] as $p)
                                <option value="{{$p}}" {{ ($priority === $p) ? "selected" : "" }}>{{$p}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class='col-md-2'>
                    <div class="form-group">
                        <label for="상태">상태</label>
                        <select class="form-control" name="status">
                            @foreach(['all','등록', '진행', '완료'] as $s)
                                <option value="{{$s}}" {{ ($status === $s) ? "selected" : "" }}>{{$s}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class='col-md-2'>
                    <div class="form-group">
                        <div style="margin-top:24px;">
                            <button type="submit" class="btn btn-primary">
                                찾기
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <script type="text/javascript">
            $(function () {
                $('#start_date').datetimepicker({
                    defaultDate: '{{ $start_date }}',
                    format: 'YYYY-MM-DD HH:mm:ss'
                });
                $('#end_date').datetimepicker({
                    defaultDate: '{{ $end_date }}',
                    format: 'YYYY-MM-DD HH:mm:ss',
                    useCurrent: false //Important! See issue #1075
                });
                $("#start_date").on("dp.change", function (e) {
                    $('#end_date').data("DateTimePicker").minDate(e.date);
                });
                $("#end_date").on("dp.change", function (e) {
                    $('#start_date').data("DateTimePicker").maxDate(e.date);
                });
            });
        </script>

        <h3>태스크 목록</h3>
        <table class="table table-striped">
            <thead>
            <tr>
                <td>프로젝트</td>
                <td>태스크</td>
                <td>우선순위</td>
                <td>상태</td>
                <td>기한</td>
            </tr>
            </thead>
            <tbody>
            @foreach ($tasks as $task)
                <tr>
                    <td>
                        {{ $task->project->name }}
                    </td>
                    <td>
                        <a href="{{route('project.task.show', [$task->project->id, $task->id])}}">{{ $task->name }}</a>
                    </td>
                    <td>
                        {{ $task->priority }}
                    </td>
                    <td>
                        {{ $task->status }}
                    </td>
                    <td>
                        {{ $task->due_date }}
                    </td>
                    <td>
                        <a class="btn btn-success" href="{{ route('project.task.edit', [$task->project->id, $task->id]) }}">편집</a>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('project.task.destroy', [$task->project->id, $task->id]) }}">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-danger">
                                삭제
                            </button>
                        </form>
                    </td>
                </tr>

            @endforeach
            </tbody>
        </table>
    </div>

@endsection
