<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { watch, ref, computed, onBeforeUnmount, onMounted } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = page.props.auth.user;

// Form untuk upload profile photo
const photoForm = useForm({
    profile_photo: null,
});

const photoPreviewUrl = ref(user.profile_photo_path ? `/storage/${user.profile_photo_path}` : null);
const photoInputRef = ref(null);
const photoFileSize = ref(null);
const photoUploadLoading = ref(false);

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    phone: user.phone || '',
    bio: user.bio || '',
    address: user.address || '',
    city: user.city || '',
    province: user.province || '',
    postal_code: user.postal_code || '',
    date_of_birth: user.date_of_birth || '',
    gender: user.gender || '',
    // Education fields with defaults (convert to string for input binding)
    education_level: user.education_level || 'SMA',
    education_institution: user.education_institution || '',
    education_year_graduated: String(user.education_year_graduated || new Date().getFullYear()),
    education_major: user.education_major || '',
    education_grade: user.education_grade || '',
    // Experience (array)
    experiences: user.experiences || [],
    // Skills
    skills: user.skills || '',
    id_number: user.id_number || '',
    emergency_contact_name: user.emergency_contact_name || '',
    emergency_contact_phone: user.emergency_contact_phone || '',
    // Profile photo
    profile_photo_path: user.profile_photo_path || '',
});

// Handle photo file selection
const handlePhotoSelect = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        // Validate file size
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            alert('Ukuran file terlalu besar (maksimal 5MB)');
            return;
        }

        photoForm.profile_photo = file;
        photoFileSize.value = (file.size / 1024 / 1024).toFixed(2);
        
        // Show preview
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreviewUrl.value = e.target?.result;
        };
        reader.readAsDataURL(file);
    }
};

// Upload profile photo
const uploadProfilePhoto = () => {
    if (!photoForm.profile_photo) return;
    
    photoUploadLoading.value = true;
    
    // Use Inertia's router.post with file support
    router.post(route('profile.upload-photo'), 
        { profile_photo: photoForm.profile_photo },
        {
            onSuccess: () => {
                // Clear and reload
                clearPhotoSelection();
                photoUploadLoading.value = false;
            },
            onError: (errors) => {
                photoUploadLoading.value = false;
                console.error('Upload errors:', errors);
                const errorMsg = errors.profile_photo || 'Gagal mengupload foto. Silakan coba lagi.';
                alert(Array.isArray(errorMsg) ? errorMsg[0] : errorMsg);
            }
        }
    );
};

// Clear photo selection
const clearPhotoSelection = () => {
    photoForm.profile_photo = null;
    photoFileSize.value = null;
    photoPreviewUrl.value = user.profile_photo_path ? `/storage/${user.profile_photo_path}` : null;
    if (photoInputRef.value) {
        photoInputRef.value.value = '';
    }
};

// For adding new experience
const newExperience = ref({
    company: '',
    position: '',
    start_year: '',
    end_year: '',
    description: '',
});

const showNewExperienceForm = ref(false);

// Watch untuk reset form ketika user data berubah dari server
// Hanya watch user.id untuk avoid deep reactive issues
watch(
    () => page.props.auth.user?.id,
    (newUserId) => {
        // Only sync form ketika user berubah, form tidak sedang di-edit, dan component masih mounted
        if (newUserId && !form.isDirty && !isEditMode.value && isMounted.value) {
            try {
                const userData = page.props.auth.user;
                if (userData && isMounted.value) {
                    form.name = userData.name || '';
                    form.email = userData.email || '';
                    form.phone = userData.phone || '';
                    form.bio = userData.bio || '';
                    form.address = userData.address || '';
                    form.city = userData.city || '';
                    form.province = userData.province || '';
                    form.postal_code = userData.postal_code || '';
                    form.date_of_birth = userData.date_of_birth || '';
                    form.gender = userData.gender || '';
                    form.education_level = userData.education_level || 'SMA';
                    form.education_institution = userData.education_institution || '';
                    form.education_year_graduated = String(userData.education_year_graduated || new Date().getFullYear());
                    form.education_major = userData.education_major || '';
                    form.education_grade = userData.education_grade || '';
                    form.experiences = userData.experiences || [];
                    form.skills = userData.skills || '';
                    form.id_number = userData.id_number || '';
                    form.emergency_contact_name = userData.emergency_contact_name || '';
                    form.emergency_contact_phone = userData.emergency_contact_phone || '';
                }
            } catch (error) {
                // Ignore errors during component swap/unmount
                console.debug('Form sync skipped during component lifecycle');
            }
        }
    }
);

