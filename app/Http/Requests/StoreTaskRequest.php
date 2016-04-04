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
            'name' => 'required|unique:tasks|max:20',
            'priority' => 'in:낮음,보통,높음',
            'due_date' => 'date|after:today',
        ];
    }

}
