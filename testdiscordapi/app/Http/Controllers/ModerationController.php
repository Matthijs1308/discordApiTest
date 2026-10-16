<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModerationController extends Controller
{
    //ban command
    public function store(Request $request, string $guildId)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'action' => 'required|string|max:50',
            'reason' => 'nullable|string',
            'mod_id' => 'required|integer',
            'duration' => 'nullable|string|max:50',
            'username' => 'nullable|string|max:100',
        ]);

        $caseId = DB::transaction(function () use ($guildId, $data) {
            $max = DB::table('moderation_actions')->where('guild_id', $guildId)->lockForUpdate()->max('case_id');
            $caseId = ($max ?? 0) + 1;

            DB::table('moderation_actions')->insert($data + [
                'guild_id' => $guildId,
                'case_id' => $caseId,
                'time_of_moderation' => now(),
            ]);

            return $caseId;
        });

        return response()->json(['case_id' => $caseId], 201);
    }

    // case command
    public function show(string $guildId, int $caseId)
    {
        $case = DB::table('moderation_actions')->where('guild_id', $guildId)->where('case_id', $caseId)->first();

        return $case ? response()->json($case) : response()->json(['message' => 'Not found'], 404);
    }

    // modlogs command
    public function forUser(string $guildId, string $userId)
    {
        return response()->json(
            DB::table('moderation_actions')
                ->where('guild_id', $guildId)
                ->where('user_id', $userId)
                ->orderBy('id')
                ->get()
        );
    }
}
