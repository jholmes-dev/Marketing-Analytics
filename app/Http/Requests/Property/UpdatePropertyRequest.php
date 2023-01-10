<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
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
            'property-name' => 'required|max:255',
            'property-id' => 'required|max:64',
            'place-id' => 'required|string|max:256',
            'property-logo' => 'required|max:255',
            'property-url' => 'required|max:255',
        ];
    }
}
