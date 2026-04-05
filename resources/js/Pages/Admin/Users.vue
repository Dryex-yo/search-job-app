<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';
import { useNotification } from '@/Composables/useNotification';

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            total: 0,
            per_page: 15,
            last_page: 1
        })
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            role: ''
        })
    }
});

// State
const searchQuery = ref(props.filters.search);
const filterRole = ref(props.filters.role);
const selectedUser = ref(null);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showPasswordModal = ref(false);
const isLoading = ref(false);

// Form states
const editForm = ref({
    name: '',
    email: '',
    phone: '',
    role: 'user',
    address: '',
    city: '',
    province: '',
    postal_code: '',
    date_of_birth: '',
    gender: '',
    bio: '',
});

const passwordForm = ref({
    password: '',
    password_confirmation: '',
});

// Notifications
const { success: showSuccess, error: showError } = useNotification();

// Search and filter
const handleSearch = () => {
    router.get(route('admin.users.index'), {
        search: searchQuery.value,
        role: filterRole.value,
        per_page: perPageUsers.value,
        page: 1
    });
};

// Per page handling
const perPageUsers = ref(props.pagination?.per_page || 15);

const changePerPage = () => {
    router.get(route('admin.users.index'), {
        search: searchQuery.value,
        role: filterRole.value,
        per_page: perPageUsers.value,
        page: 1
    });
};

// Pagination computed properties
const currentPage = computed(() => props.pagination?.current_page || 1);
const perPage = computed(() => props.pagination?.per_page || 15);
const totalUsers = computed(() => props.pagination?.total || 0);
const lastPage = computed(() => props.pagination?.last_page || 1);

const usersList = computed(() => {
    // If users is an array directly
    if (Array.isArray(props.users)) {
        return props.users;
    }
    // If users is paginated object with data property
    return props.users?.data || [];
});

const pageNumbers = computed(() => {
    const pages = [];
    const maxPages = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxPages / 2));
    let end = Math.min(lastPage.value, start + maxPages - 1);
    
    if (end - start < maxPages - 1) {
        start = Math.max(1, end - maxPages + 1);
    }
    
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

const startRecord = computed(() => {
    return (currentPage.value - 1) * perPage.value + 1;
});

const endRecord = computed(() => {
    return Math.min(currentPage.value * perPage.value, totalUsers.value);
});

// Pagination navigation
const goToPage = (page) => {
    if (page < 1 || page > lastPage.value) return;
    
    router.visit(route('admin.users.index', { 
        page,
        search: searchQuery.value,
        role: filterRole.value,
        per_page: perPageUsers.value
    }));
};

const nextPage = () => {
    if (currentPage.value < lastPage.value) {
        goToPage(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        goToPage(currentPage.value - 1);
    }
};

// Open edit modal
const openEditModal = (user) => {
    selectedUser.value = user;
    editForm.value = {
        name: user.name,
        email: user.email,
        phone: user.phone === 'N/A' ? '' : user.phone,
        role: user.role,
        address: '',
        city: '',
        province: '',
        postal_code: '',
        date_of_birth: '',
        gender: '',
        bio: '',
    };
    showEditModal.value = true;
};

// Submit edit form
const submitEditForm = async () => {
    isLoading.value = true;
    
    router.patch(route('admin.users.update', selectedUser.value.id), editForm.value, {
        onSuccess: () => {
            showEditModal.value = false;
            showSuccess('User berhasil diperbarui!');
            isLoading.value = false;
        },
        onError: () => {
            showError('Gagal memperbarui user!');
            isLoading.value = false;
        }
    });
};

// Open delete modal
const openDeleteModal = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

// Delete user
const deleteUser = () => {
    isLoading.value = true;
    
    router.delete(route('admin.users.destroy', selectedUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            showSuccess('User berhasil dihapus!');
            isLoading.value = false;
        },
        onError: () => {
            showError('Gagal menghapus user!');
            isLoading.value = false;
        }
    });
};

// Open password modal
const openPasswordModal = (user) => {
    selectedUser.value = user;
    passwordForm.value = {
        password: '',
        password_confirmation: '',
    };
    showPasswordModal.value = true;
};

// Update password
const updatePassword = () => {
    if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
        showError('Password tidak cocok!');
        return;
    }

    if (passwordForm.value.password.length < 8) {
        showError('Password harus minimal 8 karakter!');
        return;
    }

    isLoading.value = true;
    
    router.post(route('admin.users.update-password', selectedUser.value.id), passwordForm.value, {
        onSuccess: () => {
            showPasswordModal.value = false;
            showSuccess('Password berhasil diubah!');
            isLoading.value = false;
        },
        onError: () => {
            showError('Gagal mengubah password!');
            isLoading.value = false;
        }
    });
};

// Get role badge color
const getRoleColor = (role) => {
    switch (role) {
        case 'admin': return 'bg-red-500/10 text-red-400 border-red-500/20';
        case 'recruiter': return 'bg-purple-500/10 text-purple-400 border-purple-500/20';
        case 'user': return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        default: return 'bg-gray-500/10 text-gray-400 border-gray-500/20';
    }
};

