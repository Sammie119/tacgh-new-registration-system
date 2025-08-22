<?php

namespace App\Helpers;

use App\Models\Admin\Dropdown;
use App\Models\Admin\EventFees;
use Illuminate\Support\Facades\File;

class Utils
{
    static public function getLookups($id)
    {
        return Dropdown::select('id', 'full_name as name')->where([
            'lookup_code_id' => $id,
            'active_flag' => 1
        ])->get();
    }

    public static function generateToken($size = 10){
        $divisor = 2;
        $chars = array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
//        $chars = array(0,1,2,3,4,5,6,7,8,9);
        $serial = '';
        $max = count($chars)-1;

        if((($size % 2) == 1) || ($size <= 4)) {
            $divisor = 1;
        }
        for($i=0;$i<$size;$i++){
            $serial .= (!($i % ($size/$divisor)) && $i ? '-' : '').$chars[rand(0, $max)];
        }

        return $serial;
    }

    public static function check($key, $item): bool
    {
        if ($key == $item)
            return true;
        return false;
    }

    public static function eventRegistrationFee($id)
    {
        $fee = EventFees::find($id)->fee_amount;
        if($fee)
            return $fee;
        return 0;
    }

    public static function fileUpload($request, $folder = 'uploads', $file_url = null)
    {
        if($file_url !== null){
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
