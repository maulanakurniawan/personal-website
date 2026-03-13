<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function welcome(): View
    {
        return view('welcome');
    }

    public function pricing(): View
    {
        $planConfig = config('plans');

        $plans = collect(['starter', 'pro'])->mapWithKeys(function (string $code) use ($planConfig) {
            $record = Plan::query()->where('internal_code', $code)->first();

            return [$code => [
                'code' => $code,
                'name' => $record?->name ?? data_get($planConfig, "{$code}.label", ucfirst($code)),
                'price_monthly' => $record?->amount !== null ? (float) $record->amount : (float) data_get($planConfig, "{$code}.price_monthly", 0),
                'currency' => $record?->currency ?? 'USD',
                'billing_interval' => $record?->billing_interval ?? 'month',
            ]];
        });

        return view('pricing', [
            'starterPlan' => $plans->get('starter'),
            'proPlan' => $plans->get('pro'),
        ]);
    }

    public function guides(): View
    {
        return view('guides', [
            'articles' => collect(File::files(resource_path('views/articles')))
                ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
                ->map(function ($file) {
                    $slug = str($file->getFilename())->before('.blade.php')->toString();

                    return [
                        'slug' => $slug,
                        'title' => str($slug)->replace('-', ' ')->title()->toString(),
                    ];
                })
                ->sortBy('title')
                ->values(),
        ]);
    }
}
