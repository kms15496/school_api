<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parents;
use App\Repositories\ParentInfoRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(
        private readonly ParentInfoRepository $parentInfoRepository
    ) {
    }

    public function parentLogin(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required'],
            'password' => ['required'],
        ]);

        $parent = Parents::where('phone', $credentials['phone'])->first();

        if($parent && !$parent->active) {
            return $this->apiResponse(false, 'Your account has been deactivated. Please contact the school administration for more information.', null, 200);
        }

        if (!$parent || !Hash::check($credentials['password'], $parent->password)) {
            return $this->apiResponse(false, 'Invalid credentials', null, 401);
        }

        $token = $parent->createToken('Parent API Token')->plainTextToken;

        $parentInfo = $this->parentInfoRepository->buildParentInfo($parent);

        return $this->apiResponse(true, 'Login success', array_merge([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], $parentInfo));
    }

    public function deactivateAccount(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        $user->update(['active' => 0]);

        return $this->apiResponse(true, 'Account deactivated successfully');
    }
}
