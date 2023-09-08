<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
// use App\Models\Listing;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\ListingOfferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RealtorListingController;
use App\Http\Controllers\NotificationSeenController;
use Illuminate\Notifications\Events\NotificationSent;
use App\Http\Controllers\RealtorListingImageController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\RealtorListingAcceptOfferController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [IndexController::class, 'index']
    /* function () {
        $listings = Listing::all(); ?>

        <style>
            .styled-table {
                border-collapse: collapse;
                margin: 25px 0;
                font-size: 0.9em;
                font-family: sans-serif;
                min-width: 400px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            }

            .styled-table thead tr {
                background-color: #009879;
                color: #ffffff;
                text-align: left;
            }

            .styled-table th,
            .styled-table td {
                padding: 12px 15px;
            }

            .styled-table tbody tr {
                border-bottom: 1px solid #dddddd;
            }

            .styled-table tbody tr:nth-of-type(even) {
                background-color: #f3f3f3;
            }

            .styled-table tbody tr:last-of-type {
                border-bottom: 2px solid #009879;
            }

            .styled-table tbody tr.active-row {
                font-weight: bold;
                color: #009879;
            }
        </style>

        <?php foreach ($listings as $listing) : ?>
                <h2>Listing <?= $listing['id']; ?></h2>
                
                <hr>

                <table class="styled-table">
                    <thead>
                        <tr>
                            <th scope="col">Beds</th>
                            <th scope="col">Baths</th>
                            <th scope="col">Area</th>
                            <th scope="col">City</th>
                            <th scope="col">Code</th>
                            <th scope="col">Street</th>
                            <th scope="col">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $listing['beds'] ; ?></td>
                            <td><?= $listing['baths'] ; ?></td>
                            <td><?= $listing['area'] ; ?></td>
                            <td><?= $listing['city'] ; ?></td>
                            <td><?= $listing['code'] ; ?></td>
                            <td><?= $listing['street'] ; ?></td>
                            <td><?= $listing['price'] ; ?></td>
                        </tr>
                    </tbody>
                </table>
                
        <?php endforeach; 
    } */
);
Route::get('/hello', [IndexController::class, 'show'])
  ->middleware('auth');

Route::resource('listing', ListingController::class)
  ->only(['index', 'show']);

Route::resource('listing.offer', ListingOfferController::class)
->middleware('auth')
->only(['store']);

Route::resource('notification', NotificationController::class)
->middleware('auth')
->only(['index']);

Route::put(
  'notification/{notification}/seen',
  NotificationSeenController::class
)->middleware('auth')->name('notification.seen');

Route::get('login', [AuthController::class, 'create'])
  ->name('login');

Route::post('login', [AuthController::class, 'store'])
  ->name('login.store');

Route::delete('logout', [AuthController::class, 'destroy'])
  ->name('logout');

Route::get('/email/verify', function(){
  return inertia('Auth/VerifyEmail');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
  $request->fulfill();

  return redirect()->route('listing.index')
    ->with('success', 'Email was verified!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
  $request->user()->sendEmailVerificationNotification();

  return back()->with('success', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::resource('user-account', UserAccountController::class)
->only(['create', 'store']);

Route::prefix('realtor')
  ->name('realtor.')
  ->middleware('auth', 'verified')
  ->group(function () {
    Route::name('listing.restore')
      ->put(
        'listing/{listing}/restore',
        [RealtorListingController::class, 'restore']
      )->withTrashed();
    Route::resource('listing', RealtorListingController::class)
      // ->only(['index', 'destroy', 'edit', 'update', 'create', 'store'])
      ->withTrashed();

    Route::name('offer.accept')
    ->put(
      'offer/{offer}/accept',
      RealtorListingAcceptOfferController::class
    );

    Route::resource('listing.image', RealtorListingImageController::class)
    ->only(['create', 'store', 'destroy']);
});

?>
