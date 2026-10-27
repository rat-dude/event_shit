<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

/*
 * Registration success notification
 */
$registration_success = $_SESSION['registration_success'] ?? '';

unset($_SESSION['registration_success']);


/*
 * Login process
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if ($username === '' || $password === '') {

        $error = 'Please enter your username and password.';

    } else {

        // Find user
        $stmt = $pdo->prepare("
            SELECT
                user_id,
                username,
                password,
                full_name,
                email,
                role
            FROM users
            WHERE username = :username
            LIMIT 1
        ");

        $stmt->execute([
            ':username' => $username
        ]);

        $user = $stmt->fetch();


        // Verify password
        if (
            $user &&
            password_verify($password, $user['password'])
        ) {

            // Store user information in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect to dashboard
            header('Location: ../dashboard/dashboard.php');
            exit;

        } else {

            $error = 'Invalid username or password.';

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - Event Management System</title>


    <style>

        * {
            box-sizing: border-box;

            margin: 0;

            padding: 0;
        }


        body {

            font-family: Arial, Helvetica, sans-serif;

            background: #f4f6f9;

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .login-container {

            width: 100%;

            max-width: 420px;

            padding: 20px;
        }


        .login-card {

            background: #ffffff;

            border-radius: 12px;

            padding: 35px;

            box-shadow:
                0 5px 25px rgba(0, 0, 0, 0.08);
        }


        .logo {

            text-align: center;

            margin-bottom: 25px;
        }


        .logo h1 {

            color: #2563eb;

            font-size: 28px;

            margin-bottom: 8px;
        }


        .logo p {

            color: #6b7280;

            font-size: 14px;
        }


        .form-group {

            margin-bottom: 18px;
        }


        label {

            display: block;

            margin-bottom: 7px;

            font-weight: 600;

            color: #374151;

            font-size: 14px;
        }


        input {

            width: 100%;

            padding: 12px 14px;

            border: 1px solid #d1d5db;

            border-radius: 7px;

            font-size: 15px;

            outline: none;

            transition: 0.2s;
        }


        input:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.1);
        }


        .error {

            background: #fee2e2;

            color: #b91c1c;

            border: 1px solid #fecaca;

            padding: 11px 13px;

            border-radius: 7px;

            margin-bottom: 18px;

            font-size: 14px;
        }


        .login-btn {

            width: 100%;

            padding: 13px;

            background: #2563eb;

            color: white;

            border: none;

            border-radius: 7px;

            font-size: 16px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.2s;
        }


        .login-btn:hover {

            background: #1d4ed8;
        }


        .register-link {

            text-align: center;

            margin-top: 20px;

            color: #6b7280;

            font-size: 14px;
        }


        .register-link a {

            color: #2563eb;

            text-decoration: none;

            font-weight: 600;
        }


        .register-link a:hover {

            text-decoration: underline;
        }


        /* SUCCESS POPUP */

        .success-popup {

            position: fixed;

            top: 25px;

            right: 25px;

            width: 380px;

            background: #ffffff;

            border-radius: 12px;

            padding: 18px 20px;

            display: flex;

            align-items: center;

            gap: 14px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.15);

            border-left: 5px solid #22c55e;

            z-index: 9999;

            animation: slideIn 0.4s ease;
        }


        .success-icon {

            width: 42px;

            height: 42px;

            border-radius: 50%;

            background: #dcfce7;

            color: #16a34a;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;

            font-weight: bold;

            flex-shrink: 0;
        }


        .success-content {

            flex: 1;
        }


        .success-content strong {

            display: block;

            color: #166534;

            font-size: 15px;

            margin-bottom: 3px;
        }


        .success-content p {

            margin: 0;

            color: #6b7280;

            font-size: 13px;
        }


        .close-popup {

            background: none;

            border: none;

            color: #9ca3af;

            font-size: 24px;

            cursor: pointer;

            line-height: 1;

            padding: 0;
        }


        .close-popup:hover {

            color: #374151;
        }


        @keyframes slideIn {

            from {

                opacity: 0;

                transform: translateX(100%);
            }

            to {

                opacity: 1;

                transform: translateX(0);
            }
        }


        @media (max-width: 500px) {

            .success-popup {

                left: 15px;

                right: 15px;

                top: 15px;

                width: auto;
            }
        }

    </style>

</head>


<body>


<!-- SUCCESS POPUP -->

<?php if ($registration_success !== ''): ?>

    <div
        class="success-popup"
        id="successPopup"
    >

        <div class="success-icon">
            ✓
        </div>


        <div class="success-content">

            <strong>
                Registration Successful!
            </strong>

            <p>
                <?= e($registration_success) ?>
            </p>

        </div>


        <button
            type="button"
            class="close-popup"
            onclick="closeSuccessPopup()"
        >
            ×
        </button>

    </div>

<?php endif; ?>


<div class="login-container">

    <div class="login-card">


        <div class="logo">

            <h1>
                Event Management
            </h1>

            <p>
                Sign in to your account
            </p>

        </div>


        <!-- LOGIN ERROR -->

        <?php if ($error !== ''): ?>

            <div class="error">

                <?= e($error) ?>

            </div>

        <?php endif; ?>


        <!-- LOGIN FORM -->

        <form
            method="POST"
            action=""
        >


            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?= e($_POST['username'] ?? '') ?>"
                    required
                    autofocus
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-btn"
            >
                Log In
            </button>


        </form>


        <div class="register-link">

            Don't have an account?

            <a href="registration.php">
                Register
            </a>

        </div>


    </div>

</div>


<script>

function closeSuccessPopup() {

    const popup =
        document.getElementById('successPopup');

    if (popup) {

        popup.style.opacity = '0';

        popup.style.transform =
            'translateX(100%)';

        setTimeout(() => {

            popup.remove();

        }, 300);
    }
}


// Automatically hide after 5 seconds

setTimeout(() => {

    closeSuccessPopup();

}, 5000);

</script>


</body>

</html>