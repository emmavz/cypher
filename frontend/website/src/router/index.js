import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Footer from '@/components/Footer.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      components: {
        default: HomeView,
        Footer: Footer
      }
    },
    {
      path: '/article/:articleId',
      name: 'article',
      // route level code-splitting
      // this generates a separate chunk (Article.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/ArticleView.vue')
    }
  ]
})

export default router
