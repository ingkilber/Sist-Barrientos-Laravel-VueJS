<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Zend\Diactoros\Request;

class cornEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hour:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an hourly email to all the users';

    /**
     * Create a new command instance.
     *
     * @return void
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * $cron_status = $request->cron_status;
    Setting::where('setting_name','offday_setting')->update(['setting_value'=> $cron_status]);
     */
    public function handle()
    {
        /*\DB::table('settings')
        ->where('setting_name','offday_setting')->update(['setting_value'=> '1']);
        */

        \DB::table('settings')
            ->where('setting_name','offday_setting')
            ->update(['setting_value'=>'11']);

        $users = User::all();

        foreach ( $users as $user){

            Mail::raw("This is automatically generated Update", function ($message) use ($user){
                $message->from('gain@booking.com', 'Gain Booking');
                $message->to($user->email)->subject('New Service/ Event Notification');
            });
        }

        $this->info('New Service/ Event Notification has been send successfully');
    }
}
