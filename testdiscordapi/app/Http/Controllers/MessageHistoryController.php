<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class MessageHistoryController extends Controller
{
    public function store(Request $request, string $guildId)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'message_id' => 'required|integer',
            'channel_id' => 'required|integer',
            'content' => 'nullable|string',
            'attachment_urls' => 'nullable|string',
            'reply_to_user_id' => 'nullable|integer',
            'reply_to_message_id' => 'nullable|integer',
        ]);

        DB::table('user_message_history')->insert($data + ['guild_id' => $guildId, 'timestamp' => now()]);

        $keep = DB::table('user_message_history')
            ->where('guild_id', $guildId)->where('user_id', $data['user_id'])
            ->orderByDesc('id')->limit(25)->pluck('id');

        DB::table('user_message_history')
            ->where('guild_id', $guildId)->where('user_id', $data['user_id'])
            ->whereNotIn('id', $keep)->delete();

        return response()->json(['ok' => true], 201);
    }

    public function index(string $guildId, string $userId)
    {
        return response()->json(
            DB::table('user_message_history')
                ->where('guild_id', $guildId)->where('user_id', $userId)
                ->orderBy('id')->get()
        );
    }
}
