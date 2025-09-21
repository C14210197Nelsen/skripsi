<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right,rgb(208, 26, 26), #861f1f);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            padding: 40px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
        }
        .login-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: bold;
            color: #343a40;
        }
        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-title">Login Sistem</div>

    <?php if($errors->has('login')): ?>
        <div class="alert alert-danger">
            <?php echo e($errors->first('login')); ?>

        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-danger rounded-pill">Login</button>
        </div>
    </form>
</div>

</body>
</html>
<?php /**PATH C:\Users\nelse\Herd\skripsi\resources\views/auth/login.blade.php ENDPATH**/ ?>