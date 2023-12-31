<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    //Show all listings
    public function index(){
        return view('listings.index',[
            'listings' => Listing::latest()->filter(
                request(['tag', 'search']))->paginate(6)
        ]);
    }

    //Show single listing
    public function show(Listing $listing){
        return view('listings.show',[
            'listing' => $listing
        ]);
    }

    //Show create form
    public function create(){
        return view('listings.create');
    }

    //Store Listing Data
    public function store(Request $req){
        $formFields = $req->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        
        if($req->hasFile('logo')){
            $formFields['logo'] = $req->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();
        Listing::create($formFields);


        //This is a Flush Message and a Redirecting to home
        return redirect(('/'))->with('message', 
            'Listing created successfully');
    }

    //Show Edit Form 
    public function edit(Listing $listing){

        //Make sure logged in user is owner
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action');
        }
        return view('listings.edit',[
            'listing' => $listing
        ]);
    }

    //Update Resource 
    public function update(Request $req, Listing $listing){
        
        
        //Make sure logged in user is owner
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action');
        }
        
        $formFields = $req->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if($req->hasFile('logo')){
            $formFields['logo'] = $req->file('logo')->store('logos', 'public');
        }
        
        $listing->update($formFields);


        //This is a Flush Message and a Redirecting to home
        return back()->with('message', 
            'Listing updated successfully');
    }

    //Delete Listing 
    public function destroy(Listing $listing){

        //Make sure logged in user is owner
        if($listing->user_id != auth()->id()){
            abort(403, 'Unauthorized Action');
        }

        $listing->delete();
        return redirect('/')->with('message', 'Listing deleted successfully');
    }
    
    //Manage Listings
    public function manage(){
        // dd(auth()->user()->listings()->get());
        return view('listings.manage', [
            'listings' => auth()->user()->listings()->get()
        ]);
    }
}
