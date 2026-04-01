<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    bio: user.bio || '',
});

// Watch untuk reset form ketika user data berubah
watch(
    () => page.props.auth.user,
    (newUser) => {
        if (newUser && !form.isDirty()) {
            form.name = newUser.name || '';
            form.email = newUser.email || '';
            form.phone = newUser.phone || '';
            form.bio = newUser.bio || '';
        }
    },
    { deep: true }
);

const submit = () => {
    form.patch(route('profile.update'), {
        onSuccess: () => {
            form.clearErrors();
            form.reset();
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                Profil Admin
            </h2>

            <p class="text-gray-600 dark:text-gray-300">
                Atur profil admin Anda untuk identifikasi dalam proses review aplikasi
            </p>
        </header>

        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <!-- Nama -->
            <div>
                <InputLabel for="name" value="Nama Admin" class="text-gray-700 dark:text-white"/>

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Nama admin"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Email Admin" class="text-gray-700 dark:text-white"/>

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="admin@email.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- No Telepon -->
            <div>
                <InputLabel for="phone" value="Nomor Telepon" class="text-gray-700 dark:text-white"/>

                <TextInput
                    id="phone"
                    type="tel"
                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                    v-model="form.phone"
                    placeholder="+62 812 3456 7890"
                />

                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <!-- Bio/Departemen -->
            <div>
                <InputLabel for="bio" value="Departemen / Deskripsi Peran" class="text-gray-700 dark:text-white"/>

                <textarea
                    id="bio"
                    v-model="form.bio"
                    placeholder="Contoh: HR Manager - Divisi Rekrutmen"
                    rows="3"
                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors px-3 py-2"
                />

                <InputError class="mt-2" :message="form.errors.bio" />
            </div>

            <!-- Info Badge Admin -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-6">
                <h3 class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">ℹ️ Informasi</h3>
                <p class="text-sm text-gray-700 dark:text-gray-200">
                    Profil admin Anda akan terlihat ketika Anda melakukan review, penerimaan, atau penolakan aplikasi pekerjaan. 
                    Ini membantu pelamar mengetahui admin mana yang menangani aplikasi mereka.
                </p>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <PrimaryButton :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700">
                    Simpan Profil Admin
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600 dark:text-green-400"
                    >
                        ✓ Profil admin berhasil diperbarui
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
