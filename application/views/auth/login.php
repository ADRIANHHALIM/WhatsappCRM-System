<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Corporate CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-primary: #3b82f6;
            --brand-secondary: #60a5fa;
            --bg-dark: #020617;
            --glass-bg: rgba(15, 23, 42, 0.45);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at 15% 50%, rgba(30, 58, 138, 0.3), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(16, 185, 129, 0.15), transparent 25%),
                var(--bg-dark);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            overflow: hidden;
            margin: 0;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                inset 0 0 0 1px rgba(255, 255, 255, 0.02);
            animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group-custom i {
            position: absolute;
            left: 1.2rem;
            color: #64748b;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }

        .form-control-custom {
            background: rgba(2, 6, 23, 0.5);
            border: 1px solid #1e293b;
            color: white;
            padding: 0.85rem 1rem 0.85rem 3rem;
            border-radius: 0.75rem;
            width: 100%;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control-custom::placeholder {
            color: #475569;
        }

        .form-control-custom:focus {
            background: rgba(2, 6, 23, 0.8);
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .form-control-custom:focus+i,
        .form-control-custom:focus~i {
            color: var(--brand-primary);
        }

        .btn-brand {
            background: linear-gradient(135deg, var(--brand-primary) 0%, #2563eb 100%);
            border: none;
            border-radius: 0.75rem;
            color: white;
            font-weight: 600;
            padding: 0.85rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-brand:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, var(--brand-secondary) 0%, var(--brand-primary) 100%);
        }

        .alert-glass {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-radius: 0.75rem;
            font-size: 0.9rem;
        }

        /* Styling khusus untuk logo WA yang baru */
        .wa-logo-huge {
            font-size: 4.5rem;
            display: inline-block;
            filter: drop-shadow(0 10px 15px rgba(25, 135, 84, 0.3));
            /* Efek glow/shadow hijau */
            transition: transform 0.3s ease;
        }

        .wa-logo-huge:hover {
            transform: scale(1.05);
            /* Sedikit membesar saat di-hover */
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="text-center mb-5">
            <i class="bi bi-whatsapp text-success mb-3 wa-logo-huge"></i>
            <h4 class="fw-bold mb-1 letter-spacing-tight">Corporate CRM</h4>
            <p class="text-muted small">Secure Access Portal</p>
        </div>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-glass d-flex align-items-center p-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <?= $this->session->flashdata('error') ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('auth/login') ?>" method="POST">
            <div class="mb-4">
                <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide"
                    style="font-size: 0.75rem; letter-spacing: 1px;">Username</label>
                <div class="input-group-custom">
                    <input type="text" name="username" class="form-control-custom" required autofocus
                        placeholder="Enter your username">
                    <i class="bi bi-person"></i>
                </div>
            </div>

            <div class="mb-5">
                <label class="form-label text-muted small fw-semibold text-uppercase tracking-wide"
                    style="font-size: 0.75rem; letter-spacing: 1px;">Password</label>
                <div class="input-group-custom">
                    <input type="password" name="password" class="form-control-custom" required placeholder="••••••••">
                    <i class="bi bi-lock"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-brand w-100">
                Sign In
            </button>
        </form>
    </div>
</body>

</html>