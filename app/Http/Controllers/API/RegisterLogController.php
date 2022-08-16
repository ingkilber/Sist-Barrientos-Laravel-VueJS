<?php

namespace App\Http\Controllers\API;

use App\Models\CashRegisterLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RegisterLogController extends Controller
{
    public function index()
    {
        return CashRegisterLog::allData();
    }

    public function saveRegisterLog(Request $request)
    {
        $this->validate($request, [
            'cash_register_id' => 'required',
            'amount' => 'required',
            'status' => 'required',
        ]);

        CashRegisterLog::create([
            'cash_register_id' => $request->cash_register_id,
            'amount' => $request->amount,
            'status' => $request->status,
            'note' => $request->note,
            'user_id' => Auth::user()->id
        ]);
    }
}
