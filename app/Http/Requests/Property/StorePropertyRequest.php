<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'property-name' => 'required|unique:properties,name|max:255',
            'property-id' => 'required|unique:properties,analytics_id|max:64',
            'place-id' => 'nullable|string|max:255',
            'property-logo' => 'nullable|max:255',
            'property-url' => 'required|unique:properties,url|max:255',
        ];
    }
}
