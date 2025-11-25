<?php

namespace App\Filament\Resources\KpoEntries\Pages;

use App\Filament\Resources\KpoEntries\KpoEntryResource;
use App\Jobs\ProcessKpoUploadJob;
use App\Models\KpoUpload;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListKpoEntries extends ListRecords
{
    protected static string $resource = KpoEntryResource::class;

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
                ->modalSubmitActionLabel('Otpremi i Obradi')
                ->modalWidth('lg'),
        ];
    }
}
