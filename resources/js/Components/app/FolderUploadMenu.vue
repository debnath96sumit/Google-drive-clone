<template>
    <MenuItem v-slot="{ active }">
        <a href="#" class="text-gray-700 block px-4 text-sm relative">
            Upload Folder
            <input 
                type="file"
                @change="onChange($event)" 
                name="" 
                id="" 
                class="absolute left-0 top-0 bottom-0 right-0 cursor-pointer opacity-0" 
                directory
                webkitdirectory
            >
        </a>
    </MenuItem>
</template>

<script setup>
    import { FILE_UPLOAD_STARTED, emitter } from '@/event-bus';
    import {MenuItem } from '@headlessui/vue'
    
    const onChange = (ev)=>{
        emitter.emit(FILE_UPLOAD_STARTED, ev.target.files);
    }
</script>

<style scoped></style>