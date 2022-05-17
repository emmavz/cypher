<template>
    <footer class="footer">
        <div class="container">
            <div class="footer__controls">
                <ul class="flex justify-center items-center">
                    <li><RouterLink :to="{name: 'home'}"><img src="@/assets/img/home-icon.svg" alt=""></RouterLink></li>
                    <li><RouterLink :to="{name: 'search'}"><img src="@/assets/img/search-icon.svg" alt=""></RouterLink></li>
                    <li><a href="#" class="active"><img src="@/assets/img/plus-white-icon.svg" alt=""></a></li>
                    <li><RouterLink :to="{name: 'notifications'}"><img src="@/assets/img/notification-icon.svg" alt=""></RouterLink></li>
                    <li><RouterLink :to="{name: 'profile'}"><img src="@/assets/img/profile-icon.svg" alt=""></RouterLink></li>
                </ul>
            </div>
        </div>
    </footer>
</template>