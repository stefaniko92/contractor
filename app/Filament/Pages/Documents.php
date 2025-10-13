<?php

namespace App\Filament\Pages;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Documents extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $title = 'Dokumenta';

    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.pages.documents';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.moja_kompanija');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.menu_items.documents');
    }

    public function getTitle(): string
    {
        return __('documents.page_title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Document::query()->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('name')
                    ->label(__('documents.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_type')
                    ->label(__('documents.fields.document_type'))
                    ->formatStateUsing(fn ($state) => $state ? Document::getDocumentTypes()[$state] ?? $state : '-')
                    ->sortable(),

                TextColumn::make('file_name')
                    ->label(__('documents.fields.file_name'))
                    ->searchable(),

                TextColumn::make('file_size_formatted')
                    ->label(__('documents.fields.file_size')),

                TextColumn::make('created_at')
                    ->label(__('documents.fields.uploaded_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label(__('documents.fields.document_type'))
                    ->options(Document::getDocumentTypes()),
            ])
            ->actions([
                Action::make('download')
                    ->label(__('documents.actions.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),

                Action::make('delete')
                    ->label(__('documents.actions.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('documents.actions.delete'))
                    ->modalSubmitActionLabel(__('documents.actions.delete'))
                    ->action(function (Document $record) {
                        Storage::disk('public')->delete($record->file_path);
                        $record->delete();

                        Notification::make()
                            ->title(__('documents.notifications.deleted'))
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('upload')
                    ->label(__('documents.actions.upload'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        TextInput::make('name')
                            ->label(__('documents.fields.name'))
                            ->required()
                            ->maxLength(255),

                        Select::make('document_type')
                            ->label(__('documents.fields.document_type'))
                            ->options(Document::getDocumentTypes())
                            ->required(),

                        Textarea::make('description')
                            ->label(__('documents.fields.description'))
                            ->rows(3)
                            ->maxLength(1000),

                        FileUpload::make('file')
                            ->label(__('documents.fields.file'))
                            ->required()
                            ->disk('public')
                            ->directory('documents')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(10240) // 10MB
                            ->preserveFilenames(),
                    ])
                    ->action(function (array $data) {
                        $file = $data['file'];
                        $filePath = Storage::disk('public')->path($file);
                        $fileSize = Storage::disk('public')->size($file);
                        $mimeType = Storage::disk('public')->mimeType($file);
                        $fileName = basename($file);

                        Document::create([
                            'user_id' => Auth::id(),
                            'name' => $data['name'],
                            'description' => $data['description'] ?? null,
                            'document_type' => $data['document_type'],
                            'file_path' => $file,
                            'file_name' => $fileName,
                            'file_size' => $fileSize,
                            'mime_type' => $mimeType,
                        ]);

                        Notification::make()
                            ->title(__('documents.notifications.uploaded'))
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
