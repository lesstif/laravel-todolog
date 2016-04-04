<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Mail;

class ReminderController extends Controller
{
    /** 사용자 id 로 검색해서 완료 일이 파라미터로 전달된 일보다 적은 태스크 검색하여 메일 전송
     *
     * @param $id 사용자 id
     * @param int $dueInDay 완료일
     *
     */
    public function sendEmailReminder($id, $dueInDay = 7)
    {
        $user = User::findOrFail($id);

        $tasks = $user->tasks()->dueInDays($dueInDay)->get();  // 만료일내 태스크 검색

        $data = [        // 메일에 전달할 데이타
            'user' => $user,
            'dueInDay' => $dueInDay,
            'tasks' => $tasks,
        ];

        Mail::send('emails.reminder', $data, function ($m) use ($user) {    // 메일 전송
            $m->from('no-reply@todolog.app', 'todolog Application');

            $m->to($user->email, $user->name)
                ->subject('태스크 만료 알림');
        });
    }

}
