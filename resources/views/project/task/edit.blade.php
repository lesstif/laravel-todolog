@extends('layouts.app')

@section('title')
    태스크 수정
@endsection

@section('content')

    <div class="col-md-12">
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form class="form-horizontal" role="form" method="POST" action="{{ route('project.task.update', [$task->project->id, $task->id]) }}">
            {{ method_field('PUT') }}
            {{ csrf_field() }}

            <div class="form-group">
                <label for="project.task.name">프로젝트</label>
                <div>
                    <select class="form-control" name="project_id">
                        @foreach($projects as $proj)
                            <option value="{{$proj->id}}" {{ ($task->project->id == $proj->id) ? "selected" : "" }}>{{$proj->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="project.task.name">태스크 명</label>
                <div>
                    <input type="text" class="form-control" name="name" value="{{ $task->name }}">
                </div>
            </div>

            <div class="form-group">
                <label for="설명">설명</label>
                <div>
                    <textarea class="form-control" rows="3" name="description">{{ $task->description }}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label>우선순위</label>
                <div>
                    <select class="form-control" name="priority">
                        @foreach(['낮음', '보통', '높음'] as $p)
                            <option value="{{$p}}" {{ ($task->priority === $p) ? "selected" : "" }}>{{$p}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>상태</label>
                <div>
                    <select class="form-control" name="status">
                        @foreach(['등록', '진행', '완료'] as $s)
                            <option value="{{$s}}" {{ ($task->status === $s) ? "selected" : "" }}>{{$s}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="기한">기한</label>
                 <div class='input-group date' id='due_date_picker'>
                    <input type='text' class="form-control" name="due_date"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
                <script type="text/javascript">
                    $(function() {
                        $('#due_date_picker').datetimepicker({
                            locale: 'en',
                            defaultDate: '{{ $task->due_date }}',
                            format: 'YYYY-MM-DD HH:mm:ss'
                        });
                    });
                </script>
            </div>
            <div class="form-group">
                <label for="생성일">생성일</label>
                <div>
                    <input type="text" class="form-control" name="created_at" value="{{ $task->created_at }}" readonly>
                </div>
            </div>
            <div class="form-group">
                <div>
                    <button type="submit" class="btn btn-primary">
                        수정
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection