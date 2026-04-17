<?php

namespace App\Filament\Pages;

use App\Jobs\ProcessKpoUploadJob;
use App\Models\Invoice;
use App\Models\KpoUpload;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadKpo')
                ->label('Otpremi KPO')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    FileUpload::make('file')
                        ->label('KPO PDF Fajl')
                        ->acceptedFileTypes(['application/pdf'])
                        ->directory('kpo-uploads')
                        ->disk('s3')
                        ->preserveFilenames()
                        ->required()
                        ->helperText('Otpremite KPO knjigu u PDF formatu. Sistem će automatski izvući sve unose.')
                        ->maxSize(10240),
                ])
                ->action(function (array $data): void {
                    $filePath = $data['file'];

                    $kpoUpload = KpoUpload::create([
                        'user_id' => auth()->id(),
                        'file_path' => $filePath,
                        'file_name' => basename($filePath),
                        'file_size' => Storage::disk('s3')->size($filePath),
                        'mime_type' => 'application/pdf',
                        'status' => 'pending',
                    ]);

                    ProcessKpoUploadJob::dispatch($kpoUpload);

                    Notification::make()
                        ->title('KPO upload u obradi')
                        ->body('Fajl je uspešno otpremljen i obrada je započeta. Proces može trajati nekoliko minuta.')
                        ->success()
                        ->send();
                })
                ->modalHeading('Otpremi KPO Knjigu')
                ->modalDescription('Nakon otpremanja, sistem će automatski izvući sve unose iz PDF-a, pronaći postojeće klijente po imenu, i kreirati nove klijente ako ne postoje.')
                ->modalSubmitActionLabel(__('actions.submit_and_process'))
                ->modalWidth('lg'),
        ];
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
