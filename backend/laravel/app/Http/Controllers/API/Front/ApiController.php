<?php

namespace App\Http\Controllers\API\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\API\Front\BaseController as BaseController;
use App\Jobs\SendUserNotification;
use App\Models\Article;
use App\Models\ArticleUserPaid;
use App\Models\User;
use App\Models\Follow;
use App\Models\Tag;
use App\Models\ArticleShare;
use App\Models\UserProfileInvestment;
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
        $articles = Article::with('user:id,name,pfp', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id')->withCount(['total_invested' => function ($query) {
            return $query->select(DB::raw("SUM(price)"));
        }])->orderBy('date_posted', 'DESC')->limit($request->limit)->skip($request->offset)->where('is_published', 1)->get();

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

        $article = Article::with(['user:id,name,pfp'])->select('id', 'title', 'date_posted', 'image_url', 'price',  'description', 'user_id')->withCount(['total_reads', 'total_shares'])->where('id', $request->article_id)->where('is_published', 1);
        if($request->article_published) {
            $article->where('user_id', $request->auth_id);
        }
        $article = $article->firstOrFail();
        $article->total_investments = $article->total_invested->sum('price');

        $request->merge(['user_id' => $request->auth_id]);
        $request->merge(['auth_id' => $request->auth_id]);
        $user = $this->get_user_profile($request, 0);

        $isAlreadyPaid = ArticleUserPaid::where('user_id', $request->auth_id)->where('article_id', $article->id)->exists();

        // If user didnot pay then check If referrals paid
        if (!$isAlreadyPaid) $isAlreadyPaid = $this->isArticlePaidByReferral($request);

        if ($this->share_article($request)) $article->total_shares_count++;

        $current_time = Carbon::now();

        return $this->sendResponse([$article, $user, $isAlreadyPaid, $current_time]);
    }

    /**
     * Display full article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_full_article(Request $request)
    {

        $article = Article::with('user:id,name,pfp')->select('id', 'title', 'date_posted', 'image_url', 'price', 'description', 'user_id', 'content')->withCount('total_reads', 'total_shares')->where('id', $request->article_id)->where('is_published', 1)->firstOrFail();


        $userStats = null;
        if ($request->auth_id != $article->user_id) {

            $request->merge(['user_id' => $article->user_id]);
            $userStats = $this->get_other_user_investments($request, false);

            // $total_invested = $article->total_invested->sum('price');
            // $article->total_investments = $article->total_invested($request->auth_id)->sum('price');

            // $article->total_stakes = 0;
            // if ($total_invested) {
            //     $article->total_stakes = stake_format(($article->total_investments / $total_invested) * 100);
            // }
        }

        $articlePaid = null;

        if ($article->user_id != $request->auth_id) {
            $articlePaid = ArticleUserPaid::where('user_id', $request->auth_id)->where('article_id', $article->id)->first();
            if (!$articlePaid) {

                // If user didnot pay then check If referrals paid
                if (!$this->isArticlePaidByReferral($request)) {
                    return $this->sendResponse([['is_article_paid' => false]]);
                }
            }
        }

        $request->merge(['user_id' => $request->auth_id]);
        $user = $this->get_user_profile($request, 0);

        if ($this->read_article($request)) $article->total_reads_count++;
        if ($this->share_article($request)) $article->total_shares_count++;

        return $this->sendResponse([$article, $user, $userStats]);
    }

    /**
     * Display user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_profile(Request $request, $shouldReturnResponse = 1)
    {
        $user = User::select('id', 'name', 'bio', 'pfp', 'balance', 'referral_token', DB::raw('CASE WHEN EXISTS(SELECT * FROM follows WHERE users.id = followed_id AND follows.follower_id = ' . $request->auth_id . ') THEN 1 ELSE 0 END AS is_followed'))->withCount(['followers', 'followed'])->where('id', $request->user_id)->firstOrFail();

        if ($shouldReturnResponse) {
            // if (!count($user)) abort(404);
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
        $articles = Article::with('user:id,name,pfp', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id')->withCount(['total_invested' => function ($query) {
            return $query->select(DB::raw("SUM(price)"));
        }])->orderBy('date_posted', 'DESC')->limit($request->limit)->skip($request->offset)->where('is_published', 1)->get();

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
        $articles = Article::with('user:id,name,pfp', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id')->withCount(['total_invested' => function ($query) {
            return $query->select(DB::raw("SUM(price)"));
        }])->orderBy('date_posted', 'DESC')->where('title', 'LIKE', '%' . $request->q . '%')->where('is_published', 1)->get();

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
        $articles = Article::with(['user' => function ($query) {
            $query->select('id', 'name', 'pfp');
        }])->whereHas('user', function($query) use ($request){
            $query->where('id', $request->user_id);
        })->select('id', 'title', 'date_posted', 'image_url', 'user_id')->latest()->where('is_published', 0)->get();

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
        $articles = Article::with('user:id,name,pfp')->whereHas('user', function ($query) use ($request) {
            $query->where('id', $request->user_id);
        })->select('id', 'title', 'date_posted', 'image_url', 'user_id');

        // NOT return if user is logged in and logged in id match with auth id
        if (!$request->auth_id) {
            $articles->with('tags:name');
            $articles->withCount(['total_invested' => function ($query) {
                return $query->select(DB::raw("SUM(price)"));
            }]);
        }

        $articles = $articles->orderBy('date_posted', 'DESC')->where('is_published', 1)->get();

        return $this->sendResponse($articles);
    }

    /** Display user draft article for editing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_draft_article(Request $request)
    {
        $article = Article::with('tags')->where('user_id', $request->user_id)->where('id', $request->article_id)->where('is_published', 0)->firstOrFail();

        return $this->sendResponse($article);
    }

    /**
     * store article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_article(Request $request)
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

        $rules = [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title'   => ['required', 'string'],
            'content' => ['nullable', 'required_if:should_publish,1', 'string'],
            'image_url' => ['nullable', Rule::RequiredIf(function () use ($article) {
                return !$article;
            }), 'mimes:' . config('website.imgformats')],
            'description' => ['nullable', 'required_if:should_publish,1', 'string', 'max:200'],
            'price'   => ['nullable', 'required_if:should_publish,1',  'numeric', 'gte:0'],
            'theta'   => ['nullable', 'required_if:should_publish,1', 'numeric', 'gte:0', 'lte:100'],
            'tags' => ['nullable', 'required_if:should_publish,1', 'array', 'exists:tags,id'],
            'should_publish' => ['required', 'boolean'],
        ];

        $fields = $this->validate_fields($request, $rules);

        $fields = Article::storeFiles($request, $fields, $oarticle);

        $article->title = $fields['title'];
        $article->description = $fields['description'];
        if ($fields['image_url']) $article->image_url = $fields['image_url'];
        $article->is_published = 0;
        $article->user_id = $fields['user_id'];
        $article->content = $fields['content'];
        $article->price = $fields['price'];
        $article->theta = $fields['theta'];

        if ($fields['should_publish']) {
            $article->is_published = 1;
            $article->date_posted = Carbon::now();
        }

        $article->save();

        $article->tags()->sync($fields['tags']);

        if ($oarticle) Article::deleteFiles($oarticle, $request);

        $user = User::find($fields['user_id']);

        if (count($user->articles) == 1 && $article->is_published) {
            SendUserNotification::dispatch(['text' => 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: ' . $article->id . ' } }"><b>' . $article->title . '</b></a>!', 'user_id' => $user->id]);
        }

        return $this->sendResponse([['id' => $article->id]]);
    }

    /** Pay Article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pay_article(Request $request)
    {
        $article = Article::where('id', $request->article_id)->where('is_published', 1);

        if ($request->is_author_article) {
            $article->where('user_id', '!=', $request->auth_id);
        }

        $article = $article->firstOrFail();
        $user = User::findOrFail($request->auth_id);

        if ($user->balance >= $article->price) {

            $articleUserPaid = new ArticleUserPaid;
            $articleUserPaid->price = $article->price;
            $articleUserPaid->user_id = $user->id;
            $articleUserPaid->article_id = $article->id;
            $articleUserPaid->author_id = $article->user_id;
            $articleUserPaid->save();

            // Deduct amount from user balance
            $user->balance -= $article->price;
            $user->update();

            // // Add 90% amount to article owner
            // $articleOwner = User::findOrFail($article->user_id);
            // $articleOwner->balance += (90 / 100) * $article->price;
            // $articleOwner->update();

            // Split 10% amount to all holders proportionally

            SendUserNotification::dispatch(['text' => '<a :to="{ name: \'profile\', params: { userId: ' . $user->id . ' } }"><b>' . $user->name . '</b></a> just invested <b>' . $article->price . ' CPHR</b>.', 'user_id' => $article->user_id]);
        } else {
            return $this->sendError(['balance' => 'You dont have enough balance!']);
        }

        return $this->sendResponse([]);
    }

    /**Read an article
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function read_article(Request $request)
    {
        $article = Article::where('id', $request->article_id)->where('is_published', 1);
        $article = $article->first();

        if ($article) {
            if ($request->auth_id != $article->user_id) {

                if (!$article->total_reads($request->auth_id)->count()) {
                    $article->total_reads()->attach([$request->auth_id]);
                    return true;
                }
            }
        }

        return false;
    }

    /**Share an article
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function share_article(Request $request)
    {

        if ($request->referral_token) {
            $user = User::where('referral_token', $request->referral_token)->where('id', '!=', $request->auth_id)->first();
            $article = Article::where('id', $request->article_id)->where('is_published', 1)->where('user_id', '!=', $request->auth_id)->first();

            if ($user && $article) {
                if (!$article->total_shares($request->auth_id)->count()) {
                    $article->total_shares()->attach([$request->auth_id => ['referrer_id' => $user->id]]);
                    return true;
                }
            }
        }

        return false;
    }

    /**Upvote
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upvote(Request $request)
    {

        $this->validate_fields($request, ['amount' => ['required', 'numeric', 'gt:0']]);

        if ($request->user_id != $request->auth_id) {
            $user = User::findOrFail($request->user_id);
            $auth = User::findOrFail($request->auth_id);

            if ($auth->balance >= $request->amount) {

                $articleUserPaid = new UserProfileInvestment;
                $articleUserPaid->amount = $request->amount;
                $articleUserPaid->user_id = $auth->id;
                $articleUserPaid->author_id = $user->id;
                $articleUserPaid->save();

                // Deduct amount from user balance
                $auth->balance -= $request->amount;
                $auth->update();

                // Add 90% amount to author
                // $user->balance += (90 / 100) * $request->amount;
                // $user->update();

                // Split 10% amount to all holders proportionally

                SendUserNotification::dispatch(['text' => '<a :to="{ name: \'profile\', params: { userId: ' . $auth->id . ' } }"><b>' . $auth->name . '</b></a> just invested <b>' . $request->amount . ' CPHR</b>.', 'user_id' => $user->id]);
            } else {
                return $this->sendError(['balance' => 'You dont have enough balance!']);
            }

            return $this->sendResponse($this->get_other_user_investments($request, false));
        }
    }

    /** Check If Referral Paid for an article
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function isArticlePaidByReferral(Request $request)
    {
        $isAlreadyPaidByReferral = false;
        $user = User::where('id', $request->auth_id)->first();
        if ($user) {
            $articleShares = ArticleShare::where('referrer_id', $user->id)->where('article_id', $request->article_id)->orderBy('id', 'ASC')->limit(config('website.max_article_shares'))->get();

            $refereesIds = [];
            if (count($articleShares)) {

                foreach ($articleShares as $articleShare) {
                    array_push($refereesIds, $articleShare->referee_id);
                    $refereesIds = array_merge($refereesIds, $this->getRefereeIdsAttribute($articleShare->referee_id, $request->article_id));
                }
            }

            $refereesIds = array_unique($refereesIds);

            $isAlreadyPaidByReferral = ArticleUserPaid::whereIn('user_id', $refereesIds)->where('article_id', $request->article_id)->exists();
        }

        return $isAlreadyPaidByReferral;
    }

    public function getRefereeIdsAttribute($referrerId, $articleId)
    {
        $referees = [];

        $articleShare = ArticleShare::where('referrer_id', $referrerId)->where('article_id', $articleId)->first();

        while (!is_null($articleShare) && count($referees) != config('website.max_article_shares')) {
            array_push($referees, $articleShare->referee_id);
            $articleShare = ArticleShare::where('referrer_id', $articleShare->referee_id)->where('article_id', $articleId)->first();
        }

        // $referees = array_reverse($referees);
        // $referees = array_slice($referees, 0, 8);

        return $referees;
    }

    /** Get other user investments
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_other_user_investments(Request $request, $shouldReturnResponse = true)
    {
        $user = User::findOrFail($request->user_id);
        $auth = User::findOrFail($request->auth_id);

        // $userStats = DB::table('user_profile_investments as a')->crossJoin('article_user_paids as b')->where('a.author_id', $request->user_id)->orWhere('b.author_id', $request->user_id)->select(DB::raw(
        //     '
        // @author_id:=a.author_id as author_id,
        // @author_id:=b.author_id as author_id,
        // @total_investments:=COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id FROM article_user_paids) as x WHERE x.author_id = @author_id)), 0) as total_investments,
        // COALESCE((SELECT COUNT(x.user_id) FROM (SELECT user_id,author_id FROM user_profile_investments UNION SELECT user_id,author_id FROM article_user_paids) as x WHERE x.author_id = @author_id), 0) as total_investors,
        // @user_total_investments:=COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id,user_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id,user_id FROM article_user_paids) as x WHERE x.author_id = @author_id AND x.user_id = ' . $request->auth_id . ')), 0) as user_total_investments,
        // COALESCE(ROUND(( @user_total_investments/@total_investments * 100 ),' . config('website.total_stakes_decimal_points') . '), 0) as total_stakes'
        // ))->first();

        // $userStats = DB::table('user_profile_investments as a')->crossJoin('article_user_paids as b')->select(DB::raw(
        //     '
        // @author_id:=a.author_id as author_id,
        // @author_id:=b.author_id as author_id,
        // @total_investments:=(COALESCE((SELECT SUM(amount) FROM user_profile_investments as a1 WHERE a1.author_id = @author_id), 0) + COALESCE((SELECT SUM(price) FROM article_user_paids as b1 WHERE b1.author_id = @author_id), 0) ) as total_investments,
        // @user_total_investments:=(COALESCE((SELECT SUM(amount) FROM user_profile_investments as a1 WHERE a1.author_id = @author_id AND a1.user_id = ' . $request->auth_id .
        //         '), 0) + COALESCE((SELECT SUM(price) FROM article_user_paids as b1 WHERE b1.author_id = @author_id AND b1.user_id = ' . $request->auth_id . '), 0) ) as user_total_investments,
        // COALESCE((SELECT COUNT(x.user_id) FROM (SELECT user_id,author_id FROM user_profile_investments UNION SELECT user_id,author_id FROM article_user_paids) as x WHERE x.author_id = @author_id), 0) as total_investors,
        // COALESCE(ROUND(( @user_total_investments/@total_investments * 100 ),' . config('website.total_stakes_decimal_points') . '), 0) as total_stakes'
        // ))->first();

        $userStats = DB::select(DB::raw('SELECT author_id,
        COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id FROM article_user_paids) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        COALESCE(((SELECT COUNT(DISTINCT x.user_id) FROM (SELECT user_id,author_id FROM user_profile_investments UNION SELECT user_id,author_id FROM article_user_paids) as x WHERE x.author_id = t.author_id)), 0) as total_investors,
        COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id,user_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id,user_id FROM article_user_paids) as x WHERE x.author_id = t.author_id AND x.user_id = ' . $request->auth_id .
            ')), 0) as user_total_investments
        FROM (SELECT author_id, user_id FROM user_profile_investments UNION ALL SELECT author_id, user_id FROM article_user_paids ) t WHERE author_id = ' . $request->user_id . ' LIMIT 1'));

        $userStats = count($userStats) ? $userStats[0] : null;

        if ($userStats) {
            $userStats->total_stakes = 0;
            if ($userStats->total_investments) {
                $userStats->total_stakes = round((($userStats->user_total_investments / $userStats->total_investments) * 100), config('website.total_stakes_decimal_points'));
            }
        } else {
            $userStats = (object) [];
            $userStats->total_investments = 0;
            $userStats->total_investors = 0;
            $userStats->user_total_investments = 0;
            $userStats->total_stakes = 0;
        }

        if ($shouldReturnResponse) return $this->sendResponse($userStats);
        else return $userStats;
    }

    /** Get user investments
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_investments(Request $request)
    {
        $user = User::findOrFail($request->auth_id);

        $userStats = DB::select(DB::raw('SELECT author_id, user_id, t.created_at, users.id as id,
        users.name as title, users.bg as image_url, users.pfp as pfp,
        COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id FROM article_user_paids) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id,user_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id,user_id FROM article_user_paids) as x WHERE x.author_id = t.author_id AND x.user_id = ' . $request->auth_id . ')), 0) as user_total_investments,
        (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`followed_id`) as `user_followers_count`,
        (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`follower_id`) as `user_followed_count`
        FROM (SELECT author_id, user_id, created_at FROM user_profile_investments  UNION ALL SELECT author_id, user_id, created_at FROM article_user_paids ) t INNER JOIN users ON users.id = t.author_id AND user_id = ' . $request->auth_id . '  GROUP BY author_id ORDER BY t.created_at DESC'));

        // $articleUserPaidTable = DB::table('article_user_paids as b')->select(
        //     'b.user_id',
        //     'b.author_id',
        //     'b.created_at',
        //     'users.id as id',
        //     'users.name as title',
        //     'users.bg as image_url',
        //     'users.pfp as pfp',
        //     DB::raw('@price_investments:=COALESCE((SELECT SUM(price) FROM article_user_paids as b1 WHERE b1.author_id = b.author_id), 0) as price_investments'),
        //     DB::raw('@price_user_investments:=COALESCE((SELECT SUM(price) FROM article_user_paids as b1 WHERE b1.author_id = b.author_id AND b1.user_id = ' . $request->auth_id . '), 0) as price_user_investments'),
        // )
        //     ->join('users', function ($join) {
        //         $join->on('users.id', '=', 'b.author_id');
        //     })->where('b.user_id', $request->auth_id)->groupBy('b.author_id');

        // $userProfileInvestmentTable = DB::table('user_profile_investments as a')->select(
        //     'a.user_id',
        //     'a.author_id',
        //     'a.created_at',
        //     'users.id as id',
        //     'users.name as title',
        //     'users.bg as image_url',
        //     'users.pfp as pfp',
        //     DB::raw('@amount_investments:=COALESCE((SELECT SUM(amount) FROM user_profile_investments as a1 WHERE a1.author_id = a.author_id), 0) as amount_investments'),
        //     DB::raw('@amount_user_investments:=COALESCE((SELECT SUM(amount) FROM user_profile_investments as a1 WHERE a1.author_id = a.author_id AND a1.user_id = ' . $request->auth_id . '), 0) as amount_user_investments')
        // )->join('users', function ($join) {
        //     $join->on('users.id', '=', 'a.author_id');
        // })->where('a.user_id', $request->auth_id)->groupBy('a.author_id');

        // $userStats = $userProfileInvestmentTable->union($articleUserPaidTable)->select(DB::raw('@price_investments+@amount_investments as total_investments'))->orderBy('created_at', 'DESC')->get();

        // dd($userStats);

        // $userStats = DB::table('user_profile_investments as a')->union('article_user_paids as b')->where('a.user_id', $request->auth_id)->where('b.user_id', $request->auth_id)->groupBy('a.author_id', 'b.author_id')
        //     ->select(DB::raw(
        //         '
        //         users.id as id,
        //         users.name as title,
        //         users.bg as image_url,
        //         users.pfp as pfp,
        //         @author_id:=a.author_id as author_id,
        //         @author_id:=b.author_id as author_id,
        //         @total_investments:=(COALESCE((SELECT SUM(amount) FROM user_profile_investments as a1 WHERE a1.author_id = @author_id), 0) + COALESCE((SELECT SUM(price) FROM article_user_paids as b1 WHERE b1.author_id = @author_id), 0) ) as total_investments,
        //         @user_total_investments:=(COALESCE((SELECT SUM(amount) FROM user_profile_investments as a1 WHERE a1.author_id = @author_id AND a1.user_id = ' . $request->auth_id .
        //             '), 0) + COALESCE((SELECT SUM(price) FROM article_user_paids as b1 WHERE b1.author_id = @author_id AND b1.user_id = ' . $request->auth_id . '), 0) ) as user_total_investments,
        //         COALESCE(ROUND(( @user_total_investments/@total_investments * 100 ),' . config('website.total_stakes_decimal_points') . '), 0) as total_stakes,
        //         (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where @author_id = `follows`.`followed_id`) as `user_followers_count`,
        //         (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where @author_id = `follows`.`follower_id`) as `user_followed_count`
        //         '
        //     ))->join('users', function ($join) {
        //         $join->on('users.id', '=', 'a.author_id');
        //         $join->on('users.id', '=', 'b.author_id');
        //     })->orderBy('b.created_at', 'DESC')->orderBy('a.created_at', 'DESC')->get();

        foreach ($userStats as $userStat) {

            $userStat->total_stakes = round(($userStat->user_total_investments / $userStat->total_investments) * 100, config('website.total_stakes_decimal_points'));

            $userStat->user = (object) [
                'id' => $userStat->id,
                'image_url' => $userStat->image_url,
                'pfp' => $userStat->pfp,
            ];

            unset($userStat->id);
            unset($userStat->bg);
            unset($userStat->pfp);
        }

        // $articleUserPaids = ArticleUserPaid::with('author:name,id,bg,pfp')->select('id', 'author_id', 'article_id', DB::raw('sum(price) as user_invested'))->addSelect(DB::raw('"" as title, 0 as total_invested, 0 as user_stakes'))->withCount(['author_followers as user_followers_count', 'author_followed as user_followed_count'])->where('user_id', $user->id)->groupBy('author_id')->toSql();
        // dd($articleUserPaids);
        // $totalInvested = ArticleUserPaid::select('author_id', DB::raw('sum(price) as total_invested'))->whereIn('author_id', $articleUserPaids->pluck('author_id'))->groupBy('author_id')->get();

        // foreach ($totalInvested as $ti) {
        //     foreach ($articleUserPaids as $key => $articleUserPaid) {
        //         if ($articleUserPaid->article_id == $ti['author_id']) {

        //             $articleUserPaid->user = $articleUserPaid->author;
        //             $articleUserPaid->title = $articleUserPaid->user->name;
        //             unset($articleUserPaid->user->name);
        //             $articleUserPaid->image_url = $articleUserPaid->user->bg;

        //             $articleUserPaid->total_invested = $ti->total_invested;
        //             if ($articleUserPaid->total_invested) {
        //                 $articleUserPaid->user_stakes =
        //                     stake_format(($articleUserPaid->user_invested / $articleUserPaid->total_invested) * 100);
        //             }
        //         }
        //     }
        // }


        return $this->sendResponse($userStats);
    }

    /**
     * facebook share.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function facebookshare(Request $request, $article_id, $user_id, $version)
    {
        $article = Article::where('id', $article_id)->where('is_published', 1)->firstOrFail();
        $user = User::findOrFail($user_id);

        $ref = \Request::server('HTTP_REFERER');
        if( (strpos($ref,'l.facebook') > -1) || (strpos($ref,'lm.facebook') > -1) ){
            return redirect()->to(env('VUE_URL').'/article/'.$article_id.'/'.$user->referral_token);
        }

        return view('front.facebookshare', ['article' => $article, 'user' => $user, 'v' => $version]);
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
            return $this->sendError($validator->errors()->all());
        }

        return $validator->validated();
    }
}
