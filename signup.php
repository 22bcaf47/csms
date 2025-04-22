<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            background-image:url('logo.jpg');           
            font-family: 'Poppins', sans-serif;
           
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            width: 350px;
            text-align: center;
            color: white;
        }
        .login-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .input-field {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 16px;
            text-align: center;
            outline: none;
        }
        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #ff416c;
            background: linear-gradient(90deg, #ff4b2b, #ff416c);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 18px;
            transition: 0.3s ease;
        }
        .login-btn:hover {
            background: linear-gradient(90deg, #ff416c, #ff4b2b);
        }
        .forgot-password {
            display: block;
            margin-top: 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Sign Up</h2>
        <form action="csmsignup2.php" method="post">
            <input type="text" class="input-field" name="username" placeholder="Username" required>
            <input type="email" class="input-field" name="email" placeholder="EmailId" required>
            <input type="text" class="input-field" name="phone" placeholder="Phone No" required>
            <input type="password" class="input-field" name="password" placeholder="Password" required>
            <button type="submit" class="login-btn">Sign Up</button>
            no Account<a href="login.php">login</a>
        </form>
        
    </div>
</body>
</html>
