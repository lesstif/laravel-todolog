<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class StoreTaskRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'bail|required|string|unique:tasks|max:20',
            'description' => 'nullable|string',
            'priority' => 'required|in:낮음,보통,높음',
            'status' => 'required|in:등록,진행,완료',
            'due_date' => 'nullable|date|after:today',
        ];
    }

}
