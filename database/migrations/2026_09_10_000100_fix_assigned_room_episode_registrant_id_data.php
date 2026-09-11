<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RoomAllocationPipe used to write assigned_room_episodes.registrant_id
     * as a RegistrantStage id instead of the intended Registrant id (the
     * convention every other reader/writer of this column - the room detail
     * page's roommate list, and AssignRoomEpisodeService's "already
     * assigned" checks - actually uses). This repairs already-written rows
     * to match: any row whose registrant_id isn't a valid registrants.id
     * but does match a registrants.stage_id gets corrected to that
     * registrant's real id.
     */
    public function up(): void
    {
        // Row-by-row rather than a joined UPDATE, so this runs the same way
        // on both MySQL (production) and SQLite (the test suite's
        // RefreshDatabase, which doesn't support a joined UPDATE ... SET
        // referencing another table's column).
        DB::table('assigned_room_episodes')
            ->orderBy('id')
            ->get(['id', 'registrant_id'])
            ->each(function ($episode) {
                $alreadyValid = DB::table('registrants')->where('id', $episode->registrant_id)->exists();
                if ($alreadyValid) {
                    return;
                }

                $registrant = DB::table('registrants')->where('stage_id', $episode->registrant_id)->first();
                if (! $registrant) {
                    return;
                }

                DB::table('assigned_room_episodes')->where('id', $episode->id)->update(['registrant_id' => $registrant->id]);
            });
    }

    /**
     * Not reversible - the original (wrong) values aren't meaningful to
     * restore, and rows this migration didn't touch (already correct, or
     * with no matching registrant either way) are left as they are.
     */
    public function down(): void
    {
        //
    }
};
