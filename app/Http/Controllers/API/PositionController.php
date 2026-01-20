<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Position;
use App\Branch;
use App\Department;
use Validator;

class PositionController extends Controller
{
    public function index()
    {       
        $positions = Position::with('department')
                             ->orderBy('name', 'ASC')
                             ->get();
        $branches = Branch::orderBy('name', 'ASC')->get();
        $departments = Department::all();

        return response()->json(['positions' => $positions, 'branches' => $branches, 'departments' => $departments], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   

        $rules = [
            'name.required' => 'Please enter position',
            'name.unique' => 'Position already exists',
            'department.integer' => 'Department must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:positions,name',
            'department_id' => 'nullable|integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $position = new Position();
        $position->name = $request->get('name');
        $position->department_id = $request->get('department_id');
        $position->save();


        $position = Position::find($position->id);

        return response()->json(['success' => 'Record has successfully added', 'position' => $position], 200);
    }


    public function edit(Request $request)
    {   
        $position_id = $request->get('position_id');

        $position = Position::find($position_id);

        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json(['position' => $position], 200);

    }


    public function update(Request $request, $position_id)
    {   

        $rules = [
            'name.required' => 'Please enter position',
            'name.unique' => 'Position already exists',
            'department.integer' => 'Department must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:positions,name,'.$position_id,
            'department_id' => 'nullable|integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $position = Position::find($position_id);

        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }

        $position->name = $request->get('name');
        $position->department_id = $request->get('department_id');
        $position->save();

        $position = Position::find($position->id);

        return response()->json([
            'success' => 'Record has been updated',
            'position' => $position]
        );
    }


    public function delete(Request $request)
    {   
        $position_id = $request->get('position_id');
        $position = Position::find($position_id);
        
        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }

        $position->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}
