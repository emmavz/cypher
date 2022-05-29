import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import SearchFullView from '../views/SearchFullView.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      components: {
        default: HomeView,
      },
    },
    {
      path: '/article/:articleId',
      name: 'article_homepage',
      // route level code-splitting
      // this generates a separate chunk (Article.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/ArticleView.vue')
    },
    {
      path: '/article_paid/:articleId',
      name: 'full_article_homepage',
      component: () => import('../views/FullArticleView.vue')
    },
    {
      path: '/article_afterpaid/:articleId',
      name: 'article_afterpaid',
      component: () => import('../views/ArticleAfterPaid.vue')
    },
    {
      path: '/search',
      name: 'search',
      component: () => import('../views/SearchView.vue'),
    },
    {
      path: '/search_full',
      name: 'search_full',
      components: {
        default: SearchFullView
      },
    },
    {
      path: '/notifications',
      name: 'notifications',
      component: () => import('../views/NotificationView.vue'),
    },
    {
      path: '/notifications/article/:articleId',
      name: 'notifications.article',
      component: () => import('../views/NotificationArticleView.vue'),
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../views/UserProfileView.vue')
    },
    {
      path: '/another-profile',
      name: 'another-profile',
      component: () => import('../views/OtherProfileView.vue')
    },
    {
      path: '/drafts',
      name: 'drafts',
      component: () => import('../views/DraftArticleView.vue')
    },
    {
      path: '/create_article',
      name: 'create_article',
      component: () => import('../views/CreateArticleView.vue')
    },
    {
      path: '/create_article_published',
      name: 'create_article_published',
      component: () => import('../views/CreateArticlePublishedView.vue')
    }
  ]
});

router.beforeResolve((to, from, next) => {
  // If this isn't an initial page load.
  if (to.name) {
    // Start the route progress bar.
    // NProgress.start()
  }
  next()
})

router.afterEach((to, from) => {
  // Complete the animation of the route progress bar.
  // NProgress.done()
})

export default router
