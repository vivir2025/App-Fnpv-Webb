<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Visitas App-Fnpv Web')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #28a745;
            --primary-dark: #218838;
            --secondary-color: #f8f9fa;
            --text-light: rgba(255, 255, 255, 0.8);
            --sidebar-width: 250px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
        }
        
        /* SIDEBAR FIJO */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Ocultar sidebar en móviles */
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
        }
        
        /* CONTENIDO PRINCIPAL */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background-color: var(--secondary-color);
            transition: margin-left 0.3s ease-in-out;
        }
        
        @media (max-width: 767.98px) {
            .main-wrapper {
                margin-left: 0;
            }
        }
        
        /* NAVBAR FIJO */
        .main-navbar {
            position: sticky;
            top: 0;
            z-index: 999;
            background-color: white !important;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        /* CONTENIDO PRINCIPAL */
        .main-content {
            padding: 20px;
            min-height: calc(100vh - 70px); /* Ajustar según altura del navbar */
        }
        
        /* ELEMENTOS DEL SIDEBAR */
        .sidebar .nav-link {
            color: var(--text-light);
            padding: 0.75rem 1rem;
            margin: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            color: white;
            background-color: var(--primary-dark);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        /* LOGO DEL SIDEBAR */
        .logo-container {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }
        
        .sidebar-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: white;
            padding: 10px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }
        
        .sidebar-logo:hover {
            transform: scale(1.05);
        }
        
        .sidebar-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        /* OVERLAY PARA MÓVILES */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* ESTILOS DEL NAVBAR */
        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
            font-size: 1.25rem;
        }
        
        .dropdown-toggle::after {
            display: none;
        }
        
        .dropdown-menu {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
        }
        
        .dropdown-item:hover {
            background-color: var(--secondary-color);
            color: var(--primary-color);
        }
        
        /* BOTONES */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        /* BOTÓN DE TOGGLE PARA MÓVILES */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        /* BOTÓN DE CERRAR SESIÓN EN SIDEBAR */
        .logout-section {
            margin-top: auto;
            padding: 1rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .btn-logout {
            background: none;
            border: none;
            color: var(--text-light);
            padding: 0.75rem 1rem;
            margin: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            width: calc(100% - 1rem);
            text-align: left;
        }
        
        .sidebar .btn-logout:hover {
            color: white;
            background-color: rgba(220, 53, 69, 0.2);
            transform: translateX(5px);
        }
        
        .sidebar .btn-logout i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }
        
        /* SCROLLBAR PERSONALIZADA PARA SIDEBAR */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* ANIMACIONES */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .main-content > * {
            animation: slideIn 0.3s ease-out;
        }
        
        /* RESPONSIVE */
        @media (max-width: 991.98px) {
            :root {
                --sidebar-width: 280px;
            }
        }
        
        @media (max-width: 575.98px) {
            .main-content {
                padding: 15px;
            }
            
            .logo-container {
                padding: 1rem;
            }
            
            .sidebar-logo {
                width: 60px;
                height: 60px;
            }
        }
        /* ESTILOS PARA SUBMENU */
        .submenu {
            list-style: none;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
            overflow: hidden;
            max-height: 0;
            transition: max-height 0.3s ease-out;
        }

        .submenu.show {
            max-height: 500px; /* Altura máxima para el submenu */
        }

        .submenu-link {
            color: var(--text-light);
            padding: 0.5rem 1rem;
            margin: 0.25rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .submenu-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .submenu-link.active {
            color: white;
            background-color: var(--primary-dark);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .submenu-link i {
            margin-right: 10px;
            width: 16px;
            text-align: center;
            font-size: 0.85rem;
        }

        .submenu-toggle {
            position: relative;
        }

        .submenu-icon {
            transition: transform 0.3s ease;
            font-size: 0.7rem;
        }

        .submenu-toggle.active .submenu-icon {
            transform: rotate(180deg);
        }

    </style>
    @yield('styles')
</head>
<body>
    <!-- Overlay para móviles -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar fijo -->
    <nav class="sidebar" id="sidebar">
        <div class="d-flex flex-column h-100">
            <!-- Logo -->
            <div class="logo-container">
                <div class="sidebar-logo">
                    <img src="https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png" alt="Logo">
                </div>
                <div class="mt-2">
                    <small class="text-white-50">Sistema App-Fnpv Web</small>
                </div>
            </div>
            
            <!-- Navegación principal -->
            <ul class="nav flex-column flex-grow-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('visitas.*') ? 'active' : '' }}" href="{{ route('visitas.buscar') }}">
                        <i class="fas fa-home"></i>
                        <span>Visitas Domiciliarias</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('laboratorio.*') ? 'menu-open' : '' }}">
                    <a class="nav-link {{ request()->routeIs('laboratorio.*') ? 'active' : '' }}" href="{{ route('laboratorio.index') }}">
                        <i class="fas fa-vial"></i>
                        <span>Envío de Muestras</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link submenu-toggle {{ request()->routeIs('reportes.*') || request()->routeIs('visitas.export') ? 'active' : '' }}" href="#" onclick="toggleSubmenu(event, this)">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                        <i class="fas fa-chevron-down ms-auto submenu-icon"></i>
                    </a>
                    <ul class="submenu collapse {{ request()->routeIs('reportes.*') || request()->routeIs('visitas.export') ? 'show' : '' }}">
                        <li>
                            <a class="submenu-link {{ request()->routeIs('visitas.export') ? 'active' : '' }}" href="{{ route('visitas.export') }}">
                                <i class="fas fa-file-excel"></i>
                                <span>Exportar Visitas</span>
                            </a>
                        </li>
                        <li>
                            <a class="submenu-link {{ request()->routeIs('brigadas.export') ? 'active' : '' }}" href="{{ route('brigadas.export') }}">
                                <i class="fas fa-file-medical"></i>
                                <span>Exportar Brigadas</span>
                            </a>
                        </li>
                        <li>
                            <a class="submenu-link {{ request()->routeIs('encuestas.export') ? 'active' : '' }}" href="{{ route('encuestas.export') }}">
                                <i class="fas fa-file-excel"></i>
                                <span>Exportar Encuestas</span>
                            </a>
                        </li>
                        <li>
                            <a class="submenu-link {{ request()->routeIs('findrisk.export') ? 'active' : '' }}" href="{{ route('findrisk.export') }}">
                                <i class="fas fa-file-export me-1"></i>
                                <span>Exportar Tests</span>
                            </a>
                        </li>
                        <li>
                            <a class="submenu-link {{ request()->routeIs('afinamientos.export') ? 'active' : '' }}" href="{{ route('afinamientos.export') }}">
                                <i class="fas fa-heartbeat"></i>
                                <span>Exportar Afinamientos</span>
                            </a>
                        </li>
                        <li>
                            <a class="submenu-link {{ request()->routeIs('tamizajes.export') ? 'active' : '' }}" href="{{ route('tamizajes.export') }}">
                                <i class="fas fa-heartbeat"></i>
                                <span>Exportar Tamizajes</span>
                            </a>
                        </li>


                        <!-- Puedes agregar más opciones de submenu aquí -->
                    </ul>
                </li>

            </ul>
            
            <!-- Sección de cerrar sesión -->
            <div class="logout-section">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="main-wrapper">
        <!-- Navbar superior -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white main-navbar">
            <div class="container-fluid">
                <!-- Botón para mostrar sidebar en móviles -->
                <button class="navbar-toggler d-md-none" type="button" id="sidebarToggle">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Título de la página -->
                <span class="navbar-brand mb-0">@yield('title', 'Dashboard')</span>
                
                <!-- Usuario dropdown -->
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle text-success me-2"></i>
                            <span class="d-none d-sm-inline">{{ Auth::user()->nombre ?? 'Usuario' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="#" onclick="return false;">
                                    <i class="fas fa-user-cog text-success me-2"></i> Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt text-danger me-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido de la página -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Funcionalidad para mostrar/ocultar sidebar en móviles
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            // Función para mostrar sidebar
            function showSidebar() {
                sidebar.classList.add('show');
                sidebarOverlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
            
            // Función para ocultar sidebar
            function hideSidebar() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
            
            // Event listeners
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', showSidebar);
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', hideSidebar);
            }
            
            // Cerrar sidebar al hacer clic en un enlace en móviles
            const sidebarLinks = sidebar.querySelectorAll('.nav-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        hideSidebar();
                    }
                });
            });
            
            // Manejar cambios de tamaño de ventana
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    hideSidebar();
                }
            });
            
            // Prevenir scroll del body cuando el sidebar está abierto en móviles
            let touchStartY = 0;
            sidebar.addEventListener('touchstart', function(e) {
                touchStartY = e.touches[0].clientY;
            });
            
            sidebar.addEventListener('touchmove', function(e) {
                const touchCurrentY = e.touches[0].clientY;
                const touchDelta = touchCurrentY - touchStartY;
                
                // Si está en el tope y intenta hacer scroll hacia arriba, o
                // si está en el fondo y intenta hacer scroll hacia abajo, prevenir
                if ((sidebar.scrollTop <= 0 && touchDelta > 0) ||
                    (sidebar.scrollTop >= sidebar.scrollHeight - sidebar.clientHeight && touchDelta < 0)) {
                    e.preventDefault();
                }
            });
        });

        
        // Función para marcar el enlace activo dinámicamente
        function setActiveNavLink() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        }
        // Función para manejar el submenu
        function toggleSubmenu(event, element) {
            event.preventDefault();
            
            // Toggle la clase active en el enlace
            element.classList.toggle('active');
            
            // Encuentra el submenu siguiente
            const submenu = element.nextElementSibling;
            
            // Toggle la clase show en el submenu
            if (submenu) {
                submenu.classList.toggle('show');
            }
        }

        // Inicializar submenus al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Marcar como activo el elemento padre si un submenu está activo
            const activeSubmenuLink = document.querySelector('.submenu-link.active');
            if (activeSubmenuLink) {
                const parentItem = activeSubmenuLink.closest('.nav-item');
                if (parentItem) {
                    const parentToggle = parentItem.querySelector('.submenu-toggle');
                    if (parentToggle) {
                        parentToggle.classList.add('active');
                    }
                }
            }
        });

        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', setActiveNavLink);
    </script>
    
    @yield('scripts')
</body>
</html>