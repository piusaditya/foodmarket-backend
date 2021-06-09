<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
  //
  public function all(Request $request)
  {
    $id = $request->input('id');
    $limit = $request->input('limit', 6);
    $food_id = $request->input('food_id');
    $status = $request->input('status');

    if ($id) {

      //relasi food dan user
      $transaction = Transaction::with(['food', 'user'])->find($id);

      if ($transaction) {
        return ResponseFormatter::success(
          $transaction,
          'Data transaksi berhasil diambil'
        );
      } else {
        return ResponseFormatter::error(
          null,
          'Data transaksi tidak ada',
          404
        );
      }
    }

    // menampilkan hanya transaksi pada masing2 user
    $transaction = Transaction::with(['food', 'user'])->where('user_id', Auth::user()->id);

    if ($food_id) {
      $transaction->where('food_id', $food_id);
    }

    if ($status) {
      $transaction->where('status', $status);
    }

    return ResponseFormatter::success(
      $transaction->paginate($limit),
      'Data list transaksi berhasil diambil'
    );
  }


  // update transaksi
  public function update(Request $request, $id)
  {
    $transaction = Transaction::findOrFail($id);

    $transaction->update($request->all());

    return ResponseFormatter::success($transaction, 'Transaksi berhasil diperbarui');
  }


  //API checkout midtrans
  public function checkout(Request $request)
  {
    //validasi
    $request->validate([
      'food_id' => 'required|exists:food,id',
      'user_id' => 'required|exists:users,id',
      'quantity' => 'required',
      'total' => 'required',
      'status' => 'status'
    ]);

    $transaction = Transaction::create([
      'food_id' => $request->food_id,
      'user_id' => $request->user_id,
      'quantity' => $request->quantity,
      'total' => $request->total,
      'status' => $request->status,
      'payment_url' => '',
    ]);

    // Konfigurasi Midtrans
    Config::$serverKey = config('services.midtrans.serverKey');
    Config::$isProduction = config('services.midtrans.isPro$isProduction');
    Config::$isSanitized = config('services.midtrans.isSa$isSanitized');
    Config::$is3ds = config('services.midtrans.isSa$is3ds');

    // Memanggil transaksi yg dibuat
    $transaction = Transaction::with(['food', 'user'])->find($transaction->id);

    // Membuat transaksi Midtrans
    $midtrans = [
      'transaction_details' => [
        'order_id' => $transaction->id,
        'gross_amount' => (int) $transaction->total,
      ],
      'customer_details' => [
        'first_name' => $transaction->user->name,
        'email' => $transaction->user->email,
      ],
      'enabled_payments' => ['gopay', 'bank_transfer'],
      'vt_web' => []
    ];

    // Memanggil Midtrans
    try {
      // Ambil halaman payment Midtrans
      $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

      $transaction->payment_url = $paymentUrl;
      $transaction->save();

      // Mengembalikan data ke API
      return ResponseFormatter::success($transaction, 'Transaksi berhasil');
    } catch (Exception $e) {
      return ResponseFormatter::error($e->getMessage(), 'Transaksi gagal');
    }
  }
}
