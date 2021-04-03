<?php

namespace App\Http\Controllers;

use App\Events\ListingUpdated;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Jetstream\Jetstream;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $listId)
    {
    	$data = $request->validate([
    		'type' => [ 'required' ],
			'text' => [ 'required' ],
		]);
		$list = Cache::get('list.' . $listId) ?? [];
		$list[$data['type']] = $list[$data['type']] ?? [];
		$list[$data['type']][] = [ 'id' => uniqid(), 'text' => $data['text'], 'author' => auth()->user()->name ];

		Cache::put('list.' . $listId, $list, 6000);

		broadcast(new ListingUpdated($listId, $list));

		return $list;
    }

	/**
	 * Show the general profile settings screen.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Inertia\Response
	 */
	public function show(Request $request, $listId)
	{
		if (!auth()->check()) {
			// Make anonymous user

		}
		$list = Cache::get('list.' . $listId) ?? [];

		return Jetstream::inertia()->render($request, 'Lists/Show', [
			'list' => $list,
			'listId' => $listId,
		]);
	}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Listing  $listing
     * @return \Illuminate\Http\Response
     */
    public function edit(Listing $listing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Listing  $listing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Listing $listing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Listing  $listing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Listing $listing)
    {
        //
    }
}
