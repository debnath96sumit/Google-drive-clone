<template>
    <nav class="min-w-[200px]">
        <Link :href="route('myFiles')" class="h-[80px] px-3 flex items-center gap-3">
            <ApplicationLogo 
                class="block h-9 w-auto fill-current text-gray-800"
            />
            Your File Storage
        </Link>
        <div class="px-3">
            <CreateNewDropdown/>

            <div class="py-3">
                <NavLink :href="route('myFiles')" :active="$page.url === '/my-files'">My Files</NavLink>
                <NavLink :href="route('file.sharedWithMe')"  :active="$page.url === '/shared-with-me'" >Shared with me</NavLink>
                <NavLink :href="route('file.sharedByMe')"  :active="$page.url === '/shared-by-me'" >Shared by me</NavLink>
                <NavLink :href="route('trash')" :active="$page.url === '/trash'">Trash</NavLink>
            </div>
        </div>
    </nav>
</template>

<script setup>

import { Link } from "@inertiajs/vue3";
import ApplicationLogo from "../ApplicationLogo.vue" ;
import CreateNewDropdown from "./CreateNewDropdown.vue";
import NavLink from "..//NavLink.vue";
</script>

<style scoped>

</style>