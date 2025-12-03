<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;
    protected $sender;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->apiKey = config('services.whatsapp.api_key');
        $this->sender = config('services.whatsapp.sender');
    }

    /**
     * Send WhatsApp message
     */
    public function send($phone, $message)
    {
        try {
            $phone = $this->formatPhone($phone);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/send-message', [
                'phone' => $phone,
                'message' => $message,
                'sender' => $this->sender,
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent', ['phone' => $phone]);
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $response->json()
                ];
            }

            Log::error('WhatsApp send failed', [
                'phone' => $phone,
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send invoice notification
     */
    public function sendInvoiceNotification($customer, $invoice)
    {
        $message = "Halo *{$customer->name}*,\n\n";
        $message .= "Tagihan internet Anda telah terbit:\n\n";
        $message .= "📋 *Invoice:* {$invoice->invoice_number}\n";
        $message .= "📦 *Paket:* {$invoice->package->name}\n";
        $message .= "💰 *Total:* Rp " . number_format($invoice->amount, 0, ',', '.') . "\n";
        $message .= "📅 *Jatuh Tempo:* " . ($invoice->due_date ? $invoice->due_date->format('d M Y') : '-') . "\n\n";
        $message .= "Silakan lakukan pembayaran sebelum jatuh tempo.\n\n";
        $message .= "Terima kasih,\n";
        $message .= "*" . config('app.name') . "*";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation($customer, $invoice)
    {
        $message = "Halo *{$customer->name}*,\n\n";
        $message .= "✅ Pembayaran Anda telah kami terima!\n\n";
        $message .= "📋 *Invoice:* {$invoice->invoice_number}\n";
        $message .= "💰 *Jumlah:* Rp " . number_format($invoice->amount, 0, ',', '.') . "\n";
        $message .= "📅 *Tanggal Bayar:* " . ($invoice->paid_date ? $invoice->paid_date->format('d M Y') : now()->format('d M Y')) . "\n\n";
        $message .= "Terima kasih atas pembayaran Anda.\n\n";
        $message .= "*" . config('app.name') . "*";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send voucher to customer
     */
    public function sendVoucher($phone, $vouchers, $package)
    {
        $message = "🎫 *Voucher Internet Anda*\n\n";
        $message .= "Paket: *{$package}*\n\n";
        
        foreach ($vouchers as $index => $voucher) {
            $message .= "Voucher " . ($index + 1) . ":\n";
            $message .= "👤 Username: `{$voucher['code']}`\n";
            $message .= "🔑 Password: `{$voucher['password']}`\n\n";
        }
        
        $message .= "Cara pakai:\n";
        $message .= "1. Hubungkan ke WiFi\n";
        $message .= "2. Buka browser\n";
        $message .= "3. Masukkan username & password\n\n";
        $message .= "Terima kasih!\n";
        $message .= "*" . config('app.name') . "*";

        return $this->send($phone, $message);
    }

    /**
     * Send payment reminder
     */
    public function sendPaymentReminder($customer, $invoice)
    {
        $message = "⚠️ *Pengingat Pembayaran*\n\n";
        $message .= "Halo *{$customer->name}*,\n\n";
        $message .= "Tagihan Anda belum dibayar:\n\n";
        $message .= "📋 *Invoice:* {$invoice->invoice_number}\n";
        $message .= "💰 *Total:* Rp " . number_format($invoice->amount, 0, ',', '.') . "\n";
        $message .= "📅 *Jatuh Tempo:* " . ($invoice->due_date ? $invoice->due_date->format('d M Y') : '-') . "\n\n";
        $message .= "Mohon segera lakukan pembayaran untuk menghindari pemutusan layanan.\n\n";
        $message .= "*" . config('app.name') . "*";

        return $this->send($customer->phone, $message);
    }

    /**
     * Send suspension notice
     */
    public function sendSuspensionNotice($customer)
    {
        $message = "🚫 *Pemberitahuan Penangguhan Layanan*\n\n";
        $message .= "Halo *{$customer->name}*,\n\n";
        $message .= "Layanan internet Anda telah ditangguhkan karena tunggakan pembayaran.\n\n";
        $message .= "Silakan hubungi kami atau lakukan pembayaran untuk mengaktifkan kembali layanan Anda.\n\n";
        $message .= "*" . config('app.name') . "*";

        return $this->send($customer->phone, $message);
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }

    /**
     * Check connection status
     */
    public function checkStatus()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->apiUrl . '/status');

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
