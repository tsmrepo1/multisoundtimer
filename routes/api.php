<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SoundController;
use App\Http\Controllers\Api\TimerController as ApiTimerController;
use App\Http\Middleware\LogRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::post('register', [AuthController::class, 'register']);
Route::post('register', [AuthController::class, 'signup']);
Route::get('register', [AuthController::class, 'signup']);
Route::post('email-exist', [AuthController::class, 'is_email_exist']);
Route::post('contact', [AuthController::class, 'sendcontact']);


Route::get('upgrade-account', [AuthController::class, 'upgrade_account'])->middleware("auth:api");
Route::get('upgrade-account-success', [AuthController::class, 'upgrade_account_success']);
Route::get('upgrade-account-cancel', [AuthController::class, 'upgrade_account_cancel']);

Route::get('success', [AuthController::class, 'payment_status_success']);
Route::get('cancel', [AuthController::class, 'payment_status_failed']);

Route::get('account-type', [AuthController::class, 'account_type']);
Route::get('how-did-you-find-us', [AuthController::class, 'find_us']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forget-password', [AuthController::class, 'forget_password']);
Route::post('update-forget-password', [AuthController::class, 'update_forget_password']);
Route::post('verify-email/', [AuthController::class, 'verify_email_with_code']);
Route::middleware('auth:api')->group(function(){
    //AUTHENTICATION ROUTE
    Route::get('user-info',[AuthController::class, 'user_info']);
    Route::get('edit-account',[AuthController::class, 'edit_account']);
    Route::post('edit-account-action',[AuthController::class, 'edit_account_action']);
    Route::post('delete-account',[AuthController::class, 'delete_account_action']);
    Route::get('send-verify-email/{email}', [AuthController::class, 'verify_email']);
    Route::post('logout',[AuthController::class, 'logout']);
    
    //TIMER ROUTE
    Route::get('/timer-list', [ApiTimerController::class, 'timer_list'])->name('timer_list');
    Route::post('/timer-details', [ApiTimerController::class, 'timer_details'])->name('timer_details');
    Route::post('/add-timer-action', [ApiTimerController::class, 'add_timer_action'])->name('add_timer_action');
    Route::post('/edit-timer-action', [ApiTimerController::class, 'edit_timer_action'])->name('edit_timer_action');
    Route::post('/delete-timer', [ApiTimerController::class, 'delete_timer'])->name('delete_timer');
    Route::post('/duplicate-timer', [ApiTimerController::class, 'duplicate_timer'])->name('duplicate_timer');
    Route::post('/favourite', [ApiTimerController::class, 'favourite'])->name('favourite');
    Route::get('/favourite-tab', [ApiTimerController::class, 'favourite_tab'])->name('favourite_tab');

    //NOTIFICATIONS ROUTE
    Route::get('/notification-list',[NotificationController::class,'notification_list'])->name('notification_list');
    Route::post('/notification-action',[NotificationController::class,'notification_action'])->name('notification_action');

    //SOUND ROUTE
    Route::get('sound-cat',[SoundController::class,'sound_cat'])->name('sound_cat');
    Route::get('sound-list',[SoundController::class,'sound_list'])->name('sound_list');
    Route::get('own-sound',[SoundController::class,'presound'])->name('pre-conf-soundlist');
    Route::post('select-sound', [SoundController::class,'select_sound'])->name('select_sound');

    //COLLECTION ROUTE
    Route::get('/collection-list', [CollectionController::class, 'collection_list'])->name('collection_list');
    Route::post('/add-collection', [CollectionController::class, 'add_collection'])->name('add_collection');
    Route::post('/delete-collection', [CollectionController::class, 'delete_collection'])->name('delete_coll');
    Route::post('/update-collection', [CollectionController::class, 'update_collection'])->name('update_collection');
    Route::get('/timer-playlist', [CollectionController::class, 'timer_playlist'])->name('timer_playlist');


});
