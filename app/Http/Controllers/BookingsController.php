<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Interfaces\CabinCategoryInterface;
use App\Interfaces\CabinInterface;
use App\Interfaces\EventRepositoryInterface;
use App\Models\Cabin;
use App\Models\CabinCategory;
use App\Repositories\CabinCategoryRepository;
use App\Repositories\CabinRepository;
use App\Repositories\EventRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Rules\ValidDateFormat;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Support\Arr;

class BookingsController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;

    public function __construct()
    {
    }
    public function index()
    {
        try {
            return $this->withPermission([Permissions::ViewCabinCategories], function () {
                $data = CabinCategory::all()->toArray();
                return Inertia::render('Bookings/Index', [
                    'data' => array('data' => $data),
                    'tab' => 'CATEGORIES',
                ]);
            });
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }

    public function create()
    {
       
    }


    public function store(Request $request) {
        
    }

    public function edit(Request $request)
    {
        
    }

    public function update(Request $request)
    {

    }

    public function show(Cabin $cabin)
    {
       
    }

    public function destroy(Cabin $cabin)
    {
       
    }

    public function addTag(Request $request)
    {
       
    }

    public function updateStatus(Request $request)
    {
        
    }
}
