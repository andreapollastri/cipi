<?php

namespace App\Http\Controllers;


use App\Models\Mysqluser;
use App\Models\Userdatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DatabaseController extends Controller
{

    public function viewdatabase(){
        $mysqluser = Mysqluser::all();
        $userdata = Userdatabase::all();
        return view('database', compact('mysqluser', 'userdata'));
    }

    public function createdatabase(Request $request)
    {
        dd($request->all());
        $database = new Userdatabase();
       
        $database->user_id = Auth::user()->id;
        $database->database_name = $request->data_name;
        
        if($database->save()){
            return redirect()->back()->with('success', 'You have successfully Created database!');

        }else{
            return redirect()->back()->with('failed', 'Unable to Create database!');
        }
    }

    public function createuser(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required|min:6|confirm',
            'conf_password' => 'required'
        ]);

        $mysql = new Mysqluser();
        $mysql->user_id = Auth::user()->id;
        $mysql->username = $request->username;
        $mysql->password = $request->password;
        $mysql->conf_password = $request->conf_password;

        if($request->password == $request->conf_password){
            if($mysql->save()){
                return redirect()->back()->with('success', 'You have successfully added User!');
            }else{
                return redirect()->back()->with('failed', 'Unable to add User!');
            }
        }else{
            return redirect()->back()->with('failed', 'Confirm Password does not match!');
        }
    }

    public function linkdatabaseuser(Request $request)
    {
    $mysqluserid = $request->username;
    $databaseId = $request->database;
    Userdatabase::where('id', $databaseId)->update(['mysqluser_id' => $mysqluserid]);

    }
}
