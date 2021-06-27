<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminSaleblockRequest extends FormRequest
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
            'date' => 'required|date|date_format:Y-m-d',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024|dimensions:width=370,height=250'
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'Введите дату',
            'image.required' => 'Загрузите картину',
            'image.dimensions' => 'Картина доллжна быть 370x250 px',
            'image.mimes' => 'Формат картины должен быть (jpeg,png,jpg,gif)',
            'image.max' => 'Размер картины должна быть менее 1 МБ',
            'image.image' => 'Эй, вы че? Загрузите картину!',
        ];
    }
}
