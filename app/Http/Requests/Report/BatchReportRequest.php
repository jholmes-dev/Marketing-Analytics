<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class BatchReportRequest extends FormRequest
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
            'month' => 'required|string|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'year' => 'required|integer'
        ];
    }
}
