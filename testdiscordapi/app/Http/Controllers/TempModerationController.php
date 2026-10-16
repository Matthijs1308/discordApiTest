<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TempModerationController extends Controller
{
    public function store(Request $request, string $guildId)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'unban_time' => 'required|date',
        ]);

        DB::table('temp_moderations')->insert([
            'guild_id' => $guildId,
            'user_id' => $data['user_id'],
            'unban_time' => Carbon::parse($data['unban_time'])->utc()->format('Y-m-d H:i:s'),
        ]);

        return response()->json(['ok' => true], 201);
    }

    public function destroy(string $guildId, string $userId)
    {
        DB::table('temp_moderations')
            ->where('guild_id', $guildId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
