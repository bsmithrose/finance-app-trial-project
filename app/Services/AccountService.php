<?php

namespace App\Services;

use App\Models\Account;
use App\Http\Resources\AccountResource;


class AccountService
{
    public function getAllWithProcessedTransactions($account_id): AccountResource
    {
        return new AccountResource(Account::where('id', $account_id)->first());
    }
}
