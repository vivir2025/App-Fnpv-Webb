<!-- resources/views/auth/login.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema App-Fnpv Web</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/logo.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #28a745; /* Color verde principal */
            --secondary-color: #f8f9fa; /* Color blanco/gris claro */
        }
        
        body {
            background-color: var(--primary-color);
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        
        .login-container {
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 900px;
            height: 500px;
            display: flex;
        }
        
        .login-left {
            background-color: var(--primary-color);
            width: 40%;
            position: relative;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .login-left::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background-color: white;
            border-radius: 0 0 0 100%;
        }
        
        .login-left::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 150px;
            height: 150px;
            background-color: white;
            border-radius: 0 100% 0 0;
        }
        
        .login-illustration {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        
        .login-illustration img {
            max-width: 100%;
            height: 490px;/*Ajustatax;usta este valor según necesites */
            object-fit: contain; /* Esto mantiene la proporción de la imagen */
        }
        .login-right {
            width: 60%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .welcome-text {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
        }
        
        .login-form {
            width: 100%;
            max-width: 350px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-control {
            border: none;
            border-bottom: 1px solid #ddd;
            border-radius: 0;
            padding: 10px 10px 10px 40px;
            background-color: #f8f9fa;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }
        
        .form-icon {
            position: absolute;
            left: 10px;
            top: 10px;
            color: #999;
        }
        
        .forgot-link {
            text-align: right;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .forgot-link a {
            color: #999;
            text-decoration: none;
        }
        
        .login-btn {
            background-color: var(--primary-color);
            border: none;
            border-radius: 50px;
            color: white;
            padding: 12px;
            width: 100%;
            font-weight: bold;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .login-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                height: auto;
                width: 95%;
            }
            
            .login-left, .login-right {
                width: 80;
                padding: 30px;
            }
            
            .login-left {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-left">
                <div class="login-illustration">
                    <img src="https://nacerparavivir.org/wp-content/uploads/2023/11/buena-8.png" alt="Logo Institucional">
                </div>
            </div>
            <div class="login-right">
                <div class="login-logo">
                    <img src="https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png" alt="Logo de la empresa">
                </div>
                <div class="welcome-text">BIENVENIDO</div>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login.submit') }}" class="login-form">
                    @csrf
                    <div class="form-group">
                        <span class="form-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario" value="{{ old('usuario') }}" required autofocus>
                    </div>
                    <div class="form-group">
                        <span class="form-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                                <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
                            </svg>
                        </span>
                        <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Contraseña" required>
                    </div>
                    <div class="forgot-link">
                        <a href="#">¿Olvidó su contraseña?</a>
                    </div>
                    <button type="submit" class="login-btn">INGRESAR</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
