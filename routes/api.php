<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\DeviceHistoryController;
use App\Http\Controllers\Api\Payroll\EmployeeController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\TicketExportController;
use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //devices
    Route::get('/users', [UserController::class, 'all']);
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/employees/search', [EmployeeController::class, 'search']);

    Route::prefix('/devices')->group(function(){
        // Route::get('/', [DeviceController::class, 'index']);
        Route::get('/by-type', [DeviceController::class, 'indexByType']);
        Route::get('/{id}', [DeviceController::class, 'show']);
        Route::post('/', [DeviceController::class, 'store']);
        Route::put('/{id}', [DeviceController::class, 'update']);
        Route::delete('/{id}', [DeviceController::class, 'destroy']);
    });

    Route::prefix('/histories')->group(function(){
        Route::post('/', [DeviceHistoryController::class, 'store']);
        Route::put('/{id}', [DeviceHistoryController::class, 'update']);
        // Route::delete('/{id}', [DeviceHistoryController::class, 'destroy']);
    });

    Route::get('/employees/{employeeid}', [EmployeeController::class, 'show']);

    //tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets-user', [TicketController::class, 'storeUser']);
    Route::patch('/tickets/{ticket}/details', [TicketController::class, 'updateDetails']);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
    Route::put('/tickets/resolve/{ticket}', [TicketController::class, 'resolveTicket']);
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
    
    Route::get('/tickets/export-pdf', [TicketExportController::class, 'exportPdf']);

    Route::get('/users', [UserController::class, 'all']);

    Route::post('/save-fcm-token', [UserController::class, 'fcmToken']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::get('/tickets/count', [TicketController::class, 'count']);
Route::post('/tickets', [TicketController::class, 'store']);

Route::prefix('/devices')->group(function(){
    Route::get('/', [DeviceController::class, 'index']);
});

Route::get('/employees-all', [EmployeeController::class, 'all']);

Route::get('/tickets/{uid}', [TicketController::class, 'show']);

Route::get('/test', function(){
    return response()->json([
        "message" => "success"
    ], 200);
});

Route::get('/test-notification', function () {

    // -------------------------
    // Hardcoded FCM token
    // -------------------------
    $token = 'emNWDCxzJf5ou_2wTkVzfQ:APA91bEAnz8p1T-YA87h3f0GYD43G-HLvgpL7exjiyFseWi7YApFnlx-auxgN8gno4h6jJHutcf_yjXlDT9DCHDTPhos6Gmoq0xnZGK7sU41x8YY9HLltZU'; // <- Replace this with your token

    // Notification content
    $title = 'Test Notification';
    $body = 'Hello! This is a test notification.';

    // Initialize Firebase
    $factory = (new Factory)
        ->withServiceAccount(storage_path('app/firebase.json'));

    $messaging = $factory->createMessaging();

    try {
        // Send to single token
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body));

        $messaging->send($message);

        return response()->json([
            'success' => true,
            'message' => "Notification sent to token."
        ]);

    } catch (\Kreait\Firebase\Exception\MessagingException $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    } catch (\Kreait\Firebase\Exception\FirebaseException $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});