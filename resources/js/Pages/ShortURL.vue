<script setup>
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

import { useForm } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";

const shortURL = ref("")

const form = useForm({
    url: "",
});

const submit = () => {
    axios
        .post("/url-shortner", form)
        .then((res) => res.data)
        .then((res) => shortURL.value = res.data);
};
</script>

<template>
    <form @submit.prevent="submit">
        <div>
            <InputLabel for="url" value="URL" />
            <TextInput id="url" v-model="form.url" required autofocus />
        </div>
        <PrimaryButton>Submit</PrimaryButton>
        <p>
            Short URL:<a v-if="true" :href="`/url?short-url=${shortURL}`" target="_blank">{{ shortURL }}</a>
        </p>
    </form>
</template>
