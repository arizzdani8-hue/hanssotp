<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\PaymentMethod;
use App\Services\PaymentGateways\DompetxGateway;
use App\Services\PaymentGateways\PakasirGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    public function index()
    {
        $deposits = Deposit::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('user.deposits.index', compact('deposits'));
    }

    public function create()
    {
        $methods = PaymentMethod::active()->get();
        return view('user.deposits.create', compact('methods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:10000000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $method = PaymentMethod::findOrFail($request->payment_method_id);
        $amount = (float) $request->amount;
        $fee = $method->fee_flat + ($amount * $method->fee_percent / 100);
        $total = $amount + $fee;

        $invoiceId = Deposit::generateInvoiceId();

        $gateway = match ($method->gateway) {
            'dompetx' => new DompetxGateway(),
            'pakasir' => new PakasirGateway(),
            default => throw new \Exception('Payment gateway tidak tersedia'),
        };

        $result = $gateway->createPayment($total, $invoiceId, "Deposit Saldo #{$invoiceId}");

        $deposit = Deposit::create([
            'invoice_id' => $invoiceId,
            'user_id' => Auth::id(),
            'payment_method_id' => $method->id,
            'gateway' => $method->gateway,
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'status' => 'pending',
            'gateway_reference' => $result['reference'],
            'qr_url' => $result['qr_url'],
            'checkout_url' => $result['checkout_url'],
            'gateway_response' => $result['raw_response'] ?? null,
            'expires_at' => $result['expires_at'],
        ]);

        return redirect()->route('user.deposits.show', $deposit);
    }

    public function show(Deposit $deposit)
    {
        abort_if($deposit->user_id !== Auth::id(), 403);
        return view('user.deposits.show', compact('deposit'));
    }
}
