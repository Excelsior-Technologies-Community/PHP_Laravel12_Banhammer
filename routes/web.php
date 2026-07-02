<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BanController;

Route::get('/', [BanController::class, 'index'])->name('dashboard');

Route::post('/ban/{id}', [BanController::class, 'banUser'])->name('ban.user');
Route::get('/unban/{id}', [BanController::class, 'unbanUser'])->name('unban.user');
Route::post('/ban-ip', [BanController::class, 'banIP'])->name('ban.ip');
Route::post('/ban-multiple', [BanController::class, 'banMultiple'])->name('ban.multiple');
Route::post('/extend-ban/{id}', [BanController::class, 'extendBan'])->name('ban.extend');

Route::post('/issue-warning/{id}', [BanController::class, 'issueWarning'])->name('issue.warning');
Route::get('/ban-details/{id}', [BanController::class, 'getBanDetails'])->name('ban.details');
Route::post('/ban-note/{banId}', [BanController::class, 'addBanNote'])->name('ban.note.add');

Route::get('/appeals', [BanController::class, 'appealsIndex'])->name('appeals.index');
Route::post('/submit-appeal/{banId}', [BanController::class, 'submitAppeal'])->name('appeal.submit');
Route::post('/review-appeal/{id}', [BanController::class, 'reviewAppeal'])->name('appeal.review');

Route::get('/ban-history', [BanController::class, 'history'])->name('history');
Route::delete('/ban-log/{id}', [BanController::class, 'deleteBanLog'])->name('ban.log.delete');
Route::get('/export-bans', [BanController::class, 'exportBans'])->name('bans.export');
Route::get('/api/stats', [BanController::class, 'apiStats'])->name('api.stats');
Route::post('/send-notification/{id}', [BanController::class, 'sendNotification'])->name('send.notification');