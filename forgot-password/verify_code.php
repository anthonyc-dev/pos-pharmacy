<?php include '../includes/header.php'; ?>
<?php session_start(); ?>

<div class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <h4 class="fw-bold text-center mb-2">Verify Code</h4>
        <p class="text-muted small text-center">
            Enter the 6-digit code sent to <?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?>
        </p>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <form action="check_code.php" method="POST">
            <input
                type="text"
                maxlength="6"
                name="code"
                class="form-control form-control-lg mb-3 text-center fs-4"
                required
            >
            <button class="btn btn-danger w-100 btn-lg">Verify</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
