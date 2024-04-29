<?php

namespace App\Http\Controllers\Api;

use App\Models\Assessor;
use App\Models\BdcmsAssessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AssessorController extends BaseController
{

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sb_id' => 'required',
                'password' => 'required',
            ]);

            if ($validator->fails())
                return $this->errorWithData($validator->errors()->first(), $validator->errors());

            $assessor = Assessor::withoutGlobalScope('active')->where('sb_id', $request->sb_id)->first();

            if (is_null($assessor))
                return $this->error('Invalid User ID. Please contact Training Department');

            if (!$assessor->status)
                return $this->error('Assessor is not active. Please contact Training Department');

            if (!Hash::check($request->password, $assessor->password))
                return $this->error('Wrong password. Please try an other password');

            $data = [
                'assessor' => $assessor,
                'api_token' => $assessor->createToken('Api Token')->plainTextToken
            ];
            return $this->success($data);

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

    public function profile()
    {
        try {
            return $this->success(auth()->user());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

    public function bdcmProfile()
    {
        try {
            $result = BdcmsAssessor::select(DB::raw("INITCAP (EXEMPNAM) name, DECODE (GENDCODE,  'M', 'Male',  'F', 'Female')  gender, SUBSTR (NATIONAL, 4) NATIONALITY,  SUBSTR (STATUSCD, 4) STATUS"))
                ->where('EXEMPCDE', auth()->user()->sb_id)
                ->first();
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'password' => 'required|confirmed|min:6',
                'password_confirmation' => 'required|min:6',
            ]);

            if ($validator->fails())
                return $this->errorWithData($validator->errors()->first(), $validator->errors());

            if (!Hash::check($request->old_password, auth()->user()->password))
                return $this->error('Old password is Wrong. Please try again');

            $user = auth()->user()->update(['password' => $request->password,'first_login'=>0]);
            if ($user){
                auth()->user()->tokens()->delete();
            }
            return $this->success([], 'Password has been updated');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }

    public function logout()
    {
        try {
            $user = auth()->user();
            if ($user){
                auth()->user()->tokens()->delete();
            }
            return $this->success([], 'User has been logged out');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), true);
        }
    }
}
