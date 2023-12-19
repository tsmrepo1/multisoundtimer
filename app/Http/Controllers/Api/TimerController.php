<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sound;
use App\Models\Timer;
use App\Models\TimerSegment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class TimerController extends Controller
{
    //GET TIMER LIST
    public function timer_list(Request $request)
    {
        $token = $request->bearerToken();
        $check_token = User::where('remember_token', $token)->first();
       
        if ($check_token) {
            //echo auth()->user()->id;die;
            $timer_timeline = Timer::where('user_id', auth()->user()->id)->where('status', '=', 1)->latest()->get();
            //print_r($timer_timeline);die;
            $timer_segments = [];

            foreach ($timer_timeline as $timer) {
                $arr = [];
                if ($timer->favourite == 1) {
                    $arr['stat'] = true;
                } else {
                    $arr['stat'] = false;
                }
                $arr['id'] = $timer->id;
                $arr['timer_title'] = $timer->timer_title;
                $arr['timer_subhead'] = $timer->timer_subhead;
                $arr['start_sound'] = $timer->start_sound;

                $obj = TimerSegment::where("timer_id", $timer->id)->get();
                
                $minutes = 0;
                foreach ($obj as $value) {
                    
                    list($hour, $minute) = explode(':', $value->duration);
                    $minutes += $hour * 60;
                    $minutes += $minute;
                }
                $hours = floor($minutes / 60);
                $minutes -= $hours * 60;
                $total_dur = sprintf('%02d:%02d', $hours, $minutes);
                $arr['duration'] = $total_dur;
                array_push($timer_segments, $arr);
            }
            return response()->json(['timer_segments' => $timer_segments], 200);
        } else {
            return response()->json(['timer_segments' => 'You are unauthorized.'], 400);
        }
        
    }

    //TIMER DETAILS
    public function timer_details(Request $req)
    {
        $token = $req->bearerToken();
        $check_token = User::where('remember_token',$token)->first();
        if($check_token){
            $timer_timeline = Timer::select('timers.id', 'timers.timer_title', 'timers.timer_subhead', 'timers.start_sound')->where('timers.id', '=', $req->id)->first();
            //print_r($timer_timeline);
            $timer_segments['fragments'] = array();

            $timer_segments['timer_title'] = $timer_timeline->timer_title;
            $timer_segments['timer_subhead'] = $timer_timeline->timer_subhead;
            $timer_segments['start_sound'] = $timer_timeline->start_sound;
            //$timer_segments['file'] = $timer_timeline->file;
            //return $timer_segments;die;
            $obj = TimerSegment::where("timer_id", $timer_timeline->id)->get();
            $minutes = 0;
            foreach ($obj as $value) {
                list($hour, $minute) = explode(':', $value->duration);
                $minutes += $hour * 60;
                $minutes += $minute;
            }
            $hours = floor($minutes / 60);
            $minutes -= $hours * 60;
            $seks = (($hours * 60) + $minutes) * 60;

            $total_dur = sprintf('%02d:%02d', $hours, $minutes);
            $timer_segments['duration'] = $total_dur;

            $obj = TimerSegment::select('timer_segments.id', 'timer_segments.segment_name', 'timer_segments.duration', 'timer_segments.end_sound', 'sounds.file')->join('sounds', 'sounds.id', '=', 'timer_segments.end_sound')->where("timer_segments.timer_id", $timer_timeline->id)->get();

            foreach ($obj as $val) {
                $arr1 = [];
                $arr1['id'] = $val->id;
                $arr1['name'] = $val->segment_name;
                $arr1['duration'] = $val->duration;
                $rr = explode(':', $val->duration); //convert a string into an array
                $h = $rr[0];
                $m = $rr[1];
                $sec = (($h*60)+$m)*60; //convert hours & minutes into second
                $arr1['secc'] = $sec;
                $arr1['perc'] = round(($sec/ $seks)*100); //return the rounded value
                $arr1['sound_name'] = $val->end_sound;
                $arr1['file'] = URL::to('/') . '/public/admin/assets/sound-list/' . $val->file;
                array_push($timer_segments['fragments'], $arr1);
            }
            return response()->json(['timer_segments' => $timer_segments], 200);
        }else{
            return response()->json(['timer_segments' => 'You are unauthorised.'], 200);
        }
        
    }

    //ADD TIMER
    public function add_timer_action(Request $req)
    {
       
        $timer = Timer::where('user_id',auth()->user()->id)->where('status',1)->get();
        $no_of_timer = count($timer);
        $user = auth()->user();
         
        if (($user->account_type == 1) && ($no_of_timer < 3)) {
            
            $add_timer = new Timer();
            $add_timer->user_id = auth()->user()->id;
            $add_timer->timer_title = $req->timer_title;
            $add_timer->timer_subhead = $req->timer_subhead;
            $sound = explode('*', $req->start_sound);
            $add_timer->start_sound ="0";
            $add_timer->status = 1;
            $add_timer->favourite = 0;
            $add_timer->flag = 0;
            if($add_timer->save()){
                
            for ($i = 0; $i < sizeof($req->seg_name); $i++) {

                $add_timers = new TimerSegment();
                $x = explode(":", $req->seg_dur[$i]);
                $add_timers->timer_id =  $add_timer->id;
                $add_timers->segment_name = $req->seg_name[$i];
                if (sizeof($x) > 1) {
                    $add_timers->duration = $req->seg_dur[$i];
                } else {
                    $add_timers->duration = $req->seg_dur[$i] . ":00";
                }
                $add_timers->end_sound = $req->seg_end[$i];
                $add_timers->save();  
            }

             return response()->json([
                        'status' => true, 'message' => 'Timer Added Successfully.'
                    ]);    
            }

            
        } elseif ($user->account_type == 2 && $no_of_timer < 30) {
            
            $add_timer = new Timer();
            $add_timer->user_id = auth()->user()->id;
            $add_timer->timer_title = $req->timer_title;
            $add_timer->timer_subhead = $req->timer_subhead;
            $add_timer->start_sound = "0";
            $add_timer->status = 1;
            $add_timer->favourite = 0;
            $add_timer->flag = 0;
            if($add_timer->save()){
                for ($i = 0; $i < sizeof($req->seg_name); $i++) {

                $add_timers = new TimerSegment();
                $x = explode(":", $req->seg_dur[$i]);
                $add_timers->timer_id =  $add_timer->id;
                $add_timers->segment_name = $req->seg_name[$i];
                if (sizeof($x) > 1) {
                    $add_timers->duration = $req->seg_dur[$i];
                } else {
                    $add_timers->duration = $req->seg_dur[$i] . ":00";
                }
                $add_timers->end_sound = $req->seg_end[$i];
                $add_timers->save();
            }

             return response()->json([
                        'status' => true, 'message' => 'Timer Added Successfully.'
                    ]);
            }

            
        } elseif ($user->account_type == 3 && $no_of_timer < 30) {
            $add_timer = new Timer();
            $add_timer->user_id = auth()->user()->id;
            $add_timer->timer_title = $req->timer_title;
            $add_timer->timer_subhead = $req->timer_subhead;
            $add_timer->start_sound = "0";
            $add_timer->status = 1;
            $add_timer->favourite = 0;
            $add_timer->flag = 0;
            if($add_timer->save()){
                for ($i = 0; $i < sizeof($req->seg_name); $i++) {

                $add_timers = new TimerSegment();
                $x = explode(":", $req->seg_dur[$i]);
                $add_timers->timer_id =  $add_timer->id;
                $add_timers->segment_name = $req->seg_name[$i];
                if (sizeof($x) > 1) {
                    $add_timers->duration = $req->seg_dur[$i];
                } else {
                    $add_timers->duration = $req->seg_dur[$i] . ":00";
                }
                $add_timers->end_sound = $req->seg_end[$i];
                $add_timers->save();
            }

             return response()->json([
                        'status' => true, 'message' => 'Timer Added Successfully.'
                    ]);    
            }

            
        } else {
            if ($user->account_type == 1 && $no_of_timer >= 3) {
                return response()->json([
                    'status' => false, 'message' => 'You are not allowed to create more than 3 timers.'
                ]);
            } elseif ($user->account_type == 2 && $no_of_timer >= 30) {
                return response()->json([
                    'status' => false, 'message' => 'You are not allowed to create more than 30 timers.'
                ]);
            } elseif ($user->account_type == 3 && $no_of_timer >= 30) {
                return response()->json([
                    'status' => false, 'message' => 'You are not allowed to create more than 30 timers.'
                ]);
            }
        }
    }

    //UPDATE TIMER
    public function edit_timer_action(Request $req)
    {
        $update_timer = Timer::where("id", $req->timer_id)->update([
            "timer_title" => $req->timer_title,
            "timer_subhead" => $req->timer_subhead,
            "start_sound" => $req->start_sound,
            "status" => 1,
            "favourite" => 0,
            'flag' => 0

        ]);

        for ($i = 0; $i < sizeof($req->old_seg_name); $i++) {
         //  echo $req->old_seg_ids[$i];
            TimerSegment::where("id", $req->old_seg_ids[$i])->update([
                "segment_name" => $req->old_seg_name[$i],
                "duration" => $req->old_seg_dur[$i],
                "end_sound" => $req->old_seg_end[$i],
            ]);
        }
        if(isset($req->seg_name)){
            for ($i = 0; $i < sizeof($req->seg_name); $i++) {
                $new_timer_seg = new TimerSegment();
                $new_timer_seg->timer_id = $req->timer_id;
                $new_timer_seg->segment_name = $req->seg_name[$i];
                $x = explode(":", $req->seg_dur[$i]);
                if (sizeof($x) > 1) {
                    //echo "ok";
                    $new_timer_seg->duration = $req->seg_dur[$i];
                } else {
                    $new_timer_seg->duration = $req->seg_dur[$i] . ":00";
                }
                
                $new_timer_seg->end_sound = $req->seg_end[$i];
                $new_timer_seg->save();
            }
        }else{
            
        }
        return response()->json([
                'success' => true, 'message' => 'Timer Updated Successfully.'
            ]);
    }

    //DUPLICATE TIMER
    public function duplicate_timer(Request $req)
    {
        $token = $req->bearerToken();
        $check_token = User::where('remember_token',$token)->first();
        if($check_token){
            $tmr = new Timer();
            $timers = Timer::where('id', $req->id)->first();
            $tmr->user_id = $timers->user_id;
            $tmr->timer_title = $timers->timer_title;
            $tmr->timer_subhead = $timers->timer_subhead;
            $tmr->start_sound = $timers->start_sound;
            $tmr->status = $timers->status;
            $tmr->flag = 0;
            $tmr->favourite = $timers->favourite;

            $tmr->save();
            $timer_id = $tmr->id;
            $timer_segments = TimerSegment::where('timer_id', $req->id)->get();
            
            foreach ($timer_segments as $value) {
                $segment = new TimerSegment();
                $segment->timer_id = $timer_id;
                $segment->segment_name = $value->segment_name;
                $segment->duration = $value->duration;
                $segment->end_sound = $value->end_sound;
                $segment->save();
            }
            return response()->json(['success' => true, 'message' => 'Timer Duplicated Successfully.']);
        }else{
            return response()->json(['message' => 'You are unauthorised.']);
        }
        
    }

    //DELETE TIMER
    public function delete_timer(Request $req)
    {
        $token = $req->bearerToken();
        $check_token = User::where('remember_token',$token)->first();
        if($check_token){
            $data = DB::table('timers')->select('status')->where('id', '=', $req->id)->first();

            //check post status

            if ($data->status == '1') {
                $status = '0';
            } else {
                $status = '1';
            }

            //update post status

            $data = array('status' => $status);
            $delete_timer  = DB::table('timers')->where('id', $req->id)->update($data);
            return response()->json(['success' => true, 'message' => 'Timer Deleted Successfully.']);
        }else{
            return response()->json(['message' => 'You are unauthorised.']);
        }
        
    }

    //FAVOURITE TIMER
    public function favourite(Request $req)
    {
        $token = $req->bearerToken();
        $check_token = User::where('remember_token', $token)->first();
        if($check_token){
            $timer = Timer::find($req->id);
            $timer->favourite = 1;
            $timer->created_at = Carbon::now();
            $timer->save();
            return response()->json(['success' => true, 'message' => 'Timer is now favourite.']);
        }else{
            return response()->json(['message' => 'You are unauthorised.']);
        }
        
    }

    //FAVOURITE TAB

    public function favourite_tab(Request $req)
    {
        $token = $req->bearerToken();
        $check_token = User::where('remember_token', $token)->first();
        if($check_token){
           // echo auth()->user()->id;die;
            $timer_timeline = Timer::where('user_id', auth()->user()->id)->where('status', 1)->where('favourite', 1)->latest()->get();
           // print_r($timer_timeline);die;
            $timer_segments = [];

            foreach ($timer_timeline as $timer) {
                $arr = [];
                if ($timer->favourite == 1) {
                    $arr['stat'] =  true;
                } else {
                    $arr['stat'] = false;
                }
                $arr['id'] = $timer->id;
                $arr['timer_title'] = $timer->timer_title;
                $arr['timer_subhead'] = $timer->timer_subhead;
                $arr['start_sound'] = $timer->start_sound;

                $obj = TimerSegment::where("timer_id", $timer->id)->get();
                $minutes = 0;
                foreach ($obj as $value) {
                    list($hour, $minute) = explode(':', $value->duration);
                    $minutes += $hour * 60;
                    $minutes += $minute;
                }
                $hours = floor($minutes / 60);
                $minutes -= $hours * 60;
                $total_dur = sprintf('%02d:%02d', $hours, $minutes);
                $arr['duration'] = $total_dur;
                array_push($timer_segments, $arr);
            }
            return response()->json(['timer_segments' => $timer_segments], 200);
        }else{
            return response()->json(['timer_segments' => 'You are unauthorised.']);
        }
        
    }
}
