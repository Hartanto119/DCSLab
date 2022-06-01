<?php

namespace App\Http\Requests;

use App\Enums\UserRoles;
use App\Enums\ActiveStatus;
use App\Rules\isValidCompany;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();

        if (empty($user->roles)) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;

        if ($this->route()->getActionMethod() == 'store' && $user->hasPermission('employee-create')) return true;
        if ($this->route()->getActionMethod() == 'update' && $user->hasPermission('employee-update')) return true;

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $nullableArr = [
            'address' => 'nullable|max:255',
            'city' => 'nullable|max:255',
            'postal_code' => 'nullable|max:10',
            'img_path' => 'nullable',
            'remarks' => 'nullable|max:255',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'name' => 'required|min:3|max:255',
                    'email' => 'required|email|max:255',
                    'country' => 'required',
                    'tax_id' => 'required',
                    'ic_num' => 'required|min:12|max:255',
                    'join_date' => 'required',
                    'status' => [new Enum(ActiveStatus::class)]
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'name' => 'required|min:3|max:255',
                    'email' => 'required|email|max:255',
                    'country' => 'required',
                    'tax_id' => 'required',
                    'ic_num' => 'required|min:12|max:255',
                    'join_date' => 'required',
                    'status' => [new Enum(ActiveStatus::class)]
                ];
                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required'
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.company'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
            'status' => ActiveStatus::isValid($this->status) ? ActiveStatus::fromName($this->status)->value : -1
        ]);
    }
}
