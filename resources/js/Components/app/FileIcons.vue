<template>
    <span class="w-6 h-6 inline-flex items-center justify-center mr-2">
        <svg v-if="file.is_folder" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
        </svg>
        <template v-else>
            <img class="max-w-full" v-if="isImage(file)" src="/images/icons/image.png"/>
            <img class="max-w-full" v-if="isAudio(file)" src="/images/icons/musical-notes.png"/>
            <img class="max-w-full" v-if="isExcel(file)" src="/images/icons/excel-file.png"/>
            <img class="max-w-full" v-if="isPDF(file)" src="/images/icons/pdf.png"/>
            <img class="max-w-full" v-if="isText(file)" src="/images/icons/word.png"/>
            <img class="max-w-full" v-if="isVideo(file)" src="/images/icons/play.png"/>
            <img class="max-w-full" v-if="isZip(file)" src="/images/icons/zip-folder.png"/>
            <img class="max-w-full" v-if="isWord(file)" src="/images/icons/word.png"/>
        </template>
    </span>
</template>

<script setup>
import {isAudio, isExcel, isImage, isPDF, isText, isVideo, isWord, isZip} from '@/Helper/File-helper';


const {file} = defineProps({
    file: Object
})
</script>

<style scoped></style>