@extends('layouts.app')

@section('title')
    프로젝트 정보
@endsection

@section('content')

    <div class="col-md-8">

        <h3>프로젝트 정보</h3>

            <div class="form-group">
                <label for="Project name">프로젝트 명</label>
                <div>
                    <input type="text" class="form-control" name="name" value="{{ $proj->name }}" readonly="true">
                </div>
            </div>

            <div class="form-group">
                <label for="설명">설명</label>
                <div>
                    <textarea class="form-control" rows="3" name="description" readonly="true">{{ $proj->description }}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="생성일">생성일</label>
                <div>
                    <input type="text" class="form-control" name="created_at" value="{{ $proj->created_at }}" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="수정일">수정일</label>
                <div>
                    <input type="text" class="form-control" name="updated_at" value="{{ $proj->updated_at }}" readonly>
                </div>
            </div>
        <p>
            <a href="{{ route('project.task.index', $proj->id) }}" class="btn btn-info">태스크 목록</a>
        </p>


    </div>
@endsection