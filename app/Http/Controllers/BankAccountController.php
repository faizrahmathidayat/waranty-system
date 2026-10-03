<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends MasterDataController
{
    protected string $model = BankAccount::class;
    protected string $primaryKey = 'id_bank_account';
    protected string $routePrefix = 'bank-account';
    protected string $title = 'Rekening';
    protected array $rules = ['bank_name' => 'required|string|max:100', 'account_number' => 'required|string|max:50', 'account_holder' => 'required|string|max:100', 'is_active' => 'nullable|boolean'];
    protected array $fields = [['name' => 'bank_name', 'label' => 'Nama Bank', 'type' => 'text'], ['name' => 'account_number', 'label' => 'No. Rekening', 'type' => 'text'], ['name' => 'account_holder', 'label' => 'Atas Nama', 'type' => 'text'], ['name' => 'is_active', 'label' => 'Status', 'type' => 'active']];
    protected array $columns = [['data' => 'bank_name', 'name' => 'bank_accounts.bank_name', 'label' => 'Nama Bank'], ['data' => 'account_number', 'name' => 'bank_accounts.account_number', 'label' => 'No. Rekening'], ['data' => 'account_holder', 'name' => 'bank_accounts.account_holder', 'label' => 'Atas Nama'], ['data' => 'is_active', 'name' => 'bank_accounts.is_active', 'label' => 'Status']];

    protected function beforeSave(array &$data, Request $request): void { $data['is_active'] = $request->boolean('is_active', true); }
}
