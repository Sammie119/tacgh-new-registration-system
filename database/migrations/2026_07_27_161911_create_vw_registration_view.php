<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * vw_registration already exists on the live database (created manually,
     * outside of any migration). This captures its exact current definition
     * (via `SHOW CREATE VIEW vw_registration`) so fresh environments get it
     * too, and uses CREATE OR REPLACE so re-running this against the existing
     * database is a no-op rather than an error.
     *
     * Note: the two joins to event_fees (for accommodation_type and
     * registration_type) are INNER JOINs, so a registrant whose
     * accommodation_type/registration_type doesn't match an event_fees row
     * (e.g. registration_type's default of 0) will be silently excluded from
     * this view. That's inherited from the original definition, not
     * introduced here.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('DROP VIEW IF EXISTS vw_registration');
            DB::statement("
                CREATE VIEW vw_registration AS
                SELECT
                    t.id,
                    t.registration_no,
                    t.stage_id,
                    t.name,
                    t.age,
                    t.event_id,
                    t.event_name,
                    t.nationality_id,
                    t.accommodation_type,
                    t.accommodation_fee,
                    t.registrants_type,
                    t.registration_fee,
                    t.total_fee,
                    t.room_no,
                    t.check_in,
                    t.check_out,
                    t.check_in_by,
                    t.rn
                FROM (
                    SELECT
                        r.id,
                        r.registration_no,
                        r.stage_id,
                        rs.first_name || ' ' || rs.surname AS name,
                        rs.nationality_id,
                        CAST((julianday('now') - julianday(rs.date_of_birth)) / 365.25 AS INTEGER) AS age,
                        e.id AS event_id,
                        e.name AS event_name,
                        ea.description AS accommodation_type,
                        r.accommodation_fee,
                        er.description AS registrants_type,
                        r.registration_fee,
                        r.total_fee,
                        r.room_no,
                        r.check_in,
                        r.check_out,
                        r.check_in_by,
                        ROW_NUMBER() OVER (PARTITION BY r.stage_id ORDER BY r.id) AS rn
                    FROM registrants r
                    JOIN registrants_stage rs ON rs.id = r.stage_id
                    JOIN events e ON e.id = r.event_id
                    JOIN event_fees ea ON ea.id = r.accommodation_type
                    JOIN event_fees er ON er.id = r.registration_type
                    WHERE r.deleted_at IS NULL
                ) t
                WHERE t.rn = 1
            ");

            return;
        }

        DB::statement("
            CREATE OR REPLACE VIEW vw_registration AS
            SELECT
                t.id,
                t.registration_no,
                t.stage_id,
                t.name,
                t.age,
                t.event_id,
                t.event_name,
                t.nationality_id,
                t.accommodation_type,
                t.accommodation_fee,
                t.registrants_type,
                t.registration_fee,
                t.total_fee,
                t.room_no,
                t.check_in,
                t.check_out,
                t.check_in_by,
                t.rn
            FROM (
                SELECT
                    r.id,
                    r.registration_no,
                    r.stage_id,
                    CONCAT(rs.first_name, ' ', rs.surname) AS name,
                    rs.nationality_id,
                    TIMESTAMPDIFF(YEAR, rs.date_of_birth, CURDATE()) AS age,
                    e.id AS event_id,
                    e.name AS event_name,
                    ea.description AS accommodation_type,
                    r.accommodation_fee,
                    er.description AS registrants_type,
                    r.registration_fee,
                    r.total_fee,
                    r.room_no,
                    r.check_in,
                    r.check_out,
                    r.check_in_by,
                    ROW_NUMBER() OVER (PARTITION BY r.stage_id ORDER BY r.id) AS rn
                FROM registrants r
                JOIN registrants_stage rs ON rs.id = r.stage_id
                JOIN events e ON e.id = r.event_id
                JOIN event_fees ea ON ea.id = r.accommodation_type
                JOIN event_fees er ON er.id = r.registration_type
                WHERE r.deleted_at IS NULL
            ) t
            WHERE t.rn = 1
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_registration');
    }
};
