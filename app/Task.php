<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['name', 'description', 'due_date', 'priority', 'status'];

    // 완료 기한
    protected $dates = ['due_date'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeDueInDays($query, $days)
    {
        $now = \Carbon\Carbon::now();

        return $query->where('due_date', '>', $now)
             ->where('due_date', '<', $now->copy()->addDays($days));
    }

    /**
     * 만료일이 특정 일 사이인 태스크 검색하는 쿼리 스코프
     *
     * @param $query
     * @param \Carbon\Carbon $start_date
     * @param \Carbon\Carbon $end_date
     * @return mixed
     */
    public function scopeDueDateBetween($query, \Carbon\Carbon $start_date, \Carbon\Carbon $end_date)
    {
        return $query->whereBetween('due_date', [
            $start_date->startOfDay(),
            $end_date->endOfDay()
        ]);
    }

    /**
     * Request 로 전달받은 검색 조건을 사용하여 동적으로 쿼리 스코프 생성
     *
     * @param $query
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function scopeOtherParam($query, \Illuminate\Http\Request $request)
    {
        $priority = $request->get('priority');
        if (!empty($priority) && $priority != 'all') {
            $query = $query->where('priority', $priority);
        }

        $status = $request->get('status');
        if (!empty($status) && $status != 'all') {
            $query = $query->where('status', $status);
        }

        return $query;
    }


}
