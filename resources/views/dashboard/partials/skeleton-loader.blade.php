<!-- Skeleton Loader para Dashboard -->
<div id="skeleton-loader" style="display: none;">
    <style>
        /* Skeleton Loader Animations */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        .skeleton {
            background: linear-gradient(
                90deg,
                #f0f0f0 0%,
                #f8f8f8 20%,
                #f0f0f0 40%,
                #f0f0f0 100%
            );
            background-size: 1000px 100%;
            animation: shimmer 2s infinite linear;
            border-radius: 8px;
        }

        .skeleton-dark {
            background: linear-gradient(
                90deg,
                #e0e0e0 0%,
                #ececec 20%,
                #e0e0e0 40%,
                #e0e0e0 100%
            );
            background-size: 1000px 100%;
            animation: shimmer 2s infinite linear;
        }

        .skeleton-text {
            height: 12px;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .skeleton-title {
            height: 20px;
            margin-bottom: 12px;
            border-radius: 6px;
        }

        .skeleton-circle {
            border-radius: 50%;
        }

        .skeleton-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
        }

        .skeleton-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #198754, #20c997, #198754);
            background-size: 200% 100%;
            animation: slideProgress 2s ease-in-out infinite;
        }

        @keyframes slideProgress {
            0% {
                background-position: 100% 0;
            }
            100% {
                background-position: -100% 0;
            }
        }

        #skeleton-loader {
            opacity: 1;
            transition: opacity 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .skeleton-stat-number {
            width: 100px;
            height: 60px;
            margin: 0 auto 10px;
            border-radius: 12px;
        }

        .loading-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #198754;
        }

        .loading-message .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid #e0e0e0;
            border-top-color: #198754;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>

    <!-- Mensaje de carga flotante -->
    <div class="loading-message">
        <div class="spinner"></div>
        <span>Cargando datos del dashboard...</span>
    </div>

    <div class="row">
        <!-- Skeleton para Mapa -->
        <div class="col-lg-8 mb-4">
            <div class="skeleton-card" style="height: 500px;">
                <div class="skeleton skeleton-title" style="width: 40%;"></div>
                <div class="skeleton" style="height: 420px; margin-top: 20px;"></div>
            </div>
        </div>

        <!-- Skeleton para Estadísticas -->
        <div class="col-lg-4 mb-4">
            <div class="skeleton-card" style="height: 500px;">
                <div class="skeleton skeleton-title" style="width: 50%; margin-bottom: 30px;"></div>
                
                <!-- Estadística 1 -->
                <div class="text-center mb-4">
                    <div class="skeleton skeleton-stat-number"></div>
                    <div class="skeleton skeleton-text mx-auto" style="width: 60%;"></div>
                </div>

                <!-- Estadística 2 -->
                <div class="text-center mb-4">
                    <div class="skeleton skeleton-stat-number"></div>
                    <div class="skeleton skeleton-text mx-auto" style="width: 55%;"></div>
                </div>

                <!-- Estadística 3 -->
                <div class="text-center">
                    <div class="skeleton skeleton-stat-number"></div>
                    <div class="skeleton skeleton-text mx-auto" style="width: 70%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skeleton para Acordeón de Auxiliares -->
    <div class="mb-4">
        <div class="skeleton-card mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="skeleton skeleton-title" style="width: 30%;"></div>
                <div class="skeleton skeleton-circle" style="width: 30px; height: 30px;"></div>
            </div>
        </div>
        <div class="skeleton-card mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="skeleton skeleton-title" style="width: 35%;"></div>
                <div class="skeleton skeleton-circle" style="width: 30px; height: 30px;"></div>
            </div>
        </div>
        <div class="skeleton-card">
            <div class="d-flex justify-content-between align-items-center">
                <div class="skeleton skeleton-title" style="width: 28%;"></div>
                <div class="skeleton skeleton-circle" style="width: 30px; height: 30px;"></div>
            </div>
        </div>
    </div>

    <!-- Skeleton para Tabla de Auxiliares -->
    <div class="skeleton-card mb-4">
        <div class="skeleton skeleton-title" style="width: 40%; margin-bottom: 20px;"></div>
        
        <!-- Encabezado de tabla -->
        <div class="d-flex gap-2 mb-3">
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
        </div>

        <!-- Filas de tabla -->
        <div class="d-flex gap-2 mb-2">
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
        </div>
        <div class="d-flex gap-2 mb-2">
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
        </div>
        <div class="d-flex gap-2 mb-2">
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
        </div>
        <div class="d-flex gap-2">
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 20%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
            <div class="skeleton skeleton-text" style="width: 15%;"></div>
        </div>
    </div>

    <!-- Skeleton para Gráficos -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="skeleton-card" style="height: 400px;">
                <div class="skeleton skeleton-title" style="width: 50%;"></div>
                <div class="skeleton" style="height: 320px; margin-top: 20px;"></div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="skeleton-card" style="height: 400px;">
                <div class="skeleton skeleton-title" style="width: 50%;"></div>
                <div class="skeleton" style="height: 320px; margin-top: 20px;"></div>
            </div>
        </div>
    </div>
</div>
