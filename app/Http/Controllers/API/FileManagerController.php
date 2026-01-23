<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\UserFile;
use App\User;
use Carbon\Carbon;
use DB;
class FileManagerController extends Controller
{
    public function index()
    {   
        $user_files = UserFile::all();

        return response()->json(['user_files' => $user_files], 200);
    }

    public function file_upload(Request $request)
    {   
        // validate token
        $tokenResponse = $this->validateToken($request->token);

        // $tokenResponse is already a JsonResponse
        // If validation fails, just return it
        if ($tokenResponse->getStatusCode() != 200) {
            return $tokenResponse;
        }

        try {
            $file = $request->file('file');
            $file_extension = $file->getClientOriginalExtension();

            $validator = Validator::make(
                [       
                    'applicant_id' => $request->applicant_id,
                    'file_ext' => strtolower($file_extension),
                    'file' => $file,
                ],
                [   
                    'applicant_id' => 'required|integer',
                    'file_ext' => 'required|in:jpeg,jpg,png,docs,docx,pdf',
                    'file' => 'required|max: 20800'
                ], 
                [   
                    'applicant_id.required' => 'Applicant ID is required',
                    'applicant_id.integer' => 'Applicant ID must be an integer',
                    'file.required' => 'File is required',
                    'file.max' => 'File size maximum is 20MB,',
                    'file_ext.required' => 'File extension is required',
                    'file_ext.in' => 'File type must be jpeg, jpg, png, docs, docx or pdf.',
                ]
            );  
            
            if($validator->fails())
            {
                return response()->json(['error' => $validator->errors()], 200);
            }  
            if($request->file('file'))
            {   
                    $file_extension = $request->file('file')->getClientOriginalExtension();
            }

            $file_date = Carbon::now()->format('Y-m-d');
            $uploadedFile = $request->file('file');
            $file_name = time().$uploadedFile->getClientOriginalName();
            $file_path = '/wysiwyg/user_files/' . $file_date;


            $uploadedFile->move(public_path() . $file_path, $file_name);
            
            $user_file = new UserFile();
            $user_file->user_id = $request->user_id;
            $user_file->branch_id = $request->branch_id;
            $user_file->file_name = $file_name;
            $user_file->file_path = $file_path;
            $user_file->file_type = $file_extension;
            $user_file->title = $request->document_type;
            $user_file->save();

            return response()->json(['success' => 'File has been uploaded', 'user_file' => $user_file], 200);

        } catch (\Exception $e) {
                
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    public function delete(Request $request)
    {
        $file_id = $request->get('file_id');
        $file = UserFile::find($file_id);
        
        //if record is empty then display error page
        if(empty($file->id))
        {
            return abort(404, 'Not Found');
        }

        $file->delete();

        $file_path = $file->file_path;

        $path = public_path() . $file_path . "/" . $file->file_name;
        unlink($path);

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function validateToken($token) 
	{
		// Find token in Passport table
        $passportToken = DB::table('oauth_access_tokens')
            ->where('id', $token)          // token string
            ->where('name', 'qrToken')     // token type
            ->where('revoked', 0)
            ->first();

        if (!$passportToken) {
            return response()->json([
                'error' => 'Invalid QR token'
            ], 404);
        }

        // Check expiration
        if ($passportToken->expires_at && Carbon::now()->gt($passportToken->expires_at)) {
            return response()->json([
                'error' => 'QR token expired'
            ], 401);
        }

        // Get user info
        $user = User::find($passportToken->user_id);

        if (!$user) {
            return response()->json([
                'error' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success'   => true,
            'user_id'   => $user->id,
            'branch_id' => $user->branch_id ?? null,
            'token_id'  => $passportToken->id,
            'expires_at' => $passportToken->expires_at,
        ], 200);
	}

}
