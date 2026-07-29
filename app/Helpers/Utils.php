<?php

namespace App\Helpers;

use App\Models\Admin\Dropdown;
use App\Models\Admin\EventFees;
use Illuminate\Support\Facades\File;

class Utils
{
    /**
     * Matches either the local Ghanaian mobile format (0XXXXXXXXX, 10
     * digits) or the already-normalized international format
     * (+233XXXXXXXXX) — the latter so that already-stored numbers still
     * validate when a form re-submits them unchanged (e.g. during
     * registration confirmation).
     */
    public const GHANA_PHONE_REGEX = '/^(0[0-9]{9}|\+233[0-9]{9})$/';

    /**
     * Convert a validated Ghanaian local-format number (0XXXXXXXXX) to the
     * international format (+233XXXXXXXXX) that SMS/WhatsApp sending and
     * payment gateways expect. Already-international numbers and empty
     * values pass through unchanged.
     */
    public static function normalizeGhanaPhone(?string $number): ?string
    {
        if ($number === null || $number === '') {
            return $number;
        }

        $number = trim($number);

        if (preg_match('/^0[0-9]{9}$/', $number)) {
            return '+233'.substr($number, 1);
        }

        return $number;
    }

    public static function getLookups($id)
    {
        return Dropdown::select('id', 'full_name as name')->where([
            'lookup_code_id' => $id,
            'active_flag' => 1,
        ])->get();
    }

    public static function generateToken($size = 10)
    {
        $divisor = 2;
        $chars = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        //        $chars = array(0,1,2,3,4,5,6,7,8,9);
        $serial = '';
        $max = count($chars) - 1;

        if ((($size % 2) == 1) || ($size <= 4)) {
            $divisor = 1;
        }
        for ($i = 0; $i < $size; $i++) {
            $serial .= (! ($i % ($size / $divisor)) && $i ? '-' : '').$chars[rand(0, $max)];
        }

        return $serial;
    }

    /**
     * generateToken() has no collision handling and neither the
     * registrants_stage nor batch_logs token columns had a uniqueness
     * check at the DB level - regenerate until a free token is found so
     * two registrants can never silently end up with the same login token.
     * withTrashed() because the DB-level unique index (which is the real
     * backstop) doesn't exempt soft-deleted rows either.
     */
    public static function generateUniqueToken(string $modelClass, int $size = 10, int $maxAttempts = 10): string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $token = self::generateToken($size);
            if (! $modelClass::withTrashed()->where('token', $token)->exists()) {
                return $token;
            }
        }

        throw new \RuntimeException("Unable to generate a unique token for {$modelClass} after {$maxAttempts} attempts.");
    }

    public static function check($key, $item): bool
    {
        if ($key == $item) {
            return true;
        }

        return false;
    }

    public static function eventRegistrationFee($id)
    {
        $fee = EventFees::find($id)?->fee_amount;
        if ($fee) {
            return $fee;
        }

        return 0;
    }

    public static function fileUpload($request, $folder = 'uploads', $file_url = null)
    {
        if ($file_url !== null) {
            $file = 'app/'.$file_url;
            if (File::exists(storage_path($file))) {
                File::delete(storage_path($file));
            }
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store($folder);
        }

        return $path;
    }
}
