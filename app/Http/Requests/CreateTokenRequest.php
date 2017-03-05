<?php

namespace App\Http\Requests;

class CreateTokenRequest extends Request
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
            'default_content' => ['string'],
            'default_content_type' => ['string'],
            'default_status' => ['int'],
            'timeout' => ['int', 'min:0', 'max:10'],
        ];
    }
}
