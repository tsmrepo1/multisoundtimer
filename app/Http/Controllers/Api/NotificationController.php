<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //GET ALL NOTIFICATION
    public function notification_list(Request $req){
        $token = $req->bearerToken();
        $check_token = User::where('remember_token', $token)->first();
        if($check_token){
            $notifications = Notification::where("to_user", auth()->user()->id)->orWhere("to_user", NULL)->latest()->get();
            
            $list = [];
            foreach($notifications as $notification) {
                $person_read = explode(",", $notification->read_person);
                $message = "";
                
                $notification->message = json_decode($notification->message);
                
                foreach($notification->message as $txt) {
                    if($message == "")
                        $message = $txt;
                    else
                        $message = $message."|".$txt;
                }
                
                $notification->message = $message;
                if(!in_array(auth()->user()->id, $person_read)) {
                    $list[] = $notification;
                }
            }
            
            return response()->json(['get_notification' => $list], 200);
        }else{
            return response()->json(['get_notification' => 'You are unauthorised.']);
        }
    }

    //NOTIFICATION ACTION
    public function notification_action(Request $req){
        $notification = Notification::find($req->id);
        
        $person_read = explode(",", $notification->read_person);
        $person_read[] = auth()->user()->id;
        
        $notification->read_person = implode(',', $person_read);
        
        $notification->save();
        
        return response()->json(['status' => "success"], 200);
    }
}
