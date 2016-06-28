<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Post;
use Auth;
use App\Post_Comment;
use Laralum;

class PostsController extends Controller
{
    public function __construct()
    {
        # Check permissions
        Laralum::permissionToAccess('admin.posts.access');
    }

    public function index($id)
    {
        # Check permissions
        Laralum::permissionToAccess('admin.posts.view');

        # Check blog permissions
        if(!Auth::user()->has_blog(Post::findOrFail($id)->blog->id) and !Auth::user()->owns_blog(Post::findOrFail($id)->blog->id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }


        $post = Post::findOrFail($id);

        $post->addView();

        $data_index = 'comments';
        require('Data/Create/Get.php');

        if($post->logged_in_comments){
            $fields = array_diff($fields, array("name", "email"));
        }

        $comments = $post->comments()->orderBy('created_at', 'desc')->get();

        return view('admin/blogs/posts/index', [
            'comments'  =>  $comments,
            'post'      =>  $post,
            'fields'    =>  $fields,
            'confirmed' =>  $confirmed,
            'encrypted' =>  $encrypted,
            'hashed'    =>  $hashed,
            'masked'    =>  $masked,
            'table'     =>  $table,
            'code'      =>  $code,
            'wysiwyg'   =>  $wysiwyg,
        ]);
    }

    public function graphics($id){
        # Check permissions
        Laralum::permissionToAccess('admin.posts.graphics');

        # Check blog permissions
        if(!Auth::user()->has_blog(Post::findOrFail($id)->blog->id) and !Auth::user()->owns_blog(Post::findOrFail($id)->blog->id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }

        $post = Post::findOrFail($id);

        return view('admin/blogs/posts/graphics', ['post' => $post]);
    }

    public function create($id)
    {
        # Check permissions
        Laralum::permissionToAccess('admin.posts.create');

        # Check blog permissions
        if(!Auth::user()->has_blog($id) and !Auth::user()->owns_blog($id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }


        $data_index = 'posts';
        require('Data/Create/Get.php');

        return view('admin/blogs/posts/create', [
            'fields'    =>  $fields,
            'confirmed' =>  $confirmed,
            'encrypted' =>  $encrypted,
            'hashed'    =>  $hashed,
            'masked'    =>  $masked,
            'table'     =>  $table,
            'code'      =>  $code,
            'wysiwyg'   =>  $wysiwyg,
        ]);
    }

    public function store($id, Request $request)
    {
        # Check permissions
        Laralum::permissionToAccess('admin.posts.create');

        # Check blog permissions
        if(!Auth::user()->has_blog($id) and !Auth::user()->owns_blog($id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }

        # create the user
        $row = new Post;

        # Save the data
        $data_index = 'posts';
        require('Data/Create/Save.php');

        $row->user_id = Auth::user()->id;
        $row->blog_id = $id;
        $row->save();

        # Return the admin to the posts page with a success message
        return redirect(url('/admin/blogs', [$id]))->with('success', "The post has been created");
    }

    public function edit($id)
    {
        # Check permissions
        Laralum::permissionToAccess('admin.posts.edit');

        # Check blog permissions
        if(!Auth::user()->has_blog(Post::findOrFail($id)->blog->id) and !Auth::user()->owns_blog(Post::findOrFail($id)->blog->id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }

        $row = Post::findOrFail($id);

        $data_index = 'posts';
        require('Data/Edit/Get.php');

        return view('admin/blogs/posts/edit',[
            'row'       =>  $row,
            'fields'    =>  $fields,
            'confirmed' =>  $confirmed,
            'empty'     =>  $empty,
            'encrypted' =>  $encrypted,
            'hashed'    =>  $hashed,
            'masked'    =>  $masked,
            'table'     =>  $table,
            'code'      =>  $code,
            'wysiwyg'   =>  $wysiwyg,
        ]);
    }

    public function update($id, Request $request)
    {
        # Check permissions
        Laralum::permissionToAccess('admin.posts.edit');

        # Check blog permissions
        if(!Auth::user()->has_blog(Post::findOrFail($id)->blog->id) and !Auth::user()->owns_blog(Post::findOrFail($id)->blog->id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }

        $row = Post::findOrFail($id);

        $data_index = 'posts';
        require('Data/Edit/Save.php');

        $row->edited_by = Auth::user()->id;
        $row->save();

        return redirect(url('/admin/blogs', [$row->blog->id]))->with('success', "The post has been updated");
    }

    public function comments($id){
        # Check permissions
        Laralum::permissionToAccess('admin.posts.comments');

        # Check blog permissions
        if(!Auth::user()->has_blog(Post::findOrFail($id)->blog->id) and !Auth::user()->owns_blog(Post::findOrFail($id)->blog->id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }

        $post = Post::findOrFail($id);
        $comments = $post->comments()->orderBy('created_at', 'desc')->get();

        return view('admin/blogs/posts/comments', [
            'comments' =>  $comments,
            'post'  =>  $post,
        ]);
    }

    public function createComment($id, Request $request)
    {
        # Check permissions
        Laralum::permissionToAccess('admin.posts.comments');

        # Check blog permissions
        if(!Auth::user()->has_blog(Post::findOrFail($id)->blog->id) and !Auth::user()->owns_blog(Post::findOrFail($id)->blog->id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }

        $post = Post::findOrFail($id);

        # Check if comments are enabled
        if($post->logged_in_comments or $post->anonymous_comments) {

            # create the user
            $row = new Post_Comment;

            # Save the data
            $data_index = 'comments';
            require('Data/Create/Save.php');

            $row->post_id = $post->id;

            if($post->logged_in_comments) {
                $row->user_id = Auth::user()->id;
            }
            $row->save();

            return redirect(url('admin/posts', [$post->id]));
        } else {
            return redirect('/admin')->with('warning', "Comments are not enabled on this post")->send();
        }
    }

    public function destroy($id)
    {
        # Check permissions
        Laralum::permissionToAccess('admin.posts.delete');

        # Check blog permissions
        if(!Auth::user()->has_blog(Post::findOrFail($id)->blog->id) and !Auth::user()->owns_blog(Post::findOrFail($id)->blog->id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }

        # Check if blog owner or post owner
        if(!Auth::user()->owns_blog(Post::findOrFail($id)->blog->id) and !Auth::user()->owns_post($id)){
            return redirect('/admin')->with('warning', "You are not allowed to perform this action")->send();
        }

        # Find The Post
        $post = Post::findOrFail($id);
        $blog_id = $post->blog->id;

        if($post->author->id == Auth::user()->id or $post->blog->user->id == Auth::user()->id) {
            $post->delete();
        }

        return redirect(url('/admin/blogs', [$blog_id]))->with('success', "The post has been deleted");
    }

}
