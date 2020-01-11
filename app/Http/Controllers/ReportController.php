<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Report;
use App\Http\Resources\Report as ReportResource;
use App\Http\Resources\ReportCollection;

class ReportController extends Controller
{
    public function index()
    {
        return new ReportCollection(Report::where('is_deleted', '=', '0')->orderBy('id', 'ASC')->paginate(5));
    }

    public function by_internship_id($id)
    {
        return new ReportCollection(Report::where('internship_id', '=', $id)->where('is_deleted', '=', '0')->orderBy('description', 'ASC')->paginate(5));
    }

    public function search($value,$id)
    {
        return new ReportCollection(Report::where('internship_id','=',$id)->where('is_deleted','=','0')->where('description', 'LIKE', '%'.$value.'%')->orderBy('id', 'ASC')->paginate(5));
        
        
    }

    public function show($id)
    {
        return new ReportResource(Report::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'internship_id' => 'required|max:255',
            'description' => 'required|max:255',

        ]);
        
        $report = Report::create($request->all());
        $report->save();

        return (new ReportResource($report))
                ->response()
                ->setStatusCode(201);
    
    }

    public function delete($id)
    {
        $report = Report::findOrFail($id);
        $report->is_deleted="1";
        
        $report->save();

        return response()->json(null, 204);
        // return new ReportCollection(Report::all());
    }

        
    public function update(Request $request, $id)
        {
            $this->validate($request, [
                'description' => 'required|max:255',
            ]);
    
            $report = Report::findOrFail($id);
            
            $report->description = request('description');
            $report->date = request('date');
            $report->hours = request('hours');
            $report->is_valid = request('is_valid');
            $report->comment = request('comment');
            $report->updated_by = request('updated_by');

            $report->save();
    
            return response()->json([
                'message' => 'Report updated successfully!'
            ], 200);
        }
}
