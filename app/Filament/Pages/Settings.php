<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Settings')->tabs([
                Forms\Components\Tabs\Tab::make('General')->schema([
                    Forms\Components\TextInput::make('site_name')->label('Nama Website'),
                    Forms\Components\TextInput::make('site_tagline')->label('Tagline'),
                    Forms\Components\TextInput::make('site_url')->label('URL Website'),
                    Forms\Components\FileUpload::make('site_logo')->label('Logo')->image()->directory('settings'),
                    Forms\Components\FileUpload::make('site_favicon')->label('Favicon')->image()->directory('settings'),
                    Forms\Components\ColorPicker::make('primary_color')->label('Warna Utama'),
                    Forms\Components\Textarea::make('site_footer')->label('Footer Text'),
                ]),
                Forms\Components\Tabs\Tab::make('SEO')->schema([
                    Forms\Components\TextInput::make('meta_title')->label('Meta Title'),
                    Forms\Components\Textarea::make('meta_description')->label('Meta Description'),
                    Forms\Components\Textarea::make('meta_keywords')->label('Meta Keywords'),
                    Forms\Components\Textarea::make('google_analytics')->label('Google Analytics Code'),
                ]),
                Forms\Components\Tabs\Tab::make('DOMPETX')->schema([
                    Forms\Components\TextInput::make('dompetx_api_key')->label('API Key'),
                    Forms\Components\TextInput::make('dompetx_secret_key')->label('Secret Key')->password(),
                    Forms\Components\TextInput::make('dompetx_merchant_id')->label('Merchant ID'),
                    Forms\Components\TextInput::make('dompetx_api_url')->label('API URL')->default('https://dompetx.com/api/v1'),
                ]),
                Forms\Components\Tabs\Tab::make('Pakasir')->schema([
                    Forms\Components\TextInput::make('pakasir_api_key')->label('API Key'),
                    Forms\Components\TextInput::make('pakasir_secret_key')->label('Secret Key')->password(),
                    Forms\Components\TextInput::make('pakasir_merchant_id')->label('Merchant ID'),
                    Forms\Components\TextInput::make('pakasir_api_url')->label('API URL')->default('https://pakasir.com/api/v1'),
                ]),
                Forms\Components\Tabs\Tab::make('WhatsApp Gateway')->schema([
                    Forms\Components\Select::make('whatsapp_gateway')
                        ->options(['fonnte' => 'Fonnte', 'mpwa' => 'MPWA'])
                        ->default('fonnte'),
                    Forms\Components\TextInput::make('fonnte_token')->label('Fonnte Token')->password(),
                    Forms\Components\TextInput::make('mpwa_api_url')->label('MPWA API URL'),
                    Forms\Components\TextInput::make('mpwa_token')->label('MPWA Token')->password(),
                    Forms\Components\TextInput::make('whatsapp_number')->label('Nomor WhatsApp CS'),
                ]),
                Forms\Components\Tabs\Tab::make('Turnstile')->schema([
                    Forms\Components\Toggle::make('turnstile_enabled')->label('Enable Cloudflare Turnstile'),
                    Forms\Components\TextInput::make('turnstile_site_key')->label('Site Key'),
                    Forms\Components\TextInput::make('turnstile_secret_key')->label('Secret Key')->password(),
                ]),
            ])->columnSpanFull(),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
