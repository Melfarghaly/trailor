<?php

namespace App\Http\Controllers;
use App\Measurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MeasurementController extends Controller
{
    public function  index(){
        $business_id=session('user.business_id');
        $measurements = Measurement::where('business_id',$business_id)->get();
        return view('measurement.index',compact('measurements'));
    }
    public function store(Request $request)
    {
      
        $business_id=session('user.business_id');
      
    
        try {
            $input = $request->except(['options','key']);
            $input['business_id']=$business_id;
            $input['key']= str_slug($request->input('key'), '_');
            $input['label']= $request->input('key');
            $options = $request->input('options');
            $files=$request->file('options');
            $options_arr = [];
            $files = $request->file('options');
            foreach ($options as $key=>$val) {
                $path=null;
                $label=$val['name'];
                    if(!empty($files[$key])){
                        $path = $this->uploadImage($files[$key]['image']);
                    }
                $options_arr[] = ['label' => $label, 'image' => $path];
            }
            //dd($options_arr);
            $input['options'] = json_encode($options_arr);
           // dd($input);
            $measurement = Measurement::create($input);
    
            return redirect()->back()->with('success', __('messages.added_success'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('succe', __('messages.oops_something_went_wrong'));
        }
    
 }
    public function edit($id){
        $measurement = Measurement::find($id);
        return view('measurement.edit',compact('measurement'));
    }
    public function update(Request $request, $id)
    {
        try {
            $measurement = Measurement::find($id);
            $measurement->key = $request->key;
            $measurement->type = $request->type;
            $measurement->save();
            return redirect()->back()->with('success', 'Measurement has been updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function show($id)
    {
        $measurement = Measurement::find($id);
        return view('measurements.show', ['measurement' => $measurement]);
    }
    public function uploadImage($file)
    {
        $path = $file->store('options');
        return $path;
    }

    public function destroy($id){
        $measurement = Measurement::find($id);
        $measurement->delete();
        return redirect()->back()->with('status',['success'=>1,'msg'=> 'Deleted'
        ]);
    }
}
