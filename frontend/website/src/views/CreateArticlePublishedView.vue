<script>

export default {
    data() {
        return {
            image_url: 'http://localhost:8080/dynamic/create_article.png'
        }
    },
}
</script>

<template>
    <div class="app-wp">

        <Header/>

        <!-- Content -->
        <div class="content text-center">

            <div class="font-semibold f-18 br-b py-5 banner_img">
                <button class="close-icon"><img src="/src/assets/img/close-icon--v2.svg" alt="" width="34"></button>
                    Congratulations!
            </div>

            <div class="pt-8 pb-12">
                <div class="container">
                    <h2 class="f-18 mb-7">
                        You just published<br>
                        ‘The History of Fashion’
                    </h2>
                </div>

                <div>
                    <img :src="image_url" alt="" class="w-full mb-11">
                </div>

                <div class="br-t">
                    <div class="container">
                        <div class="f-18 font-semibold mt-9 mb-5">Share your story now</div>
                        <div class="flex justify-center">
                            <div><button><img src="@/assets/img/facebook-icon.svg" alt=""></button></div>
                            <div><button class="ml-6"><img src="@/assets/img/website-icon.svg" alt=""></button></div>
                        </div>
                    </div>
                </div>
            </div>

            <Error/>
        </div>

    </div>
</template>