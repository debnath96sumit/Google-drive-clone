<template>
    <Modal :show="modelValue" @show="onShow" max-width="sm">
        <div class="p-4">
            <h2 class="text-lg font-medium text-gray-900">Create New Folder</h2>
            <div class="mt-4">
                <InputLabel for="folder_name" value="Folder Name"/>
                <TextInput 
                    type="text" 
                    ref="folderNameInput"
                    id="folder_name" 
                    v-model="form.name"
                    class="mt-1 block w-full"
                    :class="form.errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                    placeholder="Folder Name"
                    @keyup.enter="createFolder"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" @click="createFolder" :disabled="form.processing">Submit</PrimaryButton>
            </div>
        </div>

    </Modal>
</template>

<script setup>
    import { useForm, usePage } from "@inertiajs/vue3";
    import Modal from "@/Components/Modal.vue";      
    import InputError from '@/Components/InputError.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import InputLabel from "../InputLabel.vue";
    import TextInput from "../TextInput.vue";
    
    import { ref, nextTick } from "vue";
    import { showSuccessNotification } from "@/event-bus";
    const folderNameInput = ref(null);
    const emit = defineEmits(['update: modelValue'])
    const page = usePage();
    const {modelValue} = defineProps({
        modelValue: Boolean
    })
    const form = useForm({
        name: '',
        parent_id: null
    });

    const createFolder = () =>{
        form.parent_id = page.props?.folder?.id;
        form.post(route('folder.create'), {
            preserveScroll: true,
            onSuccess: ()=>{
                closeModal();
                form.reset();
                showSuccessNotification('Folder has been created');
            },
            onError: ()=>{
                folderNameInput.value.focus()
            }
        });
    }

    const closeModal = ()=>{
        emit('update: modelValue', false);
        form.clearErrors();
        form.reset();
    }
    const onShow = () =>{
        nextTick(()=>{
            folderNameInput.value.focus();
        })
    }
</script>

<style scoped></style>