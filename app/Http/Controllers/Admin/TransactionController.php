<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaddleTransaction;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $transactions = PaddleTransaction::query()
            ->with(['user:id,name,email', 'subscription:id,plan'])
            ->latest('occurred_at')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.transactions', [
            'transactions' => $transactions,
        ]);
    }
}
