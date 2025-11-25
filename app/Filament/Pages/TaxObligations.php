<?php

namespace App\Filament\Pages;

use App\Helpers\NbsQrCodeHelper;
use App\Jobs\ProcessTaxResolutionJob;
use App\Models\TaxObligation;
use App\Models\TaxResolution;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class TaxObligations extends Page
{
    protected static ?string $title = 'Poreske obaveze';

    protected static ?int $navigationSort = 16;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.tax-obligations';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.moja_kompanija');
    }

    public static function getNavigationLabel(): string
    {
        return 'Poreske obaveze';
    }

    public function getTitle(): string
    {
        return 'Poreske obaveze';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadResolution')
                ->label('Otpremi Rešenje')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    Select::make('type')
                        ->label('Tip rešenja')
                        ->options([
                            'pio' => 'DOPRINOS ZA PIO',
                            'porez' => 'POREZ NA PRIHODE',
                        ])
                        ->required(),
                    Select::make('year')
                        ->label('Godina')
                        ->options(array_combine(
                            range(now()->year - 2, now()->year + 1),
                            range(now()->year - 2, now()->year + 1)
                        ))
                        ->default(now()->year)
                        ->required(),
                    FileUpload::make('file')
                        ->label('PDF Rešenje')
                        ->acceptedFileTypes(['application/pdf'])
                        ->directory('tax-resolutions')
                        ->disk('s3')
                        ->preserveFilenames()
                        ->required()
                        ->helperText('Otpremite poresko rešenje u PDF formatu.'),
                ])
                ->action(function (array $data): void {
                    $taxResolution = TaxResolution::create([
                        'user_id' => auth()->id(),
                        'file_path' => $data['file'],
                        'file_name' => basename($data['file']),
                        'file_size' => Storage::disk('s3')->size($data['file']),
                        'mime_type' => 'application/pdf',
                        'year' => $data['year'],
                        'type' => $data['type'],
                        'status' => 'pending',
                    ]);

                    ProcessTaxResolutionJob::dispatch($taxResolution);

                    Notification::make()
                        ->title('Rešenje otpremljeno')
                        ->body('Rešenje je otpremljeno i obrada je započeta.')
                        ->success()
                        ->send();
                })
                ->modalHeading('Otpremi Poresko Rešenje')
                ->modalSubmitActionLabel('Otpremi i Obradi')
                ->modalWidth('lg'),
        ];
    }

    public function getObligations()
    {
        return TaxObligation::where('user_id', auth()->id())
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get()
            ->groupBy('year');
    }

    public function getResolutions()
    {
        return TaxResolution::where('user_id', auth()->id())
            ->orderBy('year', 'desc')
            ->get();
    }

    public function downloadResolution(int $resolutionId)
    {
        $resolution = TaxResolution::where('user_id', auth()->id())
            ->findOrFail($resolutionId);

        $url = Storage::disk('s3')->temporaryUrl(
            $resolution->file_path,
            now()->addMinutes(5)
        );

        return redirect($url);
    }

    public function markObligationAsPaid(int $obligationId, $isPaid, $paidAt = null): void
    {
        $obligation = TaxObligation::where('user_id', auth()->id())
            ->findOrFail($obligationId);

        if ($isPaid) {
            $obligation->markAsPaid($paidAt ? new \DateTime($paidAt) : null);

            Notification::make()
                ->title('Obaveza označena kao plaćena')
                ->success()
                ->send();
        } else {
            $obligation->update([
                'status' => 'pending',
                'paid_at' => null,
            ]);

            Notification::make()
                ->title('Status obaveze ažuriran')
                ->success()
                ->send();
        }
    }

    public function generateQrCode(int $obligationId): ?string
    {
        try {
            $obligation = TaxObligation::where('user_id', auth()->id())
                ->findOrFail($obligationId);

            $user = auth()->user();

            $qrData = [
                'recipient_account' => $obligation->payment_recipient_account,
                'recipient_name' => $obligation->type === 'pio'
                    ? 'REPUBLIČKOG FONDA ZA PENZIJSKO I INVALIDSKO OSIGURANJE'
                    : 'PORESKA UPRAVA',
                'amount' => $obligation->amount,
                'payment_code' => $obligation->payment_code ?? '253',
                'purpose' => $obligation->description,
                'model' => $obligation->payment_model,
                'reference_number' => $obligation->payment_reference,
                'payer_name' => $user->company_name ?? $user->name,
            ];

            return NbsQrCodeHelper::generateQrCodeBase64($qrData, 250);
        } catch (\Exception $e) {
            \Log::error('QR code generation failed', [
                'obligation_id' => $obligationId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
