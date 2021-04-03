<?php

use App\Broadcasting\ListingChannel;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

function generateName() {
	$nouns = ['Platypus', 'Cucumber', 'Orangutan', 'Flower', 'River', 'Daisy'];
	shuffle($nouns);

	return $nouns[0];
}

Route::post('/broadcasting/auth', function(Illuminate\Http\Request $req) {
	if (preg_match('/^presence-list\\./u', $req->channel_name)) {
		if (!auth()->check()) {
			$name = Session::get('anonymous_username', 'Anonymous ' . generateName());
			$user = auth()->user() ?: User::factory()->make([
				'id' => (int) str_replace('.', '', microtime(true)),
				'name' => $name,
			]);
			auth()->login($user);
			Session::put('anonymous_username', $name);
		}
		return Broadcast::auth($req);
	}

	return abort(403);
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('list.{listId}', ListingChannel::class);
