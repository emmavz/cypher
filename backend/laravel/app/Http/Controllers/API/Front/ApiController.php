<?php

namespace App\Http\Controllers\API\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\API\Front\BaseController as BaseController;
use App\Jobs\SendUserNotification;
use App\Models\Article;
// use App\Models\ArticleUserPaid;
// use App\Models\UserProfileInvestment;
use App\Models\User;
use App\Models\Follow;
use App\Models\Tag;
use App\Models\ArticleShare;
use App\Models\BlockUser;
use App\Models\UserInvestment;
use App\Models\BondingCurve;
use App\Models\TotalUserInvestment;
use DB;
use Auth;
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
        $articles = Article::with('user:id,name,pfp', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id', 'price', 'liquidation_days')
            ->addSelect($this->getLiquidationDaysQuery())
            ->withCount('is_paid_by_user', 'is_paid_by_referrals')
            ->doesnthave('block_user')
            // ->withCount(['total_invested' => function ($query) {
            //     return $query->select(DB::raw("SUM(price)"));
            // }])
            ->limit($request->limit)->skip($request->offset)->where('is_published', 1)->orderBy('date_posted', 'DESC')->get();

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
        $tags = Tag::where('user_id', '=', null)->get();

        return $this->sendResponse($tags);
    }

    /**
     * Display a listing of the tags for article creating.editing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_article_tags(Request $request)
    {
        $tags = Tag::where('user_id', null)->orWhere(function ($q) use ($request) {
            // $q->where('user_id', auth()->user()->id);
            if ($request->article_id) {
                $q->where('user_id', auth()->user()->id)->where('article_id', $request->article_id);
            }
            return $q;
        })->get();

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

        $article = Article::with(['user:id,name,pfp'])->select('id', 'title', 'date_posted', 'image_url', 'price',  'description', 'user_id', 'share_to_read', 'liquidation_days')
            ->addSelect($this->getLiquidationDaysQuery())
            ->doesnthave('block_user')
            ->withCount(['total_reads', 'total_shares', 'is_paid_by_user', 'is_paid_by_referrals'])->where('id', $request->article_id)->where('is_published', 1);

        // Coming from article published page
        if ($request->article_published) {
            $article->where('user_id', auth()->user()->id);
        }

        $article = $article->firstOrFail();
        $article->total_investments = $article->total_invested->sum('price');

        // Get user profile
        $request->merge(['user_id' => $article->user_id]);
        $user = $this->get_user_profile_with_balance($request, 0);

        // Check If user is lucky winner
        $luckyWinnerId = '';
        if (!$this->isArticleFree($article, 0)) {
            $luckyWinnerId = ArticleShare::select('id')->where('article_id', $article->id)->where('lucky_sharer', true)->where('is_paid', true)->where(function ($query) {
                return $query->where(function ($q) {
                    return $q->where('referrer_id', auth()->user()->id)->where('lucky_sharer_seen_referrer_id', '=', null);
                })->orWhere(function ($q) {
                    return $q->where('referee_id', auth()->user()->id)->where('lucky_sharer_seen_referee_id', '=', null);
                });
            })->first();
        }


        // Check if article is free
        // $isArticleFreeArr = $this->isArticleFree($request, $article);
        // $isArticleFree = $isArticleFreeArr[0];
        // $liquidation_days = $isArticleFreeArr[1];

        // Insert new share in database and increment in total shares count
        if ($this->share_article($request)) $article->total_shares_count++;

        $referral_token = auth()->user()->referral_token;

        return $this->sendResponse([$article, $user, $referral_token, $luckyWinnerId]);
        // return $this->sendResponse([$article, $user, $isArticleFree, $liquidation_days, $referral_token]);
    }

    /**
     * Display full article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_full_article(Request $request)
    {

        $article = Article::with('user:id,name,pfp')->select('id', 'title', 'date_posted', 'image_url', 'price', 'description', 'user_id', 'content', 'share_to_read', 'liquidation_days')
            ->addSelect($this->getLiquidationDaysQuery())
            ->doesnthave('block_user')
            ->withCount('total_reads', 'total_shares', 'is_paid_by_user', 'is_paid_by_referrals')->where('id', $request->article_id)->where('is_published', 1)->firstOrFail();

        // Check if article is free
        if (!$this->isArticleFree($article)) {
            return $this->sendResponse([['is_article_free' => false]]);
        }

        $request->merge(['user_id' => $article->user_id]);

        $userStats = null;
        if (auth()->user()->id != $article->user_id) {
            $userStats = $this->get_other_user_investments($request, false);
        }

        // Check If user is lucky winner
        $luckyWinnerId = '';
        if (!$this->isArticleFree($article, 0)) {
            $luckyWinnerId = ArticleShare::select('id')->where('article_id', $article->id)->where('lucky_sharer', true)->where('is_paid', true)->where(function ($query) {
                return $query->where(function ($q) {
                    return $q->where('referrer_id', auth()->user()->id)->where('lucky_sharer_seen_referrer_id', '=', null);
                })->orWhere(function ($q) {
                    return $q->where('referee_id', auth()->user()->id)->where('lucky_sharer_seen_referee_id', '=', null);
                });
            })->first();
        }

        // Check if article is free
        // $isArticleFreeArr = $this->isArticleFree($request, $article);
        // $isArticleFree = $isArticleFreeArr[0];
        // $liquidation_days = $isArticleFreeArr[1];

        $user = $this->get_user_profile($request, [], 0);

        // Insert new read in database and increment in total reads count
        if ($this->read_article($request)) $article->total_reads_count++;
        // Insert new share in database and increment in total shares count
        if ($this->share_article($request)) $article->total_shares_count++;

        $referral_token = auth()->user()->referral_token;

        return $this->sendResponse([$article, $user, $userStats, $referral_token, $luckyWinnerId]);
    }

    /**
     * Display user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_profile(Request $request, $selectParams = [], $shouldReturnResponse = 1)
    {

        $params = ['id', 'name', 'bio', 'pfp', DB::raw('CASE WHEN EXISTS(SELECT * FROM follows WHERE users.id = followed_id AND follows.follower_id = ' . auth()->user()->id . ') THEN 1 ELSE 0 END AS is_followed')];
        if (count($selectParams)) {
            $params = array_merge($params, $selectParams);
        }

        $user = User::select($params)->withCount(['followers', 'followed'])->where('id', $request->user_id)->doesnthave('block_user')->firstOrFail();

        if ($shouldReturnResponse) {
            return $this->sendResponse($user);
        } else {
            return $user;
        }
    }

    /**
     * Display user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_profile_with_balance(Request $request, $shouldReturnResponse = 1)
    {
        $userProfile = $this->get_user_profile($request, [], 0);

        $arr = [$userProfile, auth()->user()->balance];

        if ($shouldReturnResponse) {
            return $this->sendResponse($arr);
        } else {
            return $arr;
        }
    }

    /**
     * Display auth user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_auth_user_profile(Request $request, $shouldReturnResponse = 1)
    {
        $user = User::select('id', 'name', 'bio', 'pfp', 'balance', 'referral_token', DB::raw('CASE WHEN EXISTS(SELECT * FROM follows WHERE users.id = followed_id AND follows.follower_id = ' . auth()->user()->id . ') THEN 1 ELSE 0 END AS is_followed'))->withCount(['followers', 'followed'])->where('id', auth()->user()->id)->firstOrFail();

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
        $articles = Article::with('user:id,name,pfp', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id', 'price', 'liquidation_days')
            ->addSelect($this->getLiquidationDaysQuery())
            ->doesnthave('block_user')
            ->withCount('is_paid_by_user', 'is_paid_by_referrals')
            // ->withCount(['total_invested' => function ($query) {
            //     return $query->select(DB::raw("SUM(price)"));
            // }])
            ->where('is_published', 1)->limit($request->limit)->skip($request->offset)->orderBy('date_posted', 'DESC')->get();

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
        $articles = Article::with('user:id,name,pfp', 'tags:name')->select('id', 'title', 'date_posted', 'image_url', 'user_id', 'price', 'liquidation_days')
            ->addSelect($this->getLiquidationDaysQuery())
            ->doesnthave('block_user')
            ->withCount('is_paid_by_user', 'is_paid_by_referrals')
            // ->withCount(['total_invested' => function ($query) {
            //     return $query->select(DB::raw("SUM(price)"));
            // }])
            ->where('title', 'LIKE', '%' . $request->q . '%')->where('is_published', 1)->orderBy('date_posted', 'DESC')->get();

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
        $users = User::select('id', 'name', 'bio', 'pfp', DB::raw('CASE WHEN EXISTS(SELECT * FROM follows WHERE users.id = followed_id AND follows.follower_id = ' . $request->follower_id . ') THEN 1 ELSE 0 END AS is_followed'))->where('name', 'LIKE', '%' . $request->q . '%')->where('id', '!=', $request->follower_id)->doesnthave('block_user')->latest()->get();

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

        $this->validate_fields($request, [
            'followed_id' => ['required', 'integer', 'exists:users,id']
        ]);

        $this->checkIfUserIsBlocked($request->followed_id);

        $follow = Follow::where('follower_id', auth()->user()->id)->where('followed_id', $request->followed_id)->first();
        if ($follow) {
            $follow->delete();
        } else {
            $follow = new Follow;
            $follow->follower_id = auth()->user()->id;
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

        $userNotifications = UserNotification::with('notification:id,text')->select('id', 'read_at', 'notification_id')->where('user_id', auth()->user()->id)->limit($request->limit)->skip($request->offset)->orderBy('id', 'DESC')->get();

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
        $this->validate_fields($request, [
            'notification_id' => ['required', 'integer', 'exists:user_notifications,id']
        ]);

        $now = Carbon::now();

        UserNotification::where('id', $request->notification_id)->where('user_id', auth()->user()->id)->update([
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
        }])->whereHas('user', function ($query) use ($request) {
            $query->where('id', auth()->user()->id);
        })->select('id', 'title', 'date_posted', 'image_url', 'user_id')->where('is_published', 0)->latest()->get();

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
        $articlesSelect = ['id', 'title', 'date_posted', 'image_url', 'user_id', 'liquidation_days'];
        $articles = Article::with('user:id,name,pfp')->whereHas('user', function ($query) use ($request) {
            $query->where('id', $request->user_id);
        });

        // Show tags and price only for other user profiles and not for auth user
        if (auth()->user()->id != $request->user_id) {
            $articles->with('tags:name');
            $articles->doesnthave('block_user');
            array_push($articlesSelect, 'price');
            // $articles->withCount(['total_invested' => function ($query) {
            //     return $query->select(DB::raw("SUM(price)"));
            // }]);
        }

        $articles->select($articlesSelect);
        $articles->addSelect($this->getLiquidationDaysQuery())->withCount('is_paid_by_user', 'is_paid_by_referrals');

        $articles = $articles->where('is_published', 1)->orderBy('date_posted', 'DESC')->get();

        return $this->sendResponse($articles);
    }

    /** Display user draft article for editing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_user_draft_article(Request $request)
    {
        $article = Article::with('tags')->where('user_id', auth()->user()->id)->where('id', $request->article_id)->firstOrFail();

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
        $alreadyPublished = false;
        if ($request->article_id) {
            $article = Article::where('user_id', auth()->user()->id)->where('id', $request->article_id)->first();
            if (!$article) {
                $article = new Article();
            } else {
                $oarticle = clone $article;
                if ($article->is_published) $alreadyPublished = true;
            }
        } else {
            $article = new Article();
        }

        // Article Tags
        $formTags = json_decode($request->tags);
        $customTags = [];
        $tagIds = [];
        foreach ($formTags as $tag) {
            if ($tag->custom) {
                array_push($customTags, $tag->name);
            } else {
                array_push($tagIds, $tag->id);
            }
        }

        $request->merge(['all_tags' => $formTags]);
        $request->merge(['tags' => $tagIds]);
        $request->merge(['custom_tags' => $customTags]);

        // Validations
        $rules = [
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'required_if:should_publish,1', 'string'],
            'image_url' => ['nullable', Rule::RequiredIf(function () use ($article) {
                return !$article;
            }), 'mimes:' . config('website.imgformats')],
            'description' => ['nullable', 'required_if:should_publish,1', 'string', 'max:' . config('website.max_article_description_length')],
            'price'   => ['nullable', 'required_if:should_publish,1',  'numeric', 'gte:0'],
            'theta'   => ['nullable', 'required_if:should_publish,1', 'numeric', 'gte:0', 'lte:100'],
            'liquidation_days' => ['nullable', 'required_if:should_publish,1', 'numeric', 'gte:0'],
            'share_to_read' => ['required', 'boolean'],
            'all_tags' => ['nullable', 'required_if:should_publish,1', 'array', 'max:' . config('website.max_article_tags')],
            'tags' => ['nullable', 'array', 'exists:tags,id'],
            'custom_tags' => ['nullable', 'array', 'max:' . config('website.max_custom_article_tags')],
            'custom_tags.*' => ['distinct:ignore_case'],
            'should_publish' => ['required', 'boolean'],
        ];

        $fields = $this->validate_fields($request, $rules);

        // Store an article
        $fields = Article::storeFiles($request, $fields, $oarticle);

        $article->title = $fields['title'];
        $article->description = $fields['description'];
        if ($fields['image_url']) $article->image_url = $fields['image_url'];
        // $article->is_published = 0;
        $article->user_id = auth()->user()->id;
        $article->content = $fields['content'];
        $article->price = $fields['price'];
        $article->theta = $fields['theta'];
        $article->liquidation_days = $fields['liquidation_days'];
        $article->share_to_read = $fields['share_to_read'];

        if ($fields['should_publish'] && !$alreadyPublished) {
            $article->is_published = 1;
            $article->date_posted = Carbon::now();
        }

        $article->save();

        // Assign Article Tags
        $tagIds = $fields['tags'];
        $customTagIds = [];

        if (count($fields['custom_tags'])) {
            foreach ($fields['custom_tags'] as $custom_tag) {
                $tag = new Tag;
                $tag->name = $custom_tag;
                $tag->user_id = auth()->user()->id;
                $tag->article_id = $article->id;
                $tag->save();

                array_push($customTagIds, $tag->id);
            }

            $tagIds = array_merge($tagIds, $customTagIds);
        }

        $article->tags()->sync($tagIds);

        // Delete old images while updating an article
        if ($oarticle) Article::deleteFiles($oarticle, $request);

        // Send auth user notification on his first article published
        if (count(auth()->user()->published_articles) == 1 && !$alreadyPublished && $fields['should_publish']) {
            SendUserNotification::dispatch(['text' => 'Congratulations on your first article: <a :to="{ name: \'article_homepage\', params: { articleId: ' . $article->id . ' } }"><b>' . $article->title . '</b></a>!', 'user_id' => auth()->user()->id]);
        }

        return $this->sendResponse($article);
    }

    /**
     * delete article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete_article(Request $request)
    {
        $article = Article::where('user_id', auth()->user()->id)->where('id', $request->article_id)->firstOrFail();
        $article->delete();
        return $this->sendResponse([]);
    }

    /** Pay Article.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function pay_article(Request $request)
    {
        $article = Article::select('*')->where('id', $request->article_id)->where('is_published', 1)->where('user_id', '!=', auth()->user()->id)
            ->addSelect($this->getLiquidationDaysQuery())
            ->withCount('is_paid_by_user', 'is_paid_by_referrals')->doesnthave('block_user');

        $article = $article->firstOrFail();
        $user = User::findOrFail(auth()->user()->id);

        if ($this->isArticleFree($article)) {
            return $this->sendError(['paid' => 'This article is already free!'], true);
        }

        if ($user->balance >= $article->price) {

            DB::transaction(function () use ($article, $user) {

                $userInvestments = [
                    'user_id' => $user->id,
                    'author_id' => $article->user_id,
                    'amount' => $article->price,
                ];

                $this->addToBondingCurve($user, $article->user_id, $article->price, $article, $user, $userInvestments);

                // Make is_paid column true of all 8 level parents of current user
                $articleShare = ArticleShare::where('article_id', $article->id)->where('referee_id', auth()->user()->id)->first();
                if ($articleShare) {

                    $maxSharesLimit = config('website.max_article_shares');
                    $maxLevelsLimit = $maxSharesLimit;

                    $cte = DB::select("WITH RECURSIVE
                    cte AS ( (SELECT id, referee_id, referrer_id, article_id, 1 lvl
                            FROM article_share
                            WHERE referee_id = " . auth()->user()->id . " AND article_id = " . $article->id . ")
                        UNION ALL
                            (SELECT t.id, t.referee_id, t.referrer_id, t.article_id, cte.lvl + 1
                            FROM cte
                            INNER JOIN article_share t ON cte.referrer_id = t.referee_id
                        WHERE cte.lvl <= " . $maxLevelsLimit . "-1  AND t.article_id = " . $article->id . ") )
                    SELECT id FROM cte;");

                    $shareIds = [];
                    foreach ($cte as $ct) {
                        array_push($shareIds, $ct->id);
                    }

                    if (count($shareIds)) {
                        ArticleShare::whereIn('id', $shareIds)->update([
                            'is_paid' => 1
                        ]);
                    }
                }

                SendUserNotification::dispatch(['text' => '<a :to="{ name: \'profile\', params: { userId: ' . $user->id . ' } }"><b>' . $user->name . '</b></a> just invested <b>' . $article->price . ' CPHR</b>.', 'user_id' => $article->user_id]);
            });
        } else {
            return $this->sendError(['balance' => 'You dont have enough balance!'], true);
        }

        return $this->sendResponse([]);
    }

    /**Upvote
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upvote(Request $request)
    {

        $this->validate_fields($request, ['amount' => ['required', 'numeric', 'gt:0']]);

        // if ($request->user_id != auth()->user()->id) {
        $user = User::doesnthave('block_user')->findOrFail($request->user_id);
        $auth = User::findOrFail(auth()->user()->id);

        if ($auth->balance >= $request->amount) {

            DB::transaction(function () use ($auth, $user, $request) {

                $totalPreviousInvestments = BondingCurve::where('author_id', $user->id)->sum('tokens');
                $upperBound = $this->calculateUpperbound($totalPreviousInvestments, $request->amount);
                $tokens = $upperBound - $totalPreviousInvestments;

                // Bonding Curve
                $bondingCurve = BondingCurve::where('author_id', $user->id)->first();
                if ($bondingCurve) {
                    $bondingCurve->tokens += $tokens;
                    $bondingCurve->investments += $request->amount;
                    $bondingCurve->update();
                } else {
                    $bondingCurve = new BondingCurve();
                    $bondingCurve->tokens = $tokens;
                    $bondingCurve->investments = $request->amount;
                    $bondingCurve->author_id = $user->id;
                    $bondingCurve->save();
                }

                // Total Investments
                $totalUserInvestment = TotalUserInvestment::where('author_id', $user->id)->where('user_id', $auth->id)->first();
                if ($totalUserInvestment) {
                    $totalUserInvestment->total_investments += $request->amount;
                    $totalUserInvestment->total_tokens += $tokens;
                    $totalUserInvestment->update();
                } else {
                    $totalUserInvestment = new TotalUserInvestment();
                    $totalUserInvestment->total_investments = $request->amount;
                    $totalUserInvestment->total_tokens = $tokens;
                    $totalUserInvestment->author_id = $user->id;
                    $totalUserInvestment->user_id = $auth->id;
                    $totalUserInvestment->save();
                }

                // User Investments
                $user->user_investments()->create([
                    'user_id' => $auth->id,
                    'author_id' => $user->id,
                    'amount' => $request->amount,
                    'tokens' => $tokens,
                    'investments' => $request->amount,
                ]);

                // Deduct amount from user balance
                $auth->balance -= $request->amount;
                $auth->update();

                if ($user->id != $auth->id) {
                    SendUserNotification::dispatch(['text' => '<a :to="{ name: \'profile\', params: { userId: ' . $auth->id . ' } }"><b>' . $auth->name . '</b></a> just invested <b>' . $request->amount . ' CPHR</b>.', 'user_id' => $user->id]);
                }
            });
        } else {
            return $this->sendError(['balance' => 'You dont have enough balance!'], true);
        }

        if ($user->id == $auth->id) {
            return $this->sendResponse($this->get_user_investments($request, false));
        } else {
            return $this->sendResponse($this->get_other_user_investments($request, false));
        }

        // }
    }

    /**Cashout
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cashout(Request $request)
    {
        $this->validate_fields($request, ['amount' => ['required', 'numeric', 'gt:0']]);

        // Currently I allowed user to cashout from blocked user
        // $user = User::doesnthave('block_user')->findOrFail($request->user_id);
        $user = User::findOrFail($request->user_id);
        $auth = User::findOrFail(auth()->user()->id);

        $totalUserInvestment = TotalUserInvestment::where('author_id', $user->id)->where('user_id', auth()->user()->id)->where('total_investments', '!=', 0)->first();
        $bondingCurve = BondingCurve::where('author_id', $user->id)->first();

        if ($totalUserInvestment && $bondingCurve) {
            if ($totalUserInvestment->total_investments >= $request->amount) {

                DB::transaction(function () use ($request, $bondingCurve, $auth, $user, $totalUserInvestment) {

                    // Update bonding curve
                    $upperBound = $bondingCurve->tokens;
                    $lowerbound = $upperBound - $request->amount;
                    $tokens = $this->calculateIntegralWithConstant($lowerbound, $upperBound);

                    $bondingCurve->investments -= $request->amount;
                    $bondingCurve->tokens -= $tokens;
                    $bondingCurve->update();

                    // Update total User Investments
                    $totalUserInvestment->total_investments -= $request->amount;
                    $totalUserInvestment->total_tokens -= $tokens;
                    $totalUserInvestment->update();

                    // Add amount into user balance
                    $auth->balance += $request->amount;
                    $auth->update();
                });
            } else {
                return $this->sendError(['balance' => 'You dont have this much amount invested into this author!'], true);
            }

            if ($user->id == $auth->id) {
                return $this->sendResponse($this->get_user_investments($request, false));
            } else {
                return $this->sendResponse($this->get_other_user_investments($request, false));
            }
        } else {
            return $this->sendError(['balance' => 'You dont have any investment to this author!'], true);
        }
    }

    /**Update bonding curve
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addToBondingCurve($authUser, $user_id, $amount, $article, $u, $userInvestments)
    {

        // Split 10% for all previous investors + current auth user
        $amountForPreviousInvestors = (10 / 100) * $amount;

        // Send theta percent directly into author wallet
        $theta = $article->theta;
        $remainingAmount = $amount - $amountForPreviousInvestors;
        if ($theta > 0) {
            $thetaPercentAmount = ($theta / 100) * $remainingAmount;
            User::where('id', $user_id)->increment('balance', $thetaPercentAmount);
            // This amount goes into author bonding curve
            $remainingAmount = $remainingAmount - $thetaPercentAmount;
        }

        // Add remaining amount into bonding curve
        $totalPreviousInvestments = BondingCurve::where('author_id', $user_id)->sum('tokens');
        $upperBound = $this->calculateUpperbound($totalPreviousInvestments, $remainingAmount);
        $tokens = $upperBound - $totalPreviousInvestments;

        $bondingCurve = BondingCurve::where('author_id', $user_id)->first();
        if ($bondingCurve) {
            $bondingCurve->tokens += $tokens;
            $bondingCurve->investments += $remainingAmount;
            $bondingCurve->update();
        } else {
            $bondingCurve = new BondingCurve();
            $bondingCurve->tokens = $tokens;
            $bondingCurve->investments = $remainingAmount;
            $bondingCurve->author_id = $user_id;
            $bondingCurve->save();
        }

        $totalUserInvestment = TotalUserInvestment::where('author_id', $user_id)->where('user_id', $authUser->id)->first();
        if ($totalUserInvestment) {
            $totalUserInvestment->total_investments += $remainingAmount;
            $totalUserInvestment->total_tokens += $tokens;
            $totalUserInvestment->update();
        } else {
            $totalUserInvestment = new TotalUserInvestment();
            $totalUserInvestment->total_investments = $remainingAmount;
            $totalUserInvestment->total_tokens = $tokens;
            $totalUserInvestment->author_id = $user_id;
            $totalUserInvestment->user_id = $authUser->id;
            $totalUserInvestment->save();
        }

        // User investment
        $userInvestments['tokens'] = $tokens;
        $userInvestments['investments'] = $remainingAmount;
        $article->user_investments()->create($userInvestments);

        // Deduct amount from user balance
        $u->balance -= $amount;
        $u->update();

        // Split 10% to all previous investors + current auth user
        $previousInvestorsQ = TotalUserInvestment::where('author_id', $user_id);
        $allPreviousInvestorsTokens = $previousInvestorsQ->sum('total_tokens');
        $allPreviousInvestors = $previousInvestorsQ->get();

        foreach ($allPreviousInvestors as $previousInvestor) {
            $splittedAmountPercentage = ($previousInvestor->total_tokens / $allPreviousInvestorsTokens) * 100;
            $splittedAmount = ($splittedAmountPercentage / 100) * $amountForPreviousInvestors;
            User::where('id', $previousInvestor->user_id)->increment(
                'balance',
                $splittedAmount
            );
        }

        return true;
    }

    /**Read an article
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function read_article(Request $request)
    {
        $article = Article::where('id', $request->article_id)->where('is_published', 1)->doesnthave('block_user');
        $article = $article->first();

        if ($article) {
            if (auth()->user()->id != $article->user_id) {

                if (!$article->total_reads(auth()->user()->id)->count()) {
                    $article->total_reads()->attach([auth()->user()->id]);
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
            $user = User::where('referral_token', $request->referral_token)->where('id', '!=', auth()->user()->id)->first();
            $article = Article::select('*')->where('id', $request->article_id)->where('is_published', 1)->where('user_id', '!=', auth()->user()->id)
                ->addSelect($this->getLiquidationDaysQuery())
                ->withCount('is_paid_by_user', 'is_paid_by_referrals')->doesnthave('block_user')->first();

            if ($user && $article && $article->share_to_read) {

                if ($this->isArticleFree($article)) {
                    return false;
                }

                $cte = DB::select("with recursive
                    children as (
                        select 1 as lvl, d.* from article_share d where referrer_id = " . auth()->user()->id . " AND article_id = " . $article->id . "
                        union all
                        select c.lvl, d.* from article_share d inner join children c on c.referee_id = d.referrer_id WHERE d.article_id = " . $article->id . "
                    ),
                    parents as (
                        select 1 as lvl, d.* from article_share d where referee_id = " . auth()->user()->id . " AND article_id = " . $article->id . "
                        union all
                        select p.lvl - 1, d.* from article_share d inner join parents p on d.referee_id = p.referrer_id WHERE d.article_id = " . $article->id . "
                    )
                select * from parents
                union   -- on purpose, to remove the duplicate on referee_id " . auth()->user()->id . "
                select * from children
                WHERE article_id = " . $article->id . "
                order by lvl;");

                $alreadyPresent = false;
                for ($i = 0; $i < count($cte); $i++) {
                    $ct = $cte[$i];
                    if (($user->id == $ct->referee_id) || ($user->id == $ct->referrer_id)) {
                        $alreadyPresent = true;
                        break;
                    }
                }

                if (!$alreadyPresent) {
                    $article->total_shares()->attach([auth()->user()->id => ['referrer_id' => $user->id, 'is_paid' => 0]]);
                    return true;
                }
            }
        }

        return false;
    }

    /** Check If Referrals Paid for an article
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function isArticlePaidByReferrals(Request $request)
    // {

    //     $maxSharesLimit = config('website.max_article_shares');
    //     $maxDirectChildsLimit = $maxSharesLimit;
    //     // maxLevelsLimit applys on recursion part in resursive query (After UNION ALL). It means first level will always return even if you set it 0
    //     $maxLevelsLimit = $maxSharesLimit;

    //     $isAlreadyPaidByReferral = false;

    //     $cte = DB::select("WITH RECURSIVE
    //         cte AS ( (SELECT referee_id, referrer_id, article_id, 1 lvl
    //                 FROM article_share
    //                 WHERE referrer_id = " . auth()->user()->id . " AND article_id = " . $request->article_id . " LIMIT " . $maxDirectChildsLimit . ")
    //             UNION ALL
    //                 (SELECT t.referee_id, t.referrer_id, cte.article_id, cte.lvl + 1
    //                 FROM cte
    //                 INNER JOIN article_share t ON cte.referee_id = t.referrer_id
    //             WHERE cte.lvl <= " . $maxLevelsLimit . "-1  AND cte.article_id = " . $request->article_id . " LIMIT " . $maxDirectChildsLimit . ") )
    //         SELECT * FROM cte;");

    //     $refereesIds = [];
    //     foreach ($cte as $ct) {
    //         array_push($refereesIds, $ct->referrer_id);
    //         array_push($refereesIds, $ct->referee_id);
    //     }
    //     $refereesIds = array_unique($refereesIds);

    //     // $refereesIds = array_merge($refereesIds, array_column($cte, 'referee_id'));
    //     // $refereesIds = array_merge($refereesIds, array_column($cte, 'referrer_id'));
    //     // $refereesIds = array_unique($refereesIds);

    //     $isAlreadyPaidByReferral = UserInvestment::whereIn('user_id', $refereesIds)->where('user_investmentable_type', Article::class)->where('user_investmentable_id', $request->article_id)->exists();

    //     return $isAlreadyPaidByReferral;

    //     // $refereesIds = [];
    //     // if (count($articleShares)) {

    //     //     foreach ($articleShares as $articleShare) {
    //     //         array_push($refereesIds, $articleShare->referee_id);
    //     //         $refereesIds = array_merge($refereesIds, $this->getRefereeIdsAttribute($articleShare->id, $articleShare->referee_id, $request->article_id));
    //     //     }
    //     // }

    //     // $refereesIds = array_unique($refereesIds);

    //     // $articleShares = ArticleShare::where('referrer_id', auth()->user()->id)->where('article_id', $request->article_id)->orderBy('id', 'ASC')->limit($maxLevelsLimit)->get();
    //     // $refereesIds = $articleShares->pluck('referee_id')->toArray();

    //     // $isAlreadyPaidByReferral = ArticleUserPaid::whereIn('user_id', $refereesIds)->where('article_id', $request->article_id)->exists();

    //     // return $isAlreadyPaidByReferral;
    // }

    // public function getRefereeIdsAttribute($id, $referrerId, $articleId)
    // {
    //     $referees = [];

    //     $articleShare = ArticleShare::where('id','>',$id)->where('referrer_id', $referrerId)->where('article_id', $articleId)->first();

    //     while (!is_null($articleShare) && count($referees) != config('website.max_article_shares')) {
    //         array_push($referees, $articleShare->referee_id);
    //         $articleShare = ArticleShare::where('id','>',$articleShare->id)->where('referrer_id', $articleShare->referee_id)->where('article_id', $articleId)->first();
    //     }

    //     // $referees = array_reverse($referees);
    //     // $referees = array_slice($referees, 0, 8);

    //     return $referees;
    // }

    /** Check If article is free to view
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function isArticleFree(Request $request, $article)
    // {
    //     $isArticleFree = false;

    //     // Check for Liquidation days
    //     $liquidation_days = $this->getLiquidationDays($article);

    //     // article is free for its author/creator
    //     if (auth()->user()->id == $article->user_id) {
    //         return [true, $liquidation_days];
    //     }

    //     if ($liquidation_days) {
    //         // Check if user himself paid the article
    //         $isArticleFree = UserInvestment::where('user_id', auth()->user()->id)->where('user_investmentable_type', Article::class)->where('user_investmentable_id', $request->article_id)->exists();

    //         // If user didnot pay then check If referrals paid
    //         if (!$isArticleFree) $isArticleFree = ArticleShare::where('article_id', $request->article_id)->where('is_paid', true)->where(function($query){ return $query->where('referrer_id', auth()->user()->id)->orWhere('referee_id', auth()->user()->id); })->exists();
    //         // if (!$isArticleFree) $isArticleFree = $this->isArticlePaidByReferrals($request);
    //     } else {
    //         $isArticleFree = true;
    //     }

    //     return [$isArticleFree, $liquidation_days];
    // }

    /** Check for lucky day winner
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function lucky_day(Request $request)
    {

        DB::transaction(function () {

            $maxSharesLimit = config('website.max_article_shares');
            $maxDirectChildsLimit = $maxSharesLimit;
            $maxLevelsLimit = $maxSharesLimit;

            $admin = $this->getSuperAdmin();

            $cte = DB::select("with recursive myCTE  as (
                    (select
                        id as root_id,
                        referrer_id as root_referrer_id,
                        referee_id as root_referee_id,
                        referee_id,
                        referrer_id,
                        article_id,
                        1 level
                    from article_share WHERE lucky_sharer = false AND is_paid = false)
                    union all
                    (select
                        mC.root_id,
                        mC.referrer_id as root_referrer_id,
                        mC.referee_id as root_referee_id,
                        mT.referee_id,
                        mT.referrer_id,
                        mC.article_id,
                        level + 1
                    from article_share mT
                    inner join myCTE mC on mT.referee_id = mC.referrer_id WHERE mC.article_id = mT.article_id AND mT.lucky_sharer = false AND mT.is_paid = false)
                )
                select root_id, group_concat(referrer_id order by level) all_parents, MAX(level) as max_level, article_id, price, liquidation_days, date_posted,
                " . $this->getLiquidationDaysQuery(1) . "
                from myCTE INNER JOIN articles ON articles.id = article_id WHERE price != 0 AND is_published = 1
                group by root_id, article_id HAVING remaining_liquidation_days != 0 ORDER BY max_level DESC
                ");

            if (count($cte)) {
                // Remove rows that belongs to same chairing chain from query result
                $arr = [];

                foreach ($cte as $k => $ct) {

                    $found = false;
                    if (count($arr)) {
                        for ($i = 0; $i < count($arr); $i++) {
                            if (($ct->article_id == $arr[$i]->article_id) && (str_contains($arr[$i]->all_parents, $ct->all_parents))) {
                                $found = true;
                                break;
                            }
                        }
                    }

                    if (!$found) {
                        array_push($arr, $ct);
                    }
                }


                // Find random indexes based on probility levels to max max_lucky_members_chain limit
                $newArr = [];

                for ($i = 0; $i < config('website.max_lucky_members_chain'); $i++) {
                    if (count($arr)) {
                        $randomItemIndex =  $this->randProb($arr, $maxLevelsLimit);
                        array_push($newArr, $arr[$randomItemIndex]);
                        unset($arr[$randomItemIndex]);
                    }
                }

                // Limit all chains to max max_lucky_members_chain limit
                // $newArr = array_slice($newArr, 0, config('website.max_lucky_members_chain'));

                foreach ($newArr as $ct) {

                    $article = Article::findOrFail($ct->article_id);
                    $articleShare = ArticleShare::where('id', $ct->root_id)->firstOrFail();
                    $user = User::findOrFail($articleShare->referee_id);

                    if ($admin->balance >= $article->price) {

                        $parents = DB::select("with recursive cte as (
                        (select id, referee_id, referrer_id, 1 lvl, article_id from article_share WHERE lucky_sharer = false AND is_paid = false AND id = " . $ct->root_id . " LIMIT " . $maxDirectChildsLimit . ")
                        union all
                        (select t.id, t.referee_id, t.referrer_id, lvl + 1, t.article_id
                        from cte c
                        inner join article_share t on t.referee_id = c.referrer_id WHERE lucky_sharer = false AND is_paid = false AND c.lvl <= " . $maxLevelsLimit . "-1  LIMIT " . $maxDirectChildsLimit . ")
                    )
                    SELECT * FROM cte");

                        $shareIds = [];
                        foreach ($parents as $parent) {
                            array_push($shareIds, $parent->id);
                        }

                        if (count($shareIds)) {
                            ArticleShare::whereIn('id', $shareIds)->update([
                                'is_paid' => 1,
                                'lucky_sharer' => 1
                            ]);
                        }

                        $userInvestments = [
                            'user_id' => $user->id,
                            'author_id' => $article->user_id,
                            'amount' => $article->price,
                        ];

                        $this->addToBondingCurve($user, $article->user_id, $article->price, $article, $admin, $userInvestments);

                        SendUserNotification::dispatch(['text' => '<a :to="{ name: \'profile\', params: { userId: ' . $user->id . ' } }"><b>' . $user->name . '</b></a> just invested <b>' . $article->price . ' CPHR</b>.', 'user_id' => $article->user_id]);
                    } else {
                        return $this->sendError(['balance' => 'You dont have enough balance!'], true);
                    }
                }
            }
        });

        return $this->sendResponse([]);
    }

    /** Check for lucky day winner
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function lucky_day_seen(Request $request)
    {
        $fields = $this->validate_fields($request, [
            'article_id' => ['required', 'exists:articles,id']
        ]);

        $articleShares = ArticleShare::where('article_id', $fields['article_id'])->where('lucky_sharer', true)->where('is_paid', true)->where(function ($query) {
            return $query->where(function ($q) {
                return $q->where('referrer_id', auth()->user()->id)->where('lucky_sharer_seen_referrer_id', '=', null);
            })->orWhere(function ($q) {
                return $q->where('referee_id', auth()->user()->id)->where('lucky_sharer_seen_referee_id', '=', null);
            });
        })->get();

        if (count($articleShares)) {

            foreach ($articleShares as $articleShare) {
                if ($articleShare->referrer_id == auth()->user()->id) {
                    $articleShare->update([
                        'lucky_sharer_seen_referrer_id' => Carbon::now()
                    ]);
                } elseif ($articleShare->referee_id == auth()->user()->id) {
                    $articleShare->update([
                        'lucky_sharer_seen_referee_id' => Carbon::now()
                    ]);
                }
            }
        }

        return $this->sendResponse([]);
    }

    // public function getLiquidationDays($article)
    // {

    //     $current_time = Carbon::now();
    //     $posted_time_after_days = Carbon::createFromFormat('Y-m-d H:i:s', $article->date_posted)->addDays($article->liquidation_days);

    //     $liquidation_days = ceil(
    //         ($posted_time_after_days->valueOf() - $current_time->valueOf()) /
    //             (1000 * 3600 * 24)
    //     );

    //     return $liquidation_days = $liquidation_days < 0 ? 0 : $liquidation_days;
    // }

    /** Get other user investments
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_other_user_investments(Request $request, $shouldReturnResponse = true)
    {
        User::doesnthave('block_user')->findOrFail($request->user_id);

        // $userStats = DB::select(DB::raw('SELECT author_id,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id FROM article_user_paids) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        // COALESCE(((SELECT COUNT(DISTINCT x.user_id) FROM (SELECT user_id,author_id FROM user_profile_investments UNION SELECT user_id,author_id FROM article_user_paids) as x WHERE x.author_id = t.author_id)), 0) as total_investors,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id,user_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id,user_id FROM article_user_paids) as x WHERE x.author_id = t.author_id AND x.user_id = ' . auth()->user()->id .
        //     ')), 0) as user_total_investments
        // FROM (SELECT author_id, user_id FROM user_profile_investments UNION ALL SELECT author_id, user_id FROM article_user_paids ) t WHERE author_id = ' . $request->user_id . ' LIMIT 1'));

        // $userStats = DB::select(DB::raw('SELECT author_id,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT total_investments as amt,author_id FROM bonding_curves) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        // COALESCE(((SELECT COUNT(DISTINCT x.user_id) FROM (SELECT user_id,author_id FROM bonding_curves) as x WHERE x.author_id = t.author_id)), 0) as total_investors,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT total_investments as amt,author_id,user_id FROM bonding_curves) as x WHERE x.author_id = t.author_id AND x.user_id = ' . auth()->user()->id .
        //     ')), 0) as user_total_investments
        // FROM bonding_curves t WHERE author_id = ' . $request->user_id . ' LIMIT 1'));

        // $userStats = DB::select(DB::raw('SELECT author_id,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id FROM user_investments) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        // COALESCE(((SELECT COUNT(DISTINCT x.user_id) FROM (SELECT user_id,author_id FROM user_investments) as x WHERE x.author_id = t.author_id)), 0) as total_investors,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id,user_id FROM user_investments) as x WHERE x.author_id = t.author_id AND x.user_id = ' . auth()->user()->id .
        //     ')), 0) as user_total_investments
        // FROM user_investments t WHERE author_id = ' . $request->user_id . ' LIMIT 1'));

        $userStats = DB::select(DB::raw('SELECT author_id,
        COALESCE(((SELECT SUM(x.amt) FROM (SELECT total_investments as amt,author_id FROM total_user_investments) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        COALESCE(((SELECT COUNT(DISTINCT x.user_id) FROM (SELECT user_id,author_id FROM total_user_investments) as x WHERE x.author_id = t.author_id)), 0) as total_investors,
        COALESCE(((SELECT SUM(x.amt) FROM (SELECT total_investments as amt,author_id,user_id FROM total_user_investments) as x WHERE x.author_id = t.author_id AND x.user_id = ' . auth()->user()->id .
            ')), 0) as user_total_investments
        FROM total_user_investments t WHERE author_id = ' . $request->user_id . ' AND t.total_investments != 0  LIMIT 1'));

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
    public function get_user_investments(Request $request, $shouldReturnResponse = true)
    {

        // $userStats = DB::select(DB::raw('SELECT author_id, user_id, t.created_at, users.id as id,
        // users.name as title, users.bg as image_url, users.pfp as pfp,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id FROM article_user_paids) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id,user_id FROM user_profile_investments UNION ALL SELECT price as amt,author_id,user_id FROM article_user_paids) as x WHERE x.author_id = t.author_id AND x.user_id = ' . auth()->user()->id . ')), 0) as user_total_investments,
        // (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`followed_id`) as `user_followers_count`,
        // (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`follower_id`) as `user_followed_count`
        // FROM (SELECT author_id, user_id, created_at FROM user_profile_investments  UNION ALL SELECT author_id, user_id, created_at FROM article_user_paids ) t INNER JOIN users ON users.id = t.author_id AND user_id = ' . auth()->user()->id . '  GROUP BY author_id ORDER BY t.created_at DESC'));

        // $userStats = DB::select(DB::raw('SELECT author_id, user_id, t.updated_at, users.id as id,
        // users.name as title, users.bg as image_url, users.pfp as pfp,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT total_investments as amt,author_id FROM bonding_curves) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT total_investments as amt,author_id,user_id FROM bonding_curves) as x WHERE x.author_id = t.author_id AND x.user_id = ' . auth()->user()->id . ')), 0) as user_total_investments,
        // (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`followed_id`) as `user_followers_count`,
        // (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`follower_id`) as `user_followed_count`
        // FROM bonding_curves t INNER JOIN users ON users.id = t.author_id AND user_id = ' . auth()->user()->id . '  ORDER BY t.updated_at DESC'));

        // $userStats = DB::select(DB::raw('SELECT author_id, user_id, t.updated_at, users.id as id,
        // users.name as title, users.bg as image_url, users.pfp as pfp,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id FROM user_investments) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        // COALESCE(((SELECT SUM(x.amt) FROM (SELECT amount as amt,author_id,user_id FROM user_investments) as x WHERE x.author_id = t.author_id AND x.user_id = ' . auth()->user()->id . ')), 0) as user_total_investments,
        // (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`followed_id`) as `user_followers_count`,
        // (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`follower_id`) as `user_followed_count`
        // FROM user_investments t INNER JOIN users ON users.id = t.author_id AND user_id = ' . auth()->user()->id . ' GROUP BY author_id  ORDER BY t.updated_at DESC'));

        $userStats = DB::select(DB::raw('SELECT author_id, user_id, t.updated_at, users.id as id,
        users.name as title, users.pfp as pfp,
        COALESCE(((SELECT SUM(x.amt) FROM (SELECT total_investments as amt,author_id FROM total_user_investments) as x WHERE x.author_id = t.author_id)), 0) as total_investments,
        COALESCE(((SELECT SUM(x.amt) FROM (SELECT total_investments as amt,author_id,user_id FROM total_user_investments) as x WHERE x.author_id = t.author_id AND x.user_id = ' . auth()->user()->id . ')), 0) as user_total_investments,
        (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`followed_id`) as `user_followers_count`,
        (select count(*) from `users` inner join `follows` on `follows`.`id` = `users`.`id` where t.author_id = `follows`.`follower_id`) as `user_followed_count`,
        (select image_url from articles WHERE articles.user_id = users.id AND articles.is_published = 1 ORDER BY articles.date_posted DESC LIMIT 1) as image_url
        FROM total_user_investments t INNER JOIN users ON users.id = t.author_id AND user_id = ' . auth()->user()->id . ' AND t.total_investments != 0 ORDER BY t.updated_at DESC'));


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

        if ($shouldReturnResponse) return $this->sendResponse($userStats);
        else return $userStats;
    }

    /**
     * Get edit user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get_edit_user_profile(Request $request)
    {
        $user = User::select('id', 'name', 'bio', 'pfp', 'email', 'referral_token')->where('id', auth()->user()->id)->firstOrFail();

        return $this->sendResponse($user);
    }

    /**
     * Update user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_user_profile(Request $request)
    {
        $rules = User::rules();
        $rules['bio'][0] = 'required';
        $rules['password'][0] = 'nullable';
        $rules['email'][4] = 'unique:users,email,' . auth()->user()->id;

        $fields = $this->validate_fields($request, $rules);

        $user = User::findOrFail(auth()->user()->id);
        $ouser = clone $user;

        $fields = User::storeFiles($request, $fields, $ouser);

        $user->name = $fields['name'];
        if ($fields['password']) $user->password = bcrypt($fields['password']);
        if ($fields['pfp']) $user->pfp = $fields['pfp'];
        $user->email = $fields['email'];
        $user->bio = $fields['bio'];
        $user->update();

        User::deleteFiles($ouser, $request);

        return $this->sendResponse([]);
    }

    /**
     * signup.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
        if (getAuthId() == -1) {
            $rules = User::rules();
            $fields = $this->validate_fields($request, $rules);

            $user = new User();
            $user->name = $fields['name'];
            $user->password = bcrypt($fields['password']);
            $user->email = $fields['email'];
            $user->balance = config('website.balance');
            $user->referral_token = generateReferral();
            $user->save();

            SendUserNotification::dispatch(['text' => 'Welcome to Cypher! <a :to="{ name: \'drafts\' }"><b>Get started here</b></a>.', 'user_id' => $user->id]);

            return $this->sendResponse(['id' => $user->id, 'name' => $user->name, 'token' => $user->createToken('API Token')->plainTextToken]);
        }
    }

    /**
     * signin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signin(Request $request)
    {
        if (getAuthId() == -1) {
            $rules = User::rules();
            $rules['name'][0] = 'nullable';
            $rules['email'][4] = '';
            $fields = $this->validate_fields($request, $rules);

            if (!Auth::attempt($fields)) {
                return $this->sendError(['error' => 'Credentials do\'not match!'], false, 401);
            }

            return $this->sendResponse(['id' => auth()->user()->id, 'name' => auth()->user()->name, 'token' => auth()->user()->createToken('API Token')->plainTextToken]);
        }
    }

    /**
     * logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([]);
    }

    /**
     * Send Token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send_token(Request $request)
    {
        $rules = [
            'cphr' => ['required', 'numeric', 'bail', 'gt:0'],
            'receiver' => ['required', 'email', 'exists:users,email']
        ];

        $fields = $this->validate_fields($request, $rules);

        $auth = User::findOrFail(auth()->user()->id);
        $user = User::where('email', $fields['receiver'])->firstOrFail();

        if ($user->id == auth()->user()->id) {
            return $this->sendError(['balance' => 'You cant send tokens to yourself!'], true);
        }

        // Check if user is blocked
        $this->checkIfUserIsBlocked($user->id);

        if ($auth->balance >= $fields['cphr']) {
            $auth->balance -= $fields['cphr'];
            $auth->update();

            $user->balance += $fields['cphr'];
            $user->update();

            SendUserNotification::dispatch(['text' => '<b>' . $auth->name . '</b> sent <b>' . $fields['cphr'] . ' CPHR</b>.', 'user_id' => $user->id]);
        } else {
            return $this->sendError(['balance' => 'You dont have enough balance!'], true);
        }

        return $this->sendResponse([]);
    }

    /**
     * Block User.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function block_user(Request $request)
    {
        $rules = [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];

        $fields = $this->validate_fields($request, $rules);

        $user = User::where('id', $fields['user_id'])->where('id', '!=', auth()->user()->id)->firstOrFail();
        if (auth()->user()->block_user_func($user->id)->exists()) {
            auth()->user()->block_user_func()->detach($user->id);
        } else {
            auth()->user()->block_user_func()->attach($user->id);
        }

        return $this->sendResponse([]);
    }

    /**
     * Send notification.
     *
     */

    // public function sendNotification($msg, $userId)
    // {
    //     $notification = new Notification();
    //     $notification->text = $msg;
    //     $notification->save();

    //     $userNotification = new UserNotification();
    //     $userNotification->notification_id = $notification->id;
    //     $userNotification->user_id = $userId;
    //     $userNotification->save();
    // }

    public function getLiquidationDaysQuery($returnString = false)
    {
        $query = '(CAST(GREATEST(CEIL(timestampdiff(SECOND, NOW(), DATE_ADD(date_posted, INTERVAL liquidation_days DAY)) / (86400)), 0) as SIGNED)) as remaining_liquidation_days';
        if ($returnString) {
            return $query;
        }
        return DB::raw($query);
        // return DB::raw('CEIL( (UNIX_TIMESTAMP(DATE_ADD(date_posted, INTERVAL liquidation_days DAY)) - CAST(UNIX_TIMESTAMP(NOW(3)) as SIGNED) ) / (1000 * 3600 * 24) ) as remaining_liquidation_days');
    }

    // If you edit this function dont forget to edit same function in frontend mixin.js
    public function isArticleFree($article, $checkForReferrals = 1)
    {
        $is_article_free = false;
        if (auth()->user() && auth()->user()->id == $article->user_id) {
            $is_article_free = true;
        } else if ($article->remaining_liquidation_days == 0) {
            $is_article_free = true;
        } else if ($article->is_paid_by_user_count) {
            $is_article_free = true;
        } else if ($checkForReferrals && $article->is_paid_by_referrals_count) {
            $is_article_free = true;
        } else if ($article->price <= 0) {
            $is_article_free = true;
        }
        return $is_article_free;
    }

    public function checkIfUserIsBlocked($user_id)
    {
        $isBlocked = BlockUser::where('user_1', getAuthId())->where('user_2', $user_id)->exists();
        if ($isBlocked) {
            return $this->sendError(['user' => ['This user is blocked by you!']], true);
        }
        return false;
    }

    public function calculateUpperbound($lowerbound, $result)
    {
        return pow((($result + (2 / 3) * pow($lowerbound, 3 / 2)) * 3) / 2, 2 / 3);
    }

    public function calculateIntegralWithConstant($lowerbound, $upperbound)
    {
        return round(0.8 * (
            (2 / 3) * pow($upperbound, 3 / 2) - (2 / 3) * pow($lowerbound, 3 / 2)
        ));
    }

    public function getSuperAdmin()
    {
        $user = User::where('is_admin', true)->firstOrFail();
        return $user;
    }

    public function is_logged_in()
    {
        return $this->sendResponse(getAuthId());
    }

    public function randProb($newArr, $maxLevelsLimit)
    {
        $totalProbability = 0; // This is defined to keep track of the total amount of entries

        foreach ($newArr as $item) {
            $item->max_level = $item->max_level > $maxLevelsLimit ? $maxLevelsLimit : $item->max_level;
            $totalProbability += $item->max_level * config('website.lucky_day_percentage');
        }

        $stopAt = rand(0, $totalProbability); // This picks a random entry to select
        $currentProbability = 0; // The current entry count, when this reaches $stopAt the winner is chosen

        foreach ($newArr as $k => $item) { // Go through each possible item
            $currentProbability += $item->max_level * config('website.lucky_day_percentage'); // Add the probability to our $currentProbability tracker
            if ($currentProbability >= $stopAt) { // When we reach the $stopAt variable, we have found our winner
                return $k;
            }
        }

        return null;
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
        if ((strpos($ref, 'l.facebook') > -1) || (strpos($ref, 'lm.facebook') > -1)) {
            return redirect()->to(env('VUE_URL') . '/article/' . $article_id . '/' . $user->referral_token);
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
            return $this->sendError($validator->errors()->all(), true);
        }

        return $validator->validated();
    }
}
