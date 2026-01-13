<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Barra de navegación -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">

                    <!-- Logo -->
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">
                            Condominios
                        </h1>
                    </div>

                    <!-- MENÚ DESKTOP -->
                    <div class="hidden sm:flex sm:items-center sm:space-x-6">
                        <Link :href="route('dashboard')" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                            Dashboard
                        </Link>

                        <Link
                            v-if="canAccess(['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO', 'PROPIETARIO'])"
                            :href="route('residentes.index')"
                            class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                        >
                            Residentes
                        </Link>

                        <Link
                            v-if="canAccess(['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO', 'PROPIETARIO'])"
                            :href="route('viviendas.index')"
                            class="text-gray-600 hover:text-gray-900 text-sm font-medium"
                        >
                            Viviendas
                        </Link>

                        <Link :href="route('actividades.index')" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                            Actividades
                        </Link>

                        <Link :href="route('aportes.index')" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                            Aportes
                        </Link>

                        <Link :href="route('comunicaciones.index')" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                            Comunicaciones
                        </Link>

                        <Link :href="route('reuniones.index')" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                            Reuniones
                        </Link>
                    </div>

                    <!-- USUARIO DESKTOP -->
                    <div class="hidden sm:flex items-center space-x-4">
                        <span class="text-sm text-gray-700">
                            {{ user.name.split(' ')[0] }} ({{ user.rol }})
                        </span>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                        >
                            Cerrar Sesión
                        </Link>
                    </div>

                    <!-- BOTÓN HAMBURGUESA -->
                    <div class="flex items-center sm:hidden">
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="p-2 rounded-md text-gray-600 hover:bg-gray-200 focus:outline-none"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    v-if="!mobileMenuOpen"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    v-else
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MENÚ MOBILE -->
            <div v-if="mobileMenuOpen" class="sm:hidden px-4 pb-4 space-y-2">
                <Link :href="route('dashboard')" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-200 text-sm font-medium" @click="closeMenu">
                    Dashboard
                </Link>

                <Link
                    v-if="canAccess(['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO', 'PROPIETARIO'])"
                    :href="route('residentes.index')"
                    class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-200 text-sm font-medium"
                    @click="closeMenu"
                >
                    Residentes
                </Link>

                <Link
                    v-if="canAccess(['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO', 'PROPIETARIO'])"
                    :href="route('viviendas.index')"
                    class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-200 text-sm font-medium"
                    @click="closeMenu"
                >
                    Viviendas
                </Link>

                <Link :href="route('actividades.index')" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-200 text-sm font-medium" @click="closeMenu">
                    Actividades
                </Link>

                <Link :href="route('aportes.index')" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-200 text-sm font-medium" @click="closeMenu">
                    Aportes
                </Link>

                <Link :href="route('comunicaciones.index')" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-200 text-sm font-medium" @click="closeMenu">
                    Comunicaciones
                </Link>

                <Link :href="route('reuniones.index')" class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-200 text-sm font-medium" @click="closeMenu">
                    Reuniones
                </Link>

                <div class="border-t pt-3">
                    <span class="block text-sm text-gray-700 mb-2">
                        {{ user.name.split(' ')[0] }} ({{ user.rol }})
                    </span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700"
                        @click="closeMenu"
                    >
                        Cerrar Sesión
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)

const mobileMenuOpen = ref(false)

const closeMenu = () => {
    mobileMenuOpen.value = false
}

const canAccess = (roles) => {
    return roles.includes(user.value.rol)
}
</script>
