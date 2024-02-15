<template>
    <Modal :show="props.modelValue" @show="onShow" max-width="sm">
        <div class="p-4">
            <h2 class="text-lg font-medium text-gray-900">Share Files</h2>
            <div class="mt-4">
                <InputLabel for="shared_emails" value="Email addresses"/>
                <TextInput 
                    type="text" 
                    ref="emailInput"
                    id="shared_emails" 
                    v-model="form.email"
                    class="mt-1 block w-full"
                    :class="form.errors.emails ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                    placeholder="Enter email addresses"
                    @keyup.enter="share"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" @click="share" :disabled="form.processing">Submit</PrimaryButton>
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
    const emailInput = ref(null);
    const emit = defineEmits(['update: modelValue'])
    const page = usePage();
    const props = defineProps({
        modelValue: Boolean,
        allSelected: Boolean,
        selectedIds: Array,
    })
    const form = useForm({
        email: '',
        all: false,
        ids: []
    });

    const share = () =>{
        form.parent_id = page.props?.folder?.id;
        if (props.allSelected) {
            form.all = true;
            form.ids = [];
        }else{
            form.ids = props.selectedIds;
        }
        form.post(route('media.share'), {
            preserveScroll: true,
            onSuccess: ()=>{
                closeModal();
                form.reset();
            },
            onError: ()=>{
                emailInput.value.focus()
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
            emailInput.value.focus();
        })
    }
</script>

<style scoped></style>