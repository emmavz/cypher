<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\Front\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('get_article_list_and_view', [ApiController::class, 'get_article_list_and_view']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('get_tags', [ApiController::class, 'get_tags']);
    Route::post('get_article_homepage', [ApiController::class, 'get_article_homepage']);
    Route::post('get_user_profile', [ApiController::class, 'get_user_profile']);
    Route::post('get_user_profile_with_balance', [ApiController::class, 'get_user_profile_with_balance']);
    Route::post('get_auth_user_profile', [ApiController::class, 'get_auth_user_profile']);
    Route::post('get_recommendations', [ApiController::class, 'get_recommendations']);
    Route::post('search_articles', [ApiController::class, 'search_articles']);
    Route::post('search_authors', [ApiController::class, 'search_authors']);
    Route::post('do_follow_toggle', [ApiController::class, 'do_follow_toggle']);
    Route::post('get_notifications', [ApiController::class, 'get_notifications']);
    Route::post('read_notification', [ApiController::class, 'read_notification']);
    Route::post('get_draft_articles', [ApiController::class, 'get_draft_articles']);
    Route::post('get_user_draft_article', [ApiController::class, 'get_user_draft_article']);
    Route::post('get_user_profile_articles', [ApiController::class, 'get_user_profile_articles']);
    Route::post('store_article', [ApiController::class, 'store_article']);
    Route::post('delete_article', [ApiController::class, 'delete_article']);
    Route::post('pay_article', [ApiController::class, 'pay_article']);
    Route::post('get_full_article', [ApiController::class, 'get_full_article']);
    Route::post('upvote', [ApiController::class, 'upvote']);
    Route::post('cashout', [ApiController::class, 'cashout']);
    Route::post('get_other_user_investments', [ApiController::class, 'get_other_user_investments']);
    Route::post('get_user_investments', [ApiController::class, 'get_user_investments']);
    Route::get('{article_id}/{user_id}/{version}/facebookshare', [ApiController::class, 'facebookshare'])->name('api.facebookshare');
    Route::post('get_edit_user_profile', [ApiController::class, 'get_edit_user_profile']);
    Route::post('update_user_profile', [ApiController::class, 'update_user_profile']);
    Route::post('logout', [ApiController::class, 'logout']);
    Route::post('send_token', [ApiController::class, 'send_token']);
    Route::post('block_user', [ApiController::class, 'block_user']);
    Route::post('lucky_day', [ApiController::class, 'lucky_day']);
});

Route::post('signin', [ApiController::class, 'signin']);
Route::post('signup', [ApiController::class, 'signup']);
