<?php include '../includes/header.php'; ?>
<?php session_start(); ?>

<div class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <h4 class="fw-bold text-center mb-3">Reset Password</h4>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="update_password.php" method="POST">
            <div class="mb-3">
                <label>New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="confirm" class="form-control" required>
            </div>

            <button class="btn btn-danger w-100 btn-lg">Update Password</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
