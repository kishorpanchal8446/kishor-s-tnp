</div><!-- /.main-content -->

<footer class="footer-premium" style="background:#0F172A;border-top:1px solid rgba(255,255,255,0.06);color:rgba(148,163,184,0.7);">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div>
            <span style="color:#818CF8;font-weight:700;">ASTPMS Admin</span> &nbsp;·&nbsp;
            Training &amp; Placement Management System &nbsp;·&nbsp;
            &copy; <?= date('Y') ?> All Rights Reserved
        </div>
        <div style="font-size:0.75rem;color:rgba(148,163,184,0.5);">
            Logged in as: <strong style="color:#93C5FD;"><?= htmlspecialchars($_SESSION['admin_name']??'Admin') ?></strong>
            &nbsp;·&nbsp;
            Role: <strong style="color:#C4B5FD;"><?= ucfirst(str_replace('_',' ',$_SESSION['admin_role']??'admin')) ?></strong>
        </div>
    </div>
</footer>

<div class="toast-container-custom" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="../assets/js/app.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
