<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class IntegrationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Mikrotik Settings
            ['key' => 'mikrotik_host', 'value' => '192.168.1.1', 'group' => 'mikrotik'],
            ['key' => 'mikrotik_port', 'value' => '8728', 'group' => 'mikrotik'],
            ['key' => 'mikrotik_username', 'value' => 'admin', 'group' => 'mikrotik'],
            ['key' => 'mikrotik_password', 'value' => '', 'group' => 'mikrotik'],
            
            // GenieACS Settings
            ['key' => 'genieacs_url', 'value' => 'http://localhost:7557', 'group' => 'genieacs'],
            ['key' => 'genieacs_username', 'value' => 'admin', 'group' => 'genieacs'],
            ['key' => 'genieacs_password', 'value' => 'admin', 'group' => 'genieacs'],
            
            // WhatsApp Settings
            ['key' => 'whatsapp_api_url', 'value' => 'https://api.fonnte.com', 'group' => 'whatsapp'],
            ['key' => 'whatsapp_api_key', 'value' => '', 'group' => 'whatsapp'],
            ['key' => 'whatsapp_sender', 'value' => '', 'group' => 'whatsapp'],
            
            // Midtrans Settings
            ['key' => 'midtrans_server_key', 'value' => '', 'group' => 'midtrans'],
            ['key' => 'midtrans_client_key', 'value' => '', 'group' => 'midtrans'],
            ['key' => 'midtrans_is_production', 'value' => 'false', 'group' => 'midtrans'],
            
            // Xendit Settings
            ['key' => 'xendit_secret_key', 'value' => '', 'group' => 'xendit'],
            ['key' => 'xendit_callback_token', 'value' => '', 'group' => 'xendit'],
            
            // Payment Settings
            ['key' => 'default_payment_gateway', 'value' => 'midtrans', 'group' => 'payment'],
            
            // Billing Settings
            ['key' => 'billing_due_day', 'value' => '25', 'group' => 'billing'],
            ['key' => 'billing_reminder_days', 'value' => '3,1', 'group' => 'billing'],
            ['key' => 'billing_suspend_days', 'value' => '7', 'group' => 'billing'],
            ['key' => 'billing_auto_generate', 'value' => 'true', 'group' => 'billing'],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group'] ?? 'general']
            );
        }
    }
}
