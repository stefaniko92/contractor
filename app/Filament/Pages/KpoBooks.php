<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KpoBooks extends Page
{
    protected static ?string $title = 'KPO knjiga prihoda';

    protected static ?int $navigationSort = 15;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-book-open';

    protected string $view = 'filament.pages.kpo-books';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.moja_kompanija');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.menu_items.kpo_books');
    }

    public function getTitle(): string
    {
        return __('kpo.page_title');
    }

    public function getYearsWithInvoices(): array
    {
        return Invoice::query()
            ->where('user_id', Auth::id())
            ->whereNotNull('issue_date')
            ->select(DB::raw('YEAR(issue_date) as year'))
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get()
            ->map(function ($item) {
                $year = $item->year;
                $invoiceCount = Invoice::where('user_id', Auth::id())
                    ->whereYear('issue_date', $year)
                    ->count();

                $totalAmount = Invoice::where('user_id', Auth::id())
                    ->whereYear('issue_date', $year)
                    ->sum('amount');

                return [
                    'year' => $year,
                    'invoice_count' => $invoiceCount,
                    'total_amount' => number_format($totalAmount, 2),
                    'currency' => 'RSD',
                ];
            })
            ->toArray();
    }
}
