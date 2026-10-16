<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\GuildConfigController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\MessageHistoryController;
use App\Http\Controllers\TempModerationController;

Route::middleware('bot')->group(function () {
    Route::get('/test', [ApiController::class, 'test']);
    Route::get('/guilds/{guildId}/config', [GuildConfigController::class, 'show']);
    Route::post('/guilds/{guildId}/config', [GuildConfigController::class, 'store']);
    Route::delete('/guilds/{guildId}/config', [GuildConfigController::class, 'destroy']);
    Route::patch('/guilds/{guildId}/channels', [GuildConfigController::class, 'updateChannel']);

    Route::post('/guilds/{guildId}/moderation-actions', [ModerationController::class, 'store']);
    Route::get('/guilds/{guildId}/moderation-actions/{caseId}', [ModerationController::class, 'show']);
    Route::get('/guilds/{guildId}/users/{userId}/moderation-actions', [ModerationController::class, 'forUser']);

    Route::post('/guilds/{guildId}/messages', [MessageHistoryController::class, 'store']);
    Route::get('/guilds/{guildId}/users/{userId}/messages', [MessageHistoryController::class, 'index']);

    Route::post('/guilds/{guildId}/temp-moderations', [TempModerationController::class, 'store']);
    Route::delete('/guilds/{guildId}/users/{userId}/temp-moderations', [TempModerationController::class, 'destroy']);
});
