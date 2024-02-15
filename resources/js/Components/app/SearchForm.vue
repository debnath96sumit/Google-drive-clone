<template>
    <div action="" class="w-[600px] h-[80px] flex items-center">

        <TextInput 
            type="text" 
            class="block w-full mr-2" 
            autocomplete
            placeholder="Search for files and folders"
            v-model="search"    
            @keyup.enter.prevent="searchFile"
        />
    </div>
</template>

<script setup>
    import { router, useForm } from "@inertiajs/vue3";
    import TextInput from "..//TextInput.vue";
    import { ref } from "vue";
    import { onMounted } from "vue";
    import { ON_SEARCH, emitter } from "@/event-bus";

    const search = ref('');
    let params = '';
 
    const searchFile = ()=>{
        if (search.value.length > 0) {
            params.set('search', search.value);
            router.get(window.location.pathname + '?' + params.toString());
            emitter.emit('ON_SEARCH', search.value);
        }else{
            // console.log(window.location.pathname);
            router.get(window.location.pathname);
        }
    }

    onMounted(()=>{
        params = new URLSearchParams(window.location.search);
        search.value = params.get('search');

    })


</script>
<style scoped></style>