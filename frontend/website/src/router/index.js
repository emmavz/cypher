import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";
import SearchFullView from "../views/SearchFullView.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      components: {
        default: HomeView,
      },
    },
    {
      path: "/article/:articleId/:referralToken?",
      name: "article_homepage",
      // route level code-splitting
      // this generates a separate chunk (Article.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import("../views/ArticleView.vue"),
    },
    {
      path: "/full_article/:articleId",
      name: "full_article_homepage",
      component: () => import("../views/FullArticleView.vue"),
    },
    {
      path: "/search",
      name: "search",
      component: () => import("../views/SearchView.vue"),
    },
    {
      path: "/search_full",
      name: "search_full",
      components: {
        default: SearchFullView,
      },
    },
    {
      path: "/notifications",
      name: "notifications",
      component: () => import("../views/NotificationView.vue"),
    },
    {
      path: "/profile/edit",
      name: "profile-edit",
      component: () => import("../views/UserProfileEdit.vue"),
    },
    {
      path: "/profile/:userId?",
      name: "profile",
      component: () => import("../views/UserProfileView.vue"),
    },
    {
      path: "/send-token/",
      name: "send-token",
      component: () => import("../views/SendToken.vue"),
    },
    {
      path: "/drafts",
      name: "drafts",
      component: () => import("../views/DraftArticleView.vue"),
    },
    {
      path: "/create_article/:articleId?",
      name: "create_article",
      component: () => import("../views/CreateArticleView.vue"),
    },
    {
      path: "/article_published/:articleId",
      name: "create_article_published",
      component: () => import("../views/CreateArticlePublishedView.vue"),
    },
    {
      path: "/signin/",
      name: "signin",
      component: () => import("../views/Signin.vue"),
    },
    {
      path: "/signup/",
      name: "signup",
      component: () => import("../views/Signup.vue"),
    },
  ],
});

router.beforeResolve((to, from, next) => {
  // If this isn't an initial page load.
  if (to.name) {
    // Start the route progress bar.
    // NProgress.start()
  }
  next();
});

router.afterEach((to, from) => {
  // Complete the animation of the route progress bar.
  // NProgress.done()
});

export default router;
