<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Post;
use Image;
use Validator;
use DB;
use Intervention\Image\ImageServiceProvider;

class ServiceController extends Controller
{
    // crud operation for admin

    public function createuser(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'name'=>'required',
            'email'=>'required',
            'phone'=>'required',
            'user_type'=>'required|in:user,manager',
            'admin_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

        $input['token'] = $this->quickRandom();

        $input['password'] =  $this->quickRandom(5);

        $finddetails = User::find($input['admin_id']);

        if($finddetails->user_type == 'admin')
        {
            $detailofuser = User::create($input);

            return $this->sendResponse($detailofuser, 'User created successfully.',$request->path());
        } else {
            return $this->sendError($request->path(),"Only admin can create a user"); 
        }


    }

    public function userlist(Request $request,$id)
    {
    	$finddetails = User::find($id);

        if($finddetails->user_type == 'admin')
        {
           $alluserlist = User::get();
		   return $this->sendResponse($alluserlist, 'User list retrived successfully.',$request->path());
        } else {
            return $this->sendError($request->path(),"Only admin can view user list"); 
        }
    	
    }

    public function userdetails(Request $request)
    {
    	$input = $request->all();

        $validator = Validator::make($input, [
            'user_id'=>'required',
            'admin_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

    	$finddetails = User::find($input['admin_id']);

        if($finddetails->user_type == 'admin')
        {
           $alluserlist = User::find($input['user_id']);
		   return $this->sendResponse($alluserlist, 'User details retrived successfully.',$request->path());
        } else {
            return $this->sendError($request->path(),"Only admin can view user list"); 
        }
    	
    }

    public function deleteuser(Request $request)
    {
    	$input = $request->all();

        $validator = Validator::make($input, [
            'user_id'=>'required',
            'admin_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

    	$finddetails = User::find($input['admin_id']);

        if($finddetails->user_type == 'admin')
        {
           DB::table('users')->where(['id'=>$input['user_id']])->delete();

		   return $this->sendResponse(['status'=>'success'], 'User deleted successfully.',$request->path());
        } else {
            return $this->sendError($request->path(),"Only admin can view user list"); 
        }
    }

    public function updateuserdetails(Request $request)
    {
    	$input = $request->all();

        $validator = Validator::make($input, [
            'user_id'=>'required',
            'admin_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

    	$finddetails = User::find($input['admin_id']);

        if($finddetails->user_type == 'admin')
        {
        	unset($input['admin_id']);
        	$user_id = $input['user_id'];
        	unset($input['user_id']);
           DB::table('users')->where(['id'=>$user_id])->update($input);

		   return $this->sendResponse(['status'=>'success'], 'User details updated successfully.',$request->path());
        } else {
            return $this->sendError($request->path(),"Only admin can view user list"); 
        }
    }


    // post crud


    public function createpost(Request $request)
    {
    	 $input = $request->all();

        $validator = Validator::make($input, [
            'title'=>'required',
            'description'=>'required',
            'user_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

        if($request->image){
             
            $filename = substr( md5($input['user_id']. '-' . time() ), 0, 15) . '.' . $request->file('image')->getClientOriginalExtension();

            $path = public_path('posts-photos/' . $filename);

            Image::make($request->image)->save($path);
             
            $input['image'] = url('/').'/public/posts-photos/'.$filename;
           

        }

        $createpost = Post::create($input);

        return $this->sendResponse($createpost, 'Post created successfully.',$request->path());

    }

    public function editpost(Request $request)
    {
    	$input = $request->all();

        $validator = Validator::make($input, [
            'post_id'=>'required',
            'user_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

        $postdetails = Post::find($input['post_id']);

        if(empty($postdetails)){
        	return $this->sendError($request->path(),"Post not found!");  
        }

        if($this->whoismaster($input['user_id']) == 0 && $postdetails->user_id != $input['user_id']){
        	return $this->sendError($request->path(),"You don't have permission to update this post");  
        }

        if($request->image){
        	$filename = substr( md5($input['user_id']. '-' . time() ), 0, 15) . '.' . $request->file('image')->getClientOriginalExtension();

            $path = public_path('posts-photos/' . $filename);

            Image::make($request->image)->save($path);
             
            $postdetails->image = url('/').'/public/posts-photos/'.$filename;
        }

        if($request->title){
        	$postdetails->title = $request->title;
        }

        if($request->description){
        	$postdetails->description = $request->description;
        }

        $postdetails->save();


        return $this->sendResponse($postdetails, 'Post updated successfully.',$request->path());

    }

    public function postlist(Request $request)
    {
    	$input = $request->all();

        $validator = Validator::make($input, [
            'user_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

        if($this->whoismaster($input['user_id']) == 1){
        	$postlisting = Post::get();
        } else {
        	$postlisting = Post::where('user_id',$input['user_id'])->get();
        }

        return $this->sendResponse($postlisting, 'Post list retrived successfully.',$request->path());
    }

    public function postdelete(Request $request)
    {
    	$input = $request->all();

        $validator = Validator::make($input, [
            'post_id'=>'required',
            'user_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

        $postdetails = Post::find($input['post_id']);

        if(empty($postdetails)){
        	return $this->sendError($request->path(),"Post not found!");  
        }

        if($this->whoismaster($input['user_id']) == 0 && $postdetails->user_id != $input['user_id']){
        	return $this->sendError($request->path(),"You don't have permission to update this post");  
        }

        DB::table('posts')->where(['id'=>$input['post_id']])->delete();

        return $this->sendResponse(['status'=>'success'], 'Post deleted successfully.',$request->path());

    }

    public function postdetails(Request $request)
    {
    	$input = $request->all();

        $validator = Validator::make($input, [
            'post_id'=>'required',
            'user_id'=>'required',
        ]);


        if($validator->fails()){
            return $this->sendError($request->path(),$validator->errors()->first());       
        }

        $postdetails = Post::find($input['post_id']);

        if(empty($postdetails)){
        	return $this->sendError($request->path(),"Post not found!");  
        }

        if($this->whoismaster($input['user_id']) == 0 && $postdetails->user_id != $input['user_id']){
        	return $this->sendError($request->path(),"You don't have permission to update this post");  
        }

        return $this->sendResponse($postdetails, 'Post details retrived successfully.',$request->path());

    }

    public function whoismaster($user_id)
    {
    	$userdetails = User::find($user_id);

    	if($userdetails->user_type == 'user'){
    		return 0;
    	} else {
    		return 1;
    	}
    }



}
