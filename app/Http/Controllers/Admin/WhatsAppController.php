<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * WhatsApp dashboard
     */
    public function index()
    {
        $status = $this->whatsapp->checkStatus();
        
        return view('admin.whatsapp.index', [
            'connected' => $status !== null,
            'status' => $status
        ]);
    }

    /**
     * Send custom message
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:4096',
        ]);

        $result = $this->whatsapp->send($validated['phone'], $validated['message']);

        if ($result['success']) {
            return back()->with('success', 'Pesan berhasil dikirim');
        }

        return back()->with('error', 'Gagal mengirim pesan: ' . ($result['message'] ?? 'Unknown error'));
    }

    /**
     * Send invoice notification
     */
    public function sendInvoice(Invoice $invoice)
    {
        $customer = $invoice->customer;

        if (!$customer || !$customer->phone) {
            return response()->json([
                'success' => false,
                'message' => 'Customer phone not found'
            ], 404);
        }

        $result = $this->whatsapp->sendInvoiceNotification($customer, $invoice);

        return response()->json($result);
    }

    /**
     * Send payment reminder
     */
    public function sendReminder(Invoice $invoice)
    {
        $customer = $invoice->customer;

        if (!$customer || !$customer->phone) {
            return response()->json([
                'success' => false,
                'message' => 'Customer phone not found'
            ], 404);
        }

        $result = $this->whatsapp->sendPaymentReminder($customer, $invoice);

        return response()->json($result);
    }

    /**
     * Bulk send invoice notifications
     */
    public function bulkSendInvoice(Request $request)
    {
        $invoiceIds = $request->input('invoice_ids', []);
        
        if (empty($invoiceIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No invoices selected'
            ], 400);
        }

        $invoices = Invoice::with('customer', 'package')
            ->whereIn('id', $invoiceIds)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;
            
            if ($customer && $customer->phone) {
                $result = $this->whatsapp->sendInvoiceNotification($customer, $invoice);
                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                }
            } else {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sent: {$sent}, Failed: {$failed}",
            'sent' => $sent,
            'failed' => $failed
        ]);
    }

    /**
     * Bulk send payment reminders
     */
    public function bulkSendReminder(Request $request)
    {
        $invoiceIds = $request->input('invoice_ids', []);
        
        if (empty($invoiceIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No invoices selected'
            ], 400);
        }

        $invoices = Invoice::with('customer', 'package')
            ->whereIn('id', $invoiceIds)
            ->where('status', 'unpaid')
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $customer = $invoice->customer;
            
            if ($customer && $customer->phone) {
                $result = $this->whatsapp->sendPaymentReminder($customer, $invoice);
                if ($result['success']) {
                    $sent++;
                } else {
                    $failed++;
                }
            } else {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sent: {$sent}, Failed: {$failed}",
            'sent' => $sent,
            'failed' => $failed
        ]);
    }

    /**
     * Check WhatsApp status
     */
    public function status()
    {
        $status = $this->whatsapp->checkStatus();

        return response()->json([
            'connected' => $status !== null,
            'data' => $status
        ]);
    }
}
