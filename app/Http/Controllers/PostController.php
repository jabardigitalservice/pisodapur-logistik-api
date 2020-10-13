<?php

namespace App\Http\Controllers;

use App\Post;
use App\Validation;
use Illuminate\Http\Request;

class PostController extends Controller {
     
    public function __construct() {
        $this->middleware('jwt-auth');
    } 

    public function index() {
        $data['status'] = true;
        $data['posts'] = Post::all();
        return response()->json(compact( 'data'));
    }

    
    public function add(Request $request){
        $param = [
            'name' => 'required',
            'description' => 'required',
            'category_id' => 'required',
        ];
        if (Validation::validate($request, $param)) {
            $user = Post::create([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
            ]);
            return response()->json(array('status' => true, 'msg' => 'Successfully Created'), 200);
        }
    }
    
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
