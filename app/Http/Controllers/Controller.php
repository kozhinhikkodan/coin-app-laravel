<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use App\Models\Coins;
// use Request
use Illuminate\Http\Request;
// use Validator
use Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // get all coins
    public function index()
    {
        $coins = Coins::all();
        foreach ($coins as $coin) {
            $coin->market = $coin->market?'UP':'DOWN';
        }
        return response()->json($coins);
    }

    // store a new coin
    public function store(Request $request)
    {
        // validator 
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'required|file',
            'price' => 'required|string|max:255',
            'market' => 'required|string|max:255',
        ]);
        // if validator fails return error
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // file upload to storage
        $file = $request->file('logo');
        $fileName = $file->getClientOriginalName();
        $file->move(public_path('images'), $fileName);
        // create new coin
        $coin = new Coins;
        $coin->name = $request->name;
        $coin->logo = $fileName;
        $coin->price = $request->price;
        $coin->save();
        return response()->json($coin);
    }

    // destroy a coin 
    public function destroy($id)
    {
        $coin = Coins::find($id);
        // if coin exists delete it
        if ($coin) {
            // if file exists delete it
            if (file_exists(public_path('images/' . $coin->logo))) {
                unlink(public_path('images/' . $coin->logo));
            }
            $response['message'] = 'Coin deleted successfully';
            $coin->delete();
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Coin not found'], 404);
        }
    }
}
