<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminInfomenuRequest extends FormRequest
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
            'position' => 'required',
            'title' => 'required',
            'alias' => 'required|unique:mainmenus',
        ];
    }

    public function messages()
    {
        return [
            'position.required' => 'Введите позицию',
            'title.required' => 'Введите заголовок',
            'alias.required' => 'Введите алиас',
            'alias.unique' => 'Алиас должен быть уникальным',
        ];
    }
}
