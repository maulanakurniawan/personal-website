<?php

namespace App\Http\Controllers;

use App\Models\PaddleTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = PaddleTransaction::query()
            ->where('user_id', $request->user()->id)
            ->latest('occurred_at')
            ->latest('created_at')
            ->paginate(20);

        return view('transactions.index', [
            'transactions' => $transactions,
        ]);
    }

    public function show(Request $request, PaddleTransaction $transaction): View
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 404);

        return view('transactions.show', [
            'transaction' => $transaction,
        ]);
    }
}
