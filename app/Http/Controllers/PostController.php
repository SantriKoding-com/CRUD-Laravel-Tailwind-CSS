<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{    
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        $posts = Post::latest()->when(request()->search, function($posts) {
            $posts = $posts->where('title', 'like', '%'. request()->search . '%');
        })->paginate(5);

        return view('post.index', compact('posts'));
    }
    
    /**
     * create
     *
     * @return void
     */
    public function create()
    {
        return view('post.create');
    }
    
    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'image'     => 'required|image|mimes:png,jpg,jpeg',
            'title'     => 'required',
            'content'   => 'required'
        ]);

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content
        ]);

        if($post){
            //redirect dengan pesan sukses
            return redirect()->route('post.index')->with(['success' => 'Data Berhasil Disimpan!']);
        }else{
            //redirect dengan pesan error
            return redirect()->route('post.index')->with(['error' => 'Data Gagal Disimpan!']);
        }

    }
    
    /**
     * edit
     *
     * @param  mixed $post
     * @return void
     */
    public function edit(Post $post)
    {
        return view('post.edit', compact('post'));
    }
    
    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $post
     * @return void
     */
    public function update(Request $request, Post $post)
    {
        $this->validate($request, [
            'title'     => 'required',
            'content'   => 'required'
        ]);
    
        //get data post by ID
        $post = Post::findOrFail($post->id);
    
        if($request->file('image') == "") {
    
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content
            ]);
    
        } else {
    
            //hapus old image
            Storage::disk('local')->delete('public/posts/'.$post->image);
    
            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());
    
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content
            ]);
    
        }
    
        if($post){
            //redirect dengan pesan sukses
            return redirect()->route('post.index')->with(['success' => 'Data Berhasil Diupdate!']);
        }else{
            //redirect dengan pesan error
            return redirect()->route('post.index')->with(['error' => 'Data Gagal Diupdate!']);
        }
    }
    
    /**
     * destroy
     *
     * @param  mixed $id
     * @return void
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        Storage::disk('local')->delete('public/posts/'.$post->image);
        $post->delete();

        if($post){
            //redirect dengan pesan sukses
            return redirect()->route('post.index')->with(['success' => 'Data Berhasil Dihapus!']);
        }else{
            //redirect dengan pesan error
            return redirect()->route('post.index')->with(['error' => 'Data Gagal Dihapus!']);
        }
    }
}
