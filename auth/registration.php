<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Account information
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Participant information
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    if (
        $username === '' ||
        $full_name === '' ||
        $email === '' ||
        $password === '' ||
        $confirm_password === '' ||
        $first_name === '' ||
        $last_name === '' ||
        $gender === '' ||
        $birthdate === '' ||
        $address === ''
    ) {
        $error = 'Please fill in all required fields.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';

    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';

    } else {

        // Check if username or email already exists
        $stmt = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE username = :username
               OR email = :email
            LIMIT 1
        ");

        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);

        if ($stmt->fetch()) {

            $error = 'Username or email already exists.';

        } else {

            try {

                // Start transaction
                $pdo->beginTransaction();

                // Hash password
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Default role
                $role = 'user';

                /*
                 * CREATE USER ACCOUNT
                 */
                $stmt = $pdo->prepare("
                    INSERT INTO users
                    (
                        username,
                        password,
                        full_name,
                        email,
                        role
                    )
                    VALUES
                    (
                        :username,
                        :password,
                        :full_name,
                        :email,
                        :role
                    )
                ");

                $stmt->execute([
                    ':username' => $username,
                    ':password' => $hashed_password,
                    ':full_name' => $full_name,
                    ':email' => $email,
                    ':role' => $role
                ]);

                // Get newly created user ID
                $user_id = (int) $pdo->lastInsertId();

                /*
                 * CREATE PARTICIPANT PROFILE
                 */
                $stmt = $pdo->prepare("
                    INSERT INTO participants
                    (
                        user_id,
                        first_name,
                        last_name,
                        gender,
                        birthdate,
                        email,
                        address
                    )
                    VALUES
                    (
                        :user_id,
                        :first_name,
                        :last_name,
                        :gender,
                        :birthdate,
                        :email,
                        :address
                    )
                ");

                $stmt->execute([
                    ':user_id' => $user_id,
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':gender' => $gender,
                    ':birthdate' => $birthdate,
                    ':email' => $email,
                    ':address' => $address
                ]);

                // Save registration notification
                $_SESSION['registration_success'] =
                    'Account successfully registered! You can now log in.';

                // Finish transaction
                $pdo->commit();

                // Redirect to login page
                header('Location: login.php');
                exit;

            } catch (PDOException $e) {

                // Roll back if something fails
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Registration failed. Please try again.';
            }
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

    <title>Register - Event Management System</title>

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

            padding: 30px 15px;
        }

        .register-container {
            width: 100%;
            max-width: 500px;
        }

        .register-card {
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

        .section-title {
            font-size: 16px;
            font-weight: 700;

            color: #374151;

            margin-top: 20px;
            margin-bottom: 15px;

            padding-bottom: 8px;

            border-bottom: 1px solid #e5e7eb;
        }

        .form-group {
            margin-bottom: 17px;
        }

        label {
            display: block;

            margin-bottom: 7px;

            font-weight: 600;

            color: #374151;

            font-size: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;

            padding: 12px 14px;

            border: 1px solid #d1d5db;

            border-radius: 7px;

            font-size: 15px;

            outline: none;

            font-family: inherit;

            transition: 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2563eb;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 90px;
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

        .register-btn {
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

            margin-top: 5px;
        }

        .register-btn:hover {
            background: #1d4ed8;
        }

        .login-link {
            text-align: center;

            margin-top: 20px;

            color: #6b7280;

            font-size: 14px;
        }

        .login-link a {
            color: #2563eb;

            text-decoration: none;

            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="register-container">

    <div class="register-card">

        <div class="logo">

            <h1>Event Management</h1>

            <p>Create your account</p>

        </div>

        <?php if ($error !== ''): ?>

            <div class="error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST" action="">

            <!-- ACCOUNT INFORMATION -->

            <div class="section-title">
                Account Information
            </div>


            <!-- Username -->

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
                >

            </div>


            <!-- Full Name -->

            <div class="form-group">

                <label for="full_name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter your full name"
                    value="<?= e($_POST['full_name'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Email -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?= e($_POST['email'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Password -->

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


            <!-- Confirm Password -->

            <div class="form-group">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm your password"
                    required
                >

            </div>


            <!-- PARTICIPANT INFORMATION -->

            <div class="section-title">
                Participant Information
            </div>


            <!-- First Name -->

            <div class="form-group">

                <label for="first_name">
                    First Name
                </label>

                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    placeholder="Enter your first name"
                    value="<?= e($_POST['first_name'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Last Name -->

            <div class="form-group">

                <label for="last_name">
                    Last Name
                </label>

                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    placeholder="Enter your last name"
                    value="<?= e($_POST['last_name'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Gender -->

            <div class="form-group">

                <label for="gender">
                    Gender
                </label>

                <select
                    id="gender"
                    name="gender"
                    required
                >

                    <option value="">
                        Select gender
                    </option>

                    <option
                        value="Male"
                        <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>
                    >
                        Male
                    </option>

                    <option
                        value="Female"
                        <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>
                    >
                        Female
                    </option>

                    <option
                        value="Other"
                        <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>
                    >
                        Other
                    </option>

                </select>

            </div>


            <!-- Birthdate -->

            <div class="form-group">

                <label for="birthdate">
                    Birthdate
                </label>

                <input
                    type="date"
                    id="birthdate"
                    name="birthdate"
                    value="<?= e($_POST['birthdate'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Address -->

            <div class="form-group">

                <label for="address">
                    Address
                </label>

                <textarea
                    id="address"
                    name="address"
                    placeholder="Enter your complete address"
                    required
                ><?= e($_POST['address'] ?? '') ?></textarea>

            </div>


            <!-- Register Button -->

            <button
                type="submit"
                class="register-btn"
            >
                Register
            </button>

        </form>


        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Log in
            </a>

        </div>

    </div>

</div>

</body>

</html>