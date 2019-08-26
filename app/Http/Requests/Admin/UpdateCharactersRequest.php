<?php

namespace App\Http\Requests\Admin;

use App\Character;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCharactersRequest extends FormRequest
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
        return Character::updateValidation($this);
    }
}