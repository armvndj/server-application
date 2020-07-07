<?php

namespace App\Http\Requests\v1\User;

use App\Presenters\User\OrdinaryUserPresenter;
use Illuminate\Foundation\Http\FormRequest;
use Request;
use Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EditUserRequest extends FormRequest
{
    /**
     * Determine if user authorized to make this request.
     *
     * @param Request $request
     * @param OrdinaryUserPresenter $user
     * @return bool
     * @throws ValidationException
     */
    public function authorize(Request $request, OrdinaryUserPresenter $user): bool
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return true;
        }

        $fillableFields = $user->getFillable();
        $requestFields = array_keys($request->except('id'));

        $fieldsDiff = array_diff($requestFields, $fillableFields);

        if (count($fieldsDiff) > 0) {
            $errorMessages = [];

            foreach ($fieldsDiff as $fieldKey) {
                $errorMessages[$fieldKey] = __('You don\'t have permission to edit this field');
            }

            throw ValidationException::withMessages($errorMessages);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'required|int',
            'full_name' => 'sometimes|required|string',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore(Request::input('id'))
            ],
            'user_language' => 'sometimes|required',
            'password' => 'sometimes|required|min:6',
            'important' => 'sometimes|bool',
            'active' => 'sometimes|required|bool',
            'screenshots_active' => 'sometimes|required|bool',
            'manual_time' => 'sometimes|required|bool',
            'screenshots_interval' => 'sometimes|required|int|min:1|max:15',
            'computer_time_popup' => 'sometimes|required|int|min:1',
            'timezone' => 'sometimes|required|string',
            'role_id' => 'sometimes|required|int|exists:role,id',
            'project_roles' => 'sometimes|required|array',
            'projects_roles.*.project_ids.*.id' => 'sometimes|required|int|exists:projects,id',
            'project_roles.*.role_id' => 'sometimes|required|int|exists:role,id',
            'type' => 'sometimes|required|string'
        ];
    }
}
