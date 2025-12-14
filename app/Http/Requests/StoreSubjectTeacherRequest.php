<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectTeacherRequest extends FormRequest
{
    /**
     * Allow the controller/policy to handle authorization.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules for creating a subject teacher mapping.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => ['required','integer','exists:users,id'],
            'subject_id' => ['required','integer','exists:volunteer_subjects,subject_id'],
            'subject_proficiency' => ['nullable','string','max:191'],
        ];
    }
}
