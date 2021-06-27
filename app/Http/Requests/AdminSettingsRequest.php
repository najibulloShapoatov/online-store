<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSettingsRequest extends FormRequest
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
            'lozung' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'lozung.required' => 'Введите лозунг',
            'address.required' => 'Введите адрес',
            'phone.required' => 'Введите телефон',
            'email.required' => 'Введите эл. почту',
        ];
    }
}
