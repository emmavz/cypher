<?php

namespace App\Http\Controllers\API\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\API\Front\BaseController as BaseController;
use App\Models\Article;
use App\Models\User;
use App\Models\Follow;
use App\Models\Tag;
use DB;
use App\Models\UserNotification;
use Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ApiController extends BaseController
{
    /**
     * Display a listing of the articles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_article_list_and_view(Request $request)
    {
        $articles = Article::with('user:id,name,pfp,total_invested', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id')->orderBy('date_posted', 'DESC')->limit($request->limit)->skip($request->offset)->where('is_published', 1)->get();

        return $this->sendResponse($articles);
    }

    /**
     * Display a listing of the tags.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_tags(Request $request)
    {
        $tags = Tag::get();

        return $this->sendResponse($tags);
    }

    /**
     * Display article homepage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_article_homepage(Request $request)
    {

        $article = Article::with('user:id,name,pfp,total_invested')->select('id', 'title', 'date_posted', 'image_url', 'price', 'liquidation_days', 'article_total_reads', 'article_total_shares', 'description', 'user_id')->where('id', $request->article_id)->where('is_published', 1)->get();

        $request->merge(['user_id' => $article[0]->user->id]);
        $request->merge(['auth_id' => $request->auth_id]);
        $user = $this->get_user_profile($request, 0);

        return $this->sendResponse([$article, $user]);
    }

    /**
     * Display user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_profile(Request $request, $shouldReturnResponse = 1)
    {
        $user = User::select('id', 'name', 'bio', 'pfp', 'balance', DB::raw('CASE WHEN EXISTS(SELECT * FROM follows WHERE users.id = followed_id AND follows.follower_id = ' . $request->auth_id . ') THEN 1 ELSE 0 END AS is_followed'))->withCount(['followers', 'followed'])->where('id', $request->user_id)->get();

        if ($shouldReturnResponse) {
            return $this->sendResponse($user);
        } else {
            return $user;
        }
    }

    /**
     * Display article recommendations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_recommendations(Request $request)
    {
        $articles = Article::with('user:id,name,pfp,total_invested', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id')->orderBy('date_posted', 'DESC')->limit($request->limit)->skip($request->offset)->where('is_published', 1)->get();

        return $this->sendResponse($articles);
    }

    /**
     * Display articles search results.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search_articles(Request $request)
    {
        $articles = Article::with('user:id,name,pfp,total_invested', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id')->orderBy('date_posted', 'DESC')->where('title', 'LIKE', '%' . $request->q . '%')->where('is_published', 1)->get();

        return $this->sendResponse($articles);
    }

    /**
     * Display authors search results.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search_authors(Request $request)
    {
        $users = User::select('id', 'name', 'bio', 'pfp', DB::raw('CASE WHEN EXISTS(SELECT * FROM follows WHERE users.id = followed_id AND follows.follower_id = ' . $request->follower_id . ') THEN 1 ELSE 0 END AS is_followed'))->where('name', 'LIKE', '%' . $request->q . '%')->where('id', '!=', $request->follower_id)->latest()->get();

        return $this->sendResponse($users);
    }

    /**
     * Follow other user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function do_follow_toggle(Request $request)
    {

        $follow = Follow::where('follower_id', $request->follower_id)->where('followed_id', $request->followed_id)->first();
        if ($follow) {
            $follow->delete();
        } else {
            $follow = new Follow;
            $follow->follower_id = $request->follower_id;
            $follow->followed_id = $request->followed_id;
            $follow->save();
        }

        return $this->sendResponse([]);
    }

    /**
     * Display notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_notifications(Request $request)
    {

        $userNotifications = UserNotification::with('notification:id,text')->select('id', 'read_at', 'notification_id')->where('user_id', $request->user_id)->latest()->get();

        return $this->sendResponse($userNotifications);
    }

    /**
     * read notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function read_notification(Request $request)
    {
        $now = Carbon::now();

        UserNotification::where('id', $request->notification_id)->where('user_id', $request->user_id)->update([
            'read_at' => $now
        ]);

        return $this->sendResponse([$now]);
    }

    /**
     * Display a listing of the draft articles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_draft_articles(Request $request)
    {
        $articles = Article::with(['user' => function ($query) use ($request) {
            $query->select('id', 'name', 'pfp', 'total_invested')->where('id', $request->user_id);
        }])->select('id', 'title', 'date_posted', 'image_url', 'user_id')->latest()->where('is_published', 0)->get();

        return $this->sendResponse($articles);
    }

    /**
     * Display a listing of the user profile articles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_profile_articles(Request $request)
    {
        $articles = Article::with('user:id,name,pfp,total_invested')->whereHas('user', function ($query) use ($request) {
            $query->where('id', $request->user_id);
        })->select('id', 'title', 'date_posted', 'image_url', 'user_id')->orderBy('date_posted', 'DESC')->where('is_published', 1)->get();

        return $this->sendResponse($articles);
    }

    /** Display user draft article for editing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_draft_article(Request $request)
    {
        $article = Article::with('tags')->where('user_id', $request->user_id)->where('id', $request->article_id)->where('is_published', 0)->get();

        return $this->sendResponse($article);
    }

    /**
     * create draft article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save_draft_article(Request $request)
    {
        $article = null;
        $oarticle = null;
        if ($request->article_id) {
            $article = Article::where('user_id', $request->user_id)->where('id', $request->article_id)->where('is_published', 0)->first();
            if (!$article) {
                $article = new Article();
            } else {
                $oarticle = clone $article;
            }
        } else {
            $article = new Article();
        }

        $request->merge(['tags' => array_filter(explode(',', $request->tags[0]))]);


        $fields = $this->validate_fields($request, [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title'   => ['required', 'string'],
            'description' => ['nullable', 'required_if:should_publish,1', 'string'],
            'image_url' => ['nullable', Rule::RequiredIf(function () use ($article) {
                return !$article;
            }), 'mimes:' . config('website.imgformats')],
            'p_description' => ['nullable', 'required_if:should_publish,1', 'string'],
            'price'   => ['nullable', 'required_if:should_publish,1', 'numeric'],
            'theta'   => ['nullable', 'required_if:should_publish,1', 'numeric'],
            'tags' => ['nullable', 'required_if:should_publish,1', 'array', 'exists:tags,id'],
            'should_publish' => ['required', 'boolean'],
        ]);

        $fields = Article::storeFiles($request, $fields, $oarticle);

        $article->title = $fields['title'];
        $article->description = $fields['p_description'];
        if ($fields['image_url']) $article->image_url = $fields['image_url'];
        $article->is_published = 0;
        $article->user_id = $fields['user_id'];
        $article->content = $fields['description'];
        $article->price = $fields['price'];

        if ($fields['should_publish']) {
            $article->is_published = 1;
            $article->date_posted = Carbon::now();
        }

        $article->save();

        $article->tags()->sync($fields['tags']);

        if ($oarticle) Article::deleteFiles($oarticle, $request);

        return $this->sendResponse([['id' => $article->id]]);
    }

    /**
     * validate  fields.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validate_fields($request, $validations)
    {

        $input = $request->all();

        $validator = Validator::make($input, $validations);

        if ($validator->fails()) {
            return $this->sendError($validator);
        }

        return $validator->validated();
    }
}
