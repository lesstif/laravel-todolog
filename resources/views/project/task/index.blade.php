@extends('layouts.app')

@section('title')
    태스크 목록
@endsection

@section('content')

    <div class="col-md-12">
        <h3>{{ $proj->name }}</h3>
        <p>
            <a href="{{ route('project.task.create', [$proj->id]) }}" class="btn btn-success">task 생성</a>
        </p>

        @if(Session::has('message'))
            <div class="alert alert-info">
                {{Session::get('message')}}
            </div>
        @endif

        <table class="table table-striped">
            <thead>
            <tr>
                <td>이름</td>
                <td>우선순위</td>
                <td>상태</td>
                <td>기한</td>
            </tr>
            </thead>
            <tbody>
            @foreach ($tasks as $task)
                <tr>
                    <td>
                        <a href="{{route('project.task.show', [$proj->id, $task->id])}}">{{ $task->name }}</a>
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
                        <a class="btn btn-success" href="{{ route('project.task.edit', [$proj->id, $task->id]) }}">편집</a>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('project.task.destroy', [$proj->id, $task->id]) }}">
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
