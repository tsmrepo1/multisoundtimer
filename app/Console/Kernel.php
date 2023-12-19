<?php

namespace App\Console;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Stringable;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $users = DB::table("users")
                        ->join("account_types", "users.account_type", "=", "account_types.id")
                        ->where("account_types.id", 1)
                        ->select("users.id as user_id", "users.created_at as created_at")
                        ->get();
                        
            print_r($users);

            foreach($users as $user) {
                
                $since = Carbon::createFromDate($user->created_at);
                $now = Carbon::now();

                $diffDate = $now->diffInDays($since);
                
                if($diffDate > 30) {
                    Notification::create([
                        "header" => "Upgrade to our premium plan now!",
                        "message" => json_encode([
                            "Upgrade to our premium plan now!",

                            "Enjoy exclusive features, ad-free experience, enhanced support, data security, and
                            regular updates.",

                            "Don’t miss out! Upgrade today: [#]",

                            "Thank you for being a valued member.",

                            "Best regards,",
                            
                            "Multi-Sound Timer Team"
                        ]),
                        "to_user" => $user->user_id
                    ]);
                }
            }
        })->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
