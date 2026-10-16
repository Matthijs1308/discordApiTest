<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GuildConfigController extends Controller
{
    public function show(string $guildId)
    {
        $roles = DB::table('roles')->where('guild_id', $guildId)->first();
        $channels = DB::table('channels')->where('guild_id', $guildId)->first();

        if (!$roles && !$channels) {
            return response()->json(['message' => 'Guild not set up'], 404);
        }

        return response()->json(['roles' => $roles, 'channels' => $channels]);
    }

    public function store(Request $request, string $guildId)
    {
        // only accept columns that really exist, so the bot can't write arbitrary fields
        $roles = Arr::only($request->input('roles', []), Schema::getColumnListing('roles'));
        $channels = Arr::only($request->input('channels', []), Schema::getColumnListing('channels'));

        DB::transaction(function () use ($guildId, $roles, $channels) {
            // server-controlled values go on the left so they can't be overridden
            DB::table('roles')->insert(['guild_id' => $guildId, 'been_setup' => 1] + $roles);
            DB::table('channels')->insert(['guild_id' => $guildId] + $channels);
        });

        return response()->json(['ok' => true], 201);
    }

    public function destroy(string $guildId)
    {
        DB::table('roles')->where('guild_id', $guildId)->delete();
        DB::table('channels')->where('guild_id', $guildId)->delete();

        return response()->json(['ok' => true]);
    }

    public function updateChannel(Request $request, string $guildId)
    {
        $data = $request->validate([
            'column' => 'required|string',
            'channel_id' => 'required|integer',
        ]);

        $allowed = ['modlogs_channel_id', 'deletion_logs_channel_id', 'edit_logs_channel_id',
                    'automod_logs_channel_id', 'staff_banproof_channel_id'];
        abort_unless(in_array($data['column'], $allowed, true), 422, 'Invalid column');

        $row = DB::table('channels')->where('guild_id', $guildId);

        if (!$row->exists()) {
            return response()->json(['message' => 'Guild not set up'], 404);
        }

        $row->update([$data['column'] => $data['channel_id']]);

        return response()->json(['ok' => true]);
    }
}
