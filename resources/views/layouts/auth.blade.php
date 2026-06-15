<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>@yield('title') - Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #e0e5ec;  /* ← Solid soft gray seperti di screenshot kamu */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #3d4468;  /* Warna text utama klasik neumorphism */
        }

        .auth-box {
            background: #e0e5ec;  /* Sama dengan body agar efek shadow keluar masuk terlihat jelas */
            padding: 2.8rem 2.2rem;
            border-radius: 28px;
            box-shadow: 
                22px 22px 70px #bec3cf,          /* Shadow gelap bawah-kanan */
                -22px -22px 70px #ffffff;        /* Shadow terang atas-kiri */
            width: 100%;
            max-width: 440px;
            transition: all 0.4s ease;
        }

        .auth-box:hover {
            transform: translateY(-5px);
        }

        .neu-input {
            position: relative;
            background: #e0e5ec;
            border-radius: 18px;
            box-shadow: 
                inset 7px 7px 14px #bec3cf,
                inset -7px -7px 14px #ffffff;
            transition: all 0.3s ease;
        }

        .neu-input:focus-within {
            box-shadow: 
                inset 4px 4px 10px #bec3cf,
                inset -4px -4px 10px #ffffff;
        }

        .neu-input input {
            background: transparent;
            border: none;
            width: 100%;
            padding: 1.4rem 1.2rem 0.6rem 4rem;
            font-size: 1.05rem;
            color: #111;
            outline: none;
        }

        .neu-input label {
            position: absolute;
            left: 4rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9499b7;
            font-size: 1.05rem;
            pointer-events: none;
            transition: all 0.35s ease;
        }

        .neu-input input:focus + label,
        .neu-input input:not(:placeholder-shown) + label {
            top: 0.9rem;
            font-size: 0.85rem;
            color: #6c7293;  /* Accent lebih soft sesuai neumorphism klasik */
        }

        .input-icon {
            position: absolute;
            left: 1.3rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9499b7;
            font-size: 1.3rem;
        }

        .password-toggle {
            position: absolute;
            right: 1.3rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #9499b7;
            cursor: pointer;
            padding: 0;
        }

        .password-toggle svg {
            width: 1.4rem;
            height: 1.4rem;
        }

        .eye-closed { display: none; }

        .neu-button {
            background: #e0e5ec;
            border: none;
            color: #3d4468;
            font-weight: 600;
            padding: 1rem;
            border-radius: 18px;
            box-shadow: 
                8px 8px 20px #bec3cf,
                -8px -8px 20px #ffffff;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1.5rem;
        }

        .neu-button:hover {
            transform: translateY(-4px);
            box-shadow: 
                12px 12px 30px #bec3cf,
                -12px -12px 30px #ffffff;
        }

        .neu-button:active {
            transform: translateY(2px);
            box-shadow: inset 6px 6px 15px #bec3cf,
                        inset -6px -6px 15px #ffffff;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .error-message {
            color: #ff3b5c;
            font-size: 0.9rem;
            margin-top: 0.4rem;
            display: block;
            min-height: 1.3rem;
        }

        /* Error state untuk input (seperti screenshot: border merah + shadow merah) */
        .form-group.error .neu-input {
            box-shadow: 
                inset 7px 7px 14px #ffb8c4,
                inset -7px -7px 14px #ffffff,
                0 0 0 2px #ff3b5c;
        }

        /* Social Buttons Section tetap ada, tapi shadow disesuaikan ke light gray */
        .social-divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            color: #9499b7;
            font-size: 0.9rem;
        }

        .social-divider .line {
            flex: 1;
            height: 1px;
            background: #bec3cf;
        }

        .social-divider span {
            padding: 0 1.2rem;
        }

        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .neu-social {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: none;
            background: #e0e5ec;
            box-shadow: 
                6px 6px 14px #bec3cf,
                -6px -6px 14px #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c7293;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .neu-social:hover {
            transform: translateY(-4px);
            box-shadow: 
                10px 10px 24px #bec3cf,
                -10px -10px 24px #ffffff;
        }

        .neu-social:active {
            transform: translateY(2px);
            box-shadow: inset 4px 4px 10px #bec3cf,
                        inset -4px -4px 10px #ffffff;
        }

        .neu-social svg {
            width: 24px;
            height: 24px;
        }

        /* Brand colors tetap */
        .social-ig { color: #e1306c; }
        .social-fb { color: #1877f2; }
        .social-google { color: #4285f4; }
        .social-twitter { color: #1da1f2; }
        .social-apple { color: #000; }

        @media (max-width: 576px) {
            .auth-box {
                padding: 2rem 1.6rem;
                border-radius: 24px;
            }
            .neu-social { width: 48px; height: 48px; }
            .neu-social svg { width: 22px; height: 22px; }
        }
    </style>
</head>
<body>

    <div class="auth-box">
        @yield('content')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password (support multiple fields seperti reset password)
            function setupPasswordToggle(inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);
                if (!input || !toggle) return;

                toggle.addEventListener('click', function() {
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;

                    const eyeOpen = toggle.querySelector('.eye-open');
                    const eyeClosed = toggle.querySelector('.eye-closed');

                    eyeOpen.style.display = type === 'text' ? 'none' : 'block';
                    eyeClosed.style.display = type === 'text' ? 'block' : 'none';
                });
            }

            setupPasswordToggle('password', 'passwordToggle');
            setupPasswordToggle('password_confirmation', 'passwordConfirmToggle');  // kalau ada di reset

            // Floating label enhancement
            document.querySelectorAll('.neu-input input').forEach(input => {
                const label = input.nextElementSibling;
                if (label && label.tagName === 'LABEL') {
                    input.addEventListener('input', function() {
                        if (this.value.trim()) {
                            label.style.top = '0.9rem';
                            label.style.fontSize = '0.85rem';
                            label.style.color = '#6c7293';
                        }
                    });
                }
            });
        });
    </script>

</body>
</html>