<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
use App\Enums\PaymentTerm;
use App\Enums\PaymentTermType;
use App\Enums\UserRoles;
use App\Rules\isValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class SupplierRequest extends FormRequest
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

        if ($this->route()->getActionMethod() == 'store' && $user->hasPermission('supplier-create')) return true;
        if ($this->route()->getActionMethod() == 'update' && $user->hasPermission('supplier-update')) return true;

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
            'address' => 'nullable',
            'contact' => 'nullable',
            'city' => 'nullable',
            'tax_id' => 'nullable',
            'remarks' => 'nullable',
            'productIds.*' => 'nullable',
            'mainProducts.*' => 'nullable',
            'poc_name' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => [new Enum(ActiveStatus::class)],
                    'payment_term_type' => [new Enum(PaymentTerm::class)],
                    'payment_term' => 'required|numeric',
                    'taxable_enterprise' => 'required|boolean',
                    'email' => ['required', 'email']
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => [new Enum(ActiveStatus::class)],
                    'payment_term_type' => [new Enum(PaymentTerm::class)],
                    'payment_term' => 'required|numeric',
                    'taxable_enterprise' => 'required|boolean',
                    'email' => ['required', 'email']
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
            'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0]:'',
            'taxable_enterprise' => $this->has('taxable_enterprise') ? filter_var($this->taxable_enterprise, FILTER_VALIDATE_BOOLEAN) : false,
            'payment_term_type' => PaymentTermType::isValid($this->payment_term_type) ? PaymentTermType::fromName($this->payment_term_type)->value : '',
            'status' => ActiveStatus::isValid($this->status) ? ActiveStatus::fromName($this->status)->value : -1
        ]);
    }
}
