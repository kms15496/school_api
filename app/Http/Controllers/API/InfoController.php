<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\ParentInfoRepository;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function __construct(
        private readonly ParentInfoRepository $parentInfoRepository
    ) {
    }

    public function getInfo(Request $request)
    {
        $parent = $request->user();

        return $this->apiResponse(true, 'Info success', $this->parentInfoRepository->buildParentInfo($parent));
    }
}
