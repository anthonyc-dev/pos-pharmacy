<?php include '../includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card shadow-lg p-4" style="width: 100%; max-width: 420px; ">
        <h4 class="fw-bold text-center mb-2">Forgot Password</h4>
        <p class="text-muted text-center small">Enter your email to receive the verification code</p>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="send_code_mail.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control form-control-lg" required>
            </div>
            <button class="btn btn-danger w-100 btn-lg">Send Code</button>

            
            <div class="text-center mt-3">
                <a href="../login.php" class="text-decoration-none">
                    Back to Login
                </a>
            </div>

           
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
