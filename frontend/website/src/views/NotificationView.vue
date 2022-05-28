<script>
export default ({
  data() {
    return {
      notifications: [
          {
              name: '<b>Allison Vega</b> just invested <b>20 CPHR</b>.',
              url: '0',
              unread: true
          },
          {
              name: '<b>Debby Anonymous</b> just invested <b>14 CPHR</b>.',
              url: '1',
              unread: false
          },
          {
              name: 'Congratulations on your first article: <b>Tokenomics</b>!',
              url: '2',
              unread: false
          },
          {
              name: 'Your article <b> School’s Out</b> has been liquidated. <b>See statistics here</b>',
              url: '3',
              unread: true
          },
          {
              name: 'Welcome to Cypher! <b>Get started here</b>.',
              url: '4',
              unread: false
          }
      ],
    }
  },
})
</script>

<template>

    <div class="app-wp">

        <Header/>

        <!-- Content -->
        <div class="content i-wrap">

            <div class="container">
                <h2 class="mb-6 ml-2">Notifications</h2>
            </div>

            <ul class="notifications">
                <li v-for="(notification, index) in notifications" :key="index" :class="{'unread': notification.unread}">
                    <RouterLink :to="{name: 'notifications.article', params: {articleId: notification.url } }" class="block" >
                        <span class="container block" v-html="notification.name"></span>
                    </RouterLink>
                </li>
            </ul>

            <Error/>
        </div>

    </div>

</template>