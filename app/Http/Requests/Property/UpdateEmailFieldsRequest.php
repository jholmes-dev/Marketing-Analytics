<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailFieldsRequest extends FormRequest
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
            'client_name' => 'max:255|nullable',
            'client_email' => 'min:1|max:10|array|required',
            'client_email.*' => 'email|distinct:ignore_case|max:64'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'client_email.min' => 'At least one email address is required.',
            'client_email.required' => 'At least one email address is required.',
            'client_email.max' => 'You may only specify a maximum of 10 email recipients.',
            'client_email.*.email' => 'One of your client emails is not a valid email, or was left empty.',
        ];
    }
}
