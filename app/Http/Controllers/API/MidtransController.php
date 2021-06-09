<?php

namespace App\Http\Controllers\API;

use Midtrans\Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Midtrans\Notification;

class MidtransController extends Controller
{
    // callback notifikasi midtrans
    public function callback(Request $request)
    {

        // Set konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isPro$isProduction');
        Config::$isSanitized = config('services.midtrans.isSa$isSanitized');
        Config::$is3ds = config('services.midtrans.isSa$is3ds');

        //  Buat instance Midtrans configuration
        $notification = new Notification();

        // Assign ke variable
        $status = $notification->transaction_status;
        $type = $notification->payment_status;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Cari transaksi berdasarkan ID
        $transaction = Transaction::findOrFail($order_id);

        // Handle notifikasi status Midtrans
        if ($status == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $transaction->status = 'PENDING';
                } else {
                    $transaction->status = 'SUCCESS';
                }
            } else if ($status == 'settlement') {
                $transaction->status = 'SUCCESS';
            } else if ($status == 'pending') {
                $transaction->status = 'PENDING';
            } else if ($status == 'deny') {
                $transaction->status = 'CANCELLED';
            } else if ($status == 'expire') {
                $transaction->status = 'CANCELLED';
            } else if ($status == 'cancel') {
                $transaction->status = 'CANCELLED';
            }
        }

        // Simpan transaksi
        $transaction->save();
    }

    // halaman success
    public function success()
    {
        return view('midtrans.success');
    }

    // halaman unfinish
    public function unfinish()
    {
        return view('midtrans.unfinish');
    }

    // halaman error
    public function error()
    {
        return view('midtrans.error');
    }
}