// Get role label
const getRoleLabel = (role) => {
    const labels = {
        'admin': 'Administrator',
        'recruiter': 'Recruiter',
        'user': 'Job Seeker'
    };
    return labels[role] || role;
};
</script>

<template>
    <Head title="Dryex Admin - User Management" />

    <AdminPageLayout title="User Management 👤" subtitle="Kelola semua user di platform dengan kontrol penuh">
        <!-- Search and Filter -->
        <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div class="col-span-1 md:col-span-2">
                <input
                    v-model="searchQuery"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="Cari berdasarkan nama atau email..."
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.05] transition-all"
                />
            </div>
            
            <!-- Filter by Role -->
            <div class="col-span-1">
                <select
                    v-model="filterRole"
                    @change="handleSearch"
                    class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.05] transition-all"
                >
                    <option value="" class="bg-gray-900 text-white">Semua Role</option>
                    <option value="admin" class="bg-gray-900 text-white">Administrator</option>
                    <option value="recruiter" class="bg-gray-900 text-white">Recruiter</option>
                    <option value="user" class="bg-gray-900 text-white">Job Seeker</option>
                </select>
            </div>
        </div>

        <!-- Results Count & Per Page -->
        <div class="mb-4 flex items-center justify-between flex-wrap gap-4">
            <div class="text-sm text-gray-400">
                Tampilkan <span class="font-bold text-cyan-400">{{ startRecord }}-{{ endRecord }}</span> dari <span class="font-bold text-cyan-400">{{ totalUsers }}</span> user
                <span class="text-gray-500 ml-2">(Halaman {{ currentPage }} dari {{ lastPage }})</span>
            </div>
            
            <!-- Per Page Selector -->
            <div class="flex items-center gap-3">
                <label class="text-sm text-gray-400">Items per halaman:</label>
                <select
                    v-model="perPageUsers"
                    @change="changePerPage"
                    class="px-3 py-2 bg-white/10 border border-white/20 rounded-lg text-white text-sm focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.05] transition-all"
                >
                    <option value="15" class="bg-gray-900">15</option>
                    <option value="25" class="bg-gray-900">25</option>
                    <option value="50" class="bg-gray-900">50</option>
                    <option value="100" class="bg-gray-900">100</option>
                </select>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] overflow-hidden shadow-inner mb-8">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/[0.02]">
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="user in usersList"
                            :key="user.id"
                            class="border-b border-white/5 hover:bg-white/[0.02] transition-colors"
                        >
                            <!-- Name -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm font-bold text-white">{{ user.name }}</p>
                            </td>

                            <!-- Email -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-300">{{ user.email }}</p>
                            </td>

                            <!-- Phone -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-400">{{ user.phone }}</p>
                            </td>

                            <!-- Role -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-3 py-1 rounded-full text-xs font-bold border', getRoleColor(user.role)]">
                                    {{ getRoleLabel(user.role) }}
                                </span>
                            </td>

                            <!-- Joined Date -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="text-sm text-gray-400">{{ user.created_at }}</p>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="openEditModal(user)"
                                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-blue-50 rounded-lg text-xs font-bold transition-colors"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="openPasswordModal(user)"
                                        class="px-3 py-1 bg-orange-600 hover:bg-orange-700 text-orange-50 rounded-lg text-xs font-bold transition-colors"
                                    >
                                        🔐
                                    </button>
                                    <button
                                        @click="openDeleteModal(user)"
                                        class="px-3 py-1 bg-red-600 hover:bg-red-700 text-red-50 rounded-lg text-xs font-bold transition-colors"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="usersList.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-gray-400 font-medium">Tidak ada user ditemukan</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div v-if="lastPage > 1" class="flex items-center justify-center gap-1 mb-8 flex-wrap">
            <!-- Previous Button -->
            <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 disabled:opacity-50 disabled:cursor-not-allowed border border-white/20 rounded-lg text-white font-semibold transition-colors"
            >
                ← Previous
            </button>

            <!-- First Page (if not visible in range) -->
            <button
                v-if="pageNumbers[0] > 1"
                @click="goToPage(1)"
                :class="[
                    'px-3 py-2 rounded-lg font-semibold transition-colors',
                    currentPage === 1
                        ? 'bg-cyan-600 text-white'
                        : 'bg-white/10 hover:bg-white/20 text-gray-300 border border-white/20'
                ]"
            >
                1
            </button>

            <!-- Ellipsis (left) -->
            <span v-if="pageNumbers[0] > 2" class="px-2 text-gray-400">...</span>

            <!-- Page Numbers -->
            <button
                v-for="page in pageNumbers"
                :key="page"
                @click="goToPage(page)"
                :class="[
                    'px-3 py-2 rounded-lg font-semibold transition-colors',
                    page === currentPage
                        ? 'bg-cyan-600 text-white'
                        : 'bg-white/10 hover:bg-white/20 text-gray-300 border border-white/20'
                ]"
            >
                {{ page }}
            </button>

            <!-- Ellipsis (right) -->
            <span v-if="pageNumbers[pageNumbers.length - 1] < lastPage - 1" class="px-2 text-gray-400">...</span>

            <!-- Last Page (if not visible in range) -->
            <button
                v-if="pageNumbers[pageNumbers.length - 1] < lastPage"
                @click="goToPage(lastPage)"
                :class="[
                    'px-3 py-2 rounded-lg font-semibold transition-colors',
                    currentPage === lastPage
                        ? 'bg-cyan-600 text-white'
                        : 'bg-white/10 hover:bg-white/20 text-gray-300 border border-white/20'
                ]"
            >
                {{ lastPage }}
            </button>

            <!-- Next Button -->
            <button
                @click="nextPage"
                :disabled="currentPage === lastPage"
                class="px-4 py-2 bg-white/10 hover:bg-white/20 disabled:opacity-50 disabled:cursor-not-allowed border border-white/20 rounded-lg text-white font-semibold transition-colors"
            >
                Next →
            </button>
        </div>

        <!-- Edit Modal -->
        <div
            v-if="showEditModal && selectedUser"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-gray-900 border border-white/10 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-gray-900 border-b border-white/10 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-black text-white">Edit User: {{ selectedUser.name }}</h2>
                    <button
                        @click="showEditModal = false"
                        :disabled="isLoading"
                        class="text-gray-400 hover:text-white transition-colors text-2xl disabled:opacity-50"
                    >
                        ✕
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-6">
                    <!-- Basic Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-wider">Informasi Dasar</h3>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Name *</label>
                            <input
                                v-model="editForm.name"
                                type="text"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Email *</label>
                            <input
                                v-model="editForm.email"
                                type="email"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Phone</label>
                                <input
                                    v-model="editForm.phone"
                                    type="text"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Role *</label>
                                <select
                                    v-model="editForm.role"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                                >
                                    <option value="user" class="bg-gray-900">Job Seeker</option>
                                    <option value="recruiter" class="bg-gray-900">Recruiter</option>
                                    <option value="admin" class="bg-gray-900">Administrator</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Address Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-wider">Alamat</h3>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Address</label>
                            <input
                                v-model="editForm.address"
                                type="text"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">City</label>
                                <input
                                    v-model="editForm.city"
                                    type="text"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Province</label>
                                <input
                                    v-model="editForm.province"
                                    type="text"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Postal Code</label>
                            <input
                                v-model="editForm.postal_code"
                                type="text"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                            />
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-wider">Informasi Tambahan</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Date of Birth</label>
                                <input
                                    v-model="editForm.date_of_birth"
                                    type="date"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-300 mb-2">Gender</label>
                                <select
                                    v-model="editForm.gender"
                                    class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                                >
                                    <option value="" class="bg-gray-900">Select...</option>
                                    <option value="male" class="bg-gray-900">Male</option>
                                    <option value="female" class="bg-gray-900">Female</option>
                                    <option value="other" class="bg-gray-900">Other</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Bio</label>
                            <textarea
                                v-model="editForm.bio"
                                rows="3"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="border-t border-white/10 px-6 py-4 flex gap-3 justify-end">
                    <button
                        @click="showEditModal = false"
                        :disabled="isLoading"
                        class="px-6 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-bold transition-colors disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitEditForm"
                        :disabled="isLoading"
                        class="px-6 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ isLoading ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div
            v-if="showDeleteModal && selectedUser"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-gray-900 border border-red-500/20 rounded-2xl max-w-md w-full">
                <div class="p-6">
                    <h2 class="text-xl font-black text-white mb-4">Hapus User?</h2>
                    <p class="text-gray-300 mb-6">
                        Apakah Anda yakin ingin menghapus user <span class="font-bold text-cyan-400">{{ selectedUser.name }}</span>? 
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button
                            @click="showDeleteModal = false"
                            :disabled="isLoading"
                            class="px-6 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-bold transition-colors disabled:opacity-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteUser"
                            :disabled="isLoading"
                            class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-colors disabled:opacity-50"
                        >
                            {{ isLoading ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Modal -->
        <div
            v-if="showPasswordModal && selectedUser"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-gray-900 border border-white/10 rounded-2xl max-w-md w-full">
                <div class="border-b border-white/10 px-6 py-4">
                    <h2 class="text-xl font-black text-white">Update Password: {{ selectedUser.name }}</h2>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-300 mb-2">New Password *</label>
                        <input
                            v-model="passwordForm.password"
                            type="password"
                            placeholder="Min 8 karakter"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-300 mb-2">Confirm Password *</label>
                        <input
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            placeholder="Konfirmasi password"
                            class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none focus:border-cyan-400/50 focus:bg-white/[0.08]"
                        />
                    </div>
                </div>

                <div class="border-t border-white/10 px-6 py-4 flex gap-3 justify-end">
                    <button
                        @click="showPasswordModal = false"
                        :disabled="isLoading"
                        class="px-6 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg text-white font-bold transition-colors disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updatePassword"
                        :disabled="isLoading"
                        class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ isLoading ? 'Updating...' : 'Update Password' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminPageLayout>
</template>
