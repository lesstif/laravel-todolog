@extends('layouts.app')

@section('title')
    태스크 생성
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
        <form class="form-horizontal" role="form" method="POST" action="{{ route('project.task.store', [$proj->id]) }}"> //1
            {{ csrf_field() }}

            <div class="form-group">
                <label for="project.task.name">태스크 명</label>
                <div>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="설명">설명</label>
                <div>
                    <textarea class="form-control" rows="3" name="description">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label>우선순위</label>
                <div>
                    <select class="form-control" name="priority">       //2
                        @foreach(['낮음' ,'보통', '높음'] as $p)
                            <option value="{{$p}}" {{ (old('priority') === $p) ? "selected" : "" }}>{{$p}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>상태</label>
                <div>
                    <select class="form-control" name="status">         //3
                        @foreach(['등록', '진행', '완료'] as $s)
                            <option value="{{$s}}" {{ (old('status') === $s) ? "selected" : "" }}>{{$s}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="기한">기한</label>
                 <div class='input-group date' id='due_date'>
                     <input type='text' class="form-control" name="due_date"/>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
                <script type="text/javascript">
                    $(function () {
                        $('#due_date').datetimepicker({     //4
                            locale: 'en',
                            defaultDate: '{{ (old('due_date')) ? old('due_date') : \Carbon\Carbon::now() }}',
                            format: 'YYYY-MM-DD HH:mm:ss'
                        });
                    });
                </script>
            </div>
            <div class="form-group">
                <div>
                    <button type="submit" class="btn btn-primary">
                        생성
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
