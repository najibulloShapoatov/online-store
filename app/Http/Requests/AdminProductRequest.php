<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminProductRequest extends FormRequest
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
            'category_id' => 'required',
            'date' => 'required|date|date_format:Y-m-d',
            'title' => 'required|unique:products',
            'alias' => 'required|unique:products',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024|dimensions:width=600,height=600',
            'price' => 'required|numeric',
            'sale' => 'numeric|nullable',
        ];
    }

    public function messages()
    {
        return [
            'category_id.required' => 'Выберите категорию',
            'date.required' => 'Введите дату',
            'title.required' => 'Введите заголовок',
            'title.unique' => 'Заголовок должен быть уникальным',
            'alias.required' => 'Введите алиас',
            'alias.unique' => 'Алиас должен быть уникальным',
            'price.required' => 'Введите стоимость',
            'price.numeric' => 'Поле стоимость должна быть числом, например: 10.5 или 10',
            'sale.numeric' => 'Поле скидка должна быть числом',
            'image.required' => 'Загрузите картину',
            'image.dimensions' => 'Картина доллжна быть 600x600 px',
            'image.mimes' => 'Формат картины должен быть (jpeg,png,jpg,gif)',
            'image.max' => 'Размер картины должна быть менее 1 МБ',
            'image.image' => 'Эй, вы че? Загрузите картину!',
        ];
    }
}
