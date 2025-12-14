<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVolunteerSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * The controller already runs authorization checks; allow here.
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
            'subject_name' => ['required','string','max:191'],
            'subject_code' => ['nullable','string','max:50'],
        ];
    }
}
