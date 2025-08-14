<?php

namespace App\Http\Controllers;

use App\Services\FormCreateService;
use App\Services\FormDeleteService;
use App\Services\FormEditService;
use App\Services\FormViewService;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function executeForm($service, $type, $id = null){
        switch ($service){
            case 'create':
                return FormCreateService::create($type);

            case 'edit':
                return FormEditService::edit($type, $id);

            case 'view':
                return FormViewService::view($type, $id);

            case 'delete':
                return FormDeleteService::delete($type, $id);

            default:
                return "No Form Selected";

        }
    }
}
