<html>
<head>
    <title>{{ $user->name }} 님의 {{ $dueInDay }} 일 이내에 만료되는 태스크</title>
</head>
<body>
<table>
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
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>