const addExperience = () => {
    if (newExperience.value.company && newExperience.value.position) {
        if (!Array.isArray(form.experiences)) {
            form.experiences = [];
        }
        form.experiences.push({...newExperience.value});
        newExperience.value = {
            company: '',
            position: '',
            start_year: '',
            end_year: '',
            description: '',
        };
        showNewExperienceForm.value = false;
    }
};

const removeExperience = (index) => {
    if (Array.isArray(form.experiences)) {
        form.experiences.splice(index, 1);
    }
};

// Mode Edit - Toggle between read-only and edit mode
const isEditMode = ref(false);
const showSuccessMessage = ref(false);
const isMounted = ref(true);
let closeTimer;
let hideTimer;

// Track component lifecycle
onMounted(() => {
    isMounted.value = true;
});

// Cleanup timers on component unmount
onBeforeUnmount(() => {
    isMounted.value = false;
    if (closeTimer) clearTimeout(closeTimer);
    if (hideTimer) clearTimeout(hideTimer);
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

// Show success message when recently successful
watch(
    () => form.recentlySuccessful,
    (success) => {
        if (success && isMounted.value) {
            try {
                // Show success message
                if (isMounted.value) {
                    showSuccessMessage.value = true;
                }
                
                // Close edit mode setelah 500ms
                closeTimer = setTimeout(() => {
                    try {
                        // Only update if component is still mounted
                        if (isMounted.value) {
                            isEditMode.value = false;
                        }
                    } catch (err) {
                        console.debug('Error closing edit mode:', err);
                    }
                }, 500);
                
                // Hide success message setelah 2500ms
                hideTimer = setTimeout(() => {
                    try {
                        // Only update if component is still mounted
                        if (isMounted.value) {
                            showSuccessMessage.value = false;
                        }
                    } catch (err) {
                        console.debug('Error hiding success message:', err);
                    }
                }, 2500);
            } catch (error) {
                console.debug('Success message handler error:', error);
            }
        }
    }
);

const cancelEdit = () => {
    isEditMode.value = false;
    // Reset form ke nilai user terbaru
    form.name = user.name || '';
    form.email = user.email || '';
    form.phone = user.phone || '';
    form.bio = user.bio || '';
    form.address = user.address || '';
    form.city = user.city || '';
    form.province = user.province || '';
    form.postal_code = user.postal_code || '';
    form.date_of_birth = user.date_of_birth || '';
    form.gender = user.gender || '';
    form.education_level = user.education_level || 'SMA';
    form.education_institution = user.education_institution || '';
    form.education_year_graduated = String(user.education_year_graduated || new Date().getFullYear());
    form.education_major = user.education_major || '';
    form.education_grade = user.education_grade || '';
    form.experiences = user.experiences || [];
    form.skills = user.skills || '';
    form.id_number = user.id_number || '';
    form.emergency_contact_name = user.emergency_contact_name || '';
    form.emergency_contact_phone = user.emergency_contact_phone || '';
    form.profile_photo_path = user.profile_photo_path || '';
};

// Format currency untuk display
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};

const getGenderLabel = (value) => {
    const genders = { male: 'Laki-laki', female: 'Perempuan', other: 'Lainnya' };
    return genders[value] || '-';
};
</script>

<template>
    <section>
        <header class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white mb-2">
                    Detail Informasi Pribadi Lengkap
                </h2>
                <p class="text-slate-400">
                    {{ isEditMode ? 'Edit informasi pribadi Anda' : 'Lihat informasi pribadi Anda' }}
                </p>
            </div>
            <button
                v-if="!isEditMode"
                type="button"
                @click="isEditMode = true"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2"
            >
                ✏️ Edit Profil
            </button>
        </header>

        <!-- Success Message Toast -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-2"
            leave-active-class="transition ease-in duration-200"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="showSuccessMessage"
                class="mt-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-lg flex items-center gap-3"
            >
                <span class="text-xl">✓</span>
                <span class="text-green-800 dark:text-green-200 font-medium">Perubahan berhasil disimpan!</span>
            </div>
        </Transition>

        <!-- PROFILE PHOTO SECTION -->
        <div class="mt-8 flex flex-col md:flex-row gap-8 items-stretch">
            <!-- Photo Preview -->
            <div class="flex-shrink-0 w-full md:w-auto md:flex-1">
                <div class="bg-gradient-to-br from-blue-50 dark:from-blue-900/20 to-purple-50 dark:to-purple-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 h-full flex flex-col items-center justify-center">
                    <div class="relative w-56 h-56 bg-gray-100 dark:bg-slate-700 rounded-lg flex items-center justify-center overflow-hidden border-4 border-dashed border-blue-300 dark:border-blue-600 shadow-md">
                        <img v-if="photoPreviewUrl" :src="photoPreviewUrl" alt="Profile Photo" class="w-full h-full object-cover">
                        <div v-else class="text-center">
                            <p class="text-6xl">👤</p>
                            <p class="text-gray-500 dark:text-gray-400 text-xs mt-2">Belum ada foto</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-6 text-center leading-relaxed px-2">
                        <span class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Rekomendasi:</span>
                        Foto berkualitas tinggi<br>
                        Aspek rasio 1:1 (persegi)<br>
                        Min 100x100 px, Maks 5MB
                    </p>
                </div>
            </div>

            <!-- Photo Upload Form -->
            <div class="flex-grow w-full md:flex-grow-0 md:flex-1">
                <div class="bg-gradient-to-br from-blue-50 dark:from-blue-900/20 to-purple-50 dark:to-purple-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 h-full flex flex-col">
                    <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4 flex items-center gap-2">
                        <span class="text-2xl">🖼️</span> Foto Profil
                    </h3>
                    
                    <div v-if="!photoForm.profile_photo" class="space-y-4 flex-grow flex flex-col">
                        <label 
                            for="profile_photo_input"
                            class="flex-grow flex flex-col items-center justify-center p-8 border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-lg text-center cursor-pointer hover:border-blue-500 dark:hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200"
                        >
                            <p class="text-4xl mb-3">📸</p>
                            <p class="text-blue-600 dark:text-blue-400 font-semibold text-lg">Pilih Foto</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Atau drag & drop di sini</p>
                            <input
                                ref="photoInputRef"
                                id="profile_photo_input"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="handlePhotoSelect"
                            />
                        </label>
                        <p class="text-gray-600 dark:text-gray-300 text-xs leading-relaxed">
                            💡 <span class="font-medium">Tips:</span> Gunakan foto wajah yang jelas dan terang. Hindari filter yang berlebihan. Foto akan di-crop menjadi kotak otomatis.
                        </p>
                    </div>

                    <div v-else class="space-y-4 flex-grow flex flex-col">
                        <div class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-green-300 dark:border-green-700 flex-grow">
                            <p class="text-green-700 dark:text-green-300 font-semibold mb-2 flex items-center gap-2">
                                <span class="text-lg">✓</span> Foto siap upload
                            </p>
                            <div class="space-y-1 text-sm">
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">File:</span> {{ photoForm.profile_photo.name }}
                                </p>
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-medium">Ukuran:</span> {{ photoFileSize }} MB
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="uploadProfilePhoto"
                                :disabled="photoUploadLoading"
                                class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-green-500 disabled:cursor-not-allowed dark:bg-green-600 dark:hover:bg-green-700 text-white rounded-lg transition-colors font-semibold flex items-center justify-center gap-2"
                            >
                                <span v-if="!photoUploadLoading">✓ Upload</span>
                                <span v-else class="flex items-center gap-2">
                                    <span class="inline-block animate-spin">⏳</span> Uploading...
                                </span>
                            </button>
                            <button
                                type="button"
                                @click="clearPhotoSelection"
                                :disabled="photoUploadLoading"
                                class="flex-1 px-4 py-3 bg-gray-400 hover:bg-gray-500 disabled:bg-gray-400 disabled:cursor-not-allowed dark:bg-slate-600 dark:hover:bg-slate-700 text-white rounded-lg transition-colors font-semibold"
                            >
                                ✕ Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODE TAMPILAN (READ-ONLY) -->
        <div v-if="!isEditMode" class="mt-8 space-y-6">
            <!-- SECTION 0: Foto Profil -->
            <div class="flex justify-center">
                <div class="bg-gradient-to-br from-blue-50 dark:from-blue-900/20 to-purple-50 dark:to-purple-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-8">
                    <div class="w-64 h-64 bg-gray-200 dark:bg-slate-700 rounded-lg flex items-center justify-center overflow-hidden border-4 border-blue-300 dark:border-blue-600">
                        <img v-if="form.profile_photo_path" :src="`/storage/${form.profile_photo_path}`" alt="Foto Profil" class="w-full h-full object-cover">
                        <div v-else class="text-center">
                            <p class="text-6xl">👤</p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-4">Tidak ada foto profil</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: Informasi Dasar -->
            <div class="bg-gradient-to-r from-blue-50 dark:from-blue-900/20 to-purple-50 dark:to-purple-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">📋 Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Nama Lengkap</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Email</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.email || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Kontak & Alamat -->
            <div class="bg-gradient-to-r from-green-50 dark:from-green-900/20 to-emerald-50 dark:to-emerald-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-green-600 dark:text-green-400 mb-4">📍 Kontak & Alamat</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Telepon/WhatsApp</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.phone || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Kota/Kabupaten</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.city || '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Alamat Lengkap</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.address || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Provinsi</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.province || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Kode Pos</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.postal_code || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Data Diri -->
            <div class="bg-gradient-to-r from-purple-50 dark:from-purple-900/20 to-pink-50 dark:to-pink-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-purple-600 dark:text-purple-400 mb-4">👤 Data Diri</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tanggal Lahir</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ formatDate(form.date_of_birth) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Jenis Kelamin</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ getGenderLabel(form.gender) }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Nomor Identitas (KTP/Paspor)</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.id_number || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Pendidikan -->
            <div class="bg-gradient-to-r from-yellow-50 dark:from-yellow-900/20 to-orange-50 dark:to-orange-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-yellow-600 dark:text-yellow-400 mb-4">🎓 Pendidikan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Jenjang Pendidikan</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.education_level || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Sekolah/Universitas</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.education_institution || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Program Studi/Jurusan</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.education_major || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tahun Lulus</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.education_year_graduated || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Nilai/Grade (GPA/IPK)</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.education_grade || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Pengalaman Kerja -->
            <div class="bg-gradient-to-r from-red-50 dark:from-red-900/20 to-rose-50 dark:to-rose-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">💼 Pengalaman Kerja</h3>
                <div v-if="form.experiences && form.experiences.length > 0" class="space-y-3">
                    <div v-for="(exp, idx) in form.experiences" :key="idx" class="bg-white dark:bg-slate-800 rounded-lg p-4 border border-gray-200 dark:border-slate-700">
                        <h4 class="text-gray-900 dark:text-white font-semibold">{{ exp.position }}</h4>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">{{ exp.company }}</p>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">{{ exp.start_year }}{{ exp.end_year ? ' - ' + exp.end_year : ' (Sekarang)' }}</p>
                        <p v-if="exp.description" class="text-gray-700 dark:text-gray-200 text-sm mt-2">{{ exp.description }}</p>
                    </div>
                </div>
                <p v-else class="text-gray-600 dark:text-gray-300">Belum ada pengalaman kerja</p>
            </div>

            <!-- SECTION 6: Skills -->
            <div class="bg-gradient-to-r from-cyan-50 dark:from-cyan-900/20 to-blue-50 dark:to-blue-900/20 border border-cyan-200 dark:border-cyan-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-cyan-600 dark:text-cyan-400 mb-4">💡 Keahlian/Skill</h3>
                <p class="text-gray-900 dark:text-white font-semibold">{{ form.skills || '-' }}</p>
            </div>

            <!-- SECTION 7: Kontak Darurat -->
            <div class="bg-gradient-to-r from-indigo-50 dark:from-indigo-900/20 to-violet-50 dark:to-violet-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-indigo-600 dark:text-indigo-400 mb-4">🚨 Kontak Darurat</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Nama Kontak</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.emergency_contact_name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Nomor Telepon</p>
                        <p class="text-gray-900 dark:text-white font-semibold">{{ form.emergency_contact_phone || '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- SECTION 8: Bio -->
            <div class="bg-gradient-to-r from-teal-50 dark:from-teal-900/20 to-green-50 dark:to-green-900/20 border border-teal-200 dark:border-teal-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-teal-600 dark:text-teal-400 mb-4">✍️ Tentang Diri (Bio)</h3>
                <p class="text-gray-900 dark:text-white font-semibold">{{ form.bio || '-' }}</p>
            </div>
        </div>

        <!-- MODE EDIT (FORM) -->
        <form
            v-if="isEditMode"
            @submit.prevent="submit"
            class="mt-6 space-y-8"
        >
            <!-- SECTION 1: Informasi Dasar -->
            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">📋 Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama -->
                    <div>
                        <InputLabel for="name" value="Nama Lengkap *" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.name"
                            required
                            placeholder="Nama lengkap Anda"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email *" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.email"
                            required
                            placeholder="alamat@email.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Kontak & Alamat -->
            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">📍 Kontak & Alamat</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Telepon -->
                    <div>
                        <InputLabel for="phone" value="Nomor Telepon/WhatsApp" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="phone"
                            type="tel"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.phone"
                            placeholder="+62 812 3456 7890"
                        />
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>

                    <!-- Alamat -->
                    <div>
                        <InputLabel for="address" value="Alamat Lengkap" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="address"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.address"
                            placeholder="Jln. Contoh No. 123"
                        />
                        <InputError class="mt-2" :message="form.errors.address" />
                    </div>

                    <!-- Kota -->
                    <div>
                        <InputLabel for="city" value="Kota/Kabupaten" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="city"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.city"
                            placeholder="Contoh: Jakarta Selatan"
                        />
                        <InputError class="mt-2" :message="form.errors.city" />
                    </div>

                    <!-- Provinsi -->
                    <div>
                        <InputLabel for="province" value="Provinsi" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="province"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.province"
                            placeholder="Contoh: DKI Jakarta"
                        />
                        <InputError class="mt-2" :message="form.errors.province" />
                    </div>

                    <!-- Kode Pos -->
                    <div>
                        <InputLabel for="postal_code" value="Kode Pos" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="postal_code"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.postal_code"
                            placeholder="Contoh: 12345"
                        />
                        <InputError class="mt-2" :message="form.errors.postal_code" />
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Data Diri -->
            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">👤 Data Diri</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tanggal Lahir -->
                    <div>
                        <InputLabel for="date_of_birth" value="Tanggal Lahir" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="date_of_birth"
                            type="date"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.date_of_birth"
                        />
                        <InputError class="mt-2" :message="form.errors.date_of_birth" />
                    </div>

                    <!-- Gender -->
                    <div>
                        <InputLabel for="gender" value="Jenis Kelamin" class="text-gray-700 dark:text-white"/>
                        <select
                            id="gender"
                            v-model="form.gender"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 px-3 py-2 transition-colors"
                        >
                            <option value="">-- Pilih --</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                            <option value="other">Lainnya</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.gender" />
                    </div>

                    <!-- Nomor Identitas -->
                    <div class="md:col-span-2">
                        <InputLabel for="id_number" value="Nomor Identitas (KTP/Paspor)" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="id_number"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.id_number"
                            placeholder="Nomor KTP atau Paspor"
                        />
                        <InputError class="mt-2" :message="form.errors.id_number" />
                    </div>
                </div>
            </div>

            <!-- SECTION 4: Pendidikan -->
            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">🎓 Pendidikan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Jenjang Pendidikan -->
                    <div>
                        <InputLabel for="education_level" value="Jenjang Pendidikan" class="text-gray-700 dark:text-white"/>
                        <select
                            id="education_level"
                            v-model="form.education_level"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 px-3 py-2 transition-colors"
                        >
                            <option value="">-- Pilih --</option>
                            <option value="SMA">SMA / Sederajat</option>
                            <option value="D3">Diploma (D3)</option>
                            <option value="S1">Sarjana (S1)</option>
                            <option value="S2">Magister (S2)</option>
                            <option value="S3">Doktor (S3)</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.education_level" />
                    </div>

                    <!-- Institusi Pendidikan -->
                    <div>
                        <InputLabel for="education_institution" value="Sekolah/Universitas" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="education_institution"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.education_institution"
                            placeholder="Contoh: Universitas Indonesia"
                        />
                        <InputError class="mt-2" :message="form.errors.education_institution" />
                    </div>

                    <!-- Program Studi/Jurusan -->
                    <div>
                        <InputLabel for="education_major" value="Program Studi/Jurusan" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="education_major"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.education_major"
                            placeholder="Contoh: Teknik Informatika"
                        />
                        <InputError class="mt-2" :message="form.errors.education_major" />
                    </div>

                    <!-- Tahun Lulus -->
                    <div>
                        <InputLabel for="education_year_graduated" value="Tahun Lulus" class="text-gray-700 dark:text-white"/>
                        <input
                            id="education_year_graduated"
                            type="text"
                            inputmode="numeric"
                            v-model="form.education_year_graduated"
                            placeholder="Contoh: 2022"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors px-3 py-2"
                        />
                        <InputError class="mt-2" :message="form.errors.education_year_graduated" />
                    </div>

                    <!-- Nilai/Grade -->
                    <div>
                        <InputLabel for="education_grade" value="Nilai/Grade (GPA/IPK)" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="education_grade"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.education_grade"
                            placeholder="Contoh: 3.75 atau 78/100"
                        />
                        <InputError class="mt-2" :message="form.errors.education_grade" />
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Pengalaman Kerja -->
            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400">💼 Pengalaman Kerja</h3>
                    <button
                        v-if="!showNewExperienceForm"
                        type="button"
                        @click="showNewExperienceForm = true"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors"
                    >
                        ➕ Tambah Pengalaman
                    </button>
                </div>

                <!-- Experirence Cards List -->
                <div v-if="form.experiences && form.experiences.length > 0" class="mb-6 space-y-4">
                    <div
                        v-for="(exp, index) in form.experiences"
                        :key="index"
                        class="bg-gradient-to-r from-blue-500/10 dark:from-blue-500/5 to-purple-500/10 dark:to-purple-500/5 border border-blue-500/30 dark:border-blue-500/20 rounded-lg p-4"
                    >
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="text-gray-900 dark:text-white font-semibold">{{ exp.position }}</h4>
                                <p class="text-gray-600 dark:text-gray-300 text-sm">{{ exp.company }}</p>
                                <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">
                                    {{ exp.start_year }}{{ exp.end_year ? ' - ' + exp.end_year : ' (Sekarang)' }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="removeExperience(index)"
                                class="ml-4 px-3 py-1 bg-red-500/20 dark:bg-red-600/20 hover:bg-red-500/40 dark:hover:bg-red-600/40 text-red-600 dark:text-red-400 text-sm rounded transition-colors"
                            >
                                ✕ Hapus
                            </button>
                        </div>
                        <p v-if="exp.description" class="text-gray-700 dark:text-gray-200 text-sm">{{ exp.description }}</p>
                    </div>
                </div>

                <!-- Form Tambah Pengalaman Baru -->
                <div v-if="showNewExperienceForm" class="bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg p-6 mb-6">
                    <h4 class="text-gray-900 dark:text-white font-semibold mb-4">Tambah Pengalaman Kerja Baru</h4>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Perusahaan -->
                            <div>
                                <InputLabel value="Nama Perusahaan *" class="text-gray-700 dark:text-white"/>
                                <TextInput
                                    type="text"
                                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                                    v-model="newExperience.company"
                                    placeholder="Contoh: PT Maju Jaya"
                                />
                            </div>

                            <!-- Posisi -->
                            <div>
                                <InputLabel value="Posisi/Jabatan *" class="text-gray-700 dark:text-white"/>
                                <TextInput
                                    type="text"
                                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                                    v-model="newExperience.position"
                                    placeholder="Contoh: Frontend Developer"
                                />
                            </div>

                            <!-- Tahun Mulai -->
                            <div>
                                <InputLabel value="Tahun Mulai" class="text-gray-700 dark:text-white"/>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    v-model="newExperience.start_year"
                                    placeholder="Contoh: 2020"
                                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors px-3 py-2"
                                />
                            </div>

                            <!-- Tahun Selesai -->
                            <div>
                                <InputLabel value="Tahun Selesai (Kosongkan jika masih bekerja)" class="text-gray-700 dark:text-white"/>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    v-model="newExperience.end_year"
                                    placeholder="Contoh: 2023"
                                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors px-3 py-2"
                                />
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <InputLabel value="Deskripsi Pekerjaan" class="text-gray-700 dark:text-white"/>
                            <textarea
                                v-model="newExperience.description"
                                placeholder="Tuliskan tanggung jawab dan pencapaian Anda di posisi ini..."
                                rows="3"
                                class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors px-3 py-2"
                            />
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="addExperience"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 text-white rounded-lg transition-colors"
                            >
                                ✓ Tambahkan
                            </button>
                            <button
                                type="button"
                                @click="showNewExperienceForm = false"
                                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white rounded-lg transition-colors"
                            >
                                ✕ Batal
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="!form.experiences || form.experiences.length === 0" class="bg-gray-100 dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg p-6 text-center">
                    <p class="text-gray-600 dark:text-gray-300">Belum ada pengalaman kerja</p>
                    <button
                        v-if="!showNewExperienceForm"
                        type="button"
                        @click="showNewExperienceForm = true"
                        class="mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700 text-white rounded-lg transition-colors"
                    >
                        ➕ Tambah Pengalaman Pertama
                    </button>
                </div>
            </div>

            <!-- SECTION 6: Skills -->
            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">💡 Keahlian/Skill</h3>
                <textarea
                    id="skills"
                    v-model="form.skills"
                    placeholder="Tuliskan keahlian Anda (pisahkan dengan koma), contoh: Java, Python, Leadership, Komunikasi"
                    rows="3"
                    class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors px-3 py-2"
                />
                <InputError class="mt-2" :message="form.errors.skills" />
            </div>

            <!-- SECTION 7: Kontak Darurat -->
            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">🚨 Kontak Darurat</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Kontak Darurat -->
                    <div>
                        <InputLabel for="emergency_contact_name" value="Nama Kontak" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="emergency_contact_name"
                            type="text"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.emergency_contact_name"
                            placeholder="Nama lengkap orang yang dapat dihubungi"
                        />
                        <InputError class="mt-2" :message="form.errors.emergency_contact_name" />
                    </div>

                    <!-- Nomor Kontak Darurat -->
                    <div>
                        <InputLabel for="emergency_contact_phone" value="Nomor Telepon" class="text-gray-700 dark:text-white"/>
                        <TextInput
                            id="emergency_contact_phone"
                            type="tel"
                            class="mt-2 block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors"
                            v-model="form.emergency_contact_phone"
                            placeholder="+62 812 3456 7890"
                        />
                        <InputError class="mt-2" :message="form.errors.emergency_contact_phone" />
                    </div>
                </div>
            </div>

            <!-- SECTION 8: Bio -->
            <div class="border-t border-gray-200 dark:border-white/10 pt-6">
                <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-400 mb-4">✍️ Tentang Diri (Bio)</h3>
                <textarea
                    id="bio"
                    v-model="form.bio"
                    placeholder="Tuliskan tentang diri Anda, motivasi, atau pesan khusus untuk recruiter..."
                    rows="4"
                    class="block w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 transition-colors px-3 py-2"
                />
                <InputError class="mt-2" :message="form.errors.bio" />
            </div>

            <!-- Submit Button -->
            <div class="flex items-center gap-4 pt-6 border-t border-gray-200 dark:border-white/10">
                <PrimaryButton :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700">
                    💾 Simpan Perubahan
                </PrimaryButton>

                <button
                    type="button"
                    @click="cancelEdit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    ✕ Batal
                </button>
            </div>
        </form>

        <!-- Info Helper -->
        <div class="mt-8 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-green-700 dark:text-green-300 mb-2">✨ Keuntungan Profil Lengkap</h4>
            <ul class="text-sm text-gray-700 dark:text-gray-200 space-y-1 list-disc list-inside">
                <li>Admin dapat melihat semua informasi Anda tanpa perlu bertanya lagi</li>
                <li>Proses aplikasi lebih cepat dan efisien</li>
                <li>Peluang diterima lebih besar dengan data lengkap</li>
                <li>Data Anda aman dan hanya dilihat oleh tim recruiting</li>
            </ul>
        </div>
    </section>
</template>
