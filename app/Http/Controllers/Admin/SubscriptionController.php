<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $subscriptions = Subscription::query()
            ->with('user:id,name,email')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.subscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
