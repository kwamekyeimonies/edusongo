
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
</head>
<body>
    <div class="container" style="height:100vh">
    <div class="row justify-content-center align-items-center" style="height:100%;">
        <div class="col-md-4 p-5" style="box-shadow:2px 3px 25px #0003;border-radius:15px">
           <h2>Forgot Password</h2>
            <form action="send_reset_email.php" method="post">
                <div class="form-group">
                    <label for="username">Enter your email address</label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>
                <button type="submit" name="submit" class="btn btn-primary btn-block mb-3">Send Reset Link</button>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>

