<?php
$page_title = "Debug Session";
include __DIR__ . '/includes/header.php';
?>
        <section>
            <h2>Debug Session</h2>
            <pre><?php print_r($_SESSION); ?></pre>

            <form method="post" action="reset_session.php">
                <p>
                    <button type="submit">Reset Data</button>
                </p>
            </form>
        </section>
<?php include __DIR__ . '/includes/footer.php'; ?>