<?php

namespace Sazumme\Publication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
//use another classes

class SampleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}